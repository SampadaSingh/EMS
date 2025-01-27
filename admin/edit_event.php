<?php
session_start();
include '../config/connect.php';

// Get event ID from URL
if (!isset($_GET['id'])) {
    header('Location: events.php');
    exit();
}

$eventId = $conn->real_escape_string($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['event_title']);
    $venue = $conn->real_escape_string($_POST['event_venue']);
    $location = $conn->real_escape_string($_POST['event_location']);
    $startTime = $conn->real_escape_string($_POST['start_time']);
    $endTime = $conn->real_escape_string($_POST['end_time']);
    $startDate = $conn->real_escape_string($_POST['start_date']);
    $endDate = $conn->real_escape_string($_POST['end_date']);
    $description = $conn->real_escape_string($_POST['event_description']);
    $organizerName = $conn->real_escape_string($_POST['organizer_name']);
    $organizerContact = $conn->real_escape_string($_POST['organizer_contact']);
    $eventFee = $conn->real_escape_string($_POST['event_fee']);

    // Handle image upload if a new image is selected
    $imageUpdate = "";
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0) {
        $targetDir = "../assets/uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["event_image"]["name"], PATHINFO_EXTENSION));
        $newFileName = "IMG-" . uniqid() . "-" . $_FILES["event_image"]["name"];
        $targetFile = $targetDir . $newFileName;

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["event_image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $targetFile)) {
                // Delete old image if it exists
                $oldImage = $conn->query("SELECT event_image FROM events WHERE id = '$eventId'")->fetch_assoc();
                if ($oldImage && $oldImage['event_image'] && file_exists($targetDir . $oldImage['event_image'])) {
                    unlink($targetDir . $oldImage['event_image']);
                }
                $imageUpdate = ", event_image = '$newFileName'";
            }
        }
    }

    $sql = "UPDATE events SET 
            event_title = '$title',
            event_venue = '$venue',
            event_location = '$location',
            start_time = '$startTime',
            end_time = '$endTime',
            start_date = '$startDate',
            end_date = '$endDate',
            event_description = '$description',
            organizer_name = '$organizerName',
            organizer_contact = '$organizerContact',
            event_fee = '$eventFee'
            $imageUpdate
            WHERE id = '$eventId'";
    
    if ($conn->query($sql)) {
        header('Location: events.php?message=Event updated successfully');
        exit();
    } else {
        $error = "Error updating event: " . $conn->error;
    }
}

// Get event data
$result = $conn->query("SELECT * FROM events WHERE id = '$eventId'");
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
    <title>Edit Event - Admin</title>
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
            background-color: #2c3e50;
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

        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
        }

        .btn-container {
            display: flex;
            gap: 10px;
        }

        .submit-btn {
            padding: 10px 20px;
            background-color: rgb(81, 64, 179);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: rgb(75, 64, 141);
        }

        .cancel-btn {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
        }

        .error {
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .current-image {
            max-width: 200px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/dashboard.png" alt="Dashboard">
            <span><a href="dashboard.php">Dashboard</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/conference.png" alt="Users">
            <span><a href="users.php">Manage Users</a></span>
        </div>
        <div class="menu-item active">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/calendar.png" alt="Events">
            <span><a href="events.php">Manage Events</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/report-card.png" alt="Reports">
            <span><a href="reports.php">Reports</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/settings.png" alt="Settings">
            <span><a href="settings.php">Settings</a></span>
        </div>
        <div class="menu-item">
            <img src="https://img.icons8.com/ios-glyphs/30/ffffff/logout-rounded.png" alt="Logout">
            <span><a href="../php/logout.php">Logout</a></span>
        </div>
    </div>

    <div class="content">
        <div class="header">
            <h1>Edit Event</h1>
        </div>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="event_title">Event Title</label>
                    <input type="text" id="event_title" name="event_title" value="<?php echo $event['event_title']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_venue">Venue</label>
                    <input type="text" id="event_venue" name="event_venue" value="<?php echo $event['event_venue']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_location">Location</label>
                    <input type="text" id="event_location" name="event_location" value="<?php echo $event['event_location']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" value="<?php echo $event['start_time']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" value="<?php echo $event['end_time']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $event['start_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $event['end_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_description">Description</label>
                    <textarea id="event_description" name="event_description" required><?php echo $event['event_description']; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="organizer_name">Organizer Name</label>
                    <input type="text" id="organizer_name" name="organizer_name" value="<?php echo $event['organizer_name']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="organizer_contact">Organizer Contact</label>
                    <input type="tel" id="organizer_contact" name="organizer_contact" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" value="<?php echo $event['organizer_contact']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_image">Event Image</label>
                    <?php if ($event['event_image']): ?>
                        <div>
                            <p>Current Image:</p>
                            <img src="../assets/uploads/<?php echo $event['event_image']; ?>" alt="Current Event Image" class="current-image">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                    <small>Leave empty to keep the current image</small>
                </div>

                <div class="form-group">
                    <label for="event_fee">Event Fee (Rs.)</label>
                    <input type="number" id="event_fee" name="event_fee" min="0" step="0.01" value="<?php echo $event['event_fee']; ?>" required>
                </div>

                <div class="btn-container">
                    <button type="submit" class="submit-btn">Update Event</button>
                    <a href="events.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
