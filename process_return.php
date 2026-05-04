<?php
require_once 'db_config.php';
require_once 'fine_calculator.php'; // Ensure this matches your calculator filename

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrow_id = filter_var($_POST['borrow_id'], FILTER_VALIDATE_INT);
    $condition = $_POST['fine_type']; // Expected 'NORMAL', 'DAMAGED', or 'LOST'
    $admin_final_amount = filter_var($_POST['final_amount'], FILTER_VALIDATE_FLOAT);

    $conn->begin_transaction(); // Start atomic operation

    try {
        // Fetch detailed record to link the Copy, Price, and User[cite: 4]
        $query = "SELECT bt.*, b.price, m.user_id, bt.Book_Copy_copy_id 
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

        // 1. UPDATE TRANSACTION: Mark as returned[cite: 4]
        $updateBt = "UPDATE book_transaction SET return_date = NOW(), status = 'Returned' WHERE borrow_id = ?";
        $stmtBt = $conn->prepare($updateBt);
        $stmtBt->bind_param("i", $borrow_id);
        $stmtBt->execute();

        // 2. UPDATE BOOK COPY STATUS: Change based on condition[cite: 4]
        // If LOST, copy stays 'Lost'. If DAMAGED, set to 'Under Repair'. Otherwise, 'Available'.
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

        // 3. RECORD FINE: Apply tiered rate logic[cite: 4]
        if ($admin_final_amount > 0) {
            $insertFine = "INSERT INTO fine (fine_rate, Book_Transaction_borrow_id, total_amount_accrued, balance, overdue_date, overdue_days, Member_user_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            // Note: overdue_days can be calculated using DATEDIFF between NOW() and $data['due_date'][cite: 4]
            $stmtFine = $conn->prepare($insertFine);
            $stmtFine->bind_param("diididi", 
                getTieredFineRate($data['price']), // Dynamic tiered rate[cite: 4]
                $borrow_id, 
                $admin_final_amount, 
                $admin_final_amount, 
                $data['due_date'], 
                0, 
                $data['user_id']
            );
            $stmtFine->execute();
        }

        $conn->commit(); // Save all changes[cite: 4]
        echo "Success: Book copy is now " . $newStatus . ". Fine: ₱" . number_format($admin_final_amount, 2);

    } catch (Exception $e) {
        $conn->rollback(); // Undo everything if one step fails[cite: 4]
        echo "Process Failed: " . $e->getMessage();
    }
}
?>
