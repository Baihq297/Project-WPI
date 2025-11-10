<?php
require_once "koneksi.php"; // pastikan koneksi hanya dimuat sekali
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Ambil data dari POST
$nama  = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['password'] ?? '');
$role  = 'pelanggan'; // default role user baru

// Validasi input dasar
if ($nama === '' || $email === '' || $pass === '') {
  echo json_encode(['success' => false, 'message' => 'Lengkapi semua kolom!']);
  exit;
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Format email tidak valid!']);
  exit;
}

// Cek apakah email sudah terdaftar
$stmt = mysqli_prepare($conn, "SELECT id_pelanggan FROM pelanggan WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
  echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar!']);
  exit;
}

// Hash password
$hash = password_hash($pass, PASSWORD_DEFAULT);

// Simpan data pelanggan baru
$stmt = mysqli_prepare($conn, "INSERT INTO pelanggan (nama, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hash, $role);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
  echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil!']);
} else {
  echo json_encode([
    'success' => false,
    'message' => 'Gagal menyimpan data: ' . mysqli_error($conn)
  ]);
}
?>
