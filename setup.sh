#! /bin/bash
sudo apt update
echo "Starting Pi Sensor interface setup"
# Set the pins to be enabled
raspi-config nonint do_i2c 0
# Install python
sudo apt-get install build-essential git python-dev python-smbus code nginx -y

#MariaDB
sudo apt install libmariadb3 libmariadb-dev

#Create venv for python packages 
python3 -m venv hydrobrain_venv
source hydrobrain_venv/bin/activate

sudo pip3 install mariadb==1.1.9

#Website
echo "WARNING: apache2 will be uninstalled if it is already on the system"
sudo chmod og+x website
sudo apt remove apache2

#Moves the website folder from the hydrobrain folder into nginx
sudo mv ~/HydroBrain/website /etc/nginx/sites-available

sudo systemctl start nginx
#Turns on website on device startup
sudo systemctl enable nginx

#To update this file with running OS's options, use dpkg --get-selections and paste them
