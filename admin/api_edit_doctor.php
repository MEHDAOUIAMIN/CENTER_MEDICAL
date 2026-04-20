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
    $specialityCode = normalizeSpecialityCode($data['speciality_code'] ?? '00');
    $phone = $data['phone'] ?? '';
    $days = $data['days'] ?? '';
    $email = trim($data['email'] ?? '');

    $stmt = $conn->prepare("UPDATE doctors SET name=?, speciality=?, speciality_code=?, phone=?, working_days=?, email=? WHERE id=?");
    $stmt->bind_param("ssssssi", $name, $spec, $specialityCode, $phone, $days, $email, $id);

    if($stmt->execute()) sendJsonResponse('success');
    sendJsonResponse('error', 'Database Error');
}
