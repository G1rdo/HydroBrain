<?php
require_once 'dbconnection.php';
// Generate two test users.
$userData = [
    [
        'user1',
        password_hash('password', PASSWORD_DEFAULT),
        'User One',
    ],
    [
        'user2',
        password_hash('letmein', PASSWORD_DEFAULT),
        'User Two',
    ],
];
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
foreach ($userData as $id => $userDatum) {
    // prepare sql and bind parameters
    $stmt = $db->prepare("INSERT INTO users (username, password, name)
    VALUES (:username, :password, :email)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':name', $name);
    // insert a row
    $username = $userD;
    $password = "Doe";
    $name = "john@example.com";
    $stmt->execute();
}

echo 'Authentication example table (re)created and the default users installed.' . PHP_EOL;
/*
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
}*/

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>HydroBrain Status</title>
    <link rel="apple-touch-icon" type="image/png" sizes="180x180" href="assets/img/iosicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/bigfavicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/bigfavicon.png" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" sizes="180x180" href="assets/img/iosicon.png">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Articles-Badges-images.css">
    <link rel="stylesheet" href="assets/css/Footer-Multi-Column-icons.css">

<script type="text/javascript">
var something=<?php echo json_encode($a); ?>;
</script>

<style>
@font-face {
  font-family: 'Noto Sans Mono';
  src: url('assets/css/Noto_Sans_Mono.woff') format('woff');
}
.center {
  margin: auto;
  width: 40%;
  /*border: 3px solid #73AD21;
  padding: 0px;*/
  text-align: center;
}
.status-right {
  /*margin: auto;*/
  margin-left: 6.5%;
  margin-top: 1%;
  margin-bottom: 1%;
  /*margin-right: 20px;*/
  /*height: 0px;*/
  position: relative;
  height: 100px;
  width: 95%;
  padding: 0px;
  border-radius: 10px;
  text-align: center;
  border-radius: 50px 0px 0px 50px;
  Background: GhostWhite;
}
.status-left {
  /*margin: auto;*/
  /*margin-right: 10px;*/
  /*height: 0px;*/
  margin-top: 1%;
  margin-bottom: 1%;
  position: relative;
  margin-left: -1.5%;
  height: 100px;
  width: 95%;
  padding: 0px;
  border-radius: 10px;
  text-align: center;
  border-radius: 0px 50px 50px 0px;
  Background: GhostWhite;
}
.dot-right {
  position: absolute;
  margin: 0;
  top: 50%;
  -webkit-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
  /*right: 95%;*/
  left: 20px;
  height: 60px;
  width: 60px;
  /*background-color: #bbb;*/
  border-radius: 50%;
  display: block;
}
.dot-left {
  position: absolute;
  margin: 0;
  top: 50%;
  -webkit-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
  /*left: 0%;*/
  right: 20px;
  height: 60px;
  width: 60px;
  /*background-color: #bbb;*/
  border-radius: 50%;
  display: block;
}
/*p.detail { color:#4C4C4C;font-weight:bold;font-family:Calibri;font-size:20 }*/
/*span.name { color:#00BF63;font-weight:bold;font-family:Tahoma;font-size:20 }*/
h1 {
  /*font-family: Andale Mono, monospace;*/
  /*font-family: arial black;*/
 
  font-family: 'Lucia Grande', monospace;
  font-family: verdana, sans-serif;
  font-family: Liberation Mon, monospace;
  font-family: Verdana;
  font-family: arial black;
  font-family: Tahoma;
  font-family: 'Noto Sans Mono', monospace;
  font-weight: 800;
}
h1.unit-name-right {
  position: absolute;
  top: 10%;
  left: 90px;
  text-align: left;
  font-family: 'Lucia Grande', monospace;
  font-family: verdana, sans-serif;
  font-family: Liberation Mon, monospace;
  font-family: Verdana;
  font-family: arial black;
  font-family: Tahoma;
  font-family: 'Noto Sans Mono', monospace;
  font-weight: 800;
}
h1.unit-name-left {
  position: absolute;
  top: 10%;
  right: 90px;
  text-align: left;
  font-family: 'Lucia Grande', monospace;
  font-family: verdana, sans-serif;
  font-family: Liberation Mon, monospace;
  font-family: Verdana;
  font-family: arial black;
  font-family: Tahoma;
  font-family: 'Noto Sans Mono', monospace;
  font-weight: 800;
}
h2.unit-right {
  position: absolute;
  top: 50%;
  left: 110px;
  text-align: left;
  font-family: 'Lucia Grande', monospace;
  font-family: verdana, sans-serif;
  font-family: Liberation Mon, monospace;
  font-family: Verdana;
  font-family: arial black;
  font-family: Tahoma;
  font-family: 'Noto Sans Mono', monospace;
  font-weight: 800;
}
h2.unit-left {
  position: absolute;
  top: 50%;
  right: 110px;
  text-align: left;
  font-family: 'Lucia Grande', monospace;
  font-family: verdana, sans-serif;
  font-family: Liberation Mon, monospace;
  font-family: Verdana;
  font-family: arial black;
  font-family: Tahoma;
  font-family: 'Noto Sans Mono', monospace;
  font-weight: 800;
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-md bg-body py-3" style="margin: 0px;border-width: 8px;border-color: var(--bs-navbar-brand-color);border-bottom-width: 3px;border-bottom-style: solid;border-radius: 10px;">
        <div class="container"><a class="navbar-brand d-flex align-items-center" href="#"><span><img src="assets/img/icon.svg" width="57" height="49">HydroBrain</span></a><button data-bs-toggle="collapse" class="navbar-toggler" data-bs-target="#navcol-2"><span class="visually-hidden">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navcol-2">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="./index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://github.com/G1rdo/HydroBrain/">Downloads</a></li>
                    <li class="nav-item"><a class="nav-link" href="./setup.html">Setup</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">About us</a></li>
                </ul><a class="btn btn-primary ms-md-2" role="button" href="#">Button</a>
            </div>
        </div>
    </nav>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>

<h1>WARNING: This login page is not secure, do not input confidential information!</h1>
<div class="center">
  <h1 style="Background: GhostWhite; border-radius: 5px;
">Status: <span class="name">Good</span></h1>
</div>
<!--<div class="status-right">
  <h1 style="Background: GhostWhite; border-radius: 10px 0px 0px 10px;
">Status: <span class="name">Good</span></h1>
</div>-->

</body>
</html>
