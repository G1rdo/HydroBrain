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

$user = 'site_reader';
$password = $cfgArray['dataBaseReaderPassword'];
$database = "sensor_data";
$table = "ph";



$color = array(0, 0, 0);
$activeSensors = array("ph", "electrical_conductivity", "dissolved_oxygen");
#TODO: make this be taken from the config file
$acceptableValueRange = [
  "ph" => array(5.5, 6.5),
  "electrical_conductivity" => array(150, 200),
  "dissolved_oxygen" => array(5, 14)
];
 
function valToRGB($value, $max, $min) {
  #If the value is between the max and minimum acceptable values,
  # return status as good and color as green
  if ($max >= $value and $value >= $min) {
      $status = "Good";
      $statusColor = array(0, 255, 0);
  } elseif ($value > $max) {
      $status = "High";
      $variation = abs(fdiv(($value-$max), ($max-$min)));
      if ($variation > 1) {
          $variation = 1;
      } elseif ($variation < 0) {
          $variation = 0;
      }
      #Variation is basically how red it is, from 0 to 1. So when variation is .5, it's yellow, halfway between red and green
      $statusColor = array(intval(round(255*$variation)), 255-intval(round(255*$variation)), 0);
         
  } elseif ($value < $min) {
      $status = "Low";
      $variation = abs(fdiv(($value-$min), ($max-$min)));
      if ($variation > 1) {
          $variation = 1;
      } elseif ($variation < 0) {
          $variation = 0;
      }
      #Variation is basically how red it is, from 0 to 1. So when variation is .5, it's yellow, halfway between red and green
      $statusColor = array(intval(round(255*$variation)), 255-intval(round(255*$variation)), 0);
  }
  return array($statusColor, $status);
}
 

  class valueData {
      public $valueType;
      public $unit;
      public $value;
      public $maxAcceptable;
      public $minAcceptable;
      public $sourceDataBaseTable;
      public $status;
      public $statusColor;
      public $valueDate;
     
     
      function __construct($valueType, $max, $min, $database) {
          $this->valueType = $valueType;
          $this->maxAcceptable = $max;
          $this->minAcceptable = $min;
          $this->sourceDataBaseTable = $database;
         


          try {
              #If you see this and think that the user and password should be not global, dm me or make a pull request :)
              $db = new PDO("mysql:host=localhost;dbname=$database", $GLOBALS['user'], $GLOBALS['password']);
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
         
         $statusArray = valToRGB($value, $max, $min);
         $statusColor = array($statusArray[0][0], $statusArray[0][1], $statusArray[0][2]);
         $this->statusColor = $statusColor;
         $status = $statusArray[1];
         $this->status = $status;
      }
  }
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


<div class="center">
  <h1 style="Background: GhostWhite; border-radius: 5px;
">Status: <span class="name">Good</span></h1>
</div>
<!--<div class="status-right">
  <h1 style="Background: GhostWhite; border-radius: 10px 0px 0px 10px;
">Status: <span class="name">Good</span></h1>
</div>-->
<?php
$count = 0;
foreach ($activeSensors as $valueData) {
  #The even numbered sensors in activeSensors will be on the left, while the odd will be on the right.
  $count = ++$count;
  if ($count % 2 == 1) {
      $side = "right";
  } else {
      $side = "left";
  }
  $minAcceptable = $acceptableValueRange[$valueData][0];
  $maxAcceptable = $acceptableValueRange[$valueData][1];
  $valueData = new valueData($valueData, $maxAcceptable, $minAcceptable, $database);
  #Gets the full rgb status color, and puts it into variable names for each color
  $r = $valueData->statusColor[0];
  $g = $valueData->statusColor[1];
  $b = $valueData->statusColor[2];
  $statusBar = "
<div class=\"status-$side\">
  <div class=\"dot-$side\" style=\"background-color:rgb($r, $g, $b);\"></div>
  <h1>Status: <span class=\"name\">$valueData->status</span></h1>
  <h1 class=\"unit-name-$side\">$dataType</h1>
  <h2 class=\"unit-$side\">$valueData->value</h2>
</div>";
  #Uses the above string as html code with the variables substituted in.
  echo $statusBar;
}
?>
<!--<div class="status-right">
  <div class="dot-right"></div>
  <h1>Status: <span class="name">Test</span></h1>
  <h1 class="unit-name-right">pH</h1>
  <h2 class="unit-right">7.31</h2>
</div>
<div class="status-left">
  <div class="dot-left"></div>
  <h1>Status: <span class="name">Test</span></h1>
  <h1 class="unit-name-left">EC</h1>
  <h2 class="unit-left">400</h2>
</div>

<div class="status-right">
  <div class="dot-right"></div>
  <h1>Status: <span class="name">Good</span></h1>
</div>
<div class="status-left">
  <div class="dot-left"></div>
  <h1>Status: <span class="name">Good</span></h1>
</div> -->

</body>
</html>
