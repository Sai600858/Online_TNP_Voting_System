<?php
// CRUCIAL: Must start the session before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";     // Default XAMPP username
$password = "";         // Default XAMPP password (often empty)
$dbname = "college_elections"; // MUST match your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection and stop the script if it fails
if ($conn->connect_error) {
    die("❌ Database Connection Failed: " . $conn->connect_error);
}

// Set character set for security and data handling
$conn->set_charset("utf8mb4"); 
?>