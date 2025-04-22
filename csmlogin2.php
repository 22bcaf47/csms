<?php
include("dbconnection.php"); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($email) || empty($password)) {
        die("<script>alert('All fields are required!'); window.location.href='login.php';</script>");
    }

 
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        
        if ($password === $stored_password) { 
            echo "<script>alert('Login successful!'); window.location.href='collection.php';</script>";
        } else {
            echo "<script>alert('Invalid email or password!'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='login.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>