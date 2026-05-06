<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', 'Invalid method');
}

$conn = Database::getConnection();

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $phone === '' || $password === '') {
    sendJsonResponse('error', 'All fields are required');
}

if ($password !== $confirmPassword) {
    sendJsonResponse('error', 'Passwords do not match');
}

$userCode = generateUserCode($conn, 'patients', '2', '00');
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO patients (user_code, speciality_code, name, email, phone, password) VALUES (?, '00', ?, ?, ?, ?)");
$stmt->bind_param("sssss", $userCode, $name, $email, $phone, $hashedPassword);

if (!$stmt->execute()) {
    sendJsonResponse('error', 'Unable to create account. Email may already exist.');
}

sendJsonResponse('success', 'Account created successfully', [
    'redirect' => 'login.php',
    'user_code' => $userCode
]);
?>
