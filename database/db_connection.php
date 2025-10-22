<?php
// Database connection settings
$host = 'localhost'; //localhost
$db   = 'theoldflavour';
$user = 'root'; //root
$pass = ''; //No password
//test

// Create connection
$conn = new mysqli($host, $user, $pass, $db); // Port 3307 for XAMPP, 3306 for WAMP

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>