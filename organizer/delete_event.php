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

// Verify event belongs to organizer and delete it
$stmt = $conn->prepare("SELECT event_image FROM events WHERE id = ? AND organizer_id = ?");
$stmt->bind_param("ii", $event_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $event = $result->fetch_assoc();
    
    // Delete the event image if it exists
    if ($event['event_image']) {
        $image_path = '../assets/uploads/' . $event['event_image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete event registrations first (foreign key constraint)
    $stmt = $conn->prepare("DELETE FROM registrations WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    // Delete the event
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
    $stmt->bind_param("ii", $event_id, $organizer_id);
    $stmt->execute();
}

// Redirect back to events page
header('Location: events.php?deleted=1');
exit();
?>
