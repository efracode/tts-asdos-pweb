# Panduan Presentasi Sistem Kasir Restoran

## Penjelasan singkat

> Aplikasi ini memiliki dua jenis pengguna: admin dan kasir. Admin mengelola menu, kategori, akun kasir, dan melihat riwayat penjualan. Kasir memilih menu, memasukkan pembayaran, lalu mencetak struk. Data disimpan di MySQL.

## Alur program

```text
Pengguna menekan tombol di halaman
        ↓
JavaScript mengirim permintaan ke index.php
        ↓
Controller memeriksa data dan hak akses
        ↓
Model membaca atau mengubah database
        ↓
Controller mengirim hasil dalam JSON
        ↓
JavaScript memperbarui halaman
```

Cara mudah menjelaskan MVC: **halaman menampilkan**, **controller mengatur permintaan**, dan **model mengurus database**.

## File yang perlu ditunjukkan

| File | Cara menjelaskannya |
|---|---|
| `login.php` | Form login mengirim username dan password ke API. |
| `index.php` | Memuat file aplikasi secara eksplisit, lalu memilih controller dari parameter `route` dan `action`. |
| `controllers/` | Memeriksa metode request, input, sesi, dan hak akses sebelum memanggil model. |
| `models/` | Berisi query database untuk pengguna, menu, kategori, dan transaksi. |
| `kasir.php` | Halaman kasir: katalog, keranjang, pembayaran, dan struk. |
| `admin.php` | Halaman admin: data menu, kategori, akun kasir, dan riwayat transaksi. |
| `assets/js/` | JavaScript dipisah per halaman agar tampilan PHP dan alur tombol lebih mudah dibaca. |
| `assets/images/ui-icons.webp` | Satu gambar WebP transparan berisi 40 ikon antarmuka yang dipakai bersama. |
| `assets/css/icons.css` | Mengatur posisi tiap ikon pada sprite dan mewarnainya mengikuti teks. |
| `sql/kasirrestoran.sql` | Membuat tabel, relasi, dan data contoh. |

## Contoh presentasi checkout

1. Kasir memilih menu. Browser menyimpan pilihan di keranjang sementara.
2. Saat checkout, browser mengirim ID menu, jumlah, dan uang bayar.
3. `TransaksiController` memeriksa bahwa kasir sudah login dan inputnya valid.
4. `Transaksi` membaca harga dari database, mengunci stok, menghitung total, lalu menyimpan transaksi dan detail dalam satu transaksi database.
5. Jika satu langkah gagal, perubahan di-rollback agar data tidak tersimpan separuh.
6. Jika berhasil, halaman menampilkan struk dan stok berkurang.

Kalimat kunci: **harga yang dipercaya berasal dari database, bukan dari browser**.

## Keamanan dengan bahasa sederhana

- Password disimpan sebagai hash dengan `password_hash`; saat login diperiksa dengan `password_verify`.
- Query database memakai PDO prepared statements.
- Endpoint admin memeriksa role sebelum mengubah data.
- Teks database di-escape saat dimasukkan ke halaman supaya tidak dianggap sebagai kode HTML.
- Checkout memakai transaksi database supaya catatan penjualan dan pengurangan stok berhasil bersama-sama atau dibatalkan bersama-sama.

## Cara menjalankan demo

1. Jalankan Apache dan MySQL di XAMPP.
2. Pastikan database `kasirrestoran` sudah dibuat dari `sql/kasirrestoran.sql`.
3. Buka `http://localhost/TTS_Asdos/login.php`.
4. Login sebagai `admin / admin123` atau `kasir1 / kasir123`.
5. Untuk demo kasir, pilih menu, lakukan pembayaran, dan buka dialog cetak.
6. Tekan **Logout** untuk memperlihatkan dialog konfirmasi. Sesi berakhir setelah memilih **Ya, Logout**.

## Hal yang perlu disebutkan saat demo

- Printer thermal dapat mengabaikan ukuran halaman dari CSS. Jika preview masih memakai A4, pilih kertas 80 mm/Receipt pada pengaturan printer.
- Pemeriksaan sintaks PHP dan JavaScript sudah dilakukan. Uji alur browser, koneksi database, dan hasil cetak tetap perlu dilakukan pada komputer demo.
