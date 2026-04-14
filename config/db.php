<?php

class Database {
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            self::$conn = new mysqli("localhost", "root", "", "medical_center");
            if (self::$conn->connect_error) {
                die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
            }
        }
        return self::$conn;
    }
}
