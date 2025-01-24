<?php
include '../config/connect.php';
session_start();

// Check if user is logged in and is a participant
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'participant') {
    header('Location: ../php/login.php');
    exit();
}

// Get event ID from URL
if (!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$event_id = $_GET['id'];
$participant_email = $_SESSION['email'];

// Get event details
$event_query = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($event_query);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: events.php');
    exit();
}

$event = $result->fetch_assoc();

// Check if user is already registered
$registration_query = "SELECT * FROM participants WHERE event_title = ? AND p_email = ?";
$reg_stmt = $conn->prepare($registration_query);
$reg_stmt->bind_param('ss', $event['event_title'], $participant_email);
$reg_stmt->execute();
$is_registered = $reg_stmt->get_result()->num_rows > 0;

// Get total registrations for this event
$total_registrations_query = "SELECT COUNT(*) as total FROM participants WHERE event_title = ?";
$total_stmt = $conn->prepare($total_registrations_query);
$total_stmt->bind_param('s', $event['event_title']);
$total_stmt->execute();
$total_registrations = $total_stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_title']); ?> - EMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #17153B;
            color: white;
            position: fixed;
            height: 100vh;
            padding: 20px;
        }

        .logo {
            font-size: 24px;
            text-align: center;
            margin-bottom: 40px;
        }

        .nav-links {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        .calendar {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .calendar h3 {
            margin-bottom: 10px;
            font-size: 18px;
            color: white;
        }

        .calendar p {
            color: #ddd;
            font-size: 14px;
        }

        /* Event Details Styles */
        .event-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .event-header {
            padding: 30px;
            background: #17153B;
            color: white;
        }

        .event-title {
            font-size: 28px;
            margin-bottom: 15px;
        }

        .event-meta {
            display: flex;
            gap: 20px;
            color: #ddd;
            font-size: 14px;
        }

        .event-meta i {
            margin-right: 5px;
        }

        .event-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .event-content {
            padding: 30px;
        }

        .event-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .event-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .detail-item i {
            color: #47338f;
            width: 20px;
        }

        .detail-label {
            color: #666;
            font-size: 14px;
        }

        .detail-value {
            color: #333;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .register-btn, .back-btn {
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .register-btn {
            background-color: #47338f;
            color: white;
        }

        .register-btn:hover {
            background-color: #372670;
        }

        .register-btn.disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .back-btn {
            background-color: #e9ecef;
            color: #333;
        }

        .back-btn:hover {
            background-color: #dde2e6;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .event-details-grid {
                grid-template-columns: 1fr;
            }
            .event-meta {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="logo">EMS</div>
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="events.php" class="nav-link">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="my_events.php" class="nav-link">
                        <i class="fas fa-star"></i>
                        <span>My Events</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="account.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>My Account</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../php/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Log Out</span>
                    </a>
                </li>
            </ul>
            <div class="calendar">
                <h3>Calendar</h3>
                <p id="currentDate"></p>
            </div>
        </div>

        <div class="main-content">
            <div class="event-container">
                <div class="event-header">
                    <h1 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h1>
                    <div class="event-meta">
                        <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($event['start_date'])); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?></span>
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['event_venue']); ?></span>
                    </div>
                </div>

                <?php if ($event['event_image']): ?>
                    <img src="../assets/uploads/<?php echo htmlspecialchars($event['event_image']); ?>" 
                         alt="<?php echo htmlspecialchars($event['event_title']); ?>" 
                         class="event-image">
                <?php endif; ?>

                <div class="event-content">
                    <div class="event-description">
                        <?php echo nl2br(htmlspecialchars($event['event_description'])); ?>
                    </div>

                    <div class="event-details-grid">
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <div class="detail-label">Total Registrations</div>
                                <div class="detail-value"><?php echo $total_registrations; ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-money-bill"></i>
                            <div>
                                <div class="detail-label">Registration Fee</div>
                                <div class="detail-value">Rs. <?php echo number_format($event['event_fee'], 2); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <div class="detail-label">Start Date</div>
                                <div class="detail-value"><?php echo date('F j, Y', strtotime($event['start_date'])); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <div class="detail-label">End Date</div>
                                <div class="detail-value"><?php echo date('F j, Y', strtotime($event['end_date'])); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <div class="detail-label">Start Time</div>
                                <div class="detail-value"><?php echo date('g:i A', strtotime($event['start_time'])); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <div class="detail-label">End Time</div>
                                <div class="detail-value"><?php echo date('g:i A', strtotime($event['end_time'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <?php if (!$is_registered && strtotime($event['start_date']) > time()): ?>
                            <a href="register_event.php?id=<?php echo $event['id']; ?>" class="register-btn">
                                Register Now
                            </a>
                        <?php elseif ($is_registered): ?>
                            <a href="#" class="register-btn disabled">Already Registered</a>
                        <?php else: ?>
                            <a href="#" class="register-btn disabled">Registration Closed</a>
                        <?php endif; ?>
                        <a href="events.php" class="back-btn">Back to Events</a>
                    </div>
                </div>
            </div>
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
