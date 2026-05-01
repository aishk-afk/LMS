<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_config.php';

// 1. Role Authorization Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit;
}

// 2. Identify User and Role
$userId = $_SESSION['user_id'];
$role = strtolower($_SESSION['role']);

// 2. Fetch Admin Profile Info
$userQuery = "SELECT u.first_name, u.last_name, u.user_type 
              FROM user u 
              WHERE u.user_id = '$userId' LIMIT 1";
$userResult = $conn->query($userQuery);
$user = $userResult->fetch_assoc();
$fullName = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'Admin User';

// 3. Fetch Library Statistics (Dynamic Data)
$totalBooks = $conn->query("SELECT COUNT(*) as count FROM Book")->fetch_assoc()['count'];
$onLoan = $conn->query("SELECT COUNT(*) as count FROM Book_Copy WHERE status = 'Borrowed'")->fetch_assoc()['count'];
$available = $conn->query("SELECT COUNT(*) as count FROM Book_Copy WHERE status = 'Available'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library Hub</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>

<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" alt="Logo" style="width: 30px;">
                <h2>Library Learning Management Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active"><a href="admin_dashboard.php"><i class="fi fi-rr-home"></i>
                            Dashboard</a></li>
                    <li class="nav-item"><a href="admin_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item"><a href="admin_users.php"><i class="fi fi-rr-users"></i> Users</a></li>
                    <li class="nav-item"><a href="admin_waitlist.php"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
                    <li class="nav-item"><a href="admin_settings.html"><i class="fi fi-rr-settings"></i> Settings</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="admin-profile">
                    <strong><?php echo htmlspecialchars($fullName); ?></strong><br>
                    <small><?php echo ucfirst($role); ?></small>
                </div>
                <a href="login.php?action=logout" class="logout-link"><i class="fi fi-rr-exit"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content">
            <header class="header-section">
                <div>
                    <h1>Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'Admin'); ?>!</h1>
                    <p>Here is what's happening with the library today.</p>
                </div>
            </header>

            <div class="stats-grid"
                style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px;">
                <div class="stat-card"
                    style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <label style="color: #64748b; font-size: 0.8rem; font-weight: 600;">TOTAL BOOKS</label>
                    <h2 style="font-size: 2rem; margin: 10px 0;"><?php echo $totalBooks; ?></h2>
                </div>
                <div class="stat-card"
                    style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <label style="color: #64748b; font-size: 0.8rem; font-weight: 600;">ON LOAN</label>
                    <h2 style="font-size: 2rem; margin: 10px 0; color: #f97316;"><?php echo $onLoan; ?></h2>
                </div>
                <div class="stat-card"
                    style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <label style="color: #64748b; font-size: 0.8rem; font-weight: 600;">AVAILABLE</label>
                    <h2 style="font-size: 2rem; margin: 10px 0; color: #059669;"><?php echo $available; ?></h2>
                </div>
            </div>

            <div class="dashboard-section" style="margin-top: 40px;">
                <div class="card-header">
                    <h3><i class="fi fi-rr-time-past"></i> Recent Transactions</h3>
                </div>
                <div class="table-container"
                    style="background: white; border-radius: 15px; padding: 20px; margin-top: 15px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid #e2e8f0; color: #64748b;">
                                <th style="padding: 10px;">Book</th>
                                <th>Member</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 15px 10px;">The Maze Runner</td>
                                <td>John Doe</td>
                                <td><span class="badge-green">Returned</span></td>
                                <td>Oct 24, 2023</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>