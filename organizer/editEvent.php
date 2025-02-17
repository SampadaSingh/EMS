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
$error_message = '';
$success_message = '';

try {
    // Fetch event details
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $event_id, $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error_message'] = "Event not found or you don't have permission to edit it.";
        header('Location: events.php');
        exit();
    }

    $event = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate required fields
        if (empty($_POST['event_title']) || empty($_POST['event_venue']) || empty($_POST['event_location']) ||
            empty($_POST['start_time']) || empty($_POST['end_time']) || empty($_POST['start_date']) || 
            empty($_POST['end_date']) || empty($_POST['organizer_name']) || empty($_POST['organizer_contact'])) {
            $error_message = "All fields are required except event fee and image.";
        } else {
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
            $event_fee = $_POST['event_fee'] ?? 0;

            // Handle image upload
            $image_path = $event['event_image']; // Keep existing image by default
            if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
                $file_type = $_FILES['event_image']['type'];

                if (!in_array($file_type, $allowed_types)) {
                    $error_message = "Only JPG, JPEG & PNG files are allowed.";
                } else {
                    $file_name = uniqid() . '_' . basename($_FILES['event_image']['name']);
                    $upload_dir = '../uploads/';
                    
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $upload_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                        // Delete old image if exists
                        if ($event['event_image'] && file_exists($upload_dir . $event['event_image'])) {
                            unlink($upload_dir . $event['event_image']);
                        }
                        $image_path = $file_name;
                    } else {
                        $error_message = "Failed to upload image.";
                    }
                }
            }

            if (empty($error_message)) {
                // Update event in database
                $query = "UPDATE events SET 
                    event_title = ?, event_venue = ?, event_location = ?,
                    start_time = ?, end_time = ?, start_date = ?, end_date = ?,
                    event_fee = ?, organizer_name = ?, organizer_contact = ?,
                    event_description = ?, event_image = ?
                    WHERE id = ? AND organizer_id = ?";

                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssssssssssii",
                    $event_title, $event_venue, $event_location,
                    $start_time, $end_time, $start_date, $end_date,
                    $event_fee, $organizer_name, $organizer_contact,
                    $event_description, $image_path, $event_id, $organizer_id
                );

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Event updated successfully!";
                    header("Location: events.php");
                    exit();
                } else {
                    $error_message = "Error updating event: " . $stmt->error;
                }
            }
        }
    }
} catch (Exception $e) {
    $error_message = "An error occurred: " . $e->getMessage();
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
        input[type="tel"],
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

        .success-message {
            background: #d4edda;
            color: #2e865f;
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
    <script src="../assets/js/script.js"></script>
    <script>
        function validateForm() {
            // Get form elements
            const organizerName = document.getElementById('organizer_name').value;
            const organizerContact = document.getElementById('organizer_contact').value;
            const eventTitle = document.getElementById('event_title').value;

            // Name validation (no numbers allowed)
            const nameRegex = /^[A-Za-z\s]+$/;
            if (!nameRegex.test(organizerName)) {
                alert('Organizer name should only contain letters and spaces');
                return false;
            }

            // Phone number validation (exactly 10 digits)
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(organizerContact)) {
                alert('Contact number must be exactly 10 digits');
                return false;
            }

            // Event title validation (alphanumeric and spaces only)
            const titleRegex = /^[A-Za-z0-9\s]+$/;
            if (!titleRegex.test(eventTitle)) {
                alert('Event title should only contain letters, numbers, and spaces');
                return false;
            }

            return true;
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <h1>Edit Event</h1>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="form-grid">
                <div class="form-group">
                    <label for="event_title">Event Title*</label>
                    <input type="text" id="event_title" name="event_title" value="<?php echo htmlspecialchars($event['event_title']); ?>" pattern="[A-Za-z0-9\s]+" title="Only letters, numbers, and spaces allowed" required>
                </div>

                <div class="form-group">
                    <label for="event_venue">Event Venue*</label>
                    <input type="text" id="event_venue" name="event_venue" value="<?php echo htmlspecialchars($event['event_venue']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_location">Event Location*</label>
                    <input type="text" id="event_location" name="event_location" value="<?php echo htmlspecialchars($event['event_location']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_fee">Entry Fee (Rs.)</label>
                    <input type="number" id="event_fee" name="event_fee" min="0" step="0.01" value="<?php echo htmlspecialchars($event['event_fee']); ?>">
                </div>

                <div class="form-group">
                    <label for="start_date">Start Date*</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $event['start_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date*</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $event['end_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time*</label>
                    <input type="time" id="start_time" name="start_time" value="<?php echo $event['start_time']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time*</label>
                    <input type="time" id="end_time" name="end_time" value="<?php echo $event['end_time']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="organizer_name">Organizer Name*</label>
                    <input type="text" id="organizer_name" name="organizer_name" value="<?php echo htmlspecialchars($event['organizer_name']); ?>" pattern="[A-Za-z\s]+" title="Only letters and spaces allowed" required>
                </div>

                <div class="form-group">
                    <label for="organizer_contact">Organizer Contact*</label>
                    <input type="tel" id="organizer_contact" name="organizer_contact" value="<?php echo htmlspecialchars($event['organizer_contact']); ?>" pattern="\d{10}" title="Please enter exactly 10 digits" required>
                </div>

                <div class="form-group full-width">
                    <label for="event_description">Event Description*</label>
                    <textarea id="event_description" name="event_description" required><?php echo htmlspecialchars($event['event_description']); ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label for="event_image">Event Image</label>
                    <?php if ($event['event_image']): ?>
                        <div class="current-image">
                            <p>Current Image:</p>
                            <img src="../uploads/<?php echo htmlspecialchars($event['event_image']); ?>" 
                                 alt="Current event image">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                </div>

                <div class="form-actions">
                    <a href="events.php" class="btn cancel-btn">Cancel</a>
                    <button type="submit" class="btn submit-btn">Update Event</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</body>
</html>
