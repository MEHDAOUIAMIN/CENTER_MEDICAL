<?php

class Database
{
    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        mysqli_report(MYSQLI_REPORT_OFF);

        self::$connection = new mysqli('localhost', 'root', '', 'medical_center');

        if (self::$connection->connect_error) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Database connection failed'
            ]);
            exit;
        }

        self::$connection->set_charset('utf8mb4');

        return self::$connection;
    }
}
