<?php
session_start();
include '../config/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Management System</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/login.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&display=swap" />
</head>
<body>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_email = $_POST['email'];
        $user_password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($user_password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user_email;
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } elseif ($user['role'] === 'organizer') {
                    header("Location: ../organizer/dashboard.php");
                } else {
                    header("Location: ../participant/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Email not found";
        }
    }
    ?>

    <div class="login-page">
        <div class="group-parent" id="groupContainer">
            <div class="sign-into-your-account-parent">
                <div class="sign-into-your">Sign into your account</div>
                <div class="no-account-r-wrapper">
                    <div class="if-you-dont-container">
                        <span class="if-you-dont">If you don't have an account registered, you can </span>
                        <a href="signup.php" style="color: #2e236c;"><b>Register here!</b></a>
                    </div>
                </div>
            </div>
            <div class="group-container">
                <form method="POST" action="login.php">
                    <div class="instance-parent">
                        <div class="email-parent">
                            <label for="email"><b>Email</b></label>
                            <input type="email" id="email" name="email" class="enter-email" placeholder="Enter your email address" required>
                            <img class="message-icon" alt="message box icon" src="../assets/img/message 1.svg">
                        </div>

                        <div class="password-parent">
                            <label for="password"><b>Password</b></label>
                            <input type="password" id="password" name="password" class="enter-password" placeholder="Enter your password" required>
                            <img class="padlock-icon" alt="padlock icon" src="../assets/img/padlock 1.svg">
                            <img class="invisible-icon" alt="show icon" id="togglePassword" src="../assets/img/eye-icon.svg" style="cursor: pointer;">
                        </div>
                    </div>

                    <!--<div class="rectangle-parent">
                        <div class="forgot-password">
                            <a href="">Forgot Password?</a>
                        </div>
                    </div>
                    -->
                    <div class="rectangle-group" id="groupContainer2">
                        <button type="submit" class="login-btn" id="login-btn">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    </script>

</body>
</html>
