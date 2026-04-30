<?php
$conn = new mysqli("localhost", "root", "", "lms_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected successfully!";
?>