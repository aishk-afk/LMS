<?php
session_start();
include 'db_config.php';

// Fetch Users
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

// Fetch Departments for dropdown
$dept_result = $conn->query("SELECT DISTINCT Department FROM member WHERE Department IS NOT NULL AND Department != '' ORDER BY Department ASC");
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

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .modal-header {
            background: #1e3a8a;
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #1e3a8a;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* Role toggle buttons */
        .role-toggle {
            display: flex;
            gap: 8px;
        }

        .role-btn {
            flex: 1;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #64748b;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .role-btn.active {
            background: #1e3a8a;
            color: white;
            border-color: #1e3a8a;
        }

        .modal-footer {
            padding: 16px 25px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            padding: 10px 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            font-weight: 600;
            color: #64748b;
        }

        .btn-submit {
            padding: 10px 24px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 14px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 9999;
            display: none;
            animation: slideIn 0.3s ease;
        }

        .toast.success {
            background: #10b981;
        }

        .toast.error {
            background: #ef4444;
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Section field hidden for Faculty */
        #sectionGroup {
            transition: opacity 0.2s;
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
                    <li class="nav-item"><a href="admin_settings.php"><i class="fi fi-rr-settings"></i> Fine
                            Settings</a></li>
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
                <!-- Trigger modal -->
                <button onclick="openAddUserModal()" class="btn-primary">
                    <i class="fi fi-rr-plus"></i> Add User
                </button>
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

                    $b_sql = "SELECT bt.*, b.title 
                              FROM book_transaction bt 
                              JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id 
                              JOIN book b ON bc.Book_book_id = b.book_id 
                              WHERE bt.Member_user_id = $uid AND bt.status != 'Returned'";
                    $b_res = $conn->query($b_sql);
                    $b_count = ($b_res) ? $b_res->num_rows : 0;
                    $fine = number_format($row['total_fine'] ?? 0, 2);
                    ?>
                    <div class="member-card-wrapper" style="border:1px solid #eee; margin-bottom:10px; border-radius:8px;">
                        <div class="member-card" onclick="toggleDetails(this)">
                            <div class="member-main-info" style="display:flex; align-items:center; gap:10px;">
                                <div class="member-avatar"
                                    style="width:40px;height:40px;background:#eef2ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;color:#4f46e5;">
                                    <?php echo strtoupper($row['first_name'][0]); ?>
                                </div>
                                <div class="member-details">
                                    <h4 style="margin:0;font-size:14px;"><?php echo $name; ?></h4>
                                    <p style="margin:0;font-size:12px;color:#666;"><?php echo $row['email']; ?></p>
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
                            style="display:none;padding:20px;background:#fcfcfc;border-top:1px solid #eee;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                                <div>
                                    <h5 style="margin-bottom:10px;color:#888;">BORROWED ITEMS</h5>
                                    <?php if ($b_count > 0): ?>
                                        <ul style="list-style:none;padding:0;font-size:13px;">
                                            <?php while ($b = $b_res->fetch_assoc()):
                                                $isOverdue = (strtotime($b['due_date']) < time()); ?>
                                                <li style="margin-bottom:8px;padding-bottom:5px;border-bottom:1px dashed #ddd;">
                                                    <strong><?php echo htmlspecialchars($b['title']); ?></strong><br>
                                                    <small>Due: <span class="<?php echo $isOverdue ? 'text-danger' : ''; ?>">
                                                            <?php echo date('M d, Y', strtotime($b['due_date'])); ?>
                                                        </span></small>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p style="font-size:12px;color:#999;">No books currently borrowed.</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h5 style="margin-bottom:10px;color:#888;">MEMBER INFO</h5>
                                    <p style="font-size:13px;margin:4px 0;"><strong>Dept:</strong>
                                        <?php echo $row['Department']; ?></p>
                                    <p style="font-size:13px;margin:4px 0;"><strong>Course:</strong>
                                        <?php echo $row['Course']; ?>     <?php echo $row['Section']; ?></p>
                                    <p style="font-size:13px;margin:4px 0;"><strong>User ID:</strong>
                                        #<?php echo $row['user_id']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </section>
        </main>
    </div>

    <!-- ===== ADD USER MODAL ===== -->
    <div id="addUserModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h2><i class="fi fi-rr-user-add"></i> Add New User</h2>
                <button class="modal-close" onclick="closeAddUserModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="addFullName" placeholder="e.g. Juan Dela Cruz">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="addEmail" placeholder="email@school.edu">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="addPassword" placeholder="Min. 6 characters">
                    </div>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <div class="role-toggle">
                        <button type="button" class="role-btn active" id="roleStudent"
                            onclick="setRole('Student')">Student</button>
                        <button type="button" class="role-btn" id="roleFaculty"
                            onclick="setRole('Faculty')">Faculty</button>
                    </div>
                    <input type="hidden" id="addRole" value="Student">
                </div>
                <div class="form-group">
                    <label>College / Department</label>
                        <select id="addDepartment">
                            <option value="">Select college...</option>
                            <option value="CCS">CCS</option>
                            <option value="CAS">CAS</option>
                            <option value="CAMP">CAMP</option>
                            <option value="CON">CON</option>
                            <option value="CED">CED</option>
                            <option value="CCJE">CCJE</option>
                            <option value="CEA">CEA</option>
                        </select>
                    </select>
                </div>
                <div class="form-row" id="courseSection">
                    <div class="form-group">
                        <label>Course</label>
                        <input type="text" id="addCourse" placeholder="e.g. BSIT">
                    </div>
                    <div class="form-group" id="sectionGroup">
                        <label>Section</label>
                        <input type="text" id="addSection" placeholder="e.g. A">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeAddUserModal()">Cancel</button>
                <button class="btn-submit" id="addUserBtn" onclick="submitAddUser()">Add User</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <script>
        // ----- Modal open/close -----
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.add('active');
        }
        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.remove('active');
            // Reset form
            document.getElementById('addFullName').value = '';
            document.getElementById('addEmail').value = '';
            document.getElementById('addPassword').value = '';
            document.getElementById('addCourse').value = '';
            document.getElementById('addSection').value = '';
            document.getElementById('addDepartment').value = '';
            setRole('Student');
        }

        // ----- Role toggle -----
        function setRole(role) {
            document.getElementById('addRole').value = role;
            document.getElementById('roleStudent').classList.toggle('active', role === 'Student');
            document.getElementById('roleFaculty').classList.toggle('active', role === 'Faculty');
            // Hide section for Faculty (they don't typically have sections)
            document.getElementById('sectionGroup').style.opacity = role === 'Faculty' ? '0.4' : '1';
        }

        // ----- Submit -----
        async function submitAddUser() {
            const fullName = document.getElementById('addFullName').value.trim();
            const email = document.getElementById('addEmail').value.trim();
            const password = document.getElementById('addPassword').value;
            const role = document.getElementById('addRole').value;
            const department = document.getElementById('addDepartment').value;
            const course = document.getElementById('addCourse').value.trim();
            const section = document.getElementById('addSection').value.trim();

            if (!fullName || !email || !password || !department) {
                showToast('Please fill in all required fields.', 'error');
                return;
            }
            if (password.length < 6) {
                showToast('Password must be at least 6 characters.', 'error');
                return;
            }

            const btn = document.getElementById('addUserBtn');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            const formData = new FormData();
            formData.append('full_name', fullName);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('role', role);
            formData.append('department', department);
            formData.append('course', course);
            formData.append('section', section);

            try {
                const res = await fetch('add_user_handler.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showToast('User added successfully!', 'success');
                    closeAddUserModal();
                    setTimeout(() => location.reload(), 1500); // Refresh list
                } else {
                    showToast(data.message, 'error');
                }
            } catch (e) {
                showToast('Server error. Please try again.', 'error');
            }

            btn.disabled = false;
            btn.textContent = 'Add User';
        }

        // ----- Toast helper -----
        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3500);
        }

        // ----- Expand/collapse member cards -----
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

        // ----- Search filter -----
        document.getElementById('userSearch').addEventListener('input', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('.member-card-wrapper').forEach(wrapper => {
                const text = wrapper.querySelector('.member-details').innerText.toLowerCase();
                wrapper.style.display = text.includes(term) ? 'block' : 'none';
            });
        });
    </script>
</body>

</html>