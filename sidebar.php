<?php include 'db_config.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Catalog</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
</head>

<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" alt="Logo" class="logo-icon">
                <h2 class="brand-name">Library Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item"><a href="admin_dashboard.html"><i class="fi fi-rr-home"></i> Dashboard</a></li>
                    <li class="nav-item active"><a href="admin_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a>
                    </li>
                    <li class="nav-item"><a href="admin_users.html"><i class="fi fi-rr-users-alt"></i> Users</a></li>
                    <li class="nav-item"><a href="admin_waitlist.html"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
                    <li class="nav-item"><a href="admin_settings.html"><i class="fi fi-rr-settings"></i> Settings</a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="admin-profile"><strong>Admin User</strong><br><small>Librarian</small></div>
                <a href="index.html" class="logout-link"><i class="fi fi-rr-exit"></i> Logout</a>
            </div>
        </aside>