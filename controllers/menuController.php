<?php
// untuk mengelola list, tambah, ubah, dan hapus menu.

class MenuController {
    private Menu $menu;
    private Kategori $kategori;
    const MAX_INT = 2147483647;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->menu     = new Menu($db);
        $this->kategori = new Kategori($db);
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'list':
                $this->list();
                break;
            case 'detail':
                $this->detail();
                break;
            case 'create':
                $this->wajibAdmin();
                $this->create();
                break;
            case 'update':
                $this->wajibAdmin();
                $this->update();
                break;
            case 'delete':
                $this->wajibAdmin();
                $this->delete();
                break;
            default:
                json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    private function list(): void {
        $this->wajibLogin();
        $data = isset($_GET['kategori'])
            ? $this->menu->getByKategori((int) $_GET['kategori'])
            : $this->menu->getAll();
        json_response(['success' => true, 'data' => $data]);
    }

    private function detail(): void {
        $this->wajibLogin();
        $data = $this->menu->getById((int) ($_GET['id'] ?? 0));
        if (!$data) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }
        json_response(['success' => true, 'data' => $data]);
    }

    private function create(): void {
        $this->wajibPost();
        $error = $this->validasi();
        if ($error) {
            json_response(['success' => false, 'message' => $error], 422);
        }

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) $_POST['stok'];

        if ($this->menu->create()) {
            json_response(['success' => true, 'message' => 'Menu berhasil ditambah'], 201);
        }
        json_response(['success' => false, 'message' => 'Gagal menambah menu'], 500);
    }

    private function update(): void {
        $this->wajibPost();
        $id   = (int) ($_POST['id'] ?? 0);
        if (!$this->menu->getById($id)) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }

        $error = $this->validasi();
        if ($error) {
            json_response(['success' => false, 'message' => $error], 422);
        }

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) $_POST['stok'];

        if ($this->menu->update($id)) {
            json_response(['success' => true, 'message' => 'Menu berhasil diubah']);
        }
        json_response(['success' => false, 'message' => 'Gagal mengubah menu'], 500);
    }

    private function delete(): void {
        $this->wajibPost();
        $id   = (int) ($_POST['id'] ?? 0);
        if (!$this->menu->getById($id)) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }

        try {
            if ($this->menu->delete($id)) {
                json_response(['success' => true, 'message' => 'Menu berhasil dihapus']);
            }
        } catch (PDOException $error) {
            // Riwayat transaksi nyimpen relasi ke menu, jadi menu lama tidak boleh dihapus.
            if ($error->getCode() === '23000') {
                json_response([
                    'success' => false,
                    'message' => 'Menu sudah dipakai dalam transaksi dan tidak bisa dihapus. Ubah stok menjadi 0 agar menu tidak dijual lagi.'
                ], 409);
            }
            throw $error;
        }
        json_response(['success' => false, 'message' => 'Gagal menghapus menu'], 500);
    }

    private function validasi(): ?string {
        $nama  = trim($_POST['nama_menu'] ?? '');
        $harga = filter_var($_POST['harga'] ?? null, FILTER_VALIDATE_INT);
        $stok  = filter_var($_POST['stok'] ?? null, FILTER_VALIDATE_INT);
        $kat   = (int) ($_POST['id_kategori'] ?? 0);

        if ($nama === '' || mb_strlen($nama) > 100) {
            return 'Nama menu wajib diisi, maksimal 100 karakter.';
        }
        if ($harga === false || $harga < 1 || $harga > self::MAX_INT) {
            return 'Harga harus angka antara 1 dan 2.147.483.647.';
        }
        if ($stok === false || $stok < 0 || $stok > self::MAX_INT) {
            return 'Stok harus angka antara 0 dan 2.147.483.647.';
        }
        if ($kat <= 0 || !$this->kategori->getById($kat)) {
            return 'Kategori tidak valid.';
        }
        return null;
    }

    private function wajibPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }
    }

    private function wajibLogin(): void {
        if (!isset($_SESSION['user'])) {
            json_response(['success' => false, 'message' => 'Silakan login dulu'], 401);
        }
    }

    private function wajibAdmin(): void {
        if (!isset($_SESSION['user'])) {
            json_response(['success' => false, 'message' => 'Silakan login dulu'], 401);
        }
        if ($_SESSION['user']['role'] !== 'admin') {
            json_response(['success' => false, 'message' => 'Aksi ini khusus admin'], 403);
        }
    }
}
