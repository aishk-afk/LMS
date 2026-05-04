<?php
session_start();
// If you have database includes, they go here
include 'db_config.php';

// Fetch Active Borrows (Not yet returned)
// Fetch Active Borrows (Status is 'Active')
$active_query = "SELECT bt.borrow_id, b.title, u.first_name, u.last_name, bt.borrow_date, bt.due_date 
                 FROM book_transaction bt
                 JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
                 JOIN book b ON bc.Book_book_id = b.book_id
                 JOIN user u ON bt.Member_user_id = u.user_id
                 WHERE bt.status = 'Active'";
$active_result = mysqli_query($conn, $active_query);

// Fetch Overdue Items (Status is 'Overdue' or logic-based check)
$overdue_query = "SELECT bt.borrow_id, b.title, u.first_name, u.last_name, bt.due_date, f.total_amount_accrued 
                  FROM book_transaction bt
                  JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
                  JOIN book b ON bc.Book_book_id = b.book_id
                  JOIN user u ON bt.Member_user_id = u.user_id
                  LEFT JOIN fine f ON bt.borrow_id = f.Book_Transaction_borrow_id
                  WHERE bt.status = 'Overdue' OR (bt.status = 'Active' AND bt.due_date < CURDATE())";
$overdue_result = mysqli_query($conn, $overdue_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/member.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <style>
        .filter-buttons {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            gap: 4px;
        }

        .filter-buttons button {
            border: none;
            background: transparent;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 14px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-buttons button.active {
            background: white;
            color: #1e293b;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            font-weight: 600;
        }

        /* Date input and navigation button styling */
        #dateInput {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            transition: all 0.2s;
            color: #1e293b;
            font-weight: 500;
        }

        #dateInput:hover,
        #dateInput:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
            outline: none;
        }

        .nav-button {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-button:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #1e293b;
        }

        .overview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .overview-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .title-group h1 {
                font-size: 24px !important;
            }
        }

        .book-img-holder {
            width: 45px;
            height: 60px;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .book-img-holder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .details-section {
            background: white;
            margin: 20px 0;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #f1f5f9;
        }

        .hidden {
            display: none !important;
        }

        .rotate-arrow {
            transform: rotate(180deg);
            transition: 0.3s;
        }

        .text-red {
            color: #ef4444 !important;
        }

        /* Row Layouts */
        .dashboard-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .dashboard-row.equal {
            grid-template-columns: 1fr 1fr;
        }

        .dashboard-row.single {
            grid-template-columns: 1fr;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid #f1f5f9;
        }

        #returnBookModal {
            display: none;
            position: fixed;
            z-index: 9999;
            /* Higher than your sidebar and charts */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent background */
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
                    <li class="nav-item active"><a href="admin_dashboard.php"><i class="fi fi-rr-home"></i>
                            Dashboard</a></li>
                    <li class="nav-item"><a href="admin_catalog.php"><i class="fi fi-rr-search"></i> Catalog</a></li>
                    <li class="nav-item"><a href="admin_users.php"><i class="fi fi-rr-users-alt"></i> Users</a></li>
                    <li class="nav-item"><a href="admin_waitlist.php"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
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
            <header class="overview-header"
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
                <div class="title-group">
                    <h1 style="font-size: 28px; margin: 0;">System Overview</h1>
                    <p style="color: #64748b; margin: 4px 0 0;" id="periodIndicator">Monitor library activity and
                        statistics.</p>
                </div>
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <!-- Date Navigation Controls -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button class="nav-button" onclick="previousPeriod()" title="Previous Period">
                            <i class="fi fi-rr-angle-left"></i>
                        </button>
                        <input type="date" id="dateInput" />
                        <button class="nav-button" onclick="nextPeriod()" title="Next Period">
                            <i class="fi fi-rr-angle-right"></i>
                        </button>
                    </div>
                    <!-- Filter Buttons -->
                    <div class="filter-buttons">
                        <button class="active" onclick="changePeriod('week')" data-period="week">Weekly</button>
                        <button onclick="changePeriod('month')" data-period="month">Monthly</button>
                        <button onclick="changePeriod('year')" data-period="year">Yearly</button>
                    </div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card" onclick="toggleDetails('activeSection', 'arrow1')" style="cursor: pointer;">
                    <div class="stat-icon icon-blue"><i class="fi fi-rr-book-alt"></i></div>
                    <div class="stat-info"><span>Active Borrows</span>
                        <h3 id="statActiveBorrows">—</h3>
                    </div>
                    <i class="fi fi-rr-angle-small-down dropdown-arrow" id="arrow1"></i>
                </div>
                <div class="stat-card" onclick="toggleDetails('overdueSection', 'arrow2')" style="cursor: pointer;">
                    <div class="stat-icon icon-red"><i class="fi fi-rr-exclamation"></i></div>
                    <div class="stat-info"><span>Overdue Items</span>
                        <h3 class="text-red" id="statOverdueItems">—</h3>
                    </div>
                    <i class="fi fi-rr-angle-small-down dropdown-arrow" id="arrow2"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fi fi-rr-book"></i></div>
                    <div class="stat-info"><span>Total Books</span>
                        <h3 id="statTotalBooks">—</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-purple"><i class="fi fi-rr-credit-card"></i></div>
                    <div class="stat-info"><span>Fines Collected</span>
                        <h3 id="statFinesCollected">—</h3>
                    </div>
                </div>
            </div>

            <div id="activeSection" class="details-section hidden">
                <h3 style="margin-bottom: 20px;"><i class="fi fi-rr-book-alt" style="color:#3b82f6"></i> Active Borrows
                </h3>
                <table class="borrow-table">
                    <thead>
                        <tr>
                            <th>BOOK</th>
                            <th>BORROWER</th>
                            <th>DATE BORROWED</th>
                            <th>DUE DATE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($active_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($active_result)): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <div class="book-img-holder"><img src="book_placeholder.jpg"></div>
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                                    <td>
                                        <button class="btn-return" onclick="openReturnModal(<?php echo $row['borrow_id']; ?>, '<?php echo addslashes($row['title']); ?>')">
                                            Return
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">No active borrows found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="overdueSection" class="details-section hidden">
                <h3 style="margin-bottom: 20px;"><i class="fi fi-rr-exclamation" style="color:#ef4444"></i> Overdue
                    Items</h3>
                <table class="borrow-table">
                    <thead>
                        <tr>
                            <th>BOOK</th>
                            <th>BORROWER</th>
                            <th>DAYS OVERDUE</th>
                            <th>FINE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="book-img-holder"><img src="book1.jpg"></div>Pride and Prejudice
                                </div>
                            </td>
                            <td>Jane Doe</td>
                            <td class="text-red">29 days</td>
                            <td>₱87</td>
                            <td><button class="btn-return" style="background:#ef4444">Return</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="dashboard-row">
                <div class="card">
                    <h3>Borrowing Trends</h3>
                    <div class="chart-wrapper">
                        <canvas id="borrowingChart"></canvas>
                    </div>
                </div>
                <div class="card">
                    <h3>Genre Popularity</h3>
                    <div class="chart-wrapper chart-wrapper--small">
                        <canvas id="genreChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="dashboard-row equal">
                <div class="card">
                    <h3 style="margin-bottom: 20px;">🏆 Top Borrowers</h3>
                    <div id="topBorrowersList">
                        <div class="borrower-item"
                            style="display:flex; align-items:center; justify-content:space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="avatar"
                                    style="background:#eff6ff; color:#3b82f6; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:600;">
                                    ?</div>
                                <div><strong>Loading...</strong><br><small style="color:#64748b;">Top borrowers</small>
                                </div>
                            </div>
                            <span style="font-weight:600; color:#3b82f6;">0x</span>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                        <h3 style="margin:0;">Materials Added by Genre</h3>
                        <small id="materialsTotal" style="color:#64748b;">0 total items</small>
                    </div>
                    <div class="chart-wrapper chart-wrapper--small">
                        <canvas id="materialsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="dashboard-row single">
                <div class="card">
                    <div style="margin-bottom: 20px;">
                        <h3 style="margin:0;">Fine Summary</h3>
                        <small style="color:#64748b;" id="fineSummaryText">Collected: ₱0 · Pending: ₱0 · Total: ₱0</small>
                    </div>
                    <div class="chart-wrapper chart-wrapper--small">
                        <canvas id="finesChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="dashboard_charts.js"></script>
    <script>
        let currentPeriod = 'week';
        let currentDate = new Date();

        function toggleDetails(id, arrowId) {
            document.getElementById(id).classList.toggle('hidden');
            document.getElementById(arrowId).classList.toggle('rotate-arrow');
        }

        function changePeriod(period) {
            currentPeriod = period;
            currentDate = new Date();

            // Update button states
            document.querySelectorAll('.filter-buttons button[data-period]').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`button[data-period="${period}"]`).classList.add('active');

            updatePeriodIndicator();
            updateDateInput();
            updateAllCharts();
        }

        function previousPeriod() {
            if (currentPeriod === 'week') {
                currentDate.setDate(currentDate.getDate() - 7);
            } else if (currentPeriod === 'month') {
                currentDate.setMonth(currentDate.getMonth() - 1);
            } else if (currentPeriod === 'year') {
                currentDate.setFullYear(currentDate.getFullYear() - 1);
            }
            updatePeriodIndicator();
            updateDateInput();
            updateAllCharts();
        }

        function nextPeriod() {
            if (currentPeriod === 'week') {
                currentDate.setDate(currentDate.getDate() + 7);
            } else if (currentPeriod === 'month') {
                currentDate.setMonth(currentDate.getMonth() + 1);
            } else if (currentPeriod === 'year') {
                currentDate.setFullYear(currentDate.getFullYear() + 1);
            }
            updatePeriodIndicator();
            updateDateInput();
            updateAllCharts();
        }

        function updateDateInput() {
            const input = document.getElementById('dateInput');
            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const day = String(currentDate.getDate()).padStart(2, '0');
            input.value = `${year}-${month}-${day}`;
        }

        function updatePeriodIndicator() {
            const indicator = document.getElementById('periodIndicator');
            let text = '';

            if (currentPeriod === 'week') {
                const weekStart = new Date(currentDate);
                weekStart.setDate(currentDate.getDate() - currentDate.getDay());
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);

                const options = {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                };
                text = `Week of ${weekStart.toLocaleDateString('en-US', options)} - ${weekEnd.toLocaleDateString('en-US', options)}`;
            } else if (currentPeriod === 'month') {
                const options = {
                    month: 'long',
                    year: 'numeric'
                };
                text = `${currentDate.toLocaleDateString('en-US', options)}`;
            } else if (currentPeriod === 'year') {
                text = `Year ${currentDate.getFullYear()}`;
            }

            indicator.textContent = text;
        }

        function getDateRange() {
            let startDate, endDate;
            const today = new Date(currentDate);

            if (currentPeriod === 'week') {
                const day = today.getDay();
                const diffToMonday = (day === 0) ? -6 : 1 - day;
                startDate = new Date(today);
                startDate.setDate(today.getDate() + diffToMonday);
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
            } else if (currentPeriod === 'month') {
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } else if (currentPeriod === 'year') {
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
            }

            function toLocalDate(d) {
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            }

            return {
                start: toLocalDate(startDate),
                end: toLocalDate(endDate)
            };
        }

        function updateAllCharts() {
            // This will be called by the dashboard_charts.js
            if (window.refreshCharts) {
                window.refreshCharts(currentPeriod, getDateRange());
            }
        }

        function openReturnModal(borrowId, title) {
            // 1. Get the elements by their IDs
            const modal = document.getElementById('returnBookModal');
            const inputId = document.getElementById('returnBorrowId');
            const textTitle = document.getElementById('returnBookTitle');

            // 2. Check if they exist to prevent the "nothing happens" bug
            if (modal && inputId && textTitle) {
                inputId.value = borrowId; // Passes the ID to the hidden form
                textTitle.innerText = title; // Displays the book name
                modal.style.display = 'block'; // Shows the actual HTML Modal
            } else {
                // If this runs, it means your HTML Modal code is missing the IDs!
                console.error("Critical Error: Modal IDs not found in the HTML.");
                alert("System Error: Modal UI not initialized.");
            }
        }

        function closeReturnModal() {
            document.getElementById('returnBookModal').style.display = 'none';

        }

        // Close the modal if the admin clicks outside of the box
        window.onclick = function(event) {
            const modal = document.getElementById('returnBookModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function toggleSeverity() {
            const condition = document.getElementById('returnCondition').value;
            const container = document.getElementById('severityContainer');

            // Only show severity options if the status is 'DAMAGED'
            if (condition === 'DAMAGED') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }

        // Handle direct date input change
        document.getElementById('dateInput').addEventListener('change', function() {
            const newDate = new Date(this.value);
            if (!isNaN(newDate.getTime())) {
                currentDate = newDate;
                updatePeriodIndicator();
                updateAllCharts();
            }
        });

        // Initialize on page load
        window.addEventListener('load', function() {
            updatePeriodIndicator();
            updateDateInput();
            updateAllCharts();
        });
    </script>
</body>

</html>