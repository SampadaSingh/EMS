<?php
include '../config/connect.php';
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];

// Get filters
$search = $_GET['search'] ?? '';
$event_filter = $_GET['event'] ?? '';

// Base query
$query = "SELECT p.*, e.start_date, e.end_date, e.event_fee 
          FROM participants p
          JOIN events e ON p.event_title = e.event_title
          WHERE e.organizer_id = ?";
$params = [$organizer_id];
$types = "i";

// Add search filter
if ($search) {
    $query .= " AND (p.p_name LIKE ? OR p.p_email LIKE ? OR p.p_phone LIKE ?)";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param, $search_param);
    $types .= "sss";
}

// Add event filter
if ($event_filter) {
    $query .= " AND p.event_title = ?";
    array_push($params, $event_filter);
    $types .= "s";
}


$query .= " ORDER BY p.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get events for filter dropdown
$events_query = "SELECT DISTINCT event_title FROM events WHERE organizer_id = ? ORDER BY start_date DESC";
$events_stmt = $conn->prepare($events_query);
$events_stmt->bind_param('i', $organizer_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants - EMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
            .content {
            flex-grow: 1;
            margin-left: 300px;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
        }

        .stat-title {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .stat-value {
            color: #17153B;
            font-size: 1.5em;
            font-weight: 700;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 8px;
            color: #17153B;
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-btn {
            padding: 10px 20px;
            background: #433D8B;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s;
            height: 40px;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
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

        .event-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            background: #e3f2fd;
            color: #1976d2;
        }

        .no-participants {
            text-align: center;
            padding: 40px;
            color: #666;
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

            .filter-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('sidebar.php'); ?>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <h1>Participants</h1>
        </div>

        <div class="filters">
            <form class="filter-form" method="GET">
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" 
                           placeholder="Name, email or phone"
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label for="event">Event</label>
                    <select id="event" name="event">
                        <option value="">All Events</option>
                        <?php while ($event = $events_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($event['event_title']); ?>"
                                    <?php echo ($event_filter === $event['event_title']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($event['event_title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i>
                    Apply Filters
                </button>
            </form>
        </div>

        <div class="participants-table">
            <div class="table-header">
                <h3>All Participants</h3>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Event</th>
                            <th>Registration Date</th>
                            <th>Event Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($participant = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($participant['p_name']); ?></td>
                                <td><?php echo htmlspecialchars($participant['p_email']); ?></td>
                                <td><?php echo htmlspecialchars($participant['p_phone']); ?></td>
                                <td>
                                    <span class="event-badge">
                                        <?php echo htmlspecialchars($participant['event_title']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($participant['created_at'])); ?></td>
                                <td>Rs. <?php echo number_format($participant['event_fee'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-participants">
                    <p>No participants found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
