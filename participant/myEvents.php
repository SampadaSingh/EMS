<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'participant') {
    header('Location: ../php/login.php');
    exit();
}

$participant_id = $_SESSION['user_id'];
$participant_email = $_SESSION['email'];

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'upcoming'; 

$query = "SELECT e.*, p.created_at as registration_date 
          FROM events e 
          JOIN participants p ON e.id = p.event_id 
          WHERE p.p_email = ?";
$params = [$participant_email];
$types = "s";

if ($search) {
    $query .= " AND (e.event_title LIKE ? OR e.event_description LIKE ? OR e.event_venue LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

switch ($status) {
    case 'upcoming':
        $query .= " AND e.start_date >= CURDATE()";
        break;
    case 'past':
        $query .= " AND e.end_date < CURDATE()";
        break;
    case 'ongoing':
        $query .= " AND CURDATE() BETWEEN e.start_date AND e.end_date";
        break;
}

$query .= " ORDER BY e.start_date ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - EMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .filters {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .filter-group {
            flex: 1;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .event-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .event-details {
            padding: 20px;
        }

        .event-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .event-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .event-info i {
            width: 20px;
            color: #47338f;
        }

        .event-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin-top: 10px;
        }

        .status-upcoming {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-ongoing {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-past {
            background: #ffebee;
            color: #c62828;
        }

        .details-button {
            display: inline-block;
            padding: 8px 16px;
            background-color: #47338f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .details-button:hover {
            background-color: #372670;
        }

        .no-events {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #666;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .events-grid {
                grid-template-columns: 1fr;
            }
            .filters {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="container">
                <div class="header">
                    <h1>My Events</h1>
                </div>

                <div class="filters">
                    <div class="filter-group">
                        <label for="search">Search Events</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search events...">
                    </div>
                    <div class="filter-group">
                        <label for="status">Event Status</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo ($status === 'all') ? 'selected' : ''; ?>>All Events</option>
                            <option value="upcoming" <?php echo ($status === 'upcoming') ? 'selected' : ''; ?>>Upcoming Events</option>
                            <option value="ongoing" <?php echo ($status === 'ongoing') ? 'selected' : ''; ?>>Ongoing Events</option>
                            <option value="past" <?php echo ($status === 'past') ? 'selected' : ''; ?>>Past Events</option>
                        </select>
                    </div>
                </div>

                <?php if ($result->num_rows > 0): ?>
                    <div class="events-grid">
                        <?php while ($event = $result->fetch_assoc()): 
                            $event_status = '';
                            $status_class = '';
                            
                            if (strtotime($event['end_date']) < time()) {
                                $event_status = 'Past Event';
                                $status_class = 'status-past';
                            } elseif (strtotime($event['start_date']) > time()) {
                                $event_status = 'Upcoming Event';
                                $status_class = 'status-upcoming';
                            } elseif (strtotime($event['start_date']) <= time() && strtotime($event['end_date']) >= time()) {
                                $event_status = 'Ongoing Event';
                                $status_class = 'status-ongoing';
                            }else{
                                $event_status = 'Unknown';
                                $status_class = 'status-unknown';
                            }
                        ?>
                            <div class="event-card">
                                <?php if ($event['event_image']): ?>
                                    <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="<?php echo htmlspecialchars($event['event_title']); ?>" class="event-image">
                                <?php endif; ?>
                                <div class="event-details">
                                    <h3 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                    <p class="event-info">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($event['event_venue']); ?>
                                    </p>
                                    <p class="event-info">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('F j, Y', strtotime($event['start_date'])); ?>
                                    </p>
                                    <p class="event-info">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('g:i A', strtotime($event['start_time'])); ?> - 
                                        <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                    </p>
                                    <span class="event-status <?php echo $status_class; ?>">
                                        <?php echo $event_status; ?>
                                    </span>
                                    <br>
                                    <a href="eventDetails.php?id=<?php echo $event['id']; ?>" class="details-button">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-events">
                        <h3>No events found</h3>
                        <p>You haven't registered for any events yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('search').addEventListener('input', function() {
            updateFilters();
        });

        document.getElementById('status').addEventListener('change', function() {
            updateFilters();
        });

        function updateFilters() {
            const search = document.getElementById('search').value;
            const status = document.getElementById('status').value;
            
            let url = window.location.pathname + '?';
            if (search) url += 'search=' + encodeURIComponent(search) + '&';
            if (status) url += 'status=' + encodeURIComponent(status);
            
            window.location.href = url;
        }
    </script>
</body>
</html>
