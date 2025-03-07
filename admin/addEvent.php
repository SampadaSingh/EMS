<?php
session_start();
include '../config/connect.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

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

    // Validate contact number
    if (!preg_match("/^\d{10}$/", $organizerContact)) {
        $_SESSION['error_message'] = "Contact number must be exactly 10 digits";
    } 
    // Validate organizer name
    else if (!preg_match("/^[A-Za-z\s]+$/", $organizerName)) {
        $_SESSION['error_message'] = "Organizer name should only contain letters and spaces";
    }
    else {
        // Handle image upload
        $eventImage = '';
        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0) {
            $targetDir = "../assets/uploads/";
            $imageFileType = strtolower(pathinfo($_FILES["event_image"]["name"], PATHINFO_EXTENSION));
            
            // Check file type
            if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
                $_SESSION['error_message'] = "Sorry, only JPG, JPEG & PNG files are allowed.";
            } else {
                $newFileName = "IMG-" . uniqid() . "." . $imageFileType;
                $targetFile = $targetDir . $newFileName;

                // Check if image file is a actual image
                $check = getimagesize($_FILES["event_image"]["tmp_name"]);
                if ($check === false) {
                    $_SESSION['error_message'] = "File is not an image.";
                } else if (!move_uploaded_file($_FILES["event_image"]["tmp_name"], $targetFile)) {
                    $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
                } else {
                    $eventImage = $newFileName;
                }
            }
        }

        if (!isset($_SESSION['error_message'])) {
            $sql = "INSERT INTO events (event_title, event_venue, event_location, start_time, end_time, 
                    start_date, end_date, event_description, organizer_name, organizer_contact, 
                    event_image, event_fee) 
                    VALUES ('$title', '$venue', '$location', '$startTime', '$endTime', 
                    '$startDate', '$endDate', '$description', '$organizerName', '$organizerContact', 
                    '$eventImage', '$eventFee')";
            
            if ($conn->query($sql)) {
                $_SESSION['success_message'] = "Event added successfully!";
                header('Location: manageEvents.php');
                exit();
            } else {
                $_SESSION['error_message'] = "Error adding event: " . $conn->error;
            }
        }
    }
}

// Get any messages
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear the messages
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - Admin</title>
    <style>

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

        .success-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .error-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <div class="header">
            <h1>Add New Event</h1>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="event_title">Event Title</label>
                    <input type="text" id="event_title" name="event_title" required>
                </div>

                <div class="form-group">
                    <label for="event_venue">Venue</label>
                    <input type="text" id="event_venue" name="event_venue" required>
                </div>

                <div class="form-group">
                    <label for="event_location">Location</label>
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
                    <label for="event_description">Description</label>
                    <textarea id="event_description" name="event_description" required></textarea>
                </div>

                <div class="form-group">
                    <label for="organizer_name">Organizer Name</label>
                    <input type="text" id="organizer_name" name="organizer_name" required>
                </div>

                <div class="form-group">
                    <label for="organizer_contact">Organizer Contact</label>
                    <input type="tel" id="organizer_contact" name="organizer_contact" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
                </div>

                <div class="form-group">
                    <label for="event_image">Event Image</label>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="event_fee">Event Fee (Rs.)</label>
                    <input type="number" id="event_fee" name="event_fee" min="0" step="0.01" value="0.00" required>
                </div>

                <div class="btn-container">
                    <button type="submit" class="submit-btn">Add Event</button>
                    <a href="manageEvents.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
