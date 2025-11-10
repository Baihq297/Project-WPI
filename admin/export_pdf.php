<?php
session_start();
require '../vendor/autoload.php'; 
include "../api/koneksi.php";

use Dompdf\Dompdf;

// 🔒 Pastikan hanya admin
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  header("Location: ../index.php?login_required=1");
  exit;
}

// 🔹 Filter tanggal
$where = "";
$periode = "Semua Data";
$tgl1 = $_GET['tgl_mulai'] ?? ''; 
$tgl2 = $_GET['tgl_selesai'] ?? ''; 

if (!empty($tgl1) && !empty($tgl2)) {
  $tgl1_safe = mysqli_real_escape_string($conn, $tgl1);
  $tgl2_safe = mysqli_real_escape_string($conn, $tgl2);
  $where = "WHERE DATE(p.created_at) BETWEEN '$tgl1_safe' AND '$tgl2_safe'";
  $periode = date('d M Y', strtotime($tgl1)) . " s.d " . date('d M Y', strtotime($tgl2));
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

// 🔹 Inisialisasi total pendapatan
$total_pendapatan = 0;

// 🔹 Header HTML
$html = '
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
  h3 { text-align: center; color: #B22222; margin-bottom: 0; }
  p { text-align: center; margin-top: 2px; color: #555; font-size: 10px; }
  table { border-collapse: collapse; width: 100%; margin-top: 15px; }
  th, td { border: 1px solid #999; padding: 5px; text-align: left; }
  th { background-color: #ffc107; color: #111; text-align: center; }
  tr:nth-child(even) { background-color: #fff7e6; }
  .total-row td { font-weight: bold; background: #ffeeba; }
  .text-center { text-align: center; }
  .text-right { text-align: right; }
</style>

<h3>LAPORAN DETAIL PESANAN GORENG CHICKEN CO.</h3>
<p>Periode: ' . htmlspecialchars($periode) . '</p>

<table>
  <tr>
    <th>ID Pesanan</th>
    <th>Tanggal</th>
    <th>Pelanggan</th>
    <th>Menu</th>
    <th>Qty</th>
    <th>Harga Satuan</th>
    <th>Subtotal</th>
    <th>Status</th>
  </tr>
';

// 🔹 Data tabel
$no = 1;
while ($r = mysqli_fetch_assoc($res)) {
  // PERBAIKAN: Total pendapatan sekarang menjumlahkan subtotal per item
  $total_pendapatan += $r['subtotal'];
  $html .= "
  <tr>
    <td class='text-center'>#{$r['id_pesanan']}</td>
    <td>" . date('d/m/Y H:i', strtotime($r['created_at'])) . "</td>
    <td>".htmlspecialchars($r['nama_pelanggan'])."</td>
    <td>".htmlspecialchars($r['nama_menu'])."</td>
    <td class='text-center'>".htmlspecialchars($r['qty'])."</td>
    <td class='text-right'>Rp " . number_format($r['harga_satuan'], 0, ',', '.') . "</td>
    <td class='text-right'>Rp " . number_format($r['subtotal'], 0, ',', '.') . "</td>
    <td class='text-center'>".htmlspecialchars($r['status'])."</td>
  </tr>";
  $no++;
}

// 🔹 Jika tidak ada data
if ($no == 1) {
  $html .= '<tr><td colspan="8" class="text-center">Tidak ada data pesanan pada periode ini.</td></tr>';
}

// 🔹 Tambahkan total pendapatan
$html .= '
  <tr class="total-row">
    <td colspan="6" class="text-right">Total Pendapatan</td>
    <td colspan="2" class="text-right">Rp ' . number_format($total_pendapatan, 0, ',', '.') . '</td>
  </tr>
</table>
<p style="text-align:right; margin-top:30px; font-size:10px;">
Dicetak pada: ' . date('d/m/Y H:i') . '
</p>
';

// 🔹 Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// 🔹 Nama file otomatis
$filename = "Laporan_Detail_Pesanan_GorengChicken_";
if (!empty($tgl1) && !empty($tgl2)) {
    $filename .= $tgl1 . "_sampai_" . $tgl2 . ".pdf";
} else {
    $filename .= "Semua_Tanggal_" . date('Ymd') . ".pdf";
}

$dompdf->stream($filename, ["Attachment" => true]);
?>