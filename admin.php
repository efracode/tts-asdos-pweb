<?php
// Ini buat nampilin halaman admin dan dialog pengelolaan data.

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kasir Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/icons.css?v=4">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @page { size: 80mm 200mm; margin: 0; }
        @media print {
            body * { visibility: hidden !important; }
            #modalStruk, #modalStruk * { visibility: visible !important; }
            #modalStruk { display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 80mm !important; padding: 0 !important; background: white !important; }
            #modalStruk > div { display: block !important; position: static !important; width: 72mm !important; max-width: none !important; margin: 0; padding: 0; border: 0; box-shadow: none; overflow: visible !important; animation: none !important; transform: none !important; }
            #modalStruk > div > div { padding: 0 !important; }
            #areaStruk { display: block !important; position: static; width: 72mm; padding: 4mm; background: white; color: black; font: 12px/1.4 Arial, sans-serif; }
            #areaStruk [class~="text-[10px]"] { font-size: 11px !important; }
            #areaStruk [class~="text-[11px]"], #areaStruk [class~="text-xs"] { font-size: 12px !important; }
            #areaStruk [class~="text-sm"] { font-size: 14px !important; }
            #areaStruk + div { display: none !important; }
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-toast { animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

<!-- Ini buat nampilin pesan berhasil atau gagal. -->
<div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 pointer-events-none"></div>

<!-- Ini buat nampilin navigasi dan akun yang sedang masuk. -->
<nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur-md lg:px-8">
    <div class="mx-auto flex w-full max-w-[1600px] items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3 sm:gap-3.5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-400 shadow-md shadow-orange-500/25 ring-1 ring-orange-400/30">
                <span class="app-icon text-2xl" data-icon="register" aria-hidden="true"></span>
            </div>
            <div class="min-w-0">
                <span class="block truncate text-base font-extrabold leading-tight tracking-tight text-slate-900 sm:text-lg">Kasir Restoran</span>
                <span class="mt-1 hidden items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-blue-700 sm:inline-flex sm:text-[11px]">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Admin portal
                </span>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-slate-50/80 px-2 py-1.5 sm:px-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-600 text-xs font-extrabold text-white shadow-sm shadow-blue-700/20">
                    <?= htmlspecialchars(strtoupper(substr($user['nama'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="hidden min-w-0 text-left leading-tight sm:block">
                    <span class="block max-w-40 truncate text-xs font-bold text-slate-800"><?= htmlspecialchars($user['nama']) ?></span>
                    <span class="mt-1 flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-blue-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Administrator
                    </span>
                </div>
            </div>
            <button onclick="logout()" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-bold text-rose-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-300 sm:px-3.5">
                <span class="app-icon" data-icon="logout" aria-hidden="true"></span>
                <span>Keluar</span>
            </button>
        </div>
    </div>
</nav>

<!-- Ini buat nampilin konten utama halaman. -->
<main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full space-y-6">

    <!-- Ini buat nampilin ringkasan data admin. -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5">
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                <span class="app-icon" data-icon="utensils" aria-hidden="true"></span>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block">Total Menu</span>
                <span id="statMenu" class="text-xl lg:text-2xl font-extrabold text-slate-800">0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <span class="app-icon" data-icon="tags" aria-hidden="true"></span>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block">Kategori</span>
                <span id="statKategori" class="text-xl lg:text-2xl font-extrabold text-slate-800">0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <span class="app-icon" data-icon="receipt" aria-hidden="true"></span>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block">Transaksi</span>
                <span id="statTransaksi" class="text-xl lg:text-2xl font-extrabold text-slate-800">0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <span class="app-icon" data-icon="wallet" aria-hidden="true"></span>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-medium text-slate-400 block truncate">Total Omset</span>
                <span id="statOmset" class="text-lg lg:text-xl font-extrabold text-slate-800 truncate block">Rp 0</span>
            </div>
        </div>
    </div>

    <!-- Ini buat berpindah bagian pengelolaan admin. -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <div class="flex gap-2 flex-wrap">
            <button id="tab-menu" onclick="bukaTab('menu')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-orange-500 text-white shadow-sm shadow-orange-500/20">
                <span class="app-icon" data-icon="burger" aria-hidden="true"></span>Kelola Menu
            </button>
            <button id="tab-kategori" onclick="bukaTab('kategori')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80">
                <span class="app-icon" data-icon="tags" aria-hidden="true"></span>Kategori
            </button>
            <button id="tab-riwayat" onclick="bukaTab('riwayat')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80">
                <span class="app-icon" data-icon="chart" aria-hidden="true"></span>Riwayat Penjualan
            </button>
            <button id="tab-kasir" onclick="bukaTab('kasir')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80">
                <span class="app-icon" data-icon="users-gear" aria-hidden="true"></span>Kelola Kasir
            </button>
        </div>
    </div>

    <!-- Ini buat nampilin pengelolaan menu. -->
    <section id="sec-menu" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs" data-icon="search" aria-hidden="true"></span>
                <input type="text" id="cariMenu" oninput="filterMenu()" placeholder="Cari nama menu atau kategori..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <button onclick="bukaModalMenu()" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs lg:text-sm font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-orange-500/20 shrink-0">
                <span class="app-icon" data-icon="plus" aria-hidden="true"></span>Tambah Menu Baru
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs lg:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200/80 uppercase text-[11px] font-bold tracking-wider">
                            <th class="py-3.5 px-4">Menu</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Harga</th>
                            <th class="py-3.5 px-4">Stok</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelMenu" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Ini buat nampilin pengelolaan kategori. -->
    <section id="sec-kategori" class="hidden space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs" data-icon="search" aria-hidden="true"></span>
                <input type="text" id="cariKategori" oninput="filterKategoriTabel()" placeholder="Cari kategori..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <button onclick="bukaModalKategori()" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs lg:text-sm font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-orange-500/20 shrink-0">
                <span class="app-icon" data-icon="plus" aria-hidden="true"></span>Tambah Kategori
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs lg:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200/80 uppercase text-[11px] font-bold tracking-wider">
                            <th class="py-3.5 px-4 w-20">ID</th>
                            <th class="py-3.5 px-4">Nama Kategori</th>
                            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelKategori" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Ini buat nampilin riwayat penjualan. -->
    <section id="sec-riwayat" class="hidden space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs" data-icon="search" aria-hidden="true"></span>
                <input type="text" id="cariRiwayat" oninput="filterRiwayatTabel()" placeholder="Cari No. TRX, Kasir, atau Tanggal..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <button onclick="muatRiwayat()" class="inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs lg:text-sm font-semibold px-4 py-2 rounded-xl transition-all border border-slate-200 shrink-0">
                <span class="app-icon" data-icon="refresh" aria-hidden="true"></span>Refresh Data
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs lg:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200/80 uppercase text-[11px] font-bold tracking-wider">
                            <th class="py-3.5 px-4">No. Transaksi</th>
                            <th class="py-3.5 px-4">Waktu</th>
                            <th class="py-3.5 px-4">Kasir</th>
                            <th class="py-3.5 px-4">Total</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-center">Struk</th>
                        </tr>
                    </thead>
                    <tbody id="tabelRiwayat" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Ini buat nampilin pengelolaan akun kasir. -->
    <section id="sec-kasir" class="hidden space-y-4">
        <!-- Ini buat nampilin ringkasan akun kasir. -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    <span class="app-icon" data-icon="users" aria-hidden="true"></span>
                </div>
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block">Total Kasir</span>
                    <span id="statTotalKasir" class="text-lg font-extrabold text-slate-800">0</span>
                </div>
            </div>
            <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-lg shrink-0">
                    <span class="app-icon" data-icon="register" aria-hidden="true"></span>
                </div>
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block">Total Transaksi</span>
                    <span id="statTotalTrxKasir" class="text-lg font-extrabold text-slate-800">0</span>
                </div>
            </div>
        </div>

        <!-- Ini buat mencari dan nambahin akun kasir. -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs" data-icon="search" aria-hidden="true"></span>
                <input type="text" id="cariKasir" oninput="filterKasirTabel()" placeholder="Cari nama kasir atau username..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <div class="flex gap-2 shrink-0">
                <button onclick="muatKasir()" class="inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs lg:text-sm font-semibold px-3 py-2 rounded-xl transition-all border border-slate-200">
                    <span class="app-icon" data-icon="refresh" aria-hidden="true"></span>Refresh
                </button>
                <button onclick="bukaModalUser()" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs lg:text-sm font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-blue-600/20">
                    <span class="app-icon" data-icon="user-plus" aria-hidden="true"></span>Tambah Kasir
                </button>
            </div>
        </div>

        <!-- Ini buat nampilin list akun kasir. -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs lg:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200/80 uppercase text-[11px] font-bold tracking-wider">
                            <th class="py-3.5 px-4">Kasir</th>
                            <th class="py-3.5 px-4">Username</th>
                            <th class="py-3.5 px-4">Transaksi</th>
                            <th class="py-3.5 px-4">Omset</th>
                            <th class="py-3.5 px-4">Terakhir Aktif</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelKasir" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>
    </section>

</main>

<!-- Ini buat nambah atau ngubah menu. -->
<div id="modalMenu" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulModalMenu" class="font-extrabold text-base lg:text-lg text-slate-900">Tambah Menu</h3>
            <button onclick="tutupModalMenu()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <span class="app-icon" data-icon="close" aria-hidden="true"></span>
            </button>
        </div>

        <div id="pesanMenu" class="hidden mx-6 mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium p-3"></div>

        <form id="formMenu" class="p-6 space-y-4">
            <input type="hidden" name="id" id="menuId">

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Menu <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_menu" id="menuNama" maxlength="100" required placeholder="Contoh: Nasi Goreng Spesial"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori <span class="text-rose-500">*</span></label>
                    <select name="id_kategori" id="menuKategori" required
                            class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500 transition-all bg-white"></select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stok Porsi <span class="text-rose-500">*</span></label>
                    <input type="number" name="stok" id="menuStok" min="0" max="2147483647" required placeholder="0"
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                <input type="number" name="harga" id="menuHarga" min="1" max="2147483647" required placeholder="Contoh: 25000"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500 transition-all">
            </div>

            <div class="flex gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="tutupModalMenu()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs lg:text-sm font-semibold transition-all">Batal</button>
                <button type="submit" id="btnSubmitMenu" class="flex-1 rounded-xl bg-orange-500 hover:bg-orange-600 text-white py-2.5 text-xs lg:text-sm font-bold transition-all shadow-sm shadow-orange-500/25 flex items-center justify-center gap-2">
                    <span id="btnSubmitMenuTeks">Simpan Menu</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ini buat nambah atau ngubah kategori. -->
<div id="modalKategori" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulModalKategori" class="font-extrabold text-base text-slate-900">Tambah Kategori</h3>
            <button onclick="tutupModalKategori()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <span class="app-icon" data-icon="close" aria-hidden="true"></span>
            </button>
        </div>

        <div id="pesanKategori" class="hidden mx-6 mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium p-3"></div>

        <form id="formKategori" class="p-6 space-y-4">
            <input type="hidden" id="kategoriId">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                <input type="text" id="kategoriNama" maxlength="50" required placeholder="Contoh: Aneka Jus"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500 transition-all">
            </div>

            <div class="flex gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalKategori()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs lg:text-sm font-semibold transition-all">Batal</button>
                <button type="submit" id="btnSubmitKategori" class="flex-1 rounded-xl bg-orange-500 hover:bg-orange-600 text-white py-2.5 text-xs lg:text-sm font-bold transition-all shadow-sm shadow-orange-500/25 flex items-center justify-center gap-2">
                    <span id="btnSubmitKategoriTeks">Simpan Kategori</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ini buat nampilin dan mencetak struk transaksi. -->
<div id="modalStruk" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="p-6">
            <div id="areaStruk"></div>
            <div class="flex gap-2.5 mt-6 pt-4 border-t border-slate-100">
                <button onclick="tutupStruk()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs font-semibold">Tutup</button>
                <button onclick="window.print()" class="flex-1 rounded-xl bg-slate-900 hover:bg-black text-white py-2.5 text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                    <span class="app-icon" data-icon="print" aria-hidden="true"></span>Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Ini buat minta konfirmasi sebelum aksi penting. -->
<div id="modalKonfirmasi" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center animate-toast border border-slate-200">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3">
            <span class="app-icon" data-icon="warning" aria-hidden="true"></span>
        </div>
        <h4 id="judulKonfirmasi" class="font-extrabold text-base text-slate-900 mb-1">Konfirmasi Hapus</h4>
        <p id="pesanKonfirmasi" class="text-xs text-slate-500 mb-5 leading-relaxed">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="flex gap-2.5">
            <button onclick="tutupKonfirmasi(false)" class="flex-1 rounded-xl border border-slate-200 text-slate-600 py-2.5 text-xs font-semibold hover:bg-slate-100">Batal</button>
            <button id="btnAksiKonfirmasi" onclick="tutupKonfirmasi(true)" class="flex-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white py-2.5 text-xs font-bold shadow-sm shadow-rose-600/25">Ya, Hapus</button>
        </div>
    </div>
</div>

<!-- Ini buat nambah atau ngubah akun user. -->
<div id="modalUser" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulModalUser" class="font-extrabold text-base lg:text-lg text-slate-900">Tambah Kasir</h3>
            <button onclick="tutupModalUser()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <span class="app-icon" data-icon="close" aria-hidden="true"></span>
            </button>
        </div>

        <div id="pesanUser" class="hidden mx-6 mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium p-3"></div>

        <form id="formUser" class="p-6 space-y-4">
            <input type="hidden" id="userId">

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" id="userNama" maxlength="100" required placeholder="Contoh: Budi Santoso"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username <span class="text-rose-500">*</span></label>
                    <input type="text" id="userUsername" maxlength="50" required placeholder="huruf_angka_saja"
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all">
                    <p class="text-[10px] text-slate-400 mt-1">Huruf, angka & underscore</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role</label>
                    <select id="userRole" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all bg-white">
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div id="wrapperPasswordUser">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <input type="password" id="userPassword" minlength="5" placeholder="Minimal 5 karakter"
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all pr-10">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span id="iconPassword" class="app-icon" data-icon="eye" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <div class="flex gap-2.5 pt-3 border-t border-slate-100">
                <button type="button" onclick="tutupModalUser()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs lg:text-sm font-semibold transition-all">Batal</button>
                <button type="submit" id="btnSubmitUser" class="flex-1 rounded-xl bg-blue-600 hover:bg-blue-700 text-white py-2.5 text-xs lg:text-sm font-bold transition-all shadow-sm shadow-blue-600/25 flex items-center justify-center gap-2">
                    <span id="btnSubmitUserTeks">Simpan Kasir</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ini buat mengatur ulang password kasir. -->
<div id="modalResetPassword" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulResetPassword" class="font-extrabold text-base text-slate-900">Reset Password</h3>
            <button onclick="tutupModalResetPassword()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <span class="app-icon" data-icon="close" aria-hidden="true"></span>
            </button>
        </div>

        <div id="pesanResetPassword" class="hidden mx-6 mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium p-3"></div>

        <form id="formResetPassword" class="p-6 space-y-4">
            <input type="hidden" id="resetPasswordUserId">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                <span class="app-icon mr-1" data-icon="warning" aria-hidden="true"></span>
                Password kasir <strong id="resetPasswordNama"></strong> akan direset. Pastikan untuk menginformasikan password baru kepada kasir.
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru <span class="text-rose-500">*</span></label>
                <input type="password" id="resetNewPassword" minlength="5" required placeholder="Minimal 5 karakter"
                       class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all">
            </div>
            <div class="flex gap-2.5 pt-2 border-t border-slate-100">
                <button type="button" onclick="tutupModalResetPassword()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs lg:text-sm font-semibold transition-all">Batal</button>
                <button type="submit" id="btnResetPassword" class="flex-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-white py-2.5 text-xs lg:text-sm font-bold transition-all shadow-sm shadow-amber-500/25 flex items-center justify-center gap-2">
                    <span id="btnResetPasswordTeks">Reset Password</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/admin.js"></script>
</body>
</html>
