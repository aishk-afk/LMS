<?php
header('Content-Type: application/json');
include 'db_config.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'stats';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-7 days'));
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
$period = isset($_GET['period']) ? $_GET['period'] : 'week';

// Validate and sanitize date format
$startDate = date('Y-m-d', strtotime($startDate));
$endDate = date('Y-m-d', strtotime($endDate));
$startYear = intval(date('Y', strtotime($startDate)));
$endYear = intval(date('Y', strtotime($endDate)));

// Function to execute query and return data
function executeQuery($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return ['error' => mysqli_error($conn)];
    }
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Get active borrows count
if ($type === 'activeBorrows') {
    $sql = "SELECT COUNT(*) as count FROM book_transaction 
            WHERE status = 'Borrowed' 
            AND DATE(borrow_date) >= '$startDate' 
            AND DATE(borrow_date) <= '$endDate'";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($data['count'] ?? 0)]);
    exit;
}

// Get overdue items count
if ($type === 'overdueItems') {
    $sql = "SELECT COUNT(*) as count FROM book_transaction 
            WHERE status = 'Overdue' 
            AND due_date < NOW()";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['count' => $data['count'] ?? 0]);
    exit;
}

// Get total books
if ($type === 'totalBooks') {
    $sql = "SELECT COUNT(DISTINCT book_id) as count FROM book";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($data['count'] ?? 0)]);
    exit;
}

// Get fines collected in period
if ($type === 'finesCollected') {
    $sql = "SELECT COALESCE(SUM(f.amount_paid), 0) as total FROM fine f
            INNER JOIN book_transaction bt ON f.Book_Transaction_borrow_id = bt.borrow_id
            WHERE DATE(bt.borrow_date) >= '$startDate' 
            AND DATE(bt.borrow_date) <= '$endDate'
            AND f.amount_paid > 0";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['total' => intval($data['total'] ?? 0)]);
    exit;
}

// Get borrowing trends data
if ($type === 'borrowingTrends') {
    if ($period === 'week') {
        // Daily data for the week
        $sql = "SELECT DATE(borrow_date) as date, 
                COUNT(CASE WHEN status = 'Borrowed' OR return_date IS NULL THEN 1 END) as borrows,
                COUNT(CASE WHEN status = 'Returned' AND return_date IS NOT NULL THEN 1 END) as returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(borrow_date)
                ORDER BY borrow_date ASC";
    } elseif ($period === 'month') {
        // Weekly data for the month
        $sql = "SELECT CONCAT('Week ', WEEK(borrow_date) - WEEK(DATE_SUB(borrow_date, INTERVAL DAYOFMONTH(borrow_date)-1 DAY)) + 1) as label,
                COUNT(CASE WHEN status = 'Borrowed' OR return_date IS NULL THEN 1 END) as borrows,
                COUNT(CASE WHEN status = 'Returned' AND return_date IS NOT NULL THEN 1 END) as returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY WEEK(borrow_date)
                ORDER BY borrow_date ASC";
    } else {
        // Monthly data for the year
        $sql = "SELECT DATE_FORMAT(borrow_date, '%b') as label,
                COUNT(CASE WHEN status = 'Borrowed' OR return_date IS NULL THEN 1 END) as borrows,
                COUNT(CASE WHEN status = 'Returned' AND return_date IS NOT NULL THEN 1 END) as returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY MONTH(borrow_date)
                ORDER BY MONTH(borrow_date)";
    }
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get genre popularity
if ($type === 'genrePopularity') {
    $sql = "SELECT g.genre_name as label, COUNT(bt.borrow_id) as value
            FROM genre g
            INNER JOIN book b ON g.genre_id = b.Genre_genre_id
            INNER JOIN book_copy bc ON b.book_id = bc.Book_book_id
            INNER JOIN book_transaction bt ON bc.copy_id = bt.Book_Copy_copy_id
            WHERE DATE(bt.borrow_date) >= '$startDate'
              AND DATE(bt.borrow_date) <= '$endDate'
            GROUP BY g.genre_id, g.genre_name
            ORDER BY value DESC
            LIMIT 10";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get top borrowers
if ($type === 'topBorrowers') {
    $sql = "SELECT m.user_id,
                   CASE WHEN TRIM(CONCAT(u.first_name, ' ', u.last_name)) = '' THEN m.user_id ELSE TRIM(CONCAT(u.first_name, ' ', u.last_name)) END AS borrower_name,
                   COUNT(bt.borrow_id) as borrow_count
            FROM member m
            INNER JOIN book_transaction bt ON m.user_id = bt.Member_user_id
            LEFT JOIN user u ON m.user_id = u.user_id
            WHERE DATE(bt.borrow_date) >= '$startDate'
              AND DATE(bt.borrow_date) <= '$endDate'
            GROUP BY m.user_id
            ORDER BY borrow_count DESC
            LIMIT 5";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get materials added by genre
if ($type === 'materialsAdded') {
    $sql = "SELECT g.genre_name as label, COUNT(b.book_id) as value
            FROM genre g
            LEFT JOIN book b ON g.genre_id = b.Genre_genre_id
            WHERE b.publication_date IS NOT NULL
              AND b.publication_date <> ''
              AND CAST(b.publication_date AS UNSIGNED) BETWEEN $startYear AND $endYear
            GROUP BY g.genre_id, g.genre_name
            ORDER BY value DESC
            LIMIT 10";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get fine summary
if ($type === 'fineSummary') {
    if ($period === 'week') {
        $sql = "SELECT DATE(bt.borrow_date) as label, COALESCE(SUM(f.amount_paid), 0) as amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) >= '$startDate'
                AND DATE(bt.borrow_date) <= '$endDate'
                GROUP BY DATE(bt.borrow_date)
                ORDER BY bt.borrow_date ASC";
    } elseif ($period === 'month') {
        $sql = "SELECT CONCAT('Week ', WEEK(bt.borrow_date) - WEEK(DATE_SUB(bt.borrow_date, INTERVAL DAYOFMONTH(bt.borrow_date)-1 DAY)) + 1) as label,
                COALESCE(SUM(f.amount_paid), 0) as amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) >= '$startDate'
                AND DATE(bt.borrow_date) <= '$endDate'
                GROUP BY WEEK(bt.borrow_date)
                ORDER BY bt.borrow_date ASC";
    } else {
        $sql = "SELECT DATE_FORMAT(bt.borrow_date, '%b') as label, COALESCE(SUM(f.amount_paid), 0) as amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) >= '$startDate'
                AND DATE(bt.borrow_date) <= '$endDate'
                GROUP BY MONTH(bt.borrow_date)
                ORDER BY MONTH(bt.borrow_date)";
    }
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get active borrow details
if ($type === 'activeBorrowDetails') {
    $sql = "SELECT bt.borrow_id, b.title,
                   CASE WHEN TRIM(CONCAT(u.first_name, ' ', u.last_name)) = '' THEN m.user_id ELSE TRIM(CONCAT(u.first_name, ' ', u.last_name)) END AS borrower_name,
                   DATE_FORMAT(bt.borrow_date, '%b %d, %Y') as borrow_date,
                   DATE_FORMAT(bt.due_date, '%b %d, %Y') as due_date,
                   b.image_url
            FROM book_transaction bt
            INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
            INNER JOIN book b ON bc.Book_book_id = b.book_id
            INNER JOIN member m ON bt.Member_user_id = m.user_id
            LEFT JOIN user u ON m.user_id = u.user_id
            WHERE bt.status = 'Borrowed'
            ORDER BY bt.due_date ASC
            LIMIT 10";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get overdue details
if ($type === 'overdueDetails') {
    $sql = "SELECT bt.borrow_id, b.title,
                   CASE WHEN TRIM(CONCAT(u.first_name, ' ', u.last_name)) = '' THEN m.user_id ELSE TRIM(CONCAT(u.first_name, ' ', u.last_name)) END AS borrower_name,
                   DATEDIFF(CURDATE(), bt.due_date) as days_overdue,
                   (DATEDIFF(CURDATE(), bt.due_date) * COALESCE(f.fine_rate, 0)) as fine_amount
            FROM book_transaction bt
            INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
            INNER JOIN book b ON bc.Book_book_id = b.book_id
            INNER JOIN member m ON bt.Member_user_id = m.user_id
            LEFT JOIN user u ON m.user_id = u.user_id
            LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
            WHERE bt.status = 'Overdue'
            AND bt.due_date < CURDATE()
            ORDER BY bt.due_date ASC
            LIMIT 10";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;

}

//Fine summary

if ($type === 'fineSummary') {
    $collectedSql = "SELECT COALESCE (SUM(f.amount_paid), 0) as collected FROM fine f";
    $collectedResult = mysqli_query ($conn, $collectedSql);
    $collected = intval(mysqli_fetch_assoc($collectedResult)['collected'] ?? 0);
    $pendingSql = "SELECT COALESCE (SUM(f.balance), 0) as pending FROM fine f WHERE f.balance > 0";
    $pendingResult = mysqli_query ($conn, $pendingSql);
    $pending = intval(mysqli_fetch_assoc($pendingResult)['pending'] ?? 0);

    $totalSql = "SELECT COALESCE (SUM(f.total_amount_accrued), 0) as total FROM fine f";
    $totalResult = mysqli_query ($conn, $totalSql);
    $total = intval(mysqli_fetch_assoc($totalResult)['total'] ?? 0);
    echo json_encode([
        'collected' => $collected,
        'pending' => $pending,
        'total' => $total
    ]);
    exit;
}

// Default response
echo json_encode(['error' => 'Invalid request type']);
?>
