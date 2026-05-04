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
            WHERE status = 'Active'";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($data['count'] ?? 0)]);
    exit;
}

// Get overdue items count
// Get overdue items count — replace the existing overdueItems block
if ($type === 'overdueItems') {
    $sql = "SELECT COUNT(*) as count FROM book_transaction 
            WHERE status = 'Active' AND due_date < CURDATE()";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($data['count'] ?? 0)]);
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
        $sql = "SELECT DATE(borrow_date) as date, 
                COUNT(CASE WHEN status IN ('Active','Overdue') THEN 1 END) as borrows,
                COUNT(CASE WHEN status = 'Returned' THEN 1 END) as returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(borrow_date)
                ORDER BY borrow_date ASC";
    } elseif ($period === 'month') {
        $sql = "SELECT CONCAT('Week ', WEEK(borrow_date) - WEEK(DATE_SUB(borrow_date, INTERVAL DAYOFMONTH(borrow_date)-1 DAY)) + 1) as label,
                COUNT(CASE WHEN status IN ('Active','Overdue') THEN 1 END) as borrows,
                COUNT(CASE WHEN status = 'Returned' THEN 1 END) as returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY WEEK(borrow_date)
                ORDER BY borrow_date ASC";
    } else {
        $sql = "SELECT DATE_FORMAT(borrow_date, '%b') as label,
                COUNT(CASE WHEN status IN ('Active','Overdue') THEN 1 END) as borrows,
                COUNT(CASE WHEN status = 'Returned' THEN 1 END) as returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY MONTH(borrow_date)
                ORDER BY MONTH(borrow_date)";
    }
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get genre popularity (based on borrow frequency)
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
                   TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS borrower_name,
                   m.Course AS course,
                   COUNT(bt.borrow_id) as borrow_count
            FROM member m
            INNER JOIN book_transaction bt ON m.user_id = bt.Member_user_id
            LEFT JOIN user u ON m.user_id = u.user_id
            WHERE DATE(bt.borrow_date) >= '$startDate'
              AND DATE(bt.borrow_date) <= '$endDate'
            GROUP BY m.user_id, u.first_name, u.last_name, m.Course
            ORDER BY borrow_count DESC
            LIMIT 5";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// ─── FIX: materialsAdded — NOT date-filtered, counts all books by genre ───────
if ($type === 'materialsAdded') {
    $sql = "SELECT g.genre_name AS label, COUNT(b.book_id) AS value
            FROM genre g
            INNER JOIN book b ON g.genre_id = b.Genre_genre_id
            GROUP BY g.genre_id, g.genre_name
            ORDER BY value DESC";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// ─── FIX: fineSummary — TWO separate behaviors in one type, fixed with subtype
// When called for the chart (period-based), returns time-series data.
// When called for the summary card (no dates), returns collected/pending/total.
if ($type === 'fineSummary') {

    // If no valid startDate/endDate passed, return the summary card totals
    if (empty($_GET['startDate']) || empty($_GET['endDate'])) {
        $collectedResult = mysqli_query($conn, "SELECT COALESCE(SUM(amount_paid), 0) AS collected FROM fine");
        $collected = intval(mysqli_fetch_assoc($collectedResult)['collected'] ?? 0);

        $pendingResult = mysqli_query($conn, "SELECT COALESCE(SUM(balance), 0) AS pending FROM fine WHERE balance > 0");
        $pending = intval(mysqli_fetch_assoc($pendingResult)['pending'] ?? 0);

        $totalResult = mysqli_query($conn, "SELECT COALESCE(SUM(total_amount_accrued), 0) AS total FROM fine");
        $total = intval(mysqli_fetch_assoc($totalResult)['total'] ?? 0);

        echo json_encode([
            'collected' => $collected,
            'pending'   => $pending,
            'total'     => $total
        ]);
        exit;
    }

    // Otherwise return time-series data for the fines chart
    if ($period === 'week') {
        $sql = "SELECT DATE(bt.borrow_date) as label, COALESCE(SUM(f.amount_paid), 0) as amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(bt.borrow_date)
                ORDER BY bt.borrow_date ASC";
    } elseif ($period === 'month') {
        $sql = "SELECT CONCAT('Week ', WEEK(bt.borrow_date) - WEEK(DATE_SUB(bt.borrow_date, INTERVAL DAYOFMONTH(bt.borrow_date)-1 DAY)) + 1) as label,
                COALESCE(SUM(f.amount_paid), 0) as amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
                GROUP BY WEEK(bt.borrow_date)
                ORDER BY bt.borrow_date ASC";
    } else {
        $sql = "SELECT DATE_FORMAT(bt.borrow_date, '%b') as label, COALESCE(SUM(f.amount_paid), 0) as amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
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
                   TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS borrower_name,
                   DATE_FORMAT(bt.borrow_date, '%b %d, %Y') as borrow_date,
                   DATE_FORMAT(bt.due_date, '%b %d, %Y') as due_date,
                   b.image_url
            FROM book_transaction bt
            INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
            INNER JOIN book b ON bc.Book_book_id = b.book_id
            INNER JOIN member m ON bt.Member_user_id = m.user_id
            LEFT JOIN user u ON m.user_id = u.user_id
            WHERE bt.status = 'Active'
            ORDER BY bt.due_date ASC
            LIMIT 10";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Get overdue details
if ($type === 'overdueDetails') {
    $sql = "SELECT bt.borrow_id, b.title,
                   TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS borrower_name,
                   DATEDIFF(CURDATE(), bt.due_date) as days_overdue,
                   COALESCE(f.total_amount_accrued, 0) as fine_amount
            FROM book_transaction bt
            INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
            INNER JOIN book b ON bc.Book_book_id = b.book_id
            INNER JOIN member m ON bt.Member_user_id = m.user_id
            LEFT JOIN user u ON m.user_id = u.user_id
            LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
            WHERE bt.status = 'Active' AND bt.due_date < CURATE()
            ORDER BY bt.due_date ASC
            LIMIT 10";
    $data = executeQuery($conn, $sql);
    echo json_encode($data);
    exit;
}

// Default response
echo json_encode(['error' => 'Invalid request type: ' . $type]);
?>