<?php
session_start();
include "koneksi.php";
header('Content-Type: application/json');

// 1. Cek Admin
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
  exit;
}

// 2. Ambil Data dari POST (FormData)
$id_menu = intval($_POST['id_menu']);
$nama = trim($_POST['nama_menu']);
$harga = intval($_POST['harga']);
$kategori = $_POST['kategori'];
$stok = intval($_POST['stok']);
$status_input = isset($_POST['status']) ? 'tersedia' : 'habis';

// 3. Validasi
if ($id_menu <= 0 || empty($nama) || $harga <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID, Nama Menu, dan Harga wajib diisi.']);
    exit;
}

// 4. Cek duplikat nama
$stmt_cek = mysqli_prepare($conn, "SELECT id_menu FROM menu WHERE nama_menu = ? AND id_menu != ?");
mysqli_stmt_bind_param($stmt_cek, "si", $nama, $id_menu);
mysqli_stmt_execute($stmt_cek);
$res_cek = mysqli_stmt_get_result($stmt_cek);
if (mysqli_num_rows($res_cek) > 0) {
    echo json_encode(['success' => false, 'message' => "Nama menu '$nama' sudah ada."]);
    mysqli_stmt_close($stmt_cek);
    exit;
}
mysqli_stmt_close($stmt_cek);

// === PERBAIKAN LOGIKA STOK DAN STATUS ===
// Jika admin set status 'tersedia' TAPI stoknya 0, paksa status jadi 'habis'
if ($status_input === 'tersedia' && $stok <= 0) {
    $status_final = 'habis';
} else {
    $status_final = $status_input;
}
// === AKHIR PERBAIKAN ===

// 5. Ambil nama gambar lama dari DB
$stmt_old = mysqli_prepare($conn, "SELECT gambar FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($stmt_old, "i", $id_menu);
mysqli_stmt_execute($stmt_old);
$result_old = mysqli_stmt_get_result($stmt_old);
$row_old = mysqli_fetch_assoc($result_old);

// === PERBAIKAN ERROR 500 ===
// Cek jika data ditemukan sebelum mengambil 'gambar'
if ($row_old) {
    $gambar_final = $row_old['gambar'];
} else {
    // Jika ID tidak ditemukan, kirim error
    echo json_encode(['success' => false, 'message' => 'Error: Menu ID tidak ditemukan.']);
    exit;
}
mysqli_stmt_close($stmt_old);
// === AKHIR PERBAIKAN ===


// 6. Cek jika ada gambar BARU yang di-upload
if (!empty($_FILES['gambar']['name'])) {
    $gambar_baru = $_FILES['gambar']['name'];
    $targetDir = '../assets/img/';
    $targetFile = $targetDir . basename($gambar_baru);
    
    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
        $gambar_final = basename($gambar_baru); // Ganti nama file jika upload sukses
    }
}

// 7. Update Database
$stmt_update = mysqli_prepare($conn, "UPDATE menu SET nama_menu=?, harga=?, kategori=?, stok=?, status=?, gambar=? WHERE id_menu=?");
// Gunakan $status_final yang sudah divalidasi
mysqli_stmt_bind_param($stmt_update, "sisiisi", $nama, $harga, $kategori, $stok, $status_final, $gambar_final, $id_menu);
$ok = mysqli_stmt_execute($stmt_update);

if ($ok) {
  echo json_encode(['success' => true, 'message' => "Menu '$nama' berhasil diperbarui!"]);
} else {
  echo json_encode(['success' => false, 'message' => 'Gagal update database: ' . mysqli_error($conn)]);
}
mysqli_stmt_close($stmt_update);
?>