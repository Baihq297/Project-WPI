<footer id="footer" class="bg-black text-white py-5 mb-0">
  <div class="container">
    <div class="row text-center text-md-start">

      <div class="col-md-3 mb-4">
        <h4 class="fw-bold"><span class="text-warning">Goreng</span> Chicken Co.</h4>
        <p class="small text-secondary">
          Kami menyajikan ayam goreng renyah dan juicy dengan resep rahasia yang telah memanjakan lidah sejak 2005.
        </p>
      </div>

      <div class="col-md-3 mb-4">
        <h5 class="fw-semibold mb-3">Contact Us</h5>
        <p class="small mb-1"><i class="bi bi-geo-alt"></i>Jl. Ayam Crispy No.45, RT.01/RW.02, Kota Rasa Enak, Jawa Tengah 50000</p>
        <p class="small mb-1"><i class="bi bi-envelope"></i> info@gorengchickenco.com</p>
        <p class="small"><i class="bi bi-telephone"></i>+62 8123 4567 8901</p>
      </div>

      <div class="col-md-3 mb-4">
        <h5 class="fw-semibold mb-3">Working Hours</h5>
        <p class="small mb-1">Mon - Sun : 10.00 - 22.00</p>
      </div>

      <div class="col-md-3 mb-4 text-center text-md-start">
        <h5 class="fw-semibold mb-3">Social Media</h5>
        <div class="d-flex justify-content-center justify-content-md-start gap-3">
          <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
          <a href="#" class="text-white fs-4"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>
    </div>

    <hr class="border-secondary my-4" />
    <p class="small text-center text-secondary mb-0">
      Copyright © <?= date('Y') ?> Goreng Chicken Co. All Rights Reserved.
    </p>
  </div>
</footer>
</body>
</html>

<style>
  /* 🌟 Pastikan footer nempel di bawah */
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    background: #fff;
  }

  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  #footer {
    margin-top: auto; /* Ini yang membuat footer selalu di bawah */
  }

  /* 🌟 Warna dan font footer */
  #footer {
    font-family: 'Poppins', sans-serif; /* Pastikan font ini di-link di head HTML */
  }

  #footer a:hover {
    color: #ffc107 !important; /* Warna kuning Bootstrap (text-warning) */
  }
</style>