<?php
session_start();
// Path include disesuaikan agar selalu berfungsi
include __DIR__ . "/../includes/config.php"; 

// === Cek Login dan Role ===
$is_logged_in = isset($_SESSION['pelanggan']['id']);
$is_admin = $is_logged_in && ($_SESSION['pelanggan']['role'] === 'admin');
$id_pelanggan_aktif = $is_logged_in ? $_SESSION['pelanggan']['id'] : 0;
// $nama_pelanggan_aktif tidak diperlukan di backend proses, dihapus agar kode lebih ringkas.

// Ambil ID Post untuk redirect (gunakan REQUEST karena bisa dari GET atau POST)
$id_post_redirect = isset($_REQUEST['id_post']) ? intval($_REQUEST['id_post']) : 0;
$redirect_url = "../index.php?page=blog_detail&id=" . $id_post_redirect . "#comments";


// ===============================================
// 1. PENANGANAN AKSI ADMIN (MODERASI HAPUS) - DENGAN GET
//    (Hanya untuk action=delete)
// ===============================================
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    if (!$is_admin) {
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
// 2. PENANGANAN SUBMIT KOMENTAR/BALASAN/EDIT - DENGAN POST
// ===============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'add');
    
    if (!$is_logged_in) {
        // Harus login untuk mengirim atau mengedit
        die("Akses Ditolak: Anda harus login untuk berinteraksi dengan komentar.");
    }
    
    $id_post = isset($_POST['id_post']) ? intval($_POST['id_post']) : 0;
    $komentar = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';
    
    // Validasi data wajib
    if ($id_post <= 0 || empty($komentar)) {
        die("Error: Kolom komentar wajib diisi dan ID post harus valid.");
    }

    // --- LOGIKA EDIT KOMENTAR ---
    if ($action === 'edit') {
        $id_komentar = isset($_POST['id_komentar']) ? intval($_POST['id_komentar']) : 0;

        if ($id_komentar <= 0) {
            die("Error: ID komentar tidak valid untuk proses edit.");
        }

        // Cek kepemilikan atau hak admin sebelum mengedit
        // Query akan memastikan bahwa komentar hanya diedit jika:
        // 1. User adalah admin (p.role='admin') ATAU
        // 2. User adalah pemilik komentar (c.id_pelanggan = ?)
        $sql = "UPDATE komentar c
                LEFT JOIN pelanggan p ON c.id_pelanggan = p.id_pelanggan
                SET c.isi_komentar = ?, c.tanggal_komentar = NOW()
                WHERE c.id_komentar = ? 
                AND (c.id_pelanggan = ? OR p.role = 'admin')";
        
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Tipe binding: "sii" -> string (isi_komentar), integer (id_komentar), integer (id_pelanggan_aktif)
            mysqli_stmt_bind_param($stmt, "sii", $komentar, $id_komentar, $id_pelanggan_aktif);

            if (mysqli_stmt_execute($stmt)) {
                // Cek apakah ada baris yang terpengaruh (artinya edit berhasil dan user punya hak)
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    header("Location: " . $redirect_url);
                    exit;
                } else {
                    die("Akses Ditolak: Anda tidak memiliki hak untuk mengedit komentar ini.");
                }
            } else {
                die("Error: Gagal memperbarui komentar. " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            die("Error: Gagal menyiapkan query edit. " . mysqli_error($conn));
        }
    }


    // --- LOGIKA TAMBAH KOMENTAR/BALASAN (action=add atau action=reply) ---
    else if ($action === 'add' || $action === 'reply') {
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;
        
        // Atur parent_id menjadi NULL jika nilainya 0
        if ($parent_id === 0) {
            $parent_id = NULL;
        }

        // Pengecekan Hak Akses
        if ($is_admin) {
             // Admin HANYA boleh membalas (parent_id wajib ada)
             if ($parent_id === NULL) {
                 die("Error Admin: Admin hanya dapat mengirim balasan.");
             }
        } else {
            // Pelanggan HANYA boleh komentar utama (parent_id wajib NULL)
            if ($parent_id !== NULL) {
                 die("Error Pelanggan: Pelanggan tidak diizinkan mengirim balasan.");
            }
        }
        
        // Query INSERT komentar atau balasan
        $sql = "INSERT INTO komentar (id_post, id_pelanggan, isi_komentar, parent_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // Tipe binding: "iisi" -> integer (id_post), integer (id_pelanggan), string (isi_komentar), integer (parent_id)
            mysqli_stmt_bind_param($stmt, "iisi", $id_post, $id_pelanggan_aktif, $komentar, $parent_id);

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
}

mysqli_close($conn);
?>