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

# Load the page
function loadPage() {
    echo("page loaded");
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // User credentials have been entered, trim them to prevent common
        // whitespace mistakes.
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
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
    </div>

    <button type="submit" class="login-button">Login</button>
</form>

</body>

</html>