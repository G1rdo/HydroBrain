import serial
import subprocess
import time
import json
import random
import os
import mysql.connector 
from mysql.connector import errors
#import math

#Upper/Lower Bound (where it becomes no longer good)
acceptableBorder=0.2
# Written by Avi, gets a ratio of 0 to 1 based on the raw value, ideal value, and range (deviation)
# For example, if you want pH to be 6 +/- 0.5, do best = 6 and acceptableRange = 0.5.
def convertRawValueToStatusRatio(best, acceptableRange, rawValue):
    slope = abs((0.5 - acceptableBorder) / acceptableRange)
    yInt = 0.5 - (slope * best)
    return((slope * rawValue) + yInt)

# Compile and send C++ driver code to screen
subprocess.run(["arduino-cli", "compile",
                "-b", "esp32:esp32:esp32",
                "-p", "/dev/ttyUSB0",
                "-u",
                "--warnings", "none",
                "./screen.ino"])
print("Successfully initiated the screen.")
ser = serial.Serial('/dev/ttyUSB0', 115200)
time.sleep(2)

SCREEN_USER = os.environ["SCREEN_USER"]
SCREEN_PASSWORD = os.environ["SCREEN_PASSWORD"]
MYSQL_DATABASE = os.environ["MYSQL_DATABASE"]

def connect_with_retry():
    while True:
        try:
            connection = mysql.connector.connect(
                host="sql",
                user=SCREEN_USER,
                password=SCREEN_PASSWORD,
                database=MYSQL_DATABASE
            )
            print("Connected to database.")
            return connection
        except errors.InterfaceError as e:
            print(f"Database not ready, retrying in 5 seconds... ({e})")
            time.sleep(5)

mydb = connect_with_retry()

mycursor = mydb.cursor()


def readFromDatabase():
    mydb.commit()
    def getAverage(name):
        mycursor.execute(
            "SELECT * FROM sensorData WHERE name = %s ORDER BY time DESC LIMIT 3",
            (name,)
        )
        results = mycursor.fetchall()
        if not results:
            return 0.0
        total = sum(row[2] for row in results)
        return total / len(results)

    pHAverage = getAverage("pH")
    ECAverage = getAverage("conductivity")
    heightAverage = getAverage("height")

    return pHAverage, ECAverage, heightAverage
    

def runScreen():
    print("Sending JSON Encoded Data")
    pHAverage, ECAverage, heightAverage = readFromDatabase()
    print(pHAverage)
    # This would be read from database, and then the color calculated on the python or C++ side
    data = {
        "PH": pHAverage,
        "PH_Status": convertRawValueToStatusRatio(6, 0.5, pHAverage),
        "EC": ECAverage,
        "EC_Status": convertRawValueToStatusRatio(1, 0.2, ECAverage),
        "WaterLevel": "Full",
        "WaterLevel_Status": convertRawValueToStatusRatio(0.5, 0.1, heightAverage),
    }
    print("Sending JSON Encoded Data")
    json_string = json.dumps(data) + "\n"
    print(json_string)
    ser.write(json_string.encode("utf-8"))

while True:
    try:
        runScreen()
        time.sleep(1)

    except KeyboardInterrupt:
        print("Script interrupted by Ctrl+C. Exiting.")
        break
    except Exception as e:
        print(f"An error occurred: {e}")
