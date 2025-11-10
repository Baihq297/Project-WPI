<?php
session_start();
include "koneksi.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔹 Proteksi agar hanya admin yang bisa menjalankan
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
    die("❌ Akses ditolak! Hanya admin yang boleh menjalankan script ini.");
}

echo "<h3>🔐 Hashing Password Pelanggan yang Belum Aman...</h3>";

$q = mysqli_query($conn, "SELECT id_pelanggan, password FROM pelanggan");

if (mysqli_num_rows($q) === 0) {
    die("⚠️ Tidak ada data pelanggan ditemukan.");
}

$updated = 0;
$skipped = 0;

while ($r = mysqli_fetch_assoc($q)) {
    $id = $r['id_pelanggan'];
    $pw = $r['password'];

    // 🔸 Jika sudah di-hash (bcrypt/argon2), lewati
    if (strpos($pw, '$2y$') === 0 || strpos($pw, '$argon') === 0) {
        echo htmlspecialchars("⏩ Skip ID: $id (sudah hash)<br>");
        $skipped++;
        continue;
    }

    // 🔸 Buat hash baru
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $safe_hash = mysqli_real_escape_string($conn, $hash);

    $ok = mysqli_query($conn, "UPDATE pelanggan SET password='$safe_hash' WHERE id_pelanggan=$id");

    if ($ok) {
        echo htmlspecialchars("✅ Updated ID: $id<br>");
        $updated++;
    } else {
        echo htmlspecialchars("❌ Gagal update ID: $id (" . mysqli_error($conn) . ")<br>");
    }
}

echo "<hr><b>Selesai!</b><br>";
echo "🔸 Password di-hash ulang: $updated<br>";
echo "🔸 Password dilewati (sudah aman): $skipped<br>";
?>
