-- Ini buat membuat tabel database dan nambahin data awal.

CREATE DATABASE IF NOT EXISTS kasirrestoran DEFAULT CHARSET utf8mb4;
USE kasirrestoran;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin','kasir') DEFAULT 'kasir',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama_menu VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    stok INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(20) UNIQUE NOT NULL,
    id_user INT NOT NULL,
    total INT NOT NULL,
    bayar INT NOT NULL,
    kembalian INT NOT NULL,
    status ENUM('pending','selesai','batal') DEFAULT 'pending',
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_menu INT NOT NULL,
    qty INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES menu(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Dummy Users
-- admin  : password = admin123
-- kasir1 : password = kasir123
INSERT INTO users (username, password, nama, role, is_active) VALUES
('admin',  '$2y$12$/cEKzih8gaLXO8XSjoo00OuXb3hNGGFmUuqh7eMvhQ19hUbEfK7.O', 'Administrator', 'admin', 1),
('kasir1', '$2y$12$BTOvgHP3z2fcJsoUWkZN.uGH7a3wkUrFKpG5imQQUWQaUa.xiOXi2', 'Teo Kasir',     'kasir', 1);

INSERT INTO kategori (nama_kategori) VALUES ('Makanan'), ('Minuman'), ('Snack');

INSERT INTO menu (id_kategori, nama_menu, harga, stok) VALUES
(1, 'Nasi Goreng Spesial', 25000, 50),
(1, 'Ayam Bakar Madu', 35000, 30),
(1, 'Mie Goreng Jawa', 22000, 40),
(2, 'Es Teh Manis', 5000, 100),
(2, 'Es Jeruk', 7000, 80),
(2, 'Kopi Hitam', 8000, 60),
(3, 'Kentang Goreng', 15000, 45),
(3, 'Pisang Goreng', 10000, 50);
