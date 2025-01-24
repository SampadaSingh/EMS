<?php
include '../config/connect.php';
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = $_GET['id'] ?? 0;

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: events.php');
    exit();
}

$event = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_title = $_POST['event_title'];
    $event_venue = $_POST['event_venue'];
    $event_location = $_POST['event_location'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $event_description = $_POST['event_description'];
    $organizer_name = $_POST['organizer_name'];
    $organizer_contact = $_POST['organizer_contact'];
    $event_fee = $_POST['event_fee'];

    // Handle image upload
    $event_image = $event['event_image']; // Keep existing image by default
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['event_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $newname = uniqid('IMG-', true) . '.' . $filetype;
            $upload_dir = '../assets/uploads/';
            
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_dir . $newname)) {
                // Delete old image if exists
                if ($event['event_image'] && file_exists($upload_dir . $event['event_image'])) {
                    unlink($upload_dir . $event['event_image']);
                }
                $event_image = $newname;
            }
        }
    }

    // Update event in database
    $stmt = $conn->prepare("UPDATE events SET 
        event_title = ?, event_venue = ?, event_location = ?, 
        start_time = ?, end_time = ?, start_date = ?, end_date = ?,
        event_description = ?, organizer_name = ?, organizer_contact = ?,
        event_image = ?, event_fee = ?
        WHERE id = ? AND organizer_id = ?");
    
    $stmt->bind_param("sssssssssssdii", 
        $event_title, $event_venue, $event_location,
        $start_time, $end_time, $start_date, $end_date,
        $event_description, $organizer_name, $organizer_contact,
        $event_image, $event_fee, $event_id, $organizer_id
    );

    if ($stmt->execute()) {
        header('Location: events.php?updated=1');
        exit();
    } else {
        $error = "Error updating event: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - EMS</title>
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

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #17153B;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        input[type="time"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #433D8B;
        }

        .current-image {
            margin-top: 10px;
            text-align: center;
        }

        .current-image img {
            max-width: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 25px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .submit-btn {
            background: #433D8B;
            color: white;
        }

        .cancel-btn {
            background: #ffebee;
            color: #c62828;
            text-decoration: none;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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

            .form-grid {
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
            <h1>Edit Event</h1>
        </div>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="event_title">Event Title</label>
                        <input type="text" id="event_title" name="event_title" value="<?php echo htmlspecialchars($event['event_title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="event_venue">Venue</label>
                        <input type="text" id="event_venue" name="event_venue" value="<?php echo htmlspecialchars($event['event_venue']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="event_location">Location</label>
                        <input type="text" id="event_location" name="event_location" value="<?php echo htmlspecialchars($event['event_location']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="event_fee">Entry Fee (Rs.)</label>
                        <input type="number" id="event_fee" name="event_fee" min="0" step="0.01" value="<?php echo htmlspecialchars($event['event_fee']); ?>">
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
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" value="<?php echo $event['start_time']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" value="<?php echo $event['end_time']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="organizer_name">Organizer Name</label>
                        <input type="text" id="organizer_name" name="organizer_name" value="<?php echo htmlspecialchars($event['organizer_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="organizer_contact">Organizer Contact</label>
                        <input type="text" id="organizer_contact" name="organizer_contact" value="<?php echo htmlspecialchars($event['organizer_contact']); ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="event_description">Event Description</label>
                        <textarea id="event_description" name="event_description" required><?php echo htmlspecialchars($event['event_description']); ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="event_image">Event Image</label>
                        <input type="file" id="event_image" name="event_image" accept="image/*">
                        <?php if ($event['event_image']): ?>
                            <div class="current-image">
                                <p>Current Image:</p>
                                <img src="../assets/uploads/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                     alt="Current event image">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <a href="events.php" class="btn cancel-btn">Cancel</a>
                        <button type="submit" class="btn submit-btn">Update Event</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validate end date is after start date
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    
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
