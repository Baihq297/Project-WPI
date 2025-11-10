<?php
// api/update_profile.php
session_start();
require_once "koneksi.php";
header('Content-Type: application/json');

if (!isset($_SESSION['pelanggan'])) {
  echo json_encode(['success'=>false,'message'=>'Not authenticated']);
  exit;
}
$id = intval($_SESSION['pelanggan']['id']);

// Only foto upload (ajax)
if (isset($_GET['only_foto']) && $_GET['only_foto']=='1') {
  if (!isset($_FILES['foto']) || $_FILES['foto']['error']!==UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'message'=>'File tidak dikirim']); exit;
  }
  $f = $_FILES['foto'];
  $allowed = ['image/jpeg','image/png','image/jpg'];
  if (!in_array(mime_content_type($f['tmp_name']), $allowed)) {
    echo json_encode(['success'=>false,'message'=>'Format gambar tidak didukung']); exit;
  }
  if ($f['size'] > 2*1024*1024) { echo json_encode(['success'=>false,'message'=>'File terlalu besar']); exit; }

  $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
  $new = 'avatar_'.$id.'_'.time().'.'.$ext;
  $target = __DIR__ . '/../assets/uploads/'.$new;
  if (!move_uploaded_file($f['tmp_name'], $target)) {
    echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file']); exit;
  }
  
  // update DB
  $stmt = mysqli_prepare($conn, "UPDATE pelanggan SET foto = ? WHERE id_pelanggan = ?");
  mysqli_stmt_bind_param($stmt,"si",$new,$id);
  mysqli_stmt_execute($stmt);
  
  // === PERBAIKAN: Update session juga! ===
  $_SESSION['pelanggan']['foto'] = $new; 
  // ======================================

  echo json_encode(['success'=>true,'message'=>'Foto tersimpan','file'=>$new]); exit;
}

// Normal profile update
$nama = trim($_POST['nama'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$latitude = isset($_POST['latitude']) && $_POST['latitude']!=='' ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) && $_POST['longitude']!=='' ? floatval($_POST['longitude']) : null;

if ($nama === '') {
  echo json_encode(['success'=>false,'message'=>'Nama wajib diisi']); exit;
}

$query = "UPDATE pelanggan SET nama = ?, no_hp = ?, alamat = ?, latitude = ?, longitude = ? WHERE id_pelanggan = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sssddi", $nama, $no_hp, $alamat, $latitude, $longitude, $id);
$ok = mysqli_stmt_execute($stmt);

if ($ok) {
  // update session nama
  $_SESSION['pelanggan']['nama'] = $nama;
  echo json_encode(['success'=>true,'message'=>'Profil diperbarui']);
} else {
  echo json_encode(['success'=>false,'message'=>'Gagal menyimpan: '.mysqli_error($conn)]);
}
?>