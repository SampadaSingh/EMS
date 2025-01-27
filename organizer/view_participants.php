<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = $_GET['id'] ?? '';

$event_query = "SELECT * FROM events WHERE id = ? AND organizer_id = ?";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("ii", $event_id, $organizer_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    header('Location: events.php');
    exit();
}

$event = $event_result->fetch_assoc();

$query = "SELECT p.* FROM participants p 
          WHERE p.event_id = ?
          ORDER BY p.id ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Participants - EMS</title>
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

        .content {
            flex-grow: 1;
            margin-left: 300px;
            padding: 40px;
        }

        .header {
            margin-bottom: 30px;
        }

        .event-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .event-info h2 {
            color: #17153B;
            margin-bottom: 15px;
        }

        .event-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #17153B;
        }

        .participants-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .export-btn {
            padding: 10px 20px;
            background: #433D8B;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.3s;
        }

        .export-btn:hover {
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            color: #17153B;
            font-weight: 500;
        }

        tr:hover {
            background: #f5f7fa;
        }

        .no-participants {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background: #f5f5f5;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .back-btn:hover {
            transform: translateY(-2px);
        }

        .back-btn img {
            width: 20px;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar h2, .menu-item span {
                display: none;
            }

            .content {
                margin-left: 80px;
                padding: 20px;
            }

            .event-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <h1>Event Participants</h1>
        </div>

        <div class="event-info">
            <h2><?php echo htmlspecialchars($event['event_title']); ?></h2>
            <div class="event-details">
                <div class="detail-item">
                    <div class="detail-label">Venue</div>
                    <div class="detail-value"><?php echo htmlspecialchars($event['event_venue']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">
                        <?php 
                        echo date('M j, Y', strtotime($event['start_date']));
                        if ($event['start_date'] !== $event['end_date']) {
                            echo ' - ' . date('M j, Y', strtotime($event['end_date']));
                        }
                        ?>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Time</div>
                    <div class="detail-value">
                        <?php 
                        echo date('g:i A', strtotime($event['start_time'])) . ' - ' . 
                             date('g:i A', strtotime($event['end_time']));
                        ?>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Entry Fee</div>
                    <div class="detail-value">Rs. <?php echo number_format($event['event_fee'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="participants-table">
            <div class="table-header">
                <h3>Registered Participants</h3>
                <a href="export_participants.php?event_title=<?php echo urlencode($event_title); ?>" class="export-btn">
                    Export to Excel
                </a>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($participant = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($participant['p_name']); ?></td>
                                <td><?php echo htmlspecialchars($participant['p_email']); ?></td>
                                <td><?php echo htmlspecialchars($participant['p_phone']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($participant['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-participants">
                    <p>No participants have registered for this event yet.</p>
                </div>
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