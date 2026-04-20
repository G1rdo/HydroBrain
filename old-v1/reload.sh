#! /bin/bash
#Get and set variables
websitePort=$(sed -nr "/^\[website\]/ { :l /^sitePort[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $websitePort
HomeDir=$(printenv HOME)

#MariaDB
#Start database
sudo systemctl start mariadb



#Website
sudo chmod og+x nginx

#Sets the port the site is hosted on
sed -e "s/{portvar}/$websitePort/g" nginx/nginx_template.conf > nginx/nginx.conf

sudo mv nginx/nginx.conf /etc/nginx
#Removes the website already in /var/www/website and copies the website folder from the hydrobrain folder into it
sudo rm -r /var/www/website
sudo cp -r nginx/website /var/www
#This replaces all instances of $HYDROBRAINHOME = "" with the same string but the home directory of the main user in it. 
#It could only replace the first, and it breaks if the directory contains the @ symbol in its name, but this was so horrible to make I am not editing it
sudo sed -i --expression "s@^\$HYDROBRAINHOME = \".*\"\;@\$HYDROBRAINHOME = \"$HomeDir\"\;@" /var/www/website/ph.php
sudo sed -i --expression "s@^\$HYDROBRAINHOME = \".*\"\;@\$HYDROBRAINHOME = \"$HomeDir\"\;@" /var/www/website/data.php

sudo systemctl start nginx
sudo systemctl reload nginx
