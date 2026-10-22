<?php
// Autoloader: load class otomatis tanpa require manual
spl_autoload_register(function (string $class): void {
    $candidates = [
        __DIR__ . '/models/' . strtolower($class) . '.php',      // Menu, Kategori, Transaksi, User
        __DIR__ . '/config/' . strtolower($class) . '.php',      // Database
        __DIR__ . '/controllers/' . lcfirst($class) . '.php',    // controllers
    ];

    foreach ($candidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Helper: kirim response JSON lalu berhenti
function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Router
$route  = $_GET['route']  ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($route) {
    case 'menu':
        (new MenuController())->handle($action);
        break;

    case 'transaksi':
        (new TransaksiController())->handle($action);
        break;
    
    case 'kategori':
        (new KategoriController())->handle($action);
        break;    

    case 'home':
        json_response([
            'success' => true,
            'message' => 'API Kasir Restoran aktif',
            'contoh'  => [
                'index.php?route=menu&action=list',
                'index.php?route=transaksi&action=riwayat',
            ],
        ]);
        break;

    default:
        json_response(['success' => false, 'message' => 'Route tidak ditemukan'], 404);
}