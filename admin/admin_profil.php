<?php
session_start();
include "../api/koneksi.php"; // pastikan $conn ada dan valid

// 🔒 Hanya admin yang bisa akses
if (!isset($_SESSION['pelanggan']) || ($_SESSION['pelanggan']['role'] ?? '') !== 'admin') {
    header("Location: ../index.php?login_required=1");
    exit;
}

$id_admin = (int) $_SESSION['pelanggan']['id'];
$msg = "";
$icon = "info"; // Untuk SweetAlert

// Helper: redirect kalau $conn tidak tersedia
if (!isset($conn) || !$conn) {
    // Jika koneksi gagal, hentikan eksekusi agar tidak terjadi error lebih jauh
    $msg = "Koneksi database gagal. Periksa file koneksi.";
    $icon = "error";
}

// 🔹 Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($conn) && $conn) {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordLama = trim($_POST['password_lama'] ?? '');
    $passwordBaru = trim($_POST['password_baru'] ?? '');
    $konfirmasi = trim($_POST['konfirmasi_password'] ?? '');

    // Validasi dasar
    if ($nama === '' || $email === '') {
        $msg = "Nama dan email wajib diisi!";
        $icon = "warning";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Format email tidak valid!";
        $icon = "warning";
    } else {
        // Update nama & email
        $ok = false;
        $query = "UPDATE pelanggan SET nama = ?, email = ? WHERE id_pelanggan = ?";
        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $id_admin);
            $ok = mysqli_stmt_execute($stmt);
            // optional: mysqli_stmt_close($stmt);
        } else {
            $msg = "Gagal menyiapkan perintah update profil: " . mysqli_error($conn);
            $icon = "error";
        }

        $passwordMsg = '';
        // Jika ada usaha mengganti password, lakukan validasi lengkap
        if ($passwordLama !== '' || $passwordBaru !== '' || $konfirmasi !== '') {
            if ($passwordBaru === '' || $konfirmasi === '') {
                $passwordMsg = "Lengkapi semua kolom password baru dan konfirmasi!";
                $icon = "warning";
            } elseif ($passwordBaru !== $konfirmasi) {
                $passwordMsg = "Password baru dan konfirmasi tidak sama!";
                $icon = "warning";
            } else {
                // Ambil password hash yang tersimpan
                $cek = mysqli_prepare($conn, "SELECT password FROM pelanggan WHERE id_pelanggan = ?");
                if ($cek) {
                    mysqli_stmt_bind_param($cek, "i", $id_admin);
                    mysqli_stmt_execute($cek);
                    $result = mysqli_stmt_get_result($cek);
                    $row = mysqli_fetch_assoc($result);

                    if ($row && password_verify($passwordLama, $row['password'])) {
                        $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
                        $stmt2 = mysqli_prepare($conn, "UPDATE pelanggan SET password = ? WHERE id_pelanggan = ?");
                        if ($stmt2) {
                            mysqli_stmt_bind_param($stmt2, "si", $hashBaru, $id_admin);
                            $okPass = mysqli_stmt_execute($stmt2);
                            if ($okPass) {
                                $passwordMsg = "Password berhasil diganti.";
                                $icon = "success";
                            } else {
                                $passwordMsg = "Gagal menyimpan password baru.";
                                $icon = "error";
                            }
                        } else {
                            $passwordMsg = "Gagal menyiapkan query update password.";
                            $icon = "error";
                        }
                    } else {
                        $passwordMsg = "Password lama tidak sesuai!";
                        $icon = "error";
                    }
                    // mysqli_stmt_close($cek);
                } else {
                    $passwordMsg = "Gagal menyiapkan query pengecekan password.";
                    $icon = "error";
                }
            }
        }

        // Atur pesan akhir
        if (isset($ok) && $ok) {
            // Update session agar tampilan langsung berubah
            $_SESSION['pelanggan']['nama'] = $nama;
            $_SESSION['pelanggan']['email'] = $email;
            $msg = "Profil berhasil diperbarui." . ($passwordMsg ? " " . $passwordMsg : "");
            // Jika belum ada perubahan password menghasilkan sukses, jaga ikon sukses
            $icon = ($icon === 'info' || $icon === 'success') ? 'success' : $icon;
        } else {
            // Jika update profil gagal dan belum ada pesan error spesifik, beri pesan generik
            if ($msg === '') $msg = "Gagal memperbarui profil.";
            if ($passwordMsg) $msg .= " " . $passwordMsg;
            $icon = $icon === 'info' ? 'error' : $icon;
        }
    }
}

// 🔹 Ambil data admin (untuk menampilkan di form)
$admin = null;
if (isset($conn) && $conn) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM pelanggan WHERE id_pelanggan = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_admin);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        if (!$admin) {
            // kalau tidak ditemukan, redirect atau beri pesan
            $msg = "Data admin tidak ditemukan.";
            $icon = "error";
        }
    } else {
        $msg = "Gagal menyiapkan query ambil data admin.";
        $icon = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Edit Profil Admin - Goreng Chicken Co.</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --hitam: #1e1e1e;
      --kuning: #ffc107;
      --merah: #b22222;
      --putih: #fff;
      --abu: #f8f9fa;
    }
    body {
      background-color: var(--abu);
      font-family: 'Poppins', sans-serif;
      color: #333;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .card { background: #fffdfa; border: none; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .card-header { background-color: var(--hitam); color: var(--kuning); border-top-left-radius: 16px; border-top-right-radius: 16px; font-weight: 600; text-align: center; }
    .form-label { color: #444; font-weight: 500; }
    .form-control:focus { border-color: var(--kuning); box-shadow: 0 0 5px var(--kuning); }
    .btn-danger { background-color: var(--merah); border: none; }
    .btn-danger:hover { background-color: #941b1b; }
    .btn-secondary { background-color: var(--kuning); color: var(--hitam); border: none; }
    .btn-secondary:hover { background-color: #ffcd39; color: var(--hitam); }
  </style>
</head>
<body>

<div class="container">
  <div class="card mx-auto" style="max-width:500px;">
    <div class="card-header">
      <h4><i class="bi bi-person-circle"></i> Edit Profil Admin</h4>
    </div>
    <div class="card-body">
      <?php if (!$admin): ?>
        <div class="alert alert-danger">Tidak ada data admin untuk ditampilkan.</div>
      <?php else: ?>
      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label">Nama</label>
          <input name="nama" class="form-control" value="<?= htmlspecialchars($admin['nama'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
        </div>
        <hr>
        <h6 class="text-danger"><i class="bi bi-lock-fill"></i> Ubah Password (Opsional)</h6>
        <div class="mb-2">
          <label>Password Lama</label>
          <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password lama">
        </div>
        <div class="mb-2">
          <label>Password Baru</label>
          <input type="password" name="password_baru" class="form-control" placeholder="Masukkan password baru">
        </div>
        <div class="mb-3">
          <label>Konfirmasi Password Baru</label>
          <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru">
        </div>
        <button type="submit" class="btn btn-danger w-100 mb-3">
          <i class="bi bi-save"></i> Simpan Perubahan
        </button>
      </form>
      <?php endif; ?>
    </div>
    <div class="card-footer text-center bg-transparent border-0">
      <a href="dashboard.php" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
      </a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if ($msg): 
    $title = 'Info';
    if ($icon === 'success') $title = 'Berhasil!';
    elseif ($icon === 'error') $title = 'Gagal!';
    elseif ($icon === 'warning') $title = 'Perhatian!';
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        title: <?= json_encode($title) ?>,
        text: <?= json_encode(trim($msg)) ?>,
        icon: <?= json_encode($icon) ?>,
        confirmButtonText: 'OK',
        confirmButtonColor: '#b22222'
    });
});
</script>
<?php endif; ?>

</body>
</html>
