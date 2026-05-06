<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

checkPatientAuth();

$connection = Database::getConnection();
$patientId = (int) $_SESSION['patient_id'];

$patientStatement = $connection->prepare('SELECT id, user_code, name, email FROM patients WHERE id = ? LIMIT 1');
$patientStatement->bind_param('i', $patientId);
$patientStatement->execute();
$patientResult = $patientStatement->get_result();
$patient = $patientResult ? $patientResult->fetch_assoc() : null;

if (!$patient) {
    sendJsonResponse('error', 'Patient introuvable');
}

$hasPatientIdColumn = tableHasColumn($connection, 'appointments', 'patient_id');

if ($hasPatientIdColumn) {
    $sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, d.name AS doctor_name, c.notes
            FROM appointments a
            INNER JOIN doctors d ON d.id = a.doctor_id
            LEFT JOIN consultations c ON c.rendez_vous_id = a.id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $statement = $connection->prepare($sql);
    $statement->bind_param('i', $patientId);
} else {
    $sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, d.name AS doctor_name, c.notes
            FROM appointments a
            INNER JOIN doctors d ON d.id = a.doctor_id
            LEFT JOIN consultations c ON c.rendez_vous_id = a.id
            WHERE a.email = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $statement = $connection->prepare($sql);
    $statement->bind_param('s', $patient['email']);
}

$statement->execute();
$result = $statement->get_result();

$appointments = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

sendJsonResponse('success', null, [
    'patient_code' => $patient['user_code'],
    'patient_name' => $patient['name'],
    'appointments' => $appointments
]);
