<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/utils/helpers.php';

$conn = Database::getConnection();
$doctors = $conn->query("SELECT * FROM doctors ORDER BY name ASC");

$doctorList = [];
if ($doctors) {
    while ($row = $doctors->fetch_assoc()) {
        $doctorList[] = $row;
    }
}

sendJsonResponse('success', null, $doctorList);
