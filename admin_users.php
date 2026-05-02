<?php
session_start();
include 'db_config.php';

// FETCH USERS
$result = $conn->query("SELECT * FROM user ORDER BY user_id DESC");

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
        <h2 class="brand-name">Library LMS</h2>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_catalog.php">Catalog</a></li>
            <li class="active"><a href="admin_users.php">Users</a></li>
            <li><a href="admin_waitlist.php">Waitlist</a></li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <strong>
                <?php
                $first = $_SESSION['user_name'] ?? 'Admin';
                $last = $_SESSION['last_name'] ?? '';
                echo htmlspecialchars($first . ' ' . $last);
                ?>
            </strong>
            <br>
            <small>
                <?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Admin')); ?>
            </small>
        </div>

        <a href="index.php" class="logout-link">Logout</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-content">

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