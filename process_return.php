<?php
require_once 'db_config.php';
require_once 'fine_calculator.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = filter_var($_POST['borrow_id'], FILTER_VALIDATE_INT);
    $condition = $_POST['fine_type']; // 'NORMAL', 'DAMAGED', or 'LOST'
    $admin_final_amount = filter_var($_POST['final_amount'], FILTER_VALIDATE_FLOAT);

    $conn->begin_transaction(); // Ensure all updates happen together or not at all

    try {
        // Fetch record to get due_date, book price, and IDs
        $query = "SELECT bt.*, b.price, m.user_id, bt.Book_Copy_copy_id, bt.due_date 
                  FROM book_transaction bt 
                  JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
                  JOIN book b ON bc.Book_book_id = b.book_id 
                  JOIN member m ON bt.Member_user_id = m.user_id 
                  WHERE bt.borrow_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $borrow_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) { throw new Exception("Transaction record not found."); }

        // 1. CALCULATE ACTUAL OVERDUE DAYS
        $today = new DateTime();
        $due_date = new DateTime($data['due_date']);
        $overdue_days = ($today > $due_date) ? $today->diff($due_date)->days : 0;

        // 2. UPDATE TRANSACTION: Mark as returned[cite: 1]
        $updateBt = "UPDATE book_transaction SET return_date = NOW(), status = 'Returned' WHERE borrow_id = ?";
        $stmtBt = $conn->prepare($updateBt);
        $stmtBt->bind_param("i", $borrow_id);
        $stmtBt->execute();

        // 3. UPDATE BOOK COPY STATUS[cite: 1]
        $newStatus = 'Available';
        if ($condition === 'LOST') {
            $newStatus = 'Lost';
        } elseif ($condition === 'DAMAGED') {
            $newStatus = 'Under Repair';
        }

        $updateCopy = "UPDATE book_copy SET status = ? WHERE copy_id = ?";
        $stmtCopy = $conn->prepare($updateCopy);
        $stmtCopy->bind_param("si", $newStatus, $data['Book_Copy_copy_id']);
        $stmtCopy->execute();

        // 4. RECORD FINE: Log to the student's balance[cite: 1]
        if ($admin_final_amount > 0) {
            $insertFine = "INSERT INTO fine (fine_rate, Book_Transaction_borrow_id, total_amount_accrued, balance, overdue_date, overdue_days, Member_user_id, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'Unpaid')";
            
            $stmtFine = $conn->prepare($insertFine);
            $stmtFine->bind_param("diididi", 
                getTieredFineRate($data['price']), // Uses the "Grounded" rates from your settings[cite: 1]
                $borrow_id, 
                $admin_final_amount, 
                $admin_final_amount, // Balance equals total because it's unpaid[cite: 1]
                $data['due_date'], 
                $overdue_days, 
                $data['user_id']
            );
            $stmtFine->execute();
        }

        $conn->commit(); 
        echo "Success: Book is " . $newStatus . ". Fine of ₱" . number_format($admin_final_amount, 2) . " added to account.";

    } catch (Exception $e) {
        $conn->rollback(); 
        echo "Process Failed: " . $e->getMessage();
    }
}
?>
