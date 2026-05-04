<?php
header('Content-Type: application/json');
include 'db_config.php';

$type      = $_GET['type']      ?? 'stats';
$startDate = $_GET['startDate'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate   = $_GET['endDate']   ?? date('Y-m-d');
$period    = $_GET['period']    ?? 'week';

// Sanitize dates
$startDate = date('Y-m-d', strtotime($startDate));
$endDate   = date('Y-m-d', strtotime($endDate));

// ── Helper: run a query and return all rows as an array ──────────────────────
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

// ── Shared WHERE clause for "currently active" borrows ───────────────────────
// Catches: status='Active', status='Overdue', AND legacy rows where status is
// NULL or empty string but no return_date has been recorded yet.
define('ACTIVE_WHERE', "
    (
        bt.status IN ('Active', 'Overdue')
        OR (bt.status IS NULL  AND bt.return_date IS NULL)
        OR (bt.status = ''     AND bt.return_date IS NULL)
    )
");

// Same clause but table alias is `book_transaction` not `bt`
define('ACTIVE_WHERE_BT', "
    (
        status IN ('Active', 'Overdue')
        OR (status IS NULL  AND return_date IS NULL)
        OR (status = ''     AND return_date IS NULL)
    )
");

// ── Active Borrows count ─────────────────────────────────────────────────────
if ($type === 'activeBorrows') {
    $sql = "SELECT COUNT(*) AS count
            FROM book_transaction
            WHERE " . ACTIVE_WHERE_BT;
    $result = mysqli_query($conn, $sql);
    $data   = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($data['count'] ?? 0)]);
    exit;
}

// ── Overdue Items count ──────────────────────────────────────────────────────
if ($type === 'overdueItems') {
    $sql = "SELECT COUNT(*) AS count
            FROM book_transaction
            WHERE " . ACTIVE_WHERE_BT . "
              AND due_date < CURDATE()
              AND due_date IS NOT NULL";
    $result = mysqli_query($conn, $sql);
    $data   = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($data['count'] ?? 0)]);
    exit;
}

// ── Total Books ──────────────────────────────────────────────────────────────
if ($type === 'totalBooks') {
    $sql    = "SELECT COUNT(DISTINCT book_id) AS count FROM book";
    $result = mysqli_query($conn, $sql);
    $data   = mysqli_fetch_assoc($result);
    echo json_encode(['count' => intval($data['count'] ?? 0)]);
    exit;
}

// ── Fines Collected in period ────────────────────────────────────────────────
if ($type === 'finesCollected') {
    $sql = "SELECT COALESCE(SUM(f.amount_paid), 0) AS total
            FROM fine f
            INNER JOIN book_transaction bt ON f.Book_Transaction_borrow_id = bt.borrow_id
            WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
              AND f.amount_paid > 0";
    $result = mysqli_query($conn, $sql);
    $data   = mysqli_fetch_assoc($result);
    echo json_encode(['total' => floatval($data['total'] ?? 0)]);
    exit;
}

// ── Borrowing Trends ─────────────────────────────────────────────────────────
if ($type === 'borrowingTrends') {
    $activeClause = "status IN ('Active','Overdue')
                     OR (status IS NULL AND return_date IS NULL)
                     OR (status = ''   AND return_date IS NULL)";

    if ($period === 'week') {
        $sql = "SELECT DATE(borrow_date) AS date,
                       COUNT(CASE WHEN ($activeClause) THEN 1 END) AS borrows,
                       COUNT(CASE WHEN status = 'Returned'         THEN 1 END) AS returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(borrow_date)
                ORDER BY borrow_date ASC";

    } elseif ($period === 'month') {
        $sql = "SELECT CONCAT('Week ', WEEK(borrow_date) - WEEK(DATE_SUB(borrow_date, INTERVAL DAYOFMONTH(borrow_date)-1 DAY)) + 1) AS label,
                       COUNT(CASE WHEN ($activeClause) THEN 1 END) AS borrows,
                       COUNT(CASE WHEN status = 'Returned'         THEN 1 END) AS returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY WEEK(borrow_date)
                ORDER BY borrow_date ASC";

    } else {
        $sql = "SELECT DATE_FORMAT(borrow_date, '%b') AS label,
                       COUNT(CASE WHEN ($activeClause) THEN 1 END) AS borrows,
                       COUNT(CASE WHEN status = 'Returned'         THEN 1 END) AS returns
                FROM book_transaction
                WHERE borrow_date BETWEEN '$startDate' AND '$endDate'
                GROUP BY MONTH(borrow_date)
                ORDER BY MONTH(borrow_date)";
    }

    echo json_encode(executeQuery($conn, $sql));
    exit;
}

// ── Genre Popularity ─────────────────────────────────────────────────────────
if ($type === 'genrePopularity') {
    $sql = "SELECT g.genre_name AS label, COUNT(bt.borrow_id) AS value
            FROM genre g
            INNER JOIN book b        ON g.genre_id  = b.Genre_genre_id
            INNER JOIN book_copy bc  ON b.book_id   = bc.Book_book_id
            INNER JOIN book_transaction bt ON bc.copy_id = bt.Book_Copy_copy_id
            WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
            GROUP BY g.genre_id, g.genre_name
            ORDER BY value DESC
            LIMIT 10";
    echo json_encode(executeQuery($conn, $sql));
    exit;
}

// ── Top Borrowers ────────────────────────────────────────────────────────────
if ($type === 'topBorrowers') {
    $sql = "SELECT m.user_id,
                   TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS borrower_name,
                   m.Course AS course,
                   COUNT(bt.borrow_id) AS borrow_count
            FROM member m
            INNER JOIN book_transaction bt ON m.user_id = bt.Member_user_id
            LEFT JOIN  user u              ON m.user_id = u.user_id
            WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
            GROUP BY m.user_id, u.first_name, u.last_name, m.Course
            ORDER BY borrow_count DESC
            LIMIT 5";
    echo json_encode(executeQuery($conn, $sql));
    exit;
}

// ── Materials Added by Genre (all-time, no date filter) ──────────────────────
if ($type === 'materialsAdded') {
    $sql = "SELECT g.genre_name AS label, COUNT(b.book_id) AS value
            FROM genre g
            INNER JOIN book b ON g.genre_id = b.Genre_genre_id
            GROUP BY g.genre_id, g.genre_name
            ORDER BY value DESC";
    echo json_encode(executeQuery($conn, $sql));
    exit;
}

// ── Fine Summary ─────────────────────────────────────────────────────────────
// • No dates passed  → summary card totals (collected / pending / total)
// • Dates passed     → time-series data for the fines chart
if ($type === 'fineSummary') {

    if (empty($_GET['startDate']) || empty($_GET['endDate'])) {
        // Summary card
        $cRes = mysqli_query($conn, "SELECT COALESCE(SUM(amount_paid), 0)        AS collected FROM fine");
        $pRes = mysqli_query($conn, "SELECT COALESCE(SUM(balance), 0)            AS pending   FROM fine WHERE balance > 0");
        $tRes = mysqli_query($conn, "SELECT COALESCE(SUM(total_amount_accrued),0) AS total     FROM fine");

        echo json_encode([
            'collected' => floatval(mysqli_fetch_assoc($cRes)['collected'] ?? 0),
            'pending'   => floatval(mysqli_fetch_assoc($pRes)['pending']   ?? 0),
            'total'     => floatval(mysqli_fetch_assoc($tRes)['total']     ?? 0),
        ]);
        exit;
    }

    // Time-series for chart
    if ($period === 'week') {
        $sql = "SELECT DATE(bt.borrow_date) AS label,
                       COALESCE(SUM(f.amount_paid), 0) AS amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
                GROUP BY DATE(bt.borrow_date)
                ORDER BY bt.borrow_date ASC";

    } elseif ($period === 'month') {
        $sql = "SELECT CONCAT('Week ', WEEK(bt.borrow_date) - WEEK(DATE_SUB(bt.borrow_date, INTERVAL DAYOFMONTH(bt.borrow_date)-1 DAY)) + 1) AS label,
                       COALESCE(SUM(f.amount_paid), 0) AS amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
                GROUP BY WEEK(bt.borrow_date)
                ORDER BY bt.borrow_date ASC";

    } else {
        $sql = "SELECT DATE_FORMAT(bt.borrow_date, '%b') AS label,
                       COALESCE(SUM(f.amount_paid), 0) AS amount
                FROM book_transaction bt
                LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                WHERE DATE(bt.borrow_date) BETWEEN '$startDate' AND '$endDate'
                GROUP BY MONTH(bt.borrow_date)
                ORDER BY MONTH(bt.borrow_date)";
    }

    echo json_encode(executeQuery($conn, $sql));
    exit;
}

// ── Active Borrow Details (table in admin dashboard) ─────────────────────────
if ($type === 'activeBorrowDetails') {
    $sql = "SELECT bt.borrow_id,
                   b.title,
                   TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS borrower_name,
                   DATE_FORMAT(bt.borrow_date, '%b %d, %Y') AS borrow_date,
                   DATE_FORMAT(bt.due_date,    '%b %d, %Y') AS due_date,
                   b.image_url
            FROM book_transaction bt
            INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
            INNER JOIN book b       ON bc.Book_book_id      = b.book_id
            INNER JOIN member m     ON bt.Member_user_id    = m.user_id
            LEFT JOIN  user u       ON m.user_id            = u.user_id
            WHERE " . ACTIVE_WHERE . "
            ORDER BY bt.due_date ASC
            LIMIT 50";
    echo json_encode(executeQuery($conn, $sql));
    exit;
}

// ── Overdue Details (table in admin dashboard) ───────────────────────────────
if ($type === 'overdueDetails') {
    $sql = "SELECT bt.borrow_id,
                   b.title,
                   TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS borrower_name,
                   DATEDIFF(CURDATE(), bt.due_date)              AS days_overdue,
                   COALESCE(f.total_amount_accrued, 0)           AS fine_amount
            FROM book_transaction bt
            INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
            INNER JOIN book b       ON bc.Book_book_id      = b.book_id
            INNER JOIN member m     ON bt.Member_user_id    = m.user_id
            LEFT JOIN  user u       ON m.user_id            = u.user_id
            LEFT JOIN  fine f       ON bt.borrow_id         = f.Book_Transaction_borrow_id
            WHERE " . ACTIVE_WHERE . "
              AND bt.due_date < CURDATE()
              AND bt.due_date IS NOT NULL
            ORDER BY bt.due_date ASC
            LIMIT 50";
    echo json_encode(executeQuery($conn, $sql));
    exit;
}

// ── Fallback ─────────────────────────────────────────────────────────────────
echo json_encode(['error' => 'Invalid request type: ' . htmlspecialchars($type)]);
?>