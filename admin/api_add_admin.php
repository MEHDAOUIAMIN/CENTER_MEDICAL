<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

checkAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', 'Invalid method');
}

$data = getJsonInput();
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    sendJsonResponse('error', 'Name, email and password are required');
}

$conn = Database::getConnection();
$userCode = generateUserCode($conn, 'admins', '3', '00');
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (user_code, speciality_code, name, email, password) VALUES (?, '00', ?, ?, ?)");
$stmt->bind_param("ssss", $userCode, $name, $email, $hashedPassword);

if (!$stmt->execute()) {
    sendJsonResponse('error', 'Unable to create admin account. Email may already exist.');
}

sendJsonResponse('success', 'Admin account created successfully', ['user_code' => $userCode]);
?>
