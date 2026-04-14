<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkAdminAuth();
$conn = Database::getConnection();

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    $id = intval($_GET['id'] ?? 0);
    $result = $conn->query("SELECT * FROM doctors WHERE id=$id");
    if($result && $result->num_rows > 0){
        sendJsonResponse('success', null, $result->fetch_assoc());
    }
    sendJsonResponse('error', 'Doctor not found');
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = getJsonInput();
    $id = intval($data['id'] ?? 0);
    $name = $data['name'] ?? '';
    $spec = $data['speciality'] ?? '';
    $phone = $data['phone'] ?? '';
    $days = $data['days'] ?? '';

    $stmt = $conn->prepare("UPDATE doctors SET name=?, speciality=?, phone=?, working_days=? WHERE id=?");
    $stmt->bind_param("ssssi", $name, $spec, $phone, $days, $id);

    if($stmt->execute()) sendJsonResponse('success');
    sendJsonResponse('error', 'Database Error');
}
