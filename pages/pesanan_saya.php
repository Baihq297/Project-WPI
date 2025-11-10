<?php
// pastikan tidak ada whitespace atau BOM sebelum tag PHP
session_start();
$BASE_URL = "..";
$currentPage = 'static';

// include konfigurasi DB dulu (tanpa include navbar)
include "../includes/config.php"; // pastikan $conn tersedia

// Cek login pelanggan (harus sebelum include navbar / output apapun)
if (!isset($_SESSION['pelanggan'])) {
    header("Location: ../index.php?page=home");
    exit;
}

$id_pelanggan = intval($_SESSION['pelanggan']['id'] ?? $_SESSION['pelanggan']['id_pelanggan'] ?? 0);
$nama_raw = $_SESSION['pelanggan']['nama'] ?? 'Pelanggan';
$nama = htmlspecialchars($nama_raw, ENT_QUOTES);

if ($id_pelanggan <= 0) {
    // tampilkan halaman error tanpa include navbar untuk menghindari output ganda
    echo "<!DOCTYPE html><html lang='id'><head><title>Error</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body>";
    echo "<div class='container py-5' style='margin-top: 80px;'><div class='alert alert-danger'>Error: Sesi pelanggan tidak valid. Silakan login ulang.</div></div>";
    include "../layout/footer.php";
    echo "</body></html>";
    exit;
}

// Sekarang aman untuk include navbar karena tidak akan redirect
include "../layout/navbar.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Pesanan - <?= $nama ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* CSS perbaikan layout accordion */
.accordion-button {
  display: flex;
  align-items: center;
  padding-right: 1rem;
  overflow: hidden;
}
.accordion-button .order-info-group {
  flex-grow: 1;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
  padding-right: 1rem;
}
.accordion-button .status-wrapper {
  margin-left: auto !important;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.accordion-button .total-price {
  flex-shrink: 0;
  font-weight: 700;
  color: var(--bs-dark);
}
.accordion-button .badge {
  font-size: 0.72rem;
  padding: 0.22rem 0.45rem;
}
@media (max-width: 576px) {
    .accordion-button .text-muted.small { display: none !important; }
    .accordion-button .total-price { font-size: 0.95rem; }
}
</style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<div class="container py-5 flex-grow-1" style="margin-top: 80px;">
    <h3 class="text-danger mb-4 text-center fw-bold">📜 Riwayat Pesanan Anda</h3>

    <?php
    // Pastikan koneksi DB tersedia
    if (!isset($conn) || !$conn) {
        echo "<div class='alert alert-danger'>Koneksi database tidak tersedia.</div>";
    } else {
        $sql_pesanan = "
            SELECT id_pesanan, total, status, created_at, payment_method
            FROM pesanan
            WHERE id_pelanggan = ?
            ORDER BY id_pesanan DESC
        ";
        $stmt_pesanan = mysqli_prepare($conn, $sql_pesanan);
        if (!$stmt_pesanan) {
            echo "<div class='alert alert-danger'>Gagal menyiapkan query riwayat pesanan.</div>";
        } else {
            mysqli_stmt_bind_param($stmt_pesanan, "i", $id_pelanggan);
            mysqli_stmt_execute($stmt_pesanan);
            $result_pesanan = mysqli_stmt_get_result($stmt_pesanan);

            if ($result_pesanan && mysqli_num_rows($result_pesanan) > 0) {
                echo '<div class="accordion" id="accordionRiwayatPesanan">';

                $sql_detail = "
                    SELECT dp.qty, dp.harga, m.nama_menu
                    FROM detail_pesanan dp
                    JOIN menu m ON dp.id_menu = m.id_menu
                    WHERE dp.id_pesanan = ?
                ";
                $stmt_detail = mysqli_prepare($conn, $sql_detail);
                if (!$stmt_detail) {
                    $stmt_detail = null; // tandai tidak tersedia
                }

                while ($p = mysqli_fetch_assoc($result_pesanan)) {
                    $id_pesanan = (int) $p['id_pesanan'];
                    $total_harga_formatted = number_format($p['total'] ?? 0, 0, ',', '.');

                    $tanggal = '—';
                    if (!empty($p['created_at'])) {
                        $ts = strtotime($p['created_at']);
                        if ($ts !== false) $tanggal = date('d M Y, H:i', $ts);
                    }

                    $status_raw = $p['status'] ?? '';
                    $status_lower = strtolower($status_raw);

                    if ($status_lower == 'selesai') $badge = 'success';
                    elseif (in_array($status_lower, ['diproses', 'paid', 'processing'])) $badge = 'warning';
                    elseif ($status_lower == 'dibatalkan') $badge = 'secondary';
                    else $badge = 'info';

                    $metode_bayar_text = '';
                    if (!empty($p['payment_method'])) {
                        $pm = $p['payment_method'];
                        switch ($pm) {
                            case 'cod': $metode_readable = 'Bayar di Tempat'; break;
                            case 'transfer': $metode_readable = 'Transfer Bank'; break;
                            case 'va': $metode_readable = 'Virtual Account'; break;
                            default: $metode_readable = htmlspecialchars($pm, ENT_QUOTES); break;
                        }
                        $metode_bayar_text = "<span class='badge bg-dark'>{$metode_readable}</span>";
                    }

                    $status_badge = "<span class='badge bg-{$badge}'>" . htmlspecialchars($status_raw, ENT_QUOTES) . "</span>";
                    ?>
                    <div class="accordion-item shadow-sm mb-2">
                        <h2 class="accordion-header" id="heading-<?= $id_pesanan ?>">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $id_pesanan ?>">
                                <span class="order-info-group">
                                    <span class="fw-bold">Pesanan #<?= $id_pesanan ?></span>
                                    <span class="text-muted small ms-3">(<?= htmlspecialchars($tanggal, ENT_QUOTES) ?>)</span>
                                </span>

                                <span class="status-wrapper ms-auto me-3">
                                    <?= $status_badge ?>
                                    <?= $metode_bayar_text ?>
                                </span>

                                <span class="total-price text-dark">Total: Rp <?= $total_harga_formatted ?></span>
                            </button>
                        </h2>
                        <div id="collapse-<?= $id_pesanan ?>" class="accordion-collapse collapse" data-bs-parent="#accordionRiwayatPesanan">
                            <div class="accordion-body">
                                <h6 class="fw-bold">Detail Item:</h6>
                                <ul class="list-group list-group-flush">
                                    <?php
                                    if ($stmt_detail) {
                                        mysqli_stmt_bind_param($stmt_detail, "i", $id_pesanan);
                                        mysqli_stmt_execute($stmt_detail);
                                        $result_detail = mysqli_stmt_get_result($stmt_detail);
                                        if ($result_detail) {
                                            while ($d = mysqli_fetch_assoc($result_detail)) {
                                                $nama_menu = htmlspecialchars($d['nama_menu'] ?? '-', ENT_QUOTES);
                                                $qty = (int) ($d['qty'] ?? 0);
                                                $harga = (float) ($d['harga'] ?? 0);
                                                $subtotal = number_format($harga * $qty, 0, ',', '.');
                                                ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <?= $nama_menu ?>
                                                        <small class="text-muted d-block">
                                                            <?= $qty ?> x Rp <?= number_format($harga, 0, ',', '.') ?>
                                                        </small>
                                                    </div>
                                                    <span class="fw-medium">Rp <?= $subtotal ?></span>
                                                </li>
                                                <?php
                                            }
                                        } else {
                                            echo "<li class='list-group-item'>Gagal mengambil detail pesanan.</li>";
                                        }
                                    } else {
                                        echo "<li class='list-group-item'>Query detail tidak tersedia.</li>";
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php
                } // end while pesanan

                if ($stmt_detail) mysqli_stmt_close($stmt_detail);
                echo '</div>'; // accordion
            } else {
                echo "<div class='alert alert-warning text-center shadow-sm'>Anda belum memiliki riwayat pesanan.</div>";
            }

            mysqli_stmt_close($stmt_pesanan);
        }
    } // end else koneksi
    ?>

    <div class="text-center mt-4">
        <a href="../index.php?page=menu" class="btn btn-outline-danger">
            <i class="bi bi-arrow-left"></i> Kembali ke Menu
        </a>
    </div>
</div>

<?php include "../layout/footer.php"; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
