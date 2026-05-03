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

// 1. Find one 'Available' copy from the book_copy table
$stmt = $conn->prepare("SELECT copy_id FROM book_copy WHERE Book_book_id = ? AND status = 'Available' LIMIT 1");
$stmt->bind_param("s", $bookId);
$stmt->execute();
$res = $stmt->get_result();

if ($copy = $res->fetch_assoc()) {
    $copyId = $copy['copy_id'];
    $dueDate = date('Y-m-d', strtotime('+14 days'));
    $borrowId = uniqid('BT-');

    $conn->begin_transaction();
    try {
        // 2. Change the status to 'Borrowed'.
        $stmt_update = $conn->prepare("UPDATE book_copy SET status = 'Borrowed' WHERE copy_id = ?");
        $stmt_update->bind_param("s", $copyId);
        $stmt_update->execute();
        $stmt_update->close();

        // 3. Create the transaction record
        $stmt_trans = $conn->prepare("INSERT INTO book_transaction (borrow_id, Book_Copy_copy_id, Member_user_id, borrow_date, due_date, status) VALUES (?, ?, ?, NOW(), ?, 'Borrowed')");
        $stmt_trans->bind_param("ssss", $borrowId, $copyId, $userId, $dueDate);
        $stmt_trans->execute();
        $stmt_trans->close();

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Book borrowed! Return by $dueDate"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No available copies found in the database."]);
}