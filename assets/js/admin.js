// Ini buat mengatur tombol, data, dan tabel pada halaman admin.

const API = 'index.php?route=';
const MAX_INT = 2147483647;

let listMenu = [], listKategori = [], listRiwayat = [], listKasir = [];
let callbackKonfirmasi = null;

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

// Ini buat minta konfirmasi sebelum menjalankan aksi.
function mintaKonfirmasi(judul, pesan, teksAksi = 'Ya, Hapus') {
    return new Promise((resolve) => {
        document.getElementById('judulKonfirmasi').innerText = judul;
        document.getElementById('pesanKonfirmasi').innerText = pesan;
        document.getElementById('btnAksiKonfirmasi').innerText = teksAksi;
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

// Ini buat berpindah bagian pada halaman admin.
function bukaTab(nama) {
    ['menu', 'kategori', 'riwayat', 'kasir'].forEach(t => {
        const sec = document.getElementById('sec-' + t);
        const tab = document.getElementById('tab-' + t);
        if (!sec || !tab) return;
        const activeColor = t === 'kasir' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/20' : 'bg-orange-500 text-white shadow-sm shadow-orange-500/20';
        if (t === nama) {
            sec.classList.remove('hidden');
            tab.className = 'px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 ' + activeColor;
            if (t === 'kasir' && !listKasir.length) muatKasir();
        } else {
            sec.classList.add('hidden');
            tab.className = 'px-4 py-2.5 rounded-xl text-xs lg:text-sm font-semibold transition-all flex items-center gap-2 bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80';
        }
    });
}

// Ini buat ngambil data admin dari server.
async function muatMenu() {
    try {
        const r = await fetch(API + 'menu&action=list').then(res => res.json());
        listMenu = r.data || [];
        renderMenu();
        updateStats();
    } catch (err) {
        tampilkanToast('Gagal muat data menu', 'error');
    }
}

async function muatKategori() {
    try {
        const r = await fetch(API + 'kategori&action=list').then(res => res.json());
        listKategori = r.data || [];
        renderKategori();
        renderSelectKategori();
        updateStats();
    } catch (err) {
        tampilkanToast('Gagal muat data kategori', 'error');
    }
}

async function muatRiwayat() {
    try {
        const r = await fetch(API + 'transaksi&action=riwayat').then(res => res.json());
        listRiwayat = r.data || [];
        renderRiwayat();
        updateStats();
    } catch (err) {
        tampilkanToast('Gagal muat riwayat transaksi', 'error');
    }
}

function updateStats() {
    document.getElementById('statMenu').innerText = listMenu.length;
    document.getElementById('statKategori').innerText = listKategori.length;
    document.getElementById('statTransaksi').innerText = listRiwayat.length;
    const totalOmset = listRiwayat
        .filter(r => r.status === 'selesai')
        .reduce((sum, r) => sum + Number(r.total), 0);
    document.getElementById('statOmset').innerText = rupiah(totalOmset);
}

// Ini buat nampilin list menu di tabel.
function renderMenu(data = listMenu) {
    const tbody = document.getElementById('tabelMenu');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-slate-400 py-10">
            <span class="app-icon text-3xl mb-2 block opacity-40" data-icon="box-open" aria-hidden="true"></span>Tidak ada data menu ditemukan.
        </td></tr>`;
        return;
    }
    tbody.innerHTML = data.map(m => `
        <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3 px-4">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-slate-900">${escapeHTML(m.nama_menu)}</span>
                </div>
            </td>
            <td class="py-3 px-4">
                <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    ${escapeHTML(m.nama_kategori || 'Tanpa Kategori')}
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
                        <span class="app-icon" data-icon="edit" aria-hidden="true"></span>
                    </button>
                    <button onclick="hapusMenu(${m.id})" class="p-2 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Menu">
                        <span class="app-icon" data-icon="trash" aria-hidden="true"></span>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

function filterMenu() {
    const q = document.getElementById('cariMenu').value.toLowerCase().trim();
    const filtered = listMenu.filter(m =>
        m.nama_menu.toLowerCase().includes(q) ||
        (m.nama_kategori && m.nama_kategori.toLowerCase().includes(q))
    );
    renderMenu(filtered);
}

// Ini buat nampilin list kategori di tabel.
function renderKategori(data = listKategori) {
    const tbody = document.getElementById('tabelKategori');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center text-slate-400 py-10">
            <span class="app-icon text-3xl mb-2 block opacity-40" data-icon="tags" aria-hidden="true"></span>Belum ada kategori.
        </td></tr>`;
        return;
    }
    tbody.innerHTML = data.map(k => `
        <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3 px-4 text-slate-400 font-mono text-xs">#${k.id}</td>
            <td class="py-3 px-4 font-bold text-slate-900">${escapeHTML(k.nama_kategori)}</td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-1">
                    <button onclick="editKategori(${k.id})" class="p-2 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Kategori">
                        <span class="app-icon" data-icon="edit" aria-hidden="true"></span>
                    </button>
                    <button onclick="hapusKategori(${k.id})" class="p-2 rounded-lg text-slate-600 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Kategori">
                        <span class="app-icon" data-icon="trash" aria-hidden="true"></span>
                    </button>
                </div>
            </td>
        </tr>`).join('');
}

function filterKategoriTabel() {
    const q = document.getElementById('cariKategori').value.toLowerCase().trim();
    const filtered = listKategori.filter(k => k.nama_kategori.toLowerCase().includes(q));
    renderKategori(filtered);
}

function renderSelectKategori() {
    document.getElementById('menuKategori').innerHTML = listKategori
        .map(k => `<option value="${k.id}">${escapeHTML(k.nama_kategori)}</option>`).join('');
}

// Ini buat nampilin riwayat transaksi di tabel.
function renderRiwayat(data = listRiwayat) {
    const tbody = document.getElementById('tabelRiwayat');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-slate-400 py-10">
            <span class="app-icon text-3xl mb-2 block opacity-40" data-icon="receipt" aria-hidden="true"></span>Belum ada transaksi terekam.
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
            <td class="py-3 px-4 font-mono font-bold text-slate-900">${escapeHTML(r.no_transaksi)}</td>
            <td class="py-3 px-4 text-slate-500 text-xs">${escapeHTML(r.tanggal)}</td>
            <td class="py-3 px-4">
                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700">
                    <span class="app-icon text-slate-400" data-icon="user-circle" aria-hidden="true"></span>${escapeHTML(r.nama_kasir)}
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
                    <span class="app-icon" data-icon="receipt" aria-hidden="true"></span>Lihat
                </button>
            </td>
        </tr>`).join('');
}

function filterRiwayatTabel() {
    const q = document.getElementById('cariRiwayat').value.toLowerCase().trim();
    const filtered = listRiwayat.filter(r =>
        r.no_transaksi.toLowerCase().includes(q) ||
        r.nama_kasir.toLowerCase().includes(q) ||
        r.tanggal.toLowerCase().includes(q) ||
        r.status.toLowerCase().includes(q)
    );
    renderRiwayat(filtered);
}

// Ini buat nambah atau ngubah menu.
function bukaModalMenu(id = null) {
    document.getElementById('formMenu').reset();
    document.getElementById('menuId').value = '';
    document.getElementById('pesanMenu').classList.add('hidden');
    renderSelectKategori();

    if (id !== null) {
        const m = listMenu.find(x => x.id == id);
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

    // Ini buat mencegah form dikirim berkali-kali.
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
        pesanEl.innerText = 'Terjadi kesalahan jaringan saat nyimpen menu.';
        pesanEl.classList.remove('hidden');
        tampilkanToast('Gagal terhubung ke server.', 'error');
    } finally {
        btn.disabled = false;
        btnTeks.innerText = originalText;
    }
});

async function hapusMenu(id) {
    const m = listMenu.find(x => x.id == id);
    const nama = m ? m.nama_menu : 'menu ini';
    const yakin = await mintaKonfirmasi('Hapus Menu?', `Apakah Anda yakin ingin hapus "${nama}"? Tindakan ini tidak dapat dibatalkan.`);
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

// Ini buat nambah atau ngubah kategori.
function bukaModalKategori(id = null) {
    document.getElementById('kategoriId').value = '';
    document.getElementById('kategoriNama').value = '';
    document.getElementById('pesanKategori').classList.add('hidden');

    if (id !== null) {
        const k = listKategori.find(x => x.id == id);
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

async function hapusKategori(id) {
    const k = listKategori.find(x => x.id == id);
    const nama = k ? k.nama_kategori : 'kategori ini';
    const yakin = await mintaKonfirmasi('Hapus Kategori?', `Yakin ingin hapus kategori "${nama}"? Kategori yang masih berisi menu tidak bisa dihapus.`);
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

// Ini buat nampilin detail struk transaksi.
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
            <div class="flex justify-between text-xs py-1">
                <span>${escapeHTML(d.nama_menu)} <span class="text-slate-400">x${d.qty}</span></span>
                <span class="font-bold">${rupiah(d.subtotal)}</span>
            </div>`).join('');

        document.getElementById('areaStruk').innerHTML = `
            <div class="text-center border-b border-dashed border-slate-300 pb-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mx-auto mb-1 text-sm font-bold">
                    <span class="app-icon" data-icon="utensils" aria-hidden="true"></span>
                </div>
                <p class="font-extrabold text-slate-900 text-sm tracking-tight">KASIR RESTORAN</p>
                <p class="text-[11px] text-slate-500 font-mono mt-0.5">${escapeHTML(head.no_transaksi)}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">${escapeHTML(head.tanggal)} &bull; Kasir: ${escapeHTML(head.nama_kasir)}</p>
            </div>
            <div class="divide-y divide-dashed divide-slate-200">${items}</div>
            <div class="border-t border-dashed border-slate-300 mt-3 pt-3 text-xs space-y-1.5 font-medium">
                <div class="flex justify-between font-extrabold text-sm text-slate-900"><span>Total</span><span>${rupiah(head.total)}</span></div>
                <div class="flex justify-between text-slate-600"><span>Uang Bayar</span><span>${rupiah(head.bayar)}</span></div>
                <div class="flex justify-between text-emerald-600 font-bold"><span>Kembalian</span><span>${rupiah(head.kembalian)}</span></div>
            </div>`;
        document.getElementById('modalStruk').classList.remove('hidden');
    } catch (err) {
        tampilkanToast('Gagal muat detail struk.', 'error');
    }
}
function tutupStruk() { document.getElementById('modalStruk').classList.add('hidden'); }

async function logout() {
    const yakin = await mintaKonfirmasi(
        'Yakin ingin logout?',
        'Sesi Anda akan diakhiri dan Anda akan kembali ke halaman login.',
        'Ya, Logout'
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

// =============================================
// Ini buat ngatur data kasir.
// =============================================
async function muatKasir() {
    try {
        const r = await fetch(API + 'user&action=kasir-list').then(res => res.json());
        listKasir = r.data || [];
        renderKasir();
        updateStatsKasir();
    } catch (err) {
        tampilkanToast('Gagal muat data kasir', 'error');
    }
}

function updateStatsKasir() {
    const aktif = listKasir.filter(k => k.is_active == 1).length;
    const nonaktif = listKasir.filter(k => k.is_active == 0).length;
    const totalTrx = listKasir.reduce((s, k) => s + Number(k.total_transaksi || 0), 0);
    document.getElementById('statTotalKasir').innerText = listKasir.length;
    document.getElementById('statKasirAktif').innerText = aktif;
    document.getElementById('statKasirNonaktif').innerText = nonaktif;
    document.getElementById('statTotalTrxKasir').innerText = totalTrx;
}

function renderKasir(data = listKasir) {
    const tbody = document.getElementById('tabelKasir');
    if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-slate-400 py-10">
            <span class="app-icon text-3xl mb-2 block opacity-40" data-icon="users" aria-hidden="true"></span>Belum ada kasir terlist.
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
                    <div class="w-9 h-9 rounded-full ${avatarBg} text-white flex items-center justify-center text-xs font-bold shrink-0">${escapeHTML(avatar)}</div>
                    <div>
                        <span class="font-bold text-slate-900 block leading-tight">${escapeHTML(k.nama)}</span>
                        <span class="text-[10px] text-slate-400">Bergabung ${new Date(k.created_at).toLocaleDateString('id-ID', {day:'numeric',month:'short',year:'numeric'})}</span>
                    </div>
                </div>
            </td>
            <td class="py-3 px-4"><code class="text-xs font-mono bg-slate-100 px-2 py-0.5 rounded-md">${escapeHTML(k.username)}</code></td>
            <td class="py-3 px-4">${statusBadge}</td>
            <td class="py-3 px-4 font-bold text-slate-800">${k.total_transaksi || 0}</td>
            <td class="py-3 px-4 font-extrabold text-orange-600">${rupiah(k.total_omset || 0)}</td>
            <td class="py-3 px-4 text-xs text-slate-500">${trxTerakhir}</td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-0.5">
                    <button onclick="editUser(${k.id})" class="p-2 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit User">
                        <span class="app-icon text-xs" data-icon="edit" aria-hidden="true"></span>
                    </button>
                    <button onclick="toggleKasirStatus(${k.id}, ${isActive ? 0 : 1})" class="p-2 rounded-lg transition-colors ${isActive ? 'text-slate-500 hover:text-amber-600 hover:bg-amber-50' : 'text-slate-500 hover:text-emerald-600 hover:bg-emerald-50'}" title="${isActive ? 'Nonaktifkan' : 'Aktifkan'}">
                        <span class="app-icon text-xs" data-icon="${isActive ? 'user-slash' : 'user-check'}" aria-hidden="true"></span>
                    </button>
                    <button onclick="bukaResetPassword(${k.id})" class="p-2 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Reset Password">
                        <span class="app-icon text-xs" data-icon="key" aria-hidden="true"></span>
                    </button>
                    <button onclick="hapusUser(${k.id})" class="p-2 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus User">
                        <span class="app-icon text-xs" data-icon="trash" aria-hidden="true"></span>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filterKasirTabel() {
    const q = document.getElementById('cariKasir').value.toLowerCase().trim();
    const filtered = listKasir.filter(k =>
        k.nama.toLowerCase().includes(q) || k.username.toLowerCase().includes(q)
    );
    renderKasir(filtered);
}

// Ini buat nambah atau ngubah akun user.
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
        icon.dataset.icon = 'eye-slash';
    } else {
        inp.type = 'password';
        icon.dataset.icon = 'eye';
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

async function toggleKasirStatus(id, newStatus) {
    const k = listKasir.find(x => x.id == id);
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
    const k = listKasir.find(x => x.id == id);
    const nama = k ? k.nama : 'user ini';
    const yakin = await mintaKonfirmasi('Hapus User?', `Yakin ingin hapus "${nama}"? User yang sudah memiliki transaksi tidak bisa dihapus (nonaktifkan saja).`);
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

// Ini buat mengatur ulang password kasir.
function bukaResetPassword(id) {
    const k = listKasir.find(x => x.id == id);
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

// Ini buat muat tampilan awal halaman.
bukaTab('menu');
muatMenu();
muatKategori();
muatRiwayat();
