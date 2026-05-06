<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

checkDoctorAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', 'Invalid method');
}

$connection = Database::getConnection();
$doctorId = (int) $_SESSION['doctor_id'];
$payload = getJsonInput();

$appointmentId = (int) ($payload['appt_id'] ?? 0);
$notes = cleanInput($payload['notes'] ?? '');

if ($appointmentId <= 0 || $notes === '') {
    sendJsonResponse('error', 'Appointment and notes are required');
}

$hasPatientIdColumn = tableHasColumn($connection, 'appointments', 'patient_id');

if ($hasPatientIdColumn) {
    $appointmentStatement = $connection->prepare(
        'SELECT id, patient_id, email FROM appointments WHERE id = ? AND doctor_id = ? LIMIT 1'
    );
    $appointmentStatement->bind_param('ii', $appointmentId, $doctorId);
} else {
    $appointmentStatement = $connection->prepare(
        'SELECT id, email FROM appointments WHERE id = ? AND doctor_id = ? LIMIT 1'
    );
    $appointmentStatement->bind_param('ii', $appointmentId, $doctorId);
}

$appointmentStatement->execute();
$appointmentResult = $appointmentStatement->get_result();
$appointment = $appointmentResult ? $appointmentResult->fetch_assoc() : null;

if (!$appointment) {
    sendJsonResponse('error', 'Appointment not found');
}

$patientId = isset($appointment['patient_id']) ? (int) $appointment['patient_id'] : 0;

if ($patientId <= 0) {
    $patientStatement = $connection->prepare('SELECT id FROM patients WHERE email = ? LIMIT 1');
    $patientStatement->bind_param('s', $appointment['email']);
    $patientStatement->execute();
    $patientResult = $patientStatement->get_result();
    $patient = $patientResult ? $patientResult->fetch_assoc() : null;
    $patientId = $patient ? (int) $patient['id'] : 0;
}

if ($patientId <= 0) {
    sendJsonResponse('error', 'Patient not found for this appointment');
}

$consultationDate = date('Y-m-d');

$insertStatement = $connection->prepare(
    'INSERT INTO consultations (rendez_vous_id, doctor_id, patient_id, notes, consultation_date)
     VALUES (?, ?, ?, ?, ?)'
);
$insertStatement->bind_param('iiiss', $appointmentId, $doctorId, $patientId, $notes, $consultationDate);

if (!$insertStatement->execute()) {
    sendJsonResponse('error', 'Unable to save consultation');
}

$updateStatement = $connection->prepare("UPDATE appointments SET status = 'done' WHERE id = ? AND doctor_id = ?");
$updateStatement->bind_param('ii', $appointmentId, $doctorId);
$updateStatement->execute();

sendJsonResponse('success', 'Consultation added');
