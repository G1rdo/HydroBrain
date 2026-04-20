<?php

$HYDROBRAINHOME = "Stringtoreplace";
$cfgArray = parse_ini_file("${HYDROBRAINHOME}/HydroBrain/config.ini");
#print_r($cfgArray['dataBaseReaderPassword']);
$user = 'site_reader';
$password = $cfgArray['dataBaseReaderPassword'];
$database = "sensor_data";
$table = "ph";
#print_r($cfgArray);

try {
  $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
  echo "<h2>Array: $cfgArray</h2>";
  echo "<h2>pH Values</h2><ol>"; 
  foreach($db->query("SELECT ph FROM $table") as $row) {
    print_r($row);
    #echo "<li>" . $row['ph'] . "</li>";
  }
  echo "</ol>";
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}