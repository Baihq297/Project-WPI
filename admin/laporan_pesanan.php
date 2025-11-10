<?php
session_start();
include "../api/koneksi.php";

if (!isset($_SESSION['pelanggan']) || $_SESSION['pelanggan']['role'] !== 'admin') {
  header("Location: ../index.php?login_required=1");
  exit;
}

$notif_msg = $_SESSION['notif'] ?? null;
$notif_type = $_SESSION['notif_type'] ?? 'success';
unset($_SESSION['notif'], $_SESSION['notif_type']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Pesanan - Goreng Chicken Co.</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    /* (CSS Anda sudah benar) */
    :root {
      --hitam: #1e1e1e;
      --kuning: #ffc107;
      --merah: #b22222;
      --putih: #fffdfa;
      --abu: #f8f9fa;
    }
    body { background-color: var(--abu); font-family: 'Poppins', sans-serif; color: #333; }
    .navbar { background-color: var(--hitam); box-shadow: 0 4px 12px rgba(0,0,0,0.25); }
    .navbar-brand { color: var(--kuning) !important; font-weight: 700; font-size: 1.1rem; }
    .navbar .btn-outline-light:hover { background-color: var(--kuning); color: var(--hitam); border: none; }
    .card { background-color: var(--putih); border: none; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
    .card h4 { color: var(--merah); font-weight: 700; }
    .form-control, .form-select { border-radius: 10px; }
    .form-control:focus, .form-select:focus { border-color: var(--kuning); box-shadow: 0 0 5px var(--kuning); }
    .btn-danger { background-color: var(--merah); border: none; }
    .btn-danger:hover { background-color: #941b1b; }
    .btn-success { background-color: #28a745; border: none; }
    .btn-success:hover { background-color: #218838; }
    .btn-secondary { background-color: var(--kuning); border: none; color: var(--hitam); }
    .btn-secondary:hover { background-color: #ffce33; }
    .table { background-color: var(--putih); border-radius: 12px; overflow: hidden; }
    thead { background-color: var(--merah); color: #fff; }
    tbody tr:hover { background-color: #fff6f6; }
    .badge { font-size: 0.85rem; padding: 6px 10px; border-radius: 8px; }
    #toast-container { position: fixed; top: 90px; right: 20px; z-index: 2000; }
    @media (max-width: 768px) {
      .table th, .table td { font-size: 12px; }
    }

    /* === PERBAIKAN 1: CSS untuk Modal Detail === */
    #modal_order_details {
        max-height: 200px; /* Batasi tinggi, buat bisa scroll jika banyak */
        overflow-y: auto;
    }
    #modal_order_details .list-group-item {
        padding-left: 0;
        padding-right: 0;
        border-bottom: 1px dashed #eee;
    }
    #modal_order_details .list-group-item:last-child {
        border-bottom: none;
    }
    /* === AKHIR PERBAIKAN 1 === */
  </style>
</head>
<body>

<nav class="navbar navbar-dark py-3">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand"><i class="bi bi-receipt-cutoff"></i> Laporan Pesanan</a>
    <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="bi bi-speedometer2"></i> Dashboard</a>
  </div>
</nav>

<div class="container my-4">
  <div class="card p-4">
    <h4 class="mb-3"><i class="bi bi-journal-text"></i> Daftar Pesanan Masuk</h4>
    
    <div class="mb-3">
      <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
        <input type="date" name="tgl_mulai" class="form-control" value="<?= $_GET['tgl_mulai'] ?? '' ?>" style="max-width: 200px;">
        <input type="date" name="tgl_selesai" class="form-control" value="<?= $_GET['tgl_selesai'] ?? '' ?>" style="max-width: 200px;">
        <button class="btn btn-danger" name="filter"><i class="bi bi-funnel"></i> Filter</button>
        <a href="laporan_pesanan.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Reset</a>
      </form>
    </div>
    <div class="d-flex justify-content-end mb-3">
      <a href="export_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger me-2"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
      <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
    </div>

    <div class="table-responsive">
      <table class="table align-middle text-center">
        <thead>
          <tr>
            <th>ID Pesanan</th>
            <th>Nama Pelanggan</th>
            <th>Total (Rp)</th>
            <th>Tanggal</th>
            <th>Metode Bayar</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $where = "";
          if (isset($_GET['filter']) && !empty($_GET['tgl_mulai']) && !empty($_GET['tgl_selesai'])) {
            $tgl1 = mysqli_real_escape_string($conn, $_GET['tgl_mulai']);
            $tgl2 = mysqli_real_escape_string($conn, $_GET['tgl_selesai']);
            $where = "WHERE DATE(p.created_at) BETWEEN '$tgl1' AND '$tgl2'";
          }
          $query = "
            SELECT 
                p.id_pesanan, p.total, p.status, p.created_at, p.payment_method,
                pel.nama AS nama_pelanggan
            FROM pesanan p
            JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
            $where
            ORDER BY p.id_pesanan DESC
          ";
          $res = mysqli_query($conn, $query);
          
          if (!$res) {
              echo "<tr><td colspan='7' class='text-danger'>Error: " . mysqli_error($conn) . "</td></tr>";
          } elseif (mysqli_num_rows($res) === 0) {
              echo "<tr><td colspan='7' class='text-muted'>Tidak ada data pesanan ditemukan.</td></tr>";
          } else {
              while ($r = mysqli_fetch_assoc($res)) {
                  $status_text = $r['status'] ?? 'Baru';
                  $badge_class = 'bg-secondary';
                  switch (strtolower($status_text)) {
                      case 'selesai': $badge_class = 'bg-success'; break;
                      case 'diproses': $badge_class = 'bg-warning text-dark'; break;
                      case 'dikirim': $badge_class = 'bg-info text-dark'; break;
                  }
                  echo "
                    <tr>
                      <td class='fw-bold text-danger'>#{$r['id_pesanan']}</td>
                      <td>".htmlspecialchars($r['nama_pelanggan'])."</td>
                      <td>Rp ".number_format($r['total'],0,',','.')."</td>
                      <td>".date('d/m/Y H:i', strtotime($r['created_at'] ?? 'now'))."</td>
                      <td>".htmlspecialchars($r['payment_method'] ?? '-')."</td>
                      <td><span class='badge $badge_class'>".htmlspecialchars($status_text)."</span></td>
                      <td>
                        <button type='button' class='btn btn-sm btn-primary btn-edit-status' 
                                data-bs-toggle='modal' 
                                data-bs-target='#statusModal'
                                data-order-id='{$r['id_pesanan']}'
                                data-current-status='".htmlspecialchars($status_text)."'>
                          <i class='bi bi-pencil-square'></i>
                        </button>
                        <a href='hapus_pesanan.php?id={$r['id_pesanan']}' class='btn btn-sm btn-danger btn-delete-pesanan'>
                          <i class='bi bi-trash'></i>
                        </a>
                      </td>
                    </tr>
                  ";
              }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="statusModalTitle">Ubah Status Pesanan #</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        
        <h6 class="fw-bold text-danger">Detail Item:</h6>
        <div id="modal_order_details" class="mb-3">
            <p class="text-muted text-center"><i class="bi bi-arrow-clockwise"></i> Memuat detail...</p>
        </div>
        <hr>
        <form id="statusForm">
          <input type="hidden" name="id_pesanan" id="modal_id_pesanan">
          <div class="mb-3">
            <label class="form-label fw-bold">Ubah Status Pesanan</label>
            <select name="status" id="modal_status" class="form-select">
              <option value="Baru">Baru</option>
              <option value="Diproses">Diproses</option>
              <option value="Dikirim">Dikirim</option>
              <option value="Selesai">Selesai</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-danger" id="saveStatusButton">Simpan Perubahan</button>
      </div>
    </div>
  </div>
</div>
<div id="toast-container"></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script>
// (Fungsi showToast tidak berubah)
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
  // (Notifikasi Session dan Konfirmasi Hapus tidak berubah)
  <?php if ($notif_msg): ?>
    showToast(<?= json_encode($notif_msg) ?>, <?= $notif_type === 'error' ? 'true' : 'false' ?>);
  <?php endif; ?>
  $('.btn-delete-pesanan').on('click', function(e) {
      e.preventDefault(); 
      const href = $(this).attr('href'); 
      Swal.fire({
          title: 'Anda Yakin?',
          text: "Pesanan yang dihapus tidak dapat dikembalikan!",
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

  // 
  // ============================================
  //  PERBAIKAN 3: JavaScript untuk Modal (Diperbarui)
  // ============================================
  //
  const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
  const modalDetailsBody = $('#modal_order_details'); // Cache selector
  
  // 1. Saat tombol edit diklik
  $('.btn-edit-status').on('click', function() {
      const orderId = $(this).data('order-id');
      const currentStatus = $(this).data('current-status');
      
      // Isi form di dalam modal (kode lama)
      $('#statusModalTitle').text('Ubah Status Pesanan #' + orderId);
      $('#modal_id_pesanan').val(orderId);
      $('#modal_status').val(currentStatus);

      // --- INI BAGIAN BARU ---
      // 1. Set loading state
      modalDetailsBody.html('<p class="text-muted text-center"><i class="bi bi-arrow-clockwise"></i> Memuat detail...</p>');

      // 2. Ambil data detail via AJAX
      $.ajax({
          url: '../api/get_order_details.php', // Panggil API baru
          method: 'GET',
          data: { id_pesanan: orderId },
          dataType: 'json',
          success: function(res) {
              if (res.success && res.items.length > 0) {
                  let html = '<ul class="list-group list-group-flush">';
                  res.items.forEach(item => {
                      const totalHarga = (item.subtotal).toLocaleString('id-ID');
                      const hargaSatuan = (item.harga).toLocaleString('id-ID');
                      
                      html += `<li class="list-group-item d-flex justify-content-between align-items-center px-0">
                          <div>
                              <strong>${item.nama_menu}</strong>
                              <small class="d-block text-muted">${item.qty} x Rp ${hargaSatuan}</small>
                          </div>
                          <span class="fw-bold">Rp ${totalHarga}</span>
                      </li>`;
                  });
                  html += '</ul>';
                  modalDetailsBody.html(html); // Masukkan daftar item
              } else if (res.items.length === 0) {
                  modalDetailsBody.html('<p class="text-danger text-center">Tidak ada detail item untuk pesanan ini.</p>');
              } else {
                   modalDetailsBody.html(`<p class="text-danger text-center">Gagal memuat detail: ${res.message}</p>`);
              }
          },
          error: function() {
              modalDetailsBody.html('<p class="text-danger text-center">Gagal terhubung ke server.</p>');
          }
      });
      // --- AKHIR BAGIAN BARU ---
  });
  
  // 2. Saat tombol "Simpan Perubahan" di modal diklik (AJAX)
  $('#saveStatusButton').on('click', function() {
      const form = $('#statusForm');
      const button = $(this);
      
      button.prop('disabled', true).text('Menyimpan...'); 
      
      $.ajax({
          url: '../api/update_order_status.php',
          method: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function(res) {
              if (res.success) {
                  statusModal.hide();
                  showToast(res.message, false);
                  setTimeout(() => {
                      window.location.reload();
                  }, 1500);
              } else {
                  Swal.fire('Oops!', res.message || 'Gagal menyimpan.', 'error');
              }
          },
          error: function() {
              Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
          },
          complete: function() {
              button.prop('disabled', false).text('Simpan Perubahan');
          }
      });
  });
});
</script>

</body>
</html>