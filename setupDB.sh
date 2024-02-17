#!/bin/bash
 
#Get all the varaibles we need
root_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseMainPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ./config.ini)
echo $root_password
supportedpHSensors=$(sed -nr "/^\[database\]/ { :l /^supportedpHSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ./config.ini)
echo $supportedpHSensors
reader_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseReaderPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ./config.ini)
echo $reader_password

#This is a replacement for Mariadb-secure-installation
sudo mariadb -e "SET PASSWORD FOR root@localhost = PASSWORD(\"$root_password\");FLUSH PRIVILEGES;" 
sudo mariadb -e "DROP USER IF EXISTS ''@'$(hostname)'"
sudo mariadb -e "DROP USER IF EXISTS ''@'localhost'"
sudo mariadb -e "DROP DATABASE IF EXISTS test"
sudo mariadb -e "FLUSH PRIVILEGES"


#Create database and fill it with some data 
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS sensor_data"
sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS ph ( \
    id INT NOT NULL auto_increment PRIMARY KEY, \
    probe_name ENUM($supportedpHSensors), \
    ph decimal (8, 6), \
    sql_timestamp TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB;" \
 
echo "pH table created."

echo "Inserting data into pH table..."
 
 
query1="use sensor_data; INSERT INTO ph (probe_name, ph, sensor_timestamp) \
        VALUES('PH-BTA', 3.14149, '2024-02-06 00:33:06+00:00')"
query2="use sensor_data; INSERT INTO ph (probe_name, ph, sensor_timestamp) \
        VALUES('PH-BTA', 3.14149, '2024-02-06 00:33:06+00:00')"
query3="use sensor_data; INSERT INTO ph (probe_name, ph, sensor_timestamp) \
        VALUES('PH-BTA', 30.222, '2024-02-06 00:33:06+00:00')"
query4="use sensor_data; INSERT INTO ph (probe_name, ph, sensor_timestamp) \
        VALUES('E-312', 11.0011111111, '2024-02-06 00:33:06+00:00')"
 
sudo mariadb -e "$query1"
sudo mariadb -e "$query2"
sudo mariadb -e "$query3"
sudo mariadb -e "$query4"
 
 
#Grant privileges to the website server to read data from the sensor_data database. 
sudo mariadb -e "CREATE USER IF NOT EXISTS 'site_reader'@'localhost' IDENTIFIED BY '$reader_password'"
sudo mariadb -e "GRANT SELECT ON sensor_data.* TO 'site_reader'@'localhost' IDENTIFIED BY '$reader_password';"
# Make our changes take effect
sudo mariadb -e "FLUSH PRIVILEGES"
