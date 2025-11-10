<?php
session_start();

// Hapus semua data sesi
session_unset();
session_destroy();

// Arahkan kembali ke halaman utama (index.php di root)
header("Location: ../index.php");
exit;
?>
