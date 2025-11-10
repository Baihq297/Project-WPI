<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

include "koneksi.php";

// 🔹 Ambil semua data menu yang masih tersedia
$q = "SELECT * FROM menu WHERE status != 'habis' ORDER BY kategori, nama_menu";
$res = mysqli_query($conn, $q);

if (!$res) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengambil data menu: ' . mysqli_error($conn),
        'data' => []
    ]);
    exit;
}

if (mysqli_num_rows($res) === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Belum ada menu yang tersedia',
        'data' => []
    ]);
    exit;
}

$data = [];
while ($r = mysqli_fetch_assoc($res)) {
    $data[] = [
        'id_menu' => (int)$r['id_menu'],
        'nama_menu' => $r['nama_menu'],
        'harga' => (int)$r['harga'],
        'kategori' => $r['kategori'],
        'stok' => (int)$r['stok'],
        'status' => $r['status'],
        'gambar' => $r['gambar']
            ? 'assets/img/' . $r['gambar']
            : 'assets/img/default_user.png'
    ];
}

// 🔹 Kirim respon JSON
echo json_encode([
    'success' => true,
    'message' => 'Data menu berhasil diambil',
    'data' => $data
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
