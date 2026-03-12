<?php
declare(strict_types=1);

class Database {

    private static ?mysqli $connection = null;
    private static ?string $lastError = null;

    private static function setUpConnection(): void {

        if (self::$connection === null) {

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            try {

                $env = parse_ini_file(__DIR__ . '/../.env');

                self::$connection = new mysqli(
                    $env["DB_HOST"],
                    $env["DB_USER"],
                    $env["DB_PASS"],
                    $env["DB_NAME"],
                    (int)$env["DB_PORT"]
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
        self::$lastError = null;

        try {

            $stmt = self::$connection->prepare($query);

            if (!$stmt) {
                self::$lastError = self::$connection->error;
                return false;
            }

            if ($types && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $stmt->close();

            return true;

        } catch (mysqli_sql_exception $e) {

            self::$lastError = $e->getMessage();
            error_log($e->getMessage());
            return false;
        }
    }

    public static function search(string $query, string $types = "", array $params = []): mysqli_result|false {

        self::setUpConnection();
        self::$lastError = null;

        try {

            $stmt = self::$connection->prepare($query);

            if (!$stmt) {
                self::$lastError = self::$connection->error;
                return false;
            }

            if ($types && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();

            return $stmt->get_result();

        } catch (mysqli_sql_exception $e) {

            self::$lastError = $e->getMessage();
            error_log($e->getMessage());
            return false;
        }
    }

    public static function lastInsertId(): int {
        return self::$connection?->insert_id ?? 0;
    }

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