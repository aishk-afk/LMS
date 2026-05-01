<?php
session_start();
// If you have database includes, they go here
include 'db_config.php'; 
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
                <a href="index.html" class="logout-link">
                    <i class="fi fi-rr-exit"></i> Logout
                </a>
            </div>
        </aside>

        <main class="main-content">
            <header class="welcome-section">
                <h1>Welcome back, Jane Doe</h1>
                <p>Here is the status of your library account.</p>
            </header>

            <div class="member-stats-grid">
                <div class="m-stat-card">
                    <div class="m-icon-box bg-blue"><i class="fi fi-rr-book-alt"></i></div>
                    <div class="m-stat-info"><span>Borrowed Items</span>
                        <h3>1</h3>
                    </div>
                </div>
                <div class="m-stat-card">
                    <div class="m-icon-box bg-orange"><i class="fi fi-rr-clock"></i></div>
                    <div class="m-stat-info"><span>On Waitlist</span>
                        <h3>0</h3>
                    </div>
                </div>
                <div class="m-stat-card">
                    <div class="m-icon-box bg-red"><i class="fi fi-rr-credit-card"></i></div>
                    <div class="m-stat-info"><span>Outstanding Fines</span>
                        <h3 class="text-red">₱87</h3>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fi fi-rr-book"></i> Currently Borrowed</h3>
                    <span class="badge-count">1 Active</span>
                </div>
                <div class="borrowed-item-box">
                    <img src="book3.jpg" alt="Book" class="book-cover-sm">
                    <div class="item-details">
                        <h4>Pride and Prejudice</h4>
                        <p>Jane Austen</p>
                        <div class="item-tags">
                            <span class="tag tag-red"><i class="fi fi-rr-exclamation"></i> Overdue 29d · Mar 15</span>
                            <span class="tag tag-outline">Fine: ₱87</span>
                            <span class="tag tag-outline">Home-Use</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="history-section">
                <div class="card-header">
                    <h3><i class="fi fi-rr-time-past"></i> Borrow History</h3>
                    <span class="badge-count">3 Records</span>
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
                        <tr>
                            <td><strong>Campbell Biology</strong><br><small>Jane B. Reece...</small></td>
                            <td><span class="status-tag status-reference">Reference</span></td>
                            <td>Feb 1, 2026</td>
                            <td>Feb 15, 2026</td>
                            <td>Feb 28, 2026</td>
                            <td><span class="text-red">₱260</span></td>
                        </tr>
                        <tr>
                            <td><strong>Sapiens: A Brief History</strong><br><small>Yuval Noah Harari</small></td>
                            <td><span class="status-tag status-reference">Home-Use</span></td>
                            <td>Feb 10, 2026</td>
                            <td>Feb 24, 2026</td>
                            <td>Feb 20, 2026</td>
                            <td><span class="status-none">None</span></td>
                        </tr>
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