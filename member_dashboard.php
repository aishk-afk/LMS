<?php
session_start();
include 'db_config.php';

// Get user ID from session
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Fetch user name
$user_query = $conn->prepare("SELECT first_name, last_name FROM user WHERE user_id = ?");
$user_query->bind_param("s", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_name = htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
$user_query->close();

// Fetch stats
$borrowed_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM book_transaction WHERE Member_user_id = ? AND status IN ('Active', 'Overdue')");
$borrowed_count_stmt->bind_param("s", $user_id);
$borrowed_count_stmt->execute();
$borrowed_count = $borrowed_count_stmt->get_result()->fetch_assoc()['count'];
$borrowed_count_stmt->close();

$waitlist_stmt = $conn->prepare("SELECT COUNT(*) as count FROM waitlist WHERE Member_user_id = ?");
$waitlist_stmt->bind_param("s", $user_id);
$waitlist_stmt->execute();
$waitlist_count = $waitlist_stmt->get_result()->fetch_assoc()['count'];
$waitlist_stmt->close();

$fine_stmt = $conn->prepare("SELECT SUM(f.balance) as total_fines FROM fine f JOIN book_transaction bt ON f.Book_Transaction_borrow_id = bt.borrow_id WHERE bt.Member_user_id = ?");
$fine_stmt->bind_param("s", $user_id);
$fine_stmt->execute();
$total_fines = $fine_stmt->get_result()->fetch_assoc()['total_fines'] ?? 0;
$fine_stmt->close();

// Fetch currently borrowed books
// Fix the borrowed SQL to exclude waitlisted books
$borrowed_sql = "SELECT b.title,
                         CONCAT(a.first_name, ' ', a.last_name) AS author_name,
                         bt.borrow_date,
                         bt.due_date,
                         bt.status,
                         f.total_amount_accrued,
                         b.image_url,
                         g.genre_name
                 FROM book_transaction bt
                 JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
                 JOIN book b ON bc.Book_book_id = b.book_id
                 LEFT JOIN book_author_assignment baa ON baa.Book_book_id = b.book_id
                 LEFT JOIN author a ON baa.Author_author_id = a.author_id
                 LEFT JOIN fine f ON f.Book_Transaction_borrow_id = bt.borrow_id
                 LEFT JOIN genre g ON b.Genre_genre_id = g.genre_id
                 WHERE bt.Member_user_id = ? 
                 AND bt.status IN ('Active', 'Overdue')
                 AND b.book_id NOT IN (
                     SELECT Book_book_id FROM waitlist WHERE Member_user_id = ?
                 )
                 ORDER BY bt.due_date ASC";
$borrowed_stmt = $conn->prepare($borrowed_sql);
$borrowed_stmt->bind_param("ss", $user_id, $user_id); // two params now
$borrowed_stmt->execute();
$borrowed_result = $borrowed_stmt->get_result();
$borrowed_count = $borrowed_result->num_rows;    // get count from the result directly

// Fetch borrow history
$history_sql = "SELECT b.title,
                        CONCAT(a.first_name, ' ', a.last_name) AS author_name,
                        bt.borrow_date,
                        bt.due_date,
                        bt.return_date,
                        f.total_amount_accrued,
                        g.genre_name
                FROM book_transaction bt
                JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
                JOIN book b ON bc.Book_book_id = b.book_id
                LEFT JOIN book_author_assignment baa ON baa.Book_book_id = b.book_id
                LEFT JOIN author a ON baa.Author_author_id = a.author_id
                LEFT JOIN fine f ON f.Book_Transaction_borrow_id = bt.borrow_id
                LEFT JOIN genre g ON b.Genre_genre_id = g.genre_id
                WHERE bt.Member_user_id = ? AND bt.status = 'Returned'
                ORDER BY bt.return_date DESC LIMIT 10";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param("s", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$history_count = $history_result->num_rows;

// Fetch waitlist with position
$waitlist_sql = "SELECT b.title,
                        CONCAT(a.first_name, ' ', a.last_name) AS author_name,
                        w.request_date,
                        (SELECT COUNT(*) FROM waitlist w2 WHERE w2.Book_book_id = w.Book_book_id AND w2.request_date <= w.request_date) AS position_in_queue
                 FROM waitlist w
                 JOIN book b ON w.Book_book_id = b.book_id
                 LEFT JOIN book_author_assignment baa ON baa.Book_book_id = b.book_id
                 LEFT JOIN author a ON baa.Author_author_id = a.author_id
                 WHERE w.Member_user_id = ?
                 ORDER BY w.request_date ASC";
$waitlist_stmt = $conn->prepare($waitlist_sql);
$waitlist_stmt->bind_param("s", $user_id);
$waitlist_stmt->execute();
$waitlist_result = $waitlist_stmt->get_result();
$waitlist_count = $waitlist_result->num_rows;

// Notifications: assuming 0 for now
$notification_count = 0;

// Close statements
$borrowed_stmt->close();
$history_stmt->close();
$waitlist_stmt->close();
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
                    <li class="nav-item"><a href="member_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item"><a href="member_account.php"><i class="fi fi-rr-user"></i> Account</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <strong>
                        <?php
                        // We use 'user_name' and 'last_name' from your login.php
                        $first = $_SESSION['user_name'] ?? 'User';
                        $last = $_SESSION['last_name'] ?? '';
                        echo htmlspecialchars($first . ' ' . $last);
                        ?>
                    </strong>
                    <br>
                    <small>
                        <?php
                        // ucfirst() turns "student" into "Student" or "member" into "Member"
                        $role = $_SESSION['user_role'] ?? 'Member';
                        echo htmlspecialchars(ucfirst($role));
                        ?>
                    </small>
                </div>
                <a href="index.php" class="logout-link">
                    <i class="fi fi-rr-exit"></i> Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="welcome-section">
                <h1>Welcome back, <?php echo $user_name; ?></h1>
                <p>Here is the status of your library account.</p>
            </header>

            <div class="member-stats-grid">
                <div class="m-stat-card">
                    <div class="m-icon-box bg-blue"><i class="fi fi-rr-book-alt"></i></div>
                    <div class="m-stat-info"><span>Borrowed Items</span>
                        <h3><?php echo $borrowed_count; ?></h3>
                    </div>
                </div>
                <div class="m-stat-card">
                    <div class="m-icon-box bg-orange"><i class="fi fi-rr-clock"></i></div>
                    <div class="m-stat-info"><span>On Waitlist</span>
                        <h3><?php echo $waitlist_count; ?></h3>
                    </div>
                </div>
                <div class="m-stat-card">
                    <div class="m-icon-box bg-red"><i class="fi fi-rr-credit-card"></i></div>
                    <div class="m-stat-info"><span>Outstanding Fines</span>
                        <h3 class="text-red">₱<?php echo number_format($total_fines, 2); ?></h3>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fi fi-rr-book"></i> Currently Borrowed</h3>
                    <span class="badge-count"><?php echo $borrowed_count; ?> Active</span>
                </div>
                <?php if ($borrowed_result->num_rows > 0): ?>
                    <?php while ($book = $borrowed_result->fetch_assoc()):
                        $isOverdue = strtotime($book['due_date']) < time();
                        $days_overdue = $isOverdue ? (int) ((time() - strtotime($book['due_date'])) / 86400) : 0;
                        ?>
                        <div class="borrowed-item-box"
                            style="<?php echo $isOverdue ? 'background:#fff5f5; border-left:3px solid #ef4444;' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($book['image_url'] ?? 'book3.jpg'); ?>" alt="Book"
                                class="book-cover-sm">
                            <div class="item-details">
                                <h4>
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </h4>
                                <p style="color:#64748b; font-size:13px;">
                                    <?php echo htmlspecialchars($book['author_name'] ?? 'Unknown Author'); ?>
                                </p>
                                <div class="item-tags" style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                                    <?php if ($isOverdue): ?>
                                        <span class="tag tag-red">
                                            <i class="fi fi-rr-exclamation"></i> Overdue
                                            <?php echo $days_overdue; ?>d ·
                                            <?php echo date('M d', strtotime($book['due_date'])); ?>
                                        </span>
                                        <span class="tag tag-red">Fine: ₱
                                            <?php echo number_format($book['total_amount_accrued'] ?? 0, 2); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="tag tag-outline" style="color:#16a34a; border-color:#16a34a;">
                                            ✓ Due
                                            <?php echo date('M d', strtotime($book['due_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="tag tag-outline">
                                        <?php echo htmlspecialchars($book['genre_name'] ?? 'General'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state-card">
                        <p>You have no borrowed books.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="history-section">
                <div class="card-header">
                    <h3><i class="fi fi-rr-time-past"></i> Borrow History</h3>
                    <span class="badge-count"><?php echo $history_count; ?> Records</span>
                </div>
                <?php if ($history_result->num_rows > 0): ?>
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
                            <?php while ($row = $history_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['title']); ?></strong><br><small><?php echo htmlspecialchars($row['author_name'] ?? 'Unknown Author'); ?></small>
                                    </td>
                                    <td><span class="status-tag status-reference">Genre:
                                            <?php echo htmlspecialchars($row['genre_name'] ?? 'General'); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['return_date'])); ?></td>
                                    <td><?php echo ($row['total_amount_accrued'] > 0) ? '<span class="text-red">₱' . number_format($row['total_amount_accrued'], 2) . '</span>' : '<span class="status-none">None</span>'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state-card">
                        <p>No borrow history available.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-section">
                <div class="card-header">
                    <h3><i class="fi fi-rr-clock"></i> My Waitlist</h3>
                    <span class="badge-orange"><?php echo $waitlist_count; ?> Books</span>
                </div>
                <?php if ($waitlist_count > 0): ?>
                    <ul>
                        <?php while ($item = $waitlist_result->fetch_assoc()): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                <small><?php echo htmlspecialchars($item['author_name'] ?? 'Unknown Author'); ?></small><br>
                                <small>Position: #<?php echo intval($item['position_in_queue']); ?> • Requested:
                                    <?php echo date('M d, Y', strtotime($item['request_date'])); ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state-card">
                        <p>You are not on any waitlists.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-section">
                <div class="card-header">
                    <h3><i class="fi fi-rr-bell"></i> Availability Notifications</h3>
                    <span class="badge-green"><?php echo $notification_count; ?> New</span>
                </div>
                <div class="empty-state-card">
                    <p>No new availability notifications.</p>
                </div>
            </div>
        </main>
    </div>
</body>

</html>