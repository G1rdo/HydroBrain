#!/bin/bash
#Declare the associative array configRules
declare -A configRules
: '
Gets database startup variables from the config.ini config page, by their key and section

@param string $sourceRule
  The name of the source rule (the key)
@param string $sourceSection
  The section in the config file that the key is under

@return array
  What is the thing returned?
'
getVariable() {
  #In the file config.ini, search for the first rule with the key of $1 after the section titled $2
  $ruleValue=$(sed -nr "/^\[$2\]/ { :l /^$1[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
  #Append the key and value pair to the configRules associative array
  configRules+=([$1]=$ruleValue)
}

getVariable "dataBaseMainPassword" "database"
echo "The Returned value is: "$configRules["dataBaseMainPassword"]
#Get all the varaibles we need
root_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseMainPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $root_password
siteAccessConfigPassword=$(sed -nr "/^\[database\]/ { :l /^dataBaseSiteConfigPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $siteAccessConfigPassword
reader_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseReaderPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $reader_password
inserter_password=$(sed -nr "/^\[database\]/ { :l /^dataBaseInserterPassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $inserter_password

supportedpHSensors=$(sed -nr "/^\[database\]/ { :l /^supportedpHSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $supportedpHSensors
pHUnits=$(sed -nr "/^\[database\]/ { :l /^PHUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $pHUnits
supportedDOSensors=$(sed -nr "/^\[database\]/ { :l /^supportedDOSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $supportedDOSensors
DOUnits=$(sed -nr "/^\[database\]/ { :l /^DOUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $DOUnits
supportedECSensors=$(sed -nr "/^\[database\]/ { :l /^supportedECSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $supportedECSensors
ECUnits=$(sed -nr "/^\[database\]/ { :l /^ECUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $ECUnits
supportedTimeSensors=$(sed -nr "/^\[database\]/ { :l /^supportedTimeSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $supportedTimeSensors
TimeUnits=$(sed -nr "/^\[database\]/ { :l /^TimeUnits[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" config.ini)
echo $TimeUnits


#This is a replacement for Mariadb-secure-installation
sudo mariadb -e "SET PASSWORD FOR root@localhost = PASSWORD(\"$root_password\");FLUSH PRIVILEGES;" 
sudo mariadb -e "DROP USER IF EXISTS ''@'$(hostname)'"
sudo mariadb -e "DROP USER IF EXISTS ''@'localhost'"
sudo mariadb -e "DROP DATABASE IF EXISTS test"
sudo mariadb -e "FLUSH PRIVILEGES"

#Create config database and fill it with tables
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS config"

sudo mariadb -e "use config;CREATE TABLE IF NOT EXISTS main ( \
    rule varchar(255) PRIMARY KEY NOT NULL COMMENT 'The name of the rule being defined.', \
    value varchar(255) NOT NULL COMMENT 'The name of the rule being defined.', \
    edit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP \
    ) ENGINE=INNODB COMMENT='General configuration, including rules including but not limited to: Email lists, Email account password, and critical system passwords.';" \
 
echo "main configuration table created."

echo "adding data to main config table."
sudo mariadb -e "REPLACE INTO config.main (rule, value) VALUES ('dataBaseSiteConfigPassword', '$siteAccessConfigPassword');"
echo "data added to config table."



sudo mariadb -e "use config;CREATE TABLE IF NOT EXISTS site ( \
    rule varchar(255) NOT NULL PRIMARY KEY COMMENT 'The name of the rule being defined.', \
    value varchar(255) NOT NULL COMMENT 'The name of the rule being defined.', \
    edit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP \
    ) ENGINE=INNODB COMMENT='Nginx website configuration, including rules including but not limited to: Email lists, Email account password, site ports.';" \
 
echo "main configuration table created."

sudo mariadb -e "use config;CREATE TABLE IF NOT EXISTS sensors ( \
    sensor_name varchar(255) NOT NULL PRIMARY KEY COMMENT 'The name of sensor which is being declared.', \
    standard_unit varchar(255) NOT NULL COMMENT 'The unit the data should be stored as. This should be the most common unit you would use for this data tyoe', \
    accuracy decimal(19,6) NOT NULL COMMENT 'The accuracy of that sensor in ± (plusminus) with the declared standard unit.', \

    standard_unit_minimum_value FLOAT SIGNED COMMENT 'In the standard unit the minimum possible value returned by the sensor.', \
    standard_unit_maximum_value FLOAT SIGNED COMMENT 'In the standard unit the maximum possible value returned by the sensor.', \

    temperature_range_minimum FLOAT SIGNED COMMENT 'In celsius the minimum safe opperating temperature.', \
    temperature_range_maximum FLOAT SIGNED COMMENT 'In celsius the maximum safe opperating temperature.' \
    ) ENGINE=INNODB COMMENT='Configuration for the different types of sensors and informaton about them.';" \
 
echo "sensor configuration table created."

sudo mariadb -e "use config;CREATE TABLE IF NOT EXISTS data_collection ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    is_active BOOLEAN NOT NULL COMMENT 'Should data be collected from the data source?', \
    hourly_sample_rate FLOAT DEFAULT '2' COMMENT 'How many times an hour should data be requested from the source.', \
    collection_type ENUM('LabQuest2', 'Other') NOT NULL COMMENT 'The type of data source being used. For example, one data type coming from a vernier labquest.', \
    sensor_name ENUM('PH-BTA') COMMENT 'The sensor which is collecting the data', \
    ip_address INET6 COMMENT 'The ip address of the data source. Currently only used for vernier labquest equipment, and is a major security warning if not a private ip.' \
    ) ENGINE=INNODB COMMENT='Configuration for what sensors will be active and information required for their use';" \
 
echo "data collection configuration table created."


#Create sensor database and fill it with tables
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS sensor_data"

sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS ph ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedpHSensors), \
    ph decimal (8, 6), \
    units ENUM($pHUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB  COMMENT='Storage for ph data.';" \
 
echo "pH table created."


sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS dissolved_oxygen ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedDOSensors), \
    dissolved_oxygen decimal (13, 6), \
    units ENUM($DOUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB COMMENT='Storage for dissolved oxygen data.';" \
 
echo "DO table created."

sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS electrical_conductivity ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedECSensors), \
    electrical_conductivity decimal (13, 6), \
    units ENUM($ECUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB COMMENT='Storage for electrical conductivity data.';" \
 
echo "EC table created."

sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS time ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    probe_name ENUM($supportedTimeSensors), \
    time decimal (13, 6), \
    units ENUM($TimeUnits), \
    sql_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB COMMENT='Storage for timestamps.';" \
 
echo "Time table created."


sudo mariadb -e "CREATE DATABASE IF NOT EXISTS user_data"

sudo mariadb -e "use user_data;CREATE TABLE IF NOT EXISTS users ( \
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, \
    username varchar(255) NOT NULL COMMENT 'The username of the user.', \
    password varchar(255) NOT NULL COMMENT 'The password of the user.', \
    name varchar(255) NULL DEFAULT '' COMMENT 'The name of the user.' \
    ) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Storage for user authentication details.';" \
 
echo "User table created."

 
#Grant privileges to the unprivledged website server to read data from the sensor_data database. 
sudo mariadb -e "CREATE USER IF NOT EXISTS 'site_reader'@'localhost' IDENTIFIED BY '$reader_password'"
sudo mariadb -e "GRANT SELECT ON sensor_data.* TO 'site_reader'@'localhost' IDENTIFIED BY '$reader_password';"

#Grant privileges to the privledged and authenticated website server user to write data to the config database. 
sudo mariadb -e "CREATE USER IF NOT EXISTS 'site_config'@'localhost' IDENTIFIED BY '$siteAccessConfigPassword'"
sudo mariadb -e "GRANT SELECT ON sensor_data.* TO 'site_config'@'localhost' IDENTIFIED BY '$siteAccessConfigPassword';"
#Grant access to most config files
sudo mariadb -e "GRANT SELECT, INSERT, UPDATE, DELETE ON config.site TO 'site_config'@'localhost' IDENTIFIED BY '$siteAccessConfigPassword';"
sudo mariadb -e "GRANT SELECT, INSERT, UPDATE, DELETE ON config.sensors TO 'site_config'@'localhost' IDENTIFIED BY '$siteAccessConfigPassword';"
sudo mariadb -e "GRANT SELECT, INSERT, UPDATE, DELETE ON config.data_collection TO 'site_config'@'localhost' IDENTIFIED BY '$siteAccessConfigPassword';"

#Grant privileges to the sensors and data inputs to put data into the database
sudo mariadb -e "CREATE USER IF NOT EXISTS 'data_inserter'@'localhost' IDENTIFIED BY '$inserter_password'"
sudo mariadb -e "GRANT INSERT ON sensor_data.* TO 'data_inserter'@'localhost' IDENTIFIED BY '$inserter_password';"
# Make our changes take effect
sudo mariadb -e "FLUSH PRIVILEGES"
