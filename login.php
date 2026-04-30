<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

/* ✅ FIX DATABASE NAME */
$conn = mysqli_connect("localhost", "root", "", "lms_db");

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "DB failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Missing fields"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];

        /* ✅ FIX: match hashed password */
        $password = hash('sha256', $password);

        if ($password === $user['password']) {

            // 🔥 CHECK ADMIN
            $adminCheck = $conn->prepare("SELECT user_id FROM admin WHERE user_id = ?");
            $adminCheck->bind_param("s", $user_id);
            $adminCheck->execute();
            $adminResult = $adminCheck->get_result();

            if ($adminResult->num_rows > 0) {
                echo json_encode([
                    "status" => "success",
                    "role" => "admin",
                    "user_id" => $user_id
                ]);
                exit;
            }

            // 🔥 CHECK MEMBER
            $memberCheck = $conn->prepare("SELECT user_id FROM member WHERE user_id = ?");
            $memberCheck->bind_param("s", $user_id);
            $memberCheck->execute();
            $memberResult = $memberCheck->get_result();

            if ($memberResult->num_rows > 0) {
                echo json_encode([
                    "status" => "success",
                    "role" => "member",
                    "user_id" => $user_id
                ]);
                exit;
            }

            echo json_encode([
                "status" => "error",
                "message" => "User has no role assigned"
            ]);

        } else {
            echo json_encode(["status" => "error", "message" => "Wrong password"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
    }

    $stmt->close();
}
?>