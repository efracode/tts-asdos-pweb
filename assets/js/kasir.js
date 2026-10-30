// Ini buat mengatur katalog, keranjang, pembayaran, dan struk kasir.

const API = 'index.php?route=';
const NAMA_KASIR = window.NAMA_KASIR;

let listMenu = [], listKategori = [], keranjang = [], filterKategoriDipilih = null;

const rupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

// Ini buat mencegah teks database dibaca sebagai kode HTML.
function escapeHTML(teks) {
    return String(teks).replace(/[&<>"']/g, karakter => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[karakter]);
}

// Ini buat nampilin pesan berhasil, gagal, atau informasi.
function tampilkanToast(pesan, tipe = 'sukses') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const config = {
        sukses: { bg: 'bg-emerald-600 text-white', icon: 'check-circle', border: 'border-emerald-500' },
        error:  { bg: 'bg-rose-600 text-white', icon: 'error-circle', border: 'border-rose-500' },
        info:   { bg: 'bg-slate-800 text-white', icon: 'info-circle', border: 'border-slate-700' }
    }[tipe] || { bg: 'bg-slate-800 text-white', icon: 'info-circle', border: 'border-slate-700' };

    toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl ${config.bg} ${config.border} border text-xs font-semibold animate-toast min-w-[280px] max-w-sm`;
    toast.innerHTML = `
        <span class="app-icon text-base shrink-0" data-icon="${config.icon}" aria-hidden="true"></span>
        <span class="flex-1">${escapeHTML(pesan)}</span>
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

// Ini buat ngambil list menu dan kategori dari server.
async function ambilData() {
    try {
        const [kat, menu] = await Promise.all([
            fetch(API + 'kategori&action=list').then(r => r.json()),
            fetch(API + 'menu&action=list').then(r => r.json())
        ]);
        listKategori = kat.data || [];
        listMenu = menu.data || [];
        renderFilterKategori();
        renderMenu();
    } catch (err) {
        tampilkanToast('Gagal muat katalog menu dari server.', 'error');
    }
}

// Ini buat nampilin tombol filter kategori.
function renderFilterKategori() {
    const tombol = [{ id: null, nama_kategori: 'Semua Menu' }, ...listKategori];
    document.getElementById('filterKategori').innerHTML = tombol.map(k => `
        <button onclick="pilihKategori(${k.id})"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all ${filterKategoriDipilih === k.id
                ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25'
                : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200/80'}">
            ${escapeHTML(k.nama_kategori)}
        </button>`).join('');
}

function pilihKategori(id) {
    filterKategoriDipilih = id;
    renderFilterKategori();
    renderMenu();
}

// Ini buat nampilin menu yang bisa dipilih kasir.
function renderMenu() {
    const q = document.getElementById('cariMenu').value.toLowerCase().trim();
    let data = listMenu;

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
                <span class="app-icon text-4xl mb-2 opacity-30 block" data-icon="utensils" aria-hidden="true"></span>
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
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">${escapeHTML(m.nama_kategori || 'Menu')}</span>
                <p class="font-extrabold text-xs lg:text-sm text-slate-900 group-hover:text-orange-600 transition-colors line-clamp-1">${escapeHTML(m.nama_menu)}</p>
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100">
                <span class="text-xs lg:text-sm font-extrabold text-orange-600">${rupiah(m.harga)}</span>
                <span class="w-7 h-7 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-xs group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <span class="app-icon" data-icon="plus" aria-hidden="true"></span>
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

// Ini buat ngatur item di keranjang.
function tambahKeranjang(id) {
    const menu = listMenu.find(m => m.id == id);
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
                <span class="app-icon text-3xl mb-2 opacity-30 block" data-icon="basket" aria-hidden="true"></span>
                <p class="text-xs font-medium">Keranjang masih kosong.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Pilih menu di samping untuk memesan.</p>
            </div>`;
        btnBayar.disabled = true;
    } else {
        wrap.innerHTML = keranjang.map(k => `
            <div class="flex items-center justify-between gap-3 bg-slate-50 border border-slate-200/80 rounded-xl p-2.5">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-900 truncate">${escapeHTML(k.nama)}</p>
                    <p class="text-[11px] text-slate-400">${rupiah(k.harga)} &times; ${k.qty} = <span class="font-bold text-slate-700">${rupiah(k.harga * k.qty)}</span></p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <button onclick="ubahQty(${k.id}, -1)" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs font-bold">-</button>
                    <span class="text-xs font-extrabold w-5 text-center text-slate-900">${k.qty}</span>
                    <button onclick="ubahQty(${k.id}, 1)" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 flex items-center justify-center text-xs font-bold">+</button>
                    <button onclick="hapusItemKeranjang(${k.id})" class="text-rose-400 hover:text-rose-600 ml-1 text-xs p-1" title="Hapus"><span class="app-icon" data-icon="trash" aria-hidden="true"></span></button>
                </div>
            </div>`).join('');
        btnBayar.disabled = false;
    }

    document.getElementById('totalItemCount').innerText = totalItemQty() + ' item';
    document.getElementById('totalBelanja').innerText = rupiah(totalBelanja());
}

// Ini buat ngitung pembayaran dan kembalian.
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

// Ini buat ngirim transaksi ke server.
async function prosesBayar() {
    const bayar = Number(document.getElementById('inputBayar').value) || 0;
    const total = totalBelanja();

    if (bayar < total) {
        tampilkanToast('Uang pembayaran masih kurang dari total belanja!', 'error');
        return;
    }

    const btn = document.getElementById('btnProsesBayar');
    const btnTeks = document.getElementById('btnProsesBayarTeks');

    // Ini buat mencegah tombol checkout ditekan berkali-kali.
    btn.disabled = true;
    const originalText = btnTeks.innerText;
    btnTeks.innerHTML = '<span class="app-icon mr-1.5 is-spinning" data-icon="spinner" aria-hidden="true"></span>Memproses Transaksi...';

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
            tampilkanToast(hasil.message || 'Gagal nyimpen transaksi.', 'error');
        }
    } catch (err) {
        tampilkanToast('Terjadi kesalahan jaringan saat checkout.', 'error');
    } finally {
        btn.disabled = false;
        btnTeks.innerText = originalText;
    }
}

// Ini buat nampilin dan mencetak struk.
function tampilkanStruk(data, bayar, itemBeli) {
    const itemsHtml = itemBeli.map(k => `
        <div class="flex justify-between text-xs py-1">
            <span>${escapeHTML(k.nama)} <span class="text-slate-400">x${k.qty}</span></span>
            <span class="font-bold">${rupiah(k.harga * k.qty)}</span>
        </div>`).join('');

    document.getElementById('areaStruk').innerHTML = `
        <div class="text-center border-b border-dashed border-slate-300 pb-3 mb-3">
            <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mx-auto mb-1 text-sm font-bold">
                <span class="app-icon" data-icon="utensils" aria-hidden="true"></span>
            </div>
            <p class="font-extrabold text-slate-900 text-sm tracking-tight">KASIR RESTORAN</p>
            <p class="text-[11px] text-slate-500 font-mono mt-0.5">${escapeHTML(data.no_transaksi)}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">${new Date().toLocaleString('id-ID')} &bull; Kasir: ${escapeHTML(NAMA_KASIR)}</p>
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

let konfirmasiLogoutSelesai = null;

function mintaKonfirmasiLogout() {
    document.getElementById('modalKonfirmasiLogout').classList.remove('hidden');
    return new Promise((resolve) => {
        konfirmasiLogoutSelesai = resolve;
    });
}

function tutupKonfirmasiLogout(jadiLogout) {
    document.getElementById('modalKonfirmasiLogout').classList.add('hidden');
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

// Ini buat muat tampilan awal halaman.
ambilData();
renderKeranjang();
