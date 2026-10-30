<?php
// Ini buat nyimpen query database untuk login dan data user.

class User {
    private PDO $conn;
    private string $table = 'users';

    public int $id = 0;
    public string $username = '';
    public string $password = '';
    public string $nama = '';
    public string $role = 'kasir';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Ini buat ngecek kecocokan username, password, dan status akun.
    public function login(): bool {
        $query = "SELECT id, username, password, nama, role
                  FROM {$this->table}
                  WHERE username = :username
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':username' => $this->username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($this->password, $user['password'])) {
            return false;
        }

        $this->id = (int) $user['id'];
        $this->username = $user['username'];
        $this->nama = $user['nama'];
        $this->role = $user['role'];
        return true;
    }

    // Ini buat nyimpen user baru dengan password yang sudah di-hash.
    public function create(): bool {
        if (!in_array($this->role, ['admin', 'kasir'], true)) {
            return false;
        }

        $passwordHash = password_hash($this->password, PASSWORD_DEFAULT);
        $query = "INSERT INTO {$this->table} (username, password, nama, role)
                  VALUES (:username, :password, :nama, :role)";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':username' => $this->username,
            ':password' => $passwordHash,
            ':nama' => $this->nama,
            ':role' => $this->role,
        ]);
    }

    // Ini buat ngambil semua user untuk halaman admin.
    public function getAll(): array {
        $query = "SELECT id, username, nama, role, created_at
                  FROM {$this->table}
                  ORDER BY id ASC";
        return $this->conn->query($query)->fetchAll();
    }

    // Ini buat ngambil data kasir beserta ringkasan penjualannya.
    public function getKasirList(): array {
        $query = "SELECT u.id, u.username, u.nama, u.role, u.created_at,
                         COUNT(t.id) AS total_transaksi,
                         COALESCE(SUM(CASE WHEN t.status = 'selesai' THEN t.total ELSE 0 END), 0) AS total_omset,
                         MAX(t.tanggal) AS transaksi_terakhir
                  FROM {$this->table} u
                  LEFT JOIN transaksi t ON t.id_user = u.id
                  WHERE u.role = 'kasir'
                  GROUP BY u.id
                  ORDER BY u.id ASC";
        return $this->conn->query($query)->fetchAll();
    }

    // Ini buat mencari user berdasarkan ID.
    public function getById(int $id): array|false {
        $query = "SELECT id, username, nama, role, created_at
                  FROM {$this->table}
                  WHERE id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Ini buat ngubah username, nama tampilan, atau hak akses.
    public function update(int $id): bool {
        if (!in_array($this->role, ['admin', 'kasir'], true)) {
            return false;
        }

        $query = "UPDATE {$this->table}
                  SET username = :username, nama = :nama, role = :role
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            ':username' => $this->username,
            ':nama' => $this->nama,
            ':role' => $this->role,
            ':id' => $id,
        ]);
    }

    // Ini buat nyimpen password baru dalam bentuk hash.
    public function updatePassword(int $id, string $password): bool {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET password = :password WHERE id = :id"
        );

        return $stmt->execute([
            ':password' => $passwordHash,
            ':id' => $id,
        ]);
    }

    // Ini buat ngitung transaksi milik user sebelum akun dihapus.
    public function countTransaksiByUser(int $id): int {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM transaksi WHERE id_user = :id'
        );
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    // Ini buat hapus akun user dari database.
    public function delete(int $id): bool {
        $stmt = $this->conn->prepare(
            "DELETE FROM {$this->table} WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
