<?php
session_start();
include 'db_config.php';
header('Content-Type: application/json');

// Get borrow_id from POST
$borrowId = $_POST['borrow_id'] ?? null;

if (!$borrowId) {
    echo json_encode(['status' => 'error', 'message' => 'Borrow ID is required']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get the book copy ID from the borrow record
    $stmt = $conn->prepare("SELECT Book_Copy_copy_id FROM book_transaction WHERE borrow_id = ?");
    $stmt->bind_param("s", $borrowId);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrowRow = $result->fetch_assoc();
    $stmt->close();
    
    if (!$borrowRow) {
        throw new Exception("Borrow record not found");
    }
    
    $copyId = $borrowRow['Book_Copy_copy_id'];
    
    // Update book_transaction status to 'Returned' and set return_date
    $stmt = $conn->prepare("UPDATE book_transaction SET status = 'Returned', return_date = NOW() WHERE borrow_id = ?");
    $stmt->bind_param("s", $borrowId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update book transaction: " . $stmt->error);
    }
    $stmt->close();
    
    // Update book_copy status back to 'Available'
    $stmt = $conn->prepare("UPDATE book_copy SET status = 'Available' WHERE copy_id = ?");
    $stmt->bind_param("s", $copyId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to update book copy: " . $stmt->error);
    }
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Book returned successfully']);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
