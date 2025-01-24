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
$participant_name = $_SESSION['username']; 

// Get participant's full name from database
$name_query = "SELECT full_name FROM users WHERE id = ?";
$name_stmt = $conn->prepare($name_query);
$name_stmt->bind_param('i', $participant_id);
$name_stmt->execute();
$name_result = $name_stmt->get_result();
$user_data = $name_result->fetch_assoc();
$full_name = $user_data['full_name'];

// Get total participants count
$total_participants_query = "SELECT COUNT(DISTINCT p_email) as total FROM participants";
$total_participants_result = $conn->query($total_participants_query);
$total_participants = $total_participants_result->fetch_assoc()['total'];

// Get total events count
$total_events_query = "SELECT COUNT(*) as total FROM events";
$total_events_result = $conn->query($total_events_query);
$total_events = $total_events_result->fetch_assoc()['total'];

// Get upcoming events
$upcoming_query = "SELECT * FROM events 
                  WHERE start_date > CURDATE() 
                  ORDER BY start_date ASC 
                  LIMIT 5";
$upcoming_result = $conn->query($upcoming_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participant Dashboard - EMS</title>
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

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .search-box input {
            width: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }

        .search-box button {
            padding: 10px 20px;
            background-color: #47338f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .welcome-header {
            margin-bottom: 30px;
        }

        .welcome-header h1 {
            font-size: 28px;
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #47338f;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 16px;
        }

        .events-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .events-header {
            margin-bottom: 20px;
        }

        .events-header h2 {
            color: #333;
            font-size: 20px;
        }

        .event-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .event-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .event-info h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .event-date {
            color: #666;
            font-size: 14px;
        }

        .details-button {
            padding: 8px 16px;
            background-color: #47338f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .details-button:hover {
            background-color: #372670;
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

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        .calendar {
            margin-top: 40px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .calendar h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }

        #currentDate {
            font-size: 14px;
            color: #ddd;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .search-box input {
                width: 200px;
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
            <div class="dashboard-container">
                <div class="search-box">
                    <input type="text" placeholder="Search events">
                    <button>Search</button>
                </div>

                <div class="welcome-header">
                    <h1>Welcome, <?php echo htmlspecialchars($full_name); ?></h1>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_participants; ?></div>
                        <div class="stat-label">Total Participants</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_events; ?></div>
                        <div class="stat-label">Total Events</div>
                    </div>
                </div>

                <div class="events-section">
                    <div class="events-header">
                        <h2>Your Upcoming Events</h2>
                    </div>
                    <div class="event-list">
                        <?php while($event = $upcoming_result->fetch_assoc()): ?>
                            <div class="event-item">
                                <div class="event-info">
                                    <h3><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                    <div class="event-date">Date: <?php echo date('F j, Y', strtotime($event['start_date'])); ?></div>
                                </div>
                                <a href="event_details.php?id=<?php echo $event['id']; ?>" class="details-button">Details</a>
                            </div>
                        <?php endwhile; ?>
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
