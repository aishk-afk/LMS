<?php
require_once 'db_config.php';
require_once 'fine_calculator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. DATA RETRIEVAL & SANITIZATION
    $trs_id = filter_var($_POST['trs_id'], FILTER_VALIDATE_INT);
    $condition = $_POST['fine_type']; // Must match ENUM: 'OVERDUE', 'DAMAGED', 'LOST'
    
    // The Admin can override the suggested amount
    $admin_final_amount = filter_var($_POST['final_amount'], FILTER_VALIDATE_FLOAT);

    // 2. INITIAL ERROR CHECKING
    if (!$trs_id) {
        die("Error: Missing or invalid Transaction ID.");
    }

    // 3. DATABASE TRANSACTION START
    // We use a transaction because we must update BOTH 'book_transactions' and 'fines'
    $conn->begin_transaction();

    try {
        // A. FETCH BOOK AND USER DETAILS
        // We need these to calculate/validate the fine in the background
        $query = "SELECT bt.*, b.price, b.fine_per_day, s.is_graduating, s.user_type 
                  FROM book_transactions bt 
                  JOIN books b ON bt.book_id = b.book_id 
                  JOIN students s ON bt.student_id = s.student_id 
                  WHERE bt.trs_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $trs_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Transaction record not found.");
        }
        $data = $result->fetch_assoc();

        // B. CALCULATE DATE DIFFERENCE
        $dueDate = new DateTime($data['due_date']);
        $today = new DateTime();
        $daysLate = ($today > $dueDate) ? $dueDate->diff($today)->days : 0;

        // C. RUN CALCULATOR (Internal Validation)
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

        // D. UPDATE TRANSACTION STATUS
        $updateTrs = "UPDATE book_transactions SET return_date = NOW(), status = 'RETURNED' WHERE trs_id = ?";
        $stmtTrs = $conn->prepare($updateTrs);
        $stmtTrs->bind_param("i", $trs_id);
        $stmtTrs->execute();

        // E. INSERT FINE RECORD (Referencing save.jpg attributes)
        // If the fine is 0 (returned on time/healthy), we can skip this or record a 0 fine
        if ($admin_final_amount > 0) {
            $insertFine = "INSERT INTO fines (
                overdue_date, 
                fine_amount, 
                days_overdue, 
                trs_id, 
                fine_type, 
                payment_status, 
                book_resolution
            ) VALUES (?, ?, ?, ?, ?, 'UNPAID', 'PENDING')";
            
            $stmtFine = $conn->prepare($insertFine);
            // payment_status defaults to UNPAID as per save.jpg
            $stmtFine->bind_param("sdis s", $data['due_date'], $admin_final_amount, $daysLate, $trs_id, $condition);
            $stmtFine->execute();
        }

        // F. COMMIT EVERYTHING
        $conn->commit();
        echo "Success: Return processed. Total fine set at ₱" . number_format($admin_final_amount, 2);

    } catch (Exception $e) {
        // If anything fails, undo all database changes
        $conn->rollback();
        echo "Process Failed: " . $e->getMessage();
    }
}
?>