<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

include "koneksi.php";

// 🔹 Ambil total penjualan 7 hari terakhir
$query = "
  SELECT DATE(created_at) AS tanggal, SUM(total) AS total
  FROM pesanan
  WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY DATE(created_at)
  ORDER BY tanggal ASC
";

$res = mysqli_query($conn, $query);

// 🔹 Jika tidak ada data
if (mysqli_num_rows($res) === 0) {
  echo json_encode([
    'tanggal' => [],
    'total' => [],
    'message' => 'Belum ada transaksi dalam 7 hari terakhir'
  ]);
  exit;
}

// 🔹 Olah hasil query
$tanggal = [];
$total = [];

setlocale(LC_TIME, 'id_ID.utf8'); // optional: format tanggal Indonesia

while ($row = mysqli_fetch_assoc($res)) {
  $tanggal[] = strftime('%d %b', strtotime($row['tanggal']));
  $total[] = intval($row['total']);
}

// 🔹 Kirim JSON ke frontend
echo json_encode([
  'tanggal' => $tanggal,
  'total' => $total,
  'message' => 'Data penjualan berhasil diambil'
]);
?>
