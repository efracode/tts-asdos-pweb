// Ini buat mengatur katalog bergambar, keranjang, pembayaran, riwayat, animasi dialog, dan struk kasir.

const API = 'index.php?route=';
const NAMA_KASIR = document.body.dataset.namaKasir || 'Kasir';

let listMenu = [];
let listKategori = [];
let listRiwayatKasir = [];
let keranjang = [];
let filterKategoriDipilih = null;
let statusKatalog = 'loading';
let pesanErrorKatalog = '';
let releaseFocusTrapAktif = null;
let elemenPemanggilDialog = null;
const timerTutupModal = new WeakMap();

const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

function escapeHTML(teks) {
    return String(teks).replace(/[&<>"']/g, karakter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[karakter]);
}

function inisialMenu(nama) {
    const kata = String(nama || 'M').trim().split(/\s+/);
    if (kata.length >= 2) {
        return (kata[0][0] + kata[1][0]).toUpperCase();
    }
    return String(nama || 'MN').slice(0, 2).toUpperCase();
}

const KATEGORI_AKSEN = [
    { tileBg: 'bg-orange-50', tileText: 'text-orange-800', stripe: 'border-t-orange-700', tagBg: 'bg-orange-100 text-orange-900' },
    { tileBg: 'bg-slate-100', tileText: 'text-slate-800', stripe: 'border-t-slate-800', tagBg: 'bg-slate-200 text-slate-800' },
    { tileBg: 'bg-amber-50', tileText: 'text-amber-900', stripe: 'border-t-amber-700', tagBg: 'bg-amber-100 text-amber-900' },
    { tileBg: 'bg-emerald-50', tileText: 'text-emerald-800', stripe: 'border-t-emerald-700', tagBg: 'bg-emerald-100 text-emerald-900' }
];

function gayaKategori(idKategori) {
    const idx = listKategori.findIndex(k => k.id == idKategori);
    const aman = idx >= 0 ? idx % KATEGORI_AKSEN.length : 0;
    return KATEGORI_AKSEN[aman];
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

// Helper animasi buka & tutup modal / sheet
function bukaAnimasiEl(el) {
    const t = timerTutupModal.get(el);
    if (t) {
        clearTimeout(t);
        timerTutupModal.delete(el);
    }
    el.classList.remove('hidden');
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            el.classList.add('is-open');
        });
    });
}

function tutupAnimasiEl(el, callbackSelesai = null) {
    const t = timerTutupModal.get(el);
    if (t) clearTimeout(t);

    el.classList.remove('is-open');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const jeda = reduced ? 0 : 220;

    const tid = setTimeout(() => {
        el.classList.add('hidden');
        timerTutupModal.delete(el);
        if (callbackSelesai) callbackSelesai();
    }, jeda);
    timerTutupModal.set(el, tid);
}

// Focus trap aksesibel untuk semua modal & mobile drawer
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

// Ambil katalog menu & kategori
async function ambilData() {
    statusKatalog = 'loading';
    pesanErrorKatalog = '';
    renderMenu();

    try {
        const [resKat, resMenu] = await Promise.all([
            fetch(API + 'kategori&action=list'),
            fetch(API + 'menu&action=list')
        ]);
        const [kat, menu] = await Promise.all([resKat.json(), resMenu.json()]);

        if (!resKat.ok || !resMenu.ok || !kat.success || !menu.success) {
            throw new Error(menu.message || kat.message || 'Gagal memuat katalog menu.');
        }

        listKategori = kat.data || [];
        listMenu = menu.data || [];
        statusKatalog = 'ready';
        renderFilterKategori();
        renderMenu();
    } catch (err) {
        statusKatalog = 'error';
        pesanErrorKatalog = err.message || 'Gagal terhubung ke server saat memuat menu.';
        renderMenu();
        tampilkanToast(pesanErrorKatalog, 'error');
    }
}

function renderFilterKategori() {
    const hitungByKat = {};
    listMenu.forEach(m => {
        hitungByKat[m.id_kategori] = (hitungByKat[m.id_kategori] || 0) + 1;
    });

    const tombol = [
        { id: '', nama_kategori: 'Semua Menu', jumlah: listMenu.length },
        ...listKategori.map(k => ({ ...k, jumlah: hitungByKat[k.id] || 0 }))
    ];

    document.getElementById('filterKategori').innerHTML = tombol.map(k => {
        const aktif = (filterKategoriDipilih === null && k.id === '') || (filterKategoriDipilih !== null && String(filterKategoriDipilih) === String(k.id));
        return `
        <button type="button" data-kat-id="${escapeHTML(k.id)}" aria-pressed="${aktif}"
            class="min-h-[44px] px-3.5 py-2 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1.5 ${aktif
                ? 'bg-orange-700 text-white'
                : 'bg-slate-100 text-slate-800 hover:bg-slate-200 border border-slate-300'}">
            <span>${escapeHTML(k.nama_kategori)}</span>
            <span class="tabular-nums px-1.5 py-0.5 rounded text-[11px] ${aktif ? 'bg-orange-800 text-white' : 'bg-white text-slate-700 border border-slate-300'}">${k.jumlah}</span>
        </button>`;
    }).join('');
}

function pilihKategori(id) {
    filterKategoriDipilih = id === '' ? null : Number(id);
    renderFilterKategori();
    renderMenu();
}

function resetFilterMenu() {
    filterKategoriDipilih = null;
    document.getElementById('cariMenu').value = '';
    document.getElementById('btnClearSearch').classList.add('hidden');
    renderFilterKategori();
    renderMenu();
}

function renderMenu() {
    const grid = document.getElementById('gridMenu');

    if (statusKatalog === 'loading') {
        grid.innerHTML = `
            <div class="col-span-full py-16 text-center bg-white rounded-xl border border-slate-200 p-6">
                <span class="app-icon text-3xl text-orange-700 is-spinning block mx-auto mb-3" data-icon="spinner" aria-hidden="true"></span>
                <p class="text-sm font-bold text-slate-900">Memuat daftar menu...</p>
                <p class="text-xs text-slate-600 mt-1">Mengambil data produk dan ketersediaan stok terbaru.</p>
            </div>`;
        return;
    }

    if (statusKatalog === 'error') {
        grid.innerHTML = `
            <div class="col-span-full py-12 text-center bg-rose-50 rounded-xl border border-rose-300 p-6">
                <span class="app-icon text-3xl text-rose-700 block mx-auto mb-2" data-icon="error-circle" aria-hidden="true"></span>
                <p class="text-sm font-extrabold text-rose-900">Gagal Memuat Menu</p>
                <p class="text-xs text-rose-800 mt-1 mb-4">${escapeHTML(pesanErrorKatalog)}</p>
                <button type="button" data-action="reload-menu" class="min-h-[44px] px-4 py-2 rounded-lg bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold">
                    Muat Ulang Menu
                </button>
            </div>`;
        return;
    }

    const q = document.getElementById('cariMenu').value.toLowerCase().trim();
    let data = listMenu;

    if (filterKategoriDipilih !== null) {
        data = data.filter(m => m.id_kategori == filterKategoriDipilih);
    }
    if (q !== '') {
        data = data.filter(m =>
            m.nama_menu.toLowerCase().includes(q) ||
            (m.nama_kategori && m.nama_kategori.toLowerCase().includes(q))
        );
    }

    if (!data.length) {
        const kosongTotal = listMenu.length === 0;
        grid.innerHTML = `
            <div class="col-span-full py-14 text-center bg-white rounded-xl border border-slate-200 p-6">
                <span class="app-icon text-4xl text-slate-500 block mx-auto mb-2" data-icon="utensils" aria-hidden="true"></span>
                <p class="text-sm font-extrabold text-slate-900">${kosongTotal ? 'Belum ada menu tersedia' : 'Menu tidak ditemukan'}</p>
                <p class="text-xs text-slate-600 mt-1 mb-4">
                    ${kosongTotal ? 'Minta Admin menambahkan menu terlebih dahulu di halaman Backoffice.' : 'Tidak ada menu yang sesuai dengan kata kunci atau kategori yang dipilih.'}
                </p>
                ${kosongTotal ? '' : '<button type="button" data-action="reset-filter" class="min-h-[44px] px-4 py-2 rounded-lg bg-slate-900 hover:bg-black text-white text-xs font-bold">Tampilkan Semua Menu</button>'}
            </div>`;
        return;
    }

    grid.innerHTML = data.map(m => {
        const habis = Number(m.stok) <= 0;
        const tipis = !habis && Number(m.stok) <= 5;
        const itemKeranjang = keranjang.find(k => k.id == m.id);
        const qtyDipilih = itemKeranjang ? itemKeranjang.qty : 0;
        const aksen = gayaKategori(m.id_kategori);
        const punyaFoto = Boolean(m.gambar);

        const stokBadgeClass = habis
            ? 'bg-rose-700 text-white'
            : (tipis ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-slate-900 text-white');
        const stokTeks = habis ? 'Habis' : (tipis ? `Sisa ${m.stok}` : `Stok ${m.stok}`);

        return `
        <button type="button" data-menu-id="${m.id}" ${habis ? 'disabled' : ''}
            class="bg-white rounded-xl text-left border ${qtyDipilih > 0 ? 'border-orange-700 ring-1 ring-orange-700' : 'border-slate-200'} border-t-4 ${aksen.stripe} hover:border-orange-700 active:bg-slate-50 transition-colors flex flex-col justify-between group disabled:opacity-55 relative overflow-hidden">

            <div class="relative w-full aspect-[16/10] bg-slate-100 border-b border-slate-200 overflow-hidden">
                ${punyaFoto ? `
                    <img src="${escapeHTML(m.gambar)}" alt="${escapeHTML(m.nama_menu)}" width="320" height="200" loading="lazy"
                         class="menu-thumb-img w-full h-full object-cover">
                    <div class="hidden w-full h-full ${aksen.tileBg} flex items-center justify-center">
                        <span class="text-2xl font-extrabold tracking-tight ${aksen.tileText}">${escapeHTML(inisialMenu(m.nama_menu))}</span>
                    </div>
                ` : `
                    <div class="w-full h-full ${aksen.tileBg} flex flex-col items-center justify-center px-2">
                        <span class="text-2xl font-extrabold tracking-tight ${aksen.tileText}">${escapeHTML(inisialMenu(m.nama_menu))}</span>
                    </div>
                `}

                <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[11px] font-bold tabular-nums ${stokBadgeClass}">
                    ${stokTeks}
                </span>

                ${qtyDipilih > 0 ? `
                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded bg-orange-700 text-white text-xs font-extrabold tabular-nums">
                        ${qtyDipilih}x
                    </span>
                ` : ''}
            </div>

            <div class="p-3 flex-1 flex flex-col justify-between w-full">
                <div>
                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold ${aksen.tagBg} mb-1.5">
                        ${escapeHTML(m.nama_kategori || 'Menu')}
                    </span>
                    <p class="font-bold text-sm text-slate-900 group-hover:text-orange-700 leading-snug line-clamp-2">
                        ${escapeHTML(m.nama_menu)}
                    </p>
                </div>
                <div class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-slate-100">
                    <span class="text-sm font-extrabold text-orange-700 tabular-nums">${rupiah(m.harga)}</span>
                    <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-800 group-hover:bg-orange-700 group-hover:text-white flex items-center justify-center text-xs transition-colors shrink-0" aria-hidden="true">
                        <span class="app-icon" data-icon="plus"></span>
                    </span>
                </div>
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
    document.getElementById('cariMenu').focus();
    renderMenu();
}

// Kelola keranjang pesanan
function tambahKeranjang(id) {
    const menu = listMenu.find(m => m.id == id);
    if (!menu) return;

    const item = keranjang.find(k => k.id == id);
    const qtySaatIni = item ? item.qty : 0;

    if (qtySaatIni >= Number(menu.stok)) {
        tampilkanToast(`Stok untuk "${menu.nama_menu}" tidak mencukupi (sisa ${menu.stok}).`, 'error');
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
            gambar: menu.gambar || null,
            qty: 1
        });
    }
    renderKeranjang();
    renderMenu();
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
    renderMenu();
}

function hapusItemKeranjang(id) {
    keranjang = keranjang.filter(k => k.id != id);
    renderKeranjang();
    renderMenu();
}

function kosongkanKeranjang() {
    if (!keranjang.length) return;
    keranjang = [];
    renderKeranjang();
    renderMenu();
    tampilkanToast('Keranjang telah dikosongkan.', 'info');
}

const totalBelanja = () => keranjang.reduce((total, k) => total + (k.harga * k.qty), 0);
const totalItemQty = () => keranjang.reduce((total, k) => total + k.qty, 0);

function renderKeranjang() {
    const wrap = document.getElementById('isiKeranjang');
    const btnBayar = document.getElementById('tombolBayar');
    const btnReset = document.getElementById('btnResetKeranjang');

    if (!keranjang.length) {
        wrap.innerHTML = `
            <div class="py-12 text-center text-slate-600">
                <span class="app-icon text-3xl mb-2 opacity-60 block" data-icon="basket" aria-hidden="true"></span>
                <p class="text-sm font-bold text-slate-800">Keranjang masih kosong</p>
                <p class="text-xs text-slate-600 mt-1">Ketuk kartu menu untuk menambahkan pesanan.</p>
            </div>`;
        btnBayar.disabled = true;
        btnReset.disabled = true;
    } else {
        wrap.innerHTML = keranjang.map(k => `
            <div class="flex items-center justify-between gap-2.5 bg-slate-50 border border-slate-200 rounded-lg p-2.5">
                <div class="w-10 h-10 rounded-md overflow-hidden border border-slate-200 bg-white shrink-0 flex items-center justify-center">
                    ${k.gambar
                        ? `<img src="${escapeHTML(k.gambar)}" alt="" width="40" height="40" class="w-10 h-10 object-cover">`
                        : `<span class="text-xs font-extrabold text-slate-700">${escapeHTML(inisialMenu(k.nama))}</span>`}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-extrabold text-slate-900 truncate">${escapeHTML(k.nama)}</p>
                    <p class="text-xs text-slate-600 tabular-nums">${rupiah(k.harga)} &times; ${k.qty} = <span class="font-bold text-slate-900">${rupiah(k.harga * k.qty)}</span></p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" data-cart-action="dec" data-id="${k.id}" aria-label="Kurangi jumlah ${escapeHTML(k.nama)}"
                            class="w-11 h-11 rounded-md bg-white border border-slate-300 text-slate-800 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">-</button>
                    <span class="text-xs font-extrabold w-6 text-center text-slate-900 tabular-nums">${k.qty}</span>
                    <button type="button" data-cart-action="inc" data-id="${k.id}" aria-label="Tambah jumlah ${escapeHTML(k.nama)}"
                            class="w-11 h-11 rounded-md bg-white border border-slate-300 text-slate-800 hover:bg-slate-100 flex items-center justify-center text-sm font-bold">+</button>
                    <button type="button" data-cart-action="del" data-id="${k.id}" aria-label="Hapus ${escapeHTML(k.nama)} dari pesanan"
                            class="w-11 h-11 rounded-md text-rose-700 hover:bg-rose-50 flex items-center justify-center text-xs">
                        <span class="app-icon" data-icon="trash" aria-hidden="true"></span>
                    </button>
                </div>
            </div>`).join('');
        btnBayar.disabled = false;
        btnReset.disabled = false;
    }

    const jml = totalItemQty();
    const ttl = totalBelanja();
    document.getElementById('totalItemCount').innerText = jml + ' item';
    document.getElementById('totalBelanja').innerText = rupiah(ttl);
    document.getElementById('mobileCartCount').innerText = jml + ' item dipilih';
    document.getElementById('mobileCartTotal').innerText = rupiah(ttl);
}

// Drawer keranjang untuk mobile/tablet (< lg) dengan animasi buka/tutup
function bukaKeranjangMobile() {
    const panel = document.getElementById('panelKeranjang');
    const backdrop = document.getElementById('mobileCartBackdrop');
    panel.classList.add('flex');
    bukaAnimasiEl(backdrop);
    bukaAnimasiEl(panel);
    pasangFocusTrap(panel, tutupKeranjangMobile);
}

function tutupKeranjangMobile() {
    const panel = document.getElementById('panelKeranjang');
    const backdrop = document.getElementById('mobileCartBackdrop');
    tutupAnimasiEl(backdrop);
    tutupAnimasiEl(panel, () => {
        panel.classList.remove('flex');
    });
    lepasFocusTrap();
}

// Pembayaran & kembalian
function bukaBayar() {
    if (!keranjang.length) return;
    if (window.innerWidth < 1024) {
        const panel = document.getElementById('panelKeranjang');
        const backdrop = document.getElementById('mobileCartBackdrop');
        backdrop.classList.remove('is-open');
        backdrop.classList.add('hidden');
        panel.classList.remove('is-open', 'flex');
        panel.classList.add('hidden');
    }
    const inputBayar = document.getElementById('inputBayar');
    inputBayar.value = '';
    document.getElementById('infoTotal').innerText = rupiah(totalBelanja());
    hitungKembalian();
    const modal = document.getElementById('modalBayar');
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, tutupBayar, inputBayar);
}

function tutupBayar() {
    tutupAnimasiEl(document.getElementById('modalBayar'));
    lepasFocusTrap();
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
        infoEl.className = 'text-lg font-extrabold text-slate-900 tabular-nums';
        statusEl.innerText = 'Masukkan uang pembayaran';
        statusEl.className = 'text-xs text-slate-600';
    } else if (selisih < 0) {
        infoEl.innerText = 'Kurang ' + rupiah(Math.abs(selisih));
        infoEl.className = 'text-lg font-extrabold text-rose-700 tabular-nums';
        statusEl.innerText = 'Uang bayar belum mencukupi';
        statusEl.className = 'text-xs text-rose-700 font-bold';
    } else if (selisih === 0) {
        infoEl.innerText = 'Uang Pas';
        infoEl.className = 'text-lg font-extrabold text-emerald-700';
        statusEl.innerText = 'Tidak ada kembalian';
        statusEl.className = 'text-xs text-emerald-700 font-bold';
    } else {
        infoEl.innerText = rupiah(selisih);
        infoEl.className = 'text-lg font-extrabold text-emerald-700 tabular-nums';
        statusEl.innerText = 'Kembalian yang harus diberikan';
        statusEl.className = 'text-xs text-emerald-700 font-bold';
    }
}

async function prosesBayar() {
    const bayar = Number(document.getElementById('inputBayar').value) || 0;
    const total = totalBelanja();

    if (bayar < total) {
        tampilkanToast('Uang pembayaran masih kurang dari total belanja!', 'error');
        document.getElementById('inputBayar').focus();
        return;
    }

    const btn = document.getElementById('btnProsesBayar');
    const btnTeks = document.getElementById('btnProsesBayarTeks');
    btn.disabled = true;
    const originalText = btnTeks.innerText;
    btnTeks.innerHTML = '<span class="app-icon mr-1.5 is-spinning" data-icon="spinner" aria-hidden="true"></span>Menyimpan...';

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
            const itemStruk = keranjang.map(k => ({ ...k }));
            tutupBayar();
            tampilkanStruk({
                no_transaksi: hasil.data.no_transaksi,
                total: hasil.data.total,
                bayar: bayar,
                kembalian: hasil.data.kembalian,
                waktu: new Date().toLocaleString('id-ID'),
                kasir: NAMA_KASIR
            }, itemStruk);
            tampilkanToast('Transaksi berhasil disimpan!', 'sukses');
            keranjang = [];
            renderKeranjang();
            ambilData();
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

function tampilkanStruk(head, itemBeli) {
    const itemsHtml = itemBeli.map(k => `
        <div class="flex justify-between text-xs py-1 gap-2">
            <span>${escapeHTML(k.nama)} <span class="text-slate-600">x${k.qty}</span></span>
            <span class="font-bold tabular-nums shrink-0">${rupiah(k.subtotal !== undefined ? k.subtotal : k.harga * k.qty)}</span>
        </div>`).join('');

    document.getElementById('areaStruk').innerHTML = `
        <div class="text-center border-b border-dashed border-slate-300 pb-3 mb-3">
            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center mx-auto mb-1 text-sm font-bold">
                <span class="app-icon" data-icon="utensils" aria-hidden="true"></span>
            </div>
            <p class="font-extrabold text-slate-900 text-sm tracking-tight">KASIR RESTORAN</p>
            <p class="text-xs text-slate-700 font-mono mt-0.5">${escapeHTML(head.no_transaksi)}</p>
            <p class="text-[11px] text-slate-600 mt-0.5">${escapeHTML(head.waktu)} &bull; Kasir: ${escapeHTML(head.kasir)}</p>
        </div>
        <div class="divide-y divide-dashed divide-slate-200">${itemsHtml}</div>
        <div class="border-t border-dashed border-slate-300 mt-3 pt-3 text-xs space-y-1.5 font-medium">
            <div class="flex justify-between font-extrabold text-sm text-slate-900"><span>Total Belanja</span><span class="tabular-nums">${rupiah(head.total)}</span></div>
            <div class="flex justify-between text-slate-700"><span>Tunai Diterima</span><span class="tabular-nums">${rupiah(head.bayar)}</span></div>
            <div class="flex justify-between text-emerald-700 font-bold"><span>Kembalian</span><span class="tabular-nums">${rupiah(head.kembalian)}</span></div>
        </div>
        <p class="text-center text-[11px] text-slate-600 mt-4 italic">Terima kasih atas kunjungan Anda!</p>`;

    const modal = document.getElementById('modalStruk');
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, tutupStruk);
}

function tutupStruk() {
    tutupAnimasiEl(document.getElementById('modalStruk'));
    lepasFocusTrap();
}

// Riwayat transaksi terbaru di kasir
async function bukaRiwayatKasir() {
    const modal = document.getElementById('modalRiwayatKasir');
    const isi = document.getElementById('isiRiwayatKasir');
    isi.innerHTML = `
        <div class="py-10 text-center">
            <span class="app-icon text-2xl text-orange-700 is-spinning block mx-auto mb-2" data-icon="spinner" aria-hidden="true"></span>
            <p class="text-xs font-bold text-slate-700">Memuat riwayat transaksi...</p>
        </div>`;
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, tutupRiwayatKasir);

    try {
        const res = await fetch(API + 'transaksi&action=riwayat&limit=20');
        const json = await res.json();
        if (!res.ok || !json.success) {
            throw new Error(json.message || 'Gagal memuat riwayat.');
        }

        listRiwayatKasir = json.data || [];
        if (!listRiwayatKasir.length) {
            isi.innerHTML = `
                <div class="py-10 text-center text-slate-600">
                    <span class="app-icon text-3xl mb-2 block opacity-60" data-icon="receipt" aria-hidden="true"></span>
                    <p class="text-sm font-bold text-slate-900">Belum ada transaksi tercatat</p>
                    <p class="text-xs text-slate-600 mt-1">Transaksi yang selesai akan muncul di daftar ini.</p>
                </div>`;
            return;
        }

        isi.innerHTML = `
            <div class="divide-y divide-slate-200">
                ${listRiwayatKasir.map(t => `
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-mono font-extrabold text-slate-900">${escapeHTML(t.no_transaksi)}</p>
                            <p class="text-xs text-slate-600">${escapeHTML(t.tanggal)} &bull; ${escapeHTML(t.nama_kasir)}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-sm font-extrabold text-slate-900 tabular-nums">${rupiah(t.total)}</span>
                            <button type="button" data-riwayat-id="${t.id}" class="min-h-[44px] px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-xs font-bold text-slate-800">
                                Struk
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>`;
    } catch (err) {
        isi.innerHTML = `
            <div class="py-8 text-center bg-rose-50 border border-rose-300 rounded-lg p-4">
                <p class="text-xs font-bold text-rose-800 mb-3">${escapeHTML(err.message || 'Gagal memuat riwayat transaksi.')}</p>
                <button type="button" data-action="reload-riwayat" class="min-h-[44px] px-4 py-2 rounded-lg bg-rose-700 text-white text-xs font-bold">Coba Lagi</button>
            </div>`;
    }
}

function tutupRiwayatKasir() {
    tutupAnimasiEl(document.getElementById('modalRiwayatKasir'));
    lepasFocusTrap();
}

async function cetakUlangDariKasir(id) {
    const head = listRiwayatKasir.find(t => t.id == id);
    if (!head) return;
    try {
        const res = await fetch(API + 'transaksi&action=detail&id=' + head.id);
        const json = await res.json();
        if (!res.ok || !json.success) {
            tampilkanToast(json.message || 'Gagal memuat detail struk.', 'error');
            return;
        }
        tutupRiwayatKasir();
        setTimeout(() => {
            tampilkanStruk({
                no_transaksi: head.no_transaksi,
                total: head.total,
                bayar: head.bayar,
                kembalian: head.kembalian,
                waktu: head.tanggal,
                kasir: head.nama_kasir
            }, json.data.map(d => ({
                nama: d.nama_menu,
                harga: d.harga,
                qty: d.qty,
                subtotal: d.subtotal
            })));
        }, 230);
    } catch (err) {
        tampilkanToast('Gagal memuat detail struk.', 'error');
    }
}

// Konfirmasi Logout
let konfirmasiLogoutSelesai = null;

function mintaKonfirmasiLogout() {
    const modal = document.getElementById('modalKonfirmasiLogout');
    bukaAnimasiEl(modal);
    pasangFocusTrap(modal, () => tutupKonfirmasiLogout(false));
    return new Promise((resolve) => {
        konfirmasiLogoutSelesai = resolve;
    });
}

function tutupKonfirmasiLogout(jadiLogout) {
    tutupAnimasiEl(document.getElementById('modalKonfirmasiLogout'));
    lepasFocusTrap();
    if (konfirmasiLogoutSelesai) {
        konfirmasiLogoutSelesai(jadiLogout);
        konfirmasiLogoutSelesai = null;
    }
}

async function logout() {
    const yakin = await mintaKonfirmasiLogout();
    if (!yakin) return;

    try {
        const respon = await fetch(API + 'auth&action=logout', { method: 'POST' });
        if (!respon.ok) throw new Error('Logout gagal');
        location.href = 'login.php';
    } catch (error) {
        tampilkanToast('Logout gagal. Silakan coba lagi.', 'error');
    }
}

// Binding seluruh event DOM tanpa inline JS di view
function pasangEventKasir() {
    document.getElementById('btnRiwayatKasir').addEventListener('click', bukaRiwayatKasir);
    document.getElementById('btnLogoutKasir').addEventListener('click', logout);
    document.getElementById('cariMenu').addEventListener('input', cariMenuKasir);
    document.getElementById('btnClearSearch').addEventListener('click', clearSearch);
    document.getElementById('btnMuatUlangMenu').addEventListener('click', ambilData);

    document.getElementById('btnResetKeranjang').addEventListener('click', kosongkanKeranjang);
    document.getElementById('btnTutupKeranjangMobile').addEventListener('click', tutupKeranjangMobile);
    document.getElementById('mobileCartBackdrop').addEventListener('click', tutupKeranjangMobile);
    document.getElementById('btnBukaKeranjangMobile').addEventListener('click', bukaKeranjangMobile);
    document.getElementById('tombolBayar').addEventListener('click', bukaBayar);

    document.getElementById('btnCloseModalBayar').addEventListener('click', tutupBayar);
    document.getElementById('btnBatalBayar').addEventListener('click', tutupBayar);
    document.getElementById('btnProsesBayar').addEventListener('click', prosesBayar);
    document.getElementById('inputBayar').addEventListener('input', hitungKembalian);
    document.getElementById('btnNominalPas').addEventListener('click', setNominalPas);

    document.querySelectorAll('.btn-nominal-set').forEach(btn => {
        btn.addEventListener('click', () => setNominal(Number(btn.dataset.nominalSet)));
    });
    document.querySelectorAll('.btn-nominal-add').forEach(btn => {
        btn.addEventListener('click', () => tambahNominal(Number(btn.dataset.nominalAdd)));
    });

    document.getElementById('btnCloseRiwayatKasir').addEventListener('click', tutupRiwayatKasir);
    document.getElementById('btnBatalLogoutKasir').addEventListener('click', () => tutupKonfirmasiLogout(false));
    document.getElementById('btnYaLogoutKasir').addEventListener('click', () => tutupKonfirmasiLogout(true));
    document.getElementById('btnTutupStrukKasir').addEventListener('click', tutupStruk);
    document.getElementById('btnCetakStrukKasir').addEventListener('click', () => window.print());

    // Delegasi untuk elemen dinamis (kategori, menu, fallback foto, keranjang, riwayat, toast)
    document.getElementById('filterKategori').addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-kat-id]');
        if (btn) pilihKategori(btn.dataset.katId);
    });

    const gridMenu = document.getElementById('gridMenu');
    gridMenu.addEventListener('click', (e) => {
        const reloadBtn = e.target.closest('button[data-action="reload-menu"]');
        if (reloadBtn) return ambilData();
        const resetBtn = e.target.closest('button[data-action="reset-filter"]');
        if (resetBtn) return resetFilterMenu();
        const cardBtn = e.target.closest('button[data-menu-id]');
        if (cardBtn) tambahKeranjang(Number(cardBtn.dataset.menuId));
    });
    gridMenu.addEventListener('error', (e) => {
        if (e.target && e.target.tagName === 'IMG') {
            e.target.classList.add('hidden');
            if (e.target.nextElementSibling) e.target.nextElementSibling.classList.remove('hidden');
        }
    }, true);

    document.getElementById('isiKeranjang').addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-cart-action]');
        if (!btn) return;
        const id = Number(btn.dataset.id);
        const action = btn.dataset.cartAction;
        if (action === 'dec') ubahQty(id, -1);
        else if (action === 'inc') ubahQty(id, 1);
        else if (action === 'del') hapusItemKeranjang(id);
    });

    document.getElementById('isiRiwayatKasir').addEventListener('click', (e) => {
        const retryBtn = e.target.closest('button[data-action="reload-riwayat"]');
        if (retryBtn) return bukaRiwayatKasir();
        const strukBtn = e.target.closest('button[data-riwayat-id]');
        if (strukBtn) cetakUlangDariKasir(Number(strukBtn.dataset.riwayatId));
    });

    document.getElementById('toastContainer').addEventListener('click', (e) => {
        const closeBtn = e.target.closest('button[data-toast-close]');
        if (closeBtn) closeBtn.parentElement.remove();
    });

    // Klik area gelap di luar kotak modal untuk menutup
    [
        ['modalBayar', tutupBayar],
        ['modalRiwayatKasir', tutupRiwayatKasir],
        ['modalStruk', tutupStruk],
        ['modalKonfirmasiLogout', () => tutupKonfirmasiLogout(false)]
    ].forEach(([id, fn]) => {
        const el = document.getElementById(id);
        el.addEventListener('click', (e) => {
            if (e.target === el) fn();
        });
    });

    // Pintasan keyboard '/' untuk fokus pencarian menu cepat
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
            const adaDialogTerbuka = !document.getElementById('modalBayar').classList.contains('hidden')
                || !document.getElementById('modalStruk').classList.contains('hidden')
                || !document.getElementById('modalRiwayatKasir').classList.contains('hidden')
                || !document.getElementById('modalKonfirmasiLogout').classList.contains('hidden');
            if (!adaDialogTerbuka) {
                e.preventDefault();
                document.getElementById('cariMenu').focus();
            }
        }
    });
}

pasangEventKasir();
ambilData();
renderKeranjang();
