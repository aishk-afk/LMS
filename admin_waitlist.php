<?php
session_start();
include 'db_config.php';

$sql = "SELECT b.book_id, b.title, b.image_url, b.edition, 
        GROUP_CONCAT(u.first_name, ' ', u.last_name ORDER BY w.request_date ASC SEPARATOR '||') as borrower_names,
        GROUP_CONCAT(u.email ORDER BY w.request_date ASC SEPARATOR '||') as borrower_emails,
        GROUP_CONCAT(u.user_type ORDER BY w.request_date ASC SEPARATOR '||') as borrower_types,
        COUNT(w.waitlist_id) as waiting_count
        FROM waitlist w
        JOIN book b ON w.Book_book_id = b.book_id
        JOIN user u ON w.Member_user_id = u.user_id
        GROUP BY b.book_id";

$waitlist_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Waitlist</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <style>
        /* --- WAITLIST SPECIFIC STYLES --- */
        .waitlist-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .search-container {
            position: relative;
            width: 350px;
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            border-color: #2563eb;
        }

        /* Empty State Box from Figma */
        .empty-state-container {
            width: 100%;
            height: 400px;
            border: 2px dashed #e2e8f0;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: rgba(248, 250, 252, 0.5);
            margin-top: 20px;
        }

        .empty-icon-circle {
            width: 80px;
            height: 80px;
            background: #eff6ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .empty-icon-circle i {
            font-size: 32px;
            color: #3b82f6;
            opacity: 0.6;
        }

        .empty-state-container h2 {
            font-size: 20px;
            color: #1e293b;
            margin: 0 0 8px 0;
        }

        .empty-state-container p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" alt="Logo" class="logo-icon">
                <h2 class="brand-name">Library Learning Management Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item"><a href="admin_dashboard.php"><i class="fi fi-rr-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="admin_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item"><a href="admin_users.php"><i class="fi fi-rr-users-alt"></i> Users</a></li>
                    <li class="nav-item active"><a href="admin_waitlist.php"><i class="fi fi-rr-clock"></i> Waitlist</a>
                    </li>
                    <li class="nav-item"><a href="admin_settings.php"><i class="fi fi-rr-settings"></i> Settings</a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <strong>
                        <?php
                        // Pulls admin's first and last name
                        $first = $_SESSION['user_name'] ?? 'Admin';
                        $last = $_SESSION['last_name'] ?? '';
                        echo htmlspecialchars($first . ' ' . $last);
                        ?>
                    </strong>
                    <br>
                    <small>
                        <?php
                        // Capitalizes 'admin' to 'Admin'
                        $role = $_SESSION['user_role'] ?? 'Admin';
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
            <div class="header-section" style="margin-bottom: 30px;">
                <h1 style="font-size: 1.8rem; color: #1e3a8a; margin: 0;">Waitlist Management</h1>
                <p style="color: #64748b;">
                    <?php echo ($waitlist_result && $waitlist_result->num_rows > 0) ? $waitlist_result->num_rows : '0'; ?>
                    book(s) with active waiting patrons
                </p>
            </div>

            <?php if ($waitlist_result && $waitlist_result->num_rows > 0): ?>
                <?php while ($row = $waitlist_result->fetch_assoc()):
                    $names = explode('||', $row['borrower_names']);
                    $emails = explode('||', $row['borrower_emails']);
                    $types = explode('||', $row['borrower_types']);
                    ?>
                    <div
                        style="background: white; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">

                        <div style="display: flex; gap: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>"
                                style="width: 70px; height: 95px; border-radius: 8px; object-fit: cover;">
                            <div style="flex-grow: 1;">
                                <h2 style="font-size: 1.2rem; color: #1e293b; margin: 0;">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </h2>
                                <p style="color: #64748b; font-size: 0.85rem; margin: 4px 0;">
                                    <?php echo htmlspecialchars($row['edition']); ?>
                                </p>
                                <div style="display: flex; gap: 8px; margin-top: 10px;">
                                    <span
                                        style="background: #eff6ff; color: #3b82f6; padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">Home-Use</span>
                                    <span
                                        style="background: #fef2f2; color: #ef4444; padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">Unavailable</span>
                                </div>
                            </div>

                            <div
                                style="background: #fff7ed; border: 1px solid #ffedd5; padding: 8px 15px; border-radius: 12px; text-align: center; min-width: 70px;">
                                <p
                                    style="margin: 0; font-size: 0.65rem; color: #c2410c; font-weight: 700; text-transform: uppercase;">
                                    Waiting</p>
                                <p style="margin: 0; font-size: 1.4rem; color: #ea580c; font-weight: 800;">
                                    <?php echo $row['waiting_count']; ?>
                                </p>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <h4
                                style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 15px; font-weight: 600; text-transform: uppercase;">
                                Waitlisted Borrowers</h4>

                            <?php for ($i = 0; $i < count($names); $i++): ?>
                                <div
                                    style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: <?php echo ($i < count($names) - 1) ? '1px solid #f8fafc' : 'none'; ?>;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <span style="color: #ea580c; font-weight: 700; width: 15px; font-size: 0.9rem;">
                                            <?php echo ($i + 1); ?>
                                        </span>
                                        <div
                                            style="width: 38px; height: 38px; background: #dbeafe; color: #2563eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem;">
                                            <?php echo strtoupper(substr($names[$i], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p style="margin: 0; font-size: 0.9rem; font-weight: 600; color: #1e293b;">
                                                <?php echo htmlspecialchars($names[$i]); ?>
                                            </p>
                                            <p style="margin: 0; font-size: 0.75rem; color: #64748b;">
                                                <?php echo htmlspecialchars($types[$i]); ?> •
                                                <?php echo htmlspecialchars($emails[$i]); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        style="background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8; padding: 6px 14px; border-radius: 8px; font-size: 0.8rem; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                                        <i class="fi fi-rr-bell-ring" style="font-size: 0.75rem;"></i> Unavailable
                                    </button>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div
                    style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 60vh; text-align: center;">
                    <div
                        style="background: #f1f5f9; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fi fi-rr-clock" style="font-size: 2rem; color: #94a3b8;"></i>
                    </div>
                    <h2 style="color: #1e293b; margin: 0; font-size: 1.5rem;">No active waitlists</h2>
                    <p style="color: #64748b; margin-top: 8px;">No books currently have users on their waitlist.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>