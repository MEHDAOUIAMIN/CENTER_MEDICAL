<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['patient_id'])) {
    sendJsonResponse('error', 'Unauthorized');
}

$conn = Database::getConnection();
$patient_email = $_SESSION['patient_email'];

$stmt = $conn->prepare("
    SELECT a.appointment_date, a.appointment_time, a.status, d.name as doctor_name, c.notes 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    LEFT JOIN consultations c ON c.rendez_vous_id = a.id
    WHERE a.email = ?
    ORDER BY a.appointment_date DESC
");
$stmt->bind_param("s", $patient_email);
$stmt->execute();
$result = $stmt->get_result();

$appts = [];
if($result && $result->num_rows > 0) {
    while($r = $result->fetch_assoc()) $appts[] = $r;
}

sendJsonResponse('success', null, [
    'patient_name' => $_SESSION['patient_name'],
    'appointments' => $appts
]);
