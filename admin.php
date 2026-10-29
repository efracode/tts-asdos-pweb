<?php
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            body * { visibility: hidden; }
            #areaStruk, #areaStruk * { visibility: visible; }
            #areaStruk { position: absolute; left: 0; top: 0; width: 100%; }
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-toast { animation: slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 pointer-events-none"></div>

<!-- Top Navigation -->
<nav class="bg-white border-b border-slate-200 px-4 lg:px-8 py-3.5 flex items-center justify-between sticky top-0 z-40 shadow-sm backdrop-blur-md bg-white/95">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-orange-500 to-amber-400 flex items-center justify-center text-white shadow-md shadow-orange-500/20">
            <i class="fa-solid fa-utensils text-lg"></i>
        </div>
        <div>
            <span class="font-extrabold text-base lg:text-lg text-slate-900 tracking-tight block leading-tight">Kasir Restoran</span>
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Admin Portal</span>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100/80 border border-slate-200/60">
            <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div class="text-left leading-tight">
                <span class="text-xs font-bold text-slate-700 block"><?= htmlspecialchars($user['nama']) ?></span>
                <span class="text-[10px] font-medium text-blue-600 uppercase tracking-wide">Administrator</span>
            </div>
        </div>
        <button onclick="logout()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-3 py-2 rounded-lg transition-colors border border-transparent hover:border-rose-200">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span class="hidden sm:inline">Logout</span>
        </button>
    </div>
</nav>

<!-- Main Container -->
<main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full space-y-6">

    <!-- Metric Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5">
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block">Total Menu</span>
                <span id="statMenu" class="text-xl lg:text-2xl font-extrabold text-slate-800">0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block">Kategori</span>
                <span id="statKategori" class="text-xl lg:text-2xl font-extrabold text-slate-800">0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block">Transaksi</span>
                <span id="statTransaksi" class="text-xl lg:text-2xl font-extrabold text-slate-800">0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 lg:p-5 border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-medium text-slate-400 block truncate">Total Omset</span>
                <span id="statOmset" class="text-lg lg:text-xl font-extrabold text-slate-800 truncate block">Rp 0</span>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <div class="flex gap-2 flex-wrap">
            <button id="tab-menu" onclick="bukaTab('menu')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-orange-500 text-white shadow-sm shadow-orange-500/20">
                <i class="fa-solid fa-burger"></i>Kelola Menu
            </button>
            <button id="tab-kategori" onclick="bukaTab('kategori')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80">
                <i class="fa-solid fa-tags"></i>Kategori
            </button>
            <button id="tab-riwayat" onclick="bukaTab('riwayat')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80">
                <i class="fa-solid fa-chart-line"></i>Riwayat Penjualan
            </button>
            <button id="tab-kasir" onclick="bukaTab('kasir')" class="px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80">
                <i class="fa-solid fa-users-gear"></i>Kelola Kasir
            </button>
        </div>
    </div>

    <!-- SECTION: MENU -->
    <section id="sec-menu" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="cariMenu" oninput="filterMenu()" placeholder="Cari nama menu atau kategori..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <button onclick="bukaModalMenu()" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs lg:text-sm font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-orange-500/20 shrink-0">
                <i class="fa-solid fa-plus"></i>Tambah Menu Baru
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

    <!-- SECTION: KATEGORI -->
    <section id="sec-kategori" class="hidden space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="cariKategori" oninput="filterKategoriTabel()" placeholder="Cari kategori..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <button onclick="bukaModalKategori()" class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-xs lg:text-sm font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-orange-500/20 shrink-0">
                <i class="fa-solid fa-plus"></i>Tambah Kategori
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

    <!-- SECTION: RIWAYAT -->
    <section id="sec-riwayat" class="hidden space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="cariRiwayat" oninput="filterRiwayatTabel()" placeholder="Cari No. TRX, Kasir, atau Tanggal..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <button onclick="muatRiwayat()" class="inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs lg:text-sm font-semibold px-4 py-2 rounded-xl transition-all border border-slate-200 shrink-0">
                <i class="fa-solid fa-rotate"></i>Refresh Data
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

    <!-- SECTION: KELOLA KASIR -->
    <section id="sec-kasir" class="hidden space-y-4">
        <!-- Quick Stats Kasir -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block">Total Kasir</span>
                    <span id="statTotalKasir" class="text-lg font-extrabold text-slate-800">0</span>
                </div>
            </div>
            <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block">Kasir Aktif</span>
                    <span id="statKasirAktif" class="text-lg font-extrabold text-emerald-600">0</span>
                </div>
            </div>
            <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block">Nonaktif</span>
                    <span id="statKasirNonaktif" class="text-lg font-extrabold text-rose-600">0</span>
                </div>
            </div>
            <div class="bg-white rounded-xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <div>
                    <span class="text-[11px] font-medium text-slate-400 block">Total Transaksi</span>
                    <span id="statTotalTrxKasir" class="text-lg font-extrabold text-slate-800">0</span>
                </div>
            </div>
        </div>

        <!-- Search + Add -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="cariKasir" oninput="filterKasirTabel()" placeholder="Cari nama kasir atau username..."
                       class="w-full pl-9 pr-4 py-2 text-xs lg:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
            </div>
            <div class="flex gap-2 shrink-0">
                <button onclick="muatKasir()" class="inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs lg:text-sm font-semibold px-3 py-2 rounded-xl transition-all border border-slate-200">
                    <i class="fa-solid fa-rotate"></i>Refresh
                </button>
                <button onclick="bukaModalUser()" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs lg:text-sm font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm shadow-blue-600/20">
                    <i class="fa-solid fa-user-plus"></i>Tambah Kasir
                </button>
            </div>
        </div>

        <!-- Table Kasir -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs lg:text-sm border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 border-b border-slate-200/80 uppercase text-[11px] font-bold tracking-wider">
                            <th class="py-3.5 px-4">Kasir</th>
                            <th class="py-3.5 px-4">Username</th>
                            <th class="py-3.5 px-4">Status</th>
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

<!-- MODAL: MENU (Tambah / Edit) -->
<div id="modalMenu" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulModalMenu" class="font-extrabold text-base lg:text-lg text-slate-900">Tambah Menu</h3>
            <button onclick="tutupModalMenu()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
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

<!-- MODAL: KATEGORI (Tambah / Edit) -->
<div id="modalKategori" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulModalKategori" class="font-extrabold text-base text-slate-900">Tambah Kategori</h3>
            <button onclick="tutupModalKategori()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
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

<!-- MODAL: STRUK TRANSAKSI -->
<div id="modalStruk" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="p-6">
            <div id="areaStruk"></div>
            <div class="flex gap-2.5 mt-6 pt-4 border-t border-slate-100">
                <button onclick="tutupStruk()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs font-semibold">Tutup</button>
                <button onclick="window.print()" class="flex-1 rounded-xl bg-slate-900 hover:bg-black text-white py-2.5 text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-print"></i>Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI KUSTOM (Pengganti confirm() bawaan) -->
<div id="modalKonfirmasi" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center animate-toast border border-slate-200">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h4 id="judulKonfirmasi" class="font-extrabold text-base text-slate-900 mb-1">Konfirmasi Hapus</h4>
        <p id="pesanKonfirmasi" class="text-xs text-slate-500 mb-5 leading-relaxed">Apakah Anda yakin ingin menghapus data ini?</p>
        <div class="flex gap-2.5">
            <button onclick="tutupKonfirmasi(false)" class="flex-1 rounded-xl border border-slate-200 text-slate-600 py-2.5 text-xs font-semibold hover:bg-slate-100">Batal</button>
            <button id="btnAksiKonfirmasi" onclick="tutupKonfirmasi(true)" class="flex-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white py-2.5 text-xs font-bold shadow-sm shadow-rose-600/25">Ya, Hapus</button>
        </div>
    </div>
</div>

<!-- MODAL: KELOLA USER (Tambah / Edit) -->
<div id="modalUser" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulModalUser" class="font-extrabold text-base lg:text-lg text-slate-900">Tambah Kasir</h3>
            <button onclick="tutupModalUser()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
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
                        <i id="iconPassword" class="fa-solid fa-eye"></i>
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

<!-- MODAL: RESET PASSWORD -->
<div id="modalResetPassword" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="judulResetPassword" class="font-extrabold text-base text-slate-900">Reset Password</h3>
            <button onclick="tutupModalResetPassword()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="pesanResetPassword" class="hidden mx-6 mt-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium p-3"></div>

        <form id="formResetPassword" class="p-6 space-y-4">
            <input type="hidden" id="resetPasswordUserId">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
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

<script>
const API = 'index.php?route=';
const MAX_INT = 2147483647;

let daftarMenu = [], daftarKategori = [], daftarRiwayat = [], daftarKasir = [];
let callbackKonfirmasi = null;

const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

// --- SISTEM TOAST NOTIFICATION MODERN ---
function tampilkanToast(pesan, tipe = 'sukses') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const config = {
        sukses: { bg: 'bg-emerald-600 text-white', icon: 'fa-circle-check', border: 'border-emerald-500' },
        error:  { bg: 'bg-rose-600 text-white', icon: 'fa-circle-exclamation', border: 'border-rose-500' },
        info:   { bg: 'bg-slate-800 text-white', icon: 'fa-circle-info', border: 'border-slate-700' }
    }[tipe] || { bg: 'bg-slate-800 text-white', icon: 'fa-circle-info', border: 'border-slate-700' };

    toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl ${config.bg} ${config.border} border text-xs font-semibold animate-toast min-w-[280px] max-w-sm`;
    toast.innerHTML = `
        <i class="fa-solid ${config.icon} text-base shrink-0"></i>
        <span class="flex-1">${pesan}</span>
        <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100 text-sm ml-2">&times;</button>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// --- MODAL KONFIRMASI MODERN ---
function mintaKonfirmasi(judul, pesan) {
    return new Promise((resolve) => {
        document.getElementById('judulKonfirmasi').innerText = judul;
        document.getElementById('pesanKonfirmasi').innerText = pesan;
        document.getElementById('modalKonfirmasi').classList.remove('hidden');
        callbackKonfirmasi = resolve;
    });
}
function tutupKonfirmasi(hasil) {
    document.getElementById('modalKonfirmasi').classList.add('hidden');
    if (callbackKonfirmasi) {
        callbackKonfirmasi(hasil);
        callbackKonfirmasi = null;
    }
}

// --- TAB SWITCHER ---
function bukaTab(nama) {
    ['menu', 'kategori', 'riwayat', 'kasir'].forEach(t => {
        const sec = document.getElementById('sec-' + t);
        const tab = document.getElementById('tab-' + t);
        if (!sec || !tab) return;
        const activeColor = t === 'kasir' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-orange-500 text-white shadow-sm shadow-orange-500/20';
        if (t === nama) {
            sec.classList.remove('hidden');
            tab.className = 'px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 ' + activeColor;
            if (t === 'kasir' && !daftarKasir.length) muatKasir();
        } else {
            sec.classList.add('hidden');
            tab.className = 'px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80';
        }
    });
}

// --- DATA FETCHING & STATS ---
async function muatMenu() {
    try {
        const r = await fetch(API + 'menu&action=list').then(res => res.json());
        daftarMenu = r.data || [];
        renderMenu();
        updateStats();
    } catch (err) {
        tampilkanToast('Gagal memuat data menu', 'error');
    }
}

async function muatKategori() {
    try {
        const r = await fetch(API + 'kategori&action=list').then(res => res.json());
        daftarKategori = r.data || [];
        renderKategori();
        renderSelectKategori();
        updateStats();
    } catch (err) {
        tampilkanToast('Gagal memuat data kategori', 'error');
    }
}

async function muatRiwayat() {
    try {
        const r = await fetch(API + 'transaksi&action=riwayat').then(res => res.json());
        daftarRiwayat = r.data || [];
        renderRiwayat();
        updateStats();
    } catch (err) {
        tampilkanToast('Gagal memuat riwayat transaksi', 'error');
    }
}

function updateStats() {
    document.getElementById('statMenu').innerText = daftarMenu.length;
    document.getElementById('statKategori').innerText = daftarKategori.length;
    document.getElementById('statTransaksi').innerText = daftarRiwayat.length;
    const totalOmset = daftarRiwayat
        .filter(r => r.status === 'selesai')
        .reduce((sum, r) => sum + Number(r.total), 0);
    document.getElementById('statOmset').innerText = rupiah(totalOmset);
}

// --- RENDER TABLE MENU ---
function renderMenu(data = daftarMenu) {
    const tbody = document.getElementById('tabelMenu');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-slate-400 py-10">
            <i class="fa-solid fa-box-open text-3xl mb-2 block opacity-40"></i>Tidak ada data menu ditemukan.
        </td></tr>`;
        return;
    }
    tbody.innerHTML = data.map(m => `
        <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                        ${m.gambar
                            ? `<img src="uploads/${m.gambar}?t=${Date.now()}" class="w-full h-full object-cover">`
                            : '<i class="fa-solid fa-utensils text-slate-300 text-lg"></i>'}
                    </div>
                    <span class="font-bold text-slate-900">${m.nama_menu}</span>
                </div>
            </td>
            <td class="py-3 px-4">
                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    ${m.nama_kategori || 'Tanpa Kategori'}
                </span>
            </td>
            <td class="py-3 px-4 font-extrabold text-orange-600">${rupiah(m.harga)}</td>
            <td class="py-3 px-4">
                <span class="inline-flex items-center gap-1.5 font-semibold ${m.stok <= 5 ? 'text-rose-600 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-md' : 'text-slate-700'}">
                    <span class="w-1.5 h-1.5 rounded-full ${m.stok <= 5 ? 'bg-rose-500' : 'bg-emerald-500'}"></span>
                    ${m.stok} porsi
                </span>
            </td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-1">
                    <button onclick="editMenu(${m.id})" class="p-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Menu">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button onclick="hapusMenu(${m.id})" class="p-2 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Menu">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

function filterMenu() {
    const q = document.getElementById('cariMenu').value.toLowerCase().trim();
    const filtered = daftarMenu.filter(m =>
        m.nama_menu.toLowerCase().includes(q) ||
        (m.nama_kategori && m.nama_kategori.toLowerCase().includes(q))
    );
    renderMenu(filtered);
}

// --- RENDER TABLE KATEGORI ---
function renderKategori(data = daftarKategori) {
    const tbody = document.getElementById('tabelKategori');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-slate-400 py-10">
            <i class="fa-solid fa-tags text-3xl mb-2 block opacity-40"></i>Belum ada kategori.
        </td></tr>`;
        return;
    }
    tbody.innerHTML = data.map(k => `
        <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3 px-4 text-slate-400 font-mono text-xs">#${k.id}</td>
            <td class="py-3 px-4 font-bold text-slate-900">${k.nama_kategori}</td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-1">
                    <button onclick="editKategori(${k.id})" class="p-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Kategori">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button onclick="hapusKategori(${k.id})" class="p-2 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Kategori">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

function filterKategoriTabel() {
    const q = document.getElementById('cariKategori').value.toLowerCase().trim();
    const filtered = daftarKategori.filter(k => k.nama_kategori.toLowerCase().includes(q));
    renderKategori(filtered);
}

function renderSelectKategori() {
    document.getElementById('menuKategori').innerHTML = daftarKategori
        .map(k => `<option value="${k.id}">${k.nama_kategori}</option>`).join('');
}

// --- RENDER TABLE RIWAYAT ---
function renderRiwayat(data = daftarRiwayat) {
    const tbody = document.getElementById('tabelRiwayat');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-slate-400 py-10">
            <i class="fa-solid fa-receipt text-3xl mb-2 block opacity-40"></i>Belum ada transaksi terekam.
        </td></tr>`;
        return;
    }
    const badge = {
        selesai: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        pending: 'bg-amber-50 text-amber-700 border border-amber-200',
        batal:   'bg-rose-50 text-rose-700 border border-rose-200'
    };
    tbody.innerHTML = data.map(r => `
        <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3 px-4 font-mono font-bold text-slate-900">${r.no_transaksi}</td>
            <td class="py-3 px-4 text-slate-500 text-xs">${r.tanggal}</td>
            <td class="py-3 px-4">
                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700">
                    <i class="fa-solid fa-circle-user text-slate-400"></i>${r.nama_kasir}
                </span>
            </td>
            <td class="py-3 px-4 font-extrabold text-slate-900">${rupiah(r.total)}</td>
            <td class="py-3 px-4">
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-md ${badge[r.status] || 'bg-slate-100 text-slate-600'}">
                    ${r.status.toUpperCase()}
                </span>
            </td>
            <td class="py-3 px-4 text-center">
                <button onclick="bukaStruk(${r.id})" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-orange-600 bg-slate-100 hover:bg-orange-50 px-3 py-1.5 rounded-lg border border-slate-200 transition-colors">
                    <i class="fa-solid fa-receipt"></i>Lihat
                </button>
            </td>
        </tr>`).join('');
}

function filterRiwayatTabel() {
    const q = document.getElementById('cariRiwayat').value.toLowerCase().trim();
    const filtered = daftarRiwayat.filter(r =>
        r.no_transaksi.toLowerCase().includes(q) ||
        r.nama_kasir.toLowerCase().includes(q) ||
        r.tanggal.toLowerCase().includes(q) ||
        r.status.toLowerCase().includes(q)
    );
    renderRiwayat(filtered);
}

// --- INTERAKTIF UPLOAD GAMBAR DENGAN PREVIEW & INDIKATOR UKURAN ---
function handleFilePilih(input) {
    const file = input.files[0];
    if (!file) return;

    const maxUkuran = 2 * 1024 * 1024; // 2MB
    if (file.size > maxUkuran) {
        tampilkanToast('Ukuran gambar melebihi 2MB! Silakan pilih file yang lebih kecil.', 'error');
        input.value = '';
        return;
    }

    const ekstensi = file.name.split('.').pop().toLowerCase();
    if (!['jpg', 'jpeg', 'png', 'webp'].includes(ekstensi)) {
        tampilkanToast('Format file harus JPG, JPEG, PNG, atau WEBP.', 'error');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('imgPreview').src = e.target.result;
        document.getElementById('namaFilePreview').innerText = file.name;
        const sizeFormatted = file.size >= 1024 * 1024
            ? (file.size / (1024 * 1024)).toFixed(2) + ' MB'
            : (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('ukuranFilePreview').innerText = sizeFormatted + ' / Maks 2MB';
        document.getElementById('previewContainer').classList.remove('hidden');
        document.getElementById('dropzonePrompt').classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

// --- MODAL MENU LOGIC & ANTI-SPAM ---
function bukaModalMenu(id = null) {
    document.getElementById('formMenu').reset();
    document.getElementById('menuId').value = '';
    document.getElementById('pesanMenu').classList.add('hidden');
    renderSelectKategori();

    if (id !== null) {
        const m = daftarMenu.find(x => x.id == id);
        document.getElementById('judulModalMenu').innerText = 'Edit Menu: ' + m.nama_menu;
        document.getElementById('menuId').value = m.id;
        document.getElementById('menuNama').value = m.nama_menu;
        document.getElementById('menuKategori').value = m.id_kategori;
        document.getElementById('menuHarga').value = m.harga;
        document.getElementById('menuStok').value = m.stok;

    } else {
        document.getElementById('judulModalMenu').innerText = 'Tambah Menu Baru';
    }
    document.getElementById('modalMenu').classList.remove('hidden');
}

function editMenu(id) { bukaModalMenu(id); }
function tutupModalMenu() { document.getElementById('modalMenu').classList.add('hidden'); }

document.getElementById('formMenu').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitMenu');
    const btnTeks = document.getElementById('btnSubmitMenuTeks');
    const pesanEl = document.getElementById('pesanMenu');
    pesanEl.classList.add('hidden');

    const id = document.getElementById('menuId').value;
    const aksi = id ? 'update' : 'create';

    // Anti-Spam: Kunci tombol submit & berikan indikator loading
    btn.disabled = true;
    const originalText = btnTeks.innerText;
    btnTeks.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Menyimpan...';

    try {
        const formData = new FormData(e.target);
        const respon = await fetch(API + 'menu&action=' + aksi, { method: 'POST', body: formData });
        const hasil = await respon.json();

        if (hasil.success) {
            tutupModalMenu();
            tampilkanToast(hasil.message || 'Menu berhasil disimpan!', 'sukses');
            muatMenu();
        } else {
            pesanEl.innerText = hasil.message;
            pesanEl.classList.remove('hidden');
            tampilkanToast(hasil.message, 'error');
        }
    } catch (err) {
        pesanEl.innerText = 'Terjadi kesalahan jaringan saat menyimpan menu.';
        pesanEl.classList.remove('hidden');
        tampilkanToast('Gagal terhubung ke server.', 'error');
    } finally {
        btn.disabled = false;
        btnTeks.innerText = originalText;
    }
});

async function hapusMenu(id) {
    const m = daftarMenu.find(x => x.id == id);
    const nama = m ? m.nama_menu : 'menu ini';
    const yakin = await mintaKonfirmasi('Hapus Menu?', `Apakah Anda yakin ingin menghapus "${nama}"? Tindakan ini tidak dapat dibatalkan.`);
    if (!yakin) return;

    const body = new FormData();
    body.append('id', id);
    try {
        const r = await fetch(API + 'menu&action=delete', { method: 'POST', body }).then(res => res.json());
        if (r.success) {
            tampilkanToast('Menu berhasil dihapus!', 'sukses');
            muatMenu();
        } else {
            tampilkanToast(r.message, 'error');
        }
    } catch (err) {
        tampilkanToast('Gagal terhubung ke server.', 'error');
    }
}

// --- MODAL KATEGORI LOGIC & ANTI-SPAM ---
function bukaModalKategori(id = null) {
    document.getElementById('kategoriId').value = '';
    document.getElementById('kategoriNama').value = '';
    document.getElementById('pesanKategori').classList.add('hidden');

    if (id !== null) {
        const k = daftarKategori.find(x => x.id == id);
        document.getElementById('judulModalKategori').innerText = 'Edit Kategori: ' + k.nama_kategori;
        document.getElementById('kategoriId').value = k.id;
        document.getElementById('kategoriNama').value = k.nama_kategori;
    } else {
        document.getElementById('judulModalKategori').innerText = 'Tambah Kategori Baru';
    }
    document.getElementById('modalKategori').classList.remove('hidden');
}

function editKategori(id) { bukaModalKategori(id); }
function tutupModalKategori() { document.getElementById('modalKategori').classList.add('hidden'); }

document.getElementById('formKategori').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitKategori');
    const btnTeks = document.getElementById('btnSubmitKategoriTeks');
    const pesanEl = document.getElementById('pesanKategori');
    pesanEl.classList.add('hidden');

    const id = document.getElementById('kategoriId').value;
    const nama = document.getElementById('kategoriNama').value.trim();

    btn.disabled = true;
    const originalText = btnTeks.innerText;
    btnTeks.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Menyimpan...';

    const body = new FormData();
    body.append('nama_kategori', nama);
    if (id) body.append('id', id);

    try {
        const r = await fetch(API + 'kategori&action=' + (id ? 'update' : 'create'), { method: 'POST', body }).then(res => res.json());
        if (r.success) {
            tutupModalKategori();
            tampilkanToast(r.message || 'Kategori berhasil disimpan!', 'sukses');
            muatKategori();
            muatMenu();
        } else {
            pesanEl.innerText = r.message;
            pesanEl.classList.remove('hidden');
            tampilkanToast(r.message, 'error');
        }
    } catch (err) {
        pesanEl.innerText = 'Gagal terhubung ke server.';
        pesanEl.classList.remove('hidden');
        tampilkanToast('Gagal terhubung ke server.', 'error');
    } finally {
        btn.disabled = false;
        btnTeks.innerText = originalText;
    }
});

async function hapusKategori(id) {
    const k = daftarKategori.find(x => x.id == id);
    const nama = k ? k.nama_kategori : 'kategori ini';
    const yakin = await mintaKonfirmasi('Hapus Kategori?', `Yakin ingin menghapus kategori "${nama}"? Kategori yang masih berisi menu tidak bisa dihapus.`);
    if (!yakin) return;

    const body = new FormData();
    body.append('id', id);
    try {
        const r = await fetch(API + 'kategori&action=delete', { method: 'POST', body }).then(res => res.json());
        if (r.success) {
            tampilkanToast('Kategori berhasil dihapus!', 'sukses');
            muatKategori();
            muatMenu();
        } else {
            tampilkanToast(r.message, 'error');
        }
    } catch (err) {
        tampilkanToast('Gagal terhubung ke server.', 'error');
    }
}

// --- STRUK TRANSAKSI ---
async function bukaStruk(id) {
    const head = daftarRiwayat.find(r => r.id == id);
    try {
        const r = await fetch(API + 'transaksi&action=detail&id=' + id).then(res => res.json());
        if (!r.success) {
            tampilkanToast(r.message, 'error');
            return;
        }

        const items = r.data.map(d => `
            <div class="flex justify-between text-xs py-1">
                <span>${d.nama_menu} <span class="text-slate-400">x${d.qty}</span></span>
                <span class="font-bold">${rupiah(d.subtotal)}</span>
            </div>`).join('');

        document.getElementById('areaStruk').innerHTML = `
            <div class="text-center border-b border-dashed border-slate-300 pb-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mx-auto mb-1 text-sm font-bold">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <p class="font-extrabold text-slate-900 text-sm tracking-tight">KASIR RESTORAN</p>
                <p class="text-[11px] text-slate-500 font-mono mt-0.5">${head.no_transaksi}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">${head.tanggal} &bull; Kasir: ${head.nama_kasir}</p>
            </div>
            <div class="divide-y divide-dashed divide-slate-200">${items}</div>
            <div class="border-t border-dashed border-slate-300 mt-3 pt-3 text-xs space-y-1.5 font-medium">
                <div class="flex justify-between font-extrabold text-sm text-slate-900"><span>Total</span><span>${rupiah(head.total)}</span></div>
                <div class="flex justify-between text-slate-600"><span>Uang Bayar</span><span>${rupiah(head.bayar)}</span></div>
                <div class="flex justify-between text-emerald-600 font-bold"><span>Kembalian</span><span>${rupiah(head.kembalian)}</span></div>
            </div>`;
        document.getElementById('modalStruk').classList.remove('hidden');
    } catch (err) {
        tampilkanToast('Gagal memuat detail struk.', 'error');
    }
}
function tutupStruk() { document.getElementById('modalStruk').classList.add('hidden'); }

async function logout() {
    await fetch(API + 'auth&action=logout');
    location.href = 'login.php';
}

// =============================================
// KELOLA KASIR — DATA, RENDER, CRUD, MODALS
// =============================================
async function muatKasir() {
    try {
        const r = await fetch(API + 'user&action=kasir-list').then(res => res.json());
        daftarKasir = r.data || [];
        renderKasir();
        updateStatsKasir();
    } catch (err) {
        tampilkanToast('Gagal memuat data kasir', 'error');
    }
}

function updateStatsKasir() {
    const aktif = daftarKasir.filter(k => k.is_active == 1).length;
    const nonaktif = daftarKasir.filter(k => k.is_active == 0).length;
    const totalTrx = daftarKasir.reduce((s, k) => s + Number(k.total_transaksi || 0), 0);
    document.getElementById('statTotalKasir').innerText = daftarKasir.length;
    document.getElementById('statKasirAktif').innerText = aktif;
    document.getElementById('statKasirNonaktif').innerText = nonaktif;
    document.getElementById('statTotalTrxKasir').innerText = totalTrx;
}

function renderKasir(data = daftarKasir) {
    const tbody = document.getElementById('tabelKasir');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-slate-400 py-10">
            <i class="fa-solid fa-users text-3xl mb-2 block opacity-40"></i>Belum ada kasir terdaftar.
        </td></tr>`;
        return;
    }
    tbody.innerHTML = data.map(k => {
        const isActive = k.is_active == 1;
        const statusBadge = isActive
            ? '<span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Aktif</span>'
            : '<span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full bg-rose-50 text-rose-600 border border-rose-200"><span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>Nonaktif</span>';
        const avatar = k.nama ? k.nama.charAt(0).toUpperCase() : '?';
        const avatarBg = isActive ? 'bg-blue-600' : 'bg-slate-400';
        const trxTerakhir = k.transaksi_terakhir
            ? new Date(k.transaksi_terakhir).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})
            : '<span class="text-slate-400 italic">Belum ada</span>';
        return `
        <tr class="hover:bg-slate-50/80 transition-colors ${!isActive ? 'opacity-60' : ''}">
            <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full ${avatarBg} text-white flex items-center justify-center text-xs font-bold shrink-0">${avatar}</div>
                    <div>
                        <span class="font-bold text-slate-900 block leading-tight">${k.nama}</span>
                        <span class="text-[10px] text-slate-400">Bergabung ${new Date(k.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'})}</span>
                    </div>
                </div>
            </td>
            <td class="py-3 px-4"><code class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded-md">${k.username}</code></td>
            <td class="py-3 px-4">${statusBadge}</td>
            <td class="py-3 px-4 font-bold text-slate-800">${k.total_transaksi || 0}</td>
            <td class="py-3 px-4 font-extrabold text-orange-600">${rupiah(k.total_omset || 0)}</td>
            <td class="py-3 px-4 text-xs text-slate-500">${trxTerakhir}</td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-0.5">
                    <button onclick="editUser(${k.id})" class="p-2 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit User">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>
                    <button onclick="toggleKasirStatus(${k.id}, ${isActive ? 0 : 1})" class="p-2 rounded-lg transition-colors ${isActive ? 'text-slate-500 hover:text-amber-600 hover:bg-amber-50' : 'text-slate-500 hover:text-emerald-600 hover:bg-emerald-50'}" title="${isActive ? 'Nonaktifkan' : 'Aktifkan'}">
                        <i class="fa-solid ${isActive ? 'fa-user-slash' : 'fa-user-check'} text-xs"></i>
                    </button>
                    <button onclick="bukaResetPassword(${k.id})" class="p-2 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Reset Password">
                        <i class="fa-solid fa-key text-xs"></i>
                    </button>
                    <button onclick="hapusUser(${k.id})" class="p-2 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus User">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filterKasirTabel() {
    const q = document.getElementById('cariKasir').value.toLowerCase().trim();
    const filtered = daftarKasir.filter(k =>
        k.nama.toLowerCase().includes(q) || k.username.toLowerCase().includes(q)
    );
    renderKasir(filtered);
}

// --- MODAL USER ---
function bukaModalUser(id = null) {
    document.getElementById('formUser').reset();
    document.getElementById('userId').value = '';
    document.getElementById('pesanUser').classList.add('hidden');

    const passwordWrapper = document.getElementById('wrapperPasswordUser');
    const passwordInput = document.getElementById('userPassword');

    if (id !== null) {
        const u = daftarKasir.find(x => x.id == id);
        if (!u) return;
        document.getElementById('judulModalUser').innerText = 'Edit User: ' + u.nama;
        document.getElementById('userId').value = u.id;
        document.getElementById('userNama').value = u.nama;
        document.getElementById('userUsername').value = u.username;
        document.getElementById('userRole').value = u.role;
        passwordWrapper.classList.add('hidden');
        passwordInput.removeAttribute('required');
        document.getElementById('btnSubmitUserTeks').innerText = 'Simpan Perubahan';
    } else {
        document.getElementById('judulModalUser').innerText = 'Tambah Kasir Baru';
        passwordWrapper.classList.remove('hidden');
        passwordInput.setAttribute('required', 'required');
        document.getElementById('btnSubmitUserTeks').innerText = 'Simpan Kasir';
    }
    document.getElementById('modalUser').classList.remove('hidden');
}

function editUser(id) { bukaModalUser(id); }
function tutupModalUser() { document.getElementById('modalUser').classList.add('hidden'); }

function togglePasswordVisibility() {
    const inp = document.getElementById('userPassword');
    const icon = document.getElementById('iconPassword');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

document.getElementById('formUser').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitUser');
    const btnTeks = document.getElementById('btnSubmitUserTeks');
    const pesanEl = document.getElementById('pesanUser');
    pesanEl.classList.add('hidden');

    const id = document.getElementById('userId').value;
    const isEdit = !!id;

    btn.disabled = true;
    const originalText = btnTeks.innerText;
    btnTeks.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Menyimpan...';

    try {
        const body = new FormData();
        body.append('nama', document.getElementById('userNama').value.trim());
        body.append('username', document.getElementById('userUsername').value.trim());
        body.append('role', document.getElementById('userRole').value);
        if (isEdit) {
            body.append('id', id);
        } else {
            body.append('password', document.getElementById('userPassword').value);
        }

        const r = await fetch(API + 'user&action=' + (isEdit ? 'update' : 'create'), { method: 'POST', body }).then(res => res.json());
        if (r.success) {
            tutupModalUser();
            tampilkanToast(r.message, 'sukses');
            muatKasir();
        } else {
            pesanEl.innerText = r.message;
            pesanEl.classList.remove('hidden');
            tampilkanToast(r.message, 'error');
        }
    } catch (err) {
        pesanEl.innerText = 'Gagal terhubung ke server.';
        pesanEl.classList.remove('hidden');
        tampilkanToast('Gagal terhubung ke server.', 'error');
    } finally {
        btn.disabled = false;
        btnTeks.innerText = originalText;
    }
});

async function toggleKasirStatus(id, newStatus) {
    const k = daftarKasir.find(x => x.id == id);
    const nama = k ? k.nama : 'kasir ini';
    const label = newStatus ? 'mengaktifkan' : 'menonaktifkan';
    const labelDesc = newStatus
        ? `Kasir "${nama}" akan bisa login kembali.`
        : `Kasir "${nama}" tidak akan bisa login sampai diaktifkan kembali.`;

    const yakin = await mintaKonfirmasi(`${newStatus ? 'Aktifkan' : 'Nonaktifkan'} Kasir?`, labelDesc);
    if (!yakin) return;

    const body = new FormData();
    body.append('id', id);
    body.append('is_active', newStatus);
    try {
        const r = await fetch(API + 'user&action=toggle', { method: 'POST', body }).then(res => res.json());
        if (r.success) {
            tampilkanToast(r.message, 'sukses');
            muatKasir();
        } else {
            tampilkanToast(r.message, 'error');
        }
    } catch (err) {
        tampilkanToast('Gagal terhubung ke server.', 'error');
    }
}

async function hapusUser(id) {
    const k = daftarKasir.find(x => x.id == id);
    const nama = k ? k.nama : 'user ini';
    const yakin = await mintaKonfirmasi('Hapus User?', `Yakin ingin menghapus "${nama}"? User yang sudah memiliki transaksi tidak bisa dihapus (nonaktifkan saja).`);
    if (!yakin) return;

    const body = new FormData();
    body.append('id', id);
    try {
        const r = await fetch(API + 'user&action=delete', { method: 'POST', body }).then(res => res.json());
        if (r.success) {
            tampilkanToast(r.message, 'sukses');
            muatKasir();
        } else {
            tampilkanToast(r.message, 'error');
        }
    } catch (err) {
        tampilkanToast('Gagal terhubung ke server.', 'error');
    }
}

// --- RESET PASSWORD ---
function bukaResetPassword(id) {
    const k = daftarKasir.find(x => x.id == id);
    if (!k) return;
    document.getElementById('formResetPassword').reset();
    document.getElementById('pesanResetPassword').classList.add('hidden');
    document.getElementById('resetPasswordUserId').value = id;
    document.getElementById('resetPasswordNama').innerText = k.nama;
    document.getElementById('judulResetPassword').innerText = 'Reset Password: ' + k.nama;
    document.getElementById('modalResetPassword').classList.remove('hidden');
}
function tutupModalResetPassword() { document.getElementById('modalResetPassword').classList.add('hidden'); }

document.getElementById('formResetPassword').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnResetPassword');
    const btnTeks = document.getElementById('btnResetPasswordTeks');
    const pesanEl = document.getElementById('pesanResetPassword');
    pesanEl.classList.add('hidden');

    btn.disabled = true;
    const originalText = btnTeks.innerText;
    btnTeks.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Mereset...';

    try {
        const body = new FormData();
        body.append('id', document.getElementById('resetPasswordUserId').value);
        body.append('new_password', document.getElementById('resetNewPassword').value);

        const r = await fetch(API + 'user&action=reset-password', { method: 'POST', body }).then(res => res.json());
        if (r.success) {
            tutupModalResetPassword();
            tampilkanToast(r.message, 'sukses');
        } else {
            pesanEl.innerText = r.message;
            pesanEl.classList.remove('hidden');
            tampilkanToast(r.message, 'error');
        }
    } catch (err) {
        pesanEl.innerText = 'Gagal terhubung ke server.';
        pesanEl.classList.remove('hidden');
        tampilkanToast('Gagal terhubung ke server.', 'error');
    } finally {
        btn.disabled = false;
        btnTeks.innerText = originalText;
    }
});

// Inisialisasi awal
bukaTab('menu');
muatMenu();
muatKategori();
muatRiwayat();
</script>
</body>
</html>