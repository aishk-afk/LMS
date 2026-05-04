<?php
session_start();
include 'db_config.php';

$sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.user_type, 
               m.Department, m.Course, m.Section, m.Member_Role,
               (SELECT SUM(balance) FROM fine WHERE Member_user_id = u.user_id) AS total_fine
        FROM user u
        JOIN member m ON u.user_id = m.user_id
        WHERE u.user_type = 'Member' AND m.Member_Role IN ('Student', 'Faculty')
        ORDER BY u.user_id DESC";

$result = $conn->query($sql);
if (!$result) die("Database error: " . $conn->error);
$total_members = $result->num_rows;
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
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <style>
        * { box-sizing: border-box; }
        .main-content { background: #f8fafc; padding: 30px; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-header h1 { font-size: 1.8rem; color: #0f172a; margin: 0; }
        .page-header p  { color: #64748b; margin: 4px 0 0; font-size: 14px; }

        .btn-add-user {
            background: #1e3a8a; color: white; border: none;
            padding: 11px 22px; border-radius: 50px; font-size: 14px;
            font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;
        }
        .btn-add-user:hover { background: #1e40af; }

        .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .search-box {
            flex: 1; display: flex; align-items: center; gap: 10px;
            background: white; border: 1px solid #e2e8f0; border-radius: 50px; padding: 10px 18px;
        }
        .search-box i { color: #94a3b8; font-size: 14px; }
        .search-box input { border: none; outline: none; font-size: 14px; width: 100%; background: transparent; }

        .filter-tabs { display: flex; background: white; border: 1px solid #e2e8f0; border-radius: 50px; overflow: hidden; }
        .filter-tab {
            padding: 10px 18px; font-size: 13px; font-weight: 600; border: none;
            background: none; cursor: pointer; color: #64748b;
        }
        .filter-tab.active { background: #1e3a8a; color: white; border-radius: 50px; }
        .result-count { font-size: 13px; color: #94a3b8; white-space: nowrap; }

        /* Member rows */
        .member-row { background: white; border: 1px solid #e8edf5; border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
        .member-summary {
            display: grid;
            grid-template-columns: 2.2fr 1.4fr 0.8fr 0.8fr 0.8fr 0.8fr auto;
            align-items: center; padding: 16px 20px; cursor: pointer; gap: 10px;
        }
        .member-identity { display: flex; align-items: center; gap: 12px; }
        .avatar {
            width: 42px; height: 42px; border-radius: 50%; background: #eef2ff;
            color: #4f46e5; font-weight: 700; font-size: 16px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .member-name  { font-size: 14px; font-weight: 600; color: #0f172a; margin: 0; }
        .member-email { font-size: 12px; color: #94a3b8; margin: 2px 0 0; }

        .col-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; display: block; margin-bottom: 3px; }
        .col-value  { font-size: 13px; font-weight: 600; color: #1e293b; }

        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; }
        .badge-student { background: #ede9fe; color: #7c3aed; }
        .badge-faculty { background: #dbeafe; color: #1d4ed8; }
        .badge-overdue { background: #fee2e2; color: #dc2626; }
        .badge-ontime  { background: #dcfce7; color: #16a34a; }
        .badge-count   { background: #f1f5f9; color: #475569; }

        .fine-danger { color: #dc2626; font-weight: 700; }
        .fine-clear  { color: #94a3b8; }
        .expand-icon { color: #cbd5e1; transition: transform 0.3s; font-size: 18px; }

        /* Expanded panel */
        .member-details-panel { display: none; border-top: 1px solid #f1f5f9; background: #f8fafc; }
        .details-header {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 110px;
            padding: 10px 20px; background: #f1f5f9; border-bottom: 1px solid #e2e8f0;
        }
        .details-header span { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.5px; }
        .book-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr 110px;
            padding: 14px 20px; align-items: center; border-bottom: 1px dashed #e2e8f0; font-size: 13px;
        }
        .book-row:last-child { border-bottom: none; }
        .book-title  { font-weight: 600; color: #0f172a; }
        .no-books    { padding: 20px; text-align: center; color: #94a3b8; font-size: 13px; }

        .member-info-footer {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0;
        }
        .info-chips { display: flex; gap: 10px; flex-wrap: wrap; }
        .info-chip  { background: white; border: 1px solid #e2e8f0; border-radius: 50px; padding: 4px 12px; font-size: 12px; color: #475569; }
        .overdue-summary { color: #dc2626; font-size: 13px; font-weight: 600; }
        .fine-summary    { color: #dc2626; font-size: 13px; font-weight: 700; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: white; border-radius: 16px; width: 90%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden; }
        .modal-header { background: #1e3a8a; color: white; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { margin: 0; font-size: 1.1rem; }
        .modal-close { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
        .modal-body { padding: 25px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 6px; letter-spacing: 0.5px; }
        .form-group input, .form-group select { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus { border-color: #1e3a8a; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .role-toggle { display: flex; gap: 8px; }
        .role-btn { flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; color: #64748b; font-weight: 600; cursor: pointer; }
        .role-btn.active { background: #1e3a8a; color: white; border-color: #1e3a8a; }
        .modal-footer { padding: 16px 25px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 10px; }
        .btn-cancel { padding: 10px 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; cursor: pointer; font-weight: 600; color: #64748b; }
        .btn-submit { padding: 10px 24px; background: #1e3a8a; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

        .toast { position: fixed; bottom: 30px; right: 30px; padding: 14px 20px; border-radius: 10px; color: white; font-weight: 600; z-index: 9999; display: none; }
        .toast.success { background: #10b981; }
        .toast.error   { background: #ef4444; }
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
                <li class="nav-item active"><a href="admin_users.php"><i class="fi fi-rr-users-alt"></i> Users</a></li>
                <li class="nav-item"><a href="admin_waitlist.php"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
                <li class="nav-item"><a href="admin_settings.php"><i class="fi fi-rr-settings"></i> Fine Settings</a></li>
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
        <div class="page-header">
            <div>
                <h1>Library Members</h1>
                <p>Manage users, view borrowed books, and issue returns.</p>
            </div>
            <button class="btn-add-user" onclick="openAddUserModal()">
                <i class="fi fi-rr-user-add"></i> Add User
            </button>
        </div>

        <div class="toolbar">
            <div class="search-box">
                <i class="fi fi-rr-search"></i>
                <input type="text" id="userSearch" placeholder="Search by name, email, college, or section...">
            </div>
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All</button>
                <button class="filter-tab" data-filter="Student">Student</button>
                <button class="filter-tab" data-filter="Faculty">Faculty</button>
            </div>
            <span class="result-count" id="resultCount"><?php echo $total_members; ?> results</span>
        </div>

        <div id="membersList">
        <?php
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()):
            $uid  = $row['user_id'];
            $name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
            $role = $row['Member_Role'];

            $b_sql = "SELECT bt.borrow_id, bt.borrow_date, bt.due_date, bt.status, b.title, b.book_id
            FROM book_transaction bt
            JOIN book_copy bc ON bt.Book_Copy_copy_id = bc.copy_id
            JOIN book b ON bc.Book_book_id = b.book_id
            WHERE bt.Member_user_id = $uid 
            AND bt.status != 'Returned'
            AND b.book_id NOT IN (
                SELECT Book_book_id FROM waitlist WHERE Member_user_id = $uid
            )";
            $b_res = $conn->query($b_sql);

            $overdue_count = 0;
            $books = [];
            if ($b_res) {
                while ($b = $b_res->fetch_assoc()) {
                    $b['is_overdue'] = strtotime($b['due_date']) < time();
                    if ($b['is_overdue']) $overdue_count++;
                    $books[] = $b;
                }
            }
            $b_count  = count($books);
            $fine     = $row['total_fine'] ?? 0;
            $fine_fmt = number_format($fine, 2);
            $dept     = htmlspecialchars($row['Department'] ?? '');
            $course   = htmlspecialchars($row['Course'] ?? '');
            $section  = htmlspecialchars($row['Section'] ?? '');
        ?>
        <div class="member-row" data-role="<?php echo $role; ?>" data-search="<?php echo strtolower($name . ' ' . $row['email'] . ' ' . $dept . ' ' . $section); ?>">
            <div class="member-summary" onclick="toggleRow(this)">
                <div class="member-identity">
                    <div class="avatar"><?php echo strtoupper($row['first_name'][0]); ?></div>
                    <div>
                        <p class="member-name"><?php echo $name; ?></p>
                        <p class="member-email"><?php echo htmlspecialchars($row['email']); ?></p>
                    </div>
                </div>
                <div>
                    <span class="col-label">College &amp; Section</span>
                    <span class="col-value"><?php echo $dept ?: '—'; ?><?php echo $section ? ' · Sec ' . $section : ''; ?></span>
                </div>
                <div>
                    <span class="col-label">Role</span>
                    <span class="badge <?php echo $role === 'Faculty' ? 'badge-faculty' : 'badge-student'; ?>"><?php echo $role; ?></span>
                </div>
                <div>
                    <span class="col-label">Borrowed</span>
                    <?php if ($b_count > 0): ?>
                        <span class="badge badge-count"><i class="fi fi-rr-book-alt"></i> <?php echo $b_count; ?></span>
                    <?php else: ?>
                        <span class="col-value" style="color:#94a3b8;">None</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="col-label">Overdue</span>
                    <?php if ($overdue_count > 0): ?>
                        <span class="badge badge-overdue"><i class="fi fi-rr-clock"></i> <?php echo $overdue_count; ?> overdue</span>
                    <?php else: ?>
                        <span class="col-value" style="color:#16a34a; font-size:13px;">On time</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="col-label">Fine Balance</span>
                    <span class="col-value <?php echo $fine > 0 ? 'fine-danger' : 'fine-clear'; ?>">₱<?php echo $fine_fmt; ?></span>
                </div>
                <div style="display:flex;align-items:center;justify-content:flex-end;">
                    <i class="fi fi-rr-angle-small-down expand-icon"></i>
                </div>
            </div>

            <div class="member-details-panel">
                <?php if ($b_count > 0): ?>
                <div class="details-header">
                    <span>Book</span><span>Borrow Date</span><span>Due Date</span>
                    <span>Status</span><span>Fine</span><span></span><span>Action</span>
                </div>
                <?php foreach ($books as $b): ?>
                <div class="book-row">
                    <div class="book-title"><?php echo htmlspecialchars($b['title']); ?></div>
                    <div style="color:#64748b;"><?php echo $b['borrow_date'] ? date('M d, Y', strtotime($b['borrow_date'])) : '—'; ?></div>
                    <div style="color:<?php echo $b['is_overdue'] ? '#dc2626' : '#16a34a'; ?>;font-weight:600;">
                        <?php echo date('M d, Y', strtotime($b['due_date'])); ?>
                    </div>
                    <div>
                        <?php if ($b['is_overdue']): ?>
                            <span class="badge badge-overdue">Overdue</span>
                        <?php else: ?>
                            <span class="badge badge-ontime">On Time</span>
                        <?php endif; ?>
                    </div>
                    <div style="color:<?php echo $b['is_overdue'] ? '#dc2626' : '#94a3b8'; ?>;font-weight:600;">
                        <?php echo $b['is_overdue'] ? '₱—' : 'None'; ?>
                    </div>
                    <div></div>
                    <div>
                        <button onclick="returnBook(<?php echo $b['borrow_id']; ?>, this)" 
                            style="background:#1e3a8a;color:white;border:none;padding:6px 14px;border-radius:50px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;">
                            <i class="fi fi-rr-undo"></i> Return
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="no-books">No books currently borrowed.</div>
                <?php endif; ?>

                <div class="member-info-footer">
                    <div class="info-chips">
                        <span class="info-chip"><strong>ID:</strong> #<?php echo $uid; ?></span>
                        <span class="info-chip"><strong>Dept:</strong> <?php echo $dept ?: '—'; ?></span>
                        <span class="info-chip"><strong>Course:</strong> <?php echo ($course ?: '—') . ($section ? ' Sec '.$section : ''); ?></span>
                    </div>
                    <div style="display:flex;gap:16px;align-items:center;">
                        <?php if ($overdue_count > 0): ?>
                            <span class="overdue-summary">⚠ <?php echo $overdue_count; ?> overdue item<?php echo $overdue_count > 1 ? 's' : ''; ?></span>
                        <?php endif; ?>
                        <?php if ($fine > 0): ?>
                            <span class="fine-summary">Total fines: ₱<?php echo $fine_fmt; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        </div>
    </main>
</div>

<!-- ADD USER MODAL -->
<div id="addUserModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h2>Add New User</h2>
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
                    <button type="button" class="role-btn active" id="roleStudent" onclick="setRole('Student')">Student</button>
                    <button type="button" class="role-btn" id="roleFaculty" onclick="setRole('Faculty')">Faculty</button>
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
            </div>
            <div class="form-row">
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

<div class="toast" id="toast"></div>

<script>
    function toggleRow(el) {
        const panel = el.nextElementSibling;
        const icon  = el.querySelector('.expand-icon');
        const open  = panel.style.display === 'block';
        panel.style.display   = open ? 'none' : 'block';
        icon.style.transform  = open ? 'rotate(0deg)' : 'rotate(180deg)';
        el.style.background   = open ? '' : '#f8faff';
    }

    const searchInput = document.getElementById('userSearch');
    const filterTabs  = document.querySelectorAll('.filter-tab');
    const resultCount = document.getElementById('resultCount');
    let activeFilter  = 'all';

    searchInput.addEventListener('input', applyFilters);
    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            activeFilter = tab.dataset.filter;
            applyFilters();
        });
    });

    function applyFilters() {
        const term = searchInput.value.toLowerCase();
        let visible = 0;
        document.querySelectorAll('.member-row').forEach(row => {
            const show = row.dataset.search.includes(term) && (activeFilter === 'all' || row.dataset.role === activeFilter);
            row.style.display = show ? 'block' : 'none';
            if (show) visible++;
        });
        resultCount.textContent = visible + ' results';
    }

    function openAddUserModal()  { document.getElementById('addUserModal').classList.add('active'); }
    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.remove('active');
        ['addFullName','addEmail','addPassword','addCourse','addSection'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('addDepartment').value = '';
        setRole('Student');
    }
    function setRole(role) {
        document.getElementById('addRole').value = role;
        document.getElementById('roleStudent').classList.toggle('active', role === 'Student');
        document.getElementById('roleFaculty').classList.toggle('active', role === 'Faculty');
        document.getElementById('sectionGroup').style.opacity = role === 'Faculty' ? '0.4' : '1';
    }
    async function submitAddUser() {
        const fullName = document.getElementById('addFullName').value.trim();
        const email    = document.getElementById('addEmail').value.trim();
        const password = document.getElementById('addPassword').value;
        const role     = document.getElementById('addRole').value;
        const dept     = document.getElementById('addDepartment').value;
        const course   = document.getElementById('addCourse').value.trim();
        const section  = document.getElementById('addSection').value.trim();

        if (!fullName || !email || !password || !dept) { showToast('Please fill in all required fields.', 'error'); return; }
        if (password.length < 6) { showToast('Password must be at least 6 characters.', 'error'); return; }

        const btn = document.getElementById('addUserBtn');
        btn.disabled = true; btn.textContent = 'Saving...';

        const fd = new FormData();
        fd.append('full_name', fullName); fd.append('email', email); fd.append('password', password);
        fd.append('role', role); fd.append('department', dept); fd.append('course', course); fd.append('section', section);

        try {
            const res  = await fetch('add_user_handler.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { showToast('User added successfully!', 'success'); closeAddUserModal(); setTimeout(() => location.reload(), 1500); }
            else showToast(data.message, 'error');
        } catch(e) { showToast('Server error. Please try again.', 'error'); }

        btn.disabled = false; btn.textContent = 'Add User';
    }
    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        t.textContent = msg; t.className = `toast ${type}`; t.style.display = 'block';
        setTimeout(() => { t.style.display = 'none'; }, 3500);
    }
    async function returnBook(borrowId, btn) {
    if (!confirm('Mark this book as returned?')) return;

    btn.disabled = true;
    btn.textContent = 'Processing...';

    const fd = new FormData();
    fd.append('borrow_id', borrowId);

    try {
        const res  = await fetch('return_book_handler.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Return';
        }
    } catch(e) {
        showToast('Server error.', 'error');
        btn.disabled = false;
    }
}
</script>
</body>
</html>