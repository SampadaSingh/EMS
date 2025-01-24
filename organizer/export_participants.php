<?php
include '../config/connect.php';
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_title = $_GET['event_title'] ?? '';

// Verify the event belongs to the organizer
$event_query = "SELECT * FROM events WHERE event_title = ? AND organizer_id = ?";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("si", $event_title, $organizer_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    header('Location: events.php');
    exit();
}

// Fetch participants
$query = "SELECT p.p_name, p.p_email, p.p_phone, p.created_at 
          FROM participants p 
          WHERE p.event_title = ?
          ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $event_title);
$stmt->execute();
$result = $stmt->get_result();

// Set headers for Excel download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $event_title . '_participants.csv"');

// Create a file pointer connected to PHP output
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add headers
fputcsv($output, array('Name', 'Email', 'Phone', 'Registration Date'));

// Add data rows
while ($row = $result->fetch_assoc()) {
    $registration_date = date('M j, Y g:i A', strtotime($row['created_at']));
    fputcsv($output, array(
        $row['p_name'],
        $row['p_email'],
        $row['p_phone'],
        $registration_date
    ));
}

// Close the file pointer
fclose($output);
exit();
?>
