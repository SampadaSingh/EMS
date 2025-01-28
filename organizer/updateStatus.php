<?php
include '../config/connect.php';
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$registration_id = $_GET['id'] ?? 0;
$new_status = $_GET['status'] ?? '';

// Verify the registration belongs to one of the organizer's events
$stmt = $conn->prepare("
    SELECT r.id 
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    WHERE r.id = ? AND e.organizer_id = ?
");
$stmt->bind_param("ii", $registration_id, $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0 && in_array($new_status, ['attended', 'cancelled'])) {
    // Update the registration status
    $update_stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $registration_id);
    $update_stmt->execute();
}

// Redirect back to participants page
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>
