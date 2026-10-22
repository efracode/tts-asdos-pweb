<?php
class TransaksiController {
    private Transaksi $transaksi;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->transaksi = new Transaksi($db);
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'simpan':  $this->simpan();  break;
            case 'riwayat': $this->riwayat(); break;
            case 'detail':  $this->detail();  break;
            default:
                json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    // POST: simpan transaksi
    // Form: id_user=2, bayar=60000, items=[{"id_menu":1,"harga":25000,"qty":2}] (string JSON)
    // Catatan: id_user dari POST bersifat sementara, nanti diganti dari session login
    private function simpan(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }

        $idUser = (int) ($_POST['id_user'] ?? 0);
        $bayar  = (int) ($_POST['bayar'] ?? 0);
        $items  = json_decode($_POST['items'] ?? '', true);

        if ($idUser <= 0) {
            json_response(['success' => false, 'message' => 'ID user tidak valid'], 422);
        }
        if ($bayar <= 0) {
            json_response(['success' => false, 'message' => 'Nominal bayar tidak valid'], 422);
        }
        if (!is_array($items)) {
            json_response(['success' => false, 'message' => 'Format items tidak valid (harus JSON array)'], 422);
        }

        $hasil = $this->transaksi->simpanTransaksi($idUser, $items, $bayar);

        if ($hasil['success']) {
            json_response(['success' => true, 'data' => $hasil], 201);
        }
        json_response(['success' => false, 'message' => $hasil['message']], 422);
    }

    // GET: riwayat transaksi, opsional ?limit=20
    private function riwayat(): void {
        $limit = (int) ($_GET['limit'] ?? 50);
        json_response(['success' => true, 'data' => $this->transaksi->getRiwayat($limit)]);
    }

    // GET: detail transaksi untuk struk ?id=1
    private function detail(): void {
        $data = $this->transaksi->getDetail((int) ($_GET['id'] ?? 0));

        if (empty($data)) {
            json_response(['success' => false, 'message' => 'Detail transaksi tidak ditemukan'], 404);
        }
        json_response(['success' => true, 'data' => $data]);
    }
}