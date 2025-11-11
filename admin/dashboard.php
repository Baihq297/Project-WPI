<?php
session_start();
include "../api/koneksi.php";

if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
session_regenerate_id(true);

$notif_msg = $_SESSION['notif'] ?? null;
$notif_type = $_SESSION['notif_type'] ?? 'success';
unset($_SESSION['notif'], $_SESSION['notif_type']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Dashboard Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
:root {
    --merah: #b22222;
    --kuning: #ffc107;
    --putih: #fffdfa;
    --gelap: #222;
    --abu: #f8f9fa;
    --hijau: #28a745;
}
body { background-color: var(--abu); color: var(--gelap); font-family: 'Poppins', sans-serif; }
.navbar { background-color: var(--gelap); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
.navbar-brand { color: var(--kuning) !important; font-weight: 700; font-size: 1.3rem; }
.navbar a.btn-outline-light:hover { background: var(--kuning); color: var(--gelap) !important; border-color: var(--kuning); }
.card { border-radius: 16px; border: none; transition: 0.3s; background: var(--putih); }
.card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
.card i { font-size: 2.3rem; color: var(--merah); }
.btn-warning { background: var(--kuning); border: none; color: var(--gelap); }
.btn-success { background: var(--hijau); border: none; }
.btn-success:hover { background: #23913c; }
.table { background-color: var(--putih); color: var(--gelap); border-radius: 12px; overflow: hidden; }
thead { background: var(--merah); color: white; }
tbody tr:hover { background-color: #fff3f3; transition: 0.2s; }
.chart-card { background: var(--putih); border-radius: 16px; padding: 20px; box-shadow: 0 6px 16px rgba(0,0,0,0.05); }
.form-check-input { width: 2.8em; height: 1.4em; background-color: #ccc; border-color: #ccc; }
.form-check-input:checked { background-color: var(--hijau); border-color: var(--hijau); }
#toast-container { position: fixed; top: 90px; right: 20px; z-index: 2000; }
.image-preview { width: 120px; height: 120px; object-fit: cover; border: 3px solid #eee; display: none; }
.file-input-modal { display: none; }
.file-upload-label { cursor: pointer; }
</style>
</head>
<body>

<nav class="navbar navbar-dark shadow-sm py-3">
<div class="container d-flex justify-content-between align-items-center">
<a class="navbar-brand"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
<div class="d-flex align-items-center">
<a href="http://localhost/GorengChicken/index.php?page=menu" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-house"></i> Menu</a>
<a href="http://localhost/GorengChicken/index.php?page=blog" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-journal-text"></i> Blog</a>
<a href="admin_profil.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-person-circle"></i> Profil</a>
<span class="text-light me-3">Halo, <strong class="text-warning"><?= htmlspecialchars($_SESSION['pelanggan']['nama']) ?></strong></span>
<a href="../auth/logout.php" class="btn btn-light btn-sm text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>
</div>
</nav>

<div class="container my-4">

<div class="row mb-4">
<?php
$totalMenu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM menu"))['jml'];
$totalPesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM pesanan"))['jml'];
$totalPelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM pelanggan"))['jml'];
?>
<div class="col-md-4"><div class="card text-center"><div class="card-body"><i class="bi bi-journal-text"></i><h6 class="mt-2 mb-1 text-secondary">Total Menu</h6><h3><?= $totalMenu ?></h3></div></div></div>
<div class="col-md-4"><div class="card text-center"><div class="card-body"><i class="bi bi-cart-check"></i><h6 class="mt-2 mb-1 text-secondary">Total Pesanan</h6><h3><?= $totalPesanan ?></h3></div></div></div>
<div class="col-md-4"><div class="card text-center"><div class="card-body"><i class="bi bi-people"></i><h6 class="mt-2 mb-1 text-secondary">Total Pelanggan</h6><h3><?= $totalPelanggan ?></h3></div></div></div>
</div>

<div class="chart-card mb-4">
<h5 class="fw-bold text-danger mb-3"><i class="bi bi-bar-chart-line"></i> Grafik Penjualan Mingguan</h5>
<div id="chartPenjualan" style="height: 400px;"></div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="text-danger fw-bold"><i class="bi bi-list-ul"></i> Kelola Menu</h4>
<div>
<a href="laporan_pesanan.php" class="btn btn-success me-2 shadow-sm"><i class="bi bi-clipboard-data"></i> Laporan</a>
<button type="button" class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#tambahMenuModal"><i class="bi bi-plus-circle"></i> Tambah Menu</button>
</div>
</div>

<h5 class="text-secondary fw-bold mb-3"><i class="bi bi-egg-fried"></i> Daftar Makanan</h5>
<div class="table-responsive shadow-sm rounded mb-5">
<table class="table align-middle">
<thead class="text-center">
<tr><th>No</th><th>Gambar</th><th>Nama Menu</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
</thead>
<tbody>
<?php
$res_makanan = mysqli_query($conn, "SELECT * FROM menu WHERE kategori='makanan' ORDER BY nama_menu");
$no = 1;
if (mysqli_num_rows($res_makanan) > 0) {
while ($r = mysqli_fetch_assoc($res_makanan)) {
$isTersedia = (in_array(strtolower($r['status']), ['aktif', 'tersedia']));
$isStokAda = (intval($r['stok']) > 0);
$checked = ($isTersedia && $isStokAda) ? 'checked' : '';
$imgPath = "../assets/img/" . $r['gambar'];
?>
<tr>
<td class="text-center fw-semibold text-danger"><?= $no ?></td>
<td class="text-center">
<?php if (!empty($r['gambar']) && file_exists($imgPath)): ?>
<img src="<?= htmlspecialchars($imgPath, ENT_QUOTES) ?>" width="70" height="70" class="rounded" style="object-fit:cover">
<?php else: ?><span class="text-muted fst-italic">No image</span><?php endif; ?>
</td>
<td><?= htmlspecialchars($r['nama_menu']) ?></td>
<td>Rp <?= number_format($r['harga'],0,',','.') ?></td>
<td class="text-center"><?= htmlspecialchars($r['stok']) ?></td>
<td class="text-center"><div class="form-check form-switch d-flex justify-content-center"><input type="checkbox" class="form-check-input toggle-status" data-id="<?= $r['id_menu'] ?>" <?= $checked ?>></div></td>
<td class="text-center">
<button type="button" class="btn btn-sm btn-primary btn-edit-menu" data-bs-toggle="modal" data-bs-target="#editMenuModal" data-id="<?= $r['id_menu'] ?>"><i class="bi bi-pencil"></i></button>
<a href="hapus_menu.php?id=<?= $r['id_menu'] ?>" class="btn btn-sm btn-danger btn-delete-menu"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php $no++; }} else { echo "<tr><td colspan='7' class='text-center text-muted p-4'>Belum ada data makanan.</td></tr>"; } ?>
</tbody>
</table>
</div>

<h5 class="text-secondary fw-bold mb-3"><i class="bi bi-cup-straw"></i> Daftar Minuman</h5>
<div class="table-responsive shadow-sm rounded">
<table class="table align-middle">
<thead class="text-center">
<tr><th>No</th><th>Gambar</th><th>Nama Menu</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
</thead>
<tbody>
<?php
$res_minuman = mysqli_query($conn, "SELECT * FROM menu WHERE kategori='minuman' ORDER BY nama_menu");
$no = 1;
if (mysqli_num_rows($res_minuman) > 0) {
while ($r = mysqli_fetch_assoc($res_minuman)) {
$isTersedia = (in_array(strtolower($r['status']), ['aktif', 'tersedia']));
$isStokAda = (intval($r['stok']) > 0);
$checked = ($isTersedia && $isStokAda) ? 'checked' : '';
$imgPath = "../assets/img/" . $r['gambar'];
?>
<tr>
<td class="text-center fw-semibold text-danger"><?= $no ?></td>
<td class="text-center">
<?php if (!empty($r['gambar']) && file_exists($imgPath)): ?>
<img src="<?= htmlspecialchars($imgPath, ENT_QUOTES) ?>" width="70" height="70" class="rounded" style="object-fit:cover">
<?php else: ?><span class="text-muted fst-italic">No image</span><?php endif; ?>
</td>
<td><?= htmlspecialchars($r['nama_menu']) ?></td>
<td>Rp <?= number_format($r['harga'],0,',','.') ?></td>
<td class="text-center"><?= htmlspecialchars($r['stok']) ?></td>
<td class="text-center"><div class="form-check form-switch d-flex justify-content-center"><input type="checkbox" class="form-check-input toggle-status" data-id="<?= $r['id_menu'] ?>" <?= $checked ?>></div></td>
<td class="text-center">
<button type="button" class="btn btn-sm btn-primary btn-edit-menu" data-bs-toggle="modal" data-bs-target="#editMenuModal" data-id="<?= $r['id_menu'] ?>"><i class="bi bi-pencil"></i></button>
<a href="hapus_menu.php?id=<?= $r['id_menu'] ?>" class="btn btn-sm btn-danger btn-delete-menu"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php $no++; }} else { echo "<tr><td colspan='7' class='text-center text-muted p-4'>Belum ada data minuman.</td></tr>"; } ?>
</tbody>
</table>
</div>
</div>

<div id="toast-container"></div>

<!-- Modal Tambah Menu -->
<div class="modal fade" id="tambahMenuModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content" style="border-radius:16px;">
<div class="modal-header"><h5 class="modal-title fw-bold text-danger"><i class="bi bi-plus-circle"></i> Tambah Menu Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<form id="addMenuForm" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_menu" value="0">
<div class="row mb-3">
<div class="col-md-6"><label class="form-label">Nama Menu</label><input type="text" name="nama_menu" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" class="form-control" required></div>
</div>
<div class="row mb-3">
<div class="col-md-6"><label class="form-label">Kategori</label><select name="kategori" class="form-select"><option value="makanan">Makanan</option><option value="minuman">Minuman</option></select></div>
<div class="col-md-6"><label class="form-label">Stok Awal</label><input type="number" name="stok" class="form-control" min="0" required></div>
</div>
<div class="row mb-3">
<div class="col-md-6"><label class="form-label">Status Menu</label><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" id="statusSwitchModal" value="tersedia" checked><label class="form-check-label" id="statusLabelModal">Tersedia</label></div></div>
<div class="col-md-6"><label class="form-label">Gambar Menu</label><div class="d-flex align-items-start gap-3"><img id="imagePreview" class="image-preview rounded shadow-sm"><div><input type="file" name="gambar" id="fileInputModal" class="file-input-modal" accept="image/png, image/jpeg"><label for="fileInputModal" class="btn btn-outline-danger file-upload-label"><i class="bi bi-upload"></i> Pilih Gambar</label><div class="form-text mt-1" id="fileName">No file chosen</div></div></div></div>
</div>
</form>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" form="addMenuForm" class="btn btn-danger" id="saveMenuButton"><i class="bi bi-save"></i> Simpan Menu</button></div>
</div>
</div>
</div>

<!-- Modal Edit Menu -->
<div class="modal fade" id="editMenuModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content" style="border-radius:16px;">
<div class="modal-header"><h5 class="modal-title fw-bold text-danger" id="editMenuModalLabel"><i class="bi bi-pencil-square"></i> Edit Menu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<form id="editMenuForm" method="post" enctype="multipart/form-data">
<input type="hidden" name="id_menu" id="edit_id_menu">
<div class="row mb-3">
<div class="col-md-6"><label class="form-label">Nama Menu</label><input type="text" name="nama_menu" id="edit_nama_menu" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" id="edit_harga" class="form-control" required></div>
</div>
<div class="row mb-3">
<div class="col-md-6"><label class="form-label">Kategori</label><select name="kategori" id="edit_kategori" class="form-select"><option value="makanan">Makanan</option><option value="minuman">Minuman</option></select></div>
<div class="col-md-6"><label class="form-label">Stok</label><input type="number" name="stok" id="edit_stok" class="form-control" min="0" required></div>
</div>
<div class="row mb-3">
<div class="col-md-6"></div>
<div class="col-md-6"><label class="form-label">Gambar Menu</label><div class="d-flex align-items-start gap-3"><img id="edit_imagePreview" class="image-preview rounded shadow-sm"><div><input type="file" name="gambar" id="edit_fileInput" class="file-input-modal" accept="image/png, image/jpeg"><label for="edit_fileInput" class="btn btn-outline-danger file-upload-label"><i class="bi bi-upload"></i> Ganti Gambar</label><div class="form-text mt-1" id="edit_fileName">Ganti jika perlu...</div></div></div></div>
</div>
</form>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" form="editMenuForm" class="btn btn-danger" id="updateMenuButton"><i class="bi bi-save"></i> Simpan Perubahan</button></div>
</div>
</div>
</div>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
fetch('../api/chart_data.php')
.then(res => res.json())
.then(data => {
Highcharts.chart('chartPenjualan', {
chart: { type: 'column', backgroundColor: '#fffdfa' },
title: { text: '' },
xAxis: { categories: data.tanggal },
yAxis: { min: 0, title: { text: 'Total Penjualan (Rp)' } },
series: [{ name: 'Penjualan', data: data.total, color: '#b22222' }]
});
});

function showToast(message, isError = false) {
const bg = isError ? 'bg-danger' : 'bg-success';
const toast = $(`
<div class="toast align-items-center text-white ${bg} border-0 shadow mb-2" role="alert">
<div class="d-flex">
<div class="toast-body">${message}</div>
<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
</div>
</div>
`);
$('#toast-container').append(toast);
const bsToast = new bootstrap.Toast(toast[0], { delay: 2500 });
bsToast.show();
toast.on('hidden.bs.toast', () => toast.remove());
}

$(document).ready(function() {

<?php if ($notif_msg): ?>
showToast(<?= json_encode($notif_msg) ?>, <?= $notif_type === 'error' ? 'true' : 'false' ?>);
<?php endif; ?>

$('.btn-delete-menu').on('click', function(e) {
e.preventDefault();
const href = $(this).attr('href');
Swal.fire({
title: 'Anda Yakin?',
text: "Menu yang dihapus tidak dapat dikembalikan!",
icon: 'warning',
showCancelButton: true,
confirmButtonColor: '#b22222',
cancelButtonColor: '#6c757d',
confirmButtonText: 'Ya, Hapus Saja!',
cancelButtonText: 'Batal'
}).then((result) => {
if (result.isConfirmed) {
window.location.href = href;
}
});
});

$('.toggle-status').change(function() {
let id = $(this).data('id');
let status = $(this).is(':checked') ? 'aktif' : 'nonaktif';
let currentToggle = $(this);

$.post('../api/update_status.php', { id_menu: id, status: status }, function(res) {
if (res.success) {
showToast('Status diperbarui: ' + res.new_status, false);
} else {
showToast(res.message, true);
currentToggle.prop('checked', res.new_status === 'aktif');
}
}, 'json').fail(function() {
showToast('Kesalahan koneksi ke server!', true);
currentToggle.prop('checked', !currentToggle.is(':checked'));
});
});

const addMenuForm = $('#addMenuForm');
const saveMenuButton = $('#saveMenuButton');
const statusSwitchModal = document.getElementById('statusSwitchModal');
const statusLabelModal = document.getElementById('statusLabelModal');
statusSwitchModal.addEventListener('change', () => statusLabelModal.textContent = statusSwitchModal.checked ? 'Tersedia' : 'Habis');

const fileInputModal = document.getElementById('fileInputModal');
const imagePreview = document.getElementById('imagePreview');
const fileNameDisplay = document.getElementById('fileName');

fileInputModal.addEventListener('change', function(event) {
const file = event.target.files[0];
if (file) {
fileNameDisplay.textContent = file.name;
const reader = new FileReader();
reader.onload = e => { imagePreview.src = e.target.result; imagePreview.style.display = 'block'; };
reader.readAsDataURL(file);
} else { fileNameDisplay.textContent = 'No file chosen'; imagePreview.src = ''; imagePreview.style.display = 'none'; }
});

addMenuForm.on('submit', function(e) {
e.preventDefault();
const formData = new FormData(this);
saveMenuButton.prop('disabled', true).text('Menyimpan...');

$.ajax({
url: '../api/add_menu_api.php',
method: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
success: function(res) {
if (res.success) { $('#tambahMenuModal').modal('hide'); showToast(res.message, false); setTimeout(() => location.reload(), 2000); }
else Swal.fire({ title: 'Oops, Gagal!', html: res.message, icon: 'error', confirmButtonColor: '#b22222' });
},
error: () => Swal.fire('Error', 'Gagal terhubung ke server.', 'error'),
complete: () => saveMenuButton.prop('disabled', false).text('Simpan Menu')
});
});

const editMenuForm = $('#editMenuForm');
const updateMenuButton = $('#updateMenuButton');

$('.btn-edit-menu').on('click', function() {
const id_menu = $(this).data('id');

$.ajax({
url: '../api/get_menu_detail.php',
method: 'GET',
data: { id_menu },
dataType: 'json',
success: function(res) {
if (res.success) {
const data = res.data;
$('#edit_id_menu').val(data.id_menu);
$('#edit_nama_menu').val(data.nama_menu);
$('#edit_harga').val(data.harga);
$('#edit_kategori').val(data.kategori);
$('#edit_stok').val(data.stok);
$('#edit_fileName').text('Ganti jika perlu...');
$('#edit_imagePreview').attr('src', '../assets/img/' + data.gambar).show();
} else { Swal.fire('Gagal', res.message, 'error'); }
}
});
});

const editFileInput = document.getElementById('edit_fileInput');
const editImagePreview = document.getElementById('edit_imagePreview');
const editFileName = document.getElementById('edit_fileName');

editFileInput.addEventListener('change', function(event) {
const file = event.target.files[0];
if (file) {
editFileName.textContent = file.name;
const reader = new FileReader();
reader.onload = e => { editImagePreview.src = e.target.result; editImagePreview.style.display = 'block'; };
reader.readAsDataURL(file);
} else editFileName.textContent = 'Ganti jika perlu...';
});

editMenuForm.on('submit', function(e) {
e.preventDefault();
const formData = new FormData(this);
updateMenuButton.prop('disabled', true).text('Menyimpan...');

$.ajax({
url: '../api/update_menu_api.php',
method: 'POST',
data: formData,
processData: false,
contentType: false,
dataType: 'json',
success: function(res) {
if (res.success) { $('#editMenuModal').modal('hide'); showToast(res.message, false); setTimeout(() => location.reload(), 2000); }
else Swal.fire({ title: 'Oops, Gagal!', html: res.message, icon: 'error', confirmButtonColor: '#b22222' });
},
error: () => Swal.fire('Error', 'Gagal terhubung ke server.', 'error'),
complete: () => updateMenuButton.prop('disabled', false).text('Simpan Perubahan')
});
});

});
</script>

</body>
</html>
