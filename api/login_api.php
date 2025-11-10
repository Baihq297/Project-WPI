<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once "koneksi.php";

$email = trim($_POST['email'] ?? '');
$pass  = trim($_POST['password'] ?? '');

// 🔹 Validasi input
if ($email === '' || $pass === '') {
    echo json_encode(['success' => false, 'message' => 'Email dan password wajib diisi']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
    exit;
}

// 🔹 Ambil data pelanggan
$stmt = mysqli_prepare($conn, "SELECT * FROM pelanggan WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $role = $row['role'] ?: 'pelanggan';
    $isValid = false;

    // 🔹 Cek password hash
    if (password_verify($pass, $row['password'])) {
        $isValid = true;
    } elseif ($pass === $row['password']) {
        // 🔹 Password lama masih plaintext — hash ulang otomatis
        $newHash = password_hash($pass, PASSWORD_DEFAULT);
        $update = mysqli_prepare($conn, "UPDATE pelanggan SET password = ? WHERE id_pelanggan = ?");
        mysqli_stmt_bind_param($update, "si", $newHash, $row['id_pelanggan']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        $isValid = true;
    }

    // 🔹 Set session login and only proceed if password was validated
    if ($isValid) {
        $_SESSION['pelanggan'] = [
            'id' => $row['id_pelanggan'],
            'nama' => $row['nama'],
            'email' => $row['email'],
            'role' => $role,
            'foto' => $row['foto'] // <-- TAMBAHKAN BARIS INI
        ];

        // === START: Sinkronisasi SESSION cart -> tabel user_cart ===
        $id_pelanggan = intval($row['id_pelanggan']);
        if (!empty($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
            // Normalisasi berbagai format keranjang yang mungkin tersisa di session
            $normalized = [];
            foreach ($_SESSION['keranjang'] as $k => $v) {
                $id_menu = intval($k);
                $qty = 0;
                if (is_array($v) && isset($v['qty'])) $qty = intval($v['qty']);
                elseif (is_numeric($v)) $qty = intval($v);
                // jika keyed by numeric index and value is full product array
                if ($id_menu <= 0 && is_array($v)) {
                    if (isset($v['id_menu'])) $id_menu = intval($v['id_menu']);
                    elseif (isset($v['id'])) $id_menu = intval($v['id']);
                    $qty = isset($v['qty']) ? intval($v['qty']) : $qty;
                }
                if ($id_menu > 0 && $qty > 0) {
                    if (!isset($normalized[$id_menu])) $normalized[$id_menu] = $qty;
                    else $normalized[$id_menu] += $qty;
                }
            }

            // Prepare statements we'll reuse
            $stmtStok = mysqli_prepare($conn, "SELECT stok FROM menu WHERE id_menu = ?");
            $stmtInsert = mysqli_prepare($conn,
                "INSERT INTO user_cart (id_pelanggan, id_menu, qty) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE qty = LEAST(qty + VALUES(qty), ?)"
            );

            foreach ($normalized as $id_menu => $qty) {
                // cek produk ada & ambil stok
                if (!$stmtStok) break;
                mysqli_stmt_bind_param($stmtStok, "i", $id_menu);
                mysqli_stmt_execute($stmtStok);
                mysqli_stmt_bind_result($stmtStok, $stokDb);
                if (!mysqli_stmt_fetch($stmtStok)) {
                    // produk tidak ditemukan -> lewati
                    mysqli_stmt_free_result($stmtStok);
                    continue;
                }
                mysqli_stmt_store_result($stmtStok);
                mysqli_stmt_free_result($stmtStok);

                $stok = intval($stokDb);
                if ($stok <= 0) continue;

                // pastikan qty tidak melebihi stok
                if ($qty > $stok) $qty = $stok;

                if ($stmtInsert) {
                    // bind params: id_pelanggan, id_menu, qty, stok (for LEAST)
                    mysqli_stmt_bind_param($stmtInsert, "iiii", $id_pelanggan, $id_menu, $qty, $stok);
                    mysqli_stmt_execute($stmtInsert);
                    // ignore individual errors (could log if needed)
                }
            }

            if ($stmtStok) mysqli_stmt_close($stmtStok);
            if ($stmtInsert) mysqli_stmt_close($stmtInsert);

            // Setelah sinkronisasi, kosongkan session keranjang agar tidak menggandakan
            unset($_SESSION['keranjang']);
        }
        // === END: Sinkronisasi SESSION -> DB ===

        // 🔹 Base URL diperbaiki ke folder project kamu sekarang
        $baseURL = "http://" . $_SERVER['HTTP_HOST'] . "/GorengChicken/";

        if ($role === 'admin') {
            $redirect = $baseURL . "admin/dashboard.php";
            $message = "Login berhasil sebagai Admin";
        } else {
            $redirect = $baseURL . "index.php?page=menu";
            $message = "Login berhasil sebagai Pelanggan";
        }

        echo json_encode([
            'success' => true,
            'role' => $role,
            'redirect' => $redirect,
            'message' => $message
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
        exit;
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Email tidak ditemukan']);
    exit;
}