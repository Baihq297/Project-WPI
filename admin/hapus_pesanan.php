<?php
// PASTIKAN TIDAK ADA SPASI, TAB, ATAU BARIS KOSONG SEBELUM BARIS INI.

session_start();
include "../api/koneksi.php";

// Cek login admin
if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
    header("Location: ../index.php?login_required=1");
    exit;
}

// Ambil ID dari parameter GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // --- 1. Hapus dari tabel detail_pesanan terlebih dahulu ---
    $stmt_detail = mysqli_prepare($conn, "DELETE FROM detail_pesanan WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt_detail, "i", $id);
    $detail_deleted = mysqli_stmt_execute($stmt_detail);
    mysqli_stmt_close($stmt_detail);

    if ($detail_deleted) {
        // --- 2. Hapus dari tabel pesanan (induk) ---
        $stmt_pesanan = mysqli_prepare($conn, "DELETE FROM pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_pesanan, "i", $id);
        mysqli_stmt_execute($stmt_pesanan);
        $rows_affected = mysqli_stmt_affected_rows($stmt_pesanan);
        mysqli_stmt_close($stmt_pesanan);

        if ($rows_affected > 0) {
            $_SESSION['notif'] = "✅ Pesanan (ID: $id) dan detail terkait berhasil dihapus!";
            $_SESSION['notif_type'] = "success";
        } else {
            $_SESSION['notif'] = "⚠️ Pesanan sudah tidak ada atau gagal dihapus.";
            $_SESSION['notif_type'] = "error";
        }
    } else {
        $_SESSION['notif'] = "❌ Gagal menghapus detail pesanan terkait! " . mysqli_error($conn);
        $_SESSION['notif_type'] = "error";
    }
} else {
    $_SESSION['notif'] = "⚠️ ID pesanan tidak valid!";
    $_SESSION['notif_type'] = "error";
}

// Redirect kembali ke halaman laporan
header("Location: laporan_pesanan.php");
exit;
?>
