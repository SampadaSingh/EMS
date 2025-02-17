<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/global.css" />
    <link rel="stylesheet" href="../css/signup.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500&display=swap" />
    <title>Sign Up</title>
</head>

<body>
    <?php
    session_start();
    include('../config/connect.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $full_name = $_POST["full_name"];
        $email = $_POST["email"];
        $username = $_POST["username"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $phone = $_POST["phone"];
        $role = $_POST["role"];

        $emailPattern = "/^[^\s@]+@[^\s@]+\.[^\s@]+$/";
        $passwordPattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";

        if (!preg_match($emailPattern, $email)) {
            die("Invalid email format.");
        }

        if (!preg_match($passwordPattern, $password)) {
            die("Password must be at least 8 characters long, contain a letter, a digit, and a special character.");
        }

        if ($password !== $confirm_password) {
            die("Passwords do not match.");
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, username, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $full_name, $email, $username, $password_hash, $phone, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!');</script>";
            echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 2000);</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
    ?>
    <div class="register">
        <div class="registration-parent">
            <div class="registration">Registration</div>

            <!-- Form submission-->
            <form id="signup-form" action="signup.php" method="POST">
                <div class="input-parent">
                    <label for="full-name">Full Name:</label>
                    <input type="text" id="full-name" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="input-parent">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                </div>

                <div class="input-parent">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username (optional)" required pattern="[A-Za-z0-9_]+" title="Username should only contain letters, numbers and underscores">
                </div>

                <div class="input-parent">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                </div>

                <div class="input-parent">
                    <label for="confirm-password">Confirm Password:</label>
                    <input type="password" id="confirm-password" name="confirm_password" placeholder="Re-enter your password" required>
                </div>

                <div class="input-parent">
                    <label for="phone">Phone Number (optional):</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                </div>

                <div class="input-parent">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="participant">Participant</option>
                        <option value="organizer">Organizer</option>
                    </select><br>
                </div>

                <button type="submit" id="signUp-btn">Sign Up</button>
            </form>

            <p id="error-message" style="color: red; display: none;"></p>
        </div>
    </div>

    <script>
        document.getElementById("signup-form").addEventListener("submit", function(event) {
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;
            const errorMessage = document.getElementById("error-message");

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!emailPattern.test(email)) {
                errorMessage.textContent = "Please enter a valid email address.";
                errorMessage.style.display = "block";
                event.preventDefault();
                return;
            }

            if (!passwordPattern.test(password)) {
                alert('Password must be at least 8 characters long, contain a letter, a digit, and a special character.');
                event.preventDefault();
                return;
            }

            if (password !== confirmPassword) {
                errorMessage.textContent = "Passwords do not match. Please try again.";
                errorMessage.style.display = "block";
                event.preventDefault();
                return;
            }

            errorMessage.style.display = "none";
        });
    </script>
</body>

</html>