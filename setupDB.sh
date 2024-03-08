#!/bin/bash
 
#Get all the varaibles we need
root_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseMainPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $root_password
reader_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseReaderPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $reader_password
inserter_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseInserterPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $inserter_password

supportedpHSensors=$(sed -nr "/^\[database\]/ { :l /^supportedpHSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $supportedpHSensors
pHUnits=$(sed -nr "/^\[database\]/ { :l /^PHUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $pHUnits
supportedDOSensors=$(sed -nr "/^\[database\]/ { :l /^supportedDOSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $supportedDOSensors
DOUnits=$(sed -nr "/^\[database\]/ { :l /^DOUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $DOUnits
supportedECSensors=$(sed -nr "/^\[database\]/ { :l /^supportedECSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $supportedECSensors
ECUnits=$(sed -nr "/^\[database\]/ { :l /^ECUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $ECUnits
supportedTimeSensors=$(sed -nr "/^\[database\]/ { :l /^supportedTimeSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $supportedTimeSensors
TimeUnits=$(sed -nr "/^\[database\]/ { :l /^TimeUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ~/HydroBrain/config.ini)
echo $TimeUnits


#This is a replacement for Mariadb-secure-installation
sudo mariadb -e "SET PASSWORD FOR root@localhost = PASSWORD(\"$root_password\");FLUSH PRIVILEGES;" 
sudo mariadb -e "DROP USER IF EXISTS ''@'$(hostname)'"
sudo mariadb -e "DROP USER IF EXISTS ''@'localhost'"
sudo mariadb -e "DROP DATABASE IF EXISTS test"
sudo mariadb -e "FLUSH PRIVILEGES"


#Create sensor database and fill it with tables
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS sensor_data"

sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS ph ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedpHSensors), \
    ph decimal (8, 6), \
    units ENUM($pHUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB;" \
 
echo "pH table created."


sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS dissolved_oxygen ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedDOSensors), \
    dissolved_oxygen decimal (13, 6), \
    units ENUM($DOUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB;" \
 
echo "DO table created."

sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS electrical_conductivity ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedECSensors), \
    electrical_conductivity decimal (13, 6), \
    units ENUM($ECUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB;" \
 
echo "EC table created."

sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS time ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedTimeSensors), \
    time decimal (13, 6), \
    units ENUM($TimeUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB;" \
 
echo "Time table created."


sudo mariadb -e "CREATE DATABASE IF NOT EXISTS user_data"

sudo mariadb -e "use user_data;CREATE TABLE IF NOT EXISTS users ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    username varchar(255) NOT NULL COMMENT 'The username of the user.', \
    password varchar(255) NOT NULL COMMENT 'The password of the user.', \
    name varchar(255) NULL DEFAULT '' COMMENT 'The name of the user.', \
    ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Storage for user authentication details.';" \
 
echo "User table created."

 
#Grant privileges to the website server to read data from the sensor_data database. 
sudo mariadb -e "CREATE USER IF NOT EXISTS 'site_reader'@'localhost' IDENTIFIED BY '$reader_password'"
sudo mariadb -e "GRANT SELECT ON sensor_data.* TO 'site_reader'@'localhost' IDENTIFIED BY '$reader_password';"
sudo mariadb -e "CREATE USER IF NOT EXISTS 'data_inserter'@'localhost' IDENTIFIED BY '$inserter_password'"
sudo mariadb -e "GRANT INSERT ON sensor_data.* TO 'data_inserter'@'localhost' IDENTIFIED BY '$inserter_password';"
# Make our changes take effect
sudo mariadb -e "FLUSH PRIVILEGES"
