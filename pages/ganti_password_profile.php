<?php
include "../api/koneksi.php";
session_start();

// ✅ Cek login
if (!isset($_SESSION['pelanggan'])) {
  header("Location: ../index.php");
  exit;
}

$id = $_SESSION['pelanggan']['id_pelanggan'] ?? null;
$msg = '';
$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $lama  = trim($_POST['password_lama'] ?? '');
  $baru  = trim($_POST['password_baru'] ?? '');
  $ulang = trim($_POST['konfirmasi_password'] ?? '');

  // 🔍 Ambil password lama dari database
  $query = mysqli_prepare($conn, "SELECT password FROM pelanggan WHERE id_pelanggan=?");
  mysqli_stmt_bind_param($query, "i", $id);
  mysqli_stmt_execute($query);
  $result = mysqli_stmt_get_result($query);
  $user = mysqli_fetch_assoc($result);

  if (!$user || !password_verify($lama, $user['password'])) {
    $msg = "Password lama salah.";
    $alert = "error";
  } elseif ($baru !== $ulang) {
    $msg = "Password baru tidak cocok.";
    $alert = "warning";
  } elseif (strlen($baru) < 6) {
    $msg = "Password baru minimal 6 karakter.";
    $alert = "warning";
  } else {
    $hash = password_hash($baru, PASSWORD_DEFAULT);
    $update = mysqli_prepare($conn, "UPDATE pelanggan SET password=? WHERE id_pelanggan=?");
    mysqli_stmt_bind_param($update, "si", $hash, $id);
    mysqli_stmt_execute($update);
    $msg = "Password berhasil diganti!";
    $alert = "success";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Ganti Password - Profil Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .box { max-width: 420px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .header { background: #ffc107; padding: 15px; text-align: center; font-weight: 700; font-size: 1.2rem; }
    .form-control { border-radius: 8px; }
    .btn-warning { font-weight: 600; }
    a { text-decoration: none; color: #0d6efd; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<div class="box">
  <div class="header">🔒 Ganti Password</div>
  <div class="p-4">
    <form method="POST">
      <div class="mb-3">
        <label>Password Lama</label>
        <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password lama" required>
        <small><a href="../auth/lupa_password_profile.php?from=profile" class="text-danger"><i class="bi bi-question-circle"></i> Lupa Password?</a></small>
      </div>
      <div class="mb-3">
        <label>Password Baru</label>
        <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required>
      </div>
      <div class="mb-3">
        <label>Konfirmasi Password Baru</label>
        <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru" required>
      </div>
      <button class="btn btn-warning w-100">Simpan Perubahan</button>
    </form>

    <div class="mt-4 text-center">
      <a href="profile.php">⬅️ Kembali ke Profil</a>
    </div>
  </div>
</div>

<?php if ($msg): ?>
<script>
Swal.fire({
  icon: '<?= $alert ?>',
  title: '<?= $alert === "success" ? "Berhasil!" : "Perhatian" ?>',
  text: '<?= $msg ?>',
  showConfirmButton: true,
  confirmButtonColor: '#ffc107'
});
</script>
<?php endif; ?>

</body>
</html>
