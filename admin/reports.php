<?php
session_start();
include '../config/connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Get statistics
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalEvents = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$totalParticipants = $conn->query("SELECT COUNT(*) as count FROM participants")->fetch_assoc()['count'];

// Get monthly event statistics
$monthlyStats = $conn->query("
    SELECT DATE_FORMAT(start_date, '%Y-%m') as month, COUNT(*) as count 
    FROM events 
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 12
");

// Get popular events
$popularEvents = $conn->query("
    SELECT e.event_title, COUNT(p.id) as participant_count 
    FROM events e 
    LEFT JOIN participants p ON e.id = p.event_id 
    GROUP BY e.id 
    ORDER BY participant_count DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .chart-card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 18px;
        }

        canvas {
            width: 100% !important;
            height: 300px !important;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content">
        <div class="header">
            <h1>Reports Dashboard</h1>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Events</h3>
                <p><?php echo $totalEvents; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Participants</h3>
                <p><?php echo $totalParticipants; ?></p>
            </div>
        </div>

        <div class="charts-container">
            <div class="chart-card">
                <h3>Monthly Events</h3>
                <canvas id="monthlyEventsChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Popular Events</h3>
                <canvas id="popularEventsChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Monthly Events Chart
        const monthlyData = <?php 
            $labels = [];
            $data = [];
            while ($row = $monthlyStats->fetch_assoc()) {
                $labels[] = $row['month'];
                $data[] = $row['count'];
            }
            echo json_encode(['labels' => $labels, 'data' => $data]);
        ?>;

        new Chart(document.getElementById('monthlyEventsChart'), {
            type: 'line',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Number of Events',
                    data: monthlyData.data,
                    borderColor: 'rgb(104, 92, 175)',
                    tension: 0.1
                }]
            }
        });

        // Popular Events Chart
        const popularData = <?php 
            $labels = [];
            $data = [];
            while ($row = $popularEvents->fetch_assoc()) {
                $labels[] = $row['event_title'];
                $data[] = $row['participant_count'];
            }
            echo json_encode(['labels' => $labels, 'data' => $data]);
        ?>;

        new Chart(document.getElementById('popularEventsChart'), {
            type: 'bar',
            data: {
                labels: popularData.labels,
                datasets: [{
                    label: 'Number of Participants',
                    data: popularData.data,
                    backgroundColor: 'rgb(104, 92, 175,0.2)',
                    borderColor: 'rgb(104, 92, 175)',
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html>
