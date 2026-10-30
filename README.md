# Kasir Restoran

Aplikasi kasir sederhana buat ngatur menu, menerima pembayaran, mencatat transaksi, dan mencetak struk. Admin ngatur data aplikasi; kasir melayani pesanan.

## Teknologi

- **Tampilan:** HTML, Tailwind CSS, dan JavaScript.
- **Server:** PHP Native dengan PDO.
- **Database:** MySQL atau MariaDB.
- **Ikon:** Lucide, disimpan sebagai satu sprite WebP lokal. Lisensi ikon ada di `assets/images/ICON-LICENSE.txt`.

## Gaya Komentar

Komentar penting pakai pola `// Ini buat ...` sebelum bagian kode. Tulis santai dan jelasin tujuan bagian utamanya. Nggak perlu komentar di setiap baris biar kodenya tetap enak dibaca.

Contoh:

```php
// Ini buat ngecek apakah form dikirim lewat POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Proses form.
}
```

## Susunan Proyek

| Lokasi | Isi |
|---|---|
| `login.php`, `admin.php`, `kasir.php` | Halaman login, admin, dan kasir. |
| `index.php` | Pintu masuk API yang ngarahin request ke controller. |
| `controllers/` | Ngecek request, input, dan hak akses. |
| `models/` | Menjalankan query database. |
| `config/database.php` | Pengaturan koneksi database. |
| `sql/kasirrestoran.sql` | Membuat tabel dan data awal. |
| `assets/js/` | JavaScript untuk tiap halaman. |
| `assets/images/ui-icons.webp` | Kumpulan ikon aplikasi. |

## Cara Kerja

```text
Halaman ngirim request → Controller ngecek → Model akses database
                       ← Controller ngirim JSON ←
```

Cara gampang menjelaskan MVC: **halaman nampilin aplikasi, controller ngatur request, model ngurus database**.

Saat checkout, server ngambil harga dari database, ngecek stok, lalu nyimpen transaksi dan detailnya. Kalau ada proses yang gagal, perubahan dibatalin supaya data nggak tersimpan setengah.

## Fitur

- **Admin:** ngatur menu, kategori, dan akun kasir; lihat riwayat penjualan dan struk.
- **Kasir:** cari menu, atur keranjang, hitung kembalian, simpan transaksi, dan cetak struk.
- **Keamanan dasar:** password di-hash, query memakai prepared statements, dan aksi admin ngecek role.
- **Validasi:** server ngecek data form sebelum disimpan ke database.

Database punya tabel `users`, `kategori`, `menu`, `transaksi`, dan `detail_transaksi`.

## Menjalankan Aplikasi

1. Jalankan Apache dan MySQL lewat XAMPP, Laragon, atau WampServer.
2. Impor `sql/kasirrestoran.sql` lewat phpMyAdmin.
3. Periksa nama database dan kredensial di `config/database.php`.
4. Pastikan ekstensi PHP `PDO MySQL` dan `mbstring` aktif.
5. Buka `http://localhost/TTS_Asdos/login.php`.

Akun contoh:

| Peran | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Kasir | `kasir1` | `kasir123` |

Untuk demo struk, pilih kertas **80 mm / Receipt** di pengaturan printer kalau preview masih pakai A4.

## Dokumen Tambahan

- [Panduan presentasi](PANDUAN_PRESENTASI.md)
