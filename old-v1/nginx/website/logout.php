<?php
// Get the session data
session_start();

// Unset the session data
session_unset();

// Destroy the session
session_destroy();

// Redirect the user to the index page.
header("Location: index.html");
?>
