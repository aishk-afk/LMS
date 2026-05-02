<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_config.php';

// Get user info from session so the page knows who is browsing
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['user_role'] ?? 'member';
$is_admin = (strtolower($role) === 'admin');

// 1. Fetch Genres for the filter dropdown
$genre_query = "SELECT * FROM genre";
$genres_result = $conn->query($genre_query);

// 2. Fetch Books with Genre Names and Copy counts
$query = "SELECT b.*, g.genre_name, 
          (SELECT COUNT(*) FROM Book_Copy WHERE Book_book_id = b.book_id) as copies 
          FROM Book b
          LEFT JOIN Genre g ON b.Genre_genre_id = g.genre_id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Management System - Catalog</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>

    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
            background: #f8fafc;
        }

        .main-content {
            flex-grow: 1;
            padding: 40px;
        }

        /* Figma-style Search and Filter Bar */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            align-items: center;
        }

        .search-container {
            position: relative;
            flex-grow: 1;
            background: white;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-container input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.9rem;
        }

        .dropdown-filter {
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #64748b;
            min-width: 130px;
            font-size: 0.9rem;
            outline: none;
            cursor: pointer;
        }

        /* Book Grid and Figma-style Badges */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 25px;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .book-card:hover {
            border-color: #3b82f6;
            transform: translateY(-3px);
        }

        .genre-badge {
            display: inline-block;
            background: #EEF2FF;
            color: #4F46E5;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .status-pill {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .status-available {
            background: #DCFCE7;
            color: #166534;
        }

        .status-unavailable {
            background: #FEE2E2;
            color: #991B1B;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="applogo(2).png" alt="Logo" style="width: 30px;">
                <h2 class="brand-name">Learning Library Management Hub</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item"><a href="member_dashboard.php"><i class="fi fi-rr-home"></i> Dashboard</a></li>
                    <li class="nav-item active"><a href="member_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a>
                    </li>
                    <li class="nav-item"><a href="member_account.php"><i class="fi fi-rr-settings"></i> Settings</a>
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

        <main class="main-content">
            <div class="header-section">
                <div>
                    <h1 style="color: #1e3a8a; font-weight: 700; font-size: 1.8rem; margin: 0;">Library Catalog</h1>
                    <p style="color: #64748b; margin: 5px 0 0 0;">Manage and monitor your library collection.</p>
                </div>
            </div>

            <div class="filter-bar">
                <div class="search-container">
                    <i class="fi fi-rr-search"></i>
                    <input type="text" id="catalogSearch" placeholder="Search by title, author, or keyword...">
                </div>

                <select class="dropdown-filter" id="filterGenre">
                    <option value="all">All Genres</option>
                    <?php
                    $genres_result->data_seek(0);
                    while ($g = $genres_result->fetch_assoc()): ?>
                        <option value="<?php echo $g['genre_name']; ?>"><?php echo $g['genre_name']; ?></option>
                    <?php endwhile; ?>
                </select>

                <select class="dropdown-filter" id="filterStatus">
                    <option value="all">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Not Available">Not Available</option>
                </select>
            </div>

            <div class="book-grid" id="bookGrid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $isAvailable = ($row['copies'] > 0);
                        ?>
                        <div class="book-card" data-genre="<?php echo htmlspecialchars($row['genre_name'] ?? 'General'); ?>"
                            data-status="<?php echo $isAvailable ? 'Available' : 'Not Available'; ?>">

                            <div style="height: 250px; position: relative; border-radius: 14px; overflow: hidden; cursor: pointer;"
                                onclick="window.location.href='book_details.php?id=<?php echo $row['book_id']; ?>'">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>"
                                    style="width:100%; height:100%; object-fit:cover;">
                                <span
                                    class="status-pill <?php echo $isAvailable ? 'status-available' : 'status-unavailable'; ?>">
                                    <?php echo $isAvailable ? 'Available' : 'Not Available'; ?>
                                </span>
                            </div>

                            <div style="padding: 20px;">
                                <span class="genre-badge"><?php echo htmlspecialchars($row['genre_name'] ?? 'General'); ?></span>
                                <h3
                                    style="font-size: 1rem; margin: 0 0 8px 0; color: #1e293b; font-weight: 700; line-height: 1.4; cursor: pointer;"
                                    onclick="window.location.href='book_details.php?id=<?php echo $row['book_id']; ?>'">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </h3>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 15px 0;">Copies: <?php echo $row['copies']; ?>
                                </p>

                                <div style="display: flex; gap: 10px;">
                                    <?php if ($isAvailable): ?>
                                        <button class="btn-borrow" onclick="event.stopPropagation(); borrowBook(<?php echo $row['book_id']; ?>)">
                                            Borrow Book
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-waitlist" onclick="event.stopPropagation(); joinWaitlist(<?php echo $row['book_id']; ?>)">
                                            Join Waitlist
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="addBookModal"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    </div>

    <script>
        function openModal() { document.getElementById('addBookModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('addBookModal').style.display = 'none'; }

        // Figma Filtering Logic
        const searchInput = document.getElementById('catalogSearch');
        const genreFilter = document.getElementById('filterGenre');
        const statusFilter = document.getElementById('filterStatus');

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const gValue = genreFilter.value;
            const sValue = statusFilter.value;

            document.querySelectorAll('.book-card').forEach(card => {
                const title = card.querySelector('h3').innerText.toLowerCase();
                const genre = card.getAttribute('data-genre');
                const status = card.getAttribute('data-status');

                const matchesSearch = title.includes(searchTerm);
                const matchesGenre = (gValue === 'all' || genre === gValue);
                const matchesStatus = (sValue === 'all' || status === sValue);

                card.style.display = (matchesSearch && matchesGenre && matchesStatus) ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', applyFilters);
        genreFilter.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);

        function borrowBook(bookId) {
            event.stopPropagation();
            alert('Borrowing process started for Book ID: ' + bookId);
            // TODO: Add actual borrow request logic here
        }

        function joinWaitlist(bookId) {
            event.stopPropagation();
            alert('Added to waitlist for Book ID: ' + bookId);
            // TODO: Add actual waitlist request logic here
        }
    </script>
</body>

</html>