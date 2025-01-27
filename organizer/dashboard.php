<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];

$user_query = "SELECT full_name FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $organizer_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$events_query = "SELECT COUNT(*) as total_events FROM events WHERE organizer_id = ?";
$events_stmt = $conn->prepare($events_query);
$events_stmt->bind_param("i", $organizer_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
$total_events = $events_result->fetch_assoc()['total_events'];

$participants_query = "SELECT COUNT(DISTINCT p.id) as total_participants 
                      FROM participants p 
                      JOIN events e ON p.event_title = e.event_title 
                      WHERE e.organizer_id = ?";
$participants_stmt = $conn->prepare($participants_query);
$participants_stmt->bind_param("i", $organizer_id);
$participants_stmt->execute();
$participants_result = $participants_stmt->get_result();
$total_participants = $participants_result->fetch_assoc()['total_participants'];

$upcoming_query = "SELECT * FROM events 
                  WHERE organizer_id = ? 
                  AND start_date >= CURDATE() 
                  ORDER BY start_date ASC 
                  LIMIT 5";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $organizer_id);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f5f7fa;
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

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
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

        .calendar {
            margin-top: 270px;
            padding: 20px;
            background-color: #2a2679;
            border-radius: 10px;
            text-align: center;
        }

        .calendar h3 {
            margin-bottom: 10px;
        }

        .content {
            flex-grow: 1;
            margin-left: 270px;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .search-container {
            display: flex;
            gap: 10px;
        }

        .search-container input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
        }

        .search-container button {
            padding: 10px 20px;
            background: #433D8B;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 2em;
            color: #433D8B;
            margin-bottom: 10px;
        }

        .upcoming-events {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .upcoming-events h2 {
            margin-bottom: 20px;
            color: #17153B;
        }

        .event {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .event:last-child {
            border-bottom: none;
        }

        .event-details {
            padding: 8px 16px;
            background: #433D8B;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .event-details:hover {
            background: #322e6a;
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
                <span>My Events</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="participants.php">
                <i class="fas fa-users"></i>
                <span>Participants</span>
            </a>
        </div>
        <div class="menu-item">
            <a href="account.php">
                <i class="fas fa-user"></i>
                <span>My Account</span>
            </a>
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
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h1>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search events" />
                <button onclick="searchEvent()">Search</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $total_events; ?></h3>
                <p>Total Events</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_participants; ?></h3>
                <p>Total Participants</p>
            </div>
        </div>

        <div class="upcoming-events">
            <h2>Your Upcoming Events</h2>
            <?php if ($upcoming_result->num_rows > 0): ?>
                <?php while ($event = $upcoming_result->fetch_assoc()): ?>
                    <div class="event">
                        <div>
                            <p><strong><?php echo htmlspecialchars($event['event_title']); ?></strong></p>
                            <p>Date: <?php echo date('F j, Y', strtotime($event['start_date'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No upcoming events</p>
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

        function searchEvent() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const events = document.querySelectorAll('.event');

            events.forEach(event => {
                const text = event.innerText.toLowerCase();
                event.style.display = text.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
