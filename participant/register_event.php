<?php
include '../config/connect.php';
session_start();

// Check if user is logged in and is a participant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'participant') {
    header('Location: ../php/login.php');
    exit();
}

$participant_id = $_SESSION['user_id'];
$participant_email = $_SESSION['email']; 
$event_id = $_GET['id'] ?? null;
$success_message = '';
$error_message = '';

// Get event details
if ($event_id) {
    $query = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    if (!$event) {
        header('Location: events.php');
        exit();
    }

    // Check if already registered
    $check_query = "SELECT * FROM participants WHERE p_email = ? AND event_title = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('ss', $participant_email, $event['event_title']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        header('Location: my_events.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (empty($name) || empty($email) || empty($phone)) {
        $error_message = "All fields are required.";
    } else {
        // Insert registration
        $insert_query = "INSERT INTO participants (p_name, p_email, p_phone, event_title, created_at) 
                        VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('ssss', $name, $email, $phone, $event['event_title']);
        
        if ($insert_stmt->execute()) {
            $success_message = "Registration successful!";
            // Redirect after successful registration
            header("Location: my_events.php");
            exit();
        } else {
            $error_message = "Error registering for event. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event - EMS</title>
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
            font-size: 24px;
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

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu-item i {
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
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .calendar h3 {
            margin-bottom: 10px;
        }

        .registration-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .event-summary {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .event-title {
            font-size: 24px;
            color: #17153B;
            margin: 0;
        }

        .event-fee {
            background: #17153B;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
        }

        .event-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        .detail-item i {
            color: #17153B;
            width: 16px;
        }

        .registration-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-title {
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #17153B;
        }

        .submit-btn {
            background: #17153B;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s;
            width: 100%;
        }

        .submit-btn:hover {
            background: #2c2975;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>EMS</h2>
        <div class="menu-item">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i>
                <span>Available Events</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="my_events.php">
                <i class="fas fa-star"></i>
                <span>My Events</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="account.php">
                <i class="fas fa-user"></i>
                <span>My Account</span>
            </a><div class="calendar">
                <h3>Calendar</h3>
                <p id="currentDate"></p>
            </div>
        </div>
        <div class="menu-item">
            <a href="../php/logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Log Out</span>
            </a>
        </div>
        <div class="calendar">
            <h3>Calendar</h3>
            <p id="currentDate"></p>
        </div>
    </div>

    <div class="content">
        <div class="registration-container">
            <?php if ($event): ?>
                <div class="event-summary">
                    <div class="event-header">
                        <h1 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h1>
                        <span class="event-fee">Rs. <?php echo number_format($event['event_fee'], 2); ?></span>
                    </div>
                    <div class="event-details">
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('F j, Y', strtotime($event['start_date'])); ?>
                            <?php if ($event['start_date'] != $event['end_date']): ?>
                                - <?php echo date('F j, Y', strtotime($event['end_date'])); ?>
                            <?php endif; ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <?php echo date('g:i A', strtotime($event['start_time'])); ?> - 
                            <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($event['event_venue']); ?>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-user"></i>
                            <?php echo htmlspecialchars($event['organizer_name']); ?>
                        </div>
                    </div>
                </div>

                <div class="registration-form">
                    <h2 class="form-title">Registration Form</h2>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>

                        <button type="submit" class="submit-btn">Register for Event</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">Event not found.</div>
            <?php endif; ?>
        </div>
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
