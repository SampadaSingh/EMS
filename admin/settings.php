<?php
session_start();
include '../config/connect.php';

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
            background-color: rgb(81, 64, 179);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: rgb(75, 64, 141);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
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
