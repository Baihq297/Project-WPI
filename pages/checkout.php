<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
session_start();
require "../api/koneksi.php";

// (Blok Normalisasi Keranjang Anda sudah benar, tidak diubah)
if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}
$normalized = [];
foreach ($_SESSION['keranjang'] as $key => $entry) {
    $id = intval($key);
    $qty = 0;
    if (is_array($entry)) {
        if (isset($entry['id_menu'])) $id = intval($entry['id_menu']);
        elseif (isset($entry['id'])) $id = intval($entry['id']);
        if (isset($entry['qty'])) $qty = intval($entry['qty']);
        elseif (isset($entry['jumlah'])) $qty = intval($entry['jumlah']);
        elseif (isset($entry['qty_input'])) $qty = intval($entry['qty_input']);
    } else {
        $qty = intval($entry);
    }
    if ($id > 0 && $qty > 0) {
        if (!isset($normalized[$id])) $normalized[$id] = ['qty' => $qty];
        else $normalized[$id]['qty'] += $qty;
    }
}
$_SESSION['keranjang'] = $normalized;
// (Akhir Blok Normalisasi)

if (empty($_SESSION['pelanggan'])) {
    header("Location: ../index.php");
    exit;
}

if (empty($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    echo "<script>alert('Keranjang kosong atau berisi item tidak valid. Silakan pilih menu terlebih dahulu.');window.location='../index.php?page=menu';</script>";
    exit;
}

// 1) Normalisasi (Sudah benar)
$cart = [];
foreach ($_SESSION['keranjang'] as $k => $v) {
    $id = intval($k);
    $qty = 0;
    if (is_array($v) && isset($v['qty'])) $qty = intval($v['qty']);
    elseif (is_numeric($v)) $qty = intval($v);
    if (is_array($v) && isset($v['id_menu'])) $id = intval($v['id_menu']);
    if (is_array($v) && isset($v['id'])) $id = intval($v['id']);
    if ($id > 0 && $qty > 0) {
        if (isset($cart[$id])) $cart[$id] += $qty;
        else $cart[$id] = $qty;
    }
}

if (empty($cart)) {
    echo "<script>alert('Keranjang kosong atau berisi item tidak valid.');window.location='../index.php?page=menu';</script>";
    exit;
}

// 2) Validasi semua id (Sudah benar)
$ids = array_keys($cart);
if (empty($ids)) {
    echo "<script>alert('Keranjang kosong atau berisi item tidak valid.');window.location='../index.php?page=menu';</script>";
    exit;
}
$idsSafe = array_map('intval', $ids);
$in = implode(',', $idsSafe);

$menuData = [];
$q = mysqli_query($conn, "SELECT id_menu, harga, stok FROM menu WHERE id_menu IN ($in)");
if (!$q) {
    die("Query error: " . mysqli_error($conn));
}
while ($r = mysqli_fetch_assoc($q)) {
    $menuData[ $r['id_menu'] ] = $r; 
}

$found = array_keys($menuData);
$missing = array_diff($idsSafe, $found);
if (!empty($missing)) {
    $msg = "Produk tidak tersedia (ID: " . implode(', ', $missing) . "). Silakan periksa kembali keranjang Anda.";
    echo "<script>alert(" . json_encode($msg) . "); window.location='../pages/keranjang.php';</script>";
    exit;
}

// 4) Periksa stok cukup (Sudah benar)
$insufficient = [];
foreach ($cart as $id => $qty) {
    if ($menuData[$id]['stok'] < $qty) $insufficient[$id] = ['requested'=>$qty,'available'=>$menuData[$id]['stok']];
}
if (!empty($insufficient)) {
    $parts = [];
    foreach ($insufficient as $id => $info) {
        $parts[] = "ID $id (minta {$info['requested']}, tersedia {$info['available']})";
    }
    $msg = "Stok tidak cukup untuk: " . implode('; ', $parts);
    echo "<script>alert(" . json_encode($msg) . "); window.location='../pages/keranjang.php';</script>";
    exit;
}

// 5) Semua valid — lakukan insert ke pesanan + detail_pesanan
mysqli_begin_transaction($conn, MYSQLI_TRANS_START_READ_WRITE);

try {
    $status = 'Menunggu Konfirmasi';
    $stmt = mysqli_prepare($conn, "INSERT INTO pesanan (id_pelanggan, created_at, status, total) VALUES (?, NOW(), ?, 0)");
    if (!$stmt) throw new Exception("Prepare insert pesanan gagal: " . mysqli_error($conn));
    
    if (isset($_SESSION['pelanggan']['id'])) {
        $id_pelanggan = intval($_SESSION['pelanggan']['id']);
    } elseif (isset($_SESSION['pelanggan']['id_pelanggan'])) {
        $id_pelanggan = intval($_SESSION['pelanggan']['id_pelanggan']);
    } else {
        throw new Exception("ID Pelanggan tidak ditemukan di session.");
    }

    mysqli_stmt_bind_param($stmt, "is", $id_pelanggan, $status);
    if (!mysqli_stmt_execute($stmt)) throw new Exception("Execute insert pesanan gagal: " . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);

    $id_pesanan = mysqli_insert_id($conn);
    if (!$id_pesanan) throw new Exception("Gagal mendapatkan id_pesanan");

    $stmtDetail = mysqli_prepare($conn, "INSERT INTO detail_pesanan (id_pesanan, id_menu, qty, harga) VALUES (?, ?, ?, ?)");
    if (!$stmtDetail) throw new Exception("Prepare insert detail gagal: " . mysqli_error($conn));

    // === PERBAIKAN 1: Query UPDATE STOK diubah ===
    // Query ini sekarang mengurangi stok DAN mengubah status jika stok <= 0
    $stmtUpdateStok = mysqli_prepare($conn, 
        "UPDATE menu SET 
            stok = stok - ?, 
            status = CASE 
                WHEN (stok - ?) <= 0 THEN 'habis' 
                ELSE status 
            END
         WHERE id_menu = ?");
    if (!$stmtUpdateStok) throw new Exception("Prepare update stok gagal: " . mysqli_error($conn));
    // ===============================================

    $total = 0.0;
    foreach ($cart as $id => $qty) {
        $harga = $menuData[$id]['harga'];
        $subtotal = $harga * $qty;
        $total += $subtotal;
        
        // 1. Masukkan ke detail pesanan
        mysqli_stmt_bind_param($stmtDetail, "iiid", $id_pesanan, $id, $qty, $harga);
        if (!mysqli_stmt_execute($stmtDetail)) {
            $err = mysqli_stmt_error($stmtDetail);
            throw new Exception("Gagal insert detail untuk id_menu={$id}: " . $err);
        }

        // === PERBAIKAN 2: Bind parameter untuk query stok baru ===
        // Kita perlu mengirim $qty dua kali (satu untuk mengurangi, satu untuk perbandingan CASE)
        mysqli_stmt_bind_param($stmtUpdateStok, "iii", $qty, $qty, $id);
        if (!mysqli_stmt_execute($stmtUpdateStok)) {
            $err = mysqli_stmt_error($stmtUpdateStok);
            throw new Exception("Gagal update stok untuk id_menu={$id}: " . $err);
        }
        // ====================================================
    }
    mysqli_stmt_close($stmtDetail);
    mysqli_stmt_close($stmtUpdateStok); // Tutup statement stok

    $stmtUpd = mysqli_prepare($conn, "UPDATE pesanan SET total = ? WHERE id_pesanan = ?");
    if (!$stmtUpd) throw new Exception("Prepare update total gagal: " . mysqli_error($conn));
    mysqli_stmt_bind_param($stmtUpd, "di", $total, $id_pesanan);
    if (!mysqli_stmt_execute($stmtUpd)) throw new Exception("Execute update total gagal: " . mysqli_stmt_error($stmtUpd));
    mysqli_stmt_close($stmtUpd);

    unset($_SESSION['keranjang']);

    mysqli_commit($conn);

    $_SESSION['order_success_flag'] = true;
    header("Location: ../pages/checkout_success.php?order_id={$id_pesanan}");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Terjadi kesalahan saat checkout: " . $e->getMessage());
}
?>