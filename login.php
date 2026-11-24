<?php
// Ini buat ngecek sesi dan menampilkan halaman login/register.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
if (isset($_SESSION['user'])) {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'admin.php' : 'kasir.php'));
    exit;
}

require __DIR__ . '/views/login.view.php';
