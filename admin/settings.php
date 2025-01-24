<?php
session_start();
include '../config/connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_password':
                if (isset($_POST['current_password']) && isset($_POST['new_password'])) {
                    $userId = $_SESSION['user_id'];
                    $currentPassword = $conn->real_escape_string($_POST['current_password']);
                    $newPassword = $conn->real_escape_string($_POST['new_password']);
                    
                    // Verify current password
                    $result = $conn->query("SELECT password FROM users WHERE id = '$userId'");
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($currentPassword, $user['password'])) {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $conn->query("UPDATE users SET password = '$hashedPassword' WHERE id = '$userId'");
                        $message = "Password updated successfully!";
                    } else {
                        $error = "Current password is incorrect!";
                    }
                }
                break;
            
            case 'update_email':
                if (isset($_POST['new_email'])) {
                    $userId = $_SESSION['user_id'];
                    $newEmail = $conn->real_escape_string($_POST['new_email']);
                    
                    $conn->query("UPDATE users SET email = '$newEmail' WHERE id = '$userId'");
                    $message = "Email updated successfully!";
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            display: flex;
            background-color: #f5f6fa;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #2c3e50;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            color: white;
            margin-bottom: 30px;
            text-align: center;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .menu-item:hover {
            background-color: #34495e;
        }

        .menu-item.active {
            background-color: #3498db;
        }

        .menu-item img {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .menu-item span {
            color: white;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .settings-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .settings-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/dashboard.png" alt="Dashboard">
            <span><a href="dashboard.php">Dashboard</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/conference.png" alt="Users">
            <span><a href="users.php">Manage Users</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/calendar.png" alt="Events">
            <span><a href="events.php">Manage Events</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/report-card.png" alt="Reports">
            <span><a href="reports.php">Reports</a></span>
        </div>
        <div class="menu-item active">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/settings.png" alt="Settings">
            <span><a href="settings.php">Settings</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/logout-rounded.png" alt="Logout">
            <span><a href="../php/logout.php">Logout</a></span>
        </div>
    </div>

    <div class="content">
        <div class="header">
            <h1>Settings</h1>
        </div>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="settings-container">
            <div class="settings-card">
                <h3>Change Password</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_password">
                    <div class="form-group">
                        <label>Current Password:</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password:</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <button type="submit">Update Password</button>
                </form>
            </div>

            <div class="settings-card">
                <h3>Update Email</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_email">
                    <div class="form-group">
                        <label>New Email:</label>
                        <input type="email" name="new_email" required>
                    </div>
                    <button type="submit">Update Email</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
