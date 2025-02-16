<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];

$query = "SELECT * FROM events WHERE organizer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - EMS</title>
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

        .create-btn {
            padding: 12px 24px;
            background: #433D8B;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }

        .create-btn:hover {
            transform: translateY(-2px);
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .event-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .event-image {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #f0f0f0;
            background-image: url('../assets/images/event-default.png');
            background-size: cover;
            background-position: center;
        }

        .event-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .date-info {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .date-item {
            display: flex;
            flex-direction: column;
        }

        .date-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .date-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .event-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }

        .action-btn {
            flex: 1;
            padding: 10px 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            color: white;
            transition: opacity 0.2s;
        }

        .view-btn {
            background-color: #433D8B;
        }

        .edit-btn {
            background-color:rgb(76, 74, 78);
            align-content: center;
        }

        .delete-btn {
            background-color: #DC3545;
            align-content: center;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar h2,
            .menu-item span {
                display: none;
            }

            .content {
                margin-left: 80px;
                padding: 20px;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header">
            <h1>My Events</h1>
            <a href="createEvent.php" class="create-btn">
                <i class="fas fa-plus"></i>
                Create Event
            </a>
        </div>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="events-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <div class="event-card">
                        <div class="event-image"></div>

                        <h2 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h2>

                        <div class="description">
                            <?php echo htmlspecialchars($event['event_description']); ?>
                        </div>

                        <div class="date-info">
                            <div class="date-item">
                                <span class="date-label">Start Date:</span>
                                <span class="date-value"><?php echo date('F j, Y', strtotime($event['start_date'])); ?></span>
                            </div>
                            <div class="date-item">
                                <span class="date-label">End Date:</span>
                                <span class="date-value"><?php echo date('F j, Y', strtotime($event['end_date'])); ?></span>
                            </div>
                        </div>

                        <div class="event-actions">
                            <a href="viewParticipants.php?event_id=<?php echo urlencode($event['id']); ?>&event_title=<?php echo urlencode($event['event_title']); ?>" class="action-btn view-btn">View Participants</a>

                            <?php
                            if ($event['end_date'] >= date('Y-m-d')):
                            ?>
                                <a href="editEvent.php?id=<?php echo urlencode($event['id']); ?>" class="action-btn edit-btn">Edit</a>
                            <?php else: ?>
                                <a href="#" class="action-btn edit-btn" style="opacity: 0.5; cursor: not-allowed;">Edit</a>
                            <?php endif; ?>

                            <a href="deleteEvent.php?event_id=<?php echo urlencode($event['id']); ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.');">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-events">
                    <p>You haven't created any events yet.</p>
                    <a href="createEvent.php" class="create-btn" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i>
                        Create Your First Event
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>