<?php

class Database
{
    private const HOST = 'localhost';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const DATABASE_NAME = 'medical_center';

    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        mysqli_report(MYSQLI_REPORT_OFF);

        $connection = new mysqli(self::HOST, self::USERNAME, self::PASSWORD);

        if ($connection->connect_error) {
            self::stopWithError('Database server connection failed');
        }

        $connection->set_charset('utf8mb4');

        self::ensureDatabaseExists($connection);

        if (!$connection->select_db(self::DATABASE_NAME)) {
            self::stopWithError('Unable to select database');
        }

        self::ensureSchemaReady($connection);
        self::$connection = $connection;

        return self::$connection;
    }

    private static function ensureDatabaseExists(mysqli $connection): void
    {
        $databaseName = self::DATABASE_NAME;
        $sql = "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        if (!$connection->query($sql)) {
            self::stopWithError('Unable to create database automatically');
        }
    }

    private static function ensureSchemaReady(mysqli $connection): void
    {
        $connection->select_db(self::DATABASE_NAME);

        $result = $connection->query("SHOW TABLES LIKE 'patients'");

        if ($result && $result->num_rows > 0) {
            return;
        }

        self::importSqlFile($connection, dirname(__DIR__) . '/medical_center.sql');
    }

    private static function importSqlFile(mysqli $connection, string $sqlFilePath): void
    {
        if (!is_file($sqlFilePath)) {
            self::stopWithError('SQL file not found');
        }

        $sql = file_get_contents($sqlFilePath);

        if ($sql === false || trim($sql) === '') {
            self::stopWithError('SQL file is empty');
        }

        $sql = preg_replace('/^--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*![\s\S]*?\*\//', '', $sql);
        $sql = str_replace('START TRANSACTION;', '', $sql);
        $sql = str_replace('COMMIT;', '', $sql);
        $sql = preg_replace('/USE\s+`?medical_center`?;/i', '', $sql);

        if (!$connection->multi_query($sql)) {
            self::stopWithError('Automatic database import failed');
        }

        do {
            if ($result = $connection->store_result()) {
                $result->free();
            }
        } while ($connection->more_results() && $connection->next_result());
    }

    private static function stopWithError(string $message): void
    {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
        exit;
    }
}
