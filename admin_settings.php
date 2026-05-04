<?php
session_start();
require_once 'db_config.php';

// 1. Listen for the Save Button click
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {

    // 2. Define exactly which keys we are allowed to update
    $settings_to_update = [
        'rate_standard',
        'rate_high',
        'premium_rate_percent',
        'admin_fee',
        'fine_cap_percent'
    ];

    $success = true;

    foreach ($settings_to_update as $key) {
        if (!empty($_POST[$key])) { // Only update if the admin actually typed something!
            $value = $_POST[$key];
            $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        }
    }

    if ($success) {
        $msg = "Configuration saved successfully!";
    } else {
        $msg = "Error: Could not update some settings.";
    }
}

// 4. Fetch the latest values so the text boxes show the CURRENT data
$current_configs = [];
$res = $conn->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $res->fetch_assoc()) {
    $current_configs[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Settings</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <style>
        /* --- SETTINGS SPECIFIC STYLES --- */
        .settings-container {
            max-width: 900px;
            margin-top: 20px;
        }

        .config-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .config-header {
            background: #1e40af;
            /* Deep Blue from Figma */
            padding: 24px 32px;
            display: flex;
            align-items: center;
            gap: 16px;
            color: white;
        }

        .config-header i {
            font-size: 24px;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
        }

        .config-header h3 {
            margin: 0;
            font-size: 20px;
        }

        .config-header p {
            margin: 4px 0 0;
            font-size: 14px;
            opacity: 0.8;
        }

        .config-body {
            padding: 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .input-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .input-label-row label {
            font-weight: 700;
            color: #1e293b;
            font-size: 14px;
        }

        .unit-label {
            font-size: 12px;
            color: #94a3b8;
        }

        .custom-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-prefix {
            position: absolute;
            left: 16px;
            color: #64748b;
            font-weight: 500;
        }

        .custom-input {
            width: 100%;
            padding: 14px 16px 14px 40px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            color: #1e293b;
            font-weight: 600;
            transition: all 0.2s;
        }

        .custom-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .input-help-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .config-footer {
            padding: 0 32px 32px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-save-config {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-save-config:hover {
            background: #1d4ed8;
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
                    <li class="nav-item"><a href="admin_waitlist.php"><i class="fi fi-rr-clock"></i> Waitlist</a></li>
                    <li class="nav-item active"><a href="admin_settings.php"><i class="fi fi-rr-settings"></i>
                            Fine Settings</a></li>
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
            <header class="overview-header" style="margin-bottom: 30px;">
                <h1 style="font-size: 28px; margin: 0;">System Configuration</h1>
                <p style="color: #64748b; margin: 4px 0 0;">Manage global library rules and fine rates[cite: 5].</p>
                <?php if (isset($success_msg)): ?>
                    <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-top: 15px; font-weight: 600;">
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
            </header>

            <form action="admin_settings.php" method="POST" class="settings-container">

                <!-- CARD 1: OVERDUE DAILY RATES -->
                <!-- CARD 1: OVERDUE DAILY RATES -->
                <div class="config-card">
                    <div class="config-header">
                        <i class="fi fi-rr-hourglass-end"></i>
                        <div>
                            <h3>Overdue Daily Rates</h3>
                            <p>Daily penalties based on book market value.</p>
                        </div>
                    </div>
                    <div class="config-body">
                        <div class="input-group">
                            <div class="input-label-row">
                                <label>Standard Rate <span class="tier-indicator">Under ₱500</span></label>
                                <span class="unit-label">₱ / Day</span>
                            </div>
                            <div class="custom-input-wrapper">
                                <span class="input-prefix">₱</span>
                                <input type="number" name="rate_standard" class="custom-input" placeholder="e.g. 2.00">
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-label-row">
                                <label>High-Value Rate <span class="tier-indicator">Up to ₱10,000</span></label>
                                <span class="unit-label">₱ / Day</span>
                            </div>
                            <div class="custom-input-wrapper">
                                <span class="input-prefix">₱</span>
                                <input type="number" name="rate_high" class="custom-input" placeholder="e.g. 10.00">
                            </div>
                        </div>
                        <div class="input-group">
                            <div class="input-label-row">
                                <label>Premium Rate (%) <span class="tier-indicator">Over ₱10,000</span></label>
                                <span class="unit-label">% / Day</span>
                            </div>
                            <div class="custom-input-wrapper">
                                <span class="input-prefix">%</span>
                                <input type="number" step="0.001" name="premium_rate_percent" class="custom-input" placeholder="e.g. 0.001">
                            </div>
                            <small class="input-help-text">Percentage of price per day (0.001 = 0.1%).</small>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: LOST & DAMAGE FEES -->
                <div class="config-card">
                    <div class="config-header" style="background: #1e293b;">
                        <i class="fi fi-rr-box-open"></i>
                        <div>
                            <h3>Lost & Damage Fees</h3>
                            <p>Handling fees and replacement logic.</p>
                        </div>
                    </div>
                    <div class="config-body">
                        <div class="input-group">
                            <div class="input-label-row">
                                <label>Lost Book Admin Fee</label>
                                <span class="unit-label">Flat Fee</span>
                            </div>
                            <div class="custom-input-wrapper">
                                <span class="input-prefix">₱</span>
                                <input type="number" name="admin_fee" class="custom-input" placeholder="e.g. 50">
                            </div>
                        </div>

                        <div class="input-group">
                            <div class="input-label-row">
                                <label>Base Repair Fee</label>
                                <span class="unit-label">₱ / Repair</span>
                            </div>
                            <div class="custom-input-wrapper">
                                <span class="input-prefix">₱</span>
                                <input type="number" name="base_repair_fee" class="custom-input" placeholder="e.g. 25">
                            </div>
                            <small class="input-help-text">Standard cost for minor physical repairs.</small>
                        </div>

                        <div class="input-group">
                            <div class="input-label-row">
                                <label>Overdue Fine Cap</label>
                                <span class="unit-label">Max %</span>
                            </div>
                            <div class="custom-input-wrapper">
                                <span class="input-prefix">%</span>
                                <input type="number" step="0.01" name="fine_cap_percent" class="custom-input" placeholder="e.g. 0.20">
                            </div>
                            <small class="input-help-text">Max late fee relative to book price (0.20 = 20%).</small>
                        </div>
                    </div>
                </div>

                <div class="config-footer">
                    <button type="submit" name="save_settings" class="btn-save-config">
                        <i class="fi fi-rr-disk"></i>
                        Save Configuration
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>

</html>
