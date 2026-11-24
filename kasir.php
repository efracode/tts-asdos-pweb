<?php
// Ini buat ngecek sesi kasir lalu menampilkan antarmuka POS.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['user']['role'] !== 'kasir') {
    header('Location: admin.php');
    exit;
}
$user = $_SESSION['user'];

require __DIR__ . '/views/kasir.view.php';
