<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

checkDoctorAuth();

$connection = Database::getConnection();
$doctorId = (int) $_SESSION['doctor_id'];
$today = date('Y-m-d');

$data = [
    'doctor_name' => $_SESSION['doctor_name'] ?? 'Doctor',
    'total' => 0,
    'today' => 0,
    'pending' => 0,
    'done' => 0,
];

$queries = [
    'total' => 'SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?',
    'today' => 'SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND appointment_date = ?',
    'pending' => "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND status = 'pending'",
    'done' => "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND status = 'done'"
];

foreach ($queries as $key => $sql) {
    $statement = $connection->prepare($sql);

    if ($key === 'today') {
        $statement->bind_param('is', $doctorId, $today);
    } else {
        $statement->bind_param('i', $doctorId);
    }

    $statement->execute();
    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $data[$key] = (int) ($row['total'] ?? 0);
}

$appointmentsStatement = $connection->prepare(
    'SELECT fullname, appointment_date, appointment_time, status
     FROM appointments
     WHERE doctor_id = ?
     ORDER BY appointment_date DESC, appointment_time DESC
     LIMIT 10'
);
$appointmentsStatement->bind_param('i', $doctorId);
$appointmentsStatement->execute();
$result = $appointmentsStatement->get_result();

$appointments = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

$data['last_appointments'] = $appointments;

sendJsonResponse('success', null, $data);
?>
