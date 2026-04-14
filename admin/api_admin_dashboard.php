<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkAdminAuth();
$conn = Database::getConnection();
$today = date('Y-m-d');

$data = [
    'total_doctors' => $conn->query("SELECT COUNT(*) as t FROM doctors")->fetch_assoc()['t'],
    'total_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments")->fetch_assoc()['t'],
    'today_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE appointment_date='$today'")->fetch_assoc()['t'],
    'pending_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE status='pending'")->fetch_assoc()['t'],
    'completed_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE status='done'")->fetch_assoc()['t'],
    'cancelled_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE status='cancelled'")->fetch_assoc()['t'],
    'admin_name' => $_SESSION['admin_name']
];

sendJsonResponse('success', null, $data);
