<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION['user_role'] ?? '';
if ($userRole !== 'admin' && $userRole !== 'doctor') {
    sendJsonResponse('error', 'Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse('error', 'Invalid method');
}

$data = getJsonInput();
$currentPassword = $data['current_password'] ?? '';
$newPassword = $data['new_password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    sendJsonResponse('error', 'All password fields are required');
}

if ($newPassword !== $confirmPassword) {
    sendJsonResponse('error', 'New password confirmation does not match');
}

$conn = Database::getConnection();
$table = $userRole === 'admin' ? 'admins' : 'doctors';
$idColumn = $userRole . '_id';
$userId = (int) ($_SESSION[$idColumn] ?? 0);

$stmt = $conn->prepare("SELECT password FROM {$table} WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;

if (!$user || !password_verify($currentPassword, $user['password'])) {
    sendJsonResponse('error', 'Current password is incorrect');
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE {$table} SET password = ? WHERE id = ?");
$update->bind_param("si", $hashedPassword, $userId);

if (!$update->execute()) {
    sendJsonResponse('error', 'Unable to update password');
}

sendJsonResponse('success', 'Password updated successfully');
?>
