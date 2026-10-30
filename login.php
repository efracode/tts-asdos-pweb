<?php
// Ini buat nampilin form login untuk admin dan kasir.

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
// Sudah login? Langsung lempar ke halaman sesuai role
if (isset($_SESSION['user'])) {
    header('Location: ' . ($_SESSION['user']['role'] === 'admin' ? 'admin.php' : 'kasir.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kasir Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/icons.css?v=4">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
    <div class="text-center mb-8">
        <span class="app-icon text-4xl text-orange-500" data-icon="utensils" aria-hidden="true"></span>
        <h1 class="text-2xl font-bold text-slate-800 mt-3">Kasir Restoran</h1>
        <p class="text-slate-500 text-sm mt-1">Silakan login untuk mulai</p>
    </div>

    <div id="pesan" class="hidden mb-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3"></div>

    <form id="formLogin" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Username</label>
            <input type="text" name="username" id="username" required
                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400"
                   placeholder="contoh: kasir1">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Password</label>
            <input type="password" name="password" id="password" required
                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400"
                   placeholder="********">
        </div>
        <button type="submit" id="tombolLogin"
                class="w-full rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 transition-colors">
            <span class="app-icon mr-2" data-icon="login" aria-hidden="true"></span>Login
        </button>
    </form>

    <p class="text-center text-xs text-slate-400 mt-6">admin / admin123 &middot; kasir1 / kasir123</p>
</div>

<script src="assets/js/login.js"></script>
</body>
</html>
