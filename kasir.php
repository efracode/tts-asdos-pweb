<?php
// Ini buat nampilin halaman kasir, pembayaran, dan struk.

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir POS - Kasir Restoran</title>
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
<body class="bg-slate-100/90 text-slate-800 min-h-screen flex flex-col">

<!-- Ini buat nampilin pesan berhasil atau gagal. -->
<div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 pointer-events-none"></div>

<!-- Ini buat nampilin navigasi dan akun yang sedang masuk. -->
<nav class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 px-4 py-3 shadow-sm backdrop-blur-md lg:px-8">
    <div class="mx-auto flex w-full max-w-[1600px] items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3 sm:gap-3.5">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-400 shadow-md shadow-orange-500/25 ring-1 ring-orange-400/30">
                <span class="app-icon text-2xl" data-icon="register" aria-hidden="true"></span>
            </div>
            <div class="min-w-0">
                <span class="block truncate text-base font-extrabold leading-tight tracking-tight text-slate-900 sm:text-lg">Kasir Restoran</span>
                <span class="mt-1 hidden items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-700 sm:inline-flex sm:text-[11px]">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Point of Sale
                </span>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-slate-50/80 px-2 py-1.5 sm:px-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-xs font-extrabold text-white shadow-sm shadow-emerald-700/20">
                    <?= htmlspecialchars(strtoupper(substr($user['nama'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="hidden min-w-0 text-left leading-tight sm:block">
                    <span class="block max-w-40 truncate text-xs font-bold text-slate-800"><?= htmlspecialchars($user['nama']) ?></span>
                    <span class="mt-1 flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Kasir aktif
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

<!-- Ini buat nampilin katalog dan keranjang kasir. -->
<main class="flex-1 p-4 lg:p-6 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Ini buat mencari dan memilih menu. -->
    <section class="lg:col-span-2 space-y-4">
        <!-- Ini buat mencari menu dan memilih kategori. -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
            <div class="relative">
                <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs" data-icon="search" aria-hidden="true"></span>
                <input type="text" id="cariMenu" oninput="cariMenuKasir()" placeholder="Cari nama menu makanan, minuman..."
                       class="w-full pl-9 pr-8 py-2.5 text-xs lg:text-sm bg-slate-50 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500 transition-all">
                <button id="btnClearSearch" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                    <span class="app-icon" data-icon="close-circle" aria-hidden="true"></span>
                </button>
            </div>
            <!-- Ini buat filter menu berdasarkan kategori. -->
            <div id="filterKategori" class="flex flex-wrap gap-2 pt-1"></div>
        </div>

        <!-- Ini buat nampilin menu yang tersedia. -->
        <div id="gridMenu" class="grid grid-cols-2 sm:grid-cols-3 gap-3.5 lg:gap-4"></div>
    </section>

    <!-- Ini buat melihat item pesanan dan totalnya. -->
    <aside class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 h-fit lg:sticky lg:top-20 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2 text-slate-900">
                    <span class="app-icon text-orange-500" data-icon="cart" aria-hidden="true"></span>
                    <h2 class="font-extrabold text-sm lg:text-base">Pesanan Pelanggan</h2>
                </div>
                <button onclick="kosongkanKeranjang()" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-1 rounded-md transition-colors" title="Hapus semua">
                    Reset
                </button>
            </div>

            <!-- Ini buat nampilin item yang dipilih. -->
            <div id="isiKeranjang" class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1"></div>

            <!-- Ini buat nampilin total dan membuka pembayaran. -->
            <div class="border-t border-slate-100 pt-4 mt-4 space-y-3">
                <div class="flex justify-between items-center text-xs text-slate-500 font-medium">
                    <span>Jumlah Item</span>
                    <span id="totalItemCount">0 item</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs uppercase tracking-wider font-bold text-slate-700">Total Tagihan</span>
                    <span id="totalBelanja" class="text-xl font-extrabold text-orange-600">Rp 0</span>
                </div>

                <button id="tombolBayar" onclick="bukaBayar()" disabled
                        class="w-full rounded-xl bg-orange-500 hover:bg-orange-600 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold py-3 text-sm transition-all shadow-sm shadow-orange-500/25 flex items-center justify-center gap-2">
                    <span class="app-icon" data-icon="register" aria-hidden="true"></span>Lanjutkan Pembayaran
                </button>
            </div>
        </div>
    </aside>

</main>

<!-- Ini buat nerima pembayaran dan ngitung kembalian. -->
<div id="modalBayar" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                <span class="app-icon text-orange-500" data-icon="register" aria-hidden="true"></span>Pembayaran Kasir
            </h3>
            <button onclick="tutupBayar()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <span class="app-icon" data-icon="close" aria-hidden="true"></span>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <!-- Ini buat nampilin total tagihan. -->
            <div class="bg-orange-50/70 border border-orange-200/60 rounded-xl p-4 text-center">
                <span class="text-xs font-semibold text-orange-800 uppercase tracking-wider block mb-0.5">Total Tagihan Belanja</span>
                <span id="infoTotal" class="text-2xl lg:text-3xl font-extrabold text-orange-600 block">Rp 0</span>
            </div>

            <!-- Ini buat memasukkan nominal pembayaran. -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nominal Uang Diterima (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">Rp</span>
                    <input type="number" id="inputBayar" oninput="hitungKembalian()" min="0" placeholder="0"
                           class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-2.5 text-base font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
                </div>
            </div>

            <!-- Ini buat memilih nominal pembayaran dengan cepat. -->
            <div class="space-y-1.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pilihan Cepat Nominal</span>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="setNominalPas()" class="py-2 px-2.5 rounded-lg border border-orange-200 bg-orange-50 text-orange-700 text-xs font-bold hover:bg-orange-100 transition-colors">
                        Uang Pas
                    </button>
                    <button type="button" onclick="setNominal(20000)" class="py-2 px-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold">
                        Rp 20.000
                    </button>
                    <button type="button" onclick="setNominal(50000)" class="py-2 px-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold">
                        Rp 50.000
                    </button>
                    <button type="button" onclick="setNominal(100000)" class="py-2 px-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold">
                        Rp 100.000
                    </button>
                    <button type="button" onclick="tambahNominal(10000)" class="py-2 px-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold">
                        +10 Ribu
                    </button>
                    <button type="button" onclick="tambahNominal(50000)" class="py-2 px-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold">
                        +50 Ribu
                    </button>
                </div>
            </div>

            <!-- Ini buat nampilin status kembalian. -->
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-600 block">Status Kembalian</span>
                    <span id="statusKembalianTeks" class="text-[11px] text-slate-400">Masukkan uang pembayaran</span>
                </div>
                <span id="infoKembalian" class="text-lg font-extrabold text-slate-800">Rp 0</span>
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="tutupBayar()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs lg:text-sm font-semibold transition-all">Batal</button>
                <button type="button" id="btnProsesBayar" onclick="prosesBayar()" class="flex-1 rounded-xl bg-orange-500 hover:bg-orange-600 text-white py-2.5 text-xs lg:text-sm font-bold transition-all shadow-sm shadow-orange-500/25 flex items-center justify-center gap-2">
                    <span id="btnProsesBayarTeks">Konfirmasi & Simpan</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Ini buat nampilin dan mencetak struk transaksi. -->
<div id="modalKonfirmasiLogout" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[60] p-4" role="dialog" aria-modal="true" aria-labelledby="judulKonfirmasiLogout">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center border border-slate-200">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3">
            <span class="app-icon" data-icon="logout" aria-hidden="true"></span>
        </div>
        <h2 id="judulKonfirmasiLogout" class="font-extrabold text-base text-slate-900 mb-1">Yakin ingin logout?</h2>
        <p class="text-xs text-slate-500 mb-5 leading-relaxed">Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login.</p>
        <div class="flex gap-2.5">
            <button type="button" onclick="tutupKonfirmasiLogout(false)" class="flex-1 rounded-xl border border-slate-200 text-slate-600 py-2.5 text-xs font-semibold hover:bg-slate-100">Batal</button>
            <button type="button" onclick="tutupKonfirmasiLogout(true)" class="flex-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white py-2.5 text-xs font-bold">Ya, Logout</button>
        </div>
    </div>
</div>

<div id="modalStruk" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="p-6">
            <div id="areaStruk"></div>
            <div class="flex gap-2.5 mt-6 pt-4 border-t border-slate-100">
                <button onclick="tutupStruk()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs font-semibold">Pesanan Baru</button>
                <button onclick="window.print()" class="flex-1 rounded-xl bg-slate-900 hover:bg-black text-white py-2.5 text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                    <span class="app-icon" data-icon="print" aria-hidden="true"></span>Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<script>window.NAMA_KASIR = <?= json_encode($user['nama'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;</script>
<script src="assets/js/kasir.js"></script>
</body>
</html>
