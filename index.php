<?php
// Ini buat menjadi pintu masuk API dan ngarahin request ke controller.

function tanganiError(Throwable $error): void {
    error_log($error->__toString());
    json_response(['success' => false, 'message' => 'Kesalahan server'], 500);
}

set_exception_handler('tanganiError');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Daftarkan file secara eksplisit agar alur aplikasi mudah diikuti.
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/user.php';
require_once __DIR__ . '/models/menu.php';
require_once __DIR__ . '/models/kategori.php';
require_once __DIR__ . '/models/transaksi.php';
require_once __DIR__ . '/controllers/authController.php';
require_once __DIR__ . '/controllers/menuController.php';
require_once __DIR__ . '/controllers/kategoriController.php';
require_once __DIR__ . '/controllers/transaksiController.php';
require_once __DIR__ . '/controllers/userController.php';

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

$route  = $_GET['route']  ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($route) {
    case 'auth':
        (new AuthController())->handle($action);
        break;
    case 'menu':
        (new MenuController())->handle($action);
        break;
    case 'kategori':
        (new KategoriController())->handle($action);
        break;
    case 'transaksi':
        (new TransaksiController())->handle($action);
        break;
    case 'user':
        (new UserController())->handle($action);
        break;
    case 'home':
        json_response(['success' => true, 'message' => 'API Aktif']);
        break;
    default:
        json_response(['success' => false, 'message' => 'Route tidak ditemukan'], 404);
}
