<?php
include "../api/koneksi.php";
session_start();

$msg = '';
$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass1 = trim($_POST['password_baru'] ?? '');
  $pass2 = trim($_POST['konfirmasi_password'] ?? '');

  if ($email === '' || $pass1 === '' || $pass2 === '') {
    $msg = "⚠️ Semua kolom wajib diisi.";
    $alert = "warning";
  } elseif ($pass1 !== $pass2) {
    $msg = "❌ Password tidak cocok.";
    $alert = "error";
  } else {
    $hash = password_hash($pass1, PASSWORD_DEFAULT);
    $update = mysqli_prepare($conn, "UPDATE pelanggan SET password=?, reset_token=NULL, reset_expired=NULL WHERE email=?");
    mysqli_stmt_bind_param($update, "ss", $hash, $email);
    mysqli_stmt_execute($update);
    $msg = "✅ Password berhasil diubah! Silakan login kembali.";
    $alert = "success";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - Warung Sate H. Faqih I</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f9f9f9; font-family: 'Poppins', sans-serif; }
    .box { max-width: 420px; margin: 100px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .header { background: #ffc107; padding: 15px; font-weight: 700; text-align: center; }
    .toast-popup { position: fixed; bottom: 25px; right: 25px; padding: 14px 22px; border-radius: 10px; font-weight: 500; opacity: 0; transition: 0.4s; z-index: 9999; }
    .show { opacity: 1; transform: translateY(0); }
    .success { background: #28a745; color: #fff; }
    .warning { background: #ffc107; color: #000; }
    .error { background: #dc3545; color: #fff; }
  </style>
</head>
<body>
<div class="box">
  <div class="header">🔑 Reset Password</div>
  <div class="p-4">
    <form method="POST">
      <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Masukkan email Anda" required></div>
      <div class="mb-3"><input type="password" name="password_baru" class="form-control" placeholder="Password baru" required></div>
      <div class="mb-3"><input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru" required></div>
      <button class="btn btn-warning w-100 fw-semibold">Ganti Password</button>
    </form>
    <div class="mt-4 text-center">
      <a href="../index.php" class="text-decoration-none">⬅️ Kembali ke Halaman Login</a>
    </div>
  </div>
</div>

<div id="toast" class="toast-popup <?= $alert ?>"><?= $msg ?></div>

<script>
const toast = document.getElementById('toast');
if (toast.textContent.trim() !== '') {
  toast.classList.add('show');
  setTimeout(()=> toast.classList.remove('show'), 4000);
}
</script>
</body>
</html>
