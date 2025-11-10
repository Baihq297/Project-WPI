<?php
// 🔹 Nonaktifkan tampilan error sensitif di produksi
error_reporting(E_ALL);
ini_set('display_errors', 0);

// 🔹 Pengaturan database (sesuaikan jika perlu)
$host = "localhost";
$user = "root";
$pass = "";
// 🍗 DIUBAH: Ganti nama database untuk Fried Chicken
$db   = "db_gorengchicken"; // Harus sama persis dengan nama database di phpMyAdmin

// 🔹 Buat koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// 🔹 Set timezone agar waktu di database konsisten
date_default_timezone_set('Asia/Jakarta');

// 🔹 Cek koneksi
if (!$conn) {
    // Kalau file ini dipanggil lewat API (seperti /api/get_menu.php)
    if (strpos($_SERVER['PHP_SELF'], '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Koneksi ke database gagal: ' . mysqli_connect_error()
        ]);
        exit;
    } else {
        // Kalau dipanggil lewat halaman biasa (HTML)
        die("<div style='color:red;font-family:monospace'>
            ❌ Koneksi database gagal: " . htmlspecialchars(mysqli_connect_error()) . "
        </div>");
    }
}
?>