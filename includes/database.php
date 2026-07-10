<?php
/**
 * Database Connection — PDO Singleton
 * Galaxy Portfolio CMS
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'if0_42179562_portfolio_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('DB Connection failed: ' . $e->getMessage());
                die(json_encode(['error' => 'Database connection failed. Please check your configuration.']));
            }
        }
        return self::$instance;
    }

    /** Execute a query and return PDOStatement */
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /** Fetch all rows */
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    /** Insert and return last insert ID */
    public static function insert(string $table, array $data): int {
        $columns = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        self::query($sql, array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    /** Update rows */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $sql = "UPDATE `$table` SET $set WHERE $where";
        $stmt = self::query($sql, [...array_values($data), ...$whereParams]);
        return $stmt->rowCount();
    }

    /** Delete rows */
    public static function delete(string $table, string $where, array $params = []): int {
        $stmt = self::query("DELETE FROM `$table` WHERE $where", $params);
        return $stmt->rowCount();
    }

    /** Begin transaction */
    public static function beginTransaction(): void {
        self::getInstance()->beginTransaction();
    }

    /** Commit transaction */
    public static function commit(): void {
        self::getInstance()->commit();
    }

    /** Rollback transaction */
    public static function rollback(): void {
        self::getInstance()->rollBack();
    }
}
