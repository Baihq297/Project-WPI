<?php
session_start();
require "../api/koneksi.php";

$id = intval($_POST['id']);
$qty = intval($_POST['qty'] ?? 1);
if ($qty < 1) $qty = 1;

// Ambil data menu & cek stok
$stmt = mysqli_prepare($conn, "SELECT stok, nama_menu, harga, gambar FROM menu WHERE id_menu = ?");
if (!$stmt) {
    echo json_encode(['success'=>false, 'message'=>'DB prepare error']); exit;
}
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $stok, $nama_menu, $harga, $gambar);

if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
    exit;
}
mysqli_stmt_close($stmt);

// Inisialisasi session
if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];

// Tambah item ke session
if (isset($_SESSION['keranjang'][$id])) {
    $_SESSION['keranjang'][$id]['qty'] += $qty;
} else {
    $_SESSION['keranjang'][$id] = [
        'id'    => $id,
        'nama'  => $nama_menu,
        'harga' => $harga,
        'gambar'=> $gambar,
        'qty'   => $qty
    ];
}

// Validasi stok
$stok = intval($stok);
if ($stok <= 0) {
    echo json_encode(['success'=>false,'message'=>'Stok habis']); exit;
}
// Jika jumlah di keranjang melebihi stok, set ke jumlah stok
if ($_SESSION['keranjang'][$id]['qty'] > $stok) {
     $_SESSION['keranjang'][$id]['qty'] = $stok;
}

echo json_encode(['success' => true, 'message' => 'Berhasil ditambahkan ke keranjang!']);
?>