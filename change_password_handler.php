<?php
session_start();
include 'db_config.php';
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$user_id      = $_SESSION['user_id'];
$current_pw   = $_POST['current_password'] ?? '';
$new_pw       = $_POST['new_password'] ?? '';

if (!$current_pw || !$new_pw) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (strlen($new_pw) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

// Fetch the stored password hash
$stmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

$stored_hash = $row['password'];

// sha256 hashing
$is_correct = ($stored_hash === hash('sha256', $current_pw));

if (!$is_correct) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit;
}

// Hash new password with bcrypt and save
$new_hash = hash('sha256', $new_pw);
$update   = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
$update->bind_param("si", $new_hash, $user_id);

if ($update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
}
$update->close();