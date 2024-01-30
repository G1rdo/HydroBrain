#! /bin/bash
sudo apt-get update
echo "Starting Pi Sensor interface setup"
# Set the pins to be enabled
raspi-config nonint do_i2c 0
# Install python
sudo apt-get install build-essential git python-dev python-smbus code

#To update this file with running OS's options, use dpkg --get-selections and paste them
