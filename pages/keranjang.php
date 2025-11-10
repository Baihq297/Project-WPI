<?php
// pages/keranjang.php
session_start();
include "../includes/config.php"; // pastikan path benar dan $conn tersedia

// ==========================
// === DIHAPUS ===
// (Blok logika PHP Anda di sini sudah benar semua, tidak saya ubah)
// ==========================

// validasi & bersihkan keranjang
if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];

$ids = array_keys($_SESSION['keranjang']);
if (!empty($ids)) {
    $idsSafe = array_map('intval', $ids);
    $in = implode(',', $idsSafe);
    $q = mysqli_query($conn, "SELECT id_menu FROM menu WHERE id_menu IN ($in)");
    $found = [];
    while ($row = mysqli_fetch_assoc($q)) $found[] = intval($row['id_menu']);
    foreach ($idsSafe as $cid) {
        if (!in_array($cid, $found, true)) {
            unset($_SESSION['keranjang'][$cid]);
        }
    }
}

// Normalisasi
foreach ($_SESSION['keranjang'] as $k => $v) {
    if (!is_array($v)) {
        $_SESSION['keranjang'][$k] = ['qty' => intval($v)];
    } elseif (!isset($v['qty'])) {
        $_SESSION['keranjang'][$k] = ['qty' => intval($v)];
    }
}

// Handle ?add=
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    if ($id > 0) {
        if (isset($_SESSION['keranjang'][$id])) {
            $_SESSION['keranjang'][$id]['qty'] = intval($_SESSION['keranjang'][$id]['qty']) + 1;
        } else {
            $_SESSION['keranjang'][$id] = ['qty' => 1];
        }
    }
    header("Location: keranjang.php");
    exit;
}

// Hapus item
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    if ($id > 0) {
        unset($_SESSION['keranjang'][$id]);
    }
    header("Location: keranjang.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Keranjang - Goreng Chicken Co.</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h3 class="text-danger mb-4 fw-bold"><i class="bi bi-cart3"></i> Keranjang Belanja Anda</h3>

  <?php if (empty($_SESSION['keranjang'])): ?>
    <div class="alert alert-warning text-center shadow-sm">
      <p class="h5 mb-3">Keranjang Anda masih kosong.</p>
      <a href="../index.php?page=menu" class="btn btn-danger mt-3">Lihat Menu</a>
    </div>

  <?php else: ?>
    <form id="cartForm" method="post" action="checkout.php">

      <div class="row">
        <div class="col-lg-8">

          <?php
          // (Blok PHP untuk loop item Anda sudah benar, tidak saya ubah)
          $makanan_items = [];
          $minuman_items = [];
          $grand = 0;

          $stmt = mysqli_prepare($conn, "SELECT id_menu, nama_menu, harga, gambar, kategori FROM menu WHERE id_menu=?");
          foreach ($_SESSION['keranjang'] as $id => $item):
            $qty = intval($item['qty']);
            if ($qty < 1) $qty = 1;

            if ($stmt) {
              mysqli_stmt_bind_param($stmt, "i", $id);
              mysqli_stmt_execute($stmt);
              $res = mysqli_stmt_get_result($stmt);
              $r = mysqli_fetch_assoc($res);
            } else {
              $q = mysqli_query($conn, "SELECT id_menu, nama_menu, harga, gambar, kategori FROM menu WHERE id_menu=" . intval($id));
              $r = mysqli_fetch_assoc($q);
            }

            if (!$r) continue;

            $total = $r['harga'] * $qty;
            $grand += $total;

            $kategori = strtolower(trim($r['kategori'] ?? ''));
            $entry = [
              'id' => $r['id_menu'],
              'nama' => $r['nama_menu'],
              'harga' => $r['harga'],
              'gambar' => $r['gambar'],
              'qty' => $qty,
              'total' => $total
            ];

            if (strpos($kategori, 'minum') !== false || $kategori === 'minuman' || $kategori === 'drink' || $kategori === 'beverage') {
              $minuman_items[] = $entry;
            } else {
              $makanan_items[] = $entry;
            }
          endforeach;

          if (!empty($makanan_items)):
          ?>
            <h5 class="mb-3">Makanan</h5>
            <?php foreach ($makanan_items as $it): ?>
              <div class="card mb-3 shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                  <div class="d-flex align-items-center gap-3 mb-2 mb-md-0">
                    <input type="checkbox" class="form-check-input item-check" data-id="<?= $it['id'] ?>" data-type="makanan">
                    <img src="../assets/img/<?= htmlspecialchars($it['gambar']) ?>" width="70" height="70" class="rounded">
                    <div>
                      <h6 class="fw-bold mb-0"><?= htmlspecialchars($it['nama']) ?></h6>
                      <p class="text-muted small mb-1">Rp <?= number_format($it['harga'], 0, ',', '.') ?></p>
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm minus" data-id="<?= $it['id'] ?>">−</button>
                    <input type="text" class="form-control form-control-sm text-center fw-bold qty-input" value="<?= $it['qty'] ?>" data-id="<?= $it['id'] ?>" readonly style="width:50px;">
                    <button type="button" class="btn btn-outline-success btn-sm plus" data-id="<?= $it['id'] ?>">+</button>
                    <a href="keranjang.php?remove=<?= $it['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-trash"></i></a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <?php if (!empty($minuman_items)): ?>
            <h5 class="mb-3 mt-4">Minuman</h5>
            <?php foreach ($minuman_items as $it): ?>
              <div class="card mb-3 shadow-sm p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                  <div class="d-flex align-items-center gap-3 mb-2 mb-md-0">
                    <input type="checkbox" class="form-check-input item-check" data-id="<?= $it['id'] ?>" data-type="minuman">
                    <img src="../assets/img/<?= htmlspecialchars($it['gambar']) ?>" width="70" height="70" class="rounded">
                    <div>
                      <h6 class="fw-bold mb-0"><?= htmlspecialchars($it['nama']) ?></h6>
                      <p class="text-muted small mb-1">Rp <?= number_format($it['harga'], 0, ',', '.') ?></p>
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm minus" data-id="<?= $it['id'] ?>">−</button>
                    <input type="text" class="form-control form-control-sm text-center fw-bold qty-input" value="<?= $it['qty'] ?>" data-id="<?= $it['id'] ?>" readonly style="width:50px;">
                    <button type="button" class="btn btn-outline-success btn-sm plus" data-id="<?= $it['id'] ?>">+</button>
                    <a href="keranjang.php?remove=<?= $it['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-trash"></i></a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>

        <div class="col-lg-4">
          <div class="card shadow-sm p-3 sticky-top" style="top:90px">
            <h5 class="fw-bold mb-3 text-dark">Ringkasan Belanja</h5>

            <p class="mb-1">Total Item Makanan Dipilih: <span id="totalItemMakanan">0</span></p>
            <p class="mb-1">Total Item Minuman Dipilih: <span id="totalItemMinuman">0</span></p>

            <hr>

            <p class="mb-1">Total Harga Makanan: <span id="totalHargaMakanan">Rp 0</span></p>
            <p class="mb-1">Total Harga Minuman: <span id="totalHargaMinuman">Rp 0</span></p>

            <h4 class="fw-bold text-danger mb-3">Total Harga: <span id="totalHarga">Rp <?= number_format($grand,0,',','.') ?></span></h4>

            <button type="submit" id="checkoutBtn" class="btn btn-danger w-100 fw-bold" <?= $grand>0 ? '' : 'disabled' ?>>Checkout</button>
            <a href="../index.php?page=menu" class="btn btn-outline-secondary w-100 mt-2">Lanjut Belanja</a>
          </div>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>

<script>
// (Seluruh blok Javascript Anda sudah benar, tidak saya ubah)
document.addEventListener("DOMContentLoaded", () => {
  const checkboxes = document.querySelectorAll(".item-check");
  const totalItemMakanan = document.getElementById("totalItemMakanan");
  const totalItemMinuman = document.getElementById("totalItemMinuman");
  const totalHargaMakanan = document.getElementById("totalHargaMakanan");
  const totalHargaMinuman = document.getElementById("totalHargaMinuman");
  const totalHarga = document.getElementById("totalHarga");
  const checkoutBtn = document.getElementById("checkoutBtn");

  function hitungTotal() {
    let makananQty = 0;
    let minumanQty = 0;
    let makananPrice = 0;
    let minumanPrice = 0;

    checkboxes.forEach(chk => {
      if (chk.checked) {
        const id = chk.dataset.id;
        const type = chk.dataset.type || 'makanan';
        const qtyInput = document.querySelector(`.qty-input[data-id='${id}']`);
        if (!qtyInput) return;
        const qty = parseInt(qtyInput.value) || 0;
        const hargaText = qtyInput.closest('.card').querySelector('p.text-muted').innerText || '';
        const harga = parseInt(hargaText.replace(/[^0-9]/g, '')) || 0;

        if (type === 'minuman') {
          minumanQty += qty;
          minumanPrice += qty * harga;
        } else {
          makananQty += qty;
          makananPrice += qty * harga;
        }
      }
    });

    totalItemMakanan.textContent = makananQty;
    totalItemMinuman.textContent = minumanQty;
    totalHargaMakanan.textContent = "Rp " + makananPrice.toLocaleString('id-ID');
    totalHargaMinuman.textContent = "Rp " + minumanPrice.toLocaleString('id-ID');
    const grand = makananPrice + minumanPrice;
    totalHarga.textContent = "Rp " + grand.toLocaleString('id-ID');
    checkoutBtn.disabled = (makananQty + minumanQty) === 0;
  }

  document.querySelectorAll(".plus, .minus").forEach(btn => {
    btn.addEventListener("click", async () => {
      const idRaw = btn.dataset.id;
      const id = parseInt(idRaw, 10);
      if (!Number.isInteger(id) || id <= 0) {
        alert('ID produk tidak valid. Silakan muat ulang halaman.');
        return;
      }
      const input = document.querySelector(`.qty-input[data-id='${id}']`);
      if (!input) {
        alert('Elemen qty tidak ditemukan. Silakan muat ulang halaman.');
        return;
      }
      let qty = parseInt(input.value) || 0;
      if (btn.classList.contains("plus")) qty++;
      else if (btn.classList.contains("minus") && qty > 1) qty--;

      try {
        const res = await fetch("keranjang_update.php", {
          method: "POST",
          body: new URLSearchParams({ id, qty })
        });
        if (!res.ok) {
          let txt = await res.text();
          try {
            const obj = JSON.parse(txt);
            throw new Error(obj.message || `Server error (${res.status})`);
          } catch (e) {
            throw new Error(`Server error (${res.status})`);
          }
        }
        const data = await res.json();
        if (data.success) {
          input.value = data.qty;
          hitungTotal();
        } else {
          alert(data.message || 'Gagal memperbarui jumlah.');
        }
      } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan saat menghubungi server: ' + err.message);
      }
    });
  });

  checkboxes.forEach(chk => chk.addEventListener("change", hitungTotal));
  hitungTotal();
});
</script>

<style>
.card {
  border-radius: 12px;
}
.qty-input {
  width: 45px;
  border-radius: 6px;
}
button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* === PERBAIKAN CHECKBOX DIMULAI DI SINI === */

/* Targetkan checkbox spesifik di keranjang */
.item-check {
  /* 1. Perbesar ukuran */
  width: 1.5rem;  /* 50% lebih besar dari standar 1rem */
  height: 1.5rem;
  
  /* 2. Buat lebih jelas untuk diklik */
  cursor: pointer;
  
  /* 3. Buat border lebih tebal/gelap saat belum di-check */
  border: 2px solid #adb5bd; /* Warna abu-abu Bootstrap */
  
  /* 4. Posisikan di tengah (vertical-align) */
  margin-top: 0.1em;
  vertical-align: middle;
}

/* 5. Beri warna merah (sesuai tema) saat di-check */
.item-check:checked {
  background-color: #dc3545; /* Warna Bootstrap 'danger' */
  border-color: #dc3545;
}

/* 6. (Opsional) Beri efek focus agar lebih jelas saat di-klik/tab */
.item-check:focus {
  box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25); /* Bayangan merah */
  border-color: #dc3545;
}
/* === AKHIR PERBAIKAN === */

</style>
</body>
</html>