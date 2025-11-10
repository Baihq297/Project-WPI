<?php
session_start();

if (!isset($_SESSION) || !is_array($_SESSION)) {
    $_SESSION = [];
}

$jumlahKeranjang = 0;
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        // Hanya hitung jika $item adalah array dan memiliki 'qty'
        if (is_array($item) && isset($item['qty'])) {
            $jumlahKeranjang += (int)$item['qty'];
        }
    }
}

echo $jumlahKeranjang;
?>