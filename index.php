<?php
session_start();

// Body JSON murni (fetch + Content-Type: application/json) digabung ke $_POST
if (empty($_POST)) {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $json = json_decode(file_get_contents('php://input'), true);
        if (is_array($json)) $_POST = $json;
    }
}

spl_autoload_register(function (string $class): void {
    $candidates = [
        __DIR__ . '/models/' . strtolower($class) . '.php',
        __DIR__ . '/config/' . strtolower($class) . '.php',
        __DIR__ . '/controllers/' . lcfirst($class) . '.php',
    ];
    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

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
    case 'home':
        json_response(['success' => true, 'message' => 'API Aktif']);
        break;
    default:
        json_response(['success' => false, 'message' => 'Route tidak ditemukan'], 404);
}