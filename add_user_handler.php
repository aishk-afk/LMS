<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$full_name  = trim($_POST['full_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';
$role       = $_POST['role'] ?? 'Student';       // 'Student' or 'Faculty'
$department = trim($_POST['department'] ?? '');
$course     = trim($_POST['course'] ?? '');
$section    = trim($_POST['section'] ?? '');

// --- Validation ---
if (!$full_name || !$email || !$password || !$department) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Check if email already exists
$check = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already exists.']);
    exit;
}
$check->close();

// Split full name into first and last
$name_parts = explode(' ', $full_name, 2);
$first_name = $name_parts[0];
$last_name  = $name_parts[1] ?? '';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// --- Insert into user table ---
$stmt = $conn->prepare("INSERT INTO user (first_name, last_name, email, password, user_type) VALUES (?, ?, ?, ?, 'Member')");
$stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to create user: ' . $stmt->error]);
    exit;
}

$new_user_id = $stmt->insert_id;
$stmt->close();

// --- Insert into member table ---
$stmt2 = $conn->prepare("INSERT INTO member (user_id, Department, Course, Section, Member_Role) VALUES (?, ?, ?, ?, ?)");
$stmt2->bind_param("issss", $new_user_id, $department, $course, $section, $role);

if (!$stmt2->execute()) {
    // Rollback user if member insert fails
    $conn->query("DELETE FROM user WHERE user_id = $new_user_id");
    echo json_encode(['success' => false, 'message' => 'Failed to create member record: ' . $stmt2->error]);
    exit;
}
$stmt2->close();

echo json_encode(['success' => true, 'message' => 'User added successfully!']);