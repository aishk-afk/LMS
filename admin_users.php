<?php
session_start();
include 'db_config.php';

$sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.user_type, m.Department, m.Course, m.Section,
    (SELECT COUNT(*) FROM book_transaction bt WHERE bt.Member_user_id = u.user_id AND bt.status IN ('Borrowed', 'Overdue')) AS borrow_count,
    (SELECT COUNT(*) FROM book_transaction bt WHERE bt.Member_user_id = u.user_id AND bt.status = 'Overdue') AS overdue_count
    FROM user u
    LEFT JOIN member m ON u.user_id = m.user_id
    ORDER BY u.user_id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Library Members</title>

    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
</head>

<body>

<div class="admin-container">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="applogo(2).png" class="logo-icon">
        <h2 class="brand-name">Library Learning Management Hub </h2>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item"><a href="admin_dashboard.php"><i class="fi fi-rr-home"></i> Dashboard</a></li>
            <li class="nav-item"><a href="admin_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
            <li class="nav-item active"><a href="admin_users.php"><i class="fi fi-rr-users-alt"></i> Users</a></li>
            <li class="nav-item"><a href="admin_waitlist.php"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
            <li class="nav-item"><a href="admin_settings.php"><i class="fi fi-rr-settings"></i> Settings</a></li>
        </ul>
    </nav>

    <div class="sidebar-footer">
         <?php
            $first = $_SESSION['user_name'] ?? '';
            $last = $_SESSION['last_name'] ?? '';
            $role = $_SESSION['user_role'] ?? 'admin';
            $displayName = trim($first . ' ' . $last);
            if ($displayName === '') {
                $displayName = 'Admin';
            }
            $displayRole = ucfirst($role);
        ?>
        <div class="user-info">
            <strong><?php echo htmlspecialchars($displayName); ?></strong>
            <br>
            <small><?php echo htmlspecialchars($displayRole); ?></small>
        </div>
        <a href="index.php" class="logout-link"><i class="fi fi-rr-exit"></i> Logout</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-content">

    <section class="page-header">
        <div>
            <h1>Library Members</h1>
            <p>Manage users, view borrowed books, and issue returns.</p>
        </div>
        <a href="#" class="btn-primary"><i class="fi fi-rr-plus"></i> Add User</a>
    </section>

    <section class="users-toolbar">
        <div class="search-container">
            <i class="fi fi-rr-search"></i>
            <input type="text" class="search-input" id="userSearch" placeholder="Search by name, email, college, or section...">
        </div>

        <div class="filter-group" role="group" aria-label="User filters">
            <button class="filter-pill active" data-filter="all">All</button>
            <button class="filter-pill" data-filter="student">Student</button>
            <button class="filter-pill" data-filter="faculty">Faculty</button>
        </div>

        <div class="result-count"><?php echo $result->num_rows; ?> results</div>
    </section>

    <section class="members-list" id="membersList">
<header style="margin-bottom:30px;">
    <h1>Library Members</h1>
    <p>Manage users and their library activity.</p>
</header>

<section class="members-list">

<?php while ($row = $result->fetch_assoc()): ?>

<?php
$name = htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')));
$email = htmlspecialchars($row['email'] ?? '');
$role = strtolower($row['user_type'] ?? 'member');
$roleClass = ($role === 'admin') ? 'role-faculty' : 'role-student';
?>

<div class="member-card">

    <div class="member-main-info">
        <div class="member-avatar">
            <?php echo strtoupper($name[0] ?? 'U'); ?>
        </div>

        <div class="member-details">
            <h4><?php echo $name; ?></h4>
            <p><?php echo $email; ?></p>
        </div>
    </div>

    <div class="member-college">
        <span>College & Section</span>
        <strong>N/A</strong>
    </div>

    <div class="member-stats">
        <span class="badge-role <?php echo $roleClass; ?>">
            <?php echo ucfirst($role); ?>
        </span>

        <div class="stat-badge">
            <i class="fi fi-rr-book-alt"></i> 0
        </div>

        <i class="fi fi-rr-angle-small-down expand-btn"></i>
    </div>

</div>

<?php endwhile; ?>

</section>

</main>
</div>

</body>
</html>