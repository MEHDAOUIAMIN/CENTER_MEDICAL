<?php

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function redirectTo(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function sendJsonResponse(string $status, ?string $message = null, $data = null): void
{
    header('Content-Type: application/json');

    $response = ['status' => $status];

    if ($message !== null) {
        $response['message'] = $message;
    }

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

function checkAdminAuth(): void
{
    startAppSession();

    if (empty($_SESSION['admin_id'])) {
        sendJsonResponse('error', 'Unauthorized');
    }
}

function checkDoctorAuth(): void
{
    startAppSession();

    if (empty($_SESSION['doctor_id'])) {
        sendJsonResponse('error', 'Unauthorized');
    }
}

function checkPatientAuth(): void
{
    startAppSession();

    if (empty($_SESSION['patient_id'])) {
        sendJsonResponse('error', 'Unauthorized');
    }
}

function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '', true);

    return is_array($data) ? $data : [];
}

function normalizeSpecialityCode($code, string $default = '00'): string
{
    $code = preg_replace('/\D/', '', (string) $code);

    if ($code === '') {
        return $default;
    }

    return str_pad(substr($code, 0, 2), 2, '0', STR_PAD_LEFT);
}

function generateUserCode(mysqli $connection, string $table, string $roleDigit, string $specialityCode = '00'): string
{
    $allowedTables = ['admins', 'doctors', 'patients'];

    if (!in_array($table, $allowedTables, true)) {
        throw new RuntimeException('Invalid table name');
    }

    $roleDigit = preg_replace('/\D/', '', $roleDigit);
    $specialityCode = normalizeSpecialityCode($specialityCode);

    if ($roleDigit === '') {
        throw new RuntimeException('Invalid role digit');
    }

    $prefix = $roleDigit . $specialityCode;
    $sql = "SELECT MAX(CAST(RIGHT(user_code, 2) AS UNSIGNED)) AS last_seq
            FROM {$table}
            WHERE user_code LIKE CONCAT(?, '%')";

    $statement = $connection->prepare($sql);
    $statement->bind_param('s', $prefix);
    $statement->execute();

    $result = $statement->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $nextNumber = (int) ($row['last_seq'] ?? 0) + 1;

    if ($nextNumber > 99) {
        throw new RuntimeException('No more IDs available for this speciality');
    }

    return $prefix . str_pad((string) $nextNumber, 2, '0', STR_PAD_LEFT);
}

function tableHasColumn(mysqli $connection, string $table, string $column): bool
{
    $sql = "SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1";

    $statement = $connection->prepare($sql);
    $statement->bind_param('ss', $table, $column);
    $statement->execute();
    $result = $statement->get_result();

    return $result !== false && $result->num_rows > 0;
}

function cleanInput(string $value): string
{
    return trim($value);
}

function isValidDate(string $date): bool
{
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);

    return $dateTime instanceof DateTime && $dateTime->format('Y-m-d') === $date;
}

function isValidTime(string $time): bool
{
    $dateTime = DateTime::createFromFormat('H:i', $time)
        ?: DateTime::createFromFormat('H:i:s', $time);

    if (!$dateTime instanceof DateTime) {
        return false;
    }

    $normalized = strlen($time) === 5 ? $dateTime->format('H:i') : $dateTime->format('H:i:s');

    return $normalized === $time;
}
