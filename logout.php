<?php
// Includes db_connect to ensure the session is started
include 'includes/db_connect.php'; 

// Destroy all session variables
session_unset();

// Destroy the session itself
session_destroy();

// Set a message to display on the index page
$_SESSION['success'] = "You have been logged out successfully.";

// Redirect to the home page
header("Location: index.php");
exit;
?>