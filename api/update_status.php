<?php
require_once "koneksi.php";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// 🔒 Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Gunakan metode POST untuk mengirim data']);
  exit;
}

// 🔹 Ambil data dari POST
$id = isset($_POST['id_menu']) ? intval($_POST['id_menu']) : 0;
$status_input = trim($_POST['status'] ?? ''); // 'aktif' or 'nonaktif'

// 🔹 Validasi parameter
if ($id <= 0 || !in_array(strtolower($status_input), ['aktif', 'nonaktif'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
  exit;
}

// 🔹 Ambil data menu (STOK dan STATUS LAMA)
$check = mysqli_prepare($conn, "SELECT stok, status FROM menu WHERE id_menu = ?");
mysqli_stmt_bind_param($check, "i", $id);
mysqli_stmt_execute($check);
$res = mysqli_stmt_get_result($check);
$menu = mysqli_fetch_assoc($res);

if (!$menu) {
  http_response_code(404);
  echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
  exit;
}

$stok_saat_ini = intval($menu['stok']);
$status_baru = '';

// === Logika Inti: Cek Stok ===
if (strtolower($status_input) === 'aktif') {
    // Admin ingin menyalakan (Tersedia)
    if ($stok_saat_ini <= 0) {
        // JANGAN IZINKAN JIKA STOK 0
        http_response_code(409); // 409 Conflict
        echo json_encode([
            'success' => false, 
            'message' => 'Gagal! Stok 0. Tidak bisa diatur "Tersedia".'
        ]);
        exit;
    }
    $status_baru = 'tersedia';
} else {
    // Admin ingin mematikan (Habis)
    $status_baru = 'habis';
}

// 🔹 Update status ke database
$stmt = mysqli_prepare($conn, "UPDATE menu SET status = ? WHERE id_menu = ?");
mysqli_stmt_bind_param($stmt, "si", $status_baru, $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
  http_response_code(200);
  echo json_encode([
    'success' => true,
    'message' => 'Status menu diperbarui menjadi ' . $status_baru,
    'new_status' => $status_baru
  ]);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Gagal update: ' . mysqli_error($conn)]);
}
?>