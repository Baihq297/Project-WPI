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
$nama = trim($_POST['nama_menu']);
$harga = intval($_POST['harga']);
$kategori = $_POST['kategori'];
$stok = intval($_POST['stok']);
$status_input_fe = isset($_POST['status']) ? $_POST['status'] : 'habis';

// 3. Validasi
if (empty($nama) || $harga <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nama Menu dan Harga wajib diisi.']);
    exit;
}

// 4. Cek duplikat nama
$stmt_cek = mysqli_prepare($conn, "SELECT id_menu FROM menu WHERE nama_menu = ?");
mysqli_stmt_bind_param($stmt_cek, "s", $nama);
mysqli_stmt_execute($stmt_cek);
$res_cek = mysqli_stmt_get_result($stmt_cek);
if (mysqli_num_rows($res_cek) > 0) {
    echo json_encode(['success' => false, 'message' => "Nama menu '$nama' sudah ada."]);
    mysqli_stmt_close($stmt_cek);
    exit;
}
mysqli_stmt_close($stmt_cek);

// 5. Tentukan status final
$status_final_db = ($status_input_fe === 'tersedia') ? 'aktif' : 'nonaktif';
if ($status_final_db === 'aktif' && $stok <= 0) {
    $status_final_db = 'nonaktif';
}

// 6. Upload gambar (jika ada)
$gambar_final = "";
if (!empty($_FILES['gambar']['name'])) {
    $filename = basename($_FILES['gambar']['name']);
    $target = "../assets/img/" . $filename;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
        $gambar_final = $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal upload gambar.']);
        exit;
    }
}

// 7. INSERT menu baru
$stmt_insert = mysqli_prepare($conn, "INSERT INTO menu (nama_menu, harga, kategori, stok, status, gambar) VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt_insert, "sisiss", $nama, $harga, $kategori, $stok, $status_final_db, $gambar_final);
$ok = mysqli_stmt_execute($stmt_insert);

if ($ok) {
    echo json_encode(['success' => true, 'message' => "Menu '$nama' berhasil ditambahkan!"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal insert database: ' . mysqli_error($conn)]);
}

mysqli_stmt_close($stmt_insert);
