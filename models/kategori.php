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
        $stmt = $this->conn->prepare("SELECT id, nama_kategori, created_at FROM " . $this->table . " ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT id, nama_kategori FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create() {
        $this->nama_kategori = htmlspecialchars(strip_tags($this->nama_kategori));
        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (nama_kategori) VALUES (:nama)");
        $stmt->bindParam(":nama", $this->nama_kategori);
        return $stmt->execute();
    }

    public function update($id) {
        $this->nama_kategori = htmlspecialchars(strip_tags($this->nama_kategori));
        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET nama_kategori = :nama WHERE id = :id");
        $stmt->bindParam(":nama", $this->nama_kategori);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function punyaMenu($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM menu WHERE id_kategori = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch()['total'] > 0;
    }
}