<?php
session_start();
include 'db_config.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$borrow_id = intval($_POST['borrow_id'] ?? 0);

if (!$borrow_id) {
    echo json_encode(['success' => false, 'message' => 'Missing borrow ID.']);
    exit;
}

// 1. Get the transaction details (copy_id and book_id)
$stmt = $conn->prepare("
    SELECT bt.Book_Copy_copy_id, bc.Book_book_id 
    FROM book_transaction bt
    JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
    WHERE bt.borrow_id = ?
");
$stmt->bind_param("i", $borrow_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Transaction not found.']);
    exit;
}

$copy_id    = $row['Book_Copy_copy_id'];
$book_id    = $row['Book_book_id'];

// 2. Mark transaction as Returned
$stmt2 = $conn->prepare("
    UPDATE book_transaction 
    SET status = 'Returned', return_date = CURDATE() 
    WHERE borrow_id = ?
");
$stmt2->bind_param("i", $borrow_id);
$stmt2->execute();
$stmt2->close();

// Clear the fine balance when book is returned
$stmt_fine = $conn->prepare("UPDATE fine SET balance = 0, amount_paid = total_amount_accrued WHERE Book_Transaction_borrow_id = ?");
$stmt_fine->bind_param("i", $borrow_id);
$stmt_fine->execute();
$stmt_fine->close();

// 3. Mark the book copy as Available
$stmt3 = $conn->prepare("UPDATE book_copy SET status = 'Available' WHERE copy_id = ?");
$stmt3->bind_param("i", $copy_id);
$stmt3->execute();
$stmt3->close();

// 4. Check if anyone is on the waitlist for this book (by priority order)
$stmt4 = $conn->prepare("
    SELECT waitlist_id, Member_user_id 
    FROM waitlist 
    WHERE Book_book_id = ? 
    ORDER BY priority ASC, request_date ASC 
    LIMIT 1
");
$stmt4->bind_param("i", $book_id);
$stmt4->execute();
$waitlist = $stmt4->get_result()->fetch_assoc();
$stmt4->close();

$message = 'Book returned successfully.';

if ($waitlist) {
    // 5. Assign the returned copy to the next person on the waitlist
    $next_user_id    = $waitlist['Member_user_id'];
    $waitlist_id     = $waitlist['waitlist_id'];
    $due_date        = date('Y-m-d', strtotime('+14 days')); // 2 week loan

    // Create new borrow transaction for waitlisted user
    $stmt5 = $conn->prepare("
        INSERT INTO book_transaction (Book_Copy_copy_id, Member_user_id, borrow_date, due_date, status)
        VALUES (?, ?, CURDATE(), ?, 'Active')
    ");
    $stmt5->bind_param("iis", $copy_id, $next_user_id, $due_date);
    $stmt5->execute();
    $stmt5->close();

    // Mark copy as Borrowed again
    $stmt6 = $conn->prepare("UPDATE book_copy SET status = 'Borrowed' WHERE copy_id = ?");
    $stmt6->bind_param("i", $copy_id);
    $stmt6->execute();
    $stmt6->close();

    // Remove from waitlist
    $stmt7 = $conn->prepare("DELETE FROM waitlist WHERE waitlist_id = ?");
    $stmt7->bind_param("i", $waitlist_id);
    $stmt7->execute();
    $stmt7->close();

    $message = 'Book returned and assigned to next person on waitlist.';
}

echo json_encode(['success' => true, 'message' => $message]);