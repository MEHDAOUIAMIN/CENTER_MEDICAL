<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/helpers.php';
checkDoctorAuth();
$conn = Database::getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = getJsonInput();
    $appt_id = intval($data['appt_id']);
    $notes = $data['notes'] ?? '';
    
    $apptParams = $conn->query("SELECT email FROM appointments WHERE id=$appt_id")->fetch_assoc();
    if(!$apptParams) sendJsonResponse('error', 'Rendez-vous introuvable');
    $p_email = $apptParams['email'];
    
    $p_data = $conn->query("SELECT id FROM patients WHERE email='$p_email'")->fetch_assoc();
    if(!$p_data) sendJsonResponse('error', 'Patient non enregistré avec ce compte (Email non trouvé dans la table Patients)');
    
    $p_id = $p_data['id'];
    $doc_id = $_SESSION['doctor_id'];
    $date = date('Y-m-d');
    
    $stmt = $conn->prepare("INSERT INTO consultations (rendez_vous_id, doctor_id, patient_id, notes, consultation_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiss", $appt_id, $doc_id, $p_id, $notes, $date);
    
    // Update appointment status to done automatically
    $conn->query("UPDATE appointments SET status='done' WHERE id=$appt_id");

    if($stmt->execute()) sendJsonResponse('success');
    else sendJsonResponse('error', 'Erreur de base de données');
}
