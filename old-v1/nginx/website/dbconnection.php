<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
$database = "user_data";

try {
    $db = new PDO("mysql:host=localhost;dbname=$database", $databaseUser, $databasePassword);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>
