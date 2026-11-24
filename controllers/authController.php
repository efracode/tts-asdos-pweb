<?php
// Ini buat ngatur login, register kasir, logout, dan pemeriksaan sesi user.

class AuthController {
    private User $user;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->user = new User($db);
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'register':
                $this->register();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'session':
            case 'check':
                $this->session();
                break;
            default:
                json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    private function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            json_response(['success' => false, 'message' => 'Username dan password wajib diisi'], 422);
        }

        $this->user->username = $username;
        $this->user->password = $password;

        $result = $this->user->login();

        if (!$result['success']) {
            if ($result['reason'] === 'account_inactive') {
                json_response([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan oleh Admin. Silakan hubungi Administrator untuk mengaktifkan kembali akun Anda.',
                    'reason' => 'account_inactive',
                ], 403);
            }
            json_response(['success' => false, 'message' => 'Username atau password salah'], 401);
        }

        $this->mulaiSesiUser();
        json_response(['success' => true, 'message' => 'Login berhasil', 'data' => $_SESSION['user']]);
    }

    private function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $nama               = trim($_POST['nama'] ?? '');
        $username           = trim($_POST['username'] ?? '');
        $password           = $_POST['password'] ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        if ($nama === '' || $username === '' || $password === '') {
            json_response(['success' => false, 'message' => 'Nama lengkap, username, dan password wajib diisi'], 422);
        }
        if (mb_strlen($nama) > 100) {
            json_response(['success' => false, 'message' => 'Nama maksimal 100 karakter'], 422);
        }
        if (strlen($username) < 3 || strlen($username) > 50) {
            json_response(['success' => false, 'message' => 'Username harus 3-50 karakter'], 422);
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            json_response(['success' => false, 'message' => 'Username hanya boleh mengandung huruf, angka, dan underscore'], 422);
        }
        if (strlen($password) < 5) {
            json_response(['success' => false, 'message' => 'Password minimal 5 karakter'], 422);
        }
        if ($password !== $konfirmasiPassword) {
            json_response(['success' => false, 'message' => 'Konfirmasi password tidak cocok'], 422);
        }

        // Pendaftaran publik selalu membuat akun kasir aktif.
        $this->user->username  = $username;
        $this->user->password  = $password;
        $this->user->nama      = $nama;
        $this->user->role      = 'kasir';
        $this->user->is_active = 1;

        try {
            if (!$this->user->create()) {
                json_response(['success' => false, 'message' => 'Gagal mendaftarkan akun'], 500);
            }
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                json_response(['success' => false, 'message' => "Username \"$username\" sudah digunakan. Silakan pilih username lain."], 409);
            }
            throw $e;
        }

        $this->user->login();
        $this->mulaiSesiUser();
        json_response([
            'success' => true,
            'message' => 'Registrasi berhasil! Mengarahkan ke halaman kasir.',
            'data'    => $_SESSION['user'],
        ], 201);
    }

    private function mulaiSesiUser(): void {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => $this->user->id,
            'username' => $this->user->username,
            'nama'     => $this->user->nama,
            'role'     => $this->user->role,
        ];
    }

    private function logout(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $_SESSION = [];
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
        session_destroy();
        json_response(['success' => true, 'message' => 'Logout berhasil']);
    }

    private function session(): void {
        if (isset($_SESSION['user'])) {
            json_response(['success' => true, 'logged_in' => true, 'data' => $_SESSION['user']]);
        }
        json_response(['success' => true, 'logged_in' => false, 'data' => null]);
    }
}
