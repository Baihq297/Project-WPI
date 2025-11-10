<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require "../api/koneksi.php"; // pastikan $conn ada

$id  = isset($_POST['id'])  ? intval($_POST['id'])  : 0;
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
if ($qty < 1) $qty = 1;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// cek apakah produk ada & ambil stok dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT stok FROM menu WHERE id_menu = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB error']);
    exit;
}
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $stokDb);
if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'Produk tidak ditemukan di database']);
    exit;
}
mysqli_stmt_close($stmt);

$stok = intval($stokDb);
if ($stok <= 0) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'Stok habis']);
    exit;
}
if ($qty > $stok) $qty = $stok;

// update session (pakai struktur konsisten: id => ['qty'=>n])
if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];
$_SESSION['keranjang'][$id] = ['qty' => $qty];

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Jumlah diperbarui', 'qty' => $qty]);
exit;
?>