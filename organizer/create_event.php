<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_title = $_POST['event_title'] ?? '';
    $event_venue = $_POST['event_venue'] ?? '';
    $event_location = $_POST['event_location'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $event_fee = $_POST['event_fee'] ?? 0;
    $organizer_name = $_POST['organizer_name'] ?? '';
    $organizer_contact = $_POST['organizer_contact'] ?? '';
    $event_description = $_POST['event_description'] ?? '';
    $event_image = $_FILES['event_image'] ?? null;

    if (
        empty($event_title) || empty($event_venue) || empty($event_location) ||
        empty($start_time) || empty($end_time) || empty($start_date) || empty($end_date) ||
        empty($event_fee) || empty($organizer_name) || empty($organizer_contact) ||
        empty($event_description)
    ) {
        $error_message = "All fields are required.";
    } else {
        $image_path = '';
        if ($event_image && $event_image['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file_type = $event_image['type'];

            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Only JPG, JPEG & PNG files are allowed.";
            } else {
                $file_name = uniqid() . '_' . basename($event_image['name']);
                $upload_path = '../uploads/' . $file_name;

                if (move_uploaded_file($event_image['tmp_name'], $upload_path)) {
                    $image_path = $file_name;
                } else {
                    $error_message = "Failed to upload image.";
                }
            }
        }

        if (empty($error_message)) {
            $query = "INSERT INTO events (event_title, event_venue, event_location, start_time, end_time, 
                     start_date, end_date, event_fee, organizer_name, organizer_contact, 
                     event_description, event_image, organizer_id, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param(
                    "sssssssssssssi",
                    $event_title,
                    $event_venue,
                    $event_location,
                    $start_time,
                    $end_time,
                    $start_date,
                    $end_date,
                    $event_fee,
                    $organizer_name,
                    $organizer_contact,
                    $event_description,
                    $image_path,
                    $organizer_id
                );

                if ($stmt->execute()) {
                    $success_message = "Event created successfully!";
                    header("Location: events.php");
                    exit();
                } else {
                    $error_message = "Error creating event. Please try again.";
                }
                $stmt->close();
            } else {
                $error_message = "Database error. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - EMS</title>
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
            min-height: 100vh;
            padding: 40px;
        }

        .content {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 30px;
        }

        .event-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #17153B;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #17153B;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
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

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #17153B;
        }

        .submit-btn {
            background: #17153B;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #2c2975;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="header">
            <h1>Create Event</h1>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form class="event-form" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="event_title">Event Title</label>
                    <input type="text" id="event_title" name="event_title" required>
                </div>

                <div class="form-group">
                    <label for="event_venue">Event Venue</label>
                    <input type="text" id="event_venue" name="event_venue" required>
                </div>

                <div class="form-group">
                    <label for="event_location">Event Location</label>
                    <input type="text" id="event_location" name="event_location" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>

                <div class="form-group">
                    <label for="event_fee">Event Fee</label>
                    <input type="number" id="event_fee" name="event_fee" step="0.01" value="0.00" required>
                </div>

                <div class="form-group">
                    <label for="organizer_name">Organizer Name</label>
                    <input type="text" id="organizer_name" name="organizer_name" required>
                </div>

                <div class="form-group">
                    <label for="organizer_contact">Organizer Contact</label>
                    <input type="text" id="organizer_contact" name="organizer_contact" required>
                </div>
            </div>

            <div class="form-group">
                <label for="event_description">Event Description</label>
                <textarea id="event_description" name="event_description" required></textarea>
            </div>

            <div class="form-group">
                <label for="event_image">Event Image</label>
                <input type="file" id="event_image" name="event_image" accept="image/*">
            </div>

            <button type="submit" class="submit-btn">Create Event</button>
        </form>
    </div>
</body>

</html>