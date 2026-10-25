<?php
class Menu {
    private $conn;
    private $table = "menu";

    public $id;
    public $id_kategori;
    public $nama_menu;
    public $harga;
    public $stok;
    public $gambar;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT m.id, m.nama_menu, m.harga, m.stok, m.gambar, k.nama_kategori
                                      FROM " . $this->table . " m
                                      LEFT JOIN kategori k ON m.id_kategori = k.id
                                      ORDER BY m.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByKategori($id_kategori) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id_kategori = :id_kategori ORDER BY nama_menu ASC");
        $stmt->bindParam(":id_kategori", $id_kategori, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create() {
        $this->nama_menu = htmlspecialchars(strip_tags($this->nama_menu));
        $this->gambar    = htmlspecialchars(strip_tags($this->gambar));

        $stmt = $this->conn->prepare("INSERT INTO " . $this->table . " (id_kategori, nama_menu, harga, stok, gambar)
                                      VALUES (:id_kategori, :nama_menu, :harga, :stok, :gambar)");
        $stmt->bindParam(":id_kategori", $this->id_kategori, PDO::PARAM_INT);
        $stmt->bindParam(":nama_menu", $this->nama_menu);
        $stmt->bindParam(":harga", $this->harga, PDO::PARAM_INT);
        $stmt->bindParam(":stok", $this->stok, PDO::PARAM_INT);
        $stmt->bindParam(":gambar", $this->gambar);
        return $stmt->execute();
    }

    public function update($id) {
        $this->nama_menu = htmlspecialchars(strip_tags($this->nama_menu));
        $this->gambar    = htmlspecialchars(strip_tags($this->gambar));

        $stmt = $this->conn->prepare("UPDATE " . $this->table . " SET
                                      id_kategori = :id_kategori, nama_menu = :nama_menu,
                                      harga = :harga, stok = :stok, gambar = :gambar
                                      WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":id_kategori", $this->id_kategori, PDO::PARAM_INT);
        $stmt->bindParam(":nama_menu", $this->nama_menu);
        $stmt->bindParam(":harga", $this->harga, PDO::PARAM_INT);
        $stmt->bindParam(":stok", $this->stok, PDO::PARAM_INT);
        $stmt->bindParam(":gambar", $this->gambar);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}