<?php
session_start();
include "koneksi.php";
header('Content-Type: application/json');

// 1. Cek Admin
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

// 2. Ambil Data dari POST
$id_menu = intval($_POST['id_menu']);
$nama = trim($_POST['nama_menu']);
$harga = intval($_POST['harga']);
$kategori = $_POST['kategori'];
$stok = intval($_POST['stok']);
$status_input_fe = isset($_POST['status']) ? $_POST['status'] : 'habis';

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

// 5. Logika Status
$status_final_db = ($status_input_fe === 'tersedia') ? 'aktif' : 'nonaktif';
if ($status_final_db === 'aktif' && $stok <= 0) {
    $status_final_db = 'nonaktif';
}

// 6. Ambil gambar lama
$stmt_old = mysqli_prepare($conn, "SELECT gambar FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($stmt_old, "i", $id_menu);
mysqli_stmt_execute($stmt_old);
$result_old = mysqli_stmt_get_result($stmt_old);
$row_old = mysqli_fetch_assoc($result_old);

if (!$row_old) {
    echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan.']);
    mysqli_stmt_close($stmt_old);
    exit;
}
$gambar_final = $row_old['gambar'];
mysqli_stmt_close($stmt_old);

// 7. Cek Upload Gambar Baru
if (!empty($_FILES['gambar']['name'])) {
    $filename = basename($_FILES['gambar']['name']);
    $target = "../assets/img/" . $filename;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
        $gambar_final = $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal upload gambar baru.']);
        exit;
    }
}

// 8. Update Database
$stmt_update = mysqli_prepare($conn, "UPDATE menu SET nama_menu=?, harga=?, kategori=?, stok=?, status=?, gambar=? WHERE id_menu=?");
mysqli_stmt_bind_param($stmt_update, "sisissi", $nama, $harga, $kategori, $stok, $status_final_db, $gambar_final, $id_menu);
$ok = mysqli_stmt_execute($stmt_update);

if ($ok) {
    echo json_encode(['success' => true, 'message' => "Menu '$nama' berhasil diperbarui!"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal update database: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt_update);
