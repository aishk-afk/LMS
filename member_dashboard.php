<?php
include 'db_config.php';

$userId = isset($_GET['user_id']) ? $conn->real_escape_string($_GET['user_id']) : 'ST001';

$userQuery = "SELECT u.first_name, u.last_name, u.user_type, m.Department, m.Course, m.Section, m.Member_TYPE 
              FROM `user` u
              LEFT JOIN member m ON u.user_id = m.user_id
              WHERE u.user_id = '$userId'
              LIMIT 1";
$userResult = $conn->query($userQuery);
$user = $userResult && $userResult->num_rows > 0 ? $userResult->fetch_assoc() : null;

$fullName = $user ? trim($user['first_name'] . ' ' . $user['last_name']) : 'Library Member';
$memberType = $user['Member_TYPE'] ?? 'Student';
$memberDepartment = $user['Department'] ?? '';
$memberCourse = $user['Course'] ?? '';

$borrowedCountResult = $conn->query("SELECT COUNT(*) AS count FROM book_transaction WHERE Member_user_id = '$userId' AND status = 'Borrowed'");
$borrowedCount = $borrowedCountResult ? intval($borrowedCountResult->fetch_assoc()['count']) : 0;

$outstandingFineResult = $conn->query("SELECT COALESCE(SUM(f.balance), 0) AS total FROM fine f
    INNER JOIN book_transaction bt ON f.Book_Transaction_borrow_id = bt.borrow_id
    WHERE bt.Member_user_id = '$userId' AND f.balance > 0");
$outstandingFine = $outstandingFineResult ? intval($outstandingFineResult->fetch_assoc()['total']) : 0;

$activeBorrowQuery = "SELECT bt.borrow_id, b.title, b.image_url, b.publisher_name, bt.borrow_date, bt.due_date, bt.status, COALESCE(f.balance,0) AS balance
    FROM book_transaction bt
    INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
    INNER JOIN book b ON bc.Book_book_id = b.book_id
    LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
    WHERE bt.Member_user_id = '$userId' AND bt.status = 'Borrowed'
    ORDER BY bt.due_date ASC LIMIT 1";
$activeBorrowResult = $conn->query($activeBorrowQuery);
$activeBorrow = $activeBorrowResult && $activeBorrowResult->num_rows > 0 ? $activeBorrowResult->fetch_assoc() : null;

$historyQuery = "SELECT bt.borrow_id, b.title, b.publisher_name, bt.borrow_date, bt.due_date, bt.return_date, bt.status, COALESCE(f.balance,0) AS balance
    FROM book_transaction bt
    INNER JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
    INNER JOIN book b ON bc.Book_book_id = b.book_id
    LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
    WHERE bt.Member_user_id = '$userId'
    ORDER BY bt.borrow_date DESC
    LIMIT 10";
$historyResult = $conn->query($historyQuery);
$historyRecords = [];
if ($historyResult && $historyResult->num_rows > 0) {
    while ($row = $historyResult->fetch_assoc()) {
        $historyRecords[] = $row;
    }
}

function formatDate($dateString) {
    if (!$dateString) {
        return '—';
    }
    $date = new DateTime($dateString);
    return $date->format('M j, Y');
}

function overdueLabel($dueDate, $status) {
    $due = new DateTime($dueDate);
    $today = new DateTime();
    if ($status === 'Overdue' || $today > $due) {
        $interval = $today->diff($due);
        $days = $interval->days;
        return "Overdue {$days}d · " . $due->format('M j');
    }
    return "Due " . $due->format('M j');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - Library System</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/member.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" alt="Logo" class="logo-icon">
                <h2 class="brand-name">Learning Library Management Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active"><a href="#"><i class="fi fi-rr-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="member_catalog.html"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item"><a href="member_account.html"><i class="fi fi-rr-user"></i> Account</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <strong><?= htmlspecialchars($fullName) ?></strong><br><small><?= htmlspecialchars($memberType) ?></small>
                </div>
                <a href="index.html" class="logout-link"><i class="fi fi-rr-exit"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="welcome-section">
                <h1>Welcome back, <?= htmlspecialchars($fullName) ?></h1>
                <p>Here is the status of your library account.</p>
            </header>

            <div class="member-stats-grid">
                <div class="m-stat-card">
                    <div class="m-icon-box bg-blue"><i class="fi fi-rr-book-alt"></i></div>
                    <div class="m-stat-info"><span>Borrowed Items</span><h3><?= $borrowedCount ?></h3></div>
                </div>
                <div class="m-stat-card">
                    <div class="m-icon-box bg-orange"><i class="fi fi-rr-clock"></i></div>
                    <div class="m-stat-info"><span>On Waitlist</span><h3>0</h3></div>
                </div>
                <div class="m-stat-card">
                    <div class="m-icon-box bg-red"><i class="fi fi-rr-credit-card"></i></div>
                    <div class="m-stat-info"><span>Outstanding Fines</span><h3 class="text-red">₱<?= number_format($outstandingFine, 0) ?></h3></div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fi fi-rr-book"></i> Currently Borrowed</h3>
                    <span class="badge-count"><?= $borrowedCount ?> Active</span>
                </div>
                <?php if ($activeBorrow): ?>
                    <div class="borrowed-item-box">
                        <img src="<?= htmlspecialchars($activeBorrow['image_url'] ?: 'book_placeholder.jpg') ?>" alt="Book" class="book-cover-sm" onerror="this.src='book_placeholder.jpg'">
                        <div class="item-details">
                            <h4><?= htmlspecialchars($activeBorrow['title']) ?></h4>
                            <p><?= htmlspecialchars($activeBorrow['publisher_name'] ?: 'Publisher not set') ?></p>
                            <div class="item-tags">
                                <span class="tag <?= $activeBorrow['status'] === 'Overdue' ? 'tag-red' : 'tag-outline' ?>"><i class="fi fi-rr-exclamation"></i> <?= htmlspecialchars(overdueLabel($activeBorrow['due_date'], $activeBorrow['status'])) ?></span>
                                <span class="tag tag-outline">Fine: ₱<?= number_format($activeBorrow['balance'], 0) ?></span>
                                <span class="tag tag-outline">Home-Use</span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="empty-state-card">
                        <p>You have no books currently borrowed.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="history-section">
                <div class="card-header">
                    <h3><i class="fi fi-rr-time-past"></i> Borrow History</h3>
                    <span class="badge-count"><?= count($historyRecords) ?> Records</span>
                </div>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Material Type</th>
                            <th>Borrowed</th>
                            <th>Due</th>
                            <th>Returned</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($historyRecords) === 0): ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No borrow history available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historyRecords as $record): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($record['title']) ?></strong><br><small><?= htmlspecialchars($record['publisher_name'] ?: 'Publisher not set') ?></small></td>
                                    <td><span class="status-tag status-reference">Home-Use</span></td>
                                    <td><?= htmlspecialchars(formatDate($record['borrow_date'])) ?></td>
                                    <td><?= htmlspecialchars(formatDate($record['due_date'])) ?></td>
                                    <td><?= htmlspecialchars(formatDate($record['return_date'])) ?></td>
                                    <td><?= $record['balance'] > 0 ? '<span class="text-red">₱' . number_format($record['balance'], 0) . '</span>' : '<span class="status-none">None</span>' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="dashboard-section">
                <div class="card-header">
                    <h3><i class="fi fi-rr-clock"></i> My Waitlist</h3>
                    <span class="badge-orange">0 Books</span>
                </div>
                <div class="empty-state-card">
                    <p>You are not on any waitlists.</p>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="card-header">
                    <h3><i class="fi fi-rr-bell"></i> Availability Notifications</h3>
                    <span class="badge-green">0 New</span>
                </div>
                <div class="empty-state-card">
                    <p>No new availability notifications.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
