<?php
session_start();
include "../api/koneksi.php";

// 🔒 Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  header("Location: ../index.php?login_required=1");
  exit;
}

// 📅 Filter tanggal
$where = "";
if (isset($_GET['tgl_mulai']) && isset($_GET['tgl_selesai']) && !empty($_GET['tgl_mulai']) && !empty($_GET['tgl_selesai'])) {
  $tgl1 = mysqli_real_escape_string($conn, $_GET['tgl_mulai']);
  $tgl2 = mysqli_real_escape_string($conn, $_GET['tgl_selesai']);
  $where = "WHERE DATE(p.created_at) BETWEEN '$tgl1' AND '$tgl2'";
}

// 
// ============================================
//  PERBAIKAN: Query diubah untuk mengambil DETAIL ITEM
// ============================================
//
$query = "
  SELECT 
      p.id_pesanan, 
      p.created_at, 
      p.status, 
      p.payment_method,
      pel.nama AS nama_pelanggan,
      m.nama_menu,
      dp.qty,
      dp.harga AS harga_satuan,
      (dp.qty * dp.harga) AS subtotal
  FROM detail_pesanan dp
  JOIN pesanan p ON dp.id_pesanan = p.id_pesanan
  JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
  JOIN menu m ON dp.id_menu = m.id_menu
  $where
  ORDER BY p.id_pesanan DESC, m.nama_menu ASC
";
$res = mysqli_query($conn, $query);

// 📊 Header file Excel
header("Content-Type: application/vnd.ms-excel");
$filename = "Laporan_Detail_Pesanan_GorengChicken_" . date('Ymd_His') . ".xls";
header("Content-Disposition: attachment; filename=$filename");
header("Pragma: no-cache");
header("Expires: 0");

// 💡 Style dan Header Tabel Diperbarui
echo "
<table border='1' cellspacing='0' cellpadding='5'>
  <tr style='background:#b22222;color:white;'>
    <th>ID Pesanan</th>
    <th>Tanggal</th>
    <th>Nama Pelanggan</th>
    <th>Nama Menu</th>
    <th>Qty</th>
    <th>Harga Satuan (Rp)</th>
    <th>Subtotal (Rp)</th>
    <th>Metode Bayar</th>
    <th>Status</th>
  </tr>
";

$no = 1;
while ($r = mysqli_fetch_assoc($res)) {
  echo "
  <tr>
    <td>#{$r['id_pesanan']}</td>
    <td>" . date('d/m/Y H:i', strtotime($r['created_at'])) . "</td>
    <td>".htmlspecialchars($r['nama_pelanggan'])."</td>
    <td>".htmlspecialchars($r['nama_menu'])."</td>
    <td>".htmlspecialchars($r['qty'])."</td>
    <td>" . number_format($r['harga_satuan'], 0, ',', '.') . "</td>
    <td>" . number_format($r['subtotal'], 0, ',', '.') . "</td>
    <td>".htmlspecialchars($r['payment_method'] ?? '-')."</td>
    <td>".htmlspecialchars($r['status'])."</td>
  </tr>";
  $no++;
}
echo "</table>";
exit;
?>