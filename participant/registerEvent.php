<?php
session_start();

include '../config/connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to register for events.');</script>";
    exit();
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id <= 0) {
    echo "<script>alert('Invalid event. Please try again.');</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

$user_query = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_query->bind_result($p_name, $p_email, $p_phone);
$user_query->fetch();
$user_query->close();

if (empty($p_name) || empty($p_email) || empty($p_phone)) {
    echo "<script>alert('User details not found. Please log in again.');</script>";
    exit();
}

$event_query = $conn->prepare("SELECT event_title FROM events WHERE id = ?");
$event_query->bind_param("i", $event_id);
$event_query->execute();
$event_query->bind_result($event_title);
$event_query->fetch();
$event_query->close();

if (empty($event_title)) {
    echo "<script>alert('Event not found. Please try again.');</script>";
    exit();
}

$check_query = $conn->prepare("SELECT * FROM participants WHERE p_email = ? AND event_title = ?");
$check_query->bind_param("ss", $p_email, $event_title);
$check_query->execute();
$check_query->store_result();

if ($check_query->num_rows > 0) {
    echo "<script>alert('You are already registered for this event.');</script>";
    echo "<script>window.location = 'myEvents.php';</script>";
} else {
    $stmt = $conn->prepare("INSERT INTO participants (p_name, p_email, p_phone, event_title, event_id, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssi", $p_name, $p_email, $p_phone, $event_title, $event_id);

    if ($stmt->execute()) {

        echo "<script>alert('Registration successful!');</script>";
        echo "<script>window.location = 'myEvents.php';</script>";
    } else {
        echo "<script>alert('Error during registration. Please try again later.');</script>";
    }

    $stmt->close();
}

$check_query->close();
$conn->close();
