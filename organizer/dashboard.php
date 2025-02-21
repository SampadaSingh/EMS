<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];

//user details
$user_query = "SELECT full_name FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $organizer_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

//total events
$events_query = "SELECT COUNT(*) as total_events FROM events WHERE organizer_id = ?";
$events_stmt = $conn->prepare($events_query);
$events_stmt->bind_param("i", $organizer_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
$total_events = $events_result->fetch_assoc()['total_events'];

//total participants
$participants_query = "SELECT COUNT(DISTINCT p.id) as total_participants 
                      FROM participants p 
                      JOIN events e ON p.event_title = e.event_title 
                      WHERE e.organizer_id = ?";
$participants_stmt = $conn->prepare($participants_query);
$participants_stmt->bind_param("i", $organizer_id);
$participants_stmt->execute();
$participants_result = $participants_stmt->get_result();
$total_participants = $participants_result->fetch_assoc()['total_participants'];

//ongoing events
$ongoing_query = "SELECT * FROM events 
                 WHERE organizer_id = ? 
                 AND start_date <= CURDATE() 
                 AND end_date >= CURDATE()";
$ongoing_stmt = $conn->prepare($ongoing_query);
$ongoing_stmt->bind_param("i", $organizer_id);
$ongoing_stmt->execute();
$ongoing_result = $ongoing_stmt->get_result();

//upcoming events
$upcoming_query = "SELECT * FROM events 
                  WHERE organizer_id = ? 
                  AND start_date >= CURDATE() 
                  ORDER BY start_date ASC 
                  LIMIT 5";
$upcoming_stmt = $conn->prepare($upcoming_query);
$upcoming_stmt->bind_param("i", $organizer_id);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();

//recent events
$recent_query = "SELECT * FROM events 
                WHERE organizer_id = ? 
                AND start_date < CURDATE() 
                ORDER BY start_date DESC 
                LIMIT 5";
$recent_stmt = $conn->prepare($recent_query);
$recent_stmt->bind_param("i", $organizer_id);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();
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

        .ongoing-events {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .upcoming-events {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .recent-events {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .ongoing-events h2, .upcoming-events h2, .recent-events h2 {
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
    <?php include('sidebar.php'); ?>

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

        <div class = "ongoing-events">
            <h2>Your Ongoing Events</h2>
            <?php if ($ongoing_result->num_rows > 0): ?>
                <?php while ($event = $ongoing_result->fetch_assoc()): ?>
                    <div class="event">
                        <div>
                            <p><strong><?php echo htmlspecialchars($event['event_title']); ?></strong></p>
                            <p>Date: <?php echo date('F j, Y', strtotime($event['start_date'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No ongoing events</p>
            <?php endif; ?>
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

        <div class = "recent-events">
            <h2>Your Recent Events</h2>
            <?php if ($recent_result->num_rows > 0): ?>
                <?php while ($event = $recent_result->fetch_assoc()): ?>
                    <div class="event">
                        <div>
                            <p><strong><?php echo htmlspecialchars($event['event_title']); ?></strong></p>
                            <p>Date: <?php echo date('F j, Y', strtotime($event['start_date'])); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No recent events</p>
            <?php endif; ?>

        </div>
    </div>

    <script>

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
