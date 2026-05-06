<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

checkAdminAuth();
$connection = Database::getConnection();
$today = date('Y-m-d');

$data = [
    'total_doctors' => (int) $connection->query('SELECT COUNT(*) AS total FROM doctors')->fetch_assoc()['total'],
    'total_appointments' => (int) $connection->query('SELECT COUNT(*) AS total FROM appointments')->fetch_assoc()['total'],
    'today_appointments' => 0,
    'pending_appointments' => (int) $connection->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'pending'")->fetch_assoc()['total'],
    'completed_appointments' => (int) $connection->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'done'")->fetch_assoc()['total'],
    'cancelled_appointments' => (int) $connection->query("SELECT COUNT(*) AS total FROM appointments WHERE status = 'cancelled'")->fetch_assoc()['total'],
    'admin_name' => $_SESSION['admin_name'] ?? 'Admin'
];

$todayStatement = $connection->prepare('SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ?');
$todayStatement->bind_param('s', $today);
$todayStatement->execute();
$todayResult = $todayStatement->get_result();
$todayRow = $todayResult ? $todayResult->fetch_assoc() : null;
$data['today_appointments'] = (int) ($todayRow['total'] ?? 0);

sendJsonResponse('success', null, $data);
