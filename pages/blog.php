<?php
// data posts (sama seperti yang kamu punya)
$posts = [
    ["id"=>1,"gambar"=>"assets/img/post_rahasia_crispy.jpg","judul"=>"Rahasia Mendapatkan Kulit Ayam Crispy Sempurna","tanggal"=>"20 Oktober 2025","kategori"=>"Tips Masak","ringkasan"=>"Kami bongkar teknik rahasia dapur kami untuk menghasilkan tekstur renyah yang tak tertandingi."],
    ["id"=>2,"gambar"=>"assets/img/post_sambal_baru.jpg","judul"=>"Memperkenalkan Sambal Geprek Level 5: Berani Coba?","tanggal"=>"15 Oktober 2025","kategori"=>"Promosi","ringkasan"=>"Sambal terbaru kami hadir dengan tantangan pedas yang akan membuat Anda ketagihan. Siapkan susu!"],
    ["id"=>3,"gambar"=>"assets/img/post_sejarah.jpg","judul"=>"14 Tahun Perjalanan Kami: Dari Warung Kecil Hingga Terkenal","tanggal"=>"5 Oktober 2025","kategori"=>"Cerita","ringkasan"=>"Baca kisah inspiratif bagaimana Goreng Chicken Co. tumbuh menjadi merek ayam favorit Anda."],
    ["id"=>4,"gambar"=>"assets/img/post_pasangan.jpg","judul"=>"Minuman Terbaik Untuk Menemani Ayam Goreng Pedas","tanggal"=>"28 September 2025","kategori"=>"Review","ringkasan"=>"Kami merekomendasikan beberapa minuman segar yang sempurna untuk menetralkan rasa pedas."],
    ["id"=>5,"gambar"=>"assets/img/post_bumbu_rahasia.jpg","judul"=>"Bumbu Rahasia yang Membuat Ayam Kami Begitu Lezat","tanggal"=>"10 September 2025","kategori"=>"Rahasia Dapur","ringkasan"=>"Kami berbagi sedikit rahasia tentang bumbu rempah yang membuat ayam kami memiliki rasa khas yang tak bisa dilupakan."],
    ["id"=>6,"gambar"=>"assets/img/post_tren_ayam2025.jpg","judul"=>"Tren Ayam Goreng 2025: Dari Crispy ke Extra Spicy","tanggal"=>"2 September 2025","kategori"=>"Trend","ringkasan"=>"Simak tren terbaru dalam dunia kuliner ayam goreng — dari cita rasa klasik hingga varian super pedas yang sedang booming!"],
];

// === PAGINATION ===
$posts_per_page = 4;
$total_posts = count($posts);
$total_pages = (int) ceil($total_posts / $posts_per_page);
$page = isset($_GET['hal']) ? (int) $_GET['hal'] : 1;
if ($page < 1) $page = 1;
if ($page > $total_pages) $page = $total_pages;

$start_index = ($page - 1) * $posts_per_page;
$page_posts = array_slice($posts, $start_index, $posts_per_page);
?>

<section id="blog" class="blog-section py-5 bg-light">
  <div class="container">

    <!-- Header -->
    <header class="text-center mb-5">
      <h6 class="text-danger fw-bold mb-2">Insights & Recipes</h6>
      <h2 class="fw-bold text-dark">The Crispy Chronicles</h2>
      <p class="text-muted">Jelajahi berita terbaru, tips memasak, dan cerita di balik Goreng Chicken Co.</p>
    </header>

    <!-- Blog Cards -->
    <div class="row g-4 justify-content-center">
      <?php foreach ($page_posts as $post) : ?>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm blog-card h-100">
            <div class="row g-0">
              <div class="col-md-4">
                <img src="<?= htmlspecialchars($post['gambar']) ?>"
                     class="img-fluid rounded-start blog-img"
                     alt="<?= htmlspecialchars($post['judul']) ?>">
              </div>

              <div class="col-md-8">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div>
                    <span class="badge bg-warning text-dark mb-2 small"><?= htmlspecialchars($post['kategori']) ?></span>
                    <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($post['judul']) ?></h5>
                    <p class="card-text text-muted small"><?= htmlspecialchars($post['ringkasan']) ?></p>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <p class="card-text mb-0"><small class="text-muted"><i class="bi bi-calendar"></i> <?= htmlspecialchars($post['tanggal']) ?></small></p>
                    <a href="index.php?page=blog_detail&id=<?= urlencode($post['id']) ?>" class="btn btn-sm btn-outline-danger fw-semibold">Baca Selengkapnya</a>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <nav class="mt-5">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="index.php?page=blog&hal=<?= $page - 1 ?>">Previous</a>
        </li>

        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
          <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
            <a class="page-link" href="index.php?page=blog&hal=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
          <a class="page-link" href="index.php?page=blog&hal=<?= $page + 1 ?>">Next</a>
        </li>
      </ul>
    </nav>

  </div>
</section>

<!-- CSS (sama seperti yang kamu punya) -->
<style>
/* ... pakai CSS blog-card, blog-img, dll seperti versi kamu ... */
.blog-card { border-radius: 12px; overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; }
.blog-card:hover { transform: translateY(-5px); box-shadow: 0 15px 25px rgba(0,0,0,.15); }
.blog-img { width:100%; height:100%; object-fit:cover; transition: transform .4s ease; }
.blog-card:hover .blog-img { transform: scale(1.05); }
@media (max-width:767px){ .blog-img{ height:150px; width:100%; } }
.card-body { padding:1.5rem; }
.pagination .page-item.active .page-link { background-color: #ffc107; border-color:#ffc107; color:#212529; }
.pagination .page-link { color:#ffc107; }
.pagination .page-link:hover { color:#ffc107; }
</style>
<style>
/* Jika navbar kamu fixed-top, tambahkan padding pada body supaya aman */
body.has-fixed-navbar .blog-section {
  margin-top: 0; /* margin di-section 0 karena padding di body sudah mengatur jarak */
}

/* Fallback: kalau body tidak punya class, set margin pada section */
.blog-section { margin-top: 2rem; }

@media (min-width: 992px) {
  .blog-section { margin-top: 4.5rem; }
}
</style>
