<?php
// === SAFE SESSION START ===
if (function_exists('session_status')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} else {
    if (session_id() === '') session_start();
}

// === VALIDASI SESSION ===
if (!isset($_SESSION) || !is_array($_SESSION)) {
    $_SESSION = [];
}

// === LOGIN STATUS ===
$isLoggedIn = isset($_SESSION['pelanggan']);
$isAdmin = $isLoggedIn && isset($_SESSION['pelanggan']['role']) && $_SESSION['pelanggan']['role'] === 'admin';

// === BASE URL (FALLBACK) ===
if (!isset($BASE_URL)) {
    $BASE_URL = "."; 
}

// === CEK HALAMAN (LOGIKA BARU) ===
if (isset($currentPage) && $currentPage == 'static') {
    $activePage = 'static';
    $isHome = false;
} else {
    $activePage = $page ?? 'home'; 
    $isHome = ($activePage === 'home');
}

// === JUMLAH KERANJANG (LOGIKA YANG BENAR) ===
$jumlahKeranjang = 0;
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        if (is_array($item) && isset($item['qty'])) {
            $jumlahKeranjang += (int)$item['qty'];
        }
    }
}
?>

<nav id="mainNavbar" class="navbar navbar-expand-lg navbar-dark fixed-top <?= $isHome ? 'navbar-home' : 'navbar-static' ?>">
  <div class="container-fluid px-4 d-flex justify-content-between align-items-center">

    <a href="<?= $BASE_URL ?>/index.php?page=home" class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold">
      <img src="<?= $BASE_URL ?>/assets/img/Logo.png" width="40" height="40" class="rounded-circle border border-warning shadow-sm">
      <span>Goreng Chicken</span>
    </a>

    <ul class="navbar-nav d-none d-lg-flex fw-semibold text-uppercase gap-4 mx-auto">
      <li><a class="nav-link <?= ($activePage=='home'?'active-nav':'') ?>" href="<?= $BASE_URL ?>/index.php?page=home">BERANDA</a></li>
      <li><a class="nav-link <?= ($activePage=='about'?'active-nav':'') ?>" href="<?= $BASE_URL ?>/index.php?page=about">TENTANG KAMI</a></li>
      <li><a class="nav-link <?= ($activePage=='menu'?'active-nav':'') ?>" href="<?= $BASE_URL ?>/index.php?page=menu">MENU</a></li>
      <li><a class="nav-link <?= ($activePage=='blog'?'active-nav':'') ?>" href="<?= $BASE_URL ?>/index.php?page=blog">BLOG</a></li>
      <li><a class="nav-link <?= ($activePage=='contact'?'active-nav':'') ?>" href="<?= $BASE_URL ?>/index.php?page=contact">KONTAK</a></li>
    </ul>


    <div class="d-flex align-items-center gap-3">

      <?php if ($isLoggedIn && !$isAdmin): // HANYA tampil jika login DAN BUKAN admin ?>
      <a href="<?= $BASE_URL ?>/pages/keranjang.php" class="text-white position-relative fs-4">
        <i class="bi bi-cart-fill"></i>
        <?php if($jumlahKeranjang > 0): ?>
          <span class="badge bg-warning text-dark position-absolute top-0 start-100 translate-middle">
            <?= $jumlahKeranjang ?>
          </span>
        <?php endif; ?>
      </a>
      <?php elseif (!$isLoggedIn): // Tampil jika BELUM login (guest) ?>
      <a href="#" class="text-white position-relative fs-4" data-bs-toggle="modal" data-bs-target="#authModal">
        <i class="bi bi-cart-fill"></i>
      </a>
      <?php endif; // Jika admin, tidak akan tampil apa-apa ?>
      
      
      <?php if($isLoggedIn): ?>
      <div class="dropdown">
        <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
            <?php
            // 1. Ambil nama file foto dari session
            $foto_profil = $_SESSION['pelanggan']['foto'] ?? null;
            
            // 2. Tentukan path untuk <img> tag (menggunakan $BASE_URL)
            // PENTING: Pastikan folder upload Anda adalah '/assets/uploads/'
            $foto_url = $BASE_URL . '/assets/uploads/' . htmlspecialchars($foto_profil);
            
            // 3. Tentukan path server untuk file_exists()
            // PENTING: Ini mengasumsikan navbar.php ada di folder /layout/
            $foto_server_path = dirname(__FILE__) . '/../assets/uploads/' . $foto_profil;

            if (!empty($foto_profil) && file_exists($foto_server_path)) {
                // 4. JIKA FOTO ADA: Tampilkan <img>
                echo '<img src="' . $foto_url . '" alt="Foto" width="22" height="22" class="rounded-circle me-2" style="object-fit: cover;">';
            } else {
                // 5. JIKA TIDAK ADA FOTO: Tampilkan icon default
                echo '<i class="bi bi-person-circle me-2"></i>';
            }
            ?>
            <span><?= htmlspecialchars($_SESSION['pelanggan']['nama']) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          
          <?php if($isAdmin): ?>
            <li><a class="dropdown-item" href="<?= $BASE_URL ?>/admin/dashboard.php">Dashboard Admin</a></li>
          <?php else: ?>
            <li><a class="dropdown-item" href="<?= $BASE_URL ?>/pages/profile.php">Profil Saya</a></li>
            <li><a class="dropdown-item" href="<?= $BASE_URL ?>/pages/pesanan_saya.php">Riwayat Pesan</a></li>
          <?php endif; ?>
          <li><hr></li>
          <li><a class="dropdown-item text-danger" href="<?= $BASE_URL ?>/auth/logout.php">Logout</a></li>
        </ul>
      </div>
      <?php else: ?>
      <button class="btn btn-warning btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#authModal">
        <i class="bi bi-person-circle"></i> Login/Register
      </button>
      <?php endif; ?>
      <button class="navbar-toggler border-0 text-white fs-2" id="menuToggle">
        <i class="bi bi-list"></i>
      </button>
    </div>

  </div>
</nav>

<div id="overlayMenu" class="overlay-menu">
  <button class="close-btn" id="closeMenu">&times;</button>
  <ul class="overlay-nav list-unstyled text-center">
    <li><a href="<?= $BASE_URL ?>/index.php?page=home">BERANDA</a></li>
    <li><a href="<?= $BASE_URL ?>/index.php?page=about">TENTANG KAMI</a></li>
    <li><a href="<?= $BASE_URL ?>/index.php?page=menu">MENU</a></li>
    <li><a href="<?= $BASE_URL ?>/index.php?page=blog">BLOG</a></li>
    <li><a href="<?= $BASE_URL ?>/index.php?page=contact">KONTAK</a></li>
  </ul>
</div>

<script>
const navbar = document.getElementById('mainNavbar');
const isHome = <?= json_encode($isHome) ?>; 
let lastScroll = 0;
window.addEventListener('scroll', () => {
  const st = window.scrollY;
  if (isHome) {
      navbar.classList.toggle('nav-scroll', st > 60);
  }
  navbar.style.transform = (st > lastScroll && st > 100) ? "translateY(-100%)" : "translateY(0)";
  lastScroll = st <= 0 ? 0 : st;
});
const menuToggle = document.getElementById("menuToggle");
const overlayMenu = document.getElementById("overlayMenu");
const closeMenu = document.getElementById("closeMenu");
if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      overlayMenu.classList.add("active");
      document.body.style.overflow = "hidden";
    });
}
if (closeMenu) {
    closeMenu.addEventListener("click", () => {
      overlayMenu.classList.remove("active");
      document.body.style.overflow = "";
    });
}
document.querySelectorAll(".overlay-link").forEach(link => {
  link.addEventListener("click", () => {
    overlayMenu.classList.remove("active");
    document.body.style.overflow = "";
  });
});
</script>

<style>
body { padding-top: 80px; }
#mainNavbar { transition: all 0.35s ease; }
.navbar-home { background: transparent; }
.navbar-home.nav-scroll {
  background: rgba(0,0,0,0.9);
  backdrop-filter: blur(10px);
}
.navbar-static {
  background: rgba(0,0,0,0.9);
  backdrop-filter: blur(10px);
}
.overlay-menu {
  display: none; position: fixed; inset: 0; background:white;
  justify-content:center; align-items:center; z-index:2000;
}
.overlay-menu.active { display:flex; }
.close-btn { position:absolute; top:20px; right:25px; font-size:2.2rem; border:none; background:none; }
.overlay-nav a { font-size:1.6rem; margin:12px 0; font-weight:600; color:#000; }
.overlay-nav a:hover { color:#d32f2f; }
.active-nav {
  color: #ffc107 !important;
  border-bottom: 2px solid #ffc107;
}
</style>

<div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">Masuk Akun</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">

        <form id="loginForm">
          <div class="mb-3 text-start">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-danger w-100 fw-semibold mb-2">Login</button>
          <div class="text-center mb-3">
            <a href="<?= $BASE_URL ?>/auth/lupa_password_profile.php" class="text-decoration-none text-danger fw-semibold small">
              <i class="bi bi-key"></i> Lupa Password?
            </a>
          </div>
          <p class="small text-center">
            Belum punya akun?
            <a href="#" id="showRegister" class="fw-bold text-danger">Daftar Sekarang</a>
          </p>
        </form>

        <form id="registerForm" class="d-none">
          <div class="mb-3 text-start">
            <label class="form-label fw-semibold">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3 text-start">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-warning w-100 fw-semibold">Daftar</button>
          <p class="mt-3 small text-center">
            Sudah punya akun?
            <a href="#" id="showLogin" class="fw-bold text-danger">Login di sini</a>
          </p>
        </form>

      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.swal2-container.swal2-top-end {
  top: 90px !important; 
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

<script>
document.addEventListener("DOMContentLoaded", () => {
  const showRegister = document.getElementById("showRegister");
  const showLogin = document.getElementById("showLogin");
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");
  const authModal = new bootstrap.Modal(document.getElementById('authModal'));

  if (showRegister) {
    showRegister.addEventListener("click", (e) => {
      e.preventDefault();
      loginForm.classList.add("d-none");
      registerForm.classList.remove("d-none");
    });
  }
  if (showLogin) {
    showLogin.addEventListener("click", (e) => {
      e.preventDefault();
      registerForm.classList.add("d-none");
      loginForm.classList.remove("d-none");
    });
  }

  function showToast(icon, message) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: icon,
      title: message,
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      background: '#fff',
      color: '#333',
      customClass: {
        popup: 'shadow-sm small-toast'
      }
    });
  }

  if (loginForm) {
      loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        const res = await fetch("<?= $BASE_URL ?>/api/login_api.php", {
          method: "POST",
          body: formData
        });
        const data = await res.json();
        
        showToast(data.success ? 'success' : 'error', data.message);

        if (data.success) {
          authModal.hide();
          
          setTimeout(() => {
            if (data.role === 'admin') {
              window.location.href = data.redirect; 
            } else {
              window.location.reload();
            }
          }, 2000); 
        }
      });
  }

  if (registerForm) {
      registerForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(registerForm);
        const res = await fetch("<?= $BASE_URL ?>/api/register_api.php", {
          method: "POST",
          body: formData
        });
        const data = await res.json();
        
        showToast(data.success ? 'success' : 'error', data.message);
        
        if (data.success) {
          registerForm.reset();
          registerForm.classList.add("d-none");
          loginForm.classList.remove("d-none");
        }
      });
  }
});
</script>