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

    public function login() {
        $stmt = $this->conn->prepare("SELECT id, username, password, nama, role FROM " . $this->table . " WHERE username = :username LIMIT 1");
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

    public function create() {
        if (!in_array($this->role, ['admin', 'kasir'], true)) return false;

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nama     = htmlspecialchars(strip_tags($this->nama));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (username, password, nama, role) VALUES (:username, :password, :nama, :role)");
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":role", $this->role);
        return $stmt->execute();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT id, username, nama, role, created_at FROM " . $this->table . " ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT id, username, nama, role FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function update($id) {
        if (!in_array($this->role, ['admin', 'kasir'], true)) return false;

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nama     = htmlspecialchars(strip_tags($this->nama));

        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET username = :username, nama = :nama, role = :role WHERE id = :id");
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}