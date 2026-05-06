<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

checkAdminAuth();

$connection = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = getJsonInput();
    $appointmentId = (int) ($payload['id'] ?? 0);
    $action = $payload['action'] ?? '';

    if ($appointmentId <= 0 || !in_array($action, ['accept', 'cancel'], true)) {
        sendJsonResponse('error', 'Invalid parameters');
    }

    $status = $action === 'accept' ? 'done' : 'cancelled';
    $statement = $connection->prepare('UPDATE appointments SET status = ? WHERE id = ?');
    $statement->bind_param('si', $status, $appointmentId);
    $statement->execute();

    sendJsonResponse('success');
}

$result = $connection->query(
    'SELECT a.id, a.fullname, a.phone, d.name AS doctor_name, a.appointment_date, a.appointment_time, a.status
     FROM appointments a
     INNER JOIN doctors d ON d.id = a.doctor_id
     ORDER BY a.appointment_date DESC, a.appointment_time DESC'
);

$appointments = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

sendJsonResponse('success', null, $appointments);
