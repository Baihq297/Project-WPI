<?php
// Pastikan session berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "api/koneksi.php"; // Pastikan path benar dari root index.php

// === PERBAIKAN: Definisikan status login di sini ===
$isLoggedIn = isset($_SESSION['pelanggan']);
$isAdmin = $isLoggedIn && isset($_SESSION['pelanggan']['role']) && $_SESSION['pelanggan']['role'] === 'admin';
// ===============================================
?>

<section id="menu" class="menu-section py-5">
  <div class="container text-center">

    <h6 class="text-danger fw-bold mb-2">Crispy & Juicy</h6>
    <h2 class="fw-bold mb-5">Menu Goreng Chicken Co.</h2>

    <?php
    // (Loop kategori Anda sudah benar)
    $kategoriRes = mysqli_query($conn, "SELECT DISTINCT kategori FROM menu ORDER BY kategori ASC");

    if ($kategoriRes && mysqli_num_rows($kategoriRes) > 0) {
      while ($kat = mysqli_fetch_assoc($kategoriRes)) {
        $kategori = ucfirst(htmlspecialchars($kat['kategori']));
        echo "<h4 class='text-start mb-4 text-warning'>Menu {$kategori}</h4>";
        echo "<div class='row g-4 justify-content-center'>";

        // (Query menu Anda sudah benar)
        $menuRes = mysqli_query($conn, "
          SELECT * FROM menu 
          WHERE kategori='" . mysqli_real_escape_string($conn, $kat['kategori']) . "' 
          ORDER BY nama_menu ASC
        ");

        if ($menuRes && mysqli_num_rows($menuRes) > 0) {
          while ($m = mysqli_fetch_assoc($menuRes)) {
            $gambar = !empty($m['gambar']) ? "assets/img/" . htmlspecialchars($m['gambar']) : "assets/img/placeholder.jpg";
            $harga = number_format((float)$m['harga'], 0, ',', '.');
            
            // 
            // ============================================
            //  PERBAIKAN LOGIKA TAMPILAN STOK
            // ============================================
            //
            $stok_asli = max(0, intval($m['stok'])); // Stok asli dari DB (cth: 15)
            $status_asli = strtolower($m['status']); // Status asli dari DB (cth: 'habis')
            $status_display = ucfirst(htmlspecialchars($m['status'])); // Status untuk ditampilkan (cth: 'Habis')

            // Tentukan apakah produk dianggap "Habis"
            $is_habis = ($stok_asli <= 0 || $status_asli == 'habis');
            
            // Stok yang Tampil: Jika status 'habis', tampilkan 0, jika tidak, tampilkan stok asli
            $stok_display = $is_habis ? 0 : $stok_asli; 
            //
            // ============================================
            //  AKHIR PERBAIKAN
            // ============================================

            $nama_menu = htmlspecialchars($m['nama_menu']);
            $id_menu = htmlspecialchars($m['id_menu']);

            // Badge stok (sekarang pakai $is_habis)
            $badge = $is_habis 
              ? "<span class='badge bg-secondary position-absolute top-0 start-0 m-2'>Habis</span>" 
              : "<span class='badge bg-success position-absolute top-0 start-0 m-2'>Tersedia</span>";

            // Tombol / kontrol (sekarang pakai $is_habis)
            if ($is_habis) {
                // 1. Stok habis
                $button = "<button class='btn btn-secondary btn-sm mt-3 w-100 fw-semibold' disabled>Stok Habis</button>";
            
            } elseif ($isAdmin) {
                // 2. Jika dia ADMIN (Stok Tersedia)
                $button = "
                    <div class='quantity-control d-flex justify-content-center align-items-center gap-2 mt-2'>
                        <button type='button' class='btn btn-outline-secondary btn-sm fw-bold minus disabled-btn' disabled data-id='{$id_menu}'>−</button>
                        <input type='text' class='form-control form-control-sm text-center fw-semibold qty-input disabled-input' value='1' data-id='{$id_menu}' readonly>
                        <button type='button' class='btn btn-outline-secondary btn-sm fw-bold plus disabled-btn' disabled data-id='{$id_menu}' data-stok='{$stok_asli}'>+</button>
                    </div>
                    <button class='btn btn-outline-secondary btn-sm mt-2 w-100 fw-semibold' disabled>
                        <i class='bi bi-person-gear'></i> Mode Admin
                    </button>
                ";
            } elseif ($isLoggedIn) {
                // 3. Jika dia PELANGGAN (Stok Tersedia)
                $button = "
                    <div class='quantity-control d-flex justify-content-center align-items-center gap-2 mt-2'>
                        <button type='button' class='btn btn-outline-danger btn-sm fw-bold minus' data-id='{$id_menu}'>−</button>
                        <input type='text' inputmode='numeric' class='form-control form-control-sm text-center fw-semibold qty-input' value='1' data-id='{$id_menu}' readonly>
                        <button type='button' class='btn btn-outline-success btn-sm fw-bold plus' data-id='{$id_menu}' data-stok='{$stok_asli}'>+</button>
                    </div>
                    <button type='button' class='btn btn-danger btn-sm w-100 mt-2 add-to-cart' data-id='{$id_menu}'>
                        <i class='bi bi-cart-plus'></i> Tambah ke Keranjang
                    </button>
                ";
            } else {
                // 4. Jika dia GUEST (Belum Login)
                $button = "
                    <div class='quantity-control d-flex justify-content-center align-items-center gap-2 mt-2'>
                        <button type='button' class='btn btn-outline-secondary btn-sm fw-bold minus disabled-btn' disabled data-id='{$id_menu}'>−</button>
                        <input type='text' class='form-control form-control-sm text-center fw-semibold qty-input disabled-input' value='1' data-id='{$id_menu}' readonly>
                        <button type='button' class='btn btn-outline-secondary btn-sm fw-bold plus disabled-btn' disabled data-id='{$id_menu}' data-stok='{$stok_asli}'>+</button>
                    </div>
                    <button class='btn btn-outline-warning btn-sm mt-2 w-100 fw-semibold login-required' data-bs-toggle='modal' data-bs-target='#authModal'>
                        <i class='bi bi-lock'></i> Login untuk Pesan
                    </button>
                ";
            }
            // ============================================

            echo "
              <div class='col-6 col-md-4 col-lg-3'>
                <div class='card border-0 shadow-sm h-100 position-relative'>
                  {$badge}
                  <img src='{$gambar}' class='card-img-top' alt='{$nama_menu}'>
                  <div class='card-body'>
                    <h5 class='card-title fw-semibold'>{$nama_menu}</h5>
                    <p class='text-muted mb-1'>Rp {$harga}</p>
                    
                    <p class='small text-muted mb-2'>Stok: <strong>{$stok_display}</strong> | Status: <strong>{$status_display}</strong></p>
                    
                    {$button}
                  </div>
                </div>
              </div>
            ";
          }
        } else {
          echo "<p class='text-muted'>Belum ada menu di kategori ini.</p>";
        }

        echo "</div><hr class='my-4'>";
      }
    } else {
      echo "<p class='text-muted'>Belum ada data menu yang tersedia di database.</p>";
    }
    ?>
  </div>
</section>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const getQtyInput = id => document.querySelector(`.qty-input[data-id='${id}']`);
  const getPlusBtn = id => document.querySelector(`.plus[data-id='${id}']`);
  const getMinusBtn = id => document.querySelector(`.minus[data-id='${id}']`);

  document.querySelectorAll(".qty-input").forEach(input => {
    const id = input.dataset.id;
    const plus = getPlusBtn(id);
    const minus = getMinusBtn(id);
    const stok = parseInt(plus?.dataset.stok || "0");
    const val = parseInt(input.value || "1");
    if (minus) minus.disabled = val <= 1;
    if (plus) plus.disabled = stok > 0 && val >= stok;
  });

  document.querySelectorAll(".plus").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const stok = parseInt(btn.dataset.stok);
      const input = getQtyInput(id);
      let val = parseInt(input.value);
      if (val < stok) val++;
      input.value = val;
      btn.disabled = val >= stok;
      const minus = getMinusBtn(id);
      if (minus) minus.disabled = val <= 1;
    });
  });

  document.querySelectorAll(".minus").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const input = getQtyInput(id);
      let val = parseInt(input.value);
      if (val > 1) val--;
      input.value = val;
      btn.disabled = val <= 1;
      const plus = getPlusBtn(id);
      if (plus) plus.disabled = val >= parseInt(plus.dataset.stok);
    });
  });

  document.querySelectorAll(".add-to-cart").forEach(btn => {
    btn.addEventListener("click", async () => {
      const id = btn.dataset.id;
      const qty = parseInt(getQtyInput(id).value || "1");
      const stok = parseInt(getPlusBtn(id).dataset.stok);

      if (qty > stok) {
        showToast('warning', `Maksimum ${stok} item tersedia.`);
        return;
      }

      try {
        const res = await fetch("pages/keranjang_tambah.php", {
          method: "POST",
          body: new URLSearchParams({ id, qty })
        });
        const data = await res.json();
        showToast(
          data.success ? 'success' : 'error',
          data.message || (data.success ? 'Ditambahkan ke keranjang.' : 'Terjadi kesalahan.')
        );
        if (data.success) {
          const cartBadge = document.querySelector(".bi-cart-fill + span, .cart-badge");
          if (cartBadge) {
            let jumlah = parseInt(cartBadge.textContent || "0");
            cartBadge.textContent = jumlah + qty; 
          }
        }
      } catch (err) {
        showToast('error', 'Gagal menambah ke keranjang.');
      }
    });
  });

  function showToast(icon, message) {
    const nav = document.getElementById('mainNavbar');
    const defaultOffset = 12;
    let offsetPx = 72; 
    if (nav) {
      offsetPx = (nav.offsetHeight || 0) + defaultOffset;
    }
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: icon,
      title: message,
      showConfirmButton: false,
      timer: 1800,
      timerProgressBar: true,
      background: '#fff',
      color: '#333',
      customClass: {
        popup: 'shadow-sm small-toast'
      },
      target: document.body,
      onOpen: (toast) => {
        const container = Swal.getContainer();
        if (container.classList.contains('swal2-top-end')) {
           container.style.top = `${offsetPx}px`;
        }
      }
    });
  }

  const nav = document.getElementById('mainNavbar');
  const defaultOffset = 12;
  let offsetPx = 72; 
  if (nav) {
    offsetPx = (nav.offsetHeight || 0) + defaultOffset;
  }
  document.documentElement.style.setProperty('--swal-top-offset', offsetPx + 'px');
});
</script>


<style>
/* (Style Anda tidak berubah) */
.menu-section {
  background-color: #fff;
}
.menu-section h2 {
  color: #222;
}
.card {
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card img {
  height: 200px;
  object-fit: cover;
}
.card-body {
  padding: 1rem;
}
.badge {
  font-size: 0.8rem;
  padding: 6px 10px;
}
.quantity-control {
  margin-bottom: 5px;
}
.qty-input {
  width: 45px;
  text-align: center;
  border: 1px solid #ddd;
  border-radius: 6px;
  background-color: #f8f9fa;
}
.menu-section {
  margin-top: 100px;
}
@media (max-width: 768px) {
  .menu-section {
    margin-top: 80px;
  }
}
.disabled-btn {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}
.disabled-input {
  background-color: #eee !important;
  color: #999 !important;
}
.login-required {
  background-color: #ffc107;
  color: #000;
  border: none;
}
.login-required:hover {
  background-color: #ffca2c;
  color: #000;
}
.swal2-container.swal2-top-end {
  top: var(--swal-top-offset, 72px) !important;
  right: 12px !important;
  left: auto !important;
}
.small-toast {
  width: 280px !important;
  padding: 0.8rem !important;
  font-size: 0.9rem !important;
  border-radius: 8px !important;
}
.swal2-popup.swal2-toast {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>