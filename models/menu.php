<?php
class Menu {
    private PDO $conn;
    private string $table = 'menu';

    public int $id_kategori;
    public string $nama_menu;
    public int $harga;
    public int $stok;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function getAll(): array {
        $stmt = $this->conn->query(
            "SELECT m.id, m.id_kategori, m.nama_menu, m.harga, m.stok, k.nama_kategori
             FROM {$this->table} m
             JOIN kategori k ON k.id = m.id_kategori
             ORDER BY m.id DESC"
        );
        return $stmt->fetchAll();
    }

    public function getByKategori(int $id_kategori): array {
        $stmt = $this->conn->prepare(
            "SELECT id, id_kategori, nama_menu, harga, stok
             FROM {$this->table}
             WHERE id_kategori = :id_kategori
             ORDER BY nama_menu ASC"
        );
        $stmt->bindValue(':id_kategori', $id_kategori, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->conn->prepare(
            "SELECT id, id_kategori, nama_menu, harga, stok
             FROM {$this->table}
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (id_kategori, nama_menu, harga, stok)
             VALUES (:id_kategori, :nama_menu, :harga, :stok)"
        );
        $stmt->bindValue(':id_kategori', $this->id_kategori, PDO::PARAM_INT);
        $stmt->bindValue(':nama_menu', $this->nama_menu);
        $stmt->bindValue(':harga', $this->harga, PDO::PARAM_INT);
        $stmt->bindValue(':stok', $this->stok, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update(int $id): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table}
             SET id_kategori = :id_kategori, nama_menu = :nama_menu,
                 harga = :harga, stok = :stok
             WHERE id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':id_kategori', $this->id_kategori, PDO::PARAM_INT);
        $stmt->bindValue(':nama_menu', $this->nama_menu);
        $stmt->bindValue(':harga', $this->harga, PDO::PARAM_INT);
        $stmt->bindValue(':stok', $this->stok, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}