<?php
$HYDROBRAINHOME = "Stringtoreplace";
$cfgLocation = $HYDROBRAINHOME . "/HydroBrain/config.ini";
try {
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
$dataPoints0 = array();
try {
    $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
    foreach($db->query("SELECT ph, sql_timestamp FROM $table WHERE id > (SELECT count(id)/2 FROM $table)") as $row) {#SELECT ph, sql_timestamp FROM $table") as $row) {
        #$dataPoints0[] = array("x" => $row['sql_timestamp'], "y" => $row['ph']);
        $dataPoints0[] = array("label" => $row['sql_timestamp'], "y" => $row['ph']);
        #echo "<li>" . $row['ph'] . "</li>";
        #echo "<li>" . $row['sql_timestamp'] . "</li>";
    }
    #echo "</ol>";
    #print_r($dataPoints0);
  } catch (PDOException $e) {
      print "Error!: " . $e->getMessage() . "<br/>";
      die();
  }
  $table = "electrical_conductivity";
  $dataPoints1 = array();
  try {
      $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
      foreach($db->query("SELECT electrical_conductivity, sql_timestamp FROM $table WHERE id > (SELECT count(id)/2 FROM $table)") as $row) {#"SELECT electrical_conductivity, sql_timestamp FROM $table") as $row) {
          $dataPoints1[] = array("label" => $row['sql_timestamp'], "y" => $row['electrical_conductivity']);
      }
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
  $table = "dissolved_oxygen";
  $dataPoints2 = array();
  try {
      $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
      foreach($db->query("SELECT dissolved_oxygen, sql_timestamp FROM $table WHERE id > (SELECT count(id)/2 FROM $table)") as $row) {#"SELECT dissolved_oxygen, sql_timestamp FROM $table") as $row) {
          $dataPoints2[] = array("label" => $row['sql_timestamp'], "y" => $row['dissolved_oxygen']);
      }
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }


 
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>HydroBrain Data Viewer</title>
    <link rel="apple-touch-icon" type="image/png" sizes="180x180" href="assets/img/iosicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/bigfavicon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/bigfavicon.png" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/png" sizes="180x180" href="assets/img/iosicon.png">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/Articles-Badges-images.css">
    <link rel="stylesheet" href="assets/css/Footer-Multi-Column-icons.css">
<script>
window.onload = function () {
 
var chart0 = new CanvasJS.Chart("chartContainer0", {
	title: {
		text: "pH Over Time"
	},
	axisY: {
		title: "pH"
	},
    axisX:{      
        valueFormatString: "DD-MMM" ,
        //labelAngle: -20
    },
	data: [{
		type: "line",
		dataPoints: <?php echo json_encode($dataPoints0, JSON_NUMERIC_CHECK); ?>
	}]
});
chart0.render();
var chart1 = new CanvasJS.Chart("chartContainer1", {
	title: {
		text: "Electrical Conductivity Over Time"
	},
	axisY: {
		title: "EC (µS/cm)"
	},
    axisX:{      
        valueFormatString: "DD-MMM" ,
        //labelAngle: -20
    },
	data: [{
		type: "line",
		dataPoints: <?php echo json_encode($dataPoints1, JSON_NUMERIC_CHECK); ?>
	}]
});
chart1.render();
var chart2 = new CanvasJS.Chart("chartContainer2", {
	title: {
		text: "Dissolved Oxygen Over Time"
	},
	axisY: {
		title: "DO (mg/L)"
	},
    axisX:{      
        valueFormatString: "DD-MMM" ,
        //labelAngle: -45
    },
	data: [{
		type: "line",
		dataPoints: <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>
	}]
});
chart2.render();
}
</script>
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
                </ul><a class="btn btn-primary ms-md-2" role="Data" href="./data.php">Button</a>
            </div>
        </div>
    </nav>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
<div id="chartContainer0" style="height: 370px; width: 90%; margin: 0 auto;"></div>
<div id="chartContainer1" style="height: 370px; width: 90%; margin: 0 auto;"></div>
<div id="chartContainer2" style="height: 370px; width: 90%; margin: 0 auto;"></div>
<script src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
</body>

</html>
