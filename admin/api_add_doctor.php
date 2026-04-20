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
$specialityCode = normalizeSpecialityCode($data['speciality_code'] ?? '00');
$phone = $data['phone'] ?? '';
$days = $data['days'] ?? '';
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    sendJsonResponse('error', 'Name, email and password are required');
}

$userCode = generateUserCode($conn, 'doctors', '1', $specialityCode);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO doctors (user_code, speciality_code, name, speciality, phone, working_days, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $userCode, $specialityCode, $name, $spec, $phone, $days, $email, $hashedPassword);

if($stmt->execute()) {
    sendJsonResponse('success', null, ['user_code' => $userCode]);
} else {
    sendJsonResponse('error', 'Database Error');
}
