<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

checkDoctorAuth();

$connection = Database::getConnection();
$doctorId = (int) $_SESSION['doctor_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = getJsonInput();
    $appointmentId = (int) ($payload['id'] ?? 0);
    $action = $payload['action'] ?? '';

    if ($appointmentId <= 0 || !in_array($action, ['accept', 'cancel'], true)) {
        sendJsonResponse('error', 'Invalid parameters');
    }

    $status = $action === 'accept' ? 'done' : 'cancelled';
    $statement = $connection->prepare('UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?');
    $statement->bind_param('sii', $status, $appointmentId, $doctorId);
    $statement->execute();

    sendJsonResponse('success');
}

$today = date('Y-m-d');

$counts = [
    'total_appointments' => 0,
    'today_appointments' => 0,
    'pending_appointments' => 0,
    'completed_appointments' => 0,
    'cancelled_appointments' => 0
];

$countQueries = [
    'total_appointments' => 'SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?',
    'today_appointments' => 'SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND appointment_date = ?',
    'pending_appointments' => "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND status = 'pending'",
    'completed_appointments' => "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND status = 'done'",
    'cancelled_appointments' => "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND status = 'cancelled'"
];

foreach ($countQueries as $key => $sql) {
    $statement = $connection->prepare($sql);

    if ($key === 'today_appointments') {
        $statement->bind_param('is', $doctorId, $today);
    } else {
        $statement->bind_param('i', $doctorId);
    }

    $statement->execute();
    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $counts[$key] = (int) ($row['total'] ?? 0);
}

$appointmentsStatement = $connection->prepare(
    'SELECT id, fullname, phone, email, appointment_date, appointment_time, status
     FROM appointments
     WHERE doctor_id = ?
     ORDER BY appointment_date ASC, appointment_time ASC'
);
$appointmentsStatement->bind_param('i', $doctorId);
$appointmentsStatement->execute();
$appointmentsResult = $appointmentsStatement->get_result();

$appointments = [];

if ($appointmentsResult) {
    while ($row = $appointmentsResult->fetch_assoc()) {
        $appointments[] = $row;
    }
}

sendJsonResponse('success', null, $counts + [
    'appointments_list' => $appointments,
    'doctor_name' => $_SESSION['doctor_name'] ?? 'Doctor',
    'today_date' => date('d/m/Y')
]);
