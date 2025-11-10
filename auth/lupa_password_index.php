<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../api/koneksi.php";
require_once "../includes/mail_config.php"; // ✅ gunakan konfigurasi email global

$msg = '';
$alertClass = '';
$icon = 'info'; // Default icon for SweetAlert

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');

  if ($email === '') {
    $msg = "⚠️ Email wajib diisi!";
    $alertClass = "alert-warning";
    $icon = "warning";
  } else {
    $stmt = mysqli_prepare($conn, "SELECT id_pelanggan FROM pelanggan WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
      // 🔑 Buat token reset
      $tokenPlain = bin2hex(random_bytes(32));
      $tokenHash = hash('sha256', $tokenPlain);
      $expired = date("Y-m-d H:i:s", time() + 3600);

      // 💾 Update database
      $updateStmt = mysqli_prepare($conn, "UPDATE pelanggan SET reset_token=?, reset_expired=? WHERE email=?");
      mysqli_stmt_bind_param($updateStmt, "sss", $tokenHash, $expired, $email);
      mysqli_stmt_execute($updateStmt);
      mysqli_stmt_close($updateStmt);

      // 📨 Siapkan isi email (HTML dan plain text)
      $year = date("Y");
      $mailBody = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Goreng Chicken</title>
  <style>
    body { background:#f6f6f6; font-family:Poppins,Arial,sans-serif; margin:0; padding:0; }
    .container { max-width:480px; margin:40px auto; background:#fff; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); overflow:hidden; }
    .header { background:#ffc107; text-align:center; padding:20px; color:#000; }
    .header img { width:80px; margin-bottom:10px; }
    .content { padding:25px; text-align:center; }
    .content h2 { margin-bottom:10px; }
    .content p { line-height:1.6; margin:0 0 20px 0; }
    .btn { display:inline-block; background:#dc3545; color:#fff; padding:12px 24px; border-radius:8px; font-weight:600; text-decoration:none; transition:background 0.3s ease; }
    .btn:hover { background:#b02a37; }
    .footer { background:#f9f9f9; text-align:center; padding:15px; font-size:13px; color:#777; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="cid:logo_gc" alt="Logo Goreng Chicken" style="width:100px; border-radius:8px; margin-bottom:10px;">
      <h1>Goreng Chicken</h1>
    </div>
    <div class="content">
      <h2>Reset Password Akun Anda</h2>
      <p>Halo, kami menerima permintaan untuk mereset password akun Anda di <b>Goreng Chicken</b>.</p>
      <p>Klik tombol di bawah ini untuk mengatur ulang password Anda:</p>
      <a href="http://localhost/GorengChicken/auth/reset_password.php?token={$tokenPlain}" class="btn">🔐 Reset Password</a>
      <p style="margin-top:25px; font-size:13px; color:#666;">
        Tautan ini hanya berlaku selama <b>1 jam</b> sejak email ini dikirim.<br>
        Jika Anda tidak meminta reset password, abaikan saja email ini.
      </p>
    </div>
    <div class="footer">
      &copy; {$year} Goreng Chicken — Semua hak dilindungi.
    </div>
  </div>
</body>
</html>
HTML;

      if (isset($mail) && is_object($mail)) {
        $mail->isHTML(true);
        $mail->Body = $mailBody;
        $mail->AltBody = "Buka tautan berikut untuk mengganti password Anda: http://localhost/GorengChicken/auth/reset_password.php?token={$tokenPlain}";
      }

      $send = sendMail($email, 'Reset Password Akun Anda', $mailBody);

      if ($send['success']) {
        $msg = "Link reset password telah dikirim ke email Anda.";
        $alertClass = "alert-success";
        $icon = "success";
      } else {
        $msg = "Gagal mengirim email: " . $send['message'];
        $alertClass = "alert-danger";
        $icon = "error";
      }
    } else {
      $msg = "Email tidak ditemukan dalam sistem kami.";
      $alertClass = "alert-warning";
      $icon = "warning";
    }
    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Lupa Password - Goreng Chicken</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f9f9f9; font-family: 'Poppins', sans-serif; }
    .reset-box { max-width: 420px; margin: 100px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .reset-header { background: #ffc107; color: #212529; padding: 15px; font-weight: 700; text-align: center; }
    .reset-body { padding: 30px; }
    .btn-danger { background-color: #dc3545; border: none; font-weight: 600; }
    .btn-danger:hover { background-color: #e63a46; }
  </style>
</head>
<body>

<div class="reset-box">
  <div class="reset-header"><i class="bi bi-key"></i> Lupa Password</div>
  <div class="reset-body">
    <p class="text-center mb-3">Masukkan email Anda untuk menerima tautan reset password.</p>
    
    <form method="POST">
      <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Masukkan Email Anda" required>
      </div>
      <button type="submit" class="btn btn-danger w-100">Kirim Link Reset</button>
    </form>
    <div class="mt-4 text-center">
      <a href="../index.php" class="text-decoration-none">
        <i class="bi bi-arrow-left-square"></i> Kembali ke Halaman Utama
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// Cek jika ada pesan ($msg) yang diset oleh PHP
if ($msg):
    
    // Tentukan judul pop-up berdasarkan ikonnya
    $title = 'Info';
    if ($icon === 'success') {
        $title = 'Berhasil!';
    } elseif ($icon === 'error') {
        $title = 'Gagal!';
    } elseif ($icon === 'warning') {
        $title = 'Perhatian!';
    }
?>
<script>
// Jalankan SweetAlert2 saat halaman dimuat
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        // --- PERBAIKAN DI SINI ---
        // Menggunakan json_encode() adalah cara paling aman
        // untuk memasukkan string PHP ke JavaScript.
        title: <?= json_encode($title) ?>,
        text: <?= json_encode($msg) ?>,
        icon: <?= json_encode($icon) ?>,
        // --- AKHIR PERBAIKAN ---

        confirmButtonText: 'OK',
        confirmButtonColor: '#dc3545' // Warna tombol merah
    });
});
</script>
<?php endif; // Akhir dari cek $msg ?>

</body>
</html>