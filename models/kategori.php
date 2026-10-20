<?php
class Kategori {
    private $conn;
    private $table = "kategori";

    public $id;
    public $nama_kategori;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT id, nama_kategori, created_at FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT id, nama_kategori FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (nama_kategori) VALUES (:nama)";
        $stmt = $this->conn->prepare($query);

        $this->nama_kategori = htmlspecialchars(strip_tags($this->nama_kategori));
        $stmt->bindParam(":nama", $this->nama_kategori);

        return $stmt->execute();
    }

    public function update($id) {
        $query = "UPDATE " . $this->table . " SET nama_kategori = :nama WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->nama_kategori = htmlspecialchars(strip_tags($this->nama_kategori));
        $stmt->bindParam(":nama", $this->nama_kategori);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}