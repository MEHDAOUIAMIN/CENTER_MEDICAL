<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkDoctorAuth();
$conn = Database::getConnection();
$doctor_id = $_SESSION['doctor_id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = getJsonInput();
    if(isset($data['action']) && isset($data['id'])){
        $id = intval($data['id']);
        if($data['action'] === 'accept'){
            $conn->query("UPDATE appointments SET status='done' WHERE id=$id AND doctor_id=$doctor_id");
        }elseif($data['action'] === 'cancel'){
            $conn->query("UPDATE appointments SET status='cancelled' WHERE id=$id AND doctor_id=$doctor_id");
        }
        sendJsonResponse('success');
    }
    sendJsonResponse('error', 'Invalid parameters');
}

$today = date('Y-m-d');
$data = [
    'total_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE doctor_id=$doctor_id")->fetch_assoc()['t'],
    'today_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE doctor_id=$doctor_id AND appointment_date='$today'")->fetch_assoc()['t'],
    'pending_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE doctor_id=$doctor_id AND status='pending'")->fetch_assoc()['t'],
    'completed_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE doctor_id=$doctor_id AND status='done'")->fetch_assoc()['t'],
    'cancelled_appointments' => $conn->query("SELECT COUNT(*) as t FROM appointments WHERE doctor_id=$doctor_id AND status='cancelled'")->fetch_assoc()['t']
];

$result = $conn->query("SELECT id, fullname, phone, email, appointment_date, appointment_time, status FROM appointments WHERE doctor_id=$doctor_id ORDER BY appointment_date ASC, appointment_time ASC");
$appts = [];
if($result && $result->num_rows > 0) {
    while($r = $result->fetch_assoc()) $appts[] = $r;
}

$data['appointments_list'] = $appts;
$data['doctor_name'] = $_SESSION['doctor_name'];
$data['today_date'] = date('l, d M Y');

sendJsonResponse('success', null, $data);
