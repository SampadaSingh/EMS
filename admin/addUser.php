<?php
session_start();
include '../config/connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($_POST['role']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format";
    } else {
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
        
        if ($conn->query($sql)) {
            $_SESSION['success_message'] = "User added successfully!";
            header('Location: manageUsers.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Error adding user: " . $conn->error;
        }
    }
}

// Get any messages
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear the messages
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
        }

        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-container {
            display: flex;
            gap: 10px;
        }

        .submit-btn {
            padding: 10px 20px;
            background-color: rgb(81, 64, 179);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: rgb(75, 64, 141);
        }

        .cancel-btn {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
        }

        .error {
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .success-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
    <script>
        function validateForm() {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Username validation (letters and spaces only)
            const nameRegex = /^[A-Za-z\s]+$/;
            if (!nameRegex.test(username)) {
                alert('Username should only contain letters and spaces');
                return false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return false;
            }

            // Password validation (minimum 6 characters)
            if (password.length < 6) {
                alert('Password must be at least 6 characters long');
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1>Add User</h1>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form action="" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="username">Username*</label>
                    <input type="text" id="username" name="username" required pattern="[A-Za-z\s]+" title="Username should only contain letters and spaces">
                </div>

                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password*</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="role">Role*</label>
                    <select id="role" name="role" required>
                        <option value="participant">Participant</option>
                        <option value="organizer">Organizer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="btn-container">
                    <button type="submit" class="submit-btn">Add User</button>
                    <a href="manageUsers.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
