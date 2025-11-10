<?php
// api/reset_password_submit.php
require_once "koneksi.php";
header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);
$tokenPlain = $_POST['token'] ?? '';
$new = $_POST['password_baru'] ?? '';
$confirm = $_POST['konfirmasi_password'] ?? '';

if (!$id || !$tokenPlain || $new==='' || $confirm==='') {
  echo json_encode(['success'=>false,'message'=>'Parameter tidak lengkap']); exit;
}
if ($new !== $confirm) { echo json_encode(['success'=>false,'message'=>'Password tidak cocok']); exit; }

$tokenHash = hash('sha256', $tokenPlain);
$stmt = mysqli_prepare($conn, "SELECT reset_expired FROM pelanggan WHERE id_pelanggan = ? AND reset_token = ?");
mysqli_stmt_bind_param($stmt, "is", $id, $tokenHash);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
if (!$row) { echo json_encode(['success'=>false,'message'=>'Token tidak valid']); exit; }
if (strtotime($row['reset_expired']) < time()) { echo json_encode(['success'=>false,'message'=>'Token kadaluarsa']); exit; }

$hash = password_hash($new, PASSWORD_DEFAULT);
$u = mysqli_prepare($conn, "UPDATE pelanggan SET password = ?, reset_token = NULL, reset_expired = NULL WHERE id_pelanggan = ?");
mysqli_stmt_bind_param($u, "si", $hash, $id);
$ok = mysqli_stmt_execute($u);
if ($ok) echo json_encode(['success'=>true,'message'=>'Password berhasil diubah']);
else echo json_encode(['success'=>false,'message'=>'Gagal menyimpan: '.mysqli_error($conn)]);
