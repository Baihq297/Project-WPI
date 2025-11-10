<?php
// pages/profile.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['pelanggan'])) {
    header("Location: ../index.php?page=menu");
    exit;
}

// 1. Variabel untuk Navbar
$BASE_URL = ".."; 
$currentPage = 'static'; 

require_once "../api/koneksi.php";

// 2. Panggil Navbar
include "../layout/navbar.php"; 

$id = intval($_SESSION['pelanggan']['id']);
$stmt = mysqli_prepare($conn, "SELECT id_pelanggan, nama, email, alamat, no_hp, foto, latitude, longitude FROM pelanggan WHERE id_pelanggan = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res) ?: [];

$foto = !empty($user['foto']) && file_exists("../assets/uploads/".$user['foto']) ? "../assets/uploads/".htmlspecialchars($user['foto']) : "../assets/img/placeholder.jpg";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil Saya</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">

<style>
/* (Perubahan di CSS ini) */
body { 
    padding-top: 80px;
    background-color: #f8f9fa;
} 
.profile-card { border-radius:12px; background:#fffdfa; box-shadow:0 6px 20px rgba(0,0,0,0.06); }
.header { background:#ffc107; padding:16px; border-radius:12px 12px 0 0; color:#111; font-weight:700; }
.btn-primary { background:#b22222; border:none; color:#fff; }
.form-label { font-weight:600; }
#map { height: 320px; border-radius:8px; border:1px solid #eee; }

/* === PERBAIKAN UKURAN AVATAR === */
.avatar { 
    width:150px; /* Diperbesar dari 120px */
    height:150px; /* Diperbesar dari 120px */
    object-fit:cover; 
    border-radius: 50%;
    border:4px solid #fff; 
    box-shadow:0 6px 18px rgba(0,0,0,0.08); 
}
.avatar-container {
    position: relative;
    width: 150px; /* Disesuaikan */
    height: 150px; /* Disesuaikan */
    margin: 0 auto 1rem; 
    border-radius: 50%;
}
.avatar-overlay-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    background-color: #b22222;
    color: white;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: background-color 0.2s ease;
}
.avatar-overlay-btn:hover { background-color: #8c1a1a; }
/* === AKHIR PERBAIKAN UKURAN === */

.small-note { font-size:0.9rem; color:#666; }
#imageToCrop { display: block; max-width: 100%; }
#fotoInput { display: none; }
.cropper-view-box,
.cropper-face {
    border-radius: 50%;
}
.cropper-line, .cropper-point {
    display: none !important;
}
</style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="profile-card overflow-hidden">
                <div class="header d-flex justify-content-between align-items-center">
                    <div><i class="bi bi-person-fill"></i> Profil Saya</div>
                    </div>
                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <div class="avatar-container">
                                <img id="avatarPreview" src="<?= $foto ?>" alt="avatar" class="avatar">
                                <div class="avatar-overlay-btn" id="uploadPhotoButton">
                                    <i class="bi bi-camera-fill"></i>
                                </div>
                            </div>
                            <form id="fotoForm" enctype="multipart/form-data">
                                <input type="file" name="foto" id="fotoInput" accept="image/*">
                            </form>
                            <div class="small-note mt-2 fw-semibold">Maks 2MB. JPG/PNG.</div> 
                        </div>

                        <div class="col-md-8">
                            <form id="profileForm">
                                <input type="hidden" name="id_pelanggan" value="<?= htmlspecialchars($user['id_pelanggan']) ?>">
                                <div class="mb-2">
                                    <label class="form-label">Nama</label>
                                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-control" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email (tidak berubah)</label>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">No HP</label>
                                    <input type="text" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($user['alamat']) ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tandai Lokasi (klik peta)</label>
                                    <div id="map"></div>
                                    <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($user['latitude']) ?>">
                                    <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($user['longitude']) ?>">
                                    <div class="small-note mt-1">Klik peta untuk mengatur lokasi rumahmu.</div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button id="saveProfile" class="btn btn-primary">Simpan Profil</button>
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                        Ganti Password
                                    </button>
                                    
                                    <a href="../index.php?page=menu" class="btn btn-outline-secondary ms-auto">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div> </div> </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cropModalLabel">Potong Gambar Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="img-container">
          <img id="imageToCrop" src="">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="cropButton">Potong & Simpan</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Ganti Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="passwordForm">
          <div class="mb-3">
            <label class="form-label">Password Lama</label>
            <input type="password" name="password_lama" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password Baru</label>
            <input type="password" name="password_baru" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="konfirmasi_password" class="form-control" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="savePasswordButton">Ubah Password</button>
      </div>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
$(function(){
  
  if (typeof showToast === 'undefined') {
      window.showToast = function(icon, message) {
          Swal.fire({
              toast: true,
              position: 'top-end',
              icon: icon,
              title: message,
              showConfirmButton: false,
              timer: 3000
          });
      }
  }

  // --- LOGIKA CROPPER.JS ---
  const cropModalEl = document.getElementById('cropModal');
  const modalImage = document.getElementById('imageToCrop');
  const avatarPreview = $('#avatarPreview');
  const fotoInput = $('#fotoInput');
  const uploadPhotoButton = $('#uploadPhotoButton');
  let cropper;
  const cropModal = new bootstrap.Modal(cropModalEl, {
    backdrop: 'static',
    keyboard: false
  });

  uploadPhotoButton.on('click', function() {
      fotoInput.trigger('click');
  });

  fotoInput.on('change', function(e){
    const file = this.files[0];
    if (!file) return;
    
    if (file.size > 2*1024*1024) { 
      Swal.fire('Oops!', 'File terlalu besar (maksimal 2MB)', 'error');
      $(this).val(''); return; 
    }
    if (!file.type.startsWith('image/')) {
      Swal.fire('Oops!', 'Hanya file gambar (JPG/PNG) yang diizinkan', 'error');
      $(this).val(''); return;
    }

    const reader = new FileReader();
    reader.onload = function (event) {
      modalImage.src = event.target.result;
      cropModal.show();
    };
    reader.readAsDataURL(file);
  });

  cropModalEl.addEventListener('shown.bs.modal', function () {
    if (cropper) cropper.destroy();
    cropper = new Cropper(modalImage, {
      aspectRatio: 1 / 1, 
      viewMode: 1,       
      background: false,
      autoCropArea: 0.8,
    });
  });

  cropModalEl.addEventListener('hidden.bs.modal', function () {
    if (cropper) cropper.destroy();
    modalImage.src = '';
    fotoInput.val('');
  });

  $('#cropButton').on('click', function() {
    if (!cropper) return;
    $(this).prop('disabled', true).text('Menyimpan...');

    cropper.getCroppedCanvas({
        width: 300, 
        height: 300,
    }).toBlob((blob) => {
        const fd = new FormData();
        fd.append('id_pelanggan', <?= json_encode(intval($user['id_pelanggan'])) ?>);
        fd.append('foto', blob, 'profile_cropped.png');
        
        $.ajax({
          url: '../api/update_profile.php?only_foto=1',
          method: 'POST',
          data: fd,
          processData: false, contentType: false,
          dataType: 'json'
        }).done(res => {
          if (res.success) {
            avatarPreview.attr('src', URL.createObjectURL(blob));
            cropModal.hide();
            showToast('success', 'Foto profil berhasil diperbarui!');
          } else {
            Swal.fire('Gagal', res.message || 'Gagal upload foto', 'error');
          }
        }).fail(() => {
          Swal.fire('Error', 'Gagal menghubungi server', 'error');
        }).always(() => {
          $(this).prop('disabled', false).text('Potong & Simpan');
        });
    }, 'image/png');
  });
  // --- BATAS LOGIKA CROPPER.JS ---


  // --- LOGIKA PETA ---
  function updateAddressFromMap(lat, lng) {
    $('#latitude').val(lat.toFixed(8));
    $('#longitude').val(lng.toFixed(8));
    const alamatTextarea = $('textarea[name="alamat"]');
    alamatTextarea.val('Mencari alamat...');
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;
    $.getJSON(url, function(data) {
        if (data && data.display_name) {
            alamatTextarea.val(data.display_name);
        } else {
            alamatTextarea.val('Alamat tidak ditemukan.');
        }
    }).fail(function() {
        alamatTextarea.val('Gagal mengambil alamat.');
    });
  }
  const defaultLat = parseFloat($('#latitude').val()) || -7.250445;
  const defaultLng = parseFloat($('#longitude').val()) || 112.768845;
  const map = L.map('map').setView([defaultLat, defaultLng], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19 }).addTo(map);
  let marker = L.marker([defaultLat, defaultLng], {draggable:true}).addTo(map);
  marker.on('dragend', function(e){ updateAddressFromMap(e.latLng.lat, e.latLng.lng); });
  map.on('click', function(e){ marker.setLatLng(e.latlng); updateAddressFromMap(e.latlng.lat, e.latlng.lng); });
  // --- BATAS LOGIKA PETA ---


  // --- LOGIKA SUBMIT FORM PROFIL ---
  $('#profileForm').on('submit', function(e){
    e.preventDefault();
    const data = $(this).serialize();
    const saveButton = $('#saveProfile');
    saveButton.prop('disabled', true).text('Menyimpan...');
    
    $.post('../api/update_profile.php', data, function(res){
      saveButton.prop('disabled', false).text('Simpan Profil');
      
      if (res.success) {
        showToast('success', 'Profil berhasil disimpan!');
        $('.dropdown-toggle').html('<i class="bi bi-person-circle"></i> ' + $('input[name="nama"]').val());
      } else {
        Swal.fire('Gagal', res.message || 'Gagal menyimpan', 'error');
      }
    }, 'json').fail(function(){ 
        saveButton.prop('disabled', false).text('Simpan Profil'); 
        Swal.fire('Error', 'Gagal menghubungi server', 'error');
    });
  });
  // --- BATAS LOGIKA SUBMIT PROFIL ---

  
  // --- LOGIKA SUBMIT PASSWORD BARU ---
  const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
  
  $('#savePasswordButton').on('click', async function() {
      const saveButton = $(this);
      const form = document.getElementById('passwordForm');
      const formData = new FormData(form);
      
      saveButton.prop('disabled', true).text('Menyimpan...');
      
      try {
          const res = await fetch('../api/update_password.php', {
              method: 'POST',
              body: formData
          });
          const data = await res.json();
          
          showToast(data.success ? 'success' : 'error', data.message);
          
          if (data.success) {
              passwordModal.hide();
              form.reset();
          }
      } catch (err) {
          Swal.fire('Error', 'Gagal menghubungi server.', 'error');
      } finally {
          saveButton.prop('disabled', false).text('Ubah Password');
      }
  });
  // --- BATAS LOGIKA SUBMIT PASSWORD ---
});
</script>
</body>
</html>