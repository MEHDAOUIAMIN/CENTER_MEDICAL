<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/helpers.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJsonResponse('error', 'Invalid request method');
}

$conn = Database::getConnection();
$fullname = $_POST['fullname'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$doctor_id = $_POST['doctor_id'] ?? '';
$date = $_POST['appointment_date'] ?? '';
$time = $_POST['appointment_time'] ?? '';
$status = "pending";

$stmt = $conn->prepare("
    INSERT INTO appointments (fullname, phone, email, doctor_id, appointment_date, appointment_time, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("sssisss", $fullname, $phone, $email, $doctor_id, $date, $time, $status);

if ($stmt->execute()) {
    sendJsonResponse('success', 'Appointment booked successfully');
} else {
    sendJsonResponse('error', 'Database Error');
}
