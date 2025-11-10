<?php
// Ambil ID dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// === Data Blog ===
$posts = [
    [
        "id" => 1,
        "gambar" => "assets/img/post_rahasia_crispy.jpg",
        "judul" => "Rahasia Mendapatkan Kulit Ayam Crispy Sempurna",
        "tanggal" => "20 Oktober 2025",
        "kategori" => "Tips Masak",
        "konten" => "
        Siapa sih yang bisa menolak kulit ayam goreng yang super renyah dan gurih? 🍗 
        Kulit ayam crispy memang punya tempat spesial di hati para pecinta kuliner. Tapi ternyata, untuk mendapatkan hasil sempurna — renyah di luar dan tetap juicy di dalam — butuh teknik khusus, bukan asal goreng.
        <br><br>
        <h4>1. Gunakan Tepung Campuran Kering dan Basah</h4>
        Rahasia kerenyahan kulit ayam ada pada lapisan tepungnya. 
        Balur ayam ke dalam tepung kering, lalu celupkan ke adonan cair berbumbu, dan kembali ke tepung kering. 
        Lakukan dua kali agar lapisannya tebal, bertekstur, dan menghasilkan sensasi kriuk saat digigit. 
        <br><br>
        <h4>2. Marinasi dengan Bumbu Tepat</h4>
        Jangan buru-buru menggoreng! 
        Rendam ayam minimal 1 jam dalam campuran rempah dan susu cair agar bumbu meresap sampai ke serat daging. 
        Bumbu sederhana seperti bawang putih, lada, garam, dan paprika bubuk sudah cukup membuat rasa ayam naik level. 
        <br><br>
        <h4>3. Suhu Minyak Menentukan Hasil</h4>
        Pastikan minyak panas stabil di suhu 170–180°C. 
        Suhu terlalu rendah bikin tepung menyerap minyak, hasilnya lembek dan berminyak. 
        Sementara suhu terlalu tinggi bisa bikin luar gosong tapi dalamnya masih mentah. 
        Gunakan termometer dapur atau lakukan tes sederhana: masukkan sedikit tepung, kalau langsung naik ke permukaan dan berbuih halus, berarti minyak siap! 
        <br><br>
        <h4>4. Tiriskan dengan Benar</h4>
        Setelah matang, jangan ditumpuk! Letakkan di atas rak kawat agar udara mengalir dan minyak bisa menetes sempurna. 
        Kalau ditumpuk, uap panas yang terjebak justru bikin kulit jadi lembek. 
        <br><br>
        Nah, kalau semua langkah ini kamu ikuti, hasilnya pasti bikin nagih. 
        Coba di rumah dan rasakan bedanya! Siapa tahu kamu bisa bikin versi crispy lebih mantap dari dapur <strong>Goreng Chicken Co.</strong> 😋
        "
    ],
    [
        "id" => 2,
        "gambar" => "assets/img/post_sambal_baru.jpg",
        "judul" => "Memperkenalkan Sambal Geprek Level 5: Berani Coba?",
        "tanggal" => "15 Oktober 2025",
        "kategori" => "Promosi",
        "konten" => "
        Sambal baru ini bukan untuk yang berhati lemah! 🌶️ 
        <strong>Goreng Chicken Co.</strong> dengan bangga memperkenalkan <em>Sambal Geprek Level 5</em> — racikan cabai rawit merah pilihan yang digiling langsung setiap kali pesanan datang, tanpa bahan pengawet, tanpa kompromi.
        <br><br>
        <h4>🔥 Sensasi Pedas yang Menggigit</h4>
        Setiap suapan menghadirkan panas yang langsung menjalar ke lidah dan tenggorokan, tapi tetap seimbang berkat perpaduan bawang goreng, minyak cabai, dan garam laut asli. 
        Level pedasnya memang ekstrem, tapi aroma khasnya membuat siapa pun sulit berhenti makan! 
        <br><br>
        <h4>🌿 100% Bahan Alami</h4>
        Kami hanya menggunakan bahan segar dari petani lokal. 
        Cabai rawit merah dipilih satu per satu, lalu diolah di dapur kami menggunakan teknik tradisional untuk menjaga cita rasa autentik khas nusantara. 
        <br><br>
        <h4>🔥 Cocok Untuk Segala Menu</h4>
        Mau ayam geprek, nasi goreng, hingga kentang goreng — semua makin mantap dengan sentuhan sambal ini. 
        Sudah banyak pelanggan kami yang menyebut Level 5 ini sebagai 'uji nyali paling nikmat'. 
        <br><br>
        Berani coba? Datang ke cabang terdekat dan buktikan sendiri! 😈
        "
    ],
    [
        "id" => 3,
        "gambar" => "assets/img/post_sejarah.jpg",
        "judul" => "14 Tahun Perjalanan Kami: Dari Warung Kecil Hingga Terkenal",
        "tanggal" => "5 Oktober 2025",
        "kategori" => "Cerita",
        "konten" => "
        Setiap bisnis besar pasti berawal dari mimpi kecil — begitu juga dengan <strong>Goreng Chicken Co.</strong>. 
        Tahun 2011, kami memulai usaha dari warung kecil di sudut jalan yang sederhana. Dengan hanya satu wajan, sedikit modal, dan semangat pantang menyerah, kami melayani pelanggan pertama kami.
        <br><br>
        Hari demi hari, pelanggan datang karena rasa ayam goreng kami berbeda — bumbunya kuat, kulitnya garing, dan pelayanannya tulus. 
        Dari situ, reputasi mulai menyebar dari mulut ke mulut hingga akhirnya kami bisa membuka cabang pertama di kota sebelah.
        <br><br>
        Kini, setelah 14 tahun berjalan, kami bukan sekadar menjual ayam goreng. 
        Kami menjual pengalaman, kebersamaan, dan kenangan masa kecil yang hangat bersama keluarga. 
        Semua itu tidak akan terwujud tanpa dukungan pelanggan setia kami. 
        Terima kasih sudah menjadi bagian dari perjalanan panjang ini 🙏
        "
    ],
    [
        "id" => 4,
        "gambar" => "assets/img/post_pasangan.jpg",
        "judul" => "Minuman Terbaik Untuk Menemani Ayam Goreng Pedas",
        "tanggal" => "28 September 2025",
        "kategori" => "Review",
        "konten" => "
        Siapa bilang makan ayam pedas cukup dengan air putih? 
        <strong>Goreng Chicken Co.</strong> punya beberapa rekomendasi minuman yang bisa jadi pasangan sempurna buat ayam gorengmu! 
        <br><br>
        <h4>1. Teh Manis Dingin</h4>
        Klasik tapi efektif. Teh manis dingin mampu menetralkan rasa pedas dan menyegarkan tenggorokan. 
        Apalagi kalau disajikan dengan es batu dan aroma melati — sensasinya bikin nagih. 
        <br><br>
        <h4>2. Jus Lemon</h4>
        Asam segarnya membantu mengurangi minyak di mulut dan memberi kesegaran alami. 
        Kandungan vitamin C-nya juga baik untuk menjaga daya tahan tubuh. 
        <br><br>
        <h4>3. Susu Dingin</h4>
        Kalau kamu sudah kepedasan level berat, ini penyelamat sejati! 
        Protein dalam susu bisa menetralkan senyawa pedas (capsaicin), jadi lidahmu langsung terasa lega. 
        <br><br>
        Apa pun pilihannya, pastikan kamu nikmati ayam pedas dengan minuman favoritmu biar pengalaman makan makin mantap! 😋
        "
    ],
    [
        "id" => 5,
        "gambar" => "assets/img/post_bumbu_rahasia.jpg",
        "judul" => "Bumbu Rahasia yang Membuat Ayam Kami Begitu Lezat",
        "tanggal" => "10 September 2025",
        "kategori" => "Rahasia Dapur",
        "konten" => "
        Pernah penasaran kenapa ayam goreng kami punya aroma yang khas dan rasa yang susah dilupakan? 
        Jawabannya ada pada bumbu rahasia keluarga yang sudah diwariskan turun-temurun. 
        <br><br>
        <h4>🌿 7 Rempah Pilihan</h4>
        Kami menggunakan 7 jenis rempah premium seperti ketumbar, bawang putih kering, merica, jahe bubuk, kunyit, lengkuas, dan sedikit pala. 
        Semua dicampur dengan proporsi yang tepat, menghasilkan aroma yang kuat tapi tetap lembut di lidah. 
        <br><br>
        <h4>🔥 Proses Perendaman yang Sempurna</h4>
        Ayam direndam minimal 2 jam sebelum digoreng. Proses ini membuat bumbu meresap hingga ke dalam daging tanpa membuat tekstur ayam menjadi lembek. 
        Setelah digoreng, wangi rempah langsung menyebar — membuat siapa pun sulit menolak. 
        <br><br>
        Inilah yang membuat cita rasa <strong>Goreng Chicken Co.</strong> tetap konsisten dari dulu sampai sekarang. 
        Tidak ada yang instan, semua berawal dari racikan cinta dan kesabaran di dapur ❤️
        "
    ],
    [
        "id" => 6,
        "gambar" => "assets/img/post_tren_ayam2025.jpg",
        "judul" => "Tren Ayam Goreng 2025: Dari Crispy ke Extra Spicy",
        "tanggal" => "2 September 2025",
        "kategori" => "Trend",
        "konten" => "
        Dunia kuliner terus berkembang, dan ayam goreng tetap jadi primadona. 
        Tahun 2025 ini, tren mulai bergeser dari ayam crispy biasa ke varian yang lebih ekstrem: pedas, beraroma kuat, dan punya tampilan unik. 
        <br><br>
        <h4>🌶️ Pedas Jadi Gaya Hidup</h4>
        Dari generasi muda hingga orang tua, semua menyukai sensasi pedas. 
        Itulah sebabnya kami menghadirkan menu <em>Extra Spicy Boom</em> — ayam goreng super pedas dengan bumbu cabai kering dan minyak cabai khas nusantara. 
        <br><br>
        <h4>🍗 Inovasi Tak Berhenti</h4>
        Kami juga mengembangkan varian baru seperti ayam goreng keju leleh, ayam madu pedas, hingga ayam rasa sambal matah Bali. 
        Semua dibuat dengan bahan segar dan cita rasa khas Indonesia. 
        <br><br>
        <h4>🚀 Menuju Masa Depan Ayam Goreng Modern</h4>
        Bukan hanya soal rasa, kami juga menghadirkan konsep <em>smart kitchen</em> untuk efisiensi dan kualitas yang lebih konsisten di setiap cabang. 
        Tahun ini, <strong>Goreng Chicken Co.</strong> siap memimpin tren ayam goreng masa depan!
        "
    ],
];

// ========================================================
// === PERBAIKAN DIMULAI DI SINI ===
// ========================================================

// 1. Definisikan pengaturan pagination (HARUS SAMA dengan blog.php)
$posts_per_page = 4;

// 2. Cari artikel, index-nya, dan hitung halaman asalnya
$selected_post = null;
$post_index = -1;
$kembali_ke_halaman = 1; // Default kembali ke halaman 1

foreach ($posts as $index => $post) {
    if ($post['id'] === $id) {
        $selected_post = $post;
        $post_index = $index;
        break;
    }
}

// 3. Hitung halaman asalnya (jika post ditemukan)
if ($post_index > -1) {
    // (Misal index 0,1,2,3 -> (0/4)=0 -> hal 1)
    // (Misal index 4,5 -> (4/4)=1 -> hal 2)
    $kembali_ke_halaman = floor($post_index / $posts_per_page) + 1;
}

// 4. Buat link kembali yang dinamis
$link_kembali = "index.php?page=blog&hal=" . $kembali_ke_halaman;

// ========================================================
// === AKHIR PERBAIKAN ===
// ========================================================
?>

<?php if ($selected_post): ?>
<section class="blog-detail py-5 bg-light">
  <div class="container">

    <div class="text-center mb-4">
      <img src="<?= htmlspecialchars($selected_post['gambar']) ?>" 
           class="img-fluid rounded shadow-sm" 
           style="width: 100%; max-width: 900px; height: 450px; object-fit: cover; border-radius: 14px; margin: auto;">
    </div>

    <div class="text-center mb-3">
      <span class="badge bg-warning text-dark"><?= htmlspecialchars($selected_post['kategori']) ?></span>
      <small class="text-muted ms-2">
        <i class="bi bi-calendar"></i> <?= htmlspecialchars($selected_post['tanggal']) ?>
      </small>
    </div>

    <h2 class="fw-bold text-center mb-4"><?= htmlspecialchars($selected_post['judul']) ?></h2>

    <div class="content text-muted mb-5" style="line-height:1.85; font-size:1.05rem;">
      <?= $selected_post['konten'] ?>
    </div>

    <div class="text-center">
      <!-- 5. Gunakan link dinamis di tombol ini -->
      <a href="<?= htmlspecialchars($link_kembali) ?>" class="btn btn-danger fw-semibold px-4">← Kembali ke Blog</a>
    </div>

  </div>
</section>

<?php else: ?>
<section class="py-5 text-center">
  <div class="container">
    <h3 class="text-danger">Artikel tidak ditemukan.</h3>
    <a href="index.php?page=blog" class="btn btn-outline-danger mt-3">Kembali ke Blog</a>
  </div>
</section>
<?php endif; ?>
<!-- spacing untuk blog detail -->
<style>
.blog-detail { margin-top: 4.5rem; } 
@media (max-width: 575.98px) {
  .blog-detail { margin-top: 2.5rem; }
}
body.has-fixed-navbar { padding-top: 70px; }
</style>