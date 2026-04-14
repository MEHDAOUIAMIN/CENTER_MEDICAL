<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
$conn = Database::getConnection();

if($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonResponse('error', 'Invalid method');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT * FROM patients WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 1){
    $user = $result->fetch_assoc();
    if(password_verify($password, $user['password'])){
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['patient_id'] = $user['id'];
        $_SESSION['patient_name'] = $user['name'];
        $_SESSION['patient_email'] = $user['email'];
        sendJsonResponse('success', null, ['redirect'=>'dashboard.html']);
    }
}
sendJsonResponse('error', 'Invalid email or password');
