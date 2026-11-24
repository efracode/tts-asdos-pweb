<?php
// Ini buat ngecek sesi admin lalu menampilkan antarmuka backoffice admin.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: kasir.php');
    exit;
}
$user = $_SESSION['user'];

require __DIR__ . '/views/admin.view.php';
