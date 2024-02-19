<?php
$ini_array = parse_ini_file("${USER}/HydroBrain/config.ini");
print_r($ini_array);

$user = "site_reader";
$password = "iR33dD@ta";
$database = "sensor_data";
$table = "ph";

try {
  $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
  echo "<h2>$ini_array</h2>";
  echo "<h2>pH Values</h2><ol>"; 
  foreach($db->query("SELECT ph FROM $table") as $row) {
    echo "<li>" . $row['ph'] . "</li>";
  }
  echo "</ol>";
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}