<?php
include '../config/connect.php';
include 'sidebar.php';

// Get event ID from URL
if (!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$event_id = $_GET['id'];

// Get event details
$event_query = "SELECT * FROM events WHERE id = ?";
$stmt = $conn->prepare($event_query);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: events.php');
    exit();
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['event_title']); ?> - EMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .event-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .event-header {
            background: #17153B;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .event-title {
            margin: 0;
        }
        .event-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-bottom: 1px solid #ddd;
        }
        .event-details {
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-item i {
            margin-right: 10px;
            color: #47338f;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #17153B;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            margin: 20px auto 0 auto;
        }
    </style>
</head>
<body>
    <div class="event-container">
        <div class="event-header">
            <h1 class="event-title"><?php echo htmlspecialchars($event['event_title']); ?></h1>
        </div>
        
        <?php if ($event['event_image']): ?>
            <img src="../assets/uploads/<?php echo htmlspecialchars($event['event_image']); ?>" 
                 alt="<?php echo htmlspecialchars($event['event_title']); ?>" 
                 class="event-image">
        <?php endif; ?>

        <div class="event-details">
            <div class="detail-item">
                <i class="fas fa-calendar"></i> <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['start_date'])); ?> - <?php echo date('F j, Y', strtotime($event['end_date'])); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-clock"></i> <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['start_time'])); ?> - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-map-marker-alt"></i> <strong>Venue:</strong> <?php echo htmlspecialchars($event['event_venue']); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-money-bill"></i> <strong>Fee:</strong> Rs. <?php echo number_format($event['event_fee'], 2); ?>
            </div>
            <div class="detail-item">
                <i class="fas fa-align-left"></i> <strong>Description:</strong>
                <p><?php echo nl2br(htmlspecialchars($event['event_description'])); ?></p>
            </div>

            <a href="manageEvents.php" class="back-btn">Go back</a>
        </div>
    </div>
</body>
</html>