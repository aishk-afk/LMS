<?php
session_start();
header("Content-Type: application/json");
error_reporting(0);

// change lms_db to with the name of your database
$conn = new mysqli("localhost", "root", "", "lms_db");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = hash('sha256', $_POST['password']);

    //user details
    $stmt = $conn->prepare("SELECT user_id, user_type, first_name, last_name FROM `User` WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_role'] = strtolower($user['user_type']);
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];

        // SEND JSON BACK TO JAVASCRIPT
        echo json_encode([
            "status" => "success",
            "role" => $_SESSION['user_role']
        ]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
        exit;
    }
}
?>