// Ini buat mengatur navigasi sidebar admin beranimasi, CRUD menu bergambar, kategori, riwayat, dan kasir.

const API = 'index.php?route=';
const MAX_INT = 2147483647;
const MAX_IMAGE_BYTES = 2 * 1024 * 1024;
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

let listMenu = [];
let listKategori = [];
let listRiwayat = [];
let listKasir = [];

let statusMenu = 'loading', errorMenu = '';
let statusKategori = 'loading', errorKategori = '';
let statusRiwayat = 'loading', errorRiwayat = '';
let statusKasir = 'loading', errorKasir = '';

let callbackKonfirmasi = null;
let previewObjectUrl = null;
let releaseFocusTrapAktif = null;
let elemenPemanggilDialog = null;
let sidebarCollapsed = false;
const timerTutupEl = new WeakMap();

const TAB_META = {
    menu: { title: 'Kelola Menu', subtitle: 'Atur daftar produk, harga, stok porsi, dan foto menu restoran.' },
    kategori: { title: 'Kelola Kategori', subtitle: 'Kelompokkan menu agar kasir lebih cepat menemukan produk.' },
    riwayat: { title: 'Riwayat Penjualan', subtitle: 'Pantau transaksi selesai dan cetak ulang struk pelanggan.' },
    kasir: { title: 'Kelola Akun Kasir', subtitle: 'Atur akses kasir, pantau jumlah transaksi, dan reset password.' }
};

const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

function escapeHTML(teks) {
    return String(teks).replace(/[&<>"']/g, karakter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[karakter]);
}

function inisialMenu(nama) {
    const kata = String(nama || 'M').trim().split(/\s+/);
    if (kata.length >= 2) return (kata[0][0] + kata[1][0]).toUpperCase();
    return String(nama || 'MN').slice(0, 2).toUpperCase();
}

function formatUkuranFile(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function tampilkanToast(pesan, tipe = 'sukses') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const config = {
        sukses: { bg: 'bg-emerald-700 text-white border-emerald-800', icon: 'check-circle' },
        error:  { bg: 'bg-rose-700 text-white border-rose-800', icon: 'error-circle' },
        info:   { bg: 'bg-slate-900 text-white border-slate-800', icon: 'info-circle' }
    }[tipe] || { bg: 'bg-slate-900 text-white border-slate-800', icon: 'info-circle' };

    toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg ${config.bg} border text-xs font-semibold pos-enter min-w-[260px] max-w-sm`;
    toast.innerHTML = `
        <span class="app-icon text-base shrink-0" data-icon="${config.icon}" aria-hidden="true"></span>
        <span class="flex-1">${escapeHTML(pesan)}</span>
        <button type="button" data-toast-close aria-label="Tutup notifikasi" class="opacity-85 hover:opacity-100 text-base leading-none px-1">&times;</button>
    `;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.2s ease';
        setTimeout(() => toast.remove(), 220);
    }, 3500);
}

// Helper animasi buka & tutup modal / backdrop
function bukaAnimasiEl(el) {
    const t = timerTutupEl.get(el);
    if (t) {
        clearTimeout(t);
        timerTutupEl.delete(el);
    }
    el.classList.remove('hidden');
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            el.classList.add('is-open');
        });
    });
}

function tutupAnimasiEl(el, callbackSelesai = null) {
    const t = timerTutupEl.get(el);
    if (t) clearTimeout(t);

    el.classList.remove('is-open');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const jeda = reduced ? 0 : 220;

    const tid = setTimeout(() => {
        el.classList.add('hidden');
        timerTutupEl.delete(el);
        if (callbackSelesai) callbackSelesai();
    }, jeda);
    timerTutupEl.set(el, tid);
}

// Focus trap aksesibel untuk semua dialog & mobile drawer
function pasangFocusTrap(modal, onClose, initialFocusEl = null) {
    if (releaseFocusTrapAktif) {
        releaseFocusTrapAktif();
        releaseFocusTrapAktif = null;
    }
    elemenPemanggilDialog = document.activeElement;

    const getFocusable = () => [...modal.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )].filter(el => el.offsetParent !== null || modal.contains(el));

    function onKeydown(e) {
        if (e.key === 'Escape') {
            e.preventDefault();
            onClose();
            return;
        }
        if (e.key !== 'Tab') return;
        const focusable = getFocusable();
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }

    modal.addEventListener('keydown', onKeydown);
    setTimeout(() => {
        if (initialFocusEl) {
            initialFocusEl.focus();
        } else {
            const focusable = getFocusable();
            if (focusable.length) focusable[0].focus();
        }
    }, 30);

    releaseFocusTrapAktif = () => {
        modal.removeEventListener('keydown', onKeydown);
        if (elemenPemanggilDialog && typeof elemenPemanggilDialog.focus === 'function') {
            elemenPemanggilDialog.focus();
        }
        elemenPemanggilDialog = null;
    };
}

function lepasFocusTrap() {
    if (releaseFocusTrapAktif) {
        releaseFocusTrapAktif();
        releaseFocusTrapAktif = null;
    }
}

// Kontrol Sidebar Collapse & Mobile Sheet dengan animasi
function toggleSidebarCollapse() {
    const sidebar = document.getElementById('adminSidebar');
    const header = document.getElementById('sidebarHeader');
    const userBox = document.getElementById('sidebarUserBox');
    const btn = document.getElementById('btnCollapseSidebar');
    sidebarCollapsed = !sidebarCollapsed;

    sidebar.classList.toggle('is-collapsed', sidebarCollapsed);
    sidebar.classList.toggle('w-64', !sidebarCollapsed);
    sidebar.classList.toggle('w-20', sidebarCollapsed);
    btn.setAttribute('aria-expanded', String(!sidebarCollapsed));

    header.classList.toggle('flex-col', sidebarCollapsed);
    header.classList.toggle('justify-between', !sidebarCollapsed);
    header.classList.toggle('justify-center', sidebarCollapsed);
    header.classList.toggle('px-4', !sidebarCollapsed);
    header.classList.toggle('px-2', sidebarCollapsed);
    header.classList.toggle('py-3.5', !sidebarCollapsed);
    header.classList.toggle('py-3', sidebarCollapsed);

    userBox.classList.toggle('justify-center', sidebarCollapsed);
    userBox.classList.toggle('px-2', !sidebarCollapsed);
    userBox.classList.toggle('px-0', sidebarCollapsed);

    sidebar.querySelectorAll('.sidebar-label, .sidebar-group-title').forEach(el => {
        el.classList.toggle('hidden', sidebarCollapsed);
    });
    sidebar.querySelectorAll('.nav-tab-btn').forEach(el => {
        el.classList.toggle('justify-center', sidebarCollapsed);
        el.classList.toggle('px-0', sidebarCollapsed);
        el.classList.toggle('px-3', !sidebarCollapsed);
    });
}

function bukaSidebarMobile() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const trigger = document.getElementById('btnMobileMenu');
    bukaAnimasiEl(backdrop);
    sidebar.classList.remove('-translate-x-full');
    trigger.setAttribute('aria-expanded', 'true');
    pasangFocusTrap(sidebar, tutupSidebarMobile);
}

function tutupSidebarMobile() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const trigger = document.getElementById('btnMobileMenu');
    tutupAnimasiEl(backdrop);
    sidebar.classList.add('-translate-x-full');
    trigger.setAttribute('aria-expanded', 'false');
    lepasFocusTrap();
}

// Konfirmasi Aksi
function mintaKonfirmasi(judul, pesan, teksAksi = 'Ya, Hapus', aksiPositif = false) {
    return new Promise((resolve) => {
        document.getElementById('judulKonfirmasi').innerText = judul;
        document.getElementById('pesanKonfirmasi').innerText = pesan;
        const tombolAksi = document.getElementById('btnAksiKonfirmasi');
        tombolAksi.innerText = teksAksi;
        tombolAksi.className = aksiPositif
            ? 'flex-1 min-h-[44px] rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white py-2.5 text-xs font-bold'
            : 'flex-1 min-h-[44px] rounded-lg bg-rose-700 hover:bg-rose-800 text-white py-2.5 text-xs font-bold';
        const ikon = document.getElementById('ikonKonfirmasi');
        ikon.className = aksiPositif
            ? 'w-12 h-12 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mx-auto mb-3'
            : 'w-12 h-12 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xl mx-auto mb-3';
        ikon.firstElementChild.dataset.icon = aksiPositif ? 'check-circle' : 'warning';

        const modal = document.getElementById('modalKonfirmasi');
        callbackKonfirmasi = resolve;
        bukaAnimasiEl(modal);
        pasangFocusTrap(modal, () => tutupKonfirmasi(false));
    });
}

function tutupKonfirmasi(hasil) {
    tutupAnimasiEl(document.getElementById('modalKonfirmasi'));
    lepasFocusTrap();
    if (callbackKonfirmasi) {
        callbackKonfirmasi(hasil);
        callbackKonfirmasi = null;
    }
}

// Berpindah tab modul admin
function bukaTab(nama) {
    ['menu', 'kategori', 'riwayat', 'kasir'].forEach(t => {
        const sec = document.getElementById('sec-' + t);
        const tab = document.getElementById('tab-' + t);
        if (!sec || !tab) return;

        const basePad = sidebarCollapsed ? 'justify-center px-0' : 'px-3';
        if (t === nama) {
            sec.classList.remove('hidden');
            tab.setAttribute('aria-current', 'page');
            tab.className = `nav-tab-btn w-full min-h-[44px] ${basePad} py-2.5 rounded-lg text-sm font-bold transition-colors flex items-center gap-3 bg-orange-700 text-white`;
            if (t === 'kasir' && statusKasir !== 'ready') muatKasir();
        } else {
            sec.classList.add('hidden');
            tab.removeAttribute('aria-current');
            tab.className = `nav-tab-btn w-full min-h-[44px] ${basePad} py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-3 text-slate-700 hover:bg-slate-100`;
        }
    });

    if (TAB_META[nama]) {
        document.getElementById('judulHalaman').innerText = TAB_META[nama].title;
        document.getElementById('subjudulHalaman').innerText = TAB_META[nama].subtitle;
    }

    if (window.innerWidth < 1024 && !document.getElementById('sidebarBackdrop').classList.contains('hidden')) {
        tutupSidebarMobile();
    }
}

// Helper row loading & error tabel
function renderBarisStatusTabel(colspan, tipe, pesan, aksiReload = '') {
    if (tipe === 'loading') {
        return `<tr><td colspan="${colspan}" class="text-center text-slate-600 py-10">
            <span class="app-icon text-2xl mb-2 block text-orange-700 is-spinning mx-auto" data-icon="spinner" aria-hidden="true"></span>
            <span class="text-xs font-bold block">${escapeHTML(pesan)}</span>
        </td></tr>`;
    }
    return `<tr><td colspan="${colspan}" class="text-center bg-rose-50 text-rose-800 py-8 px-4">
        <span class="app-icon text-2xl mb-2 block mx-auto" data-icon="error-circle" aria-hidden="true"></span>
        <p class="text-xs font-bold mb-3">${escapeHTML(pesan)}</p>
        ${aksiReload ? `<button type="button" data-retry="${escapeHTML(aksiReload)}" class="min-h-[44px] px-4 py-2 rounded-lg bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold">Muat Ulang</button>` : ''}
    </td></tr>`;
}

// Memuat data dari backend
async function muatMenu() {
    statusMenu = 'loading';
    errorMenu = '';
    renderMenu();
    try {
        const res = await fetch(API + 'menu&action=list');
        const r = await res.json();
        if (!res.ok || !r.success) throw new Error(r.message || 'Gagal memuat data menu.');
        listMenu = r.data || [];
        statusMenu = 'ready';
        renderMenu();
        updateStats();
    } catch (err) {
        statusMenu = 'error';
        errorMenu = err.message || 'Gagal terhubung ke server saat memuat menu.';
        renderMenu();
        tampilkanToast(errorMenu, 'error');
    }
}

async function muatKategori() {
    statusKategori = 'loading';
    errorKategori = '';
    renderKategori();
    try {
        const res = await fetch(API + 'kategori&action=list');
        const r = await res.json();
        if (!res.ok || !r.success) throw new Error(r.message || 'Gagal memuat data kategori.');
        listKategori = r.data || [];
        statusKategori = 'ready';
        renderKategori();
        renderSelectKategori();
        updateStats();
    } catch (err) {
        statusKategori = 'error';
        errorKategori = err.message || 'Gagal terhubung ke server saat memuat kategori.';
        renderKategori();
        tampilkanToast(errorKategori, 'error');
    }
}

async function muatRiwayat() {
    statusRiwayat = 'loading';
    errorRiwayat = '';
    renderRiwayat();
    try {
        const res = await fetch(API + 'transaksi&action=riwayat');
        const r = await res.json();
        if (!res.ok || !r.success) throw new Error(r.message || 'Gagal memuat riwayat transaksi.');
        listRiwayat = r.data || [];
        statusRiwayat = 'ready';
        renderRiwayat();
        updateStats();
    } catch (err) {
        statusRiwayat = 'error';
        errorRiwayat = err.message || 'Gagal terhubung ke server saat memuat riwayat.';
        renderRiwayat();
        tampilkanToast(errorRiwayat, 'error');
    }
}

async function muatKasir() {
    statusKasir = 'loading';
    errorKasir = '';
    renderKasir();
    try {
        const res = await fetch(API + 'user&action=kasir-list');
        const r = await res.json();
        if (!res.ok || !r.success) throw new Error(r.message || 'Gagal memuat data kasir.');
        listKasir = r.data || [];
        statusKasir = 'ready';
        renderKasir();
        updateStatsKasir();
    } catch (err) {
        statusKasir = 'error';
        errorKasir = err.message || 'Gagal terhubung ke server saat memuat akun kasir.';
        renderKasir();
        tampilkanToast(errorKasir, 'error');
    }
}

function updateStats() {
    document.getElementById('statMenu').innerText = listMenu.length;
    document.getElementById('statKategori').innerText = listKategori.length;

    const transaksiSelesai = listRiwayat.filter(r => r.status === 'selesai');
    document.getElementById('statTransaksi').innerText = transaksiSelesai.length;
    const totalOmset = transaksiSelesai.reduce((sum, r) => sum + Number(r.total), 0);
    document.getElementById('statOmset').innerText = rupiah(totalOmset);

    const stokHabis = listMenu.filter(m => Number(m.stok) <= 0).length;
    const stokTipis = listMenu.filter(m => Number(m.stok) > 0 && Number(m.stok) <= 5).length;
    const infoStokEl = document.getElementById('statStokMenipis');
    if (stokHabis > 0) {
        infoStokEl.innerText = `${stokHabis} menu habis`;
        infoStokEl.className = 'text-xs font-bold text-rose-700 block';
    } else if (stokTipis > 0) {
        infoStokEl.innerText = `${stokTipis} menu stok menipis`;
        infoStokEl.className = 'text-xs font-bold text-amber-800 block';
    } else {
        infoStokEl.innerText = 'Semua stok aman';
        infoStokEl.className = 'text-xs font-semibold text-emerald-700 block';
    }
}

// Render tabel menu
function renderMenu(data = null) {
    const tbody = document.getElementById('tabelMenu');
    if (statusMenu === 'loading') {
        tbody.innerHTML = renderBarisStatusTabel(5, 'loading', 'Memuat daftar menu...');
        return;
    }
    if (statusMenu === 'error') {
        tbody.innerHTML = renderBarisStatusTabel(5, 'error', errorMenu, 'menu');
        return;
    }

    const daftar = data ?? listMenu;
    if (!daftar.length) {
        const kosongTotal = listMenu.length === 0;
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-slate-600 py-10 px-4">
            <span class="app-icon text-3xl mb-2 block opacity-60 mx-auto" data-icon="box-open" aria-hidden="true"></span>
            <p class="text-sm font-bold text-slate-900">${kosongTotal ? 'Belum ada menu terdaftar' : 'Menu tidak ditemukan'}</p>
            <p class="text-xs text-slate-600 mt-1">${kosongTotal ? 'Klik "Tambah Menu Baru" untuk mulai mengisi katalog.' : 'Coba ubah kata kunci pencarian atau pilih semua kategori.'}</p>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = daftar.map(m => {
        const stokHabis = Number(m.stok) <= 0;
        const stokTipis = !stokHabis && Number(m.stok) <= 5;
        const stokBadge = stokHabis
            ? 'text-rose-800 bg-rose-50 border border-rose-300'
            : (stokTipis ? 'text-amber-900 bg-amber-50 border border-amber-300' : 'text-emerald-800 bg-emerald-50 border border-emerald-200');
        const dotColor = stokHabis ? 'bg-rose-600' : (stokTipis ? 'bg-amber-600' : 'bg-emerald-600');
        const stokLabel = stokHabis ? 'Habis (0)' : `${m.stok} porsi`;

        return `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg border border-slate-200 bg-slate-100 overflow-hidden shrink-0 flex items-center justify-center">
                        ${m.gambar ? `
                            <img src="${escapeHTML(m.gambar)}" alt="${escapeHTML(m.nama_menu)}" width="48" height="48" loading="lazy"
                                 class="w-12 h-12 object-cover">
                            <span class="hidden text-xs font-extrabold text-slate-700">${escapeHTML(inisialMenu(m.nama_menu))}</span>
                        ` : `
                            <span class="text-xs font-extrabold text-slate-700">${escapeHTML(inisialMenu(m.nama_menu))}</span>
                        `}
                    </div>
                    <div class="min-w-0">
                        <span class="font-bold text-slate-900 block truncate">${escapeHTML(m.nama_menu)}</span>
                        <span class="text-xs text-slate-600">${m.gambar ? 'Foto terpasang' : 'Tanpa foto'}</span>
                    </div>
                </div>
            </td>
            <td class="py-3 px-4">
                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                    ${escapeHTML(m.nama_kategori || 'Tanpa Kategori')}
                </span>
            </td>
            <td class="py-3 px-4 font-extrabold text-orange-700 tabular-nums">${rupiah(m.harga)}</td>
            <td class="py-3 px-4">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-md tabular-nums ${stokBadge}">
                    <span class="w-1.5 h-1.5 rounded-full ${dotColor}" aria-hidden="true"></span>
                    <span>${stokLabel}</span>
                </span>
            </td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                    <button type="button" data-menu-action="edit" data-id="${m.id}" aria-label="Edit menu ${escapeHTML(m.nama_menu)}"
                            class="w-11 h-11 rounded-lg border border-slate-200 text-slate-700 hover:text-slate-900 hover:bg-slate-100 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon" data-icon="edit" aria-hidden="true"></span>
                    </button>
                    <button type="button" data-menu-action="delete" data-id="${m.id}" aria-label="Hapus menu ${escapeHTML(m.nama_menu)}"
                            class="w-11 h-11 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon" data-icon="trash" aria-hidden="true"></span>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filterMenu() {
    if (statusMenu !== 'ready') return;
    const q = document.getElementById('cariMenu').value.toLowerCase().trim();
    const katId = document.getElementById('filterKategoriAdmin').value;
    const filtered = listMenu.filter(m => {
        const cocokTeks = q === ''
            || m.nama_menu.toLowerCase().includes(q)
            || (m.nama_kategori && m.nama_kategori.toLowerCase().includes(q));
        const cocokKat = katId === '' || String(m.id_kategori) === String(katId);
        return cocokTeks && cocokKat;
    });
    renderMenu(filtered);
}

// Render tabel kategori
function renderKategori(data = null) {
    const tbody = document.getElementById('tabelKategori');
    if (statusKategori === 'loading') {
        tbody.innerHTML = renderBarisStatusTabel(3, 'loading', 'Memuat daftar kategori...');
        return;
    }
    if (statusKategori === 'error') {
        tbody.innerHTML = renderBarisStatusTabel(3, 'error', errorKategori, 'kategori');
        return;
    }

    const daftar = data ?? listKategori;
    if (!daftar.length) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-slate-600 py-10">
            <span class="app-icon text-3xl mb-2 block opacity-60 mx-auto" data-icon="tags" aria-hidden="true"></span>
            <p class="text-sm font-bold text-slate-900">Kategori tidak ditemukan</p>
        </td></tr>`;
        return;
    }
    tbody.innerHTML = daftar.map(k => `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="py-3 px-4 text-slate-600 font-mono text-xs tabular-nums">#${k.id}</td>
            <td class="py-3 px-4 font-bold text-slate-900">${escapeHTML(k.nama_kategori)}</td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                    <button type="button" data-kat-action="edit" data-id="${k.id}" aria-label="Edit kategori ${escapeHTML(k.nama_kategori)}"
                            class="w-11 h-11 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon" data-icon="edit" aria-hidden="true"></span>
                    </button>
                    <button type="button" data-kat-action="delete" data-id="${k.id}" aria-label="Hapus kategori ${escapeHTML(k.nama_kategori)}"
                            class="w-11 h-11 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon" data-icon="trash" aria-hidden="true"></span>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

function filterKategoriTabel() {
    if (statusKategori !== 'ready') return;
    const q = document.getElementById('cariKategori').value.toLowerCase().trim();
    const filtered = listKategori.filter(k => k.nama_kategori.toLowerCase().includes(q));
    renderKategori(filtered);
}

function renderSelectKategori() {
    const options = listKategori
        .map(k => `<option value="${k.id}">${escapeHTML(k.nama_kategori)}</option>`).join('');
    document.getElementById('menuKategori').innerHTML = options;
    document.getElementById('filterKategoriAdmin').innerHTML = '<option value="">Semua Kategori</option>' + options;
}

// Render riwayat transaksi
function renderRiwayat(data = null) {
    const tbody = document.getElementById('tabelRiwayat');
    if (statusRiwayat === 'loading') {
        tbody.innerHTML = renderBarisStatusTabel(6, 'loading', 'Memuat riwayat penjualan...');
        return;
    }
    if (statusRiwayat === 'error') {
        tbody.innerHTML = renderBarisStatusTabel(6, 'error', errorRiwayat, 'riwayat');
        return;
    }

    const daftar = data ?? listRiwayat;
    if (!daftar.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-slate-600 py-10">
            <span class="app-icon text-3xl mb-2 block opacity-60 mx-auto" data-icon="receipt" aria-hidden="true"></span>
            <p class="text-sm font-bold text-slate-900">Belum ada transaksi terekam</p>
        </td></tr>`;
        return;
    }
    const badge = {
        selesai: 'bg-emerald-50 text-emerald-800 border border-emerald-300',
        pending: 'bg-amber-50 text-amber-900 border border-amber-300',
        batal:   'bg-rose-50 text-rose-800 border border-rose-300'
    };
    tbody.innerHTML = daftar.map(r => `
        <tr class="hover:bg-slate-50 transition-colors">
            <td class="py-3 px-4 font-mono font-bold text-slate-900">${escapeHTML(r.no_transaksi)}</td>
            <td class="py-3 px-4 text-slate-600 text-xs tabular-nums">${escapeHTML(r.tanggal)}</td>
            <td class="py-3 px-4">
                <span class="inline-flex items-center gap-1.5 font-semibold text-slate-800">
                    <span class="app-icon text-slate-500" data-icon="user-circle" aria-hidden="true"></span>
                    <span>${escapeHTML(r.nama_kasir)}</span>
                </span>
            </td>
            <td class="py-3 px-4 font-extrabold text-slate-900 tabular-nums">${rupiah(r.total)}</td>
            <td class="py-3 px-4">
                <span class="text-xs font-bold px-2.5 py-1 rounded-md ${badge[r.status] || 'bg-slate-100 text-slate-700'}">
                    ${escapeHTML(r.status.toUpperCase())}
                </span>
            </td>
            <td class="py-3 px-4 text-center">
                <button type="button" data-struk-id="${r.id}"
                        class="min-h-[44px] inline-flex items-center gap-1.5 text-xs font-bold text-slate-800 hover:text-orange-700 bg-slate-100 hover:bg-orange-50 px-3 py-1.5 rounded-lg border border-slate-300 transition-colors">
                    <span class="app-icon" data-icon="receipt" aria-hidden="true"></span>
                    <span>Lihat</span>
                </button>
            </td>
        </tr>`).join('');
}

function filterRiwayatTabel() {
    if (statusRiwayat !== 'ready') return;
    const q = document.getElementById('cariRiwayat').value.toLowerCase().trim();
    const filtered = listRiwayat.filter(r =>
        r.no_transaksi.toLowerCase().includes(q) ||
        r.nama_kasir.toLowerCase().includes(q) ||
        r.tanggal.toLowerCase().includes(q) ||
        r.status.toLowerCase().includes(q)
    );
    renderRiwayat(filtered);
}

// Pratinjau & Hapus Foto di Modal Menu
function resetPreviewFoto() {
    if (previewObjectUrl) {
        URL.revokeObjectURL(previewObjectUrl);
        previewObjectUrl = null;
    }
    const img = document.getElementById('previewGambarImg');
    const placeholder = document.getElementById('previewGambarPlaceholder');
    const btnHapus = document.getElementById('btnHapusFotoMenu');
    const namaFile = document.getElementById('namaFileGambar');

    img.removeAttribute('src');
    img.classList.add('hidden');
    placeholder.classList.remove('hidden');
    btnHapus.classList.add('hidden');
    namaFile.innerText = '';
    document.getElementById('menuHapusGambar').value = '0';
}

function tampilkanPreviewUrl(src, label, bisaHapus = true) {
    const img = document.getElementById('previewGambarImg');
    const placeholder = document.getElementById('previewGambarPlaceholder');
    const btnHapus = document.getElementById('btnHapusFotoMenu');
    const namaFile = document.getElementById('namaFileGambar');

    img.src = src;
    img.classList.remove('hidden');
    placeholder.classList.add('hidden');
    namaFile.innerText = label;
    btnHapus.classList.toggle('hidden', !bisaHapus);
}

function previewFotoMenu(input) {
    const pesanEl = document.getElementById('pesanMenu');
    pesanEl.classList.add('hidden');

    const file = input.files && input.files[0];
    if (!file) return;

    if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
        input.value = '';
        pesanEl.innerText = 'Format gambar tidak didukung. Pilih file JPG, PNG, atau WEBP.';
        pesanEl.classList.remove('hidden');
        tampilkanToast('Format file harus JPG, PNG, atau WEBP.', 'error');
        return;
    }

    if (file.size > MAX_IMAGE_BYTES) {
        input.value = '';
        pesanEl.innerText = 'Ukuran gambar terlalu besar (' + formatUkuranFile(file.size) + '). Maksimal 2 MB.';
        pesanEl.classList.remove('hidden');
        tampilkanToast('Ukuran gambar maksimal 2 MB.', 'error');
        return;
    }

    if (previewObjectUrl) {
        URL.revokeObjectURL(previewObjectUrl);
    }
    previewObjectUrl = URL.createObjectURL(file);
    document.getElementById('menuHapusGambar').value = '0';
    tampilkanPreviewUrl(previewObjectUrl, `${file.name} (${formatUkuranFile(file.size)})`, true);
}

function tandaiHapusFoto() {
    document.getElementById('menuGambar').value = '';
    resetPreviewFoto();
    document.getElementById('menuHapusGambar').value = '1';
    document.getElementById('namaFileGambar').innerText = 'Foto akan dihapus saat menu disimpan.';
}

// Modal Tambah / Edit Menu
function bukaModalMenu(id = null) {
    document.getElementById('formMenu').reset();
    document.getElementById('menuId').value = '';
    document.getElementById('pesanMenu').classList.add('hidden');
    resetPreviewFoto();
    renderSelectKategori();

    if (id !== null) {
        const m = listMenu.find(x => x.id == id);
        if (!m) return;
        document.getElementById('judulModalMenu').innerText = 'Edit Menu: ' + m.nama_menu;
        document.getElementById('menuId').value = m.id;
        document.getElementById('menuNama').value = m.nama_menu;
        document.getElementById('menuKategori').value = m.id_kategori;
        document.getElementById('menuHarga').value = m.harga;
        document.getElementById('menuStok').value = m.stok;
        if (m.gambar) {
            tampilkanPreviewUrl(m.gambar, 'Foto terpasang saat ini', true);
        }
    } else {
        document.getElementById('judulModalMenu').innerText = 'Tambah Menu Baru';
    }

    const modal = document.getElementById('modalMenu');
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, tutupModalMenu, document.getElementById('menuNama'));
}

function editMenu(id) { bukaModalMenu(id); }
function tutupModalMenu() {
    tutupAnimasiEl(document.getElementById('modalMenu'), resetPreviewFoto);
    lepasFocusTrap();
}

async function hapusMenu(id) {
    const m = listMenu.find(x => x.id == id);
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

// Modal Kategori
function bukaModalKategori(id = null) {
    document.getElementById('kategoriId').value = '';
    document.getElementById('kategoriNama').value = '';
    document.getElementById('pesanKategori').classList.add('hidden');

    if (id !== null) {
        const k = listKategori.find(x => x.id == id);
        if (!k) return;
        document.getElementById('judulModalKategori').innerText = 'Edit Kategori: ' + k.nama_kategori;
        document.getElementById('kategoriId').value = k.id;
        document.getElementById('kategoriNama').value = k.nama_kategori;
    } else {
        document.getElementById('judulModalKategori').innerText = 'Tambah Kategori Baru';
    }
    const modal = document.getElementById('modalKategori');
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, tutupModalKategori, document.getElementById('kategoriNama'));
}

function editKategori(id) { bukaModalKategori(id); }
function tutupModalKategori() {
    tutupAnimasiEl(document.getElementById('modalKategori'));
    lepasFocusTrap();
}

async function hapusKategori(id) {
    const k = listKategori.find(x => x.id == id);
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

// Detail Struk
async function bukaStruk(id) {
    const head = listRiwayat.find(r => r.id == id);
    if (!head) {
        tampilkanToast('Data transaksi tidak ditemukan. Muat ulang riwayat lalu coba lagi.', 'error');
        return;
    }
    try {
        const r = await fetch(API + 'transaksi&action=detail&id=' + id).then(res => res.json());
        if (!r.success) {
            tampilkanToast(r.message, 'error');
            return;
        }

        const items = r.data.map(d => `
            <div class="flex justify-between text-xs py-1 gap-2">
                <span>${escapeHTML(d.nama_menu)} <span class="text-slate-600">x${d.qty}</span></span>
                <span class="font-bold tabular-nums shrink-0">${rupiah(d.subtotal)}</span>
            </div>`).join('');

        document.getElementById('areaStruk').innerHTML = `
            <div class="text-center border-b border-dashed border-slate-300 pb-3 mb-3">
                <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center mx-auto mb-1 text-sm font-bold">
                    <span class="app-icon" data-icon="utensils" aria-hidden="true"></span>
                </div>
                <p class="font-extrabold text-slate-900 text-sm tracking-tight">KASIR RESTORAN</p>
                <p class="text-xs text-slate-700 font-mono mt-0.5">${escapeHTML(head.no_transaksi)}</p>
                <p class="text-[11px] text-slate-600 mt-0.5">${escapeHTML(head.tanggal)} &bull; Kasir: ${escapeHTML(head.nama_kasir)}</p>
            </div>
            <div class="divide-y divide-dashed divide-slate-200">${items}</div>
            <div class="border-t border-dashed border-slate-300 mt-3 pt-3 text-xs space-y-1.5 font-medium">
                <div class="flex justify-between font-extrabold text-sm text-slate-900"><span>Total</span><span class="tabular-nums">${rupiah(head.total)}</span></div>
                <div class="flex justify-between text-slate-700"><span>Uang Bayar</span><span class="tabular-nums">${rupiah(head.bayar)}</span></div>
                <div class="flex justify-between text-emerald-700 font-bold"><span>Kembalian</span><span class="tabular-nums">${rupiah(head.kembalian)}</span></div>
            </div>`;
        const modal = document.getElementById('modalStruk');
        bukaAnimasiEl(modal);
        pasangFocusTrap(modal, tutupStruk);
    } catch (err) {
        tampilkanToast('Gagal memuat detail struk.', 'error');
    }
}

function tutupStruk() {
    tutupAnimasiEl(document.getElementById('modalStruk'));
    lepasFocusTrap();
}

async function logout() {
    const yakin = await mintaKonfirmasi(
        'Yakin ingin keluar?',
        'Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login.',
        'Ya, Keluar'
    );
    if (!yakin) return;

    try {
        const respon = await fetch(API + 'auth&action=logout', { method: 'POST' });
        if (!respon.ok) throw new Error('Logout gagal');
        location.href = 'login.php';
    } catch (error) {
        tampilkanToast('Logout gagal. Silakan coba lagi.', 'error');
    }
}

// Kelola Akun Kasir
function updateStatsKasir() {
    const aktif = listKasir.filter(k => k.is_active == 1).length;
    const nonaktif = listKasir.filter(k => k.is_active == 0).length;
    const totalTrx = listKasir.reduce((s, k) => s + Number(k.total_transaksi || 0), 0);
    document.getElementById('statTotalKasir').innerText = listKasir.length;
    document.getElementById('statKasirAktif').innerText = aktif;
    document.getElementById('statKasirNonaktif').innerText = nonaktif;
    document.getElementById('statTotalTrxKasir').innerText = totalTrx;
}

function renderKasir(data = null) {
    const tbody = document.getElementById('tabelKasir');
    if (statusKasir === 'loading') {
        tbody.innerHTML = renderBarisStatusTabel(7, 'loading', 'Memuat daftar kasir...');
        return;
    }
    if (statusKasir === 'error') {
        tbody.innerHTML = renderBarisStatusTabel(7, 'error', errorKasir, 'kasir');
        return;
    }

    const daftar = data ?? listKasir;
    if (!daftar.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-slate-600 py-10">
            <span class="app-icon text-3xl mb-2 block opacity-60 mx-auto" data-icon="users" aria-hidden="true"></span>
            <p class="text-sm font-bold text-slate-900">Belum ada akun kasir terdaftar</p>
        </td></tr>`;
        return;
    }
    tbody.innerHTML = daftar.map(k => {
        const isActive = k.is_active == 1;
        const statusBadge = isActive
            ? '<span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-300"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span><span>Aktif</span></span>'
            : '<span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-md bg-rose-50 text-rose-800 border border-rose-300"><span class="w-1.5 h-1.5 rounded-full bg-rose-600" aria-hidden="true"></span><span>Nonaktif</span></span>';
        const avatar = k.nama ? k.nama.charAt(0).toUpperCase() : '?';
        const avatarBg = isActive ? 'bg-slate-900' : 'bg-slate-500';
        const trxTerakhir = k.transaksi_terakhir
            ? new Date(k.transaksi_terakhir).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
            : '<span class="text-slate-600 italic">Belum ada</span>';
        return `
        <tr class="hover:bg-slate-50 transition-colors ${!isActive ? 'bg-slate-50/70' : ''}">
            <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg ${avatarBg} text-white flex items-center justify-center text-xs font-extrabold shrink-0" aria-hidden="true">${escapeHTML(avatar)}</div>
                    <div>
                        <span class="font-bold text-slate-900 block leading-tight">${escapeHTML(k.nama)}</span>
                        <span class="text-xs text-slate-600">Bergabung ${new Date(k.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
                    </div>
                </div>
            </td>
            <td class="py-3 px-4"><code class="text-xs font-mono bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-200">${escapeHTML(k.username)}</code></td>
            <td class="py-3 px-4">${statusBadge}</td>
            <td class="py-3 px-4 font-bold text-slate-900 tabular-nums">${k.total_transaksi || 0}</td>
            <td class="py-3 px-4 font-extrabold text-orange-700 tabular-nums">${rupiah(k.total_omset || 0)}</td>
            <td class="py-3 px-4 text-xs text-slate-600 tabular-nums">${trxTerakhir}</td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                    <button type="button" data-user-action="edit" data-id="${k.id}" aria-label="Edit user ${escapeHTML(k.nama)}"
                            class="w-11 h-11 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon text-xs" data-icon="edit" aria-hidden="true"></span>
                    </button>
                    <button type="button" data-user-action="toggle" data-id="${k.id}" data-next-status="${isActive ? 0 : 1}" aria-label="${isActive ? 'Nonaktifkan' : 'Aktifkan'} ${escapeHTML(k.nama)}"
                            class="w-11 h-11 rounded-lg border border-slate-200 text-slate-700 hover:bg-amber-50 hover:text-amber-800 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon text-xs" data-icon="${isActive ? 'user-slash' : 'user-check'}" aria-hidden="true"></span>
                    </button>
                    <button type="button" data-user-action="reset" data-id="${k.id}" aria-label="Reset password ${escapeHTML(k.nama)}"
                            class="w-11 h-11 rounded-lg border border-slate-200 text-slate-700 hover:bg-amber-50 hover:text-amber-800 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon text-xs" data-icon="key" aria-hidden="true"></span>
                    </button>
                    <button type="button" data-user-action="delete" data-id="${k.id}" aria-label="Hapus user ${escapeHTML(k.nama)}"
                            class="w-11 h-11 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 inline-flex items-center justify-center transition-colors">
                        <span class="app-icon text-xs" data-icon="trash" aria-hidden="true"></span>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filterKasirTabel() {
    if (statusKasir !== 'ready') return;
    const q = document.getElementById('cariKasir').value.toLowerCase().trim();
    const filtered = listKasir.filter(k =>
        k.nama.toLowerCase().includes(q) || k.username.toLowerCase().includes(q)
    );
    renderKasir(filtered);
}

// Modal Tambah / Edit User
function bukaModalUser(id = null) {
    document.getElementById('formUser').reset();
    document.getElementById('userId').value = '';
    document.getElementById('pesanUser').classList.add('hidden');

    const passwordWrapper = document.getElementById('wrapperPasswordUser');
    const passwordInput = document.getElementById('userPassword');

    if (id !== null) {
        const u = listKasir.find(x => x.id == id);
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
        document.getElementById('judulModalUser').innerText = 'Tambah Akun Baru';
        passwordWrapper.classList.remove('hidden');
        passwordInput.setAttribute('required', 'required');
        document.getElementById('btnSubmitUserTeks').innerText = 'Simpan Akun';
    }
    const modal = document.getElementById('modalUser');
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, tutupModalUser, document.getElementById('userNama'));
}

function editUser(id) { bukaModalUser(id); }
function tutupModalUser() {
    tutupAnimasiEl(document.getElementById('modalUser'));
    lepasFocusTrap();
}

function togglePasswordVisibility() {
    const inp = document.getElementById('userPassword');
    const icon = document.getElementById('iconPassword');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.dataset.icon = 'eye-slash';
    } else {
        inp.type = 'password';
        icon.dataset.icon = 'eye';
    }
}

async function hapusUser(id) {
    const k = listKasir.find(x => x.id == id);
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

async function toggleKasirStatus(id, statusBaru) {
    const kasir = listKasir.find(k => k.id == id);
    const nama = kasir ? kasir.nama : 'kasir ini';
    const judul = statusBaru ? 'Aktifkan Kasir?' : 'Nonaktifkan Kasir?';
    const pesan = statusBaru
        ? `Kasir "${nama}" akan bisa login lagi.`
        : `Kasir "${nama}" tidak bisa login sampai diaktifkan lagi.`;
    const teksTombol = statusBaru ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan';

    const yakin = await mintaKonfirmasi(judul, pesan, teksTombol, statusBaru === 1);
    if (!yakin) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('is_active', statusBaru);

    try {
        const respon = await fetch(API + 'user&action=toggle', { method: 'POST', body: formData });
        const hasil = await respon.json();

        if (hasil.success) {
            tampilkanToast(hasil.message, 'sukses');
            muatKasir();
        } else {
            tampilkanToast(hasil.message, 'error');
        }
    } catch (error) {
        tampilkanToast('Gagal terhubung ke server.', 'error');
    }
}

function bukaResetPassword(id) {
    const k = listKasir.find(x => x.id == id);
    if (!k) return;
    document.getElementById('formResetPassword').reset();
    document.getElementById('pesanResetPassword').classList.add('hidden');
    document.getElementById('resetPasswordUserId').value = id;
    document.getElementById('resetPasswordNama').innerText = k.nama;
    document.getElementById('judulResetPassword').innerText = 'Reset Password: ' + k.nama;
    const modal = document.getElementById('modalResetPassword');
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, tutupModalResetPassword, document.getElementById('resetNewPassword'));
}

function tutupModalResetPassword() {
    tutupAnimasiEl(document.getElementById('modalResetPassword'));
    lepasFocusTrap();
}

// Pasang seluruh event listener DOM tanpa inline JS di view
function pasangEventAdmin() {
    document.getElementById('sidebarBackdrop').addEventListener('click', tutupSidebarMobile);
    document.getElementById('btnCollapseSidebar').addEventListener('click', toggleSidebarCollapse);
    document.getElementById('btnTutupSidebarMobile').addEventListener('click', tutupSidebarMobile);
    document.getElementById('btnMobileMenu').addEventListener('click', bukaSidebarMobile);
    document.getElementById('btnLogoutAdmin').addEventListener('click', logout);

    document.querySelectorAll('.nav-tab-btn[data-tab]').forEach(btn => {
        btn.addEventListener('click', () => bukaTab(btn.dataset.tab));
    });

    // Filter, search & tombol tambah
    document.getElementById('cariMenu').addEventListener('input', filterMenu);
    document.getElementById('filterKategoriAdmin').addEventListener('change', filterMenu);
    document.getElementById('btnTambahMenu').addEventListener('click', () => bukaModalMenu());

    document.getElementById('cariKategori').addEventListener('input', filterKategoriTabel);
    document.getElementById('btnTambahKategori').addEventListener('click', () => bukaModalKategori());

    document.getElementById('cariRiwayat').addEventListener('input', filterRiwayatTabel);
    document.getElementById('btnRefreshRiwayat').addEventListener('click', muatRiwayat);

    document.getElementById('cariKasir').addEventListener('input', filterKasirTabel);
    document.getElementById('btnRefreshKasir').addEventListener('click', muatKasir);
    document.getElementById('btnTambahUser').addEventListener('click', () => bukaModalUser());

    // Modal menu & upload foto
    document.getElementById('menuGambar').addEventListener('change', (e) => previewFotoMenu(e.target));
    document.getElementById('btnHapusFotoMenu').addEventListener('click', tandaiHapusFoto);
    document.getElementById('btnCloseModalMenu').addEventListener('click', tutupModalMenu);
    document.getElementById('btnBatalModalMenu').addEventListener('click', tutupModalMenu);

    document.getElementById('formMenu').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitMenu');
        const btnTeks = document.getElementById('btnSubmitMenuTeks');
        const pesanEl = document.getElementById('pesanMenu');
        pesanEl.classList.add('hidden');

        const id = document.getElementById('menuId').value;
        const aksi = id ? 'update' : 'create';

        btn.disabled = true;
        const originalText = btnTeks.innerText;
        btnTeks.innerHTML = '<span class="app-icon mr-1 is-spinning" data-icon="spinner" aria-hidden="true"></span>Menyimpan...';

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

    // Modal kategori
    document.getElementById('btnCloseModalKategori').addEventListener('click', tutupModalKategori);
    document.getElementById('btnBatalModalKategori').addEventListener('click', tutupModalKategori);
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
        btnTeks.innerHTML = '<span class="app-icon mr-1 is-spinning" data-icon="spinner" aria-hidden="true"></span>Menyimpan...';

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

    // Modal struk & konfirmasi
    document.getElementById('btnTutupStrukAdmin').addEventListener('click', tutupStruk);
    document.getElementById('btnCetakStrukAdmin').addEventListener('click', () => window.print());
    document.getElementById('btnBatalKonfirmasi').addEventListener('click', () => tutupKonfirmasi(false));
    document.getElementById('btnAksiKonfirmasi').addEventListener('click', () => tutupKonfirmasi(true));

    // Modal user
    document.getElementById('btnCloseModalUser').addEventListener('click', tutupModalUser);
    document.getElementById('btnBatalModalUser').addEventListener('click', tutupModalUser);
    document.getElementById('btnTogglePasswordUser').addEventListener('click', togglePasswordVisibility);
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
        btnTeks.innerHTML = '<span class="app-icon mr-1 is-spinning" data-icon="spinner" aria-hidden="true"></span>Menyimpan...';

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

    // Modal reset password
    document.getElementById('btnCloseResetPassword').addEventListener('click', tutupModalResetPassword);
    document.getElementById('btnBatalResetPassword').addEventListener('click', tutupModalResetPassword);
    document.getElementById('formResetPassword').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('btnResetPassword');
        const btnTeks = document.getElementById('btnResetPasswordTeks');
        const pesanEl = document.getElementById('pesanResetPassword');
        pesanEl.classList.add('hidden');

        btn.disabled = true;
        const originalText = btnTeks.innerText;
        btnTeks.innerHTML = '<span class="app-icon mr-1 is-spinning" data-icon="spinner" aria-hidden="true"></span>Mereset...';

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

    // Delegasi aksi baris tabel & retry error & fallback gambar
    const tabelMenu = document.getElementById('tabelMenu');
    tabelMenu.addEventListener('click', (e) => {
        const retry = e.target.closest('button[data-retry="menu"]');
        if (retry) return muatMenu();
        const btn = e.target.closest('button[data-menu-action]');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        if (btn.dataset.menuAction === 'edit') editMenu(id);
        else if (btn.dataset.menuAction === 'delete') hapusMenu(id);
    });
    tabelMenu.addEventListener('error', (e) => {
        if (e.target && e.target.tagName === 'IMG') {
            e.target.classList.add('hidden');
            if (e.target.nextElementSibling) e.target.nextElementSibling.classList.remove('hidden');
        }
    }, true);

    document.getElementById('tabelKategori').addEventListener('click', (e) => {
        const retry = e.target.closest('button[data-retry="kategori"]');
        if (retry) return muatKategori();
        const btn = e.target.closest('button[data-kat-action]');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        if (btn.dataset.katAction === 'edit') editKategori(id);
        else if (btn.dataset.katAction === 'delete') hapusKategori(id);
    });

    document.getElementById('tabelRiwayat').addEventListener('click', (e) => {
        const retry = e.target.closest('button[data-retry="riwayat"]');
        if (retry) return muatRiwayat();
        const btn = e.target.closest('button[data-struk-id]');
        if (btn) bukaStruk(Number(btn.dataset.strukId));
    });

    document.getElementById('tabelKasir').addEventListener('click', (e) => {
        const retry = e.target.closest('button[data-retry="kasir"]');
        if (retry) return muatKasir();
        const btn = e.target.closest('button[data-user-action]');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        const action = btn.dataset.userAction;
        if (action === 'edit') editUser(id);
        else if (action === 'toggle') toggleKasirStatus(id, Number(btn.dataset.nextStatus));
        else if (action === 'reset') bukaResetPassword(id);
        else if (action === 'delete') hapusUser(id);
    });

    document.getElementById('toastContainer').addEventListener('click', (e) => {
        const closeBtn = e.target.closest('button[data-toast-close]');
        if (closeBtn) closeBtn.parentElement.remove();
    });

    // Klik backdrop luar modal untuk menutup
    [
        ['modalMenu', tutupModalMenu],
        ['modalKategori', tutupModalKategori],
        ['modalStruk', tutupStruk],
        ['modalKonfirmasi', () => tutupKonfirmasi(false)],
        ['modalUser', tutupModalUser],
        ['modalResetPassword', tutupModalResetPassword]
    ].forEach(([id, fn]) => {
        const el = document.getElementById(id);
        el.addEventListener('click', (e) => {
            if (e.target === el) fn();
        });
    });
}

pasangEventAdmin();
bukaTab('menu');
muatMenu();
muatKategori();
muatRiwayat();
