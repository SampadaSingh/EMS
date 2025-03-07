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
        empty($organizer_name) || empty($organizer_contact) || empty($event_description)
    ) {
        $error_message = "All fields are required except event fee and image.";
    } else {
        /*Handle image upload*/
        $image_name = null;
        if ($event_image) {
            $image_name = basename($event_image['name']);
            $target_path = '../assets/uploads/' . $image_name;

            if (!move_uploaded_file($event_image['tmp_name'], $target_path)) {
                $error_message = "Error uploading image";
            }
        }


        if (empty($error_message)) {
            $query = "INSERT INTO events (
                event_title, event_venue, event_location, start_time, end_time,
                start_date, end_date, event_fee, organizer_name, organizer_contact,
                event_description, event_image, organizer_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            try {
                $stmt = $conn->prepare($query);

                if ($stmt) {
                    $stmt->bind_param(
                        "ssssssssssssi",
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
                        $image_name,
                        $organizer_id
                    );

                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Event created successfully!";
                        header("Location: events.php");
                        exit();
                    } else {
                        $error_message = "Error executing query: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Error preparing statement: " . $conn->error;
                }
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
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
    <script>
        function validateForm() {
            // Get form elements
            const organizerName = document.getElementById('organizer_name').value;
            const organizerContact = document.getElementById('organizer_contact').value;
            const eventTitle = document.getElementById('event_title').value;

            //organizer name
            const nameRegex = /^[A-Za-z\s]+$/;
            if (!nameRegex.test(organizerName)) {
                alert('Organizer name should only contain letters and spaces');
                return false;
            }

            // Phone number
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(organizerContact)) {
                alert('Contact number must be exactly 10 digits');
                return false;
            }

            // Event title
            const titleRegex = /^[A-Za-z0-9\s'-:]+$/;
            if (!titleRegex.test(eventTitle)) {
                alert('Event title should only contain letters, numbers, and spaces');
                return false;
            }

            return true;
        }
    </script>
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

        <form class="event-form" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="form-grid">
                <div class="form-group">
                    <label for="event_title">Event Title*</label>
                    <input type="text" id="event_title" name="event_title" pattern="[A-Za-z0-9\s'-:]+" title="Only letters, numbers, spaces, hyphens, apostrophes, and colons allowed" required>
                </div>

                <div class="form-group">
                    <label for="event_venue">Event Venue*</label>
                    <input type="text" id="event_venue" name="event_venue" required>
                </div>

                <div class="form-group">
                    <label for="event_location">Event Location*</label>
                    <input type="text" id="event_location" name="event_location" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time*</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time*</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date*</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date*</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>

                <div class="form-group">
                    <label for="event_fee">Event Fee</label>
                    <input type="number" id="event_fee" name="event_fee" value="0" min="0">
                </div>

                <div class="form-group">
                    <label for="organizer_name">Organizer Name*</label>
                    <input type="text" id="organizer_name" name="organizer_name" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" required>
                </div>

                <div class="form-group">
                    <label for="organizer_contact">Organizer Contact*</label>
                    <input type="tel" id="organizer_contact" name="organizer_contact" pattern="\d{10}" title="Please enter exactly 10 digits" required>
                </div>
            </div>

            <div class="form-group">
                <label for="event_description">Event Description*</label>
                <textarea id="event_description" name="event_description" required></textarea>
            </div>

            <div class="form-group">
                <label for="event_image">Event Image</label>
                <input type="file" id="event_image" name="event_image" accept="image/jpeg,image/png,image/jpg">
            </div>

            <button type="submit" class="submit-btn">Create Event</button>
        </form>
    </div>
</body>
<script>
    function validateTitle() {
        const title = document.getElementById('event_title').value.trim();
        if (title.length < 2 || title.length > 100) {
            alert('Enter a valid title');
            return false;
        }
        return true;
    }

    function validateDate() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const today = new Date();

        const start = new Date(startDate);
        const end = new Date(endDate);

        if (start < today || end < today) {
            alert('Invalid Date');
            return false;
        }

        if (end < start) {
            alert('Invalid Date');
            return false;
        }
        return true;
    }

    function validateTime() {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;

        if (startTime && endTime) {
            const start = new Date(`1970-01-01T${startTime}:00`);
            const end = new Date(`1970-01-01T${endTime}:00`);
            if (end <= start) {

                alert('End Time must be after Start Time');
                return false;
            }
        }
        return true;
    }

    function validateFee() {
        const fee = document.getElementById('event_fee').value.trim();
        if (isNaN(fee) || Number(fee) < 0) {
            alert('Invalid Fee: Please enter a valid fee.');
            return false;
        }
        return true;
    }

    function validateName() {
        const name = document.getElementById('organizer_name').value.trim();

        if (!name) {
            alert('Name cannot be empty');
            return false;
        }

        if (!/^[a-zA-Z\s'-]+$/.test(name)) {
            alert('Invalid Name: Only letters and spaces are allowed.');
            return false;
        }
        return true;
    }


    function validateContact() {
        const contact = document.getElementById('organizer_contact').value;
        if (isNaN(contact) || contact.length !== 10) {
            alert('Invalid Contact');
            return false;
        }
        return true;
    }

    function validateForm() {
        return (
            validateTitle() &&
            validateFee() &&
            validateDate() &&
            validateTime() &&
            validateName() &&
            validateContact()
        );
    }

    function handleSubmit(event) {
        if (!validateForm()) {
            event.preventDefault();
        }
    };

    window.onload = function() {
        const form = document.querySelector('.event-form');
        form.addEventListener('submit', handleSubmit);
        form.addEventListener('keypress', preventEnterSubmit);
    };

    function preventEnterSubmit(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    }
</script>

</html>