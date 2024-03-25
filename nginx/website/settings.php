<?php
// Start the session to include the session variables.
session_start();

if (!isset($_SESSION['user_id'])) {
    // If the user's userid isn't set, send them to the login page
    header('Location: login.php');
    exit();
} else {
  // If the user's userid is set, then load the page
  loadPage();
} 
#Translates database data type to the html form version
function translateDbTohtml($dbType) {
    $translate = array(
        'BOOLEAN' => 'checkbox'
        'VAR_STRING' => 'string',
        'STRING' => 'string',
        'BLOB' => 'blob',
        'LONGLONG' => 'int',
        'LONG' => 'int',
        #'SHORT' => 'number\" min="', doesnt seem to be supported by mariadb
        'DATETIME' => 'datetime',
        'DATE' => 'date',
        'FLOAT' => 'number" min="-34020000000000000" max="34020000000000000"',
        'DOUBLE' => 'number" min="-34020000000000000" max="34020000000000000"',
        'TIMESTAMP' => 'timestamp'
    );
    #Returns the translated database type https://stackoverflow.com/questions/3157831/how-can-i-determine-the-column-type-with-pdo
    return $translate[$dbType];
}
# Load the page
function loadPage() {
    echo("page loaded");
    require 'assets/html/configEditorAccount.php';
    try {
        #If you see this and think that the user and password should be not global, dm me or make a pull request :)
        $db = new PDO("mysql:host=localhost;dbname=config", $configEditUser, $configEditPassword);
        foreach($db->query("SELECT rule, value, edit_date FROM main WHERE id = (SELECT MAX(id) FROM $valueType)") as $row) {
            $this->unit = $row['units'];
            $value = $row[$valueType];
            $this->value = $value;
            $this->valueDate = $row['sensor_timestamp'];
        }
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
         
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <?php require 'assets/html/standardHead.html';?>

    <meta name="description" content="HydroBrain config page">
    <meta name="keywords" content="settings, config, configuration, HydroBrain, school">
    <!-- If you choose to modify your schools HydroBrain site, feel free to put your name below -->
    <meta name="author" content="">
    <title>HydroBrain Settings</title>
</head>

<body>
<?php require 'assets/html/header.html';?>

<form action="settings.php" method="post">
    <div class="login">
        <label class="form-label" for="login-username">Username</label>
        <input type="text" name="username" placeholder="Username" id="form-username" autocomplete="on" class="form-control" value="<?php echo $_POST['username'] ?? '';?>">
        <br>
        <label class="form-label" for="login-password">Password</label>
        <input type="password" name="password" placeholder="Password" id="form-password" autocomplete="on" class="form-control">

    <?php
    #Get the username and password variables for the config editing account
    require 'assets/html/configEditorAccount.php';
    try {
        #If you see this and think that the user and password should be not global, dm me or make a pull request :)
        $db = new PDO("mysql:host=localhost;dbname=config", $configEditUser, $configEditPassword);
        foreach($db->query("SELECT rule, value, edit_date FROM main") as $row) {
            $rule = $row['rule'];
            $value = $row['value'];
            $editDate = $row['edit_date'];
            #WARINGING: Unfinished. Steps to complete in email
            $formQuestion = "
        <br>
        <label class=\"form-label\" for=\"login-username\">$rule</label>
        <input type=\"text\" name=\"username\" placeholder=\"$value\" id=\"form-username\" autocomplete=\"on\" class=\"form-control\" value=\"<?php echo $_POST['username'] ?? '';?>\">";
            echo $formQuestion;
        }
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
    ?>
    </div>

    <button type="submit" class="login-button">Login</button>
</form>

</body>

</html>
