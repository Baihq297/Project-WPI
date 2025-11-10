<?php
session_start();
require_once "koneksi.php"; // Sesuaikan path jika koneksi.php ada di root
header('Content-Type: application/json');

// 1. Cek jika Admin
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
  exit;
}

// 2. Ambil ID pesanan dari request
$id_pesanan = intval($_GET['id_pesanan'] ?? 0);
if ($id_pesanan <= 0) {
  echo json_encode(['success' => false, 'message' => 'ID Pesanan tidak valid']);
  exit;
}

// 3. Query untuk mengambil detail item (JOIN dengan tabel menu)
$query = "
  SELECT 
      dp.qty, 
      dp.harga, 
      m.nama_menu
  FROM detail_pesanan dp
  JOIN menu m ON dp.id_menu = m.id_menu
  WHERE dp.id_pesanan = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Kita tambahkan subtotal di sini agar JS tidak perlu menghitung
    $row['subtotal'] = $row['qty'] * $row['harga'];
    $items[] = $row;
}

mysqli_stmt_close($stmt);

// 4. Kembalikan data sebagai JSON
echo json_encode(['success' => true, 'items' => $items]);
?>