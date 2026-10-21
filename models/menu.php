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

    // READ ALL: semua menu + nama kategori (return array)
    public function getAll() {
        $query = "SELECT m.id, m.nama_menu, m.harga, m.stok, m.gambar,
                         k.nama_kategori
                  FROM " . $this->table . " m
                  LEFT JOIN kategori k ON m.id_kategori = k.id
                  ORDER BY m.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // READ BY KATEGORI (return array)
    public function getByKategori($id_kategori) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE id_kategori = :id_kategori
                  ORDER BY nama_menu ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_kategori", $id_kategori, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // READ ONE
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (id_kategori, nama_menu, harga, stok, gambar)
                  VALUES (:id_kategori, :nama_menu, :harga, :stok, :gambar)";
        $stmt = $this->conn->prepare($query);

        $this->nama_menu = htmlspecialchars(strip_tags($this->nama_menu));
        $this->gambar    = htmlspecialchars(strip_tags($this->gambar));

        $stmt->bindParam(":id_kategori", $this->id_kategori, PDO::PARAM_INT);
        $stmt->bindParam(":nama_menu",   $this->nama_menu);
        $stmt->bindParam(":harga",       $this->harga, PDO::PARAM_INT);
        $stmt->bindParam(":stok",        $this->stok, PDO::PARAM_INT);
        $stmt->bindParam(":gambar",      $this->gambar);

        return $stmt->execute();
    }

    // UPDATE
    public function update($id) {
        $query = "UPDATE " . $this->table . " SET
                  id_kategori = :id_kategori,
                  nama_menu   = :nama_menu,
                  harga       = :harga,
                  stok        = :stok
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->nama_menu = htmlspecialchars(strip_tags($this->nama_menu));

        $stmt->bindParam(":id",          $id, PDO::PARAM_INT);
        $stmt->bindParam(":id_kategori", $this->id_kategori, PDO::PARAM_INT);
        $stmt->bindParam(":nama_menu",   $this->nama_menu);
        $stmt->bindParam(":harga",       $this->harga, PDO::PARAM_INT);
        $stmt->bindParam(":stok",        $this->stok, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // UPDATE STOK
    public function kurangiStok($id, $qty) {
        $query = "UPDATE " . $this->table . "
                  SET stok = stok - :qty
                  WHERE id = :id AND stok >= :qty";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":qty", $qty, PDO::PARAM_INT);
        $stmt->bindParam(":id",  $id,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}