<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// default
$user = [
    'full_name' => '',
    'email' => '',
    'phone' => ''
];

$query = "SELECT full_name, email, phone FROM users WHERE id = ? AND role = 'organizer'";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $error_message = "Unable to fetch user data. Please try again later.";
    }
    $stmt->close();
} else {
    $error_message = "Database error. Please try again later.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($full_name) || empty($email) || empty($phone)) {
        $error_message = "Name, email, and phone are required fields.";
    } else {
        $conn->begin_transaction();
        try {
            $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ? AND role = 'organizer'";
            if ($update_stmt = $conn->prepare($update_query)) {
                $update_stmt->bind_param("sssi", $full_name, $email, $phone, $organizer_id);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                throw new Exception("Error updating profile information.");
            }

            if (!empty($current_password)) {
                // Verify current password
                $verify_query = "SELECT password FROM users WHERE id = ? AND role = 'organizer'";
                if ($verify_stmt = $conn->prepare($verify_query)) {
                    $verify_stmt->bind_param("i", $organizer_id);
                    $verify_stmt->execute();
                    $verify_result = $verify_stmt->get_result();
                    
                    if ($verify_result && $verify_result->num_rows > 0) {
                        $current_hash = $verify_result->fetch_assoc()['password'];

                        if (password_verify($current_password, $current_hash)) {
                            if (empty($new_password) || empty($confirm_password)) {
                                throw new Exception("New password and confirmation are required.");
                            }
                            if ($new_password !== $confirm_password) {
                                throw new Exception("New passwords do not match.");
                            }
                            if (strlen($new_password) < 6) {
                                throw new Exception("Password must be at least 6 characters long.");
                            }

                            // Update password
                            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $password_query = "UPDATE users SET password = ? WHERE id = ? AND role = 'organizer'";
                            if ($password_stmt = $conn->prepare($password_query)) {
                                $password_stmt->bind_param("si", $password_hash, $organizer_id);
                                $password_stmt->execute();
                                $password_stmt->close();
                            } else {
                                throw new Exception("Error updating password.");
                            }
                        } else {
                            throw new Exception("Current password is incorrect.");
                        }
                    }
                    $verify_stmt->close();
                } else {
                    throw new Exception("Error verifying current password.");
                }
            }

            $conn->commit();
            $success_message = "Profile updated successfully!";

            // Refresh user data
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $organizer_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $error_message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - EMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f5f7fa;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 270px;
            background-color: #17153B;
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
        }

        .menu-item {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .menu-item img {
            width: 24px;
            height: 24px;
            margin-right: 15px;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
        }

        .content {
            flex-grow: 1;
            margin-left: 300px;
            padding: 40px;
        }
        
        .calendar {
            margin-top: 40px;
            padding: 20px;
            background-color: #2a2679;
            border-radius: 10px;
        }

        .calendar h3 {
            margin-bottom: 10px;
        }

        .header {
            margin-bottom: 30px;
        }

        .profile-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #17153B;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #17153B;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #433D8B;
        }

        .password-note {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 20px;
        }

        .submit-btn {
            background: #433D8B;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar h2, .menu-item span {
                display: none;
            }

            .content {
                margin-left: 80px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <h1>My Account</h1>
        </div>

        <?php if ($success_message): ?>
            <div class="message success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form class="profile-form" method="POST" action="">
            <div class="form-section">
                <h3>Profile Information</h3>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>" 
                           required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" 
                           required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" 
                           required>
                </div>
            </div>

            <div class="form-section">
                <h3>Change Password</h3>
                <p class="password-note">Leave password fields empty if you don't want to change it</p>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <button type="submit" class="submit-btn">Save Changes</button>
        </form>
    </div>
    <script>
        function updateCalendar() {
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const today = new Date().toLocaleDateString('en-US', options);
            document.getElementById('currentDate').innerText = today;
        }
        updateCalendar();
    </script>
</body>
</html>
