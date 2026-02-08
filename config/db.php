<?php
// config/db.php
// This file handles the connection to the MySQL database.

// Database credentials
$host = "localhost";
$username = "root";
$password = "";
$dbname = "expense_tracker_db";

// Create connection using procedural mysqli
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    // If connection fails, stop execution and show error
    die("Connection failed: " . mysqli_connect_error());
}
?>
