#!/usr/bin/python3

from logging import exception
#import urllib.request, urllib.error, urllib.parse
import requests
from time import sleep

#config, could probably be done a better way
import sys
#It needs to know the Ip adress to connect to, username, password, sample rate in seconds
DataShareAdress = "ip address expunged"


#Find current host status
try:
    response = requests.get("http://" + DataShareAdress + "/status")
except:
    raise Exception("Can't connect the the sensor, verify it is online, data sharing is enabled, you are connected to the same internet, and that it's ip adress is correct.")
fullResponse = response.json()
print(fullResponse)
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
        print("server responed that is has not started collecting data, trying again")
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




response = requests.get("http://" + DataShareAdress + "/status")
API_Data = response.json()
timeRecorded = API_Data["columnListTimeStamp"] #Note that this value is in Epoch Unix Timestamp, use online converter to find human readable timestamp
viewsData = API_Data["columns"]
#TODO: Get data out of this and make sql query
print(viewsData)
sleep(.5)

#Close connection if opened by program
if not(previouslyCollecting):
    goodTurnOff = False
    response = requests.get("http://" + DataShareAdress + "/stop")
    goodTurnOff = response.json()["result"]
    print(goodTurnOff)

    while not(goodTurnOff):
        print("Server responed that is has not ceased collecting data, trying again")
        sleep(5)
        response = requests.get("http://" + DataShareAdress + "/stop")
        goodTurnOff = response.json()["result"]
        print(goodTurnOff)
    print("Successfuly turned off system")
