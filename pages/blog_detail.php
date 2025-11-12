<?php
// Sertakan file koneksi database dan MULAI SESI
include __DIR__ . "/../includes/config.php"; 
// Asumsi session_start() sudah ada di config.php

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// === CEK STATUS LOGIN DAN AMBIL DATA SESI ===
$is_logged_in = isset($_SESSION['pelanggan']['id']);
$nama_pelanggan = $is_logged_in ? ($_SESSION['pelanggan']['nama'] ?? '') : '';
$id_pelanggan_aktif = $is_logged_in ? ($_SESSION['pelanggan']['id'] ?? 0) : 0;
$role_pelanggan = $is_logged_in ? ($_SESSION['pelanggan']['role'] ?? 'pelanggan') : 'guest';
$is_admin = ($role_pelanggan === 'admin'); 
// ===========================================

// === Data Blog (Dengan konten yang LEBIH PROFESIONAL dan menggunakan <strong>) ===
$posts = [
    [
        "id" => 1,
        "gambar" => "assets/img/post_rahasia_crispy.jpg",
        "judul" => "Analisis Mendalam: Rahasia Ilmiah di Balik Kulit Ayam Crispy Sempurna",
        "tanggal" => "20 Oktober 2025",
        "kategori" => "Tips Masak",
        "konten" => "
        Mencapai kerenyahan kulit ayam yang optimal adalah paduan antara seni kuliner dan <strong>ilmu pangan</strong>. Di Goreng Chicken Co., kami menyajikan tekstur renyah yang tahan lama dengan menguasai proses fisikokimia kritis selama preparasi dan penggorengan.

        <br><br>
        <h3>🔬 1. Biokimia Marinasi: Mencapai Daging yang Super Juicy</h3>
        Marinasi berfungsi ganda: sebagai infus rasa dan agen tenderisasi alami. Proses ini esensial untuk menjamin kelembutan daging (*succulence*) di bawah lapisan kulit yang garing.
        <ul>
            <li><strong>Tenderisasi Protein</strong>: Perendaman minimal 4 jam dalam *buttermilk* (atau media berasam laktat/sitrat) memanfaatkan asam lemah untuk memecah ikatan protein dan kolagen pada serat otot. Hal ini menghasilkan tekstur daging yang empuk tanpa menjadi liat.</li>
            <li><strong>Retensi Kelembapan</strong>: Cairan marinasi diserap maksimal, menciptakan penghalang hidrasi internal yang mencegah pengeringan daging saat terpapar suhu tinggi minyak.</li>
        </ul>

        <h3>🍚 2. Rekayasa Adonan Kering: The Ultimate Crispy Matrix</h3>
        Inovasi kami terletak pada Matriks Kerenyahan, sebuah formulasi adonan kering yang dirancang untuk ekspansi termal yang optimal.

        <p>Formula premium kami mengombinasikan tepung terigu protein rendah untuk kelembutan, dan bahan-bahan fungsional lainnya:</p>
        <ul>
            <li><strong>Peran *Baking Powder* (Chemical Leavening)</strong>: Adanya *baking powder* bekerja sebagai agen pengembang ganda. Saat bersentuhan dengan cairan marinasi dan panas, ia melepaskan gas $CO_2$ yang membentuk kantung udara mikro di lapisan tepung, menghasilkan tekstur 'kriwil' yang ringan dan berlapis.</li>
            <li><strong>Pati Modifikasi (Starch Inclusion)</strong>: Penambahan 10-15% pati (seperti tepung jagung atau tapioka) berperan vital. Pati meningkatkan titik gelatinisasi adonan, yang pada gilirannya memperkuat struktur lapisan dan meningkatkan ketahanan terhadap kelembapan (*moisture resistance*), memastikan kerenyahan bertahan lebih lama.</li>
        </ul>

        <h3>🌡️ 3. Kontrol Termal Presisi: Teknik *Two-Stage Frying*</h3>
        Suhu minyak adalah variabel terpenting. Kami menerapkan teknik <strong>penggorengan dua tahap (*double-frying*)</strong> untuk memaksimalkan tekstur dan kematangan internal.
        <ol>
            <li><strong>Tahap Pertama (*Cooking Phase*) - 160°C</strong>: Menggoreng pada suhu moderat (8-10 menit) memastikan panas menembus inti daging secara perlahan, mencapai kematangan sempurna (internal temperature safety zone).</li>
            <li><strong>Tahap Kedua (*Crisping Phase*) - 185°C</strong>: Ayam diangkat, diberi jeda singkat untuk melepaskan uap internal, lalu digoreng kembali sebentar (1-2 menit) pada suhu sangat tinggi. Gelombang panas kedua ini berfungsi melakukan *flash-drying* pada lapisan luar, menguapkan sisa air dan 'mengunci' lapisan menjadi sangat kering dan *extra crispy*.</li>
        </ol>

        <p>Dengan menguasai kontrol suhu, formulasi adonan, dan proses tenderisasi, kami menghilangkan kekhawatiran kulit ayam lembek. Inilah standar kualitas Goreng Chicken Co.!</p>
        "
    ],
    [
        "id" => 2,
        "gambar" => "assets/img/post_sambal_baru.jpg",
        "judul" => "Eksklusif: Menguji Batasan Skala Scoville dengan Sambal Geprek Level 5",
        "tanggal" => "15 Oktober 2025",
        "kategori" => "Promosi",
        "konten" => "
        Bagi para <strong>Aficionado Pedas</strong> yang mencari tantangan kuliner ekstrem, Level 5 dari Sambal Geprek kami hadir. Ini adalah formulasi *extra hot* premium yang diracik untuk memberikan pengalaman rasa yang eksplosif, dengan komitmen pada kesegaran dan standar kebersihan tertinggi dari Goreng Chicken Co.

        <br><br>
        <h3>🔥 1. Analisis Senyawa Aktif: Kekuatan Capsaicin Terkonsentrasi</h3>
        Sambal Geprek Level 5 didominasi oleh cabai rawit merah segar (*Capsicum frutescens*) yang kami seleksi karena kandungan <strong>Capsaicin</strong> yang ekstrem. Capsaicin adalah senyawa yang memicu sensasi pedas.
        <ul>
            <li><strong>Rentang Skala Scoville (SHU)</strong>: Sambal ini dirancang untuk mencapai antara <strong>30.000 hingga 50.000 SHU</strong>, secara resmi menempatkannya pada kategori *Extra Hot Commercial Grade*.</li>
            <li><strong>Keseimbangan Cita Rasa (*Flavor Profile*)</strong>: Meskipun pedasnya sangat tinggi, kami mempertahankan keseimbangan dengan gurihnya bawang putih segar yang terkaramelisasi dan sentuhan asam alami yang berfungsi sebagai *flavor balancer*.</li>
            <li><strong>Peringatan Konsumsi</strong>: Tingkat kepedasan ini memerlukan kehati-hatian. Selalu siapkan penawar dan hindari konsumsi saat kondisi perut sensitif.</li>
        </ul>

        <h3>🌿 2. Komitmen Kualitas dan Proses *A La Minute*</h3>
        Kualitas pedas kami tidak hanya dari levelnya, tetapi dari kesegaran bahan baku.
        <ol>
            <li><strong>Sourcing Cabai Lokal Premium</strong>: Kami hanya menggunakan cabai rawit grade A dari petani mitra, menjamin konsistensi kualitas dan kepedasan.</li>
            <li><strong>Produksi *A la Minute*</strong>: Untuk memaksimalkan aroma dan potensi capsaicin, sambal digiling dan diolah panas dengan minyak cabai (*chili oil*) berkualitas tinggi segera setelah pesanan diterima.</li>
            <li><strong>Keamanan Pangan</strong>: Kami berkomitmen pada standar pangan tertinggi, menjamin sambal 100% alami dan bebas dari pengawet sintetis.</li>
        </ol>

        <h3>🛡️ Panduan Konsumsi dan Neutralisasi Efek Capsaicin</h3>
        Efek capsaicin paling baik diredam oleh lemak dan protein.
        <ul>
            <li><strong>Penawar Optimal</strong>: Konsumsi produk berbasis <strong>dairy (susu, yogurt)</strong>. Lemak pada produk ini mengikat molekul capsaicin dan membantu meredakan sensasi terbakar lebih efektif daripada air.</li>
            <li><strong>Edukasi Minuman</strong>: Hindari minuman berkarbonasi atau beralkohol, karena justru dapat menyebarkan capsaicin ke seluruh rongga mulut lebih cepat.</li>
        </ul>

        <p>Sambal Geprek Level 5 adalah penawaran edisi terbatas. Apakah Anda siap membuktikan diri sebagai *Chili Head* sejati di Goreng Chicken Co.?</p>
        "
    ],
    [
        "id" => 3,
        "gambar" => "assets/img/post_sejarah.jpg",
        "judul" => "Evolusi Merek: 14 Tahun Goreng Chicken Co. dari Kios Lokal Menuju Waralaba Nasional",
        "tanggal" => "5 Oktober 2025",
        "kategori" => "Cerita",
        "konten" => "
        Perjalanan Goreng Chicken Co. adalah studi kasus tentang <strong>dedikasi merek</strong> dan <strong>eksekusi strategis</strong> yang konsisten. Didirikan pada tahun 2011 di sebuah kios sederhana, kami melawan tantangan pasar yang ketat dengan menjunjung tinggi dua pilar utama: kualitas produk otentik dan layanan personal.

        <br><br>
        <h3>🏘️ 1. Inisiasi dan Konsolidasi Merek (2011-2015)</h3>
        Fase awal difokuskan pada penguatan produk inti dan pembangunan *brand loyalty* di tingkat komunitas.
        <ul>
            <li><strong>Fokus Produk</strong>: Penyempurnaan formula marinasi dan penguasaan teknik *crispy-frying* eksklusif. Tujuan utamanya adalah konsistensi rasa yang tidak terdistraksi oleh ambisi ekspansi prematur.</li>
            <li><strong>Dampak Loyalitas</strong>: Reputasi 'Ayam Crispy Premium Lokal' tersebar secara organik (*word-of-mouth*), membentuk basis pelanggan yang loyal dan menjadi fondasi pertumbuhan merek.</li>
        </ul>

        <h3>📈 2. Standardisasi dan Skalabilitas (2016-2020)</h3>
        Fase ini berpusat pada penciptaan model yang dapat direplikasi untuk mendukung pertumbuhan waralaba tanpa mengorbankan kualitas.
        <ol>
            <li><strong>Penciptaan SOP (*Standard Operating Procedure*)</strong>: Implementasi SOP yang ketat di seluruh mata rantai operasional (marinasi, *coating*, kontrol suhu, layanan) untuk menjamin rasa yang identik di setiap lokasi.</li>
            <li><strong>Optimasi Rantai Pasok (*Supply Chain Management*)</strong>: Pembangunan kemitraan eksklusif dengan pemasok premium untuk menjamin ketersediaan bahan baku berkualitas tinggi dan konsisten, meminimalkan risiko operasional.</li>
            <li><strong>Diversifikasi Menu Strategis</strong>: Respons terhadap tren pasar dengan peluncuran produk pendamping seperti Nasi Geprek dan Chicken Burger, yang dirancang untuk menjaga *brand relevance*.</li>
        </ol>

        <h3>🚀 3. Digitalisasi dan Keterlibatan Konsumen (2021-Sekarang)</h3>
        Menghadapi era industri 4.0, Goreng Chicken Co. bertransformasi menjadi merek *customer-centric* yang adaptif:
        <ul>
            <li><strong>Integrasi Teknologi (*Digital Transformation*)</strong>: Peluncuran platform pemesanan seluler in-house dan integrasi yang mulus dengan agregator pihak ketiga untuk memperluas jangkauan pasar.</li>
            <li><strong>Pembangunan Ekosistem Loyalitas (GC Rewards)</strong>: Sistem poin dan personalisasi promosi untuk meningkatkan *Customer Lifetime Value* (CLV) dan memperkuat ikatan dengan pelanggan setia.</li>
            <li><strong>Misi Abadi</strong>: Kami tetap berpegang teguh pada misi awal—menyajikan ayam goreng berkualitas tinggi dengan sentuhan kehangatan layanan keluarga, kini dieksekusi dalam skala nasional.</li>
        </ul>

        <p>Kami menyampaikan apresiasi mendalam kepada seluruh pemangku kepentingan (pelanggan, tim, dan mitra) yang telah menenun sejarah 14 tahun Goreng Chicken Co. Kisah ini berlanjut, didorong oleh semangat inovasi dan komitmen kualitas.</p>
        "
    ],
    [
        "id" => 4,
        "gambar" => "assets/img/post_pasangan.jpg",
        "judul" => "Panduan Pairing: Minuman Terbaik untuk Mengimbangi Sensasi Ayam Goreng Pedas",
        "tanggal" => "28 September 2025",
        "kategori" => "Review",
        "konten" => "
        Pengalaman menyantap ayam goreng, terutama varian pedas, akan mencapai puncaknya dengan pendamping minuman yang tepat. Pilihan minuman yang cerdas tidak hanya menetralisir rasa pedas, tetapi juga membersihkan palet rasa, meningkatkan kenikmatan keseluruhan (*gastronomic experience*).

        <h3>🥛 1. Susu (Dairy-Based Beverages): The Ultimate Capsaicin Neutralizer</h3>
        Minuman berbasis susu adalah pilihan terbaik dari sudut pandang ilmiah. <strong>Lemak dan protein (kasein)</strong> yang terkandung dalam susu bekerja paling efektif dalam melarutkan dan menghilangkan molekul capsaicin (senyawa aktif pedas) dari reseptor lidah, meredakan sensasi terbakar secara cepat.

        <h3>🍋 2. Iced Lemon Tea: The Acidic Palate Cleanser</h3>
        Kombinasi antara rasa asam (dari lemon) dan manis (dari teh dan gula) berfungsi sebagai *palate cleanser* yang sangat baik. Keasaman membantu memotong rasa berminyak pada ayam, sementara rasa manis memberikan kontras yang menyegarkan setelah gigitan rempah yang intens.

        <h3>🍊 3. Fresh Orange Juice: Vitamin C Refreshment</h3>
        Jus jeruk segar memberikan rasa asam yang lembut dan manis alami, kaya akan Vitamin C. Minuman ini menjaga keseimbangan rasa, terutama setelah mengonsumsi makanan yang kaya bumbu dan minyak, mencegah perasaan 'eneg' atau terlalu berat.

        <p><strong>Rekomendasi Ahli Kami</strong>: Jika Anda memilih Ayam Geprek Level 5 atau varian *extra spicy* lainnya, <strong>Es Susu Murni Dingin</strong> adalah *pairing* yang direkomendasikan untuk efektivitas penetralan maksimal!</p>
        "
    ],
    [
        "id" => 5,
        "gambar" => "assets/img/post_bumbu_rahasia.jpg",
        "judul" => "Dibongkar: Filosofi Bumbu Rahasia yang Menciptakan *Umami* Ayam Kami",
        "tanggal" => "10 September 2025",
        "kategori" => "Rahasia Dapur",
        "konten" => "
        Keistimewaan dan kedalaman rasa pada ayam Goreng Chicken Co. adalah hasil dari bumbu racikan proprietary kami, yang telah melalui fase riset dan pengembangan (*R&D*) bertahun-tahun. Kami bertujuan menciptakan rasa <strong>Umami</strong> yang khas, gurih, dan memiliki lapisan aroma yang kompleks.

        <h3>🌱 Komposisi Rempah dan Rasio Kritis</h3>
        Rempah utama (seperti bawang putih, merica, ketumbar, dan jahe) diracik dalam rasio yang sangat presisi. Keseimbangan ini memastikan rasa gurih yang dihasilkan intens tetapi <strong>tidak didominasi oleh satu rempah saja</strong>, memberikan profil rasa yang halus (*subtle*) dan unik.

        <h3>🧂 Teknik *Deep-Penetration* Marinasi Bertahap</h3>
        Rasa tidak hanya diaplikasikan pada permukaan, tetapi secara harfiah dimasukkan ke dalam serat daging. Proses marinasi kami memerlukan waktu <strong>minimal 6 jam</strong> untuk memungkinkan rempah dan garam berdifusi penuh ke dalam mioglobin (protein daging). Hal ini menjamin rasa meresap hingga ke tulang, menghasilkan cita rasa yang konsisten dari gigitan pertama hingga terakhir.
        "
    ],
    [
        "id" => 6,
        "gambar" => "assets/img/post_tren_ayam2025.jpg",
        "judul" => "Proyeksi Tren Kuliner 2025: Dominasi Ayam Goreng *Extra Spicy* dan *Ultra-Crispy*",
        "tanggal" => "2 September 2025",
        "kategori" => "Trend",
        "konten" => "
        Tahun 2025 diproyeksikan membawa pergeseran signifikan dalam preferensi konsumen ayam goreng. Konsumen semakin mencari <strong>sensasi rasa yang eksplosif</strong> dan tuntutan terhadap kualitas tekstur yang semakin tinggi.

        <h3>🔥 Gelombang *Heat Surge* (Rasa Pedas Ekstrem)</h3>
        Tingkat kepedasan (SHU) telah bertransisi dari sekadar pilihan tambahan menjadi <strong>unsur utama diferensiasi produk</strong>. Brand yang mampu menyajikan level pedas yang otentik dan menantang, dengan tetap menjaga kualitas rasa dasar, akan memimpin pasar. Ini adalah era di mana *Spicy* menjadi *Extra Spicy*.

        <h3>🍗 Tekstur *Ultra-Crispy* (Kriwil Tahan Lama)</h3>
        Tuntutan terhadap kualitas lapisan tepung telah meningkat. Konsumen menginginkan lapisan yang sangat ringan, renyah, dan, yang paling penting, <strong>tahan terhadap kelembapan</strong> (*holding crispiness*)—bahkan setelah melalui proses pengiriman (*delivery*). Inovasi dalam formulasi adonan untuk mencapai tekstur kriwil yang ideal menjadi kunci.
        "
    ],
    [
        "id" => 7,
        "gambar" => "assets/img/promo1.jpg",
        "judul" => "Peluncuran Program Loyalitas: GC Rewards - Meningkatkan *Customer Lifetime Value*",
        "tanggal" => "25 Agustus 2025",
        "kategori" => "Promosi",
        "konten" => "
        Kami dengan bangga meluncurkan program loyalitas eksklusif untuk memberikan penghargaan setinggi-tingginya kepada pelanggan setia: <strong>GC Rewards</strong>. Program ini dirancang untuk meningkatkan *Customer Lifetime Value* (CLV) melalui sistem poin yang transparan dan *reward* yang menarik.

        <h3>🎁 Akumulasi Poin: Mekanisme Reward</h3>
        Sistem perolehan poin kami dibuat sederhana dan bernilai:
        <ul>
            <li><strong>Basis Poin</strong>: Setiap transaksi senilai <strong>Rp10.000</strong> akan mengkonversi menjadi <strong>1 poin</strong> reward.</li>
            <li><strong>Bonus & Event</strong>: Poin tambahan ditawarkan pada event promosi khusus atau saat peluncuran menu baru.</li>
        </ul>

        <h3>🍗 Redeem & Benefit: Penukaran Hadiah Premium</h3>
        Poin dapat ditukarkan dengan produk andalan kami:
        <ul>
            <li><strong>30 Poin</strong>: <strong>Ayam Crispy Utuh Gratis</strong> – *Reward* klasik kami.</li>
            <li><strong>50 Poin</strong>: <strong>Paket Combo Super Eksklusif</strong> – Paket makanan bernilai tinggi.</li>
        </ul>

        <p>Loyalitas Anda adalah prioritas kami. Semakin sering Anda berinteraksi dengan Goreng Chicken Co., semakin cepat Anda menikmati *reward* eksklusif. Mulailah kumpulkan poin Anda hari ini!</p>
        "
    ],
    [
        "id" => 8,
        "gambar" => "assets/img/chef1.jpg",
        "judul" => "Di Balik Tirai: Proses Jaminan Kualitas (*Quality Control*) Ayam Kami",
        "tanggal" => "18 Agustus 2025",
        "kategori" => "Rahasia Dapur",
        "konten" => "
        Setiap potong ayam yang meninggalkan dapur kami adalah cerminan dari komitmen kami terhadap kualitas tak tertandingi. Kami mengimplementasikan proses <strong>Quality Control (QC) Multi-Tahap</strong> yang ketat untuk memastikan konsistensi dan keamanan pangan.

        <h3>✅ Kontrol Sumber Bahan Baku (*Sourcing & Freshness*)</h3>
        Kami menerapkan standar seleksi bahan baku yang sangat tinggi:
        <ul>
            <li><strong>Kesegaran Mutlak</strong>: Daging ayam harus diproses dan digunakan dalam waktu <strong>24 jam</strong> sejak penyembelihan.</li>
            <li><strong>Integritas Daging</strong>: Kami menjamin daging bebas dari penambahan air (glazing) atau bahan kimia pengawet yang dapat mengubah tekstur dan rasa.</li>
        </ul>

        <h3>🎯 Konsistensi Operasional dan Cita Rasa</h3>
        Kunci dari pengalaman waralaba adalah konsistensi rasa di manapun.
        <p>Semua staf operasional kami menjalani pelatihan ketat berdasarkan SOP memasak standar global. Proses ini menjamin bahwa profil rasa, tingkat kerenyahan, dan kematangan produk <strong>tetap identik</strong> di setiap cabang, dimanapun Anda menikmatinya.</p>
        "
    ],
    [
        "id" => 9,
        "gambar" => "assets/img/menu_burger.jpg",
        "judul" => "Pengumuman Kolaborasi Gastronomi: GCC x Saus Keju Populer (Edisi Terbatas)",
        "tanggal" => "10 Agustus 2025",
        "kategori" => "Event",
        "konten" => "
        Dalam upaya inovasi menu musiman, Goreng Chicken Co. dengan bangga mengumumkan kolaborasi gastronomi spesial dengan brand saus keju viral terkemuka untuk menciptakan <strong>Cheese Melt Chicken Burger (Limited Edition)</strong>.

        <p>Menu ini menyatukan dua kekuatan kuliner: ayam crispy andalan kami yang legendaris, dipadukan dengan lelehan saus keju *creamy* yang kaya rasa dan otentik. Perpaduan kontras tekstur garing dengan keju lembut menghasilkan pengalaman <strong>'Crunch and Melt'</strong> yang adiktif, menjadikannya favorit instan di kalangan pelanggan muda.</p>
        "
    ]
];

// 1. Cari artikel yang dipilih berdasarkan ID dari URL
$selected_post = null;
foreach ($posts as $post) {
    if ($post['id'] === $id) {
        $selected_post = $post;
        break;
    }
}

// 2. Buat link "Kembali" yang cerdas
$kategori_kembali = isset($_GET['kategori']) ? urlencode($_GET['kategori']) : 'Semua';
$halaman_kembali = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
$link_kembali = "index.php?page=blog&hal=" . $halaman_kembali . "&kategori=" . $kategori_kembali;

// ========================================================
// === AMBIL DATA KOMENTAR DARI DATABASE (DENGAN parent_id) ===
// ========================================================
$komentar_mentah = [];

if ($id > 0 && isset($conn)) {
    // Ambil semua kolom yang relevan, termasuk parent_id, role, dan id_pelanggan
    $sql_komentar = "SELECT 
                        c.id_komentar, c.parent_id, c.isi_komentar, c.tanggal_komentar, c.id_pelanggan, 
                        IFNULL(p.nama, c.nama) AS nama_penulis, p.role
                     FROM komentar c
                     LEFT JOIN pelanggan p ON c.id_pelanggan = p.id_pelanggan
                     WHERE c.id_post = ? 
                     ORDER BY c.tanggal_komentar ASC"; 
    
    $stmt_komentar = mysqli_prepare($conn, $sql_komentar);
    if ($stmt_komentar) {
        mysqli_stmt_bind_param($stmt_komentar, "i", $id);
        mysqli_stmt_execute($stmt_komentar);
        $result_komentar = mysqli_stmt_get_result($stmt_komentar);
        
        while ($row = mysqli_fetch_assoc($result_komentar)) {
            $komentar_mentah[] = $row;
        }
        mysqli_stmt_close($stmt_komentar);
    }
}

// === FUNGSI PEMBENTUK KOMENTAR BERJENJANG ===
function buildCommentTree($comments) {
    $tree = [];
    $indexed = [];
    
    foreach ($comments as $comment) {
        $indexed[$comment['id_komentar']] = $comment;
        $indexed[$comment['id_komentar']]['children'] = [];
    }

    foreach ($indexed as $id_komentar => $comment) {
        // Hanya komentar dengan parent_id (balasan) yang harus dicari induknya
        if ($comment['parent_id'] !== NULL && isset($indexed[$comment['parent_id']])) {
            $indexed[$comment['parent_id']]['children'][] = &$indexed[$id_komentar];
        } else {
            // Komentar level tertinggi
            $tree[] = &$indexed[$id_komentar];
        }
    }
    return $tree;
}

$komentar_list_berjenjang = buildCommentTree($komentar_mentah);
$total_komentar = count($komentar_mentah);

// === FUNGSI REKURSIF UNTUK MENAMPILKAN KOMENTAR ===
function displayCommentsRecursive($comments, $is_admin, $id_post, $id_pelanggan_aktif, $level = 0) {
    $html = '';
    
    foreach ($comments as $komen) {
        $is_author_admin = ($komen['role'] === 'admin');
        // Pastikan c.id_pelanggan diambil dari query agar perbandingan ini valid.
        $is_current_user_author = (isset($komen['id_pelanggan']) && $komen['id_pelanggan'] == $id_pelanggan_aktif);
        $can_edit = $is_admin || $is_current_user_author; // Boleh edit jika admin ATAU dia penulisnya
        
        // Tentukan style untuk balasan (indentasi)
        $indent_style = $level > 0 ? 'ms-' . min(4, $level * 2) : ''; 
        $margin_top = $level > 0 ? 'mt-2' : '';

        // Tampilan Komentar
        $html .= '<div class="d-flex gap-3 mb-4 ' . $indent_style . ' ' . $margin_top . '">';
        // Tentukan ikon/avatar (dapat disempurnakan lagi)
        $icon_class = $is_author_admin ? 'text-danger' : 'text-muted';
        $html .= '<i class="bi bi-person-circle fs-2 ' . $icon_class . '"></i>'; 
        
        $html .= '<div>';
        $html .= '<h6 class="fw-bold mb-0">';
        $html .= htmlspecialchars($komen['nama_penulis'] ?? 'Pengguna');
        if ($is_author_admin) {
            $html .= '<span class="badge bg-danger ms-2">ADMIN</span>';
        }
        $html .= '</h6>';
        $html .= '<small class="text-muted">' . date('d F Y, H:i', strtotime($komen['tanggal_komentar'])) . '</small>';
        
        // Tambahkan ID unik ke paragraf komentar agar bisa diganti oleh JS
        $html .= '<p class="mt-1 mb-0 comment-content-text" id="comment-text-' . $komen['id_komentar'] . '">' . nl2br(htmlspecialchars($komen['isi_komentar'])) . '</p>';

        // Tombol Aksi (Balas, Edit & Hapus)
        $html .= '<small class="d-block mt-1">';
        
        // FITUR EDIT (UNTUK ADMIN DAN PENULIS)
        if ($can_edit) {
            // Konten komentar dienkode untuk diteruskan ke JS
            $escaped_content = htmlspecialchars($komen['isi_komentar'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            
            $html .= '<a href="#" class="text-secondary fw-semibold me-3 edit-link" data-comment-id="' . $komen['id_komentar'] . '" data-comment-content="' . $escaped_content . '">';
            $html .= '<i class="bi bi-pencil-square"></i> Edit</a>';
        }
        
        // FITUR BALAS (HANYA UNTUK ADMIN)
        if ($is_admin) {
            $html .= '<a href="#" class="text-primary fw-semibold me-3 reply-link" data-comment-id="' . $komen['id_komentar'] . '" data-comment-name="' . htmlspecialchars($komen['nama_penulis'] ?? 'Pengguna') . '">';
            $html .= '<i class="bi bi-reply"></i> Balas (Admin)</a>';
        }

        // FITUR HAPUS (HANYA UNTUK ADMIN)
        if ($is_admin) {
            $html .= '<a href="pages/proses_komentar.php?action=delete&id_komentar=' . $komen['id_komentar'] . '&id_post=' . $id_post . '" ';
            $html .= 'onclick="return confirm(\'Yakin ingin menghapus komentar ini? Tindakan ini permanen.\');" ';
            $html .= 'class="text-danger fw-semibold">';
            $html .= '<i class="bi bi-trash"></i> Hapus (Moderasi)</a>';
        }
        $html .= '</small>';
        
        // Placeholder untuk Form Edit
        if ($can_edit) {
            $html .= '<div class="edit-form-container mt-2" id="edit-form-' . $komen['id_komentar'] . '"></div>';
        }

        // Placeholder untuk Form Balasan (diisi oleh JS)
        if ($is_admin) {
             $html .= '<div class="reply-form-container mt-2" id="reply-form-' . $komen['id_komentar'] . '"></div>';
        }


        $html .= '</div>'; // Tutup div inner
        $html .= '</div>'; // Tutup d-flex

        // Tampilkan balasan (rekursi)
        if (!empty($komen['children'])) {
            $html .= displayCommentsRecursive($komen['children'], $is_admin, $id_post, $id_pelanggan_aktif, $level + 1);
        }
    }
    return $html;
}
?>

<?php if ($selected_post): ?>
<section class="blog-detail py-5 bg-light">
    <div class="container">
        
        <div class="text-center mb-5">
            <img src="<?= htmlspecialchars($selected_post['gambar']) ?>" 
                 class="img-fluid rounded shadow" 
                 alt="<?= htmlspecialchars($selected_post['judul']) ?>"
                 style="max-height: 400px; object-fit: cover;">
        </div>
        <div class="content text-muted mb-5" style="line-height:1.85; font-size:1.05rem;">
            <?= $selected_post['konten'] ?>
        </div>

        <hr class="my-5">
        
        <div id="comments" class="comments-section">
            <h4 class="fw-bold mb-4">Komentar (<?= $total_komentar ?>)</h4>

            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-3">Tinggalkan Komentar</h5>
                    
                    <?php if ($is_admin): ?>
                        <div class="alert alert-danger text-center">
                            Anda login sebagai <strong>Administrator</strong>. Gunakan tombol "Balas" di bawah untuk merespons komentar pelanggan.
                        </div>
                    <?php elseif ($is_logged_in): ?>
                        <p class="text-success fw-semibold">Anda berkomentar sebagai: <?= htmlspecialchars($nama_pelanggan) ?></p>
                        
                        <form action="pages/proses_komentar.php?action=add" method="POST">
                            <input type="hidden" name="id_post" value="<?= htmlspecialchars($id) ?>">
                            <input type="hidden" name="parent_id" value=""> <div class="mb-3">
                                <label for="komentar" class="form-label">Komentar</label>
                                <textarea name="komentar" class="form-control" id="komentar" rows="3" placeholder="Tulis komentar utama Anda di sini..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning fw-semibold">Kirim Komentar</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            Anda harus <a href="#" data-bs-toggle="modal" data-bs-target="#authModal" class="alert-link fw-bold">Login</a> untuk dapat memberikan komentar.
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="comment-list">
                <?php if (empty($komentar_mentah)): ?>
                    <div class="alert alert-secondary text-center">Jadilah yang pertama berkomentar!</div>
                <?php else: ?>
                    <?= displayCommentsRecursive($komentar_list_berjenjang, $is_admin, $selected_post['id'], $id_pelanggan_aktif) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center">
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
<style>
/* CSS Tambahan untuk indentasi balasan */
.ms-2 { margin-left: 0.5rem !important; }
.ms-4 { margin-left: 1.5rem !important; }
.ms-6 { margin-left: 3rem !important; }
.ms-8 { margin-left: 4.5rem !important; }

/* CSS lainnya tetap sama */
.blog-detail { margin-top: 4.5rem; padding-bottom: 4rem; } 
@media (max-width: 575.98px) {
    .blog-detail { margin-top: 2.5rem; }
}
body.has-fixed-navbar { padding-top: 70px; }
.comments-section {
    max-width: 900px;
    margin: 0 auto 3rem auto; /* Posisi di tengah dan beri jarak bawah */
}
</style>

<script>
    // === SCRIPT JAVASCRIPT UNTUK MENAMPILKAN FORM BALASAN/EDIT KOMENTAR ===
    document.addEventListener('DOMContentLoaded', function() {
        const replyLinks = document.querySelectorAll('.reply-link');
        const editLinks = document.querySelectorAll('.edit-link'); 

        const currentPostId = <?= $selected_post['id'] ?>;
        const adminName = "<?= htmlspecialchars($nama_pelanggan) ?>";
        
        // Fungsi untuk menutup semua form yang terbuka (edit dan balas)
        function closeAllForms() {
            document.querySelectorAll('.reply-form-container').forEach(container => container.innerHTML = '');
            document.querySelectorAll('.edit-form-container').forEach(container => container.innerHTML = '');
            // Tampilkan kembali semua teks komentar yang mungkin tersembunyi
            document.querySelectorAll('.comment-content-text').forEach(text => text.style.display = 'block'); 
        }

        // --- Logika Tombol BALAS (REPLY) ---
        replyLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const commentId = this.getAttribute('data-comment-id');
                const commentName = this.getAttribute('data-comment-name');
                const formContainer = document.getElementById('reply-form-' + commentId);

                // Tutup semua form lain, kecuali jika form ini sudah terbuka
                if (formContainer.innerHTML === '') {
                    closeAllForms();
                }

                // Jika form sudah terbuka, tutup
                if (formContainer.innerHTML !== '') {
                    formContainer.innerHTML = '';
                    return;
                }

                // Buat HTML form balasan
                const replyFormHtml = `
                    <div class="card p-3 bg-light border-warning border-2">
                        <p class="text-muted small mb-2">Membalas <strong>@${commentName}</strong> sebagai ${adminName}</p>
                        <form action="pages/proses_komentar.php?action=reply" method="POST">
                            <input type="hidden" name="id_post" value="${currentPostId}">
                            <input type="hidden" name="parent_id" value="${commentId}">
                            
                            <div class="mb-3">
                                <textarea name="komentar" class="form-control" rows="2" placeholder="Balasan Anda untuk ${commentName}..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary fw-semibold">Kirim Balasan</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="document.getElementById('reply-form-${commentId}').innerHTML = ''; closeAllForms();">Batal</button>
                        </form>
                    </div>
                `;

                // Masukkan form ke container
                formContainer.innerHTML = replyFormHtml;
            });
        });
        
        // --- Logika Tombol EDIT ---
        editLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const commentId = this.getAttribute('data-comment-id');
                // Decode konten, karena di PHP sudah di-encode dengan htmlspecialchars(..., ENT_QUOTES)
                const commentContent = link.getAttribute('data-comment-content').replace(/&quot;/g, '"').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>'); 
                
                const formContainer = document.getElementById('edit-form-' + commentId);
                const textElement = document.getElementById('comment-text-' + commentId);

                // Tutup semua form lain, kecuali jika form ini sudah terbuka
                if (formContainer.innerHTML === '') {
                    closeAllForms();
                }

                // Jika form sudah terbuka, tutup dan tampilkan kembali teks
                if (formContainer.innerHTML !== '') {
                    formContainer.innerHTML = '';
                    textElement.style.display = 'block'; 
                    return;
                }
                
                // Sembunyikan teks komentar dan tampilkan form
                textElement.style.display = 'none';

                // Buat HTML form edit
                const editFormHtml = `
                    <div class="card p-3 bg-light border-secondary border-2">
                        <p class="text-muted small mb-2">Edit Komentar</p>
                        <form action="pages/proses_komentar.php?action=edit" method="POST">
                            <input type="hidden" name="id_post" value="${currentPostId}">
                            <input type="hidden" name="id_komentar" value="${commentId}">
                            
                            <div class="mb-3">
                                <textarea name="komentar" class="form-control" rows="2" required>${commentContent}</textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success fw-semibold">Simpan Perubahan</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="document.getElementById('edit-form-${commentId}').innerHTML = ''; document.getElementById('comment-text-${commentId}').style.display = 'block'; closeAllForms();">Batal</button>
                        </form>
                    </div>
                `;

                // Masukkan form ke container
                formContainer.innerHTML = editFormHtml;
            });
        });
    });
</script>
<style>
/* --- Styling artikel blog agar rapi dan mudah dibaca --- */
.blog-detail .content {
    text-align: justify;                  /* Rata kiri-kanan */
    text-justify: inter-word;             /* Rata antar kata */
    line-height: 1.9;                     /* Spasi antar baris */
    font-size: 1.05rem;
    color: #444;
}

/* Heading di dalam konten artikel */
.blog-detail .content h3 {
    margin-top: 1.8rem;
    margin-bottom: 0.8rem;
    font-weight: 700;
    color: #333;
}

/* Paragraf dan daftar */
.blog-detail .content p {
    margin-bottom: 1rem;
}

/* Bullet list & numbering */
.blog-detail .content ul,
.blog-detail .content ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
}

.blog-detail .content li {
    margin-bottom: 0.4rem;
    text-align: justify;
}

/* Emoji heading biar sejajar rapi */
.blog-detail .content h3::before {
    margin-right: 6px;
}
</style>
