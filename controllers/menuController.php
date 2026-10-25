<?php
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
            case 'list':   $this->list();   break;
            case 'detail': $this->detail(); break;
            case 'create': $this->wajibAdmin(); $this->create(); break;
            case 'update': $this->wajibAdmin(); $this->update(); break;
            case 'delete': $this->wajibAdmin(); $this->delete(); break;
            default: json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    private function list(): void {
        $data = isset($_GET['kategori'])
            ? $this->menu->getByKategori((int) $_GET['kategori'])
            : $this->menu->getAll();
        json_response(['success' => true, 'data' => $data]);
    }

    private function detail(): void {
        $data = $this->menu->getById((int) ($_GET['id'] ?? 0));
        if (!$data) json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        json_response(['success' => true, 'data' => $data]);
    }

    private function create(): void {
        $this->wajibPost();
        $this->wajibAdmin();
        $error = $this->validasi();
        if ($error) json_response(['success' => false, 'message' => $error], 422);

        $gambar = $this->uploadGambar();

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) $_POST['stok'];
        $this->menu->gambar      = $gambar;

        if ($this->menu->create()) {
            json_response(['success' => true, 'message' => 'Menu berhasil ditambah'], 201);
        }
        if ($gambar && file_exists(__DIR__ . '/../uploads/' . $gambar)) {
            @unlink(__DIR__ . '/../uploads/' . $gambar);
        }
        json_response(['success' => false, 'message' => 'Gagal menambah menu'], 500);
    }

    private function update(): void {
        $this->wajibPost();
        $this->wajibAdmin();
        $id   = (int) ($_POST['id'] ?? 0);
        $lama = $this->menu->getById($id);
        if (!$lama) json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);

        $error = $this->validasi();
        if ($error) json_response(['success' => false, 'message' => $error], 422);

        $gambar = $lama['gambar'];
        $baru   = $this->uploadGambar();
        $hapusGambar = !empty($_POST['hapus_gambar']) && $_POST['hapus_gambar'] === '1';

        if ($baru !== null) {
            if ($gambar && file_exists(__DIR__ . '/../uploads/' . $gambar)) {
                @unlink(__DIR__ . '/../uploads/' . $gambar);
            }
            $gambar = $baru;
        } elseif ($hapusGambar) {
            if ($gambar && file_exists(__DIR__ . '/../uploads/' . $gambar)) {
                @unlink(__DIR__ . '/../uploads/' . $gambar);
            }
            $gambar = null;
        }

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) $_POST['stok'];
        $this->menu->gambar      = $gambar;

        if ($this->menu->update($id)) {
            json_response(['success' => true, 'message' => 'Menu berhasil diubah']);
        }
        if ($baru !== null && file_exists(__DIR__ . '/../uploads/' . $baru)) {
            @unlink(__DIR__ . '/../uploads/' . $baru);
        }
        json_response(['success' => false, 'message' => 'Gagal mengubah menu'], 500);
    }

    private function delete(): void {
        $this->wajibPost();
        $this->wajibAdmin();
        $id   = (int) ($_POST['id'] ?? 0);
        $lama = $this->menu->getById($id);
        if (!$lama) json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);

        if ($this->menu->delete($id)) {
            if ($lama['gambar'] && file_exists(__DIR__ . '/../uploads/' . $lama['gambar'])) {
                @unlink(__DIR__ . '/../uploads/' . $lama['gambar']);
            }
            json_response(['success' => true, 'message' => 'Menu berhasil dihapus']);
        }
        json_response(['success' => false, 'message' => 'Gagal menghapus menu'], 500);
    }

    private function validasi(): ?string {
        $nama  = trim($_POST['nama_menu'] ?? '');
        $harga = $_POST['harga'] ?? '';
        $stok  = $_POST['stok'] ?? '';
        $kat   = (int) ($_POST['id_kategori'] ?? 0);

        if ($nama === '' || mb_strlen($nama) > 100) {
            return 'Nama menu wajib diisi, maksimal 100 karakter.';
        }
        if (!is_numeric($harga) || (int) $harga < 1 || (int) $harga > self::MAX_INT) {
            return 'Harga harus angka antara 1 dan 2.147.483.647.';
        }
        if (!is_numeric($stok) || (int) $stok < 0 || (int) $stok > self::MAX_INT) {
            return 'Stok harus angka antara 0 dan 2.147.483.647.';
        }
        if ($kat <= 0 || !$this->kategori->getById($kat)) {
            return 'Kategori tidak valid.';
        }
        return null;
    }

    private function uploadGambar(): ?string {
        if (empty($_FILES['gambar']['name'])) return null;

        $file     = $_FILES['gambar'];
        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            json_response(['success' => false, 'message' => 'Upload gambar gagal, coba lagi.'], 422);
        }
        if (!in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            json_response(['success' => false, 'message' => 'Format gambar harus jpg, jpeg, png, atau webp.'], 422);
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            json_response(['success' => false, 'message' => 'Ukuran gambar maksimal 2MB.'], 422);
        }
        if (@getimagesize($file['tmp_name']) === false) {
            json_response(['success' => false, 'message' => 'File bukan gambar yang valid.'], 422);
        }

        $nama = 'menu_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi;
        if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $nama)) {
            json_response(['success' => false, 'message' => 'Gagal menyimpan gambar ke server.'], 500);
        }
        return $nama;
    }

    private function wajibPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }
    }

    private function wajibAdmin(): array {
        if (!isset($_SESSION['user'])) {
            json_response(['success' => false, 'message' => 'Silakan login dulu'], 401);
        }
        if ($_SESSION['user']['role'] !== 'admin') {
            json_response(['success' => false, 'message' => 'Aksi ini khusus admin'], 403);
        }
        return $_SESSION['user'];
    }
}