<?php
session_start();
// Path include disesuaikan agar selalu berfungsi
include __DIR__ . "/../includes/config.php"; 

// === Cek Login dan Role ===
$is_logged_in = isset($_SESSION['pelanggan']['id']);
$is_admin = $is_logged_in && ($_SESSION['pelanggan']['role'] === 'admin');
$id_pelanggan = $is_logged_in ? $_SESSION['pelanggan']['id'] : 0;
$nama_pelanggan_aktif = $is_logged_in ? $_SESSION['pelanggan']['nama'] : 'Guest';

// Ambil ID Post untuk redirect (gunakan REQUEST karena bisa dari GET atau POST)
$id_post_redirect = isset($_REQUEST['id_post']) ? intval($_REQUEST['id_post']) : 0;
$redirect_url = "../index.php?page=blog_detail&id=" . $id_post_redirect . "#comments";


// ===============================================
// 1. PENANGANAN AKSI ADMIN (MODERASI HAPUS) - DENGAN GET
// ===============================================
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    if (!$is_admin) {
        // Akses ditolak jika bukan admin
        die("Akses Ditolak: Anda tidak memiliki hak akses untuk moderasi.");
    }

    $id_komentar = isset($_GET['id_komentar']) ? intval($_GET['id_komentar']) : 0;

    if ($id_komentar > 0) {
        // Query DELETE akan menghapus komentar dan semua balasannya karena ON DELETE CASCADE
        $sql = "DELETE FROM komentar WHERE id_komentar = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id_komentar);
            if (mysqli_stmt_execute($stmt)) {
                // Redirect kembali ke halaman post setelah berhasil hapus
                header("Location: " . $redirect_url);
                exit;
            } else {
                die("Error: Gagal menghapus komentar. " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            die("Error: Gagal menyiapkan query hapus. " . mysqli_error($conn));
        }
    } else {
        header("Location: " . $redirect_url);
        exit;
    }
}


// ===============================================
// 2. PENANGANAN SUBMIT KOMENTAR/BALASAN - DENGAN POST
// ===============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_logged_in) {
        // Harus login untuk mengirim apapun
        die("Akses Ditolak: Anda harus login untuk mengirim komentar.");
    }
    
    // Ambil data wajib
    $id_post = isset($_POST['id_post']) ? intval($_POST['id_post']) : 0;
    $komentar = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';
    
    // Ambil parent_id. Jika kosong/nol, anggap komentar level 0.
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;
    
    // Validasi data
    if ($id_post <= 0 || empty($komentar)) {
        die("Error: Kolom komentar wajib diisi dan ID post harus valid.");
    }

    // --- LOGIKA UTAMA BALASAN ---
    
    if ($is_admin) {
        // Admin hanya boleh mengirim BALASAN (parent_id wajib ada)
        if ($parent_id === NULL || $parent_id === 0) {
            die("Error Admin: Admin hanya dapat mengirim balasan, bukan komentar utama.");
        }
    } else {
        // Pelanggan hanya boleh mengirim KOMENTAR UTAMA (parent_id wajib NULL/0)
        if ($parent_id !== NULL && $parent_id !== 0) {
            die("Error Pelanggan: Pelanggan tidak diizinkan mengirim balasan.");
        }
    }
    
    // Konversi 0 menjadi NULL untuk kolom parent_id di SQL
    if ($parent_id === 0) {
        $parent_id = NULL;
    }

    // Query INSERT komentar atau balasan
    $sql = "INSERT INTO komentar (id_post, id_pelanggan, isi_komentar, parent_id) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Karena parent_id bisa NULL, kita gunakan tipe binding 'i' (integer) dan NULL. 
        // MySQLi dengan prepared statement akan menangani NULL pada kolom INT dengan benar.
        // Tipe binding: "iisi" -> integer (id_post), integer (id_pelanggan), string (isi_komentar), integer (parent_id)
        mysqli_stmt_bind_param($stmt, "iisi", $id_post, $id_pelanggan, $komentar, $parent_id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: " . $redirect_url);
            exit;
        } else {
            die("Error: Gagal menyimpan komentar/balasan. " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        die("Error: Gagal menyiapkan query simpan. " . mysqli_error($conn));
    }
}

mysqli_close($conn);
?>