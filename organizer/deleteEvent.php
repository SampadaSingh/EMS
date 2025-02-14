<?php
include '../config/connect.php';
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'] ?? 0; // Changed from 'id' to 'event_id' to match the URL parameter

try {
    // First verify if the event exists and belongs to this organizer
    $stmt = $conn->prepare("SELECT event_image FROM events WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $event_id, $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        
        // Delete the event image if it exists
        if (!empty($event['event_image'])) {
            $image_path = '../uploads/' . $event['event_image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Delete the event
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
        $stmt->bind_param("ii", $event_id, $organizer_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Event deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete event. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Event not found or you don't have permission to delete it.";
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "An error occurred while deleting the event: " . $e->getMessage();
}

// Redirect back to events page
header('Location: events.php');
exit();
?>
