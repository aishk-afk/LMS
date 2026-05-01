<?php
include 'db_config.php';

// FETCH USERS
$result = $conn->query("SELECT * FROM users ORDER BY user_id DESC");

// CHECK IF QUERY FAILED
if (!$result) {
    die("Database error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library Members</title>

<link rel="stylesheet" href="css/admin.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/sidebar.css">
<link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>

<style>
.member-card {
    background: white;
    border: 1px solid #f1f5f9;
    border-radius: 20px;
    padding: 16px 24px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.member-main-info {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
}

.member-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #eff6ff;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.member-details h4 { margin: 0; }
.member-details p { margin: 2px 0 0; font-size: 13px; color: #64748b; }

.member-college { flex: 1.5; }

.member-stats {
    display: flex;
    align-items: center;
    gap: 12px;
}

.badge-role {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-student { background: #f5f3ff; color: #7c3aed; }
.role-faculty { background: #fff7ed; color: #ea580c; }

.stat-badge {
    padding: 4px 10px;
    background: #f1f5f9;
    border-radius: 8px;
    font-size: 13px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #64748b;
}
</style>
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
            <li><a href="admin_dashboard.html">Dashboard</a></li>
            <li><a href="admin_catalog.php">Catalog</a></li>
            <li class="active"><a href="admin_users.php">Users</a></li>
            <li><a href="admin_waitlist.php">Waitlist</a></li>
        </ul>
    </nav>
</aside>

<!-- MAIN -->
<main class="main-content">

<header style="margin-bottom:30px;">
    <h1>Library Members</h1>
    <p>Manage users and their library activity.</p>
</header>

<section class="members-list">

<?php if ($result->num_rows > 0): ?>

    <?php while ($row = $result->fetch_assoc()): 

        // SAFE VALUES
        $name = htmlspecialchars($row['name'] ?? 'Unknown');
        $email = htmlspecialchars($row['email'] ?? 'No email');
        $college = htmlspecialchars($row['college'] ?? 'N/A');
        $section = htmlspecialchars($row['section'] ?? 'N/A');
        $role = strtolower($row['role'] ?? 'student');

        // ROLE STYLE
        $roleClass = ($role === 'faculty') ? 'role-faculty' : 'role-student';

    ?>

    <div class="member-card">

        <!-- LEFT -->
        <div class="member-main-info">
            <div class="member-avatar">
                <?php echo strtoupper($name[0]); ?>
            </div>

            <div class="member-details">
                <h4><?php echo $name; ?></h4>
                <p><?php echo $email; ?></p>
            </div>
        </div>

        <!-- COLLEGE -->
        <div class="member-college">
            <span>College & Section</span>
            <strong><?php echo "$college · Sec $section"; ?></strong>
        </div>

        <!-- RIGHT -->
        <div class="member-stats">

            <span class="badge-role <?php echo $roleClass; ?>">
                <?php echo ucfirst($role); ?>
            </span>

            <!-- Placeholder -->
            <div class="stat-badge">0 books</div>

            <i class="fi fi-rr-angle-small-down expand-btn"></i>

        </div>

    </div>

    <?php endwhile; ?>

<?php else: ?>

    <div class="empty-state">
        No users found in the system.
    </div>

<?php endif; ?>

</section>

</main>

</div>

</body>
</html>