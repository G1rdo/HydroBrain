# Hydrobrain
This is the system files for the Hydrobrain Hydroponics system, intended to be run on a Raspberry Pi with a ESP32 Cheap Yellow Display connected via USB.

Run on Linux with 
`sudo docker compose up --build`
or on Windows with
`docker compose up --build`
Remember to give it the correct USB device on both versions, usually USB0. Usually you will need to **bind the USB with WSL for this to work on Windows**.

# First time setup on a fresh minimal Raspberry Pi OS install:
`
curl -fsSL https://get.docker.com | sh
sudo apt update
sudo raspi-config nonint do_i2c 0
git clone git@github.com:G1rdo/Hydrobrain-System.git
cd Hydrobrain-System
echo "This step will take a while for the first time, even over 10 minutes if you're using a micoSD card. A USB SSD or faster storage medium would speed this up."
sudo docker compose up --build
cp .env.example .env
echo "Fill in .env with the environment variables used for the system"
`

This project is a program to be run on a Raspberry Pi as the brain of a hydroponic system. 

Our vision is for my designs and software to be deployed in schools around the world and for them to benefit the students using them meaningfully, and allow them to collect meaningful data.
The goal is to help students learn about biology and apply computer science skills.
This system is meant to be primarily used for small scale experiments, emphasizing affordability and accessibility, however it is feasible at scale. 
To support this goal, the program is controlled by a website hosted on the Pi, with an SQL database containing all the sensor data.
The software which is meant to be modified is to be written in the most common languages taught in schools. Python and MariaDB (for its complete backward compatibility with MySQL), with c++ used only when necessary for sensors.
