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
}
?>
<!DOCTYPE html>
<html data-bs-theme="light" lang="en">

<head>
    <?php require 'assets/html/standardHead.html';?>

    <meta name="description" content="HydroBrain school home page">
    <meta name="keywords" content="HydroBrain, home, school, index">
    <!-- If you choose to modify your schools HydroBrain site, feel free to put your name below -->
    <meta name="author" content="">
    <title>HydroBrain</title>
</head>

<body>
    <?php require 'assets/html/header.html';?>
</body>

</html>
