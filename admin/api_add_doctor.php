<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkAdminAuth();

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', 'Invalid method');
}

$conn = Database::getConnection();
$data = getJsonInput();
$name = $data['name'] ?? '';
$spec = $data['speciality'] ?? '';
$phone = $data['phone'] ?? '';
$days = $data['days'] ?? '';

$stmt = $conn->prepare("INSERT INTO doctors (name, speciality, phone, working_days) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $spec, $phone, $days);

if($stmt->execute()) {
    sendJsonResponse('success');
} else {
    sendJsonResponse('error', 'Database Error');
}
