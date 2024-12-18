<?php
session_start(); // Start the session

// Destroy the session to log the user out
session_destroy();

// Redirect to the login page with a message
header("Location: login.php?message=logout_success");
exit();
?>
