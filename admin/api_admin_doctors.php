<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkAdminAuth();
$conn = Database::getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = getJsonInput();
    if(isset($data['action']) && $data['action'] === 'delete' && isset($data['id'])){
        $id = intval($data['id']);
        $conn->query("DELETE FROM doctors WHERE id=$id");
        sendJsonResponse('success');
    }
    sendJsonResponse('error', 'Invalid parameters');
}

$result = $conn->query("SELECT * FROM doctors ORDER BY id DESC");
$doctors = [];
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) $doctors[] = $row;
}
sendJsonResponse('success', null, $doctors);
