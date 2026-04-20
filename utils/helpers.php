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

function checkPatientAuth() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['patient_id'])) {
        sendJsonResponse('error', 'Unauthorized');
    }
}

function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function normalizeSpecialityCode($code, $default = '00') {
    $code = preg_replace('/\D/', '', (string) $code);
    if ($code === '') {
        return $default;
    }

    return str_pad(substr($code, 0, 2), 2, '0', STR_PAD_LEFT);
}

function generateUserCode($conn, $table, $roleDigit, $specialityCode = '00') {
    $roleDigit = preg_replace('/\D/', '', (string) $roleDigit);
    $specialityCode = normalizeSpecialityCode($specialityCode);

    if ($roleDigit === '') {
        throw new Exception('Invalid role digit');
    }

    $prefix = $roleDigit . $specialityCode;
    $stmt = $conn->prepare("SELECT MAX(CAST(RIGHT(user_code, 2) AS UNSIGNED)) AS last_seq FROM {$table} WHERE user_code LIKE CONCAT(?, '%')");
    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $next = (int) ($row['last_seq'] ?? 0) + 1;

    if ($next > 99) {
        throw new Exception('No more IDs available for this role/speciality code');
    }

    return $prefix . str_pad((string) $next, 2, '0', STR_PAD_LEFT);
}
