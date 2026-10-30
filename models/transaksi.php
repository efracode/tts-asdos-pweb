<?php
// Ini buat nyimpen transaksi, detail belanja, dan perubahan stok.

class Transaksi {
    private PDO $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Ini buat nyimpen penjualan sebagai satu proses; jika gagal, semua perubahan dibatalkan.
    public function simpanTransaksi(int $idUser, array $items, int $bayar): array {
        $nomorLock = null;
        try {
            $this->conn->beginTransaction();
            $items = $this->siapkanItems($items);

            $total = $this->hitungTotal($items);
            if ($bayar < $total) {
                throw new RuntimeException('Uang pembayaran kurang dari total belanja.');
            }

            $nomor = $this->nomorTransaksi($nomorLock);
            $idTransaksi = $this->simpanHeader($nomor, $idUser, $total, $bayar);
            $this->simpanDetailDanKurangiStok($idTransaksi, $items);

            $this->conn->commit();
            $this->lepasKunciNomor($nomorLock);
            return [
                'success' => true,
                'no_transaksi' => $nomor,
                'total' => $total,
                'kembalian' => $bayar - $total,
            ];
        } catch (Throwable $error) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->lepasKunciNomor($nomorLock);
            if ($error instanceof PDOException) {
                error_log($error->__toString());
                return ['success' => false, 'message' => 'Terjadi gangguan saat menyimpan transaksi. Silakan coba lagi.'];
            }
            if ($error instanceof InvalidArgumentException || $error instanceof RuntimeException) {
                return ['success' => false, 'message' => $error->getMessage()];
            }
            error_log($error->__toString());
            return ['success' => false, 'message' => 'Transaksi gagal disimpan. Silakan coba lagi.'];
        }
    }

    // Ini buat merapikan item dan ngecek menu serta stok masih tersedia.
    private function siapkanItems(array $items): array {
        if (!$items) {
            throw new InvalidArgumentException('Item transaksi tidak boleh kosong.');
        }

        $jumlah = [];
        foreach ($items as $item) {
            $idMenu = filter_var($item['id_menu'] ?? null, FILTER_VALIDATE_INT);
            $qty = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT);

            if (!$idMenu || !$qty || $qty < 1) {
                throw new InvalidArgumentException('Format item transaksi tidak valid.');
            }
            $jumlah[$idMenu] = ($jumlah[$idMenu] ?? 0) + $qty;
        }
        // Kunci menu dalam urutan yang sama agar checkout bersamaan tidak deadlock.
        ksort($jumlah, SORT_NUMERIC);

        $hasil = [];
        $ambilMenu = $this->conn->prepare(
            'SELECT id, nama_menu, harga, stok FROM menu WHERE id = :id FOR UPDATE'
        );

        foreach ($jumlah as $idMenu => $qty) {
            $ambilMenu->bindValue(':id', $idMenu, PDO::PARAM_INT);
            $ambilMenu->execute();
            $menu = $ambilMenu->fetch();

            if (!$menu) {
                throw new InvalidArgumentException("Menu ID {$idMenu} tidak ditemukan.");
            }
            if ($menu['stok'] < $qty) {
                throw new RuntimeException("Stok untuk {$menu['nama_menu']} tidak mencukupi.");
            }

            $hasil[] = [
                'id_menu' => $idMenu,
                'harga' => (int) $menu['harga'],
                'qty' => $qty,
            ];
        }
        return $hasil;
    }

    private function hitungTotal(array $items): int {
        $total = 0;
        foreach ($items as $item) {
            $total += $item['harga'] * $item['qty'];
        }
        return $total;
    }

    // Ini buat membuat nomor transaksi unik, termasuk saat kasir checkout bersamaan.
    private function nomorTransaksi(?string &$namaLock): string {
        $tanggal = date('Ymd');
        $prefix = 'TRX-' . $tanggal . '-';
        $namaLock = 'kasirrestoran:' . $tanggal;
        $kunci = $this->conn->prepare('SELECT GET_LOCK(:nama, 5)');
        $kunci->execute([':nama' => $namaLock]);
        if ((int) $kunci->fetchColumn() !== 1) {
            $namaLock = null;
            throw new RuntimeException('Nomor transaksi sedang digunakan. Silakan coba lagi.');
        }

        $stmt = $this->conn->prepare(
            "SELECT no_transaksi FROM transaksi
             WHERE no_transaksi LIKE :prefix
             ORDER BY id DESC LIMIT 1 FOR UPDATE"
        );
        $stmt->bindValue(':prefix', $prefix . '%');
        $stmt->execute();

        $terakhir = $stmt->fetchColumn();
        $bagianNomor = $terakhir ? substr($terakhir, strrpos($terakhir, '-') + 1) : '0';
        $urutan = (int) $bagianNomor + 1;
        return $prefix . str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
    }

    private function lepasKunciNomor(?string $namaLock): void {
        if ($namaLock === null) {
            return;
        }

        try {
            $stmt = $this->conn->prepare('SELECT RELEASE_LOCK(:nama)');
            $stmt->execute([':nama' => $namaLock]);
        } catch (Throwable $error) {
            // Koneksi database akan melepas advisory lock jika sesi berakhir.
        }
    }

    // Ini buat nyimpen ringkasan transaksi dan ngambil ID barunya.
    private function simpanHeader(string $nomor, int $idUser, int $total, int $bayar): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO transaksi
             (no_transaksi, id_user, total, bayar, kembalian, status)
             VALUES (:nomor, :user, :total, :bayar, :kembalian, 'selesai')"
        );
        $stmt->execute([
            ':nomor' => $nomor,
            ':user' => $idUser,
            ':total' => $total,
            ':bayar' => $bayar,
            ':kembalian' => $bayar - $total,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    // Ini buat nyimpen setiap item dan mengurangi stok menu.
    private function simpanDetailDanKurangiStok(int $idTransaksi, array $items): void {
        $detail = $this->conn->prepare(
            'INSERT INTO detail_transaksi (id_transaksi, id_menu, qty, subtotal)
             VALUES (:transaksi, :menu, :qty, :subtotal)'
        );
        $stok = $this->conn->prepare(
            'UPDATE menu SET stok = stok - :qty_kurang WHERE id = :menu AND stok >= :qty_tersedia'
        );

        foreach ($items as $item) {
            $detail->execute([
                ':transaksi' => $idTransaksi,
                ':menu' => $item['id_menu'],
                ':qty' => $item['qty'],
                ':subtotal' => $item['harga'] * $item['qty'],
            ]);
            $stok->execute([
                ':qty_kurang' => $item['qty'],
                ':menu' => $item['id_menu'],
                ':qty_tersedia' => $item['qty'],
            ]);
            if ($stok->rowCount() !== 1) {
                throw new RuntimeException('Stok berubah sebelum transaksi selesai. Silakan coba lagi.');
            }
        }
    }

    public function getRiwayat(int $limit = 50): array {
        $limit = max(1, min($limit, 100));
        $stmt = $this->conn->prepare(
            "SELECT t.id, t.no_transaksi, t.total, t.bayar, t.kembalian,
                    t.status, t.tanggal, u.nama AS nama_kasir
             FROM transaksi t
             JOIN users u ON u.id = t.id_user
             ORDER BY t.tanggal DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDetail(int $idTransaksi): array {
        $stmt = $this->conn->prepare(
            "SELECT d.id_menu, d.qty, d.subtotal, m.nama_menu, m.harga
             FROM detail_transaksi d
             JOIN menu m ON m.id = d.id_menu
             WHERE d.id_transaksi = :id
             ORDER BY d.id"
        );
        $stmt->bindValue(':id', $idTransaksi, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
