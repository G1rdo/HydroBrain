#!/bin/bash
 
#The following code is mostly not mine, its author is really awesome though, find them at
#https://geekdudes.wordpress.com/2020/07/16/linux-bash-script-for-creating-and-configuring-maria-database/
 
sudo apt-get install mariadb-server -y
sudo systemctl enable mariadb
sudo systemctl start mariadb
 
root_password=$(sed -nr "/^\[database\]/ { :l /^dataBasePassword[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ./config.ini)
echo $root_password
supportedpHSensors=$(sed -nr "/^\[database\]/ { :l /^supportedpHSensors[ ]*=/ { s/[^=]*=[ ]*//; p; q;}; n; b l;}" ./config.ini)
echo $supportedpHSensors

sudo mariadb -e "SET PASSWORD FOR root@localhost = PASSWORD('$root_password');FLUSH PRIVILEGES;" 
printf "$root_password\n n\n n\n n\n y\n y\n y\n" | sudo mysql_secure_installation

 
# Make sure that NOBODY can access the server without a password
sudo mariadb -e "UPDATE mysql.user SET Password = PASSWORD('$root_password') WHERE User = 'root'"
 
# Kill the anonymous users
sudo mariadb -e "DROP USER IF EXISTS ''@'localhost'"
# Because our hostname varies we'll use some Bash magic here.
sudo mariadb -e "DROP USER IF EXISTS ''@'$(hostname)'"
# Kill off the demo database
sudo mariadb -e "DROP DATABASE IF EXISTS test"
 
 
echo "Creating main database..."
 
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS sensor_data"

sudo mariadb -e "use sensor_data;CREATE TABLE IF NOT EXISTS ph ( \
    id INT NOT NULL auto_increment PRIMARY KEY, \
    probe_name ENUM($supportedpHSensors), \
    ph decimal (8, 6), \
    sql_timestamp TIMESTAMP, \
    sensor_timestamp TIMESTAMP \
    ) ENGINE=INNODB;" \
 
echo "Table ph created."

echo "Inserting data into ph table..."
 
 
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
 
 
echo "Inserting dummy data into ph table finished"



<< com
echo "Creating staging database..."
 
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS staging"
 
echo "Creating production database..."
 
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS production"
 
echo "Creating table tasks in staging database..."
 
sudo mariadb -e "use staging;CREATE TABLE IF NOT EXISTS tasks ( \
    task_id INT AUTO_INCREMENT PRIMARY KEY, \
    title VARCHAR(255) NOT NULL, \
    start_date DATE, \
    due_date DATE, \
    status TINYINT NOT NULL, \
    priority TINYINT NOT NULL, \
    description TEXT \
    ) ENGINE=INNODB;" \
 
echo "Table tasks created."
 
 
echo "Inserting data into tasks table..."
 
 
query1="use staging; INSERT INTO tasks (title, start_date, due_date, status, priority, description) \
        VALUES('task1', '2020-07-01', '2020-07-31', 1, 1, 'this is the first task')"
 
 
query2="use staging; INSERT INTO tasks (title, start_date, due_date, status, priority, description) \
        VALUES('task2', '2020-08-01', '2020-08-31', 2, 2, 'this is the second task')"
 
 
query3="use staging; INSERT INTO tasks (title, start_date, due_date, status, priority, description) \
        VALUES('task3', '2020-09-01', '2020-09-30', 1, 1, 'this is the third task')"
 
 
query4="use staging; INSERT INTO tasks (title, start_date, due_date, status, priority, description) \
        VALUES('task4', '2020-10-01', '2020-10-31', 1, 1, 'this is fourth task')"
 
 
 
 
 
sudo mariadb -e "$query1"
sudo mariadb -e "$query2"
sudo mariadb -e "$query3"
sudo mariadb -e "$query4"
 
 
echo "Inserting dummy data into tasks table finished"
 
 
echo "Creating table named 'completed' into production database..."
 
 
sudo mariadb -e "use production; CREATE TABLE IF NOT EXISTS completed ( \
    task_id INT AUTO_INCREMENT PRIMARY KEY, \
    task_name VARCHAR(255) NOT NULL, \
    finished_date DATE, \
    status TEXT, \
    description TEXT \
    ) ENGINE=INNODB;" \
 
echo "Populating completed table with some dummy data..."
 
query_1="use production; INSERT INTO completed (task_name, finished_date, status, description) \
        VALUES('task1', '2020-07-31','done', 'task one finished')"
 
 
query_2="use production; INSERT INTO completed (task_name, finished_date, status, description) \
        VALUES('task2', '2020-08-31','completed', 'task two finished')"
 
query_3="use production; INSERT INTO completed (task_name, finished_date, status, description) \
        VALUES('task3', '2020-09-30','done', 'task three finished')"
 
query_4="use production; INSERT INTO completed (task_name, finished_date, status, description) \
        VALUES('task4', '2020-10-31','done', 'task four finished')"
 
 
 
 
sudo mariadb -e "$query_1"
sudo mariadb -e "$query_2"
sudo mariadb -e "$query_3"
sudo mariadb -e "$query_4"
 
echo "Database named 'completed' pupulated with dummy data."
 
echo "Creating staging_user and grant all permissions to staging database..."
 
sudo mariadb -e "CREATE USER IF NOT EXISTS 'staging_user'@'localhost' IDENTIFIED BY 'password1'"
 
sudo mariadb -e "GRANT ALL PRIVILEGES ON staging.* to 'staging_user'@'localhost'"
 
 
echo "Creating production_user and grant all permissions to production database..."
 
sudo mariadb -e "CREATE USER IF NOT EXISTS 'production_user'@'localhost' IDENTIFIED BY 'password2'"
 
sudo mariadb -e "GRANT ALL PRIVILEGES ON production.* to 'production_user'@'localhost'"

com

# Make our changes take effect
sudo mariadb -e "FLUSH PRIVILEGES"
