<?php
session_start();
include 'db_config.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$bookId = $_POST['book_id'];

// Check if already on list
$check = $conn->prepare("SELECT * FROM Waitlist WHERE User_user_id = ? AND Book_book_id = ? AND status = 'Waiting'");
$check->bind_param("ii", $userId, $bookId);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "You are already on the waitlist"]);
} else {
    $ins = $conn->prepare("INSERT INTO Waitlist (Book_book_id, User_user_id, waitlist_date, status) VALUES (?, ?, NOW(), 'Waiting')");
    $ins->bind_param("ii", $bookId, $userId);
    $ins->execute();
    echo json_encode(["status" => "success", "message" => "Added to waitlist!"]);
}