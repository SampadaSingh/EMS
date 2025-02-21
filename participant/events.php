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
$date_filter = $_GET['date'] ?? '';

$query = "SELECT e.* FROM events e 
          WHERE e.event_title NOT IN (
              SELECT event_title FROM participants WHERE p_email = ?
          )
          AND e.start_date >= CURDATE()";
$params = [$participant_email];
$types = "s";

if ($search) {
    $query .= " AND (e.event_title LIKE ? OR e.event_description LIKE ? OR e.event_venue LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

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
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-form {
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            color: #17153B;
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-btn {
            padding: 10px 20px;
            background: rgb(81, 64, 179);
            ;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .event-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
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
            color: #17153B;
            margin-bottom: 10px;
        }

        .event-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .event-info i {
            width: 20px;
            color: #17153B;
        }

        .register-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #17153B;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            transition: background 0.3s;
        }

        .register-btn:hover {
            background: #2c2975;
        }

        .register-btn.disabled {
            background-color:rgb(74, 76, 77);
            cursor: not-allowed;
        }
        .details-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #47338f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
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
    </style>
</head>

<body>
    <div class="dashboard-layout">

        <?php include 'sidebar.php'; ?>

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

                                <?php if (strtotime($event['start_date']) > time()): ?>
                                    <a href="registerEvent.php?id=<?php echo $event['id']; ?>" class="register-btn">
                                        Register Now
                                    </a>
                                <?php else: ?>
                                    <a href="#" class="register-btn disabled">Registration Closed</a>
                                <?php endif; ?>
                                <a href="eventDetails.php?id=<?php echo $event['id']; ?>" class="details-button">View Details</a>
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

</body>

</html>