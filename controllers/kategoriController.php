<?php
// Ini buat ngatur list, tambah, ubah, dan hapus kategori.

class KategoriController {
    private Kategori $kategori;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->kategori = new Kategori($db);
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'list':
                $this->list();
                break;
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    private function list(): void {
        $this->wajibLogin();
        json_response(['success' => true, 'data' => $this->kategori->getAll()]);
    }

    private function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }
        $this->wajibAdmin();
        $nama = trim($_POST['nama_kategori'] ?? '');
        if ($nama === '' || mb_strlen($nama) > 50) {
            json_response(['success' => false, 'message' => 'Nama kategori wajib diisi, maksimal 50 karakter'], 422);
        }

        $this->kategori->nama_kategori = $nama;
        if ($this->kategori->create()) {
            json_response(['success' => true, 'message' => 'Kategori berhasil ditambah'], 201);
        }
        json_response(['success' => false, 'message' => 'Gagal menambah kategori'], 500);
    }

    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }
        $this->wajibAdmin();
        $id   = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama_kategori'] ?? '');

        if (!$this->kategori->getById($id)) {
            json_response(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }
        if ($nama === '' || mb_strlen($nama) > 50) {
            json_response(['success' => false, 'message' => 'Nama kategori wajib diisi, maksimal 50 karakter'], 422);
        }

        $this->kategori->nama_kategori = $nama;
        if ($this->kategori->update($id)) {
            json_response(['success' => true, 'message' => 'Kategori berhasil diubah']);
        }
        json_response(['success' => false, 'message' => 'Gagal mengubah kategori'], 500);
    }

    private function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }
        $this->wajibAdmin();
        $id = (int) ($_POST['id'] ?? 0);

        if (!$this->kategori->getById($id)) {
            json_response(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }
        if ($this->kategori->punyaMenu($id)) {
            json_response(['success' => false, 'message' => 'Kategori masih punya menu, tidak bisa dihapus'], 422);
        }

        if ($this->kategori->delete($id)) {
            json_response(['success' => true, 'message' => 'Kategori berhasil dihapus']);
        }
        json_response(['success' => false, 'message' => 'Gagal menghapus kategori'], 500);
    }

    private function wajibAdmin(): void {
        if (!isset($_SESSION['user'])) {
            json_response(['success' => false, 'message' => 'Silakan login dulu'], 401);
        }
        if ($_SESSION['user']['role'] !== 'admin') {
            json_response(['success' => false, 'message' => 'Akses ditolak. Khusus admin'], 403);
        }
    }

    private function wajibLogin(): void {
        if (!isset($_SESSION['user'])) {
            json_response(['success' => false, 'message' => 'Silakan login dulu'], 401);
        }
    }
}
