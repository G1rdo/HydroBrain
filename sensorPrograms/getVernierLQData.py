#!/usr/bin/python3
#preferences/advanced preferences/Labquest App
from logging import exception
#import urllib.request, urllib.error, urllib.parse
import requests
from time import sleep
'''
Please ensure the following settings are set:
Under home/preferences/advanced preferences/Labquest App, set
interpolate time-based data to True
Under Wifi/Network, set
configuration to manual (don't change other values), and then in the admin configuration set #TODO the ip address needs to be inputable in the config

'''



#config, could probably be done a better way
import sys
#It needs to know the Ip adress to connect to and the sample rate in seconds
DataShareAdress = "192.168.1.213"
sampleRate = 1


#Find current host status
try:
    response = requests.get("http://" + DataShareAdress + "/status")
except:
    raise Exception("Can't connect the the sensor, verify it is online, data sharing is enabled, you are connected to the same internet, and that it's ip adress is correct.")
fullResponse = response.json()
#print(fullResponse)
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
    #print(columns[columnID])
    name = columns[columnID]["name"]
    value = columns[columnID]["liveValue"]
    units = columns[columnID]["units"]
    timeStamp = columns[columnID]["liveValueTimeStamp"]

    # If the graph has been going for more than 100 seconds, turn it off once data collection is finished
    if name == "Time" and int(value) > 100:
        previouslyCollecting = False
    if timeStamp == "":
        raise Exception("Data is being returned as empty, this can be caused by interpolate time-based data being set to false(see note at top of file)")

    print(f'{name} recorded as {value} {units} at {timeStamp}')
    #TODO:Format this as SQL query




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
