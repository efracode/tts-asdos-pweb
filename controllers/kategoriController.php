<?php
class KategoriController {
    private Kategori $kategori;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->kategori = new Kategori($db);
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'list':   $this->list();   break;
            case 'create': $this->create(); break;
            case 'update': $this->update(); break;
            case 'delete': $this->delete(); break;
            default:
                json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    // GET: semua kategori
    private function list(): void {
        json_response(['success' => true, 'data' => $this->kategori->getAll()]);
    }

    // POST: tambah kategori
    private function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $nama = trim($_POST['nama_kategori'] ?? '');
        if ($nama === '') {
            json_response(['success' => false, 'message' => 'Nama kategori tidak boleh kosong'], 422);
        }

        $this->kategori->nama_kategori = $nama;

        if ($this->kategori->create()) {
            json_response(['success' => true, 'message' => 'Kategori berhasil ditambah'], 201);
        }
        json_response(['success' => false, 'message' => 'Gagal menambah kategori'], 500);
    }

    // POST: ubah kategori
    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $id   = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama_kategori'] ?? '');

        if (!$this->kategori->getById($id)) {
            json_response(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }
        if ($nama === '') {
            json_response(['success' => false, 'message' => 'Nama kategori tidak boleh kosong'], 422);
        }

        $this->kategori->nama_kategori = $nama;

        if ($this->kategori->update($id)) {
            json_response(['success' => true, 'message' => 'Kategori berhasil diubah']);
        }
        json_response(['success' => false, 'message' => 'Gagal mengubah kategori'], 500);
    }

    // POST: hapus kategori, ditolak jika masih punya menu
    private function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $id = (int) ($_POST['id'] ?? 0);

        if (!$this->kategori->getById($id)) {
            json_response(['success' => false, 'message' => 'Kategori tidak ditemukan'], 404);
        }
        if ($this->kategori->punyaMenu($id)) {
            json_response(['success' => false, 'message' => 'Kategori tidak bisa dihapus karena masih memiliki menu'], 422);
        }

        if ($this->kategori->delete($id)) {
            json_response(['success' => true, 'message' => 'Kategori berhasil dihapus']);
        }
        json_response(['success' => false, 'message' => 'Gagal menghapus kategori'], 500);
    }
}