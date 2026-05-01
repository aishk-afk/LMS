<?php
header('Content-Type: application/json');
include 'db_config.php';

if (isset($_GET['id'])) {
    $bookId = intval($_GET['id']);

    // Start a transaction to ensure both deletions happen together
    $conn->begin_transaction();

    try {
        // 1. Delete all copies of this book first (Child table)
        $stmt1 = $conn->prepare("DELETE FROM Book_Copy WHERE Book_book_id = ?");
        $stmt1->bind_param("i", $bookId);
        $stmt1->execute();

        // 2. Delete the book record (Parent table)
        $stmt2 = $conn->prepare("DELETE FROM Book WHERE book_id = ?");
        $stmt2->bind_param("i", $bookId);
        $stmt2->execute();

        // If everything is successful, save changes
        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Book and its copies deleted successfully."]);

    } catch (Exception $e) {
        // If there is an error, undo any partial deletions
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Failed to delete: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No book ID provided."]);
}
?>