<?php
$servername = "my-mysql";
$username = "root";       // change if needed
$password = "root";   // change to your actual MySQL root password
$database = "cms";    // change to your actual database name

// Enable exceptions for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    echo "Connected successfully to MySQL on $servername";

    // Example query
    $sql = "SELECT NOW() AS current_time";
    $result = $conn->query($sql);

    if ($row = $result->fetch_assoc()) {
        echo "<br>Server time is: " . $row['current_time'];
    }

    $conn->close();
} catch (mysqli_sql_exception $e) {
    echo "<br><strong>MySQL error:</strong> " . $e->getMessage();
}
?>
