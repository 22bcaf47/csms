<?php
$servername = "my-mysql";
$username = "root";       // change if needed
$password = "root";   // change to your actual MySQL root password
$database = "cms";    // change to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to MySQL on $servername";

// Example query (optional)
$sql = "SELECT NOW() as current_time";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    echo "<br>Server time is: " . $row['current_time'];
}

$conn->close();
?>
