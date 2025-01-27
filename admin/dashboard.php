<?php
session_start();
include '../config/connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch statistics
$participantResult = $conn->query("SELECT COUNT(*) AS total_participants FROM participants");
$totalParticipants = $participantResult->fetch_assoc()['total_participants'];

$eventResult = $conn->query("SELECT COUNT(*) AS total_events FROM events");
$totalEvents = $eventResult->fetch_assoc()['total_events'];

$organizerResult = $conn->query("SELECT COUNT(*) AS total_organizers FROM users WHERE role='organizer'");
$totalOrganizers = $organizerResult->fetch_assoc()['total_organizers'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Dashboard</title>
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
            background-color: rgb(75, 64, 141);
        }

        .menu-item.active {
            background-color: rgb(81, 64, 179);
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

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .stat-card p {
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
        }

        .recent-activity {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .recent-activity h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .activity-list {
            display: grid;
            gap: 15px;
        }

        .activity-item {
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
        }

        .activity-item h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .activity-item p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .calendar {
            margin-top: 30px;
            padding: 15px;
            background-color: #34495e;
            border-radius: 5px;
            color: white;
        }

        .calendar h3 {
            margin-bottom: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <div class="menu-item active">
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
        <div class="menu-item">
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
            <h1>Admin Dashboard</h1>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Participants</h3>
                <p><?php echo $totalParticipants; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Events</h3>
                <p><?php echo $totalEvents; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Organizers</h3>
                <p><?php echo $totalOrganizers; ?></p>
            </div>
        </div>

        <div class="recent-activity">
            <h2>Recent Activities</h2>
            <div class="activity-list">
                <?php
                $recentEvents = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5");
                while ($event = $recentEvents->fetch_assoc()) {
                    echo "<div class='activity-item'>";
                    echo "<h4>{$event['event_title']}</h4>";
                    echo "<p>Start Date: {$event['start_date']}</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                const link = item.querySelector('a');
                if (link && currentPath.includes(link.getAttribute('href'))) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
