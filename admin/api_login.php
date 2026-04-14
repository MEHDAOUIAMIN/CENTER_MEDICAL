<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
$conn = Database::getConnection();

if($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonResponse('error', 'Invalid method');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM doctors WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 1){
    $user = $result->fetch_assoc();
    if(password_verify($password, $user['password'])){
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['doctor_id'] = $user['id'];
        $_SESSION['doctor_name'] = $user['name'];
        sendJsonResponse('success', null, ['role'=>'doctor', 'redirect'=>'doctor-dashboard.html']);
    }
    sendJsonResponse('error', 'Incorrect password');
} else {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            sendJsonResponse('success', null, ['role'=>'admin', 'redirect'=>'dashboard.html']);
        }
        sendJsonResponse('error', 'Incorrect password');
    }
    sendJsonResponse('error', 'Email not found');
}
