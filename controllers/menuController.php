<?php
// untuk mengelola list, tambah, ubah, dan hapus menu beserta gambar produk.

class MenuController {
    private Menu $menu;
    private Kategori $kategori;
    const MAX_INT = 2147483647;
    const MAX_IMAGE_BYTES = 2097152; // 2 MB
    const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

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

        $gambarPath = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = $this->prosesUploadGambar($_FILES['gambar']);
            if (!$upload['ok']) {
                json_response(['success' => false, 'message' => $upload['error']], 422);
            }
            $gambarPath = $upload['path'];
        }

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) $_POST['stok'];
        $this->menu->gambar      = $gambarPath;

        if ($this->menu->create()) {
            json_response(['success' => true, 'message' => 'Menu berhasil ditambah'], 201);
        }

        $this->hapusFileGambar($gambarPath);
        json_response(['success' => false, 'message' => 'Gagal menambah menu'], 500);
    }

    private function update(): void {
        $this->wajibPost();
        $id   = (int) ($_POST['id'] ?? 0);
        $lama = $this->menu->getById($id);
        if (!$lama) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }

        $error = $this->validasi();
        if ($error) {
            json_response(['success' => false, 'message' => $error], 422);
        }

        $gambarLama  = $lama['gambar'] ?? null;
        $gambarBaru  = $gambarLama;
        $fileDiunggah = null;
        $hapusGambar = ($_POST['hapus_gambar'] ?? '0') === '1';

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = $this->prosesUploadGambar($_FILES['gambar']);
            if (!$upload['ok']) {
                json_response(['success' => false, 'message' => $upload['error']], 422);
            }
            $fileDiunggah = $upload['path'];
            $gambarBaru   = $fileDiunggah;
        } elseif ($hapusGambar) {
            $gambarBaru = null;
        }

        $this->menu->id_kategori = (int) $_POST['id_kategori'];
        $this->menu->nama_menu   = trim($_POST['nama_menu']);
        $this->menu->harga       = (int) $_POST['harga'];
        $this->menu->stok        = (int) $_POST['stok'];
        $this->menu->gambar      = $gambarBaru;

        if ($this->menu->update($id)) {
            if ($gambarLama && ($fileDiunggah !== null || $hapusGambar)) {
                $this->hapusFileGambar($gambarLama);
            }
            json_response(['success' => true, 'message' => 'Menu berhasil diubah']);
        }

        if ($fileDiunggah !== null) {
            $this->hapusFileGambar($fileDiunggah);
        }
        json_response(['success' => false, 'message' => 'Gagal mengubah menu'], 500);
    }

    private function delete(): void {
        $this->wajibPost();
        $id   = (int) ($_POST['id'] ?? 0);
        $lama = $this->menu->getById($id);
        if (!$lama) {
            json_response(['success' => false, 'message' => 'Menu tidak ditemukan'], 404);
        }

        try {
            if ($this->menu->delete($id)) {
                $this->hapusFileGambar($lama['gambar'] ?? null);
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

    private function prosesUploadGambar(array $file): array {
        $errCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE) {
            return ['ok' => false, 'path' => null, 'error' => 'Ukuran gambar melebihi batas maksimal 2 MB.'];
        }
        if ($errCode !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'path' => null, 'error' => 'Upload gambar gagal diproses. Silakan pilih ulang file gambar.'];
        }

        $tmpPath = $file['tmp_name'] ?? '';
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['ok' => false, 'path' => null, 'error' => 'Sumber file upload tidak valid.'];
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_IMAGE_BYTES) {
            return ['ok' => false, 'path' => null, 'error' => 'Ukuran file gambar maksimal 2 MB.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($tmpPath);
        if (!is_string($mime) || !isset(self::ALLOWED_MIME[$mime])) {
            return ['ok' => false, 'path' => null, 'error' => 'Format gambar harus JPG, PNG, atau WEBP.'];
        }

        if (@getimagesize($tmpPath) === false) {
            return ['ok' => false, 'path' => null, 'error' => 'File tidak dikenali sebagai gambar yang valid.'];
        }

        $targetDir = __DIR__ . '/../uploads/menu';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
            return ['ok' => false, 'path' => null, 'error' => 'Folder penyimpanan gambar tidak dapat dibuat.'];
        }

        $ext      = self::ALLOWED_MIME[$mime];
        $namaFile = 'menu_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $tujuan   = $targetDir . '/' . $namaFile;

        if (!move_uploaded_file($tmpPath, $tujuan)) {
            return ['ok' => false, 'path' => null, 'error' => 'Gagal menyimpan file gambar ke server.'];
        }

        return ['ok' => true, 'path' => 'uploads/menu/' . $namaFile, 'error' => null];
    }

    private function hapusFileGambar(?string $relativePath): void {
        if (!$relativePath) {
            return;
        }
        $namaFile = basename($relativePath);
        if (!preg_match('/^menu_[a-f0-9]+\.(jpg|png|webp)$/i', $namaFile)) {
            return;
        }
        $fullPath = __DIR__ . '/../uploads/menu/' . $namaFile;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
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
