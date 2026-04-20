import serial
import subprocess
import time
import random
import os
import mysql.connector 
from mysql.connector import errors

# Compile and send C++ driver code to sensor
subprocess.run(["arduino-cli", "compile",
                "-b", "arduino:avr:nano",
                "-p", "/dev/ttyUSB0",
                "-u",
                "--warnings", "none",
                "./sensor.ino"])
print("Successfully initiated the sensor.")
ser = serial.Serial('/dev/ttyUSB0', 115200, timeout = 2)
time.sleep(2)

SENSOR_USER = os.environ["SENSOR_USER"]
SENSOR_PASSWORD = os.environ["SENSOR_PASSWORD"]
MYSQL_DATABASE = os.environ["MYSQL_DATABASE"]

def connect_with_retry():
    while True:
        try:
            connection = mysql.connector.connect(
                host="sql",
                user=SENSOR_USER,
                password=SENSOR_PASSWORD,
                database=MYSQL_DATABASE
            )
            print("Connected to database.")
            return connection
        except errors.InterfaceError as e:
            print(f"Database not ready, retrying in 5 seconds... ({e})")
            time.sleep(5)

mydb = connect_with_retry()

mycursor = mydb.cursor()


def writeDummyData():
    sql = "INSERT INTO sensorData (name, reading) VALUES (%s, %s)"

    # PH
    randompH = 6 + (random.random() - 0.5) * 3
    val = ("pH", randompH)
    mycursor.execute(sql, val)

    # EC
    randomEC = 1 + (random.random() - 0.5) * 0.5
    val  = ("conductivity", randomEC)
    mycursor.execute(sql, val)

    # Water Height
    randomWaterHeight = random.randint(0, 1)
    val  = ("height", randomWaterHeight)
    mycursor.execute(sql, val)
    mydb.commit()

    print("Inserted pH: " + str(randompH) + " EC: " + str(randomEC) + " & Water Height: " + str(randomWaterHeight))

def writeData(name, value):
    sql = "INSERT INTO sensorData (name, reading) VALUES (%s, %s)"

    val = (name, value)
    mycursor.execute(sql, val)
    
    mydb.commit()

    print("Inserted " + name + ": " + str(value))


def getSerialData():
    line = ser.readline().decode('utf-8').strip()
    print(f"Raw: {line}")
    if line.startswith("TDS----Value:") and line.endswith("ppm"):
        tds = float(line.replace("TDS----Value:", "").replace("ppm", ""))
        print(f"TDS: {tds} ppm")
        return tds
    return None

# This is only a rough conversion for hydroponics
# Uses a common factor where 1 mS/cm is approximately 640 ppm
def convertppmTomilliSiemens(ppm, factor=1.56):
    return ppm / (factor * 1000)

while True:
    try:
        #writeDummyData()
        ppm_TDS = getSerialData()
        mSCM_TDS = convertppmTomilliSiemens(ppm_TDS)
        writeData("conductivity", mSCM_TDS)
        time.sleep(1)

    except KeyboardInterrupt:
        print("Script interrupted by Ctrl+C. Exiting.")
        break
    except Exception as e:
        print(f"An error occurred: {e}")
