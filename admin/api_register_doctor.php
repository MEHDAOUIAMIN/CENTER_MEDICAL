<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../utils/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', 'Invalid method');
}

$secretCode = trim($_POST['secret_code'] ?? '');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$speciality = trim($_POST['speciality'] ?? '');
$specialityCode = normalizeSpecialityCode($_POST['speciality_code'] ?? '00');
$phone = trim($_POST['phone'] ?? '');
$days = trim($_POST['days'] ?? '');

if ($secretCode !== DOCTOR_REGISTRATION_SECRET) {
    sendJsonResponse('error', 'Invalid doctor secret code');
}

if ($name === '' || $email === '' || $password === '' || $speciality === '') {
    sendJsonResponse('error', 'All required fields must be filled');
}

if ($password !== $confirmPassword) {
    sendJsonResponse('error', 'Passwords do not match');
}

$conn = Database::getConnection();
$userCode = generateUserCode($conn, 'doctors', '1', $specialityCode);
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO doctors (user_code, speciality_code, name, speciality, phone, working_days, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $userCode, $specialityCode, $name, $speciality, $phone, $days, $email, $hashedPassword);

if (!$stmt->execute()) {
    sendJsonResponse('error', 'Unable to create doctor account. Email may already exist.');
}

sendJsonResponse('success', 'Doctor account created successfully', [
    'user_code' => $userCode,
    'redirect' => 'login.php'
]);
?>
