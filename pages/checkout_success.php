<?php
session_start();
// Pastikan user sudah login
if (empty($_SESSION['pelanggan'])) {
    header("Location: ../index.php");
    exit;
}

include "../includes/config.php"; // Untuk koneksi ($conn)

// --- (Logika PHP validasi Anda sudah benar, tidak saya ubah) ---
$userId = intval($_SESSION['pelanggan']['id'] ?? $_SESSION['pelanggan']['id_pelanggan'] ?? 0);
if ($userId <= 0) {
    die("Error: ID Pelanggan tidak ditemukan di session.");
}
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$allowed = false;

if (!empty($_SESSION['order_success_flag'])) {
    $allowed = true;
}
if (!$allowed && $order_id > 0) {
    $stmt = mysqli_prepare($conn, "SELECT status, id_pelanggan FROM pesanan WHERE id_pesanan = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $o = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if ($o && intval($o['id_pelanggan']) === $userId && in_array($o['status'], ['paid','processing','Diproses','Dikirim','Selesai', 'Menunggu Konfirmasi'])) {
            $allowed = true;
        }
    }
}
if (!$allowed) {
    header("Location: ../index.php?page=menu");
    exit;
}
unset($_SESSION['order_success_flag']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pesanan Berhasil! - Goreng Chicken Co.</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh; padding-top: 50px; padding-bottom: 50px;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            
            <div class="card shadow-sm border-0 text-center p-4" style="border-radius: 15px;">
                <div class="card-body">
                    
                    <img src="../assets/img/logo.png" alt="Goreng Chicken Logo" style="width: 100px; margin-bottom: 20px;">
                    
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 80px; margin-bottom: 20px;"></i>
                    
                    <h2 class="fw-bold text-dark mb-3">Terima Kasih!</h2>
                    
                    <p class="text-muted mb-4" style="font-size: 1.1rem;">
                        Pesanan Anda (ID: <strong><?= htmlspecialchars($order_id) ?></strong>) telah kami terima.
                        <br>
                        <strong class="text-success">Mohon tunggu, pesanan Anda sedang kami proses.</strong>
                    </p>
                    
                    <p class="text-muted">
                        Anda dapat melihat status pesanan Anda di halaman "Riwayat Pesan".
                    </p>
                    
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                        <a href="pesanan_saya.php" class="btn btn-danger btn-lg fw-semibold">
                            <i class="bi bi-receipt"></i> Lihat Riwayat Pesan
                        </a>
                        <a href="../index.php?page=menu" class="btn btn-outline-secondary btn-lg fw-semibold">
                            <i class="bi bi-arrow-left"></i> Kembali ke Menu
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php
// Footer dihapus
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>