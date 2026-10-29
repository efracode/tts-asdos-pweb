<?php
class TransaksiController {
    private Transaksi $transaksi;

    public function __construct() {
        $this->transaksi = new Transaksi((new Database())->getConnection());
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'simpan':  $this->simpan();  break;
            case 'riwayat': $this->riwayat(); break;
            case 'detail':  $this->detail();  break;
            default: json_response(['success' => false, 'message' => 'Aksi tidak ditemukan'], 404);
        }
    }

    private function simpan(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['success' => false, 'message' => 'Method harus POST'], 405);
        }
        $login = $this->wajibLogin();

        $bayar = filter_var($_POST['bayar'] ?? null, FILTER_VALIDATE_INT);
        $items = $_POST['items'] ?? null;
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if ($bayar === false || $bayar <= 0) {
            json_response(['success' => false, 'message' => 'Nominal bayar tidak valid'], 422);
        }
        if (!is_array($items)) {
            json_response(['success' => false, 'message' => 'Format items harus berupa JSON array'], 422);
        }

        $hasil = $this->transaksi->simpanTransaksi($login['id'], $items, $bayar);

        if ($hasil['success']) json_response(['success' => true, 'data' => $hasil], 201);
        json_response(['success' => false, 'message' => $hasil['message']], 422);
    }

    private function riwayat(): void {
        $this->wajibLogin();
        $limit = (int) ($_GET['limit'] ?? 50);
        json_response(['success' => true, 'data' => $this->transaksi->getRiwayat($limit)]);
    }

    private function detail(): void {
        $this->wajibLogin();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            json_response(['success' => false, 'message' => 'ID transaksi tidak valid'], 422);
        }

        $data = $this->transaksi->getDetail($id);
        if (empty($data)) json_response(['success' => false, 'message' => 'Detail transaksi tidak ditemukan'], 404);
        json_response(['success' => true, 'data' => $data]);
    }

    private function wajibLogin(): array {
        if (!isset($_SESSION['user'])) json_response(['success' => false, 'message' => 'Silakan login dulu'], 401);
        return $_SESSION['user'];
    }
}