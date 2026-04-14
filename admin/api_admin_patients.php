<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkAdminAuth();
$conn = Database::getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = getJsonInput();
    if(isset($data['action'])){
        if($data['action'] === 'delete' && isset($data['id'])){
            $id = intval($data['id']);
            $conn->query("DELETE FROM patients WHERE id=$id");
            sendJsonResponse('success');
        } elseif($data['action'] === 'add') {
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $password = password_hash('123456', PASSWORD_DEFAULT); // Default password
            $stmt = $conn->prepare("INSERT INTO patients (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $password);
            if($stmt->execute()) sendJsonResponse('success');
            else sendJsonResponse('error', 'Erreur d\'ajout. Email existant?');
        }
    }
    sendJsonResponse('error', 'Invalid parameters');
}

$result = $conn->query("SELECT * FROM patients ORDER BY id DESC");
$patients = [];
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) $patients[] = $row;
}
sendJsonResponse('success', null, $patients);
