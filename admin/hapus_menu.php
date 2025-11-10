<?php
session_start();
include "../api/koneksi.php";

if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  header("Location: ../index.php?login_required=1");
  exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
  $res = mysqli_query($conn, "SELECT gambar FROM menu WHERE id_menu = $id");
  $menu = mysqli_fetch_assoc($res);

  if ($menu) {
    if ($menu['gambar'] !== 'placeholder.jpg' && file_exists("../assets/img/" . $menu['gambar'])) {
      unlink("../assets/img/" . $menu['gambar']);
    }

    $del = mysqli_query($conn, "DELETE FROM menu WHERE id_menu = $id");

    if ($del) {
      $_SESSION['notif'] = "✅ Menu berhasil dihapus!";
      $_SESSION['notif_type'] = "success";
    } else {
      $_SESSION['notif'] = "❌ Gagal menghapus menu: " . mysqli_error($conn);
      $_SESSION['notif_type'] = "error";
    }
  } else {
    $_SESSION['notif'] = "⚠️ Menu tidak ditemukan!";
    $_SESSION['notif_type'] = "error";
  }
} else {
  $_SESSION['notif'] = "⚠️ ID menu tidak valid!";
  $_SESSION['notif_type'] = "error";
}

// Redirect kembali ke dashboard
header("Location: dashboard.php");
exit;
?>