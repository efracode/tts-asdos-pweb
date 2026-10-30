<?php
// Ini buat nyimpen query database untuk data kategori.

class Kategori {
    private PDO $conn;
    private string $table = 'kategori';
    public string $nama_kategori;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function getAll(): array {
        $stmt = $this->conn->query(
            "SELECT id, nama_kategori, created_at
             FROM {$this->table}
             ORDER BY id DESC"
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->conn->prepare(
            "SELECT id, nama_kategori
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
            "INSERT INTO {$this->table} (nama_kategori) VALUES (:nama)"
        );
        return $stmt->execute([':nama' => $this->nama_kategori]);
    }

    public function update(int $id): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET nama_kategori = :nama WHERE id = :id"
        );
        return $stmt->execute([
            ':nama' => $this->nama_kategori,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function punyaMenu(int $id): bool {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM menu WHERE id_kategori = :id'
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
