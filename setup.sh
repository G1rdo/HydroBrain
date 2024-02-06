#! /bin/bash
sudo apt update
echo "Starting Pi Sensor interface setup"
# Set the pins to be enabled
raspi-config nonint do_i2c 0
# Install python
sudo apt-get install build-essential git python-dev python-smbus code -y

#MariaDB
sudo apt install libmariadb3 libmariadb-dev

#TODO Find some way around having to break system packages
sudo pip3 install mariadb==1.1.9 --break-system-packages


#To update this file with running OS's options, use dpkg --get-selections and paste them
