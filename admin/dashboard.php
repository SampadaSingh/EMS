<?php
session_start();
include '../config/connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

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
            border-left: 4px solid rgb(81, 64, 179);
        }

        .activity-item h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .activity-item p {
            color: #7f8c8d;
            font-size: 14px;
        }


    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

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
