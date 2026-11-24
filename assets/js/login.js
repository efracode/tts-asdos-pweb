// Ini buat mengatur tab login/register, toggle password, tombol demo, dan submit autentikasi.

const tabLogin = document.getElementById('tab-login');
const tabRegister = document.getElementById('tab-register');
const panelLogin = document.getElementById('panel-login');
const panelRegister = document.getElementById('panel-register');
const pesan = document.getElementById('pesan');

const formLogin = document.getElementById('formLogin');
const tombolLogin = document.getElementById('tombolLogin');
const formRegister = document.getElementById('formRegister');
const tombolRegister = document.getElementById('tombolRegister');

function tampilkanPesan(teks, tipe = 'error') {
    const styleMap = {
        error: 'mb-4 rounded-lg bg-rose-50 border border-rose-300 text-rose-800 text-sm px-4 py-3 font-medium',
        warning: 'mb-4 rounded-lg bg-amber-50 border border-amber-300 text-amber-900 text-sm px-4 py-3 font-medium',
        sukses: 'mb-4 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 font-medium'
    };
    pesan.className = styleMap[tipe] || styleMap.error;
    pesan.textContent = teks;
    pesan.classList.remove('hidden');
}

function sembunyikanPesan() {
    pesan.classList.add('hidden');
    pesan.textContent = '';
}

function pilihTabAuth(mode, pindahkanFokus = false) {
    const isLogin = mode === 'login';
    sembunyikanPesan();

    tabLogin.setAttribute('aria-selected', String(isLogin));
    tabRegister.setAttribute('aria-selected', String(!isLogin));
    tabLogin.tabIndex = isLogin ? 0 : -1;
    tabRegister.tabIndex = isLogin ? -1 : 0;

    tabLogin.className = isLogin
        ? 'min-h-[44px] rounded-md text-sm font-bold transition-colors bg-white text-slate-900 border border-slate-200 shadow-sm'
        : 'min-h-[44px] rounded-md text-sm font-semibold transition-colors text-slate-600 hover:text-slate-900';

    tabRegister.className = !isLogin
        ? 'min-h-[44px] rounded-md text-sm font-bold transition-colors bg-white text-slate-900 border border-slate-200 shadow-sm'
        : 'min-h-[44px] rounded-md text-sm font-semibold transition-colors text-slate-600 hover:text-slate-900';

    panelLogin.hidden = !isLogin;
    panelRegister.hidden = isLogin;

    if (pindahkanFokus) {
        (isLogin ? tabLogin : tabRegister).focus();
    }
}

tabLogin.addEventListener('click', () => pilihTabAuth('login'));
tabRegister.addEventListener('click', () => pilihTabAuth('register'));

[tabLogin, tabRegister].forEach((tab) => {
    tab.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
            e.preventDefault();
            pilihTabAuth(tab === tabLogin ? 'register' : 'login', true);
        }
    });
});

function pasangTogglePassword(btnId, inputId) {
    const btn = document.getElementById(btnId);
    const input = document.getElementById(inputId);
    if (!btn || !input) return;

    btn.addEventListener('click', () => {
        const tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(tampil));
        btn.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
        const icon = btn.querySelector('.app-icon');
        if (icon) {
            icon.dataset.icon = tampil ? 'eye-slash' : 'eye';
        }
    });
}

pasangTogglePassword('toggleLoginPass', 'loginPassword');
pasangTogglePassword('toggleRegPass', 'regPassword');

function isiDemo(username, password) {
    pilihTabAuth('login');
    document.getElementById('loginUsername').value = username;
    document.getElementById('loginPassword').value = password;
    document.getElementById('tombolLogin').focus();
}

document.querySelectorAll('.btn-demo').forEach((btn) => {
    btn.addEventListener('click', () => {
        isiDemo(btn.dataset.demoUser || '', btn.dataset.demoPass || '');
    });
});

formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();
    sembunyikanPesan();
    tombolLogin.disabled = true;
    const isiAwal = tombolLogin.innerHTML;
    tombolLogin.innerHTML = '<span class="app-icon is-spinning" data-icon="spinner" aria-hidden="true"></span><span>Memproses...</span>';

    try {
        const respon = await fetch('index.php?route=auth&action=login', {
            method: 'POST',
            body: new FormData(formLogin)
        });
        const hasil = await respon.json();

        if (hasil.success) {
            window.location.href = hasil.data.role === 'admin' ? 'admin.php' : 'kasir.php';
            return;
        }
        tampilkanPesan(hasil.message || 'Login gagal.', hasil.reason === 'account_inactive' ? 'warning' : 'error');
    } catch (err) {
        tampilkanPesan('Terjadi kesalahan jaringan. Periksa server lokal Anda.', 'error');
    } finally {
        tombolLogin.disabled = false;
        tombolLogin.innerHTML = isiAwal;
    }
});

formRegister.addEventListener('submit', async (e) => {
    e.preventDefault();
    sembunyikanPesan();

    const pass = document.getElementById('regPassword').value;
    const konf = document.getElementById('regKonfirmasi').value;
    if (pass !== konf) {
        tampilkanPesan('Konfirmasi password tidak cocok dengan password yang dimasukkan.', 'error');
        document.getElementById('regKonfirmasi').focus();
        return;
    }

    tombolRegister.disabled = true;
    const isiAwal = tombolRegister.innerHTML;
    tombolRegister.innerHTML = '<span class="app-icon is-spinning" data-icon="spinner" aria-hidden="true"></span><span>Mendaftarkan...</span>';

    try {
        const respon = await fetch('index.php?route=auth&action=register', {
            method: 'POST',
            body: new FormData(formRegister)
        });
        const hasil = await respon.json();

        if (hasil.success) {
            tampilkanPesan(hasil.message || 'Registrasi berhasil!', 'sukses');
            window.location.href = 'kasir.php';
            return;
        }
        tampilkanPesan(hasil.message || 'Pendaftaran gagal.', 'error');
    } catch (err) {
        tampilkanPesan('Terjadi kesalahan jaringan saat mendaftar.', 'error');
    } finally {
        tombolRegister.disabled = false;
        tombolRegister.innerHTML = isiAwal;
    }
});
