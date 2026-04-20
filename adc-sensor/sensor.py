import adafruit_ads1x15.ads1115 as ADS
from adafruit_ads1x15.analog_in import AnalogIn
import time
import board
import busio
import os
import mysql.connector
from mysql.connector import errors
import random

TEMPERATURE = 22.0

# Initialize I2C and ADS1115 ADC
i2c = busio.I2C(board.SCL, board.SDA)
ads = ADS.ADS1115(i2c)

# Set the Analog Input to A0
channel0 = AnalogIn(ads, 0)
channel1 = AnalogIn(ads, 1)
channel2 = AnalogIn(ads, 2)

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


# Taken from the DFRobot wiki, returns is mv/cm
def calculateEC(voltage, temperature=TEMPERATURE):
    compensation_coefficient = 1.0 + 0.02 * (temperature - 25.0)
    compensation_voltage = voltage / compensation_coefficient
    ec = (133.42 * compensation_voltage**3
          - 255.86 * compensation_voltage**2
          + 857.39 * compensation_voltage)
    ec = ec / 1000
    return ec
def calculatePH(voltage):
    return (voltage * 7.15105) - 7.44333

while True:
    try:
        #writeDummyData()
        #ppm_TDS = getSerialData()
        #mSCM_TDS = convertppmTomilliSiemens(ppm_TDS)

        # Get Electrical Conductivity
        ec = calculateEC(channel0.voltage, 20)
        print(ec)
        writeData("conductivity", ec)
        sql = "INSERT INTO sensorData (name, reading) VALUES (%s, %s)"

	# PH
        pH = calculatePH(channel1.voltage)
        #print("Super awesome value: -------- " + str(calculatePH(channel1.voltage)))
        #randompH = 6 + (random.random() - 0.5) * 3
        writeData("pH", pH)

	# EC
	#randomEC = 1 + (random.random() - 0.5) * 0.5
	#val  = ("conductivity", randomEC)
	#mycursor.execute(sql, val)

	# Water Height
        #randomWaterHeight = random.randint(0, 1)
        waterLevel = channel2.voltage
        writeData("height", waterLevel)

        #print(f"Voltage: {channel0.voltage:.2f}V EC: {ec:.2f}")
        #print(f"Raw Value: {channel0.value}, Voltage: {channel0.voltage:.2f}V")
        time.sleep(1)

    except KeyboardInterrupt:
        print("Script interrupted by Ctrl+C. Exiting.")
        break
    except Exception as e:
        print(f"An error occurred: {e}")
