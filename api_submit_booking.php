<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/helpers.php';

startAppSession();

if (!isset($_SESSION['patient_id'])) {
    sendJsonResponse('error', 'Patient not authenticated');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', 'Invalid request method');
}

$connection = Database::getConnection();
$patientId = (int) $_SESSION['patient_id'];
$doctorId = (int) ($_POST['doctor_id'] ?? 0);
$appointmentDate = cleanInput($_POST['appointment_date'] ?? '');
$appointmentTime = cleanInput($_POST['appointment_time'] ?? '');
$status = 'pending';

if ($patientId <= 0 || $doctorId <= 0 || $appointmentDate === '' || $appointmentTime === '') {
    sendJsonResponse('error', 'All fields are required');
}

if (!isValidDate($appointmentDate) || !isValidTime($appointmentTime)) {
    sendJsonResponse('error', 'Invalid date or time');
}

$patientStatement = $connection->prepare('SELECT id, name, email, phone FROM patients WHERE id = ? LIMIT 1');
$patientStatement->bind_param('i', $patientId);
$patientStatement->execute();
$patientResult = $patientStatement->get_result();
$patient = $patientResult ? $patientResult->fetch_assoc() : null;

if (!$patient) {
    sendJsonResponse('error', 'Patient not found');
}

$doctorStatement = $connection->prepare('SELECT id FROM doctors WHERE id = ? LIMIT 1');
$doctorStatement->bind_param('i', $doctorId);
$doctorStatement->execute();
$doctorResult = $doctorStatement->get_result();

if (!$doctorResult || $doctorResult->num_rows === 0) {
    sendJsonResponse('error', 'Doctor not found');
}

$fullName = $patient['name'];
$email = $patient['email'];
$phone = (string) ($patient['phone'] ?? '');
$hasPatientIdColumn = tableHasColumn($connection, 'appointments', 'patient_id');

if ($hasPatientIdColumn) {
    $statement = $connection->prepare(
        'INSERT INTO appointments (patient_id, fullname, phone, email, doctor_id, appointment_date, appointment_time, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param('isssisss', $patientId, $fullName, $phone, $email, $doctorId, $appointmentDate, $appointmentTime, $status);
} else {
    $statement = $connection->prepare(
        'INSERT INTO appointments (fullname, phone, email, doctor_id, appointment_date, appointment_time, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->bind_param('sssisss', $fullName, $phone, $email, $doctorId, $appointmentDate, $appointmentTime, $status);
}

if (!$statement->execute()) {
    sendJsonResponse('error', 'Database error');
}

sendJsonResponse('success', 'Appointment booked successfully');
