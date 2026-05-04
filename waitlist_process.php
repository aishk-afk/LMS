<?php
session_start();
include 'db_config.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$bookId = $_POST['book_id'] ?? null;

if (!$userId || !$bookId) {
    echo json_encode(["status" => "error", "message" => "Unable to determine user or book."]);
    exit;
}

// Check if already on list
$check = $conn->prepare("SELECT * FROM waitlist WHERE Member_user_id = ? AND Book_book_id = ?");
$check->bind_param("ss", $userId, $bookId);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "You are already on the waitlist"]);
    exit;
}

$priority = 1;
$ins = $conn->prepare("INSERT INTO waitlist (Book_book_id, Member_user_id, request_date, priority) VALUES (?, ?, NOW(), ?)");
$ins->bind_param("ssi", $bookId, $userId, $priority);

if ($ins->execute()) {
    echo json_encode(["status" => "success", "message" => "Added to waitlist!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Unable to add waitlist entry: " . $conn->error]);
}
