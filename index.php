<?php
// Server configuration
$servername = "my-mysql";
$username = "root";  // Replace with your MySQL username
$password = "root";  // Replace with your MySQL password
$database = "csm";  // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to the database!";
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP MySQL Connection Test</title>
</head>
<body>
    <h1>Welcome to My PHP App</h1>
    <p>This page connects to the MySQL server named <strong>my-mysql</strong>.</p>
</body>
</html>
