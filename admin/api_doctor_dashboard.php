<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkDoctorAuth();
$conn = Database::getConnection();
$doctor_id = $_SESSION['doctor_id'];
$today = date('Y-m-d');

$data = [
    'doctor_name' => $_SESSION['doctor_name'],
    'total' => $conn->query("SELECT COUNT(*) t FROM appointments WHERE doctor_id=$doctor_id")->fetch_assoc()['t'],
    'today' => $conn->query("SELECT COUNT(*) t FROM appointments WHERE doctor_id=$doctor_id AND appointment_date='$today'")->fetch_assoc()['t'],
    'pending' => $conn->query("SELECT COUNT(*) t FROM appointments WHERE doctor_id=$doctor_id AND status='pending'")->fetch_assoc()['t'],
    'done' => $conn->query("SELECT COUNT(*) t FROM appointments WHERE doctor_id=$doctor_id AND status='done'")->fetch_assoc()['t'],
];

$last = $conn->query("SELECT fullname, appointment_date, appointment_time, status FROM appointments WHERE doctor_id=$doctor_id ORDER BY appointment_date DESC LIMIT 5");
$appointments = [];
if($last) {
    while($r = $last->fetch_assoc()) $appointments[] = $r;
}
$data['last_appointments'] = $appointments;

sendJsonResponse('success', null, $data);
