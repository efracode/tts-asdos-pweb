<?php
// Ini buat ngatur login, logout, dan pemeriksaan sesi user.

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

        $berhasilLogin = $this->user->login();

        if (!$berhasilLogin) {
            json_response(['success' => false, 'message' => 'Username atau password salah'], 401);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => $this->user->id,
            'username' => $this->user->username,
            'nama'     => $this->user->nama,
            'role'     => $this->user->role,
        ];

        json_response(['success' => true, 'message' => 'Login berhasil', 'data' => $_SESSION['user']]);
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
