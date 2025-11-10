<?php
session_start();
require_once "koneksi.php";
header('Content-Type: application/json');

// 1. Cek jika Admin
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
  exit;
}

// 2. Ambil data dari POST (AJAX)
$id_pesanan = intval($_POST['id_pesanan'] ?? 0);
$status = $_POST['status'] ?? '';
$allowed_statuses = ['Baru', 'Diproses', 'Dikirim', 'Selesai'];

// 3. Validasi
if ($id_pesanan <= 0 || !in_array($status, $allowed_statuses)) {
  echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
  exit;
}

// 4. Update Database
$stmt = mysqli_prepare($conn, "UPDATE pesanan SET status = ? WHERE id_pesanan = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $id_pesanan);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
  echo json_encode(['success' => true, 'message' => 'Status pesanan berhasil diperbarui!']);
} else {
  echo json_encode(['success' => false, 'message' => 'Gagal memperbarui database.']);
}
?>