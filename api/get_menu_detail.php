<?php
session_start();
include "koneksi.php"; // Pastikan path ini benar
header('Content-Type: application/json');

// 1. Cek Admin
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
  exit;
}

// 2. Ambil ID
$id_menu = intval($_GET['id_menu'] ?? 0);
if ($id_menu <= 0) {
  echo json_encode(['success' => false, 'message' => 'ID Menu tidak valid']);
  exit;
}

// 3. Query Database
$stmt = mysqli_prepare($conn, "SELECT * FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($stmt, "i", $id_menu);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// 4. Kembalikan data
if ($data) {
  // Ubah tipe data agar mudah dibaca JavaScript
  $data['stok'] = intval($data['stok']);
  $data['harga'] = floatval($data['harga']);
  // Buat boolean 'is_tersedia' agar mudah untuk checkbox/toggle
  $data['is_tersedia'] = (in_array(strtolower($data['status']), ['aktif', 'tersedia']));
  
  echo json_encode(['success' => true, 'data' => $data]);
} else {
  echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
}
?>