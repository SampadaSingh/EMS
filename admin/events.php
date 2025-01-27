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

// Get filters
$search = $_GET['search'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Base query to get upcoming events not registered by the current participant
$query = "SELECT e.* FROM events e 
          WHERE e.event_title NOT IN (
              SELECT event_title FROM participants WHERE p_email = ?
          )
          AND e.start_date >= CURDATE()"; // Add this condition to only show upcoming events
$params = [$participant_email];
$types = "s";

// Add search filter
if ($search) {
    $query .= " AND (e.event_title LIKE ? OR e.event_description LIKE ? OR e.event_venue LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

// Add date filter
if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $query .= " AND DATE(e.start_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND e.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $query .= " AND e.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
            break;
    }
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
    <title>Events - EMS</title>
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
            background-color: #f5f6fa;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #17153B;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .add-btn {
            padding: 10px 20px;
            background-color: rgb(81, 64, 179);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .add-btn:hover {
            background-color: rgb(75, 64, 141);
        }

        .events-table {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .edit-btn {
            padding: 6px 12px;
            background-color: rgb(81, 64, 179);
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .edit-btn:hover {
            background-color: rgb(75, 64, 141);
        }

        .delete-btn {
            padding: 6px 12px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .description-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .date-cell {
            white-space: nowrap;
            min-width: 100px;
            padding: 16px 12px;
            line-height: 1.4;
        }

        .time-cell {
            white-space: nowrap;
            min-width: 80px;
            padding: 16px 12px;
            line-height: 1.4;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
                    <a href="events.php" class="nav-link active">
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
            <h1>Available Events</h1>

            <div class="filters">
                <form class="filter-form" method="GET">
                    <div class="filter-group">
                        <label for="search">Search Events</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by title, description or venue">
                    </div>
                    <div class="filter-group">
                        <label for="date">Date Range</label>
                        <select id="date" name="date">
                            <option value="">All Upcoming Events</option>
                            <option value="today" <?php echo ($date_filter === 'today') ? 'selected' : ''; ?>>Today</option>
                            <option value="week" <?php echo ($date_filter === 'week') ? 'selected' : ''; ?>>This Week</option>
                            <option value="month" <?php echo ($date_filter === 'month') ? 'selected' : ''; ?>>This Month</option>
                        </select>
                    </div>
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>
                </form>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="events-grid">
                    <?php while ($event = $result->fetch_assoc()): ?>
                        <div class="event-card">
                            <?php if ($event['event_image']): ?>
                                <img src="../assets/uploads/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($event['event_title']); ?>" 
                                     class="event-image">
                            <?php endif; ?>
                            <div class="event-details">
                                <h2 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h2>
                                <p class="event-info">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('F j, Y', strtotime($event['start_date'])); ?>
                                </p>
                                <p class="event-info">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('g:i A', strtotime($event['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                </p>
                                <p class="event-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($event['event_venue']); ?>
                                </p>
                                <p class="event-info">
                                    <i class="fas fa-money-bill"></i>
                                    Rs. <?php echo number_format($event['event_fee'], 2); ?>
                                </p>
                                <a href="register_event.php?id=<?php echo $event['id']; ?>" class="register-btn">
                                    Register Now
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-events">
                    <i class="fas fa-calendar-times fa-3x"></i>
                    <h2>No Events Found</h2>
                    <p>There are no available events matching your criteria.</p>
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