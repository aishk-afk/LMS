<?php
session_start();
include 'db_config.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$bookId = $_POST['book_id'] ?? null;

if (!$userId) {
    echo json_encode(["status" => "error", "message" => "Please login first"]);
    exit;
}

// 1. Find one 'Available' copy from the Book_Copy table
$stmt = $conn->prepare("SELECT copy_id FROM Book_Copy WHERE Book_book_id = ? AND status = 'Available' LIMIT 1");
$stmt->bind_param("i", $bookId);
$stmt->execute();
$res = $stmt->get_result();

if ($copy = $res->fetch_assoc()) {
    $copyId = $copy['copy_id'];
    $dueDate = date('Y-m-d', strtotime('+7 days'));

    $conn->begin_transaction();
    try {
        // 2. Change the status to 'Borrowed'. 
        // Next time the catalog loads, the COUNT of 'Available' books will be 1 less.
        $conn->query("UPDATE Book_Copy SET status = 'Borrowed' WHERE copy_id = $copyId");

        // 3. Create the Loan Record
        $loan = $conn->prepare("INSERT INTO Loan (loan_date, due_date, status, User_user_id, Book_Copy_copy_id) VALUES (NOW(), ?, 'Active', ?, ?)");
        $loan->bind_param("sii", $dueDate, $userId, $copyId);
        $loan->execute();

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Book borrowed! Return by $dueDate"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No available copies found in the database."]);
}