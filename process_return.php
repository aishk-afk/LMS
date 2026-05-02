<?php
require_once 'db_config.php';
require_once 'fine_calculator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trs_id = filter_var($_POST['trs_id'], FILTER_VALIDATE_INT);
    $condition = $_POST['fine_type']; 
    $admin_final_amount = filter_var($_POST['final_amount'], FILTER_VALIDATE_FLOAT);

    if (!$trs_id) {
        die("Error: Missing or invalid Transaction ID.");
    }

    $conn->begin_transaction();

    try {
        $query = "SELECT bt.*, b.price, b.fine_per_day, s.is_graduating, s.user_type 
                  FROM book_transactions bt 
                  JOIN books b ON bt.book_id = b.book_id 
                  JOIN students s ON bt.student_id = s.student_id 
                  WHERE bt.trs_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $trs_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) {
            throw new Exception("Transaction record not found.");
        }

        // Calculate Days Late
        $dueDate = new DateTime($data['due_date']);
        $today = new DateTime();
        $daysLate = ($today > $dueDate) ? $dueDate->diff($today)->days : 0;

        // FIXED: The variables now match the Calculator's new header exactly
        $calc = getCalibratedFine(
            $data['price'], 
            $data['fine_per_day'], 
            $daysLate, 
            $condition, 
            $data['is_graduating'], 
            $data['user_type']
        );

        if (isset($calc['error'])) {
            throw new Exception($calc['error']);
        }

        // Update Transaction
        $updateTrs = "UPDATE book_transactions SET return_date = NOW(), status = 'RETURNED' WHERE trs_id = ?";
        $stmtTrs = $conn->prepare($updateTrs);
        $stmtTrs->bind_param("i", $trs_id);
        $stmtTrs->execute();

        // Insert Fine
        if ($admin_final_amount > 0) {
            $insertFine = "INSERT INTO fines (overdue_date, fine_amount, days_overdue, trs_id, fine_type, payment_status, book_resolution) 
                           VALUES (?, ?, ?, ?, ?, 'UNPAID', 'PENDING')";
            $stmtFine = $conn->prepare($insertFine);
            $stmtFine->bind_param("sdis s", $data['due_date'], $admin_final_amount, $daysLate, $trs_id, $condition);
            $stmtFine->execute();
        }

        $conn->commit();
        echo "Success: Return processed. Amount: ₱" . number_format($admin_final_amount, 2);

    } catch (Exception $e) {
        $conn->rollback();
        echo "Process Failed: " . $e->getMessage();
    }
}
