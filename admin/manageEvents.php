<?php
session_start();
include '../config/connect.php';

$result = $conn->query("SELECT * FROM events ORDER BY id ASC");
$events = $result;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['event_id'])) {
        $event_id = $conn->real_escape_string($_POST['event_id']);
        $sql = "DELETE FROM events WHERE id = '$event_id'";
        if ($conn->query($sql)) {
            header('Location: events.php?message=Event deleted successfully');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin</title>
    <style>
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
            max-width: 200px;
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
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header">
            <h1>Manage Events</h1>
            <a href="addEvent.php" class="add-btn">Add New Event</a>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo $_GET['message']; ?></div>
        <?php endif; ?>

        <div class="events-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Venue</th>
                        <th>Location</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Description</th>
                        <th>Organizer</th>
                        <th>Contact</th>
                        <th>Fee</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($event = $events->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $event['id']; ?></td>
                        <td><?php echo $event['event_title']; ?></td>
                        <td><?php echo $event['event_venue']; ?></td>
                        <td><?php echo $event['event_location']; ?></td>
                        <td class="time-cell"><?php echo date('h:i A', strtotime($event['start_time'])); ?></td>
                        <td class="time-cell"><?php echo date('h:i A', strtotime($event['end_time'])); ?></td>
                        <td class="date-cell"><?php echo date('M d, Y', strtotime($event['start_date'])); ?></td>
                        <td class="date-cell"><?php echo date('M d, Y', strtotime($event['end_date'])); ?></td>
                        <td class="description-cell"><?php echo substr($event['event_description'], 0, 100) . '...'; ?></td>
                        <td><?php echo $event['organizer_name']; ?></td>
                        <td><?php echo $event['organizer_contact']; ?></td>
                        <td>Rs. <?php echo number_format($event['event_fee'], 2); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="editEvent.php?id=<?php echo $event['id']; ?>" class="edit-btn">Edit</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

