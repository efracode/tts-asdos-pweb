// Ini buat ngirim form login dan nampilin hasilnya.

const form   = document.getElementById('formLogin');
const pesan  = document.getElementById('pesan');
const tombol = document.getElementById('tombolLogin');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    pesan.classList.add('hidden');
    pesan.className = 'hidden mb-4 rounded-lg text-sm px-4 py-3'; // Ini buat membersihkan tampilan pesan sebelumnya.
    tombol.disabled = true;
    tombol.innerText = 'Memproses...';

    try {
        const respon = await fetch('index.php?route=auth&action=login', {
            method: 'POST',
            body: new FormData(form)
        });
        const hasil = await respon.json();

        if (hasil.success) {
            window.location.href = hasil.data.role === 'admin' ? 'admin.php' : 'kasir.php';
        } else {
            // Ini buat membedakan pesan akun nonaktif dari kesalahan login biasa.
            if (hasil.reason === 'account_inactive') {
                pesan.className = 'mb-4 rounded-lg bg-amber-50 border border-amber-300 text-amber-800 text-sm px-4 py-3';
                const ikon = document.createElement('span');
                ikon.className = 'app-icon mr-1.5';
                ikon.dataset.icon = 'user-lock';
                const judul = document.createElement('strong');
                judul.textContent = 'Akun Dinonaktifkan';
                const keterangan = document.createElement('span');
                keterangan.className = 'text-xs text-amber-700 leading-relaxed';
                keterangan.textContent = hasil.message;
                pesan.replaceChildren(ikon, judul, document.createElement('br'), keterangan);
            } else {
                pesan.className = 'mb-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3';
                pesan.innerText = hasil.message;
            }
            pesan.classList.remove('hidden');
        }
    } catch (err) {
        pesan.className = 'mb-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3';
        pesan.innerText = 'Terjadi kesalahan jaringan.';
        pesan.classList.remove('hidden');
    } finally {
        tombol.disabled = false;
        tombol.innerHTML = '<span class="app-icon mr-2" data-icon="login" aria-hidden="true"></span>Login';
    }
});
