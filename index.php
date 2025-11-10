<?php
session_start(); // harus paling atas

// 💡 PERBAIKAN: Logika halaman dipindahkan ke ATAS
// Ambil parameter halaman dari URL, default ke 'home'
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sanitasi simple: hanya izinkan huruf, angka, underscore dan dash
if (!preg_match('/^[a-z0-9_\-]+$/i', $page)) {
    $page = 'home';
}
// 💡 Variabel $page (misal: 'home', 'about') sekarang sudah siap
// untuk digunakan oleh head.php dan navbar.php

include "layout/head.php";
// 💡 $page sekarang sudah terdefinisi saat navbar.php dipanggil
include "layout/navbar.php";

// Tentukan path file halaman
$pagePath = __DIR__ . "/pages/" . $page . ".php";

// Cek halaman khusus atau file-nya
if ($page === 'blog_detail') {
    // include halaman detail (file harus ada)
    if (file_exists($pagePath)) {
        include $pagePath;
    } else {
        echo "<div class='container py-5 text-center'><h2 class='text-danger'>404 - Halaman Tidak Ditemukan</h2></div>";
    }
}
// Kalau halaman lain ada file-nya, tampilkan
elseif (file_exists($pagePath)) {
    include $pagePath;
}
// Kalau tidak ada file, tampilkan 404
else {
    echo "<div class='container py-5 text-center'>
            <h2 class='text-danger'>404 - Halaman Tidak Ditemukan</h2>
          </div>";
}

include "layout/footer.php";
?>