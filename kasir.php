<?php
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
<body class="bg-slate-100/90 text-slate-800 min-h-screen flex flex-col">

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 pointer-events-none"></div>

<!-- Top Navbar -->
<nav class="bg-white border-b border-slate-200/80 px-4 lg:px-8 py-3.5 flex items-center justify-between sticky top-0 z-40 shadow-sm backdrop-blur-md bg-white/95">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-orange-500 to-amber-400 flex items-center justify-center text-white shadow-md shadow-orange-500/20">
            <i class="fa-solid fa-cash-register text-lg"></i>
        </div>
        <div>
            <span class="font-extrabold text-base lg:text-lg text-slate-900 tracking-tight block leading-tight">Kasir Restoran</span>
            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Point of Sale (POS)</span>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100/80 border border-slate-200/60">
            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div class="text-left leading-tight">
                <span class="text-xs font-bold text-slate-700 block"><?= htmlspecialchars($user['nama']) ?></span>
                <span class="text-[10px] font-medium text-emerald-600 uppercase tracking-wide">Kasir Aktif</span>
            </div>
        </div>
        <button onclick="logout()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 hover:bg-rose-50 px-3 py-2 rounded-lg transition-colors border border-transparent hover:border-rose-200">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span class="hidden sm:inline">Logout</span>
        </button>
    </div>
</nav>

<!-- Main POS Layout -->
<main class="flex-1 p-4 lg:p-6 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Katalog Menu Section (Kiri 2 Kolom) -->
    <section class="lg:col-span-2 space-y-4">
        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="cariMenu" oninput="cariMenuKasir()" placeholder="Cari nama menu makanan, minuman..."
                       class="w-full pl-9 pr-8 py-2.5 text-xs lg:text-sm bg-slate-50 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500 transition-all">
                <button id="btnClearSearch" onclick="clearSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                    <i class="fa-solid fa-circle-xmark"></i>
                </button>
            </div>
            <!-- Kategori Filter Pills -->
            <div id="filterKategori" class="flex flex-wrap gap-2 pt-1"></div>
        </div>

        <!-- Grid Kartu Menu -->
        <div id="gridMenu" class="grid grid-cols-2 sm:grid-cols-3 gap-3.5 lg:gap-4"></div>
    </section>

    <!-- Keranjang Belanja Section (Kanan 1 Kolom Sticky) -->
    <aside class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 h-fit lg:sticky lg:top-20 flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2 text-slate-900">
                    <i class="fa-solid fa-cart-shopping text-orange-500"></i>
                    <h2 class="font-extrabold text-sm lg:text-base">Pesanan Pelanggan</h2>
                </div>
                <button onclick="kosongkanKeranjang()" class="text-[11px] font-semibold text-rose-500 hover:text-rose-600 hover:bg-rose-50 px-2 py-1 rounded-md transition-colors" title="Hapus semua">
                    Reset
                </button>
            </div>

            <!-- List Item Keranjang -->
            <div id="isiKeranjang" class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1"></div>

            <!-- Ringkasan Total & Tombol Bayar -->
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
                    <i class="fa-solid fa-cash-register"></i>Lanjutkan Pembayaran
                </button>
            </div>
        </div>
    </aside>

</main>

<!-- MODAL PEMBAYARAN DENGAN NOMINAL CEPAT -->
<div id="modalBayar" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 animate-toast">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-extrabold text-base text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cash-register text-orange-500"></i>Pembayaran Kasir
            </h3>
            <button onclick="tutupBayar()" class="text-slate-400 hover:text-slate-600 w-8 h-8 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <!-- Box Total -->
            <div class="bg-orange-50/70 border border-orange-200/60 rounded-xl p-4 text-center">
                <span class="text-xs font-semibold text-orange-800 uppercase tracking-wider block mb-0.5">Total Tagihan Belanja</span>
                <span id="infoTotal" class="text-2xl lg:text-3xl font-extrabold text-orange-600 block">Rp 0</span>
            </div>

            <!-- Input Uang Diterima -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nominal Uang Diterima (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">Rp</span>
                    <input type="number" id="inputBayar" oninput="hitungKembalian()" min="0" placeholder="0"
                           class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-2.5 text-base font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-orange-500/30 focus:border-orange-500">
                </div>
            </div>

            <!-- Tombol Nominal Cepat -->
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

            <!-- Box Kembalian -->
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

<!-- MODAL STRUK DIGITAL & CETAK -->
<div id="modalStruk" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200 animate-toast">
        <div class="p-6">
            <div id="areaStruk"></div>
            <div class="flex gap-2.5 mt-6 pt-4 border-t border-slate-100">
                <button onclick="tutupStruk()" class="flex-1 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 py-2.5 text-xs font-semibold">Pesanan Baru</button>
                <button onclick="window.print()" class="flex-1 rounded-xl bg-slate-900 hover:bg-black text-white py-2.5 text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-print"></i>Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const API = 'index.php?route=';
const NAMA_KASIR = <?= json_encode($user['nama']) ?>;

let daftarMenu = [], daftarKategori = [], keranjang = [], filterKategoriDipilih = null;

const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

// --- SISTEM TOAST NOTIFICATION ---
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

// --- FETCH DATA ---
async function ambilData() {
    try {
        const [kat, menu] = await Promise.all([
            fetch(API + 'kategori&action=list').then(r => r.json()),
            fetch(API + 'menu&action=list').then(r => r.json())
        ]);
        daftarKategori = kat.data || [];
        daftarMenu = menu.data || [];
        renderFilterKategori();
        renderMenu();
    } catch (err) {
        tampilkanToast('Gagal memuat katalog menu dari server.', 'error');
    }
}

// --- RENDER FILTER KATEGORI ---
function renderFilterKategori() {
    const tombol = [{ id: null, nama_kategori: 'Semua Menu' }, ...daftarKategori];
    document.getElementById('filterKategori').innerHTML = tombol.map(k => `
        <button onclick="pilihKategori(${k.id})"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all ${filterKategoriDipilih === k.id
                ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200/80'}">
            ${k.nama_kategori}
        </button>`).join('');
}

function pilihKategori(id) {
    filterKategoriDipilih = id;
    renderFilterKategori();
    renderMenu();
}

// --- RENDER KATALOG MENU ---
function renderMenu() {
    const q = document.getElementById('cariMenu').value.toLowerCase().trim();
    let data = daftarMenu;

    if (filterKategoriDipilih !== null) {
        data = data.filter(m => m.id_kategori == filterKategoriDipilih);
    }
    if (q !== '') {
        data = data.filter(m => m.nama_menu.toLowerCase().includes(q) || (m.nama_kategori && m.nama_kategori.toLowerCase().includes(q)));
    }

    const grid = document.getElementById('gridMenu');
    if (!data.length) {
        grid.innerHTML = `
            <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200/80">
                <i class="fa-solid fa-utensils text-4xl mb-2 opacity-30 block"></i>
                <p class="text-xs lg:text-sm font-medium">Tidak ada menu yang sesuai.</p>
            </div>`;
        return;
    }

    grid.innerHTML = data.map(m => {
        const habis = m.stok <= 0;
        return `
        <button onclick="tambahKeranjang(${m.id})" ${habis ? 'disabled' : ''}
            class="bg-white rounded-2xl p-3.5 text-left border border-slate-200/80 shadow-sm hover:shadow-md hover:border-orange-300 transition-all flex flex-col justify-between group disabled:opacity-50 disabled:pointer-events-none relative overflow-hidden">
            <div>
                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-md text-[10px] font-bold ${habis ? 'bg-rose-600 text-white' : (m.stok <= 5 ? 'bg-amber-500 text-white' : 'bg-slate-900/75 text-white backdrop-blur-xs')}">
                        ${habis ? 'Habis' : 'Stok ' + m.stok}
                    </span>
                </div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">${m.nama_kategori || 'Menu'}</span>
                <p class="font-extrabold text-xs lg:text-sm text-slate-900 group-hover:text-orange-600 transition-colors line-clamp-1">${m.nama_menu}</p>
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100">
                <span class="text-xs lg:text-sm font-extrabold text-orange-600">${rupiah(m.harga)}</span>
                <span class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-xs group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-plus"></i>
                </span>
            </div>
        </button>`;
    }).join('');
}

function cariMenuKasir() {
    const val = document.getElementById('cariMenu').value.trim();
    document.getElementById('btnClearSearch').classList.toggle('hidden', val === '');
    renderMenu();
}

function clearSearch() {
    document.getElementById('cariMenu').value = '';
    document.getElementById('btnClearSearch').classList.add('hidden');
    renderMenu();
}

// --- KERANJANG BELANJA ---
function tambahKeranjang(id) {
    const menu = daftarMenu.find(m => m.id == id);
    if (!menu) return;

    const item = keranjang.find(k => k.id == id);
    const qtySaatIni = item ? item.qty : 0;

    if (qtySaatIni >= Number(menu.stok)) {
        tampilkanToast(`Stok untuk "${menu.nama_menu}" tidak mencukupi (sisa ${menu.stok})!`, 'error');
        return;
    }

    if (item) {
        item.qty++;
    } else {
        keranjang.push({
            id: menu.id,
            nama: menu.nama_menu,
            harga: Number(menu.harga),
            stok: Number(menu.stok),
            qty: 1
        });
    }
    renderKeranjang();
}

function ubahQty(id, delta) {
    const item = keranjang.find(k => k.id == id);
    if (!item) return;

    item.qty += delta;
    if (item.qty <= 0) {
        keranjang = keranjang.filter(k => k.id != id);
    } else if (item.qty > item.stok) {
        item.qty = item.stok;
        tampilkanToast('Jumlah pesanan melebihi stok yang tersedia.', 'error');
    }
    renderKeranjang();
}

function hapusItemKeranjang(id) {
    keranjang = keranjang.filter(k => k.id != id);
    renderKeranjang();
}

function kosongkanKeranjang() {
    if (!keranjang.length) return;
    keranjang = [];
    renderKeranjang();
    tampilkanToast('Keranjang telah direset.', 'info');
}

const totalBelanja = () => keranjang.reduce((total, k) => total + (k.harga * k.qty), 0);
const totalItemQty = () => keranjang.reduce((total, k) => total + k.qty, 0);

function renderKeranjang() {
    const wrap = document.getElementById('isiKeranjang');
    const btnBayar = document.getElementById('tombolBayar');

    if (!keranjang.length) {
        wrap.innerHTML = `
            <div class="py-12 text-center text-slate-400">
                <i class="fa-solid fa-basket-shopping text-3xl mb-2 opacity-30 block"></i>
                <p class="text-xs font-medium">Keranjang masih kosong.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Pilih menu di samping untuk memesan.</p>
            </div>`;
        btnBayar.disabled = true;
    } else {
        wrap.innerHTML = keranjang.map(k => `
            <div class="flex items-center justify-between gap-3 bg-slate-50 border border-slate-200/80 rounded-xl p-2.5">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-900 truncate">${k.nama}</p>
                    <p class="text-[11px] text-slate-400">${rupiah(k.harga)} &times; ${k.qty} = <span class="font-bold text-slate-700">${rupiah(k.harga * k.qty)}</span></p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button onclick="ubahQty(${k.id}, -1)" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs font-bold">-</button>
                    <span class="text-xs font-extrabold w-5 text-center text-slate-900">${k.qty}</span>
                    <button onclick="ubahQty(${k.id}, 1)" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs font-bold">+</button>
                    <button onclick="hapusItemKeranjang(${k.id})" class="text-rose-400 hover:text-rose-600 ml-1 text-xs p-1" title="Hapus"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>`).join('');
        btnBayar.disabled = false;
    }

    document.getElementById('totalItemCount').innerText = totalItemQty() + ' item';
    document.getElementById('totalBelanja').innerText = rupiah(totalBelanja());
}

// --- MODAL PEMBAYARAN & NOMINAL CEPAT ---
function bukaBayar() {
    if (!keranjang.length) return;
    document.getElementById('inputBayar').value = '';
    document.getElementById('infoTotal').innerText = rupiah(totalBelanja());
    hitungKembalian();
    document.getElementById('modalBayar').classList.remove('hidden');
    setTimeout(() => document.getElementById('inputBayar').focus(), 100);
}

function tutupBayar() {
    document.getElementById('modalBayar').classList.add('hidden');
}

function setNominalPas() {
    document.getElementById('inputBayar').value = totalBelanja();
    hitungKembalian();
}

function setNominal(n) {
    document.getElementById('inputBayar').value = n;
    hitungKembalian();
}

function tambahNominal(n) {
    const curr = Number(document.getElementById('inputBayar').value) || 0;
    document.getElementById('inputBayar').value = curr + n;
    hitungKembalian();
}

function hitungKembalian() {
    const bayar = Number(document.getElementById('inputBayar').value) || 0;
    const total = totalBelanja();
    const selisih = bayar - total;
    const infoEl = document.getElementById('infoKembalian');
    const statusEl = document.getElementById('statusKembalianTeks');

    if (bayar <= 0) {
        infoEl.innerText = 'Rp 0';
        infoEl.className = 'text-lg font-extrabold text-slate-800';
        statusEl.innerText = 'Masukkan uang pembayaran';
        statusEl.className = 'text-[11px] text-slate-400';
    } else if (selisih < 0) {
        infoEl.innerText = 'Kurang ' + rupiah(Math.abs(selisih));
        infoEl.className = 'text-lg font-extrabold text-rose-600';
        statusEl.innerText = 'Uang bayar belum mencukupi';
        statusEl.className = 'text-[11px] text-rose-500 font-bold';
    } else if (selisih === 0) {
        infoEl.innerText = 'Uang Pas';
        infoEl.className = 'text-lg font-extrabold text-emerald-600';
        statusEl.innerText = 'Tidak ada kembalian';
        statusEl.className = 'text-[11px] text-emerald-600 font-bold';
    } else {
        infoEl.innerText = rupiah(selisih);
        infoEl.className = 'text-lg font-extrabold text-emerald-600';
        statusEl.innerText = 'Kembalian yang harus diberikan';
        statusEl.className = 'text-[11px] text-emerald-600 font-bold';
    }
}

// --- PROSES CHECKOUT TRANSAKSI ---
async function prosesBayar() {
    const bayar = Number(document.getElementById('inputBayar').value) || 0;
    const total = totalBelanja();

    if (bayar < total) {
        tampilkanToast('Uang pembayaran masih kurang dari total belanja!', 'error');
        return;
    }

    const btn = document.getElementById('btnProsesBayar');
    const btnTeks = document.getElementById('btnProsesBayarTeks');

    // Anti-Spam: Kunci tombol bayar & loading state
    btn.disabled = true;
    const originalText = btnTeks.innerText;
    btnTeks.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Memproses Transaksi...';

    const payloadItems = keranjang.map(k => ({
        id_menu: k.id,
        harga: k.harga,
        qty: k.qty
    }));

    const body = new FormData();
    body.append('bayar', bayar);
    body.append('items', JSON.stringify(payloadItems));

    try {
        const respon = await fetch(API + 'transaksi&action=simpan', { method: 'POST', body });
        const hasil = await respon.json();

        if (hasil.success) {
            tutupBayar();
            tampilkanStruk(hasil.data, bayar, keranjang);
            tampilkanToast('Transaksi berhasil disimpan!', 'sukses');
            keranjang = [];
            renderKeranjang();
            ambilData(); // Refresh stok menu terkini
        } else {
            tampilkanToast(hasil.message || 'Gagal menyimpan transaksi.', 'error');
        }
    } catch (err) {
        tampilkanToast('Terjadi kesalahan jaringan saat checkout.', 'error');
    } finally {
        btn.disabled = false;
        btnTeks.innerText = originalText;
    }
}

// --- STRUK DIGITAL ---
function tampilkanStruk(data, bayar, itemBeli) {
    const itemsHtml = itemBeli.map(k => `
        <div class="flex justify-between text-xs py-1">
            <span>${k.nama} <span class="text-slate-400">x${k.qty}</span></span>
            <span class="font-bold">${rupiah(k.harga * k.qty)}</span>
        </div>`).join('');

    document.getElementById('areaStruk').innerHTML = `
        <div class="text-center border-b border-dashed border-slate-300 pb-3 mb-3">
            <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mx-auto mb-1 text-sm font-bold">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <p class="font-extrabold text-slate-900 text-sm tracking-tight">KASIR RESTORAN</p>
            <p class="text-[11px] text-slate-500 font-mono mt-0.5">${data.no_transaksi}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">${new Date().toLocaleString('id-ID')} &bull; Kasir: ${NAMA_KASIR}</p>
        </div>
        <div class="divide-y divide-dashed divide-slate-200">${itemsHtml}</div>
        <div class="border-t border-dashed border-slate-300 mt-3 pt-3 text-xs space-y-1.5 font-medium">
            <div class="flex justify-between font-extrabold text-sm text-slate-900"><span>Total Belanja</span><span>${rupiah(data.total)}</span></div>
            <div class="flex justify-between text-slate-600"><span>Tunai Diterima</span><span>${rupiah(bayar)}</span></div>
            <div class="flex justify-between text-emerald-600 font-bold"><span>Kembalian</span><span>${rupiah(data.kembalian)}</span></div>
        </div>
        <p class="text-center text-[10px] text-slate-400 mt-4 italic">Terima kasih atas kunjungan Anda!</p>`;

    document.getElementById('modalStruk').classList.remove('hidden');
}

function tutupStruk() {
    document.getElementById('modalStruk').classList.add('hidden');
}

async function logout() {
    await fetch(API + 'auth&action=logout');
    location.href = 'login.php';
}

// Inisialisasi awal
ambilData();
renderKeranjang();
</script>
</body>
</html>