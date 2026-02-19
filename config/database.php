<?php
declare(strict_types=1);

class Database {

    private static ?mysqli $connection = null;

 
    private const DB_HOST = "localhost";
    private const DB_USER = "root";
    private const DB_PASS = "root";
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

                // Secure charset (prevents injection tricks)
                self::$connection->set_charset("utf8mb4");

            } catch (mysqli_sql_exception $e) {

                error_log("Database Connection Error: " . $e->getMessage());
                die("Database connection failed.");
            }
        }
    }

  
    public static function iud(string $query, string $types = "", array $params = []): bool {

        self::setUpConnection();

        try {
            $stmt = self::$connection->prepare($query);

            if ($types && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $stmt->close();

            return true;

        } catch (mysqli_sql_exception $e) {
            error_log("IUD Error: " . $e->getMessage());
            return false;
        }
    }

  
    public static function search(string $query, string $types = "", array $params = []): mysqli_result|false {

        self::setUpConnection();

        try {
            $stmt = self::$connection->prepare($query);

            if ($types && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();

            return $stmt->get_result();

        } catch (mysqli_sql_exception $e) {
            error_log("Search Error: " . $e->getMessage());
            return false;
        }
    }

  
    public static function lastInsertId(): int {
        return self::$connection?->insert_id ?? 0;
    }


    public static function close(): void {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
?>
