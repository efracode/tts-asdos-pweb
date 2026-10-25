<?php
class UserController {
    private User $user;

    public function __construct() {
        $this->user = new User((new Database())->getConnection());
    }

    public function handle(string $action): void {
        // Semua aksi di UserController memerlukan login sebagai admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            json_response(['success' => false, 'message' => 'Akses ditolak. Hanya admin yang bisa mengelola pengguna.'], 403);
        }

        switch ($action) {
            case 'list':        $this->listAll();       break;
            case 'kasir-list':  $this->kasirList();     break;
            case 'detail':      $this->detail();        break;
            case 'create':      $this->create();        break;
            case 'update':      $this->update();        break;
            case 'toggle':      $this->toggleActive();  break;
            case 'reset-password': $this->resetPassword(); break;
            case 'delete':      $this->delete();        break;
            default:
                json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    /** Daftar semua user */
    private function listAll(): void {
        $data = $this->user->getAll();
        json_response(['success' => true, 'data' => $data]);
    }

    /** Daftar kasir + statistik transaksi */
    private function kasirList(): void {
        $data = $this->user->getKasirList();
        json_response(['success' => true, 'data' => $data]);
    }

    /** Detail satu user */
    private function detail(): void {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID user tidak valid'], 422);
        }
        $data = $this->user->getById($id);
        if (!$data) {
            json_response(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }
        json_response(['success' => true, 'data' => $data]);
    }

    /** Tambah user/kasir baru */
    private function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $nama     = trim($_POST['nama'] ?? '');
        $role     = $_POST['role'] ?? 'kasir';

        // Validasi input
        if ($username === '' || $password === '' || $nama === '') {
            json_response(['success' => false, 'message' => 'Username, password, dan nama wajib diisi'], 422);
        }
        if (strlen($username) < 3 || strlen($username) > 50) {
            json_response(['success' => false, 'message' => 'Username harus 3-50 karakter'], 422);
        }
        if (strlen($password) < 5) {
            json_response(['success' => false, 'message' => 'Password minimal 5 karakter'], 422);
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            json_response(['success' => false, 'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore'], 422);
        }
        if (!in_array($role, ['admin', 'kasir'], true)) {
            json_response(['success' => false, 'message' => 'Role harus admin atau kasir'], 422);
        }

        $this->user->username  = $username;
        $this->user->password  = $password;
        $this->user->nama      = $nama;
        $this->user->role      = $role;
        $this->user->is_active = 1;

        try {
            if ($this->user->create()) {
                json_response(['success' => true, 'message' => "User \"$nama\" berhasil ditambahkan sebagai $role"]);
            } else {
                json_response(['success' => false, 'message' => 'Gagal menambahkan user'], 500);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                json_response(['success' => false, 'message' => "Username \"$username\" sudah digunakan. Pilih username lain."], 409);
            }
            throw $e;
        }
    }

    /** Update data user (tanpa password) */
    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $id       = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $nama     = trim($_POST['nama'] ?? '');
        $role     = $_POST['role'] ?? 'kasir';

        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID user tidak valid'], 422);
        }

        // Proteksi: admin tidak bisa mengedit dirinya sendiri menjadi kasir
        if ($id == $_SESSION['user']['id'] && $role !== 'admin') {
            json_response(['success' => false, 'message' => 'Anda tidak bisa mengubah role akun Anda sendiri'], 422);
        }

        $existing = $this->user->getById($id);
        if (!$existing) {
            json_response(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if ($username === '' || $nama === '') {
            json_response(['success' => false, 'message' => 'Username dan nama wajib diisi'], 422);
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            json_response(['success' => false, 'message' => 'Username hanya boleh huruf, angka, dan underscore'], 422);
        }
        if (!in_array($role, ['admin', 'kasir'], true)) {
            json_response(['success' => false, 'message' => 'Role harus admin atau kasir'], 422);
        }

        $this->user->username = $username;
        $this->user->nama     = $nama;
        $this->user->role     = $role;

        try {
            if ($this->user->update($id)) {
                json_response(['success' => true, 'message' => "Data user \"$nama\" berhasil diperbarui"]);
            } else {
                json_response(['success' => false, 'message' => 'Gagal memperbarui user'], 500);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                json_response(['success' => false, 'message' => "Username \"$username\" sudah digunakan user lain"], 409);
            }
            throw $e;
        }
    }

    /** Toggle aktif / nonaktif kasir */
    private function toggleActive(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $id     = intval($_POST['id'] ?? 0);
        $status = intval($_POST['is_active'] ?? -1);

        if ($id <= 0 || !in_array($status, [0, 1], true)) {
            json_response(['success' => false, 'message' => 'Parameter tidak valid'], 422);
        }

        // Proteksi: admin tidak bisa menonaktifkan dirinya sendiri
        if ($id == $_SESSION['user']['id']) {
            json_response(['success' => false, 'message' => 'Anda tidak bisa menonaktifkan akun Anda sendiri'], 422);
        }

        $existing = $this->user->getById($id);
        if (!$existing) {
            json_response(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if ($this->user->toggleActive($id, $status)) {
            $label = $status ? 'diaktifkan' : 'dinonaktifkan';
            json_response(['success' => true, 'message' => "Akun \"{$existing['nama']}\" berhasil $label"]);
        } else {
            json_response(['success' => false, 'message' => 'Gagal mengubah status akun'], 500);
        }
    }

    /** Reset password kasir oleh admin */
    private function resetPassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $id          = intval($_POST['id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID user tidak valid'], 422);
        }
        if (strlen($newPassword) < 5) {
            json_response(['success' => false, 'message' => 'Password baru minimal 5 karakter'], 422);
        }

        $existing = $this->user->getById($id);
        if (!$existing) {
            json_response(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        if ($this->user->updatePassword($id, $newPassword)) {
            json_response(['success' => true, 'message' => "Password untuk \"{$existing['nama']}\" berhasil direset"]);
        } else {
            json_response(['success' => false, 'message' => 'Gagal mereset password'], 500);
        }
    }

    /** Hapus user (hanya jika tidak punya transaksi) */
    private function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            json_response(['success' => false, 'message' => 'ID user tidak valid'], 422);
        }

        // Proteksi: tidak bisa hapus akun sendiri
        if ($id == $_SESSION['user']['id']) {
            json_response(['success' => false, 'message' => 'Anda tidak bisa menghapus akun Anda sendiri'], 422);
        }

        $existing = $this->user->getById($id);
        if (!$existing) {
            json_response(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        // Proteksi: jangan hapus user yang sudah punya transaksi
        $jumlahTrx = $this->user->countTransaksiByUser($id);
        if ($jumlahTrx > 0) {
            json_response([
                'success' => false,
                'message' => "Tidak bisa menghapus \"{$existing['nama']}\" karena sudah memiliki $jumlahTrx transaksi. Nonaktifkan saja jika tidak ingin user ini bisa login."
            ], 409);
        }

        if ($this->user->delete($id)) {
            json_response(['success' => true, 'message' => "User \"{$existing['nama']}\" berhasil dihapus"]);
        } else {
            json_response(['success' => false, 'message' => 'Gagal menghapus user'], 500);
        }
    }
}
