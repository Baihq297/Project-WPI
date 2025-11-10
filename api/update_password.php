<?php
// api/update_password.php
session_start();
require_once "koneksi.php";
header('Content-Type: application/json');

if (!isset($_SESSION['pelanggan'])) {
  echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit;
}
$id = intval($_SESSION['pelanggan']['id']);

$old = $_POST['password_lama'] ?? '';
$new = $_POST['password_baru'] ?? '';
$confirm = $_POST['konfirmasi_password'] ?? '';

if ($new === '' || $confirm === '') {
  echo json_encode(['success'=>false,'message'=>'Lengkapi kolom password baru dan konfirmasi']); exit;
}
if ($new !== $confirm) {
  echo json_encode(['success'=>false,'message'=>'Password baru dan konfirmasi tidak sama']); exit;
}

// ambil password lama hash dari db
$stmt = mysqli_prepare($conn, "SELECT password FROM pelanggan WHERE id_pelanggan = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
if (!$row) { echo json_encode(['success'=>false,'message'=>'User tidak ditemukan']); exit; }

$dbpass = $row['password'];

if (!password_verify($old, $dbpass)) {
  echo json_encode(['success'=>false,'message'=>'Password lama salah']); exit;
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$u = mysqli_prepare($conn, "UPDATE pelanggan SET password = ? WHERE id_pelanggan = ?");
mysqli_stmt_bind_param($u, "si", $hash, $id);
$ok = mysqli_stmt_execute($u);

if ($ok) echo json_encode(['success'=>true,'message'=>'Password berhasil diubah']);
else echo json_encode(['success'=>false,'message'=>'Gagal mengubah password: '.mysqli_error($conn)]);
