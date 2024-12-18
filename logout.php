<?php
session_start(); // Start the session

// Destroy the session and unset all session variables
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the homepage or login page after logout
header("Location: home.php"); // Redirect to login page or home.php as needed
exit();
?>
