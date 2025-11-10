<?php
// 🔹 Izinkan akses lintas domain (jika API diakses dari dashboard atau aplikasi Android)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

include "koneksi.php";

// 🔹 Ambil status semua menu
$query = mysqli_query($conn, "SELECT id_menu, nama_menu, status FROM menu ORDER BY kategori, nama_menu");

if (!$query) {
  echo json_encode([
    'success' => false,
    'message' => 'Query gagal dijalankan: ' . mysqli_error($conn),
    'menu' => []
  ]);
  exit;
}

if (mysqli_num_rows($query) === 0) {
  echo json_encode([
    'success' => false,
    'message' => 'Belum ada data menu.',
    'menu' => []
  ]);
  exit;
}

$menu = [];
while ($row = mysqli_fetch_assoc($query)) {
  $menu[] = [
    'id_menu' => (int)$row['id_menu'],
    'nama_menu' => $row['nama_menu'],
    'status' => $row['status']
  ];
}

echo json_encode([
  'success' => true,
  'message' => 'Status menu berhasil diambil.',
  'menu' => $menu
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
