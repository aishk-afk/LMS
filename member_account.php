<?php
session_start();
include 'db_config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user info from DB
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$full_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
$email     = htmlspecialchars($user['email']);
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
    <style>
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
            letter-spacing: 0.4px;
        }
        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
        }
        .form-group input[readonly] {
            background: #f8fafc;
            color: #94a3b8;
            cursor: not-allowed;
        }
        .form-group input:focus:not([readonly]) {
            border-color: #1e3a8a;
        }

        .divider {
            border: none;
            border-top: 1px solid #f1f5f9;
            margin: 24px 0;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }

        .btn-save-large {
            width: 100%;
            padding: 13px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn-save-large:hover { background: #1e40af; }
        .btn-save-large:disabled { opacity: 0.6; cursor: not-allowed; }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 14px 22px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            z-index: 9999;
            display: none;
        }
        .toast.success { background: #10b981; }
        .toast.error   { background: #ef4444; }

        /* Password strength indicator */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            margin-top: 6px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            transition: width 0.3s, background 0.3s;
        }
        .strength-label {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }
    </style>
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
                    <li class="nav-item active"><a href="member_account.php"><i class="fi fi-rr-user"></i> Account</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-info">
                    <strong><?php echo htmlspecialchars(($_SESSION['user_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')); ?></strong><br>
                    <small><?php echo htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Member')); ?></small>
                </div>
                <a href="index.php" class="logout-link"><i class="fi fi-rr-exit"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content flex-center">
            <div class="settings-card">
                <div class="settings-header">
                    <div class="icon-circle">
                        <i class="fi fi-rr-settings"></i>
                    </div>
                    <h1>Account Settings</h1>
                    <p>View your profile and update your password.</p>
                </div>

                <!-- Profile Info (read-only) -->
                <div class="section-title">Profile Information</div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" value="<?php echo $full_name; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?php echo $email; ?>" readonly>
                </div>

                <hr class="divider">

                <!-- Change Password -->
                <div class="section-title">Change Password</div>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" id="currentPassword" placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="newPassword" placeholder="Min. 6 characters" oninput="checkStrength(this.value)">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel"></div>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="confirmPassword" placeholder="Re-enter new password">
                </div>

                <button class="btn-save-large" id="saveBtn" onclick="changePassword()">Save Changes</button>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        function checkStrength(val) {
            const fill  = document.getElementById('strengthFill');
            const label = document.getElementById('strengthLabel');
            let strength = 0;
            if (val.length >= 6)                          strength++;
            if (val.match(/[A-Z]/))                       strength++;
            if (val.match(/[0-9]/))                       strength++;
            if (val.match(/[^A-Za-z0-9]/))               strength++;

            const levels = [
                { width: '0%',   color: '#e2e8f0', text: '' },
                { width: '25%',  color: '#ef4444', text: 'Weak' },
                { width: '50%',  color: '#f59e0b', text: 'Fair' },
                { width: '75%',  color: '#3b82f6', text: 'Good' },
                { width: '100%', color: '#10b981', text: 'Strong' },
            ];
            fill.style.width      = levels[strength].width;
            fill.style.background = levels[strength].color;
            label.textContent     = levels[strength].text;
            label.style.color     = levels[strength].color;
        }

        async function changePassword() {
            const current = document.getElementById('currentPassword').value;
            const newPw   = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;

            if (!current || !newPw || !confirm) {
                showToast('Please fill in all password fields.', 'error');
                return;
            }
            if (newPw.length < 6) {
                showToast('New password must be at least 6 characters.', 'error');
                return;
            }
            if (newPw !== confirm) {
                showToast('New passwords do not match.', 'error');
                return;
            }

            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            const formData = new FormData();
            formData.append('current_password', current);
            formData.append('new_password',     newPw);

            try {
                const res  = await fetch('change_password_handler.php', { method: 'POST', body: formData });
                const data = await res.json();
                showToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value     = '';
                    document.getElementById('confirmPassword').value = '';
                    checkStrength('');
                }
            } catch (e) {
                showToast('Server error. Please try again.', 'error');
            }

            btn.disabled = false;
            btn.textContent = 'Save Changes';
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent  = msg;
            toast.className    = `toast ${type}`;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 3500);
        }
    </script>
</body>
</html>