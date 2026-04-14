<?php

function sendJsonResponse($status, $message = null, $data = null) {
    header('Content-Type: application/json');
    $response = ['status' => $status];
    if ($message !== null) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

function checkAdminAuth() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['admin_id'])) {
        sendJsonResponse('error', 'Unauthorized');
    }
}

function checkDoctorAuth() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['doctor_id'])) {
        sendJsonResponse('error', 'Unauthorized');
    }
}

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
