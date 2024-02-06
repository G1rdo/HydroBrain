#!/usr/bin/python3
from logging import exception
import requests
from time import sleep
import mariadb
import configparser
from datetime import datetime, timezone
from email.message import EmailMessage
import ssl
import smtplib

'''
Please ensure the following settings are set:
Under home/preferences/advanced preferences/Labquest App, set
interpolate time-based data to True
Under Wifi/Network, set
configuration to manual (don't change other values), and then in the admin configuration set #TODO the ip address needs to be inputable in the config

'''

config = configparser.ConfigParser()
config.read('HydroBrain/config.ini')

print(config)
print(config.sections())
#It needs to know the Ip adress to connect to and the sample rate in seconds
DataShareAdress = config['vernier.getLQData']['LabQuestIp']
print(DataShareAdress)
sampleRate = config['vernier.getLQData']['serverSampleRate']
SQLNames = {"pH": "ph"} 
sensorNames = {"pH": "PH-BTA"}
dataBasePassword = config['database']['dataBasePassword']
emailSender = config['general']['sendFromEmail']
emailPassword = config['general']['emailPassword']
emailReceivers = config['general']['emailReceivers']


#TODO Make this actually do something
timeZone = str(config['general']['timeZone'])

def sendEmail(emailSender, emailPassword, emailReceivers, subject, body):
    print(emailSender)
    print(emailPassword)
    em = EmailMessage()
    em['From'] = emailSender
    em['To'] = emailReceivers
    em['Subject'] = subject
    em.set_content(body)

    context = ssl.create_default_context()

    with smtplib.SMTP_SSL('smtp.gmail.com', 465, context=context) as smtp:
        smtp.login(emailSender, emailPassword)
        smtp.sendmail(emailSender, emailReceivers, em.as_string())
#Find current host status
try:
    response = requests.get("http://" + DataShareAdress + "/status")
except:
    raise Exception("Can't connect the the sensor, verify it is online, data sharing is enabled, you are connected to the same internet, and that it's ip adress is correct.")
fullResponse = response.json()
canControl = response.json()["collection"]["canControl"]
print(canControl)
isCollecting = response.json()["collection"]["isCollecting"]
print(isCollecting)
previouslyCollecting = isCollecting

#Start it collecting if not already, verify agent credentials function
if canControl and not(isCollecting):
    print("Not collecting data, turning on collection")
    response = requests.get("http://" + DataShareAdress + "/start")
    isCollecting = response.json()["result"]
    print("Returned sign", isCollecting)
    while not(isCollecting):
        print("server responed that is has not started collecting data, trying again in 5 seconds")
        sleep(5)
        response = requests.get("http://" + DataShareAdress + "/start")
        isCollecting = response.json()["result"]
        print(isCollecting)
elif not(canControl) and not(isCollecting):
    raise Exception("Can't control system to turn it on, and it isn't already broadcasting data on this network.")
elif not(canControl) and isCollecting:
    print("User agent not sucessfully connected with permissions, double check pasword in configuration file. However, the Vernier LabQuest is already collecting data, so we can still collect data.")
else:
    # It's already running, and has permissions, so we don't need to do anything
    pass



sleep(4)


response = requests.get("http://" + DataShareAdress + "/status")
responseData = response.json()
timeRecorded = responseData["columnListTimeStamp"] #Note that this value is in Epoch Seconds, use online converter to find human readable timestamp

#Finds which data set is the most recent (hihgest value in set number)
sets = responseData["sets"]
columns = responseData["columns"]

setNumbers = {
}
#I am sorry for this horrible naming convention, Vernier decided that their Jsons needed to be like this
for setNum in sets:
    setNumbers[sets[setNum]["position"]]=setNum
#print("The biggest number is:", max(setNumbers), " and it's ID is:", setNumbers[max(setNumbers)])
currentSetID = setNumbers[max(setNumbers)]

for columnID in sets[currentSetID]["colIDs"]:
    print(columnID)
    name = columns[columnID]["name"]
    value = columns[columnID]["liveValue"]
    units = columns[columnID]["units"]
    timeStamp = datetime.fromtimestamp(columns[columnID]["liveValueTimeStamp"], timezone.utc)

    if timeStamp == "":
        raise Exception("Data is being returned as empty, this can be caused by interpolate time-based data being set to false(see note at top of file)")
    if name == "pH":
        if value < 5.5:
            sendEmail(
                emailSender,
                emailPassword,
                emailReceivers,
                'PH dropped below acceptable levels',
                """
                Hydrobrain detected a pH level below 5.5 today
                """
            )
            print("pH too low")
        elif value > 6.5:
            sendEmail(
                emailSender,
                emailPassword,
                emailReceivers,
                'PH dropped below acceptable levels',
                """
                Hydrobrain detected a pH level above 6.5 today
                """
            )
            print("pH too high")
    print(f'{name} recorded as {value} {units} at {timeStamp}')
    #TODO:Format this as SQL query
    try:
        conn = mariadb.connect(
            user="root", 
            password=dataBasePassword,
            host="127.0.0.1",
            port=3306,
            database='sensor_data'
        )
    except mariadb.Error as e:
        print(f"Error connecting to MariaDB Platform: {e}")
        raise Exception("Error occured connecting to MariaDB")
    cursor = conn.cursor()
    if name in SQLNames:
        print(SQLNames[name])
        cursor.execute(
                "INSERT INTO " + SQLNames[name] + " (probe_name, " + SQLNames[name] + ", sensor_timestamp) VALUES (?, ?, ?)", 
                (sensorNames[name], value, timeStamp))


sleep(1)
#Close connection if opened by program
if not(previouslyCollecting):
    goodTurnOff = False
    response = requests.get("http://" + DataShareAdress + "/stop")
    goodTurnOff = response.json()["result"]
    while not(goodTurnOff):
        print("Server responed that is has not ceased collecting data, trying again")
        sleep(5)
        response = requests.get("http://" + DataShareAdress + "/stop")
        goodTurnOff = response.json()["result"]
        print(goodTurnOff)
    print("Successfuly turned off system")
