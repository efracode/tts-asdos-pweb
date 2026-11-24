<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Backoffice - Kasir Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/icons.css?v=5">
    <link rel="stylesheet" href="assets/css/app.css?v=2">
    <script src="assets/js/admin.js?v=5" defer></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen flex flex-col lg:flex-row">
    <a href="#main-content" class="skip-link">Lewati ke area kerja admin</a>

    <div id="toastContainer" aria-live="polite" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- Backdrop drawer sidebar untuk layar kecil (< lg) -->
    <div id="sidebarBackdrop" class="hidden pos-backdrop fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>

    <!-- Sidebar Navigasi Moka/Kasirku-inspired -->
    <aside id="adminSidebar" aria-label="Navigasi backoffice admin"
           class="fixed inset-y-0 left-0 z-50 lg:sticky lg:top-0 lg:h-screen w-64 bg-white border-r border-slate-200 flex flex-col justify-between -translate-x-full lg:translate-x-0 shrink-0 overflow-hidden">
        <div class="flex flex-col min-h-0 flex-1">
            <!-- Header Brand Sidebar -->
            <div id="sidebarHeader" class="px-4 py-3.5 border-b border-slate-200 flex items-center justify-between gap-2">
                <div id="sidebarBrandBox" class="flex items-center gap-3 min-w-0">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-700 text-white">
                        <span class="app-icon text-xl" data-icon="register" aria-hidden="true"></span>
                    </div>
                    <div class="sidebar-label min-w-0">
                        <span class="block truncate text-sm font-extrabold text-slate-900">Kasir Restoran</span>
                        <span class="block text-xs font-semibold text-slate-600">Backoffice Admin</span>
                    </div>
                </div>
                <button type="button" id="btnCollapseSidebar" aria-expanded="true" aria-label="Ciutkan atau lebarkan sidebar"
                        class="hidden lg:inline-flex items-center justify-center w-11 h-11 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 shrink-0 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </button>
                <button type="button" id="btnTutupSidebarMobile" aria-label="Tutup menu navigasi"
                        class="lg:hidden inline-flex items-center justify-center min-w-[44px] min-h-[44px] rounded-lg text-slate-600 hover:bg-slate-100">
                    <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                </button>
            </div>

            <!-- Menu Navigasi Terkelompok -->
            <nav class="p-3 space-y-5 overflow-y-auto pos-scroll flex-1" aria-label="Modul manajemen">
                <div>
                    <p class="sidebar-group-title px-3 mb-2 text-xs font-bold uppercase tracking-wider text-slate-600">Katalog</p>
                    <div class="space-y-1">
                        <button type="button" id="tab-menu" data-tab="menu" aria-current="page" title="Kelola Menu"
                                class="nav-tab-btn w-full min-h-[44px] px-3 py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center gap-3 bg-orange-700 text-white">
                            <span class="app-icon text-base shrink-0" data-icon="burger" aria-hidden="true"></span>
                            <span class="sidebar-label truncate">Kelola Menu</span>
                        </button>
                        <button type="button" id="tab-kategori" data-tab="kategori" title="Kategori"
                                class="nav-tab-btn w-full min-h-[44px] px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-3 text-slate-700 hover:bg-slate-100">
                            <span class="app-icon text-base shrink-0" data-icon="tags" aria-hidden="true"></span>
                            <span class="sidebar-label truncate">Kategori</span>
                        </button>
                    </div>
                </div>

                <div>
                    <p class="sidebar-group-title px-3 mb-2 text-xs font-bold uppercase tracking-wider text-slate-600">Operasional</p>
                    <div class="space-y-1">
                        <button type="button" id="tab-riwayat" data-tab="riwayat" title="Riwayat Penjualan"
                                class="nav-tab-btn w-full min-h-[44px] px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-3 text-slate-700 hover:bg-slate-100">
                            <span class="app-icon text-base shrink-0" data-icon="chart" aria-hidden="true"></span>
                            <span class="sidebar-label truncate">Riwayat Penjualan</span>
                        </button>
                        <button type="button" id="tab-kasir" data-tab="kasir" title="Kelola Kasir"
                                class="nav-tab-btn w-full min-h-[44px] px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-3 text-slate-700 hover:bg-slate-100">
                            <span class="app-icon text-base shrink-0" data-icon="users-gear" aria-hidden="true"></span>
                            <span class="sidebar-label truncate">Kelola Kasir</span>
                        </button>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Footer Profil Admin & Logout -->
        <div class="p-3 border-t border-slate-200 space-y-2 bg-slate-50">
            <div id="sidebarUserBox" class="flex items-center gap-2.5 px-2 py-1.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-xs font-extrabold text-white" aria-hidden="true">
                    <?= htmlspecialchars(strtoupper(substr($user['nama'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="sidebar-label min-w-0">
                    <span class="block truncate text-xs font-bold text-slate-900"><?= htmlspecialchars($user['nama']) ?></span>
                    <span class="block text-xs text-slate-600">Administrator</span>
                </div>
            </div>
            <button type="button" id="btnLogoutAdmin" title="Keluar"
                    class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 rounded-lg border border-rose-300 bg-white hover:bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition-colors">
                <span class="app-icon" data-icon="logout" aria-hidden="true"></span>
                <span class="sidebar-label">Keluar</span>
            </button>
        </div>
    </aside>

    <!-- Konten Workspace Utama -->
    <div class="flex-1 min-w-0 flex flex-col">
        <!-- Header Workspace & Trigger Mobile -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 lg:px-8 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" id="btnMobileMenu" aria-expanded="false" aria-controls="adminSidebar"
                        class="lg:hidden min-h-[44px] inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 px-3 py-2 text-xs font-bold text-slate-800">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                    <span>Menu</span>
                </button>
                <div class="min-w-0">
                    <h1 id="judulHalaman" class="text-base sm:text-lg font-extrabold text-slate-900 truncate">Kelola Menu</h1>
                    <p id="subjudulHalaman" class="text-xs text-slate-600 truncate">Atur daftar produk, harga, stok porsi, dan foto menu restoran.</p>
                </div>
            </div>
        </header>

        <main id="main-content" tabindex="-1" class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
            <!-- Ringkasan Data Nyata dari Database -->
            <section aria-label="Ringkasan operasional" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 lg:gap-4">
                <div class="sm:col-span-2 xl:col-span-1 bg-slate-900 text-white rounded-xl p-4 border border-slate-800 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-xs font-semibold text-slate-300 block">Omset Penjualan</span>
                        <span id="statOmset" class="text-xl lg:text-2xl font-extrabold text-white tabular-nums truncate block mt-0.5">Rp 0</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-orange-700 text-white flex items-center justify-center text-xl shrink-0" aria-hidden="true">
                        <span class="app-icon" data-icon="wallet"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-slate-200 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-lg bg-orange-50 text-orange-700 border border-orange-200 flex items-center justify-center text-xl shrink-0" aria-hidden="true">
                        <span class="app-icon" data-icon="utensils"></span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs font-semibold text-slate-600 block">Total Menu</span>
                        <span id="statMenu" class="text-xl font-extrabold text-slate-900 tabular-nums block">0</span>
                        <span id="statStokMenipis" class="text-xs font-semibold text-slate-600 block">Memeriksa stok...</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-slate-200 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center text-xl shrink-0" aria-hidden="true">
                        <span class="app-icon" data-icon="tags"></span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-600 block">Kategori Aktif</span>
                        <span id="statKategori" class="text-xl font-extrabold text-slate-900 tabular-nums">0</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-slate-200 flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center text-xl shrink-0" aria-hidden="true">
                        <span class="app-icon" data-icon="receipt"></span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-600 block">Transaksi Selesai</span>
                        <span id="statTransaksi" class="text-xl font-extrabold text-slate-900 tabular-nums">0</span>
                    </div>
                </div>
            </section>

            <!-- Bagian Kelola Menu -->
            <section id="sec-menu" class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex flex-col sm:flex-row gap-2.5 flex-1">
                        <div class="relative flex-1 max-w-sm">
                            <label for="cariMenu" class="sr-only">Cari nama menu atau kategori</label>
                            <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs" data-icon="search" aria-hidden="true"></span>
                            <input type="text" id="cariMenu" placeholder="Cari nama menu atau kategori..."
                                   class="w-full min-h-[44px] pl-9 pr-4 py-2 text-sm bg-white rounded-lg border border-slate-300 focus:border-orange-700">
                        </div>
                        <div>
                            <label for="filterKategoriAdmin" class="sr-only">Filter kategori menu</label>
                            <select id="filterKategoriAdmin"
                                    class="w-full sm:w-48 min-h-[44px] px-3 py-2 text-sm bg-white rounded-lg border border-slate-300 text-slate-800 font-medium focus:border-orange-700">
                                <option value="">Semua Kategori</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" id="btnTambahMenu"
                            class="min-h-[44px] inline-flex items-center justify-center gap-2 bg-orange-700 hover:bg-orange-800 text-white text-sm font-bold px-4 py-2.5 rounded-lg transition-colors shrink-0">
                        <span class="app-icon" data-icon="plus" aria-hidden="true"></span>
                        <span>Tambah Menu Baru</span>
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse min-w-[640px]">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 border-b border-slate-200 uppercase text-xs font-bold tracking-wider">
                                    <th class="py-3.5 px-4">Produk &amp; Foto</th>
                                    <th class="py-3.5 px-4">Kategori</th>
                                    <th class="py-3.5 px-4">Harga</th>
                                    <th class="py-3.5 px-4">Stok</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelMenu" class="divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Bagian Kelola Kategori -->
            <section id="sec-kategori" class="hidden space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <label for="cariKategori" class="sr-only">Cari kategori</label>
                        <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs" data-icon="search" aria-hidden="true"></span>
                        <input type="text" id="cariKategori" placeholder="Cari kategori..."
                               class="w-full min-h-[44px] pl-9 pr-4 py-2 text-sm bg-white rounded-lg border border-slate-300 focus:border-orange-700">
                    </div>
                    <button type="button" id="btnTambahKategori"
                            class="min-h-[44px] inline-flex items-center justify-center gap-2 bg-orange-700 hover:bg-orange-800 text-white text-sm font-bold px-4 py-2.5 rounded-lg transition-colors shrink-0">
                        <span class="app-icon" data-icon="plus" aria-hidden="true"></span>
                        <span>Tambah Kategori</span>
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse min-w-[420px]">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 border-b border-slate-200 uppercase text-xs font-bold tracking-wider">
                                    <th class="py-3.5 px-4 w-20">ID</th>
                                    <th class="py-3.5 px-4">Nama Kategori</th>
                                    <th class="py-3.5 px-4 text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKategori" class="divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Bagian Riwayat Penjualan -->
            <section id="sec-riwayat" class="hidden space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <label for="cariRiwayat" class="sr-only">Cari riwayat transaksi</label>
                        <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs" data-icon="search" aria-hidden="true"></span>
                        <input type="text" id="cariRiwayat" placeholder="Cari No. TRX, Kasir, atau Tanggal..."
                               class="w-full min-h-[44px] pl-9 pr-4 py-2 text-sm bg-white rounded-lg border border-slate-300 focus:border-orange-700">
                    </div>
                    <button type="button" id="btnRefreshRiwayat"
                            class="min-h-[44px] inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-100 text-slate-800 text-sm font-bold px-4 py-2 rounded-lg transition-colors border border-slate-300 shrink-0">
                        <span class="app-icon" data-icon="refresh" aria-hidden="true"></span>
                        <span>Muat Ulang Data</span>
                    </button>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse min-w-[680px]">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 border-b border-slate-200 uppercase text-xs font-bold tracking-wider">
                                    <th class="py-3.5 px-4">No. Transaksi</th>
                                    <th class="py-3.5 px-4">Waktu</th>
                                    <th class="py-3.5 px-4">Kasir</th>
                                    <th class="py-3.5 px-4">Total</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-center">Struk</th>
                                </tr>
                            </thead>
                            <tbody id="tabelRiwayat" class="divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Bagian Kelola Kasir -->
            <section id="sec-kasir" class="hidden space-y-4">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-lg shrink-0" aria-hidden="true">
                            <span class="app-icon" data-icon="users"></span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-600 block">Total Kasir</span>
                            <span id="statTotalKasir" class="text-lg font-extrabold text-slate-900 tabular-nums">0</span>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center justify-center text-lg shrink-0" aria-hidden="true">
                            <span class="app-icon" data-icon="user-check"></span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-600 block">Kasir Aktif</span>
                            <span id="statKasirAktif" class="text-lg font-extrabold text-emerald-700 tabular-nums">0</span>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 flex items-center justify-center text-lg shrink-0" aria-hidden="true">
                            <span class="app-icon" data-icon="user-x"></span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-600 block">Nonaktif</span>
                            <span id="statKasirNonaktif" class="text-lg font-extrabold text-rose-700 tabular-nums">0</span>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-3.5 border border-slate-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-50 text-orange-700 border border-orange-200 flex items-center justify-center text-lg shrink-0" aria-hidden="true">
                            <span class="app-icon" data-icon="register"></span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-600 block">Transaksi Kasir</span>
                            <span id="statTotalTrxKasir" class="text-lg font-extrabold text-slate-900 tabular-nums">0</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <label for="cariKasir" class="sr-only">Cari nama kasir atau username</label>
                        <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs" data-icon="search" aria-hidden="true"></span>
                        <input type="text" id="cariKasir" placeholder="Cari nama kasir atau username..."
                               class="w-full min-h-[44px] pl-9 pr-4 py-2 text-sm bg-white rounded-lg border border-slate-300 focus:border-orange-700">
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" id="btnRefreshKasir"
                                class="min-h-[44px] inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-100 text-slate-800 text-sm font-bold px-3.5 py-2 rounded-lg transition-colors border border-slate-300">
                            <span class="app-icon" data-icon="refresh" aria-hidden="true"></span>
                            <span>Refresh</span>
                        </button>
                        <button type="button" id="btnTambahUser"
                                class="min-h-[44px] inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-black text-white text-sm font-bold px-4 py-2.5 rounded-lg transition-colors">
                            <span class="app-icon" data-icon="user-plus" aria-hidden="true"></span>
                            <span>Tambah Akun</span>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse min-w-[760px]">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 border-b border-slate-200 uppercase text-xs font-bold tracking-wider">
                                    <th class="py-3.5 px-4">Kasir</th>
                                    <th class="py-3.5 px-4">Username</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4">Transaksi</th>
                                    <th class="py-3.5 px-4">Omset</th>
                                    <th class="py-3.5 px-4">Terakhir Aktif</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKasir" class="divide-y divide-slate-200"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Tambah / Edit Menu -->
    <div id="modalMenu" class="hidden pos-modal fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulModalMenu">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between shrink-0">
                <h2 id="judulModalMenu" class="font-extrabold text-base lg:text-lg text-slate-900">Tambah Menu Baru</h2>
                <button type="button" id="btnCloseModalMenu" aria-label="Tutup modal menu"
                        class="min-w-[44px] min-h-[44px] text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg flex items-center justify-center">
                    <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                </button>
            </div>

            <div class="overflow-y-auto pos-scroll p-6 space-y-4">
                <div id="pesanMenu" role="alert" class="hidden rounded-lg bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold p-3"></div>

                <form id="formMenu" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="id" id="menuId">
                    <input type="hidden" name="hapus_gambar" id="menuHapusGambar" value="0">

                    <div>
                        <label for="menuGambar" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Foto Produk (Opsional)</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 rounded-lg border border-slate-300 bg-slate-50 p-3.5">
                            <div id="previewGambarBox" class="w-24 h-24 rounded-lg border border-slate-300 bg-white overflow-hidden shrink-0 flex items-center justify-center">
                                <img id="previewGambarImg" alt="Pratinjau foto menu" width="96" height="96" class="hidden w-24 h-24 object-cover">
                                <div id="previewGambarPlaceholder" class="text-center px-2 text-slate-600">
                                    <svg class="w-7 h-7 mx-auto mb-1 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="16" rx="2" />
                                        <circle cx="8.5" cy="9.5" r="1.5" />
                                        <path d="M21 16l-5-5-4 4-3-3-6 6" />
                                    </svg>
                                    <span class="text-[11px] font-semibold block">Belum ada foto</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 space-y-2 w-full">
                                <input type="file" name="gambar" id="menuGambar" accept="image/jpeg,image/png,image/webp"
                                       aria-describedby="hintMenuGambar"
                                       class="block w-full text-xs text-slate-700 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border file:border-slate-300 file:text-xs file:font-bold file:bg-white file:text-slate-800 hover:file:bg-slate-100">
                                <p id="hintMenuGambar" class="text-xs text-slate-600">Format JPG, PNG, atau WEBP. Maksimal ukuran file 2 MB.</p>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span id="namaFileGambar" class="text-xs font-semibold text-slate-700 truncate max-w-[220px]"></span>
                                    <button type="button" id="btnHapusFotoMenu"
                                            class="hidden min-h-[36px] px-2.5 py-1 rounded-md border border-rose-300 bg-white hover:bg-rose-50 text-xs font-bold text-rose-700">
                                        Hapus Foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="menuNama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Menu <span class="text-rose-700">*</span></label>
                        <input type="text" name="nama_menu" id="menuNama" maxlength="100" required placeholder="Contoh: Nasi Goreng Spesial"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="menuKategori" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori <span class="text-rose-700">*</span></label>
                            <select name="id_kategori" id="menuKategori" required
                                    class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 bg-white focus:border-orange-700"></select>
                        </div>
                        <div>
                            <label for="menuStok" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Stok Porsi <span class="text-rose-700">*</span></label>
                            <input type="number" name="stok" id="menuStok" min="0" max="2147483647" required placeholder="0"
                                   class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 tabular-nums focus:border-orange-700">
                        </div>
                    </div>

                    <div>
                        <label for="menuHarga" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Harga Jual (Rp) <span class="text-rose-700">*</span></label>
                        <input type="number" name="harga" id="menuHarga" min="1" max="2147483647" required placeholder="Contoh: 25000"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 tabular-nums focus:border-orange-700">
                    </div>

                    <div class="flex gap-2.5 pt-3 border-t border-slate-200">
                        <button type="button" id="btnBatalModalMenu" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 text-sm font-bold transition-colors">Batal</button>
                        <button type="submit" id="btnSubmitMenu" class="flex-1 min-h-[44px] rounded-lg bg-orange-700 hover:bg-orange-800 disabled:bg-slate-300 disabled:text-slate-600 text-white py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2">
                            <span id="btnSubmitMenuTeks">Simpan Menu</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Kategori -->
    <div id="modalKategori" class="hidden pos-modal fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulModalKategori">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 id="judulModalKategori" class="font-extrabold text-base text-slate-900">Tambah Kategori</h2>
                <button type="button" id="btnCloseModalKategori" aria-label="Tutup modal kategori"
                        class="min-w-[44px] min-h-[44px] text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg flex items-center justify-center">
                    <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                </button>
            </div>

            <div id="pesanKategori" role="alert" class="hidden mx-6 mt-4 rounded-lg bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold p-3"></div>

            <form id="formKategori" class="p-6 space-y-4">
                <input type="hidden" id="kategoriId">
                <div>
                    <label for="kategoriNama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Kategori <span class="text-rose-700">*</span></label>
                    <input type="text" id="kategoriNama" maxlength="50" required placeholder="Contoh: Aneka Jus"
                           class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700">
                </div>

                <div class="flex gap-2.5 pt-2 border-t border-slate-200">
                    <button type="button" id="btnBatalModalKategori" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 text-sm font-bold transition-colors">Batal</button>
                    <button type="submit" id="btnSubmitKategori" class="flex-1 min-h-[44px] rounded-lg bg-orange-700 hover:bg-orange-800 disabled:bg-slate-300 disabled:text-slate-600 text-white py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2">
                        <span id="btnSubmitKategoriTeks">Simpan Kategori</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Struk -->
    <div id="modalStruk" class="hidden pos-modal fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulModalStrukAdmin">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200">
            <div class="p-6">
                <h2 id="judulModalStrukAdmin" class="sr-only">Detail Struk Transaksi</h2>
                <div id="areaStruk"></div>
                <div data-print-hide="true" class="flex gap-2.5 mt-6 pt-4 border-t border-slate-200">
                    <button type="button" id="btnTutupStrukAdmin" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 text-xs font-bold">Tutup</button>
                    <button type="button" id="btnCetakStrukAdmin" class="flex-1 min-h-[44px] rounded-lg bg-slate-900 hover:bg-black text-white py-2.5 text-xs font-bold flex items-center justify-center gap-1.5">
                        <span class="app-icon" data-icon="print" aria-hidden="true"></span>
                        <span>Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi -->
    <div id="modalKonfirmasi" class="hidden pos-modal fixed inset-0 z-[60] bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulKonfirmasi">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center border border-slate-200">
            <div id="ikonKonfirmasi" class="w-12 h-12 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xl mx-auto mb-3">
                <span class="app-icon" data-icon="warning" aria-hidden="true"></span>
            </div>
            <h2 id="judulKonfirmasi" class="font-extrabold text-base text-slate-900 mb-1">Konfirmasi Hapus</h2>
            <p id="pesanKonfirmasi" class="text-xs text-slate-600 mb-5 leading-relaxed">Apakah Anda yakin ingin menghapus data ini?</p>
            <div class="flex gap-2.5">
                <button type="button" id="btnBatalKonfirmasi" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 py-2.5 text-xs font-bold hover:bg-slate-100">Batal</button>
                <button type="button" id="btnAksiKonfirmasi" class="flex-1 min-h-[44px] rounded-lg bg-rose-700 hover:bg-rose-800 text-white py-2.5 text-xs font-bold">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah / Edit User -->
    <div id="modalUser" class="hidden pos-modal fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulModalUser">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 id="judulModalUser" class="font-extrabold text-base lg:text-lg text-slate-900">Tambah Kasir</h2>
                <button type="button" id="btnCloseModalUser" aria-label="Tutup modal akun user"
                        class="min-w-[44px] min-h-[44px] text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg flex items-center justify-center">
                    <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                </button>
            </div>

            <div id="pesanUser" role="alert" class="hidden mx-6 mt-4 rounded-lg bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold p-3"></div>

            <form id="formUser" class="p-6 space-y-4">
                <input type="hidden" id="userId">

                <div>
                    <label for="userNama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-700">*</span></label>
                    <input type="text" id="userNama" maxlength="100" required placeholder="Nama lengkap pengguna"
                           class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="userUsername" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Username <span class="text-rose-700">*</span></label>
                        <input type="text" id="userUsername" maxlength="50" required placeholder="huruf_angka_saja"
                               aria-describedby="hintUsernameAdmin"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700">
                        <p id="hintUsernameAdmin" class="text-xs text-slate-600 mt-1">Huruf, angka &amp; underscore</p>
                    </div>
                    <div>
                        <label for="userRole" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Role</label>
                        <select id="userRole" class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 bg-white focus:border-orange-700">
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div id="wrapperPasswordUser">
                    <label for="userPassword" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password <span class="text-rose-700">*</span></label>
                    <div class="relative">
                        <input type="password" id="userPassword" minlength="5" placeholder="Minimal 5 karakter"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 pr-12 focus:border-orange-700">
                        <button type="button" id="btnTogglePasswordUser" aria-label="Tampilkan atau sembunyikan password"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center">
                            <span id="iconPassword" class="app-icon" data-icon="eye" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>

                <div class="flex gap-2.5 pt-3 border-t border-slate-200">
                    <button type="button" id="btnBatalModalUser" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 text-sm font-bold transition-colors">Batal</button>
                    <button type="submit" id="btnSubmitUser" class="flex-1 min-h-[44px] rounded-lg bg-slate-900 hover:bg-black disabled:bg-slate-300 disabled:text-slate-600 text-white py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2">
                        <span id="btnSubmitUserTeks">Simpan Kasir</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Reset Password -->
    <div id="modalResetPassword" class="hidden pos-modal fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulResetPassword">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 id="judulResetPassword" class="font-extrabold text-base text-slate-900">Reset Password</h2>
                <button type="button" id="btnCloseResetPassword" aria-label="Tutup modal reset password"
                        class="min-w-[44px] min-h-[44px] text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg flex items-center justify-center">
                    <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                </button>
            </div>

            <div id="pesanResetPassword" role="alert" class="hidden mx-6 mt-4 rounded-lg bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold p-3"></div>

            <form id="formResetPassword" class="p-6 space-y-4">
                <input type="hidden" id="resetPasswordUserId">
                <div class="bg-amber-50 border border-amber-300 rounded-lg p-3 text-xs text-amber-900">
                    <span class="app-icon mr-1" data-icon="warning" aria-hidden="true"></span>
                    Password kasir <strong id="resetPasswordNama"></strong> akan direset. Pastikan untuk menginformasikan password baru kepada kasir.
                </div>
                <div>
                    <label for="resetNewPassword" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Baru <span class="text-rose-700">*</span></label>
                    <input type="password" id="resetNewPassword" minlength="5" required placeholder="Minimal 5 karakter"
                           class="w-full min-h-[44px] rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-orange-700">
                </div>
                <div class="flex gap-2.5 pt-2 border-t border-slate-200">
                    <button type="button" id="btnBatalResetPassword" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 text-sm font-bold transition-colors">Batal</button>
                    <button type="submit" id="btnResetPassword" class="flex-1 min-h-[44px] rounded-lg bg-orange-700 hover:bg-orange-800 text-white py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2">
                        <span id="btnResetPasswordTeks">Reset Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
