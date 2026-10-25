<?php
class Transaksi {
    private $conn;
    private $table = "transaksi";

    public function __construct($db) {
        $this->conn = $db;
    }

    // FOR UPDATE mengunci baris terakhir agar nomor tidak kembar saat transaksi bersamaan
    public function generateNoTransaksi() {
        $prefix = "TRX-" . date("Ymd") . "-";
        $like   = $prefix . "%";

        $stmt = $this->conn->prepare("SELECT no_transaksi FROM " . $this->table . "
                                      WHERE no_transaksi LIKE :prefix
                                      ORDER BY id DESC LIMIT 1 FOR UPDATE");
        $stmt->bindParam(":prefix", $like);
        $stmt->execute();

        $last = $stmt->fetch();
        $urut = 1;
        if ($last) {
            $parts = explode("-", $last['no_transaksi']);
            $urut  = (int) end($parts) + 1;
        }
        return $prefix . str_pad($urut, 3, "0", STR_PAD_LEFT);
    }

    private function validasiItems($items) {
        if (empty($items) || !is_array($items)) {
            throw new Exception("Item transaksi tidak boleh kosong.");
        }
        foreach ($items as $item) {
            if (!isset($item['id_menu'], $item['harga'], $item['qty'])
                || !is_numeric($item['harga']) || !is_numeric($item['qty'])
                || $item['qty'] < 1 || $item['harga'] < 0) {
                throw new Exception("Format item transaksi tidak valid.");
            }
        }
    }

    public function simpanTransaksi($id_user, $items, $bayar) {
        try {
            $this->validasiItems($items);
            $this->conn->beginTransaction();

            $total = 0;
            foreach ($items as $item) {
                $total += $item['harga'] * $item['qty'];
            }
            if ($bayar < $total) {
                throw new Exception("Uang pembayaran kurang dari total belanja.");
            }

            $kembalian    = $bayar - $total;
            $no_transaksi = $this->generateNoTransaksi();

            $stmt = $this->conn->prepare("INSERT INTO " . $this->table . "
                                          (no_transaksi, id_user, total, bayar, kembalian, status)
                                          VALUES (:no, :user, :total, :bayar, :kembalian, 'selesai')");
            $stmt->bindParam(":no", $no_transaksi);
            $stmt->bindParam(":user", $id_user, PDO::PARAM_INT);
            $stmt->bindParam(":total", $total, PDO::PARAM_INT);
            $stmt->bindParam(":bayar", $bayar, PDO::PARAM_INT);
            $stmt->bindParam(":kembalian", $kembalian, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Gagal menyimpan data transaksi utama.");
            }
            $id_transaksi = $this->conn->lastInsertId();

            $stmt_detail = $this->conn->prepare("INSERT INTO detail_transaksi
                                                 (id_transaksi, id_menu, qty, subtotal)
                                                 VALUES (:trx, :menu, :qty, :sub)");
            $stmt_stok = $this->conn->prepare("UPDATE menu SET stok = stok - :qty
                                               WHERE id = :id AND stok >= :min_qty");

            foreach ($items as $item) {
                $subtotal = $item['harga'] * $item['qty'];

                $stmt_detail->bindParam(":trx", $id_transaksi, PDO::PARAM_INT);
                $stmt_detail->bindParam(":menu", $item['id_menu'], PDO::PARAM_INT);
                $stmt_detail->bindParam(":qty", $item['qty'], PDO::PARAM_INT);
                $stmt_detail->bindParam(":sub", $subtotal, PDO::PARAM_INT);
                if (!$stmt_detail->execute()) {
                    throw new Exception("Gagal menyimpan detail transaksi.");
                }

                $stmt_stok->bindParam(":qty", $item['qty'], PDO::PARAM_INT);
                $stmt_stok->bindParam(":id", $item['id_menu'], PDO::PARAM_INT);
                $stmt_stok->bindParam(":min_qty", $item['qty'], PDO::PARAM_INT);
                $stmt_stok->execute();
                if ($stmt_stok->rowCount() === 0) {
                    throw new Exception("Stok untuk menu ID " . $item['id_menu'] . " tidak mencukupi.");
                }
            }

            $this->conn->commit();
            return [
                "success"      => true,
                "no_transaksi" => $no_transaksi,
                "total"        => $total,
                "kembalian"    => $kembalian
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function getRiwayat($limit = 50) {
        $stmt = $this->conn->prepare("SELECT t.*, u.nama AS nama_kasir
                                      FROM " . $this->table . " t
                                      JOIN users u ON t.id_user = u.id
                                      ORDER BY t.tanggal DESC LIMIT :lim");
        $stmt->bindParam(":lim", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDetail($id_transaksi) {
        $stmt = $this->conn->prepare("SELECT dt.*, m.nama_menu, m.harga
                                      FROM detail_transaksi dt
                                      JOIN menu m ON dt.id_menu = m.id
                                      WHERE dt.id_transaksi = :id");
        $stmt->bindParam(":id", $id_transaksi, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}