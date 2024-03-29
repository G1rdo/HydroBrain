#! /bin/bash
sudo apt update
echo "Starting Pi Sensor interface setup"
# Set the pins to be enabled
sudo raspi-config nonint do_i2c 0
sudo raspi-config nonint do_serial_hw 0
sudo raspi-config nonint do_serial_cons 0
# Install dependencies
sudo apt-get install build-essential git python3 code nginx mariadb-server php8.2-fpm php-mysql sed libnss3-tools -y

#Install mkcert as binary
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/arm64"
chmod +x mkcert-v*-linux-arm64
sudo cp mkcert-v*-linux-arm64 /usr/local/bin/mkcert
rm mkcert-v*-linux-arm64

#Get and set variables
websitePort=$(sed -nr "/^\[website\]/ { :l /^sitePort[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $websitePort
HomeDir=$(printenv HOME)

#MariaDB
#Make database start on system turning on
sudo systemctl enable mariadb
#Start database
sudo systemctl start mariadb


#Create venv for python packages 
python3 -m venv hydrobrain_venv
source ~/HydroBrain/hydrobrain_venv/bin/activate
which python
python3 -m pip install requests mariadb configparser datetime #The following are python packages that are used but are in smtplib: time logging email.message ssl


#Website
echo "WARNING: apache2 will be uninstalled if it is already on the system"
sudo chmod og+x nginx
sudo apt remove apache2
sudo apt-get autoremove

#Sets the port the site is hosted on
sed -e "s/{portvar}/$websitePort/g" ~/HydroBrain/nginx/nginx_template.conf > ~/HydroBrain/nginx/nginx.conf

sudo mv ~/HydroBrain/nginx/nginx.conf /etc/nginx
#Removes the website already in /var/www/website and copies the website folder from the hydrobrain folder into it
sudo rm -r /var/www/website

configEditUser="iEd1tMostC0nfig_ChangeMe"
configEditPassword="site_config"
sudo sed -i --expression "s@^\$configEditUser = \".*\"\;@\$configEditUser = \"$configEditUser\"\;@" nginx/website/assets/html/standardHead.html
sudo sed -i --expression "s@^\$configEditPassword = \".*\"\;@\$configEditPassword = \"$configEditPassword\"\;@" nginx/website/assets/html/standardHead.html

sudo cp -r ~/HydroBrain/nginx/website /var/www
#This replaces all instances of $HYDROBRAINHOME = "" with the same string but the home directory of the main user in it. 
#It could only replace the first, and it breaks if the directory contains the @ symbol in its name, but this was so horrible to make I am not editing it

sudo sed -i --expression "s@^\$HYDROBRAINHOME = \".*\"\;@\$HYDROBRAINHOME = \"$HomeDir\"\;@" /var/www/website/ph.php
sudo sed -i --expression "s@^\$HYDROBRAINHOME = \".*\"\;@\$HYDROBRAINHOME = \"$HomeDir\"\;@" /var/www/website/data.php

sudo systemctl start nginx
#Turns on website on device startup
sudo systemctl enable nginx

#To update this file with running OS's options, use dpkg --get-selections and paste them
