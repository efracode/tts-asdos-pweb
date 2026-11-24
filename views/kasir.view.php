<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasir POS - Kasir Restoran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/icons.css?v=5">
    <link rel="stylesheet" href="assets/css/app.css?v=2">
    <script src="assets/js/kasir.js?v=3" defer></script>
</head>
<body data-nama-kasir="<?= htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8') ?>"
      class="bg-slate-100 text-slate-900 min-h-screen lg:h-screen lg:overflow-hidden flex flex-col">
    <a href="#main-content" class="skip-link">Lewati ke katalog menu</a>

    <!-- Toast notifikasi -->
    <div id="toastContainer" aria-live="polite" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- Header POS kompak -->
    <header class="sticky top-0 z-30 shrink-0 border-b border-slate-200 bg-white/95 backdrop-blur-md px-4 lg:px-6 py-2.5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-700 text-white">
                    <span class="app-icon text-xl" data-icon="register" aria-hidden="true"></span>
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-base sm:text-lg font-extrabold leading-tight text-slate-900">Kasir Restoran</h1>
                    <p class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-600" aria-hidden="true"></span>
                        <span>Point of Sale Aktif</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" id="btnRiwayatKasir"
                        class="min-h-[44px] inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 transition-colors">
                    <span class="app-icon" data-icon="receipt" aria-hidden="true"></span>
                    <span class="hidden sm:inline">Riwayat</span>
                </button>

                <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 min-h-[44px]">
                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-700 text-xs font-extrabold text-white shrink-0" aria-hidden="true">
                        <?= htmlspecialchars(strtoupper(substr($user['nama'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="hidden md:block min-w-0 leading-tight">
                        <span class="block max-w-36 truncate text-xs font-bold text-slate-900"><?= htmlspecialchars($user['nama']) ?></span>
                        <span class="block text-[11px] font-medium text-slate-600">Kasir</span>
                    </div>
                </div>

                <button type="button" id="btnLogoutKasir"
                        class="min-h-[44px] inline-flex items-center gap-2 rounded-lg border border-rose-300 bg-white hover:bg-rose-50 px-3 py-2 text-xs font-bold text-rose-700 transition-colors">
                    <span class="app-icon" data-icon="logout" aria-hidden="true"></span>
                    <span>Keluar</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Workspace POS: Katalog kiri + Keranjang kanan -->
    <main id="main-content" tabindex="-1" class="flex-1 min-h-0 flex flex-col lg:flex-row">

        <!-- Kolom Katalog Menu (scroll independen di desktop) -->
        <section aria-label="Katalog menu restoran" class="flex-1 min-w-0 flex flex-col min-h-0">
            <div class="shrink-0 bg-white border-b border-slate-200 px-4 lg:px-6 py-3 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
                    <div class="relative flex-1">
                        <label for="cariMenu" class="sr-only">Cari menu makanan atau minuman</label>
                        <span class="app-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm" data-icon="search" aria-hidden="true"></span>
                        <input type="text" id="cariMenu"
                               placeholder="Cari menu (tekan / untuk fokus)..."
                               class="w-full min-h-[44px] pl-10 pr-11 py-2 text-sm bg-slate-50 text-slate-900 rounded-lg border border-slate-300 focus:bg-white focus:border-orange-700">
                        <button type="button" id="btnClearSearch" aria-label="Bersihkan pencarian"
                                class="hidden absolute right-1 top-1/2 -translate-y-1/2 w-9 h-9 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-200 flex items-center justify-center">
                            <span class="app-icon" data-icon="close-circle" aria-hidden="true"></span>
                        </button>
                    </div>
                    <button type="button" id="btnMuatUlangMenu"
                            class="min-h-[44px] shrink-0 inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 transition-colors">
                        <span class="app-icon" data-icon="refresh" aria-hidden="true"></span>
                        <span>Muat Ulang</span>
                    </button>
                </div>

                <!-- Kategori Tabs -->
                <div id="filterKategori" role="group" aria-label="Filter kategori menu" class="flex flex-wrap gap-2"></div>
            </div>

            <!-- Grid Kartu Menu -->
            <div class="flex-1 lg:overflow-y-auto pos-scroll p-4 lg:p-6 pb-28 lg:pb-6">
                <div id="gridMenu" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4"></div>
            </div>
        </section>

        <!-- Backdrop drawer mobile untuk keranjang -->
        <div id="mobileCartBackdrop" class="hidden pos-backdrop fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>

        <!-- Panel Keranjang / Tiket Pesanan -->
        <aside id="panelKeranjang" aria-label="Keranjang pesanan pelanggan"
               class="hidden lg:flex pos-sheet fixed inset-x-0 bottom-0 z-50 lg:static lg:z-auto w-full lg:w-[390px] xl:w-[420px] shrink-0 max-h-[85dvh] lg:max-h-none bg-white border-t lg:border-t-0 lg:border-l border-slate-200 rounded-t-xl lg:rounded-none flex-col min-h-0 shadow-xl lg:shadow-none">

            <div class="shrink-0 flex items-center justify-between px-4 lg:px-5 py-3.5 border-b border-slate-200 bg-white">
                <div class="flex items-center gap-2">
                    <span class="app-icon text-orange-700 text-lg" data-icon="cart" aria-hidden="true"></span>
                    <h2 class="font-extrabold text-base text-slate-900">Pesanan Pelanggan</h2>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnResetKeranjang" disabled
                            class="min-h-[36px] px-2.5 py-1 rounded-md text-xs font-bold text-rose-700 hover:bg-rose-50 disabled:text-slate-400 disabled:hover:bg-transparent transition-colors">
                        Reset
                    </button>
                    <button type="button" id="btnTutupKeranjangMobile" aria-label="Tutup panel keranjang"
                            class="lg:hidden min-w-[44px] min-h-[44px] rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 flex items-center justify-center">
                        <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <!-- Daftar Item Keranjang -->
            <div id="isiKeranjang" class="flex-1 overflow-y-auto pos-scroll p-4 space-y-2.5"></div>

            <!-- Ringkasan Tagihan & Tombol Bayar -->
            <div class="shrink-0 border-t border-slate-200 bg-slate-50 p-4 lg:p-5 space-y-3">
                <div class="flex justify-between items-center text-xs font-semibold text-slate-600">
                    <span>Jumlah Item</span>
                    <span id="totalItemCount" class="tabular-nums font-bold text-slate-900">0 item</span>
                </div>
                <div class="flex justify-between items-baseline">
                    <span class="text-xs uppercase tracking-wider font-bold text-slate-700">Total Tagihan</span>
                    <span id="totalBelanja" class="text-2xl font-extrabold text-orange-700 tabular-nums">Rp 0</span>
                </div>

                <button type="button" id="tombolBayar" disabled
                        class="w-full min-h-[48px] rounded-lg bg-orange-700 hover:bg-orange-800 active:bg-orange-900 disabled:bg-slate-200 disabled:text-slate-500 text-white font-extrabold py-3 px-4 text-sm transition-colors flex items-center justify-center gap-2">
                    <span class="app-icon" data-icon="register" aria-hidden="true"></span>
                    <span>Lanjutkan Pembayaran</span>
                </button>
            </div>
        </aside>
    </main>

    <!-- Floating Bottom Bar khusus Mobile/Tablet (< lg) -->
    <div id="mobileCartBar" class="lg:hidden fixed inset-x-0 bottom-0 z-30 bg-white border-t border-slate-200 px-4 py-2.5 shadow-xl">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <span id="mobileCartCount" class="block text-xs font-semibold text-slate-600 tabular-nums">0 item dipilih</span>
                <span id="mobileCartTotal" class="block text-base font-extrabold text-orange-700 tabular-nums">Rp 0</span>
            </div>
            <button type="button" id="btnBukaKeranjangMobile"
                    class="min-h-[44px] shrink-0 rounded-lg bg-orange-700 hover:bg-orange-800 text-white font-bold px-4 py-2 text-sm flex items-center gap-2 transition-colors">
                <span class="app-icon" data-icon="cart" aria-hidden="true"></span>
                <span>Lihat Pesanan</span>
            </button>
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div id="modalBayar" class="hidden pos-modal fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulModalBayar">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 id="judulModalBayar" class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                    <span class="app-icon text-orange-700" data-icon="register" aria-hidden="true"></span>
                    <span>Pembayaran Kasir</span>
                </h3>
                <button type="button" id="btnCloseModalBayar" aria-label="Tutup dialog pembayaran"
                        class="min-w-[44px] min-h-[44px] text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg flex items-center justify-center">
                    <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                    <span class="text-xs font-bold text-orange-800 uppercase tracking-wider block mb-0.5">Total Tagihan Belanja</span>
                    <span id="infoTotal" class="text-2xl sm:text-3xl font-extrabold text-orange-700 tabular-nums block">Rp 0</span>
                </div>

                <div>
                    <label for="inputBayar" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nominal Uang Diterima (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-600 text-sm">Rp</span>
                        <input type="number" id="inputBayar" min="0" placeholder="0"
                               class="w-full min-h-[44px] rounded-lg border border-slate-300 pl-10 pr-4 py-2.5 text-base font-extrabold text-slate-900 tabular-nums focus:border-orange-700">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Pilihan Cepat Nominal</span>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" id="btnNominalPas" class="min-h-[44px] py-2 px-2 rounded-lg border border-orange-300 bg-orange-50 text-orange-800 text-xs font-bold hover:bg-orange-100 transition-colors">
                            Uang Pas
                        </button>
                        <button type="button" data-nominal-set="20000" class="btn-nominal-set min-h-[44px] py-2 px-2 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold tabular-nums">
                            Rp 20.000
                        </button>
                        <button type="button" data-nominal-set="50000" class="btn-nominal-set min-h-[44px] py-2 px-2 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold tabular-nums">
                            Rp 50.000
                        </button>
                        <button type="button" data-nominal-set="100000" class="btn-nominal-set min-h-[44px] py-2 px-2 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold tabular-nums">
                            Rp 100.000
                        </button>
                        <button type="button" data-nominal-add="10000" class="btn-nominal-add min-h-[44px] py-2 px-2 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold tabular-nums">
                            +10 Ribu
                        </button>
                        <button type="button" data-nominal-add="50000" class="btn-nominal-add min-h-[44px] py-2 px-2 rounded-lg border border-slate-300 bg-slate-50 hover:bg-slate-100 text-slate-800 text-xs font-bold tabular-nums">
                            +50 Ribu
                        </button>
                    </div>
                </div>

                <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between gap-2">
                    <div>
                        <span class="text-xs font-bold text-slate-700 block">Status Kembalian</span>
                        <span id="statusKembalianTeks" class="text-xs text-slate-600">Masukkan uang pembayaran</span>
                    </div>
                    <span id="infoKembalian" class="text-lg font-extrabold text-slate-900 tabular-nums">Rp 0</span>
                </div>

                <div class="flex gap-2.5 pt-2">
                    <button type="button" id="btnBatalBayar" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 text-sm font-bold transition-colors">Batal</button>
                    <button type="button" id="btnProsesBayar" class="flex-1 min-h-[44px] rounded-lg bg-orange-700 hover:bg-orange-800 disabled:bg-slate-300 disabled:text-slate-600 text-white py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2">
                        <span id="btnProsesBayarTeks">Konfirmasi &amp; Simpan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Transaksi Kasir -->
    <div id="modalRiwayatKasir" class="hidden pos-modal fixed inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulRiwayatKasir">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between shrink-0">
                <h3 id="judulRiwayatKasir" class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                    <span class="app-icon text-orange-700" data-icon="receipt" aria-hidden="true"></span>
                    <span>Riwayat Transaksi Terbaru</span>
                </h3>
                <button type="button" id="btnCloseRiwayatKasir" aria-label="Tutup riwayat transaksi"
                        class="min-w-[44px] min-h-[44px] text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg flex items-center justify-center">
                    <span class="app-icon" data-icon="close" aria-hidden="true"></span>
                </button>
            </div>
            <div id="isiRiwayatKasir" class="flex-1 overflow-y-auto pos-scroll p-4 sm:p-5"></div>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <div id="modalKonfirmasiLogout" class="hidden pos-modal fixed inset-0 z-[60] bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulKonfirmasiLogout">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center border border-slate-200">
            <div class="w-12 h-12 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xl mx-auto mb-3">
                <span class="app-icon" data-icon="logout" aria-hidden="true"></span>
            </div>
            <h3 id="judulKonfirmasiLogout" class="font-extrabold text-base text-slate-900 mb-1">Yakin ingin keluar?</h3>
            <p class="text-xs text-slate-600 mb-5 leading-relaxed">Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login.</p>
            <div class="flex gap-2.5">
                <button type="button" id="btnBatalLogoutKasir" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 py-2.5 text-xs font-bold hover:bg-slate-100">Batal</button>
                <button type="button" id="btnYaLogoutKasir" class="flex-1 min-h-[44px] rounded-lg bg-rose-700 hover:bg-rose-800 text-white py-2.5 text-xs font-bold">Ya, Keluar</button>
            </div>
        </div>
    </div>

    <!-- Modal Struk Thermal 80mm -->
    <div id="modalStruk" class="hidden pos-modal fixed inset-0 z-[55] bg-slate-900/60 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="judulModalStruk">
        <div class="pos-modal-panel bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200">
            <div class="p-6">
                <h3 id="judulModalStruk" class="sr-only">Struk Transaksi</h3>
                <div id="areaStruk"></div>
                <div data-print-hide="true" class="flex gap-2.5 mt-6 pt-4 border-t border-slate-200">
                    <button type="button" id="btnTutupStrukKasir" class="flex-1 min-h-[44px] rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 py-2.5 text-xs font-bold">Pesanan Baru</button>
                    <button type="button" id="btnCetakStrukKasir" class="flex-1 min-h-[44px] rounded-lg bg-slate-900 hover:bg-black text-white py-2.5 text-xs font-bold flex items-center justify-center gap-1.5">
                        <span class="app-icon" data-icon="print" aria-hidden="true"></span>
                        <span>Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
