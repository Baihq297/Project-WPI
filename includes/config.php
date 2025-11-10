<?php
// ===== Prevent direct access to this file =====
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diizinkan.');
}

// ===== Base URL dinamis (otomatis deteksi folder project) =====
$projectName = basename(dirname(__DIR__));
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "{$protocol}://{$host}/{$projectName}/";

// ===== Include koneksi database =====
include __DIR__ . '/../api/koneksi.php';

// ===== Jalankan session global =====
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optional: bisa dipakai untuk debugging
// echo "Config loaded from: includes/config.php";
// echo "<br>Base URL: $base_url";
?>
