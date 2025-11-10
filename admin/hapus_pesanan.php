<?php
session_start();
include "../api/koneksi.php";

if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  header("Location: ../index.php?login_required=1");
  exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
  $stmt = mysqli_prepare($conn, "DELETE FROM pesanan WHERE id_pesanan = ?");
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);

  if (mysqli_stmt_affected_rows($stmt) > 0) {
    $_SESSION['notif'] = "✅ Pesanan berhasil dihapus!";
    $_SESSION['notif_type'] = "success";
  } else {
    $_SESSION['notif'] = "❌ Gagal menghapus pesanan!";
    $_SESSION['notif_type'] = "error";
  }
} else {
  $_SESSION['notif'] = "⚠️ ID pesanan tidak valid!";
  $_SESSION['notif_type'] = "error";
}

// Redirect kembali ke laporan
header("Location: laporan_pesanan.php");
exit;
?>