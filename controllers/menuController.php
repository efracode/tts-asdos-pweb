<?php
class MenuController {
    private Menu $menu;
    private Kategori $kategori;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->menu     = new Menu($db);
        $this->kategori = new Kategori($db);
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'list':   $this->list();   break;
            case 'detail': $this->detail(); break;
            case 'create': $this->create(); break;
            case 'update': $this->update(); break;
            case 'delete': $this->delete(); break;
            default:
                json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    // GET: daftar semua menu, opsional filter ?kategori=2
    private function list(): void {
        if (isset($_GET['kategori'])) {
            $data = $this->menu->getByKategori((int) $_GET['kategori']);
        } else {
            $data = $this->menu->getAll();
        }
        json_response(['success' => true, 'data' => $data]);
    }

    // GET: satu menu ?id=1
    private function detail(): void {
        $data = $this->menu->getById((int) ($_GET['id'] ?? 0));

        if (!$data) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }
        json_response(['success' => true, 'data' => $data]);
    }

    // POST: tambah menu (form multipart jika pakai gambar)
    private function create(): void {
        $this->wajibPost();

        $error = $this->validasiInput();
        if ($error !== null) {
            json_response(['success' => false, 'message' => $error], 422);
        }

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) ($_POST['stok'] ?? 0);
        $this->menu->gambar      = $this->uploadGambar();

        if ($this->menu->create()) {
            json_response(['success' => true, 'message' => 'Menu berhasil ditambah'], 201);
        }
        json_response(['success' => false, 'message' => 'Gagal menambah menu'], 500);
    }

    // POST: ubah menu
    private function update(): void {
        $this->wajibPost();

        $id   = (int) ($_POST['id'] ?? 0);
        $lama = $this->menu->getById($id);

        if (!$lama) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }

        $error = $this->validasiInput();
        if ($error !== null) {
            json_response(['success' => false, 'message' => $error], 422);
        }

        // Gambar bersifat opsional saat update
        $gambar    = $lama['gambar'];
        $gambarBaru = $this->uploadGambar();
        if ($gambarBaru !== null) {
            if ($gambar && file_exists(__DIR__ . '/../uploads/' . $gambar)) {
                unlink(__DIR__ . '/../uploads/' . $gambar);
            }
            $gambar = $gambarBaru;
        }

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) ($_POST['stok'] ?? $lama['stok']);
        $this->menu->gambar      = $gambar;

        if ($this->menu->update($id)) {
            json_response(['success' => true, 'message' => 'Menu berhasil diubah']);
        }
        json_response(['success' => false, 'message' => 'Gagal mengubah menu'], 500);
    }

    // POST: hapus menu
    private function delete(): void {
        $this->wajibPost();

        $id   = (int) ($_POST['id'] ?? 0);
        $lama = $this->menu->getById($id);

        if (!$lama) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }

        if ($this->menu->delete($id)) {
            if ($lama['gambar'] && file_exists(__DIR__ . '/../uploads/' . $lama['gambar'])) {
                unlink(__DIR__ . '/../uploads/' . $lama['gambar']);
            }
            json_response(['success' => true, 'message' => 'Menu berhasil dihapus']);
        }
        json_response(['success' => false, 'message' => 'Gagal menghapus menu'], 500);
    }

    // Validasi input form, return pesan error atau null jika aman
    private function validasiInput(): ?string {
        $nama  = trim($_POST['nama_menu'] ?? '');
        $harga = $_POST['harga'] ?? null;
        $idKategori = (int) ($_POST['id_kategori'] ?? 0);

        if ($nama === '') {
            return 'Nama menu tidak boleh kosong.';
        }
        if (!is_numeric($harga) || (int) $harga <= 0) {
            return 'Harga harus berupa angka lebih dari 0.';
        }
        if (isset($_POST['stok']) && (!is_numeric($_POST['stok']) || (int) $_POST['stok'] < 0)) {
            return 'Stok tidak boleh negatif.';
        }
        if ($idKategori <= 0 || !$this->kategori->getById($idKategori)) {
            return 'Kategori tidak valid.';
        }
        return null;
    }

    // Upload gambar dengan validasi ekstensi dan ukuran
    private function uploadGambar(): ?string {
        if (empty($_FILES['gambar']['name'])) {
            return null;
        }

        $file       = $_FILES['gambar'];
        $izin       = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi   = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ekstensi, $izin, true)) {
            json_response(['success' => false, 'message' => 'Format gambar harus jpg, jpeg, png, atau webp'], 422);
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            json_response(['success' => false, 'message' => 'Ukuran gambar maksimal 2MB'], 422);
        }

        $namaBaru = 'menu_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;

        if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $namaBaru)) {
            json_response(['success' => false, 'message' => 'Gagal menyimpan gambar'], 500);
        }
        return $namaBaru;
    }

    private function wajibPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }
    }
}