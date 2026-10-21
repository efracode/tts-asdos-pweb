<?php
class User {
    private $conn;
    private $table = "users";

    public $id;
    public $username;
    public $password;
    public $nama;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // LOGIN
    public function login() {
        $query = "SELECT id, username, password, nama, role FROM " . $this->table . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        $row = $stmt->fetch();

        if ($row && password_verify($this->password, $row['password'])) {
            $this->id       = $row['id'];
            $this->username = $row['username'];
            $this->nama     = $row['nama'];
            $this->role     = $row['role'];
            return true;
        }
        return false;
    }

    // CREATE
    public function create() {
        if (!in_array($this->role, ['admin', 'kasir'], true)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " (username, password, nama, role) VALUES (:username, :password, :nama, :role)";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nama     = htmlspecialchars(strip_tags($this->nama));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":role", $this->role);

        return $stmt->execute();
    }

    // READ ALL
    public function getAll() {
        $query = "SELECT id, username, nama, role, created_at FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // READ ONE
    public function getById($id) {
        $query = "SELECT id, username, nama, role FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // UPDATE
    public function update($id) {
        if (!in_array($this->role, ['admin', 'kasir'], true)) {
            return false;
        }

        $query = "UPDATE " . $this->table . " SET username = :username, nama = :nama, role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nama     = htmlspecialchars(strip_tags($this->nama));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}