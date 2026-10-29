<?php
class Database {
    private string $host = 'localhost';
    private string $database = 'kasirrestoran';
    private string $username = 'root';
    private string $password = '';

    public function getConnection(): PDO {
        try {
            $connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $connection;
        } catch (PDOException $e) {
            die('Connection Error: ' . $e->getMessage());
        }
    }
}