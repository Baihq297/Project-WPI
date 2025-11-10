<?php
// proses_checkout.php (sebelumnya bernama checkout_payment.php di komen)
session_start();
include "../includes/config.php"; // sesuaikan path seperti file lain ($conn)

// Pastikan user login
if (empty($_SESSION['pelanggan'])) {
    header("Location: ../index.php");
    exit;
}

// Ambil ID pelanggan dari session
$userId = intval($_SESSION['pelanggan']['id'] ?? $_SESSION['pelanggan']['id_pelanggan'] ?? 0);
if ($userId <= 0) {
    die("Error: ID Pelanggan tidak ditemukan di session.");
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    header("Location: ../index.php?page=menu");
    exit;
}

// Ambil order dan cek kepemilikan & status
// DIUBAH: Menggunakan tabel 'pesanan', 'id_pesanan', dan 'total'
$stmt = mysqli_prepare($conn, "SELECT id_pesanan, id_pelanggan, total, status FROM pesanan WHERE id_pesanan = ?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Cek jika order tidak ada atau bukan milik user yang login
if (!$order || intval($order['id_pelanggan']) !== $userId) {
    header("Location: ../index.php?page=menu");
    exit;
}

// Jika order sudah dibayar (status 'paid' atau 'processing'), langsung arahkan ke sukses
if (in_array($order['status'], ['paid','processing','Dikirim','Selesai'])) {
    $_SESSION['order_success_flag'] = true;
    header("Location: checkout_success.php?order_id={$order_id}");
    exit;
}

// Jika form submit -> proses payment (disini simulasi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['payment_method'] ?? '';
    $allowed = ['transfer','cod','va'];
    
    if (!in_array($method, $allowed)) {
        $error = "Metode pembayaran tidak valid.";
    } else {
        // === Lakukan proses pembayaran di sini ===
        // Untuk demo kita anggap sukses:
        $payment_success = true;

        if ($payment_success) {
            // Update order status menjadi 'paid' atau 'processing'
            // DIUBAH: Menggunakan tabel 'pesanan' dan 'id_pesanan'
            $stmtUp = mysqli_prepare($conn, "UPDATE pesanan SET status = ?, payment_method = ?, paid_at = NOW(), updated_at = NOW() WHERE id_pesanan = ?");
            
            // Atur status berdasarkan metode bayar
            // Jika COD, mungkin statusnya 'Diproses', bukan 'paid'
            $statusTo = ($method === 'cod') ? 'Diproses' : 'paid';
            
            mysqli_stmt_bind_param($stmtUp, "ssi", $statusTo, $method, $order_id);
            mysqli_stmt_execute($stmtUp);
            mysqli_stmt_close($stmtUp);

            // Tandai session agar checkout_success bisa diakses
            $_SESSION['order_success_flag'] = true;

            // Kosongkan keranjang session
            unset($_SESSION['keranjang']);

            // Redirect ke halaman sukses
            // DIUBAH: Menggunakan 'order_id' yang konsisten
            header("Location: checkout_success.php?order_id={$order_id}");
            exit;
        } else {
            $error = "Pembayaran gagal, silakan coba ulang.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Pembayaran - Goreng Chicken Co.</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include "../layout/navbar.php"; // optional - agar konsisten dengan tampilan ?>
<div class="container" style="padding-top:100px; padding-bottom:80px;">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow-sm p-4">
        <h4 class="fw-bold mb-3">Pilih Metode Pembayaran</h4>

        <p>Total yang harus dibayar: <strong>Rp <?= number_format($order['total'],0,',','.') ?></strong></p>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_transfer" value="transfer" checked>
              <label class="form-check-label" for="pm_transfer">Transfer Bank</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_va" value="va">
              <label class="form-check-label" for="pm_va">Virtual Account</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="cod">
              <label class="form-check-label" for="pm_cod">Cash on Delivery (Bayar di tempat)</label>
            </div>
          </div>

          <div class="d-grid gap-2">
            <button class="btn btn-primary" type="submit">Bayar Sekarang</button>
            <a href="../index.php?page=menu" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php include "../layout/footer.php"; ?>
</body>
</html>