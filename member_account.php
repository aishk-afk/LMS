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
    <title>Account Settings - Member</title>
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
                    <li class="nav-item"><a href="member_dashboard.php"><i class="fi fi-rr-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="member_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item active"><a href="member_account.html"><i class="fi fi-rr-user"></i> Account</a>
                    </li>
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

        <main class="main-content flex-center">
            <div class="settings-card">
                <div class="settings-header">
                    <div class="icon-circle">
                        <i class="fi fi-rr-settings"></i>
                    </div>
                    <h1>Account Settings</h1>
                    <p>Update your profile info and preferences here.</p>
                </div>

                <form class="settings-form">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" value="Jane Doe" placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="student@lib.edu" placeholder="Enter your email">
                    </div>

                    <button type="submit" class="btn-save-large">Save Changes</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>