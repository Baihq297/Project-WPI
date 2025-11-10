<?php
require_once "../api/koneksi.php";

$tokenPlain = $_GET['token'] ?? '';
$msg = '';
$icon = 'info'; 

if ($tokenPlain === '') {
  die("Token tidak valid!");
}

$tokenHash = hash('sha256', $tokenPlain);

$stmt = mysqli_prepare($conn, "SELECT * FROM pelanggan WHERE reset_token = ? AND reset_expired > NOW()");
mysqli_stmt_bind_param($stmt, "s", $tokenHash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
  die("Token tidak valid atau sudah kedaluwarsa.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pass1 = trim($_POST['password_baru']);
  $pass2 = trim($_POST['konfirmasi_password']);

  if ($pass1 !== $pass2) {
    $msg = "Password tidak cocok!";
    $icon = "error";
  } elseif (strlen($pass1) < 6) {
    $msg = "Password minimal 6 karakter!";
    $icon = "warning";
  } else {
    $hash = password_hash($pass1, PASSWORD_DEFAULT);
    $stmt2 = mysqli_prepare($conn, "UPDATE pelanggan SET password=?, reset_token=NULL, reset_expired=NULL WHERE id_pelanggan=?");
    mysqli_stmt_bind_param($stmt2, "si", $hash, $user['id_pelanggan']);
    mysqli_stmt_execute($stmt2);

    header("Refresh:2; url=../index.php?reset=success");
    $msg = "Password berhasil diubah! Anda akan diarahkan ke halaman login...";
    $icon = "success";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Reset Password - Goreng Chicken Co.</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #fffefa; }
    .card { border: 1px solid #ffc107; border-radius: 12px; }
    .card-header { background: #ffc107; color: #000; font-weight: bold; border-radius: 12px 12px 0 0; }
    .btn-warning { background-color: #ffc107; border: none; color: #000; font-weight: 600; }
    .btn-warning:hover { background-color: #ffcd39; }
    .alert-info { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="card mx-auto shadow-sm" style="max-width:420px;">
    <div class="card-header text-center">
      🔒 Reset Password
    </div>
    <div class="card-body">
      
      <?php if ($icon !== 'success'): ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Password Baru</label>
          <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Konfirmasi Password</label>
          <input type="password" name="konfirmasi_password" class="form-control" required>
        </div>
        <button class="btn btn-warning w-100">Ubah Password</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
// Cek jika ada pesan ($msg) yang diset oleh PHP
if ($msg):
    
    $title = 'Info';
    if ($icon === 'success') {
        $title = 'Berhasil!';
    } elseif ($icon === 'error') {
        $title = 'Oops!';
    } elseif ($icon === 'warning') {
        $title = 'Perhatian!';
    }
?>
<script>
// Skrip ini sekarang aman karena library SweetAlert2 sudah dimuat di atas
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        title: <?= json_encode($title) ?>,
        text: <?= json_encode($msg) ?>,
        icon: <?= json_encode($icon) ?>,
        
        showConfirmButton: <?= ($icon === 'success') ? 'false' : 'true' ?>,
        timer: <?= ($icon === 'success') ? 2000 : 'null' ?>,
        confirmButtonText: 'OK',
        confirmButtonColor: '#ffc107', 
        color: '#000'
    });
});
</script>
<?php endif; // Akhir dari cek $msg ?>

</body>
</html>