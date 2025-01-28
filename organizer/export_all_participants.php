<?php
include '../config/connect.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../php/login.php');
    exit();
}

$organizer_id = $_SESSION['user_id'];

$search = $_GET['search'] ?? '';
$event_filter = $_GET['event'] ?? '';
$date_filter = $_GET['date'] ?? '';

$query = "SELECT p.p_name, p.p_email, p.p_phone, p.event_title, p.created_at, e.event_fee 
          FROM participants p
          JOIN events e ON p.event_title = e.event_title
          WHERE e.organizer_id = ?";
$params = [$organizer_id];
$types = "i";

if ($search) {
    $query .= " AND (p.p_name LIKE ? OR p.p_email LIKE ? OR p.p_phone LIKE ?)";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param, $search_param);
    $types .= "sss";
}

if ($event_filter) {
    $query .= " AND p.event_title = ?";
    array_push($params, $event_filter);
    $types .= "s";
}

if ($date_filter) {
    switch ($date_filter) {
        case 'today':
            $query .= " AND DATE(p.created_at) = CURDATE()";
            break;
        case 'week':
            $query .= " AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $query .= " AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
    }
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="all_participants.csv"');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, array('Name', 'Email', 'Phone', 'Event', 'Registration Date', 'Event Fee'));

while ($row = $result->fetch_assoc()) {
    $registration_date = date('M j, Y g:i A', strtotime($row['created_at']));
    fputcsv($output, array(
        $row['p_name'],
        $row['p_email'],
        $row['p_phone'],
        $row['event_title'],
        $registration_date,
        'Rs. ' . number_format($row['event_fee'], 2)
    ));
}

fclose($output);
exit();
?>
