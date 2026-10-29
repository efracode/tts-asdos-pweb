# Sistem Kasir Restoran

Aplikasi kasir berbasis web untuk manajemen transaksi restoran. Menggunakan pola arsitektur MVC sederhana dengan PHP murni dan MySQL, tanpa framework backend tambahan.

---

## Daftar Isi

1. Teknologi yang Digunakan
2. Struktur Proyek
3. Arsitektur dan Alur Aplikasi
4. Skema Database
5. Daftar API Endpoint
6. Fitur Keamanan
7. Cara Menjalankan
8. Hak Akses Pengguna

---

## 1. Teknologi yang Digunakan

### Backend (Server)

| Teknologi | Versi | Fungsi |
|---|---|---|
| PHP | 8.5.12 | Bahasa pemrograman utama |
| MySQL | 8.x | Penyimpanan data |
| PDO | Bawaan PHP | Penghubung PHP ke MySQL, aman dari SQL Injection |
| PHP Session | Bawaan PHP | Menyimpan data login pengguna |
| Bcrypt | Bawaan PHP | Enkripsi password (tidak bisa dibaca balik) |

### Frontend (Tampilan)

| Teknologi | Versi | Fungsi |
|---|---|---|
| HTML5 | - | Struktur halaman web |
| JavaScript | ES2020+ | Logika interaktif (tanpa framework) |
| Tailwind CSS | v3 (CDN) | Tampilan dan gaya antarmuka |
| Font Awesome | 6.4.0 (CDN) | Ikon-ikon antarmuka |
| Plus Jakarta Sans | Google Fonts | Jenis huruf yang dipakai |
| Fetch API | Bawaan Browser | Mengambil dan mengirim data ke server tanpa reload halaman |

### Kebutuhan Server

| Komponen | Syarat Minimum |
|---|---|
| Web Server | Apache (via XAMPP) |
| PHP | Versi 8.1 ke atas |
| Database | MySQL 8.x atau MariaDB 10.6+ |

---

## 2. Struktur Proyek

```
TTS_Asdos/
|
|-- index.php                 # Pintu masuk API: menerima semua request, menentukan controller
|-- admin.php                 # Halaman untuk admin (kelola menu, kategori, kasir, laporan)
|-- kasir.php                 # Halaman untuk kasir (input transaksi, cetak struk)
|-- login.php                 # Halaman login semua pengguna
|
|-- config/
|   |-- database.php          # Pengaturan koneksi ke MySQL
|
|-- models/                   # Kelas yang berinteraksi langsung dengan database
|   |-- user.php              # Data pengguna (login, tambah, edit, nonaktifkan)
|   |-- menu.php              # Data menu (tambah, edit, hapus)
|   |-- kategori.php          # Data kategori menu
|   |-- transaksi.php         # Data transaksi (simpan, riwayat, detail, stok)
|
|-- controllers/              # Kelas yang menerima request dan memanggil model
|   |-- authController.php    # Login, logout, cek sesi
|   |-- menuController.php    # CRUD menu
|   |-- kategoriController.php# CRUD kategori
|   |-- transaksiController.php# Simpan dan baca transaksi
|   |-- userController.php    # Kelola akun kasir oleh admin
|
|-- sql/
|   |-- kasirrestoran.sql     # File SQL: membuat tabel dan mengisi data awal
|
```

---

## 3. Arsitektur dan Alur Aplikasi

Proyek ini menggunakan pola **MVC (Model - View - Controller)**:

| Lapisan | File | Peran |
|---|---|---|
| View | admin.php, kasir.php, login.php | Menampilkan antarmuka ke pengguna |
| Controller | controllers/*.php | Menerima permintaan, memvalidasi, memanggil Model |
| Model | models/*.php | Mengambil atau menyimpan data ke database |

Cara kerja secara ringkas:

```
Pengguna klik/submit di browser
        |
        v
View (admin.php / kasir.php) mengirim request ke index.php via Fetch API
        |
        v
index.php menentukan Controller yang tepat berdasarkan ?route=xxx&action=yyy
        |
        v
Controller memvalidasi input dan hak akses, lalu memanggil Model
        |
        v
Model menjalankan query ke MySQL dan mengembalikan hasilnya
        |
        v
Controller mengembalikan JSON ke browser
        |
        v
JavaScript di browser memperbarui tampilan tanpa reload halaman
```

---

## 4. Skema Database

Database bernama `kasirrestoran` terdiri dari 5 tabel yang saling berelasi:

```
users ─────────────────────────── transaksi ──── detail_transaksi
  (id, username, password,           (id_user FK)      (id_transaksi FK)
   nama, role, is_active)                               (id_menu FK)
                                                              |
kategori ──── menu                                         menu
  (id)         (id_kategori FK)     (id)
```

### Tabel: users

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary key, otomatis bertambah |
| username | VARCHAR(50) | Nama login, harus unik |
| password | VARCHAR(255) | Hash Bcrypt, tidak disimpan dalam bentuk asli |
| nama | VARCHAR(100) | Nama lengkap pengguna |
| role | ENUM | Nilai: `admin` atau `kasir` |
| is_active | TINYINT(1) | `1` = akun aktif, `0` = akun dinonaktifkan |
| created_at | TIMESTAMP | Waktu pendaftaran, diisi otomatis |

### Tabel: kategori

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary key |
| nama_kategori | VARCHAR(50) | Nama kategori (contoh: Makanan, Minuman) |
| created_at | TIMESTAMP | Diisi otomatis |

### Tabel: menu

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary key |
| id_kategori | INT | Relasi ke tabel kategori |
| nama_menu | VARCHAR(100) | Nama item menu |
| harga | INT | Harga dalam Rupiah |
| stok | INT | Jumlah porsi tersedia |
| created_at | TIMESTAMP | Diisi otomatis |

### Tabel: transaksi

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary key |
| no_transaksi | VARCHAR(20) | Nomor unik, format: TRX-YYYYMMDD-NNN |
| id_user | INT | Relasi ke tabel users (kasir yang bertugas) |
| total | INT | Total tagihan dalam Rupiah |
| bayar | INT | Uang yang dibayarkan pelanggan |
| kembalian | INT | Uang kembalian (bayar - total) |
| status | ENUM | Nilai: `pending`, `selesai`, atau `batal` |
| tanggal | TIMESTAMP | Waktu transaksi, diisi otomatis |

### Tabel: detail_transaksi

Menyimpan rincian item per transaksi (satu transaksi bisa punya banyak baris).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary key |
| id_transaksi | INT | Relasi ke tabel transaksi |
| id_menu | INT | Relasi ke tabel menu |
| qty | INT | Jumlah item yang dipesan |
| subtotal | INT | Harga x qty |

---

## 5. Daftar API Endpoint

Semua permintaan dikirim ke `index.php` dengan format:

```
index.php?route=NAMA_ROUTE&action=NAMA_AKSI
```

Semua respons selalu dalam format JSON:

```json
{ "success": true, "message": "...", "data": { ... } }
```

Keterangan kolom Akses: **Publik** = siapa saja bisa akses, **Login** = harus sudah login, **Admin** = harus login sebagai admin.

| Route | Action | Method | Akses | Keterangan |
|---|---|---|---|---|
| auth | login | POST | Publik | Masuk ke sistem |
| auth | logout | GET | Login | Keluar dari sistem |
| auth | check | GET | Publik | Cek apakah sudah login |
| menu | list | GET | Login | Ambil semua menu |
| menu | detail | GET | Login | Detail satu menu (?id=x) |
| menu | create | POST | Admin | Tambah menu baru |
| menu | update | POST | Admin | Edit menu |
| menu | delete | POST | Admin | Hapus menu |
| kategori | list | GET | Login | Ambil semua kategori |
| kategori | create | POST | Admin | Tambah kategori |
| kategori | update | POST | Admin | Edit kategori |
| kategori | delete | POST | Admin | Hapus kategori |
| transaksi | simpan | POST | Login | Simpan transaksi baru |
| transaksi | riwayat | GET | Login | Daftar semua transaksi |
| transaksi | detail | GET | Login | Detail item transaksi (?id=x) |
| user | kasir-list | GET | Admin | Daftar kasir + statistik |
| user | create | POST | Admin | Tambah akun kasir |
| user | update | POST | Admin | Edit data kasir |
| user | toggle | POST | Admin | Aktifkan / nonaktifkan kasir |
| user | reset-password | POST | Admin | Reset password kasir |
| user | delete | POST | Admin | Hapus akun kasir |

---

## 6. Fitur Keamanan

| Ancaman | Cara Penanganan |
|---|---|
| SQL Injection | Semua query menggunakan Prepared Statement (PDO), input tidak langsung digabung ke query |
| Password dicuri | Password dienkripsi dengan Bcrypt sebelum disimpan, tidak bisa dibaca balik |
| Session hijacking | ID sesi diganti baru setiap kali login berhasil (`session_regenerate_id`) |
| Cookie dicuri via JS | Cookie sesi diset `httponly=true` sehingga tidak bisa diakses JavaScript |
| Akses tidak sah | Setiap endpoint admin memeriksa role pengguna sebelum dijalankan |
| XSS (input berbahaya) | Semua input teks disanitasi sebelum disimpan ke database |
| Akun bermasalah login | Akun yang dinonaktifkan admin tidak bisa login, muncul pesan penjelasan |
| Admin menonaktifkan diri | Sistem mencegah admin menonaktifkan atau menghapus akun miliknya sendiri |
| Transaksi bersamaan | Nomor transaksi digenerate di dalam DB transaction dengan kunci baris (`SELECT FOR UPDATE`) |

---

## 7. Cara Menjalankan

### Prasyarat

- XAMPP terinstal (Apache + MySQL aktif)
- Browser modern (Chrome, Firefox, Edge)

### Langkah Setup

**Langkah 1 — Salin Proyek**

Letakkan folder proyek di dalam direktori XAMPP:

```
C:\xampp\htdocs\TTS_Asdos\
```

**Langkah 2 — Import Database**

1. Buka `http://localhost/phpmyadmin`
2. Pilih tab **Import**
3. Pilih file `sql/kasirrestoran.sql`
4. Klik **Go**

Database beserta semua tabel dan data awal akan terbentuk otomatis.

**Langkah 3 — Sesuaikan Konfigurasi (jika perlu)**

Buka `config/database.php`. Ubah password jika MySQL tidak menggunakan password kosong:

```php
private $host     = "localhost";
private $db_name  = "kasirrestoran";
private $username = "root";
private $password = "";        // ganti jika ada password MySQL
```

**Langkah 4 — Buka Aplikasi**

Akses melalui browser:

```
http://localhost/TTS_Asdos/login.php
```

### Akun Bawaan

| Username | Password | Peran |
|---|---|---|
| admin | admin123 | Administrator |
| kasir1 | kasir123 | Kasir |

---

## 8. Hak Akses Pengguna

### Admin (masuk ke admin.php)

- Melihat statistik ringkasan: jumlah menu, kategori, transaksi, dan total omset
- Mengelola Menu: tambah, edit, dan hapus menu
- Mengelola Kategori: tambah, edit, hapus kategori
- Melihat Riwayat Penjualan dari semua kasir dan mencetak struk
- Mengelola Kasir: tambah akun, edit data, aktifkan/nonaktifkan, reset password, hapus akun

### Kasir (masuk ke kasir.php)

- Melihat daftar menu yang tersedia beserta stok
- Memilih item dan menentukan jumlah pesanan
- Menginput nominal pembayaran, sistem menghitung kembalian otomatis
- Mencetak struk setelah transaksi selesai

> Catatan: Kasir yang dinonaktifkan oleh admin tidak bisa login. Saat mencoba login, akan muncul pesan yang meminta kasir menghubungi administrator.
