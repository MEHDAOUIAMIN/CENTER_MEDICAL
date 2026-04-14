<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkAdminAuth();
$conn = Database::getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = getJsonInput();
    if(isset($data['action']) && isset($data['id'])){
        $id = intval($data['id']);
        if($data['action'] === 'accept'){
            $conn->query("UPDATE appointments SET status='done' WHERE id=$id");
        }elseif($data['action'] === 'cancel'){
            $conn->query("UPDATE appointments SET status='cancelled' WHERE id=$id");
        }
        sendJsonResponse('success');
    }
    sendJsonResponse('error', 'Invalid parameters');
}

$result = $conn->query("
    SELECT a.id, a.fullname, a.phone, d.name AS doctor_name, 
           a.appointment_date, a.appointment_time, a.status
    FROM appointments a JOIN doctors d ON a.doctor_id = d.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appts = [];
if($result && $result->num_rows > 0) {
    while($r = $result->fetch_assoc()) $appts[] = $r;
}
sendJsonResponse('success', null, $appts);
