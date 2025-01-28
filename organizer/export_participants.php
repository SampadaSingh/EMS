<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'] ?? '';
$event_title = $_GET['event_title'] ?? '';

$event_query = "SELECT * FROM events WHERE id = ? AND organizer_id = ?";
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("ii", $event_id, $organizer_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    header('Location: events.php');
    exit();
}

$query = "SELECT p.p_name, p.p_email, p.p_phone, p.created_at 
          FROM participants p 
          WHERE p.event_id= ?
          ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $event_title . '_participants.csv"');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, array('Name', 'Email', 'Phone', 'Registration Date'));

while ($row = $result->fetch_assoc()) {
    $registration_date = date('M j, Y g:i A', strtotime($row['created_at']));
    fputcsv($output, array(
        $row['p_name'],
        $row['p_email'],
        $row['p_phone'],
        $registration_date
    ));
}

fclose($output);
exit();
