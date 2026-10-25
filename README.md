# Sistem Kasir Restoran

Aplikasi kasir berbasis web untuk manajemen transaksi restoran. Dibangun dengan arsitektur MVC sederhana menggunakan PHP native dan MySQL, tanpa framework backend tambahan.

---

## Teknologi yang Digunakan

### Backend

| Teknologi | Versi | Keterangan |
|---|---|---|
| PHP | 8.5.12 | Bahasa pemrograman utama backend |
| MySQL | 8.x | Database relasional |
| PDO (PHP Data Objects) | Bawaan PHP | Abstraksi koneksi database, mencegah SQL Injection |
| PHP Session | Bawaan PHP | Manajemen autentikasi pengguna |
| password_hash / password_verify | Bawaan PHP | Enkripsi password menggunakan algoritma Bcrypt (cost factor 12) |

### Frontend

| Teknologi | Versi | Keterangan |
|---|---|---|
| HTML5 | - | Struktur halaman |
| JavaScript (Vanilla) | ES2020+ | Logika interaktif halaman tanpa framework tambahan |
| Tailwind CSS | CDN v3 | Framework CSS utility-first untuk tampilan |
| Font Awesome | 6.4.0 (CDN) | Ikon antarmuka |
| Plus Jakarta Sans | Google Fonts | Tipografi utama |
| Fetch API | Bawaan Browser | Komunikasi asinkron ke REST API backend |

### Server dan Lingkungan

| Komponen | Rekomendasi |
|---|---|
| Web Server | Apache (XAMPP) atau Nginx |
| PHP | Versi 8.1 ke atas, diuji pada 8.5.12 |
| Database | MySQL 8.x atau MariaDB 10.6+ |
| Charset | utf8mb4 (mendukung karakter Unicode penuh) |

---

## Struktur Proyek

```
TTS_Asdos/
|
|-- index.php                    # Entry point API, router utama, autoloader kelas
|-- admin.php                    # Halaman dashboard admin (menu, kategori, kasir, riwayat)
|-- kasir.php                    # Halaman antarmuka kasir (transaksi POS)
|-- login.php                    # Halaman login untuk semua peran
|
|-- config/
|   |-- database.php             # Konfigurasi koneksi PDO ke MySQL
|
|-- models/
|   |-- user.php                 # Model pengguna (login, CRUD user, toggle aktif)
|   |-- menu.php                 # Model menu makanan/minuman (CRUD, upload gambar)
|   |-- kategori.php             # Model kategori menu (CRUD, validasi relasi)
|   |-- transaksi.php            # Model transaksi (simpan, riwayat, detail, stok)
|
|-- controllers/
|   |-- authController.php       # Menangani login, logout, cek sesi
|   |-- menuController.php       # Menangani CRUD menu dan upload gambar
|   |-- kategoriController.php   # Menangani CRUD kategori
|   |-- transaksiController.php  # Menangani penyimpanan dan pembacaan transaksi
|   |-- userController.php       # Menangani CRUD kasir oleh admin
|
|-- sql/
|   |-- kasirrestoran.sql        # Skrip DDL dan data dummy untuk setup database
|
|-- uploads/
|   |-- .htaccess                # Proteksi agar file di uploads tidak bisa dieksekusi PHP
|
|-- info.md                      # Catatan desain dan spesifikasi proyek
|-- TODO.md                      # Catatan progres dan daftar tugas pengembangan
|-- README.md                    # Dokumentasi proyek ini
```

---

## Arsitektur Aplikasi

Proyek ini menggunakan pola **MVC (Model-View-Controller)** yang disederhanakan:

- **Model** - Bertanggung jawab atas semua interaksi dengan database melalui PDO. Setiap tabel memiliki satu kelas model.
- **Controller** - Menerima request dari router, memanggil model, dan mengembalikan respons JSON.
- **View** - File PHP (admin.php, kasir.php, login.php) yang merender antarmuka HTML. Komunikasi data ke backend dilakukan sepenuhnya melalui Fetch API JavaScript.

Alur Request:

```
Browser / Klien
    |
    v
login.php / admin.php / kasir.php      (View: render HTML, kirim Fetch request)
    |
    v
index.php?route=xxx&action=yyy         (Router dan Autoloader kelas)
    |
    v
Controller (sesuai route)              (Validasi input, cek autentikasi, panggil model)
    |
    v
Model                                  (Query ke MySQL via PDO prepared statement)
    |
    v
JSON Response                          (Dikembalikan ke JavaScript di browser)
```

---

## Konfigurasi Database

File konfigurasi: config/database.php

```
Host     : localhost
Database : kasirrestoran
Username : root
Password : (kosong, default XAMPP)
Charset  : utf8mb4
```

Atribut PDO yang diaktifkan:

- PDO::ATTR_ERRMODE = ERRMODE_EXCEPTION - Error database dilempar sebagai exception PHP
- PDO::ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC - Hasil query selalu berupa array asosiatif
- PDO::ATTR_EMULATE_PREPARES = false - Prepared statement native, bukan emulasi

---

## Skema Database

### Tabel: users

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Primary key |
| username | VARCHAR(50) UNIQUE | Nama login, unik per pengguna |
| password | VARCHAR(255) | Hash Bcrypt dengan cost factor 12 |
| nama | VARCHAR(100) | Nama lengkap pengguna |
| role | ENUM('admin','kasir') | Peran pengguna, default 'kasir' |
| is_active | TINYINT(1) | Status akun: 1 aktif, 0 nonaktif |
| created_at | TIMESTAMP | Waktu pendaftaran, diisi otomatis |

### Tabel: kategori

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Primary key |
| nama_kategori | VARCHAR(50) | Nama kategori menu |
| created_at | TIMESTAMP | Waktu dibuat, diisi otomatis |

### Tabel: menu

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Primary key |
| id_kategori | INT | Foreign key ke tabel kategori |
| nama_menu | VARCHAR(100) | Nama menu |
| harga | INT | Harga dalam satuan Rupiah |
| stok | INT | Jumlah stok porsi tersedia |
| gambar | VARCHAR(255) | Nama file gambar di folder uploads, bisa NULL |
| created_at | TIMESTAMP | Waktu dibuat, diisi otomatis |

### Tabel: transaksi

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Primary key |
| no_transaksi | VARCHAR(20) UNIQUE | Nomor unik format TRX-YYYYMMDD-NNN |
| id_user | INT | Foreign key ke users (kasir yang melayani) |
| total | INT | Total harga seluruh item |
| bayar | INT | Uang yang dibayarkan pelanggan |
| kembalian | INT | Hasil total dikurangi bayar |
| status | ENUM | Nilai: pending, selesai, atau batal |
| tanggal | TIMESTAMP | Waktu transaksi, diisi otomatis |

### Tabel: detail_transaksi

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT AUTO_INCREMENT | Primary key |
| id_transaksi | INT | Foreign key ke transaksi |
| id_menu | INT | Foreign key ke menu |
| qty | INT | Jumlah item yang dipesan |
| subtotal | INT | Hasil harga dikali qty |

---

## REST API Endpoints

Semua request dikirim ke index.php dengan query string route dan action.

Format URL: index.php?route={route}&action={action}

Semua respons menggunakan format JSON:

```json
{ "success": true, "message": "keterangan", "data": {} }
```

### Auth (route=auth)

| Action | Method | Akses | Keterangan |
|---|---|---|---|
| login | POST | Publik | Login dengan username dan password |
| logout | GET | Login | Hapus sesi, redirect ke login |
| check | GET | Publik | Cek apakah sesi sedang aktif |

### Menu (route=menu)

| Action | Method | Akses | Keterangan |
|---|---|---|---|
| list | GET | Login | Daftar semua menu, opsional filter dengan ?kategori=id |
| detail | GET | Login | Detail satu menu dengan ?id=x |
| create | POST | Admin | Tambah menu baru, mendukung upload gambar |
| update | POST | Admin | Ubah data menu, mendukung ganti atau hapus gambar |
| delete | POST | Admin | Hapus menu beserta file gambarnya dari server |

### Kategori (route=kategori)

| Action | Method | Akses | Keterangan |
|---|---|---|---|
| list | GET | Login | Daftar semua kategori |
| create | POST | Admin | Tambah kategori baru |
| update | POST | Admin | Ubah nama kategori |
| delete | POST | Admin | Hapus kategori, ditolak jika masih ada menu di dalamnya |

### Transaksi (route=transaksi)

| Action | Method | Akses | Keterangan |
|---|---|---|---|
| simpan | POST | Login | Simpan transaksi baru dan kurangi stok secara atomik |
| riwayat | GET | Login | Daftar transaksi, opsional ?limit=n, default 50 |
| detail | GET | Login | Detail item dalam satu transaksi dengan ?id=x |

### User dan Kasir (route=user)

| Action | Method | Akses | Keterangan |
|---|---|---|---|
| list | GET | Admin | Daftar semua user |
| kasir-list | GET | Admin | Daftar kasir beserta statistik transaksi dan omset |
| detail | GET | Admin | Detail satu user dengan ?id=x |
| create | POST | Admin | Tambah user baru, validasi username unik |
| update | POST | Admin | Ubah nama, username, dan role user |
| toggle | POST | Admin | Aktifkan atau nonaktifkan akun kasir |
| reset-password | POST | Admin | Reset password kasir dengan password baru |
| delete | POST | Admin | Hapus user, ditolak jika sudah memiliki riwayat transaksi |

---

## Fitur Keamanan

| Fitur | Implementasi |
|---|---|
| SQL Injection Prevention | Seluruh query menggunakan PDO Prepared Statements dengan parameter binding |
| Password Hashing | Bcrypt via password_hash() dengan cost factor 12 |
| Session Hijacking Prevention | session_regenerate_id(true) dipanggil setiap login berhasil |
| Session Cookie Security | httponly=true dan samesite=Lax untuk mencegah akses JavaScript ke cookie |
| Role-Based Access Control | Setiap controller memvalidasi sesi dan role sebelum eksekusi |
| XSS Prevention | Input teks disanitasi dengan htmlspecialchars() dan strip_tags() |
| Upload Security | Validasi ekstensi, ukuran maksimal 2MB, verifikasi getimagesize(), nama file di-random |
| Upload Execution Block | File .htaccess di folder uploads menonaktifkan eksekusi PHP |
| Account Status Check | Login ditolak dengan pesan informatif jika akun dinonaktifkan |
| Self-Protection | Admin tidak bisa menonaktifkan atau menghapus akun dirinya sendiri |
| Concurrent Transaction Safety | Nomor transaksi digenerate dalam database transaction dengan SELECT FOR UPDATE |

---

## Cara Setup

### 1. Persiapan Server

Pastikan XAMPP sudah terinstal dan layanan Apache serta MySQL sudah berjalan.

### 2. Letakkan Proyek

Salin seluruh folder proyek ke direktori web server:

```
C:\xampp\htdocs\TTS_Asdos\
```

### 3. Import Database

1. Buka http://localhost/phpmyadmin di browser
2. Pilih menu Import di bagian atas
3. Klik Pilih File dan pilih sql/kasirrestoran.sql
4. Klik Go untuk mulai import

Skrip SQL akan membuat database, semua tabel, dan mengisi data awal secara otomatis.

### 4. Sesuaikan Konfigurasi (Jika Perlu)

Buka config/database.php dan sesuaikan jika konfigurasi MySQL berbeda dari default XAMPP:

```php
private $host     = "localhost";
private $db_name  = "kasirrestoran";
private $username = "root";
private $password = "";
```

### 5. Akses Aplikasi

Buka browser dan akses:

```
http://localhost/TTS_Asdos/login.php
```

---

## Akun Default

Akun berikut sudah tersedia setelah import database:

| Username | Password | Peran |
|---|---|---|
| admin | admin123 | Administrator |
| kasir1 | kasir123 | Kasir |

---

## Peran dan Hak Akses

### Admin (admin.php)

- Melihat ringkasan statistik: total menu, kategori, transaksi, dan omset keseluruhan
- Kelola Menu: tambah, edit, dan hapus menu beserta foto
- Kelola Kategori: tambah, edit, dan hapus kategori
- Riwayat Penjualan: melihat seluruh transaksi dari semua kasir dan mencetak struk
- Kelola Kasir: tambah kasir baru, edit data, aktifkan atau nonaktifkan akun, reset password, dan hapus akun

### Kasir (kasir.php)

- Melihat daftar menu yang tersedia beserta stok
- Memilih item dan jumlah untuk keranjang belanja
- Memproses transaksi dan menghitung kembalian secara otomatis
- Mencetak struk setelah transaksi selesai

---

## Catatan Teknis

**Penanganan Stok pada Transaksi**

Pengurangan stok dilakukan di dalam database transaction. Apabila salah satu item memiliki stok tidak mencukupi, seluruh transaksi dibatalkan (rollback) dan tidak ada data yang tersimpan ke database.

**Format Nomor Transaksi**

Nomor transaksi digenerate otomatis dengan format TRX-YYYYMMDD-NNN. Contoh: TRX-20261025-001. Nomor urut dimulai dari 001 setiap hari.

**Upload Gambar Menu**

File gambar disimpan di folder uploads dengan nama gabungan timestamp dan 4 byte random. Contoh: menu_1729870000_a3f2bc01.jpg. File gambar lama dihapus otomatis dari server saat menu diedit atau dihapus.

**Akun Nonaktif**

Ketika admin menonaktifkan akun kasir, kasir tersebut tidak dapat login. Halaman login menampilkan pesan khusus yang menginformasikan bahwa akun dinonaktifkan dan kasir perlu menghubungi administrator untuk mengaktifkan kembali.
