<?php
session_start();
include 'db_config.php';

// 1. Fetch Users first (to prevent multiple cards per person)
$sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.user_type, 
               m.Department, m.Course, m.Section, m.Member_Role,
               (SELECT SUM(balance) FROM fine WHERE Member_user_id = u.user_id) AS total_fine
        FROM user u
        JOIN member m ON u.user_id = m.user_id
        WHERE u.user_type = 'Member' AND m.Member_Role IN ('Student', 'Faculty')
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
    <style>
        /* Essential styles for the table-like look in cards */
        .member-card {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr 1fr 0.5fr;
            align-items: center;
            padding: 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }

        .member-book-info span,
        .member-due-date span,
        .member-fine span {
            display: block;
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
        }

        .text-danger {
            color: #e74c3c;
            font-weight: bold;
        }

        .expand-btn {
            transition: transform 0.3s;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" class="logo-icon">
                <h2 class="brand-name">Library Learning Management Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item"><a href="admin_dashboard.php"><i class="fi fi-rr-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a href="admin_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item active"><a href="admin_users.php"><i class="fi fi-rr-users-alt"></i> Users</a>
                    </li>
                    <li class="nav-item"><a href="admin_waitlist.php"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
                    <li class="nav-item"><a href="admin_settings.php"><i class="fi fi-rr-settings"></i> Settings</a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <?php
                $displayName = trim(($_SESSION['user_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
                $displayRole = ucfirst($_SESSION['user_role'] ?? 'admin');
                ?>
                <div class="user-info">
                    <strong><?php echo htmlspecialchars($displayName ?: 'Admin'); ?></strong><br>
                    <small><?php echo htmlspecialchars($displayRole); ?></small>
                </div>
                <a href="index.php" class="logout-link"><i class="fi fi-rr-exit"></i> Logout</a>
            </div>
        </aside>

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
                    <input type="text" class="search-input" id="userSearch" placeholder="Search by name, email...">
                </div>
                <div class="result-count"><?php echo $result->num_rows; ?> results</div>
            </section>

            <section class="members-list">
                <?php while ($row = $result->fetch_assoc()):
                    $uid = $row['user_id'];
                    $name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);

                    // 2. Sub-query for multiple active borrows
                    $b_sql = "SELECT bt.*, b.title 
                    FROM book_transaction bt 
                    JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id 
                    JOIN book b ON bc.Book_book_id = b.book_id 
                    WHERE bt.Member_user_id = $uid AND bt.status != 'Returned'";
                    $b_res = $conn->query($b_sql);
                    if (!$b_res) {
                        echo "SQL Error: " . $conn->error;
                    }
                    $b_count = ($b_res) ? $b_res->num_rows : 0;
                    $fine = number_format($row['total_fine'] ?? 0, 2);

                    // Fine logic
                    $fine = number_format($row['total_fine'] ?? 0, 2);
                    ?>
                    <div class="member-card-wrapper"
                        style="border: 1px solid #eee; margin-bottom: 10px; border-radius: 8px;">
                        <div class="member-card" onclick="toggleDetails(this)">
                            <div class="member-main-info" style="display:flex; align-items:center; gap:10px;">
                                <div class="member-avatar"
                                    style="width:40px; height:40px; background:#eef2ff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#4f46e5;">
                                    <?php echo strtoupper($row['first_name'][0]); ?>
                                </div>
                                <div class="member-details">
                                    <h4 style="margin:0; font-size:14px;"><?php echo $name; ?></h4>
                                    <p style="margin:0; font-size:12px; color:#666;"><?php echo $row['email']; ?></p>
                                </div>
                            </div>

                            <div class="member-book-info">
                                <span>Currently Borrowed</span>
                                <strong><?php echo ($b_count > 0) ? ($b_count == 1 ? "1 Book" : "$b_count Books") : "None"; ?></strong>
                            </div>

                            <div class="member-due-date">
                                <span>Status</span>
                                <strong><?php echo ($b_count > 0) ? "Active Loan" : "Clear"; ?></strong>
                            </div>

                            <div class="member-fine">
                                <span>Fine Balance</span>
                                <strong
                                    class="<?php echo ($fine > 0) ? 'text-danger' : ''; ?>">₱<?php echo $fine; ?></strong>
                            </div>

                            <div class="member-stats" style="text-align:right;">
                                <i class="fi fi-rr-angle-small-down expand-btn"></i>
                            </div>
                        </div>

                        <div class="member-expanded-details"
                            style="display:none; padding: 20px; background:#fcfcfc; border-top: 1px solid #eee;">
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <h5 style="margin-bottom:10px; color:#888;">BORROWED ITEMS</h5>
                                    <?php if ($b_count > 0): ?>
                                        <ul style="list-style:none; padding:0; font-size:13px;">
                                            <?php while ($b = $b_res->fetch_assoc()):
                                                $isOverdue = (strtotime($b['due_date']) < time());
                                                ?>
                                                <li style="margin-bottom:8px; padding-bottom:5px; border-bottom:1px dashed #ddd;">
                                                    <strong><?php echo htmlspecialchars($b['title']); ?></strong><br>
                                                    <small>Due: <span
                                                            class="<?php echo $isOverdue ? 'text-danger' : ''; ?>"><?php echo date('M d, Y', strtotime($b['due_date'])); ?></span></small>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p style="font-size:12px; color:#999;">No books currently borrowed.</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h5 style="margin-bottom:10px; color:#888;">MEMBER INFO</h5>
                                    <p style="font-size:13px; margin:4px 0;"><strong>Dept:</strong>
                                        <?php echo $row['Department']; ?></p>
                                    <p style="font-size:13px; margin:4px 0;"><strong>Course:</strong>
                                        <?php echo $row['Course']; ?>     <?php echo $row['Section']; ?></p>
                                    <p style="font-size:13px; margin:4px 0;"><strong>User ID:</strong>
                                        #<?php echo $row['user_id']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </section>
        </main>
    </div>

    <script>
        function toggleDetails(cardElement) {
            const details = cardElement.nextElementSibling;
            const arrow = cardElement.querySelector('.expand-btn');

            if (details.style.display === "none" || details.style.display === "") {
                details.style.display = "block";
                arrow.style.transform = "rotate(180deg)";
                cardElement.style.background = "#f0f4ff";
            } else {
                details.style.display = "none";
                arrow.style.transform = "rotate(0deg)";
                cardElement.style.background = "transparent";
            }
        }
    </script>
</body>

</html>