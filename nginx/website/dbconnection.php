<?php
$HYDROBRAINHOME = "Stringtoreplace";
$cfgLocation = $HYDROBRAINHOME . "/HydroBrain/config.ini";
try {
    #Try and read the config file
    $cfgArray = parse_ini_file($cfgLocation);
    if (!(is_readable($cfgLocation))) {
        throw new Exception("Parsing of config file failed, no read permission.");
    } elseif ($cfgArray == "") {
        throw new Exception("Parsing of config file with parse_ini_file returned as empty. Likely cause is a syntax error in the config.");
        #Make sure there are no banned characters in the file.
    }
} catch (Exception $e) {
    print $e->getMessage();
}

$databaseUser = 'site_reader';
$databasePassword = $cfgArray['dataBaseReaderPassword'];
$database = "sensor_data";
$databaseTable = "ph";

try {
    #If you see this and think that the user and password should be not global, dm me or make a pull request :)
    $db = new PDO("mysql:host=localhost;dbname=$database", $databaseUser, $databasePassword);
    foreach($db->query("SELECT units, $valueType, sensor_timestamp FROM $valueType WHERE id = (SELECT MAX(id) FROM $valueType)") as $row) {
        $this->unit = $row['units'];
        $value = $row[$valueType];
        $this->value = $value;
        $this->valueDate = $row['sensor_timestamp'];
    }
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>
