<?php
class User {
    private $conn;
    private $table = "users";

    public $id;
    public $username;
    public $password;
    public $nama;
    public $role;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Login: verifikasi username + password + cek is_active
     * Mengembalikan array ['success' => bool, 'reason' => string]
     */
    public function login(): array {
        $stmt = $this->conn->prepare(
            "SELECT id, username, password, nama, role, is_active FROM " . $this->table . " WHERE username = :username LIMIT 1"
        );
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();
        $row = $stmt->fetch();

        if (!$row || !password_verify($this->password, $row['password'])) {
            return ['success' => false, 'reason' => 'invalid_credentials'];
        }

        if (!$row['is_active']) {
            return ['success' => false, 'reason' => 'account_inactive'];
        }

        $this->id       = $row['id'];
        $this->username = $row['username'];
        $this->nama     = $row['nama'];
        $this->role     = $row['role'];
        $this->is_active = $row['is_active'];
        return ['success' => true, 'reason' => ''];
    }

    public function create() {
        if (!in_array($this->role, ['admin', 'kasir'], true)) return false;

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nama     = htmlspecialchars(strip_tags($this->nama));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare(
            "INSERT INTO " . $this->table . " (username, password, nama, role, is_active) VALUES (:username, :password, :nama, :role, :is_active)"
        );
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":role", $this->role);
        $active = $this->is_active ?? 1;
        $stmt->bindParam(":is_active", $active, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAll() {
        $stmt = $this->conn->prepare(
            "SELECT id, username, nama, role, is_active, created_at FROM " . $this->table . " ORDER BY id ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Ambil semua user dengan role kasir beserta jumlah transaksi */
    public function getKasirList() {
        $stmt = $this->conn->prepare("
            SELECT u.id, u.username, u.nama, u.role, u.is_active, u.created_at,
                   COUNT(t.id) AS total_transaksi,
                   COALESCE(SUM(CASE WHEN t.status = 'selesai' THEN t.total ELSE 0 END), 0) AS total_omset,
                   MAX(t.tanggal) AS transaksi_terakhir
            FROM " . $this->table . " u
            LEFT JOIN transaksi t ON t.id_user = u.id
            WHERE u.role = 'kasir'
            GROUP BY u.id
            ORDER BY u.id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT id, username, nama, role, is_active, created_at FROM " . $this->table . " WHERE id = :id LIMIT 1"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function update($id) {
        if (!in_array($this->role, ['admin', 'kasir'], true)) return false;

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->nama     = htmlspecialchars(strip_tags($this->nama));

        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " SET username = :username, nama = :nama, role = :role WHERE id = :id"
        );
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Update password (hanya dipakai admin melalui controller) */
    public function updatePassword($id, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " SET password = :password WHERE id = :id"
        );
        $stmt->bindParam(":password", $hashed);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Toggle status aktif / nonaktif kasir */
    public function toggleActive($id, $status) {
        $stmt = $this->conn->prepare(
            "UPDATE " . $this->table . " SET is_active = :is_active WHERE id = :id"
        );
        $stmt->bindParam(":is_active", $status, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Hitung jumlah transaksi yang dimiliki user (untuk proteksi delete) */
    public function countTransaksiByUser($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM transaksi WHERE id_user = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}