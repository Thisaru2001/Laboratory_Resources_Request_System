<?php
declare(strict_types=1);

class Database {

    private static ?mysqli $connection = null;
    private static ?string $lastError = null; // Add this to store last error

    // private const DB_HOST = "database-1.csnikggyo5mr.us-east-1.rds.amazonaws.com";
    private const DB_HOST = "database-1.csnikggyo5mr.us-east-1.rds.amazonaws.com";
    private const DB_USER = "admin";
    private const DB_PASS = "Lrrsystem123";
    private const DB_NAME = "lab_db";
    private const DB_PORT = 3306;

    private static function setUpConnection(): void {
        if (self::$connection === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            try {
                self::$connection = new mysqli(
                    self::DB_HOST,
                    self::DB_USER,
                    self::DB_PASS,
                    self::DB_NAME,
                    self::DB_PORT
                );
                self::$connection->set_charset("utf8mb4");
            } catch (mysqli_sql_exception $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                die("Database connection failed.");
            }
        }
    }

    public static function iud(string $query, string $types = "", array $params = []): bool {
        self::setUpConnection();
        self::$lastError = null; // Reset last error

        try {
            $stmt = self::$connection->prepare($query);
            
            if (!$stmt) {
                self::$lastError = "Prepare failed: " . self::$connection->error;
                error_log(self::$lastError);
                return false;
            }

            if ($types && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                self::$lastError = "Execute failed: " . $stmt->error;
                error_log(self::$lastError);
                return false;
            }

            $stmt->close();
            return true;

        } catch (mysqli_sql_exception $e) {
            self::$lastError = $e->getMessage();
            error_log("IUD Error: " . $e->getMessage());
            return false;
        }
    }

    public static function search(string $query, string $types = "", array $params = []): mysqli_result|false {
        self::setUpConnection();
        self::$lastError = null; // Reset last error

        try {
            $stmt = self::$connection->prepare($query);
            
            if (!$stmt) {
                self::$lastError = "Prepare failed: " . self::$connection->error;
                error_log(self::$lastError);
                return false;
            }

            if ($types && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            if (!$stmt->execute()) {
                self::$lastError = "Execute failed: " . $stmt->error;
                error_log(self::$lastError);
                return false;
            }

            return $stmt->get_result();

        } catch (mysqli_sql_exception $e) {
            self::$lastError = $e->getMessage();
            error_log("Search Error: " . $e->getMessage());
            return false;
        }
    }

    public static function lastInsertId(): int {
        return self::$connection?->insert_id ?? 0;
    }

    // Add this method to get the last error
    public static function getLastError(): ?string {
        return self::$lastError;
    }

    public static function close(): void {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
?>