<?php
session_start();
include 'db_config.php';

$sql = "SELECT b.book_id, b.title, b.image_url, b.edition,
        COALESCE((SELECT COUNT(*) FROM book_copy bc WHERE bc.Book_book_id = b.book_id AND bc.status = 'Available'), 0) AS available_copies,
        GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) ORDER BY w.request_date ASC SEPARATOR '||') AS borrower_names,
        GROUP_CONCAT(u.email ORDER BY w.request_date ASC SEPARATOR '||') AS borrower_emails,
        GROUP_CONCAT(u.user_type ORDER BY w.request_date ASC SEPARATOR '||') AS borrower_types,
        COUNT(w.waitlist_id) AS waiting_count
        FROM waitlist w
        JOIN book b ON w.Book_book_id = b.book_id
        JOIN `user` u ON w.Member_user_id = u.user_id
        GROUP BY b.book_id";

$waitlist_result = $conn->query($sql);

$waitlist_rows = [];
$total_waitlisted_patrons = 0;
if ($waitlist_result) {
    while ($row = $waitlist_result->fetch_assoc()) {
        $waitlist_rows[] = $row;
        $total_waitlisted_patrons += $row['waiting_count'];
    }
}
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

        .waitlist-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .waitlist-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
        }

        .waitlist-card-top {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            align-items: flex-start;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 24px;
        }

        .book-card-info {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            flex: 1;
            min-width: 320px;
        }

        .book-card-info img {
            width: 80px;
            height: 104px;
            border-radius: 16px;
            object-fit: cover;
            flex-shrink: 0;
            background: #f8fafc;
        }

        .waitlist-details h2 {
            margin: 0;
            font-size: 1.3rem;
            color: #0f172a;
        }

        .waitlist-details .subtitle {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 0.92rem;
        }

        .chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .chip-unavailable {
            color: #b91c1c;
            background: #fef2f2;
        }

        .waiting-badge {
            min-width: 92px;
            padding: 16px 18px;
            border-radius: 18px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .waiting-badge span {
            font-size: 0.72rem;
            color: #92400e;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .waiting-badge strong {
            font-size: 1.55rem;
            color: #c2410c;
            line-height: 1;
        }

        .waitlisted-section h4 {
            font-size: 0.85rem;
            color: #64748b;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            margin: 0 0 18px 0;
        }

        .borrower-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 16px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .borrower-row:last-child {
            border-bottom: none;
        }

        .borrower-order {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fde68a;
            color: #92400e;
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .borrower-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #dbeafe;
            color: #1d4ed8;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .borrower-name {
            margin: 0;
            font-size: 0.95rem;
            color: #111827;
            font-weight: 700;
        }

        .borrower-meta {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 0.82rem;
        }

        .status-pill {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            border-radius: 999px;
            padding: 10px 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.84rem;
            cursor: default;
        }

        .empty-state-container {
            width: 100%;
            min-height: 300px;
            border: 2px dashed #e2e8f0;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: rgba(248, 250, 252, 0.6);
            margin-top: 20px;
            padding: 40px 24px;
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
            opacity: 0.7;
        }

        .empty-state-container h2 {
            font-size: 1.4rem;
            color: #1e293b;
            margin: 0 0 10px 0;
        }

        .empty-state-container p {
            font-size: 0.95rem;
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
                    <li class="nav-item"><a href="admin_settings.php"><i class="fi fi-rr-settings"></i> Fine Settings</a>
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
                <div style="display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap; align-items: flex-start;">
                    <div>
                        <h1 style="font-size: 1.8rem; color: #1e3a8a; margin: 0;">Waitlist Management</h1>
                        <p style="color: #64748b; margin-top: 8px;">
                            <?php echo count($waitlist_rows) . ' book' . (count($waitlist_rows) === 1 ? '' : 's') . ' with '; ?>
                            <?php echo $total_waitlisted_patrons . ' total waiting patron' . ($total_waitlisted_patrons === 1 ? '' : 's'); ?>
                        </p>
                    </div>
                    <div class="search-container">
                        <i class="fi fi-rr-search"></i>
                        <input id="waitlistSearch" class="search-input" type="text" placeholder="Search by title or borrower...">
                    </div>
                </div>
            </div>

            <?php if (count($waitlist_rows) > 0): ?>
                <?php foreach ($waitlist_rows as $row):
                    $names = explode('||', $row['borrower_names']);
                    $emails = explode('||', $row['borrower_emails']);
                    $types = explode('||', $row['borrower_types']);
                    $isAvailable = ($row['available_copies'] > 0);
                    $bookCategory = 'General Use';
                    $searchText = strtolower($row['title'] . ' ' . $row['edition'] . ' ' . $bookCategory . ' ' . implode(' ', $names) . ' ' . implode(' ', $emails));
                    ?>
                    <div class="waitlist-card" data-search="<?php echo htmlspecialchars($searchText); ?>">
                        <div class="waitlist-card-top">
                            <div class="book-card-info">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                                <div class="waitlist-details">
                                    <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                                    <p class="subtitle"><?php echo htmlspecialchars($row['edition']); ?></p>
                                    <div class="chip-row">
                                        <span class="chip"><?php echo htmlspecialchars($bookCategory); ?></span>
                                        <span class="chip chip-unavailable"><?php echo $isAvailable ? 'Available' : 'Unavailable'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="waiting-badge">
                                <span>Waiting</span>
                                <strong><?php echo $row['waiting_count']; ?></strong>
                            </div>
                        </div>

                        <div class="waitlisted-section" style="margin-top: 22px;">
                            <h4>Waitlisted Borrowers</h4>

                            <?php foreach ($names as $i => $borrower): ?>
                                <div class="borrower-row">
                                    <div style="display: flex; align-items: center; gap: 14px;">
                                        <div class="borrower-order"><?php echo ($i + 1); ?></div>
                                        <div class="borrower-avatar"><?php echo strtoupper(substr($borrower, 0, 1)); ?></div>
                                        <div>
                                            <p class="borrower-name"><?php echo htmlspecialchars($borrower); ?></p>
                                            <p class="borrower-meta"><?php echo htmlspecialchars($types[$i]); ?> · <?php echo htmlspecialchars($emails[$i]); ?></p>
                                        </div>
                                    </div>
                                    <button class="status-pill"><i class="fi fi-rr-bell-ring"></i> Unavailable</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div id="waitlistEmptyState" class="empty-state-container">
                    <div class="empty-icon-circle">
                        <i class="fi fi-rr-clock"></i>
                    </div>
                    <h2>No active waitlists</h2>
                    <p>No books currently have users on their waitlist.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchField = document.getElementById('waitlistSearch');
            const cards = document.querySelectorAll('.waitlist-card');
            const emptyState = document.getElementById('waitlistEmptyState');

            function updateVisibility() {
                const query = searchField.value.trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(card => {
                    const text = card.dataset.search || '';
                    const visible = !query || text.includes(query);
                    card.style.display = visible ? 'block' : 'none';
                    if (visible) visibleCount++;
                });

                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 ? 'flex' : 'none';
                }
            }

            searchField.addEventListener('input', updateVisibility);
        });
    </script>
</body>

</html>
