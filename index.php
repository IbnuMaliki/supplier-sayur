<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Beranda';
define('ALAMAT_CONST', 'Jl. H. Salihun No. 33, RT03, RW03, Kebon Jeruk, Jakarta Barat');

// Produk unggulan
$stmt = $pdo->query("SELECT p.*, k.nama AS kategori_nama FROM produk p LEFT JOIN kategori k ON p.kategori_id=k.id WHERE p.status='aktif' AND p.stok>0 ORDER BY p.id LIMIT 4");
$produkUnggulan = $stmt->fetchAll();

// Testimoni dari database
$stmt = $pdo->query("SELECT r.*, u.nama, u.nama_warung FROM rating r JOIN users u ON r.user_id=u.id ORDER BY r.id DESC LIMIT 3");
$testimoni = $stmt->fetchAll();

// Load hero background dari pengaturan
$heroBg = 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1600&q=80';
try {
    $pdo->prepare("INSERT IGNORE INTO pengaturan (kunci,nilai) VALUES (?,?)")->execute(['hero_bg_file','']);
    $pdo->prepare("INSERT IGNORE INTO pengaturan (kunci,nilai) VALUES (?,?)")->execute(['hero_bg_url','']);
    $heroFile = $pdo->query("SELECT nilai FROM pengaturan WHERE kunci='hero_bg_file'")->fetchColumn();
    if ($heroFile) {
        $heroBg = APP_URL . '/uploads/hero/' . $heroFile;
    }
} catch(Exception $e) {}

include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">

<!-- HERO -->
<section class="hero">
<div class="hero-bg">
  <img src="<?= $heroBg ?>" alt="Pasar Sayuran Segar">
</div>
<div class="hero-overlay"></div>
<div class="hero-content">
  <div class="hero-badge"><span class="icon-badge" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="rgba(255,255,255,0.9)"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg></span> Supplier Terpercaya #1 Jakarta</div>
  <h1>Sayuran <span>Segar</span><br>Langsung dari Sumber</h1>
  <p>Supplier Sayur Azam Heri menyediakan sayuran segar berkualitas tinggi untuk warung dan usaha Anda. Pengiriman pagi hari, harga kompetitif, kualitas terjamin.</p>
  <div class="hero-actions">
    <a class="btn btn-primary" href="<?= APP_URL ?>/produk.php"><span class="icon-btn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></span> Pesan Sekarang</a>
    <a class="btn btn-outline" href="#dokumentasi">Lihat Kegiatan Kami</a>
  </div>
  <div class="hero-stats">
    <div class="hero-stat">
      <div class="hero-stat-num">10+</div>
      <div class="hero-stat-label">Warung Partner</div>
    </div>
    <div class="hero-stat">
      <div class="hero-stat-num">98%</div>
      <div class="hero-stat-label">Kepuasan Customer</div>
    </div>
    <div class="hero-stat">
      <div class="hero-stat-num">10+</div>
      <div class="hero-stat-label">Jenis Sayuran</div>
    </div>
  </div>
</div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
<div class="trust-item">
  <svg class="trust-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 0 1 10 10c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2z"/><path d="M12 6v6l4 2"/></svg>
  Sayuran Segar Harian
  </div>
<div class="trust-item">
  <svg class="trust-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
  Pengiriman Pagi Hari
  </div>
<div class="trust-item">
  <svg class="trust-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
  Kualitas Terjamin
  </div>
<div class="trust-item">
  <svg class="trust-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
  Harga Grosir Kompetitif
  </div>
<div class="trust-item">
  <svg class="trust-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
  Support 7 Hari
  </div>
</div>

<!-- DOKUMENTASI -->
<?php
// Ambil foto dokumentasi dari database (maks 5, urut by urutan)
$docsStmt = $pdo->query("SELECT * FROM dokumentasi WHERE aktif=1 ORDER BY urutan ASC, id ASC LIMIT 5");
$doksList  = $docsStmt->fetchAll();
?>
<section class="section" id="dokumentasi">
<div class="section-header">
  <div class="section-label"><span class="icon-label" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></span> Kegiatan Kami</div>
  <div class="section-title">Dokumentasi Aktivitas Supplier</div>
  <div class="section-subtitle">Kami bekerja keras setiap hari memastikan sayuran terbaik sampai ke tangan Anda dengan kesegaran terjaga.</div>
</div>
<?php if (count($doksList) > 0): ?>
<div class="docs-grid">
  <?php foreach ($doksList as $dok):
      // Tentukan src gambar: upload lokal atau URL eksternal
      if ($dok['tipe'] === 'upload') {
          $imgSrc = APP_URL . '/uploads/dokumentasi/' . sanitize($dok['gambar']);
      } else {
          $imgSrc = sanitize($dok['gambar']);
      }
    ?>
  <div class="doc-item">
    <img src="<?= $imgSrc ?>" alt="<?= sanitize($dok['judul']) ?>" loading="lazy">
    <div class="doc-overlay">
      <span class="doc-label"><?= sanitize($dok['keterangan'] ?? $dok['judul']) ?></span>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<!-- Fallback jika tabel belum ada / kosong -->
<div class="docs-grid">
  <div class="doc-item"><img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=800&q=80" alt="Pasar"><div class="doc-overlay"><span class="doc-label"> Pemilihan Sayuran di Pasar Induk</span></div></div>
  <div class="doc-item"><img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=600&q=80" alt="Sortir"><div class="doc-overlay"><span class="doc-label"> Proses Penyortiran &amp; QC</span></div></div>
  <div class="doc-item"><img src="https://images.unsplash.com/photo-1519996529931-28324d5a630e?w=600&q=80" alt="Kemas"><div class="doc-overlay"><span class="doc-label"> Pengemasan Higienis</span></div></div>
  <div class="doc-item"><img src="https://images.unsplash.com/photo-1580913428023-02c695666d61?w=600&q=80" alt="Kirim"><div class="doc-overlay"><span class="doc-label"> Distribusi ke Warung</span></div></div>
  <div class="doc-item"><img src="https://images.unsplash.com/photo-1594070319944-7c0cbebb6f58?w=600&q=80" alt="Segar"><div class="doc-overlay"><span class="doc-label"> Sayuran Segar Siap Jual</span></div></div>
</div>
<?php endif; ?>
</section>

<!-- PRODUK UNGGULAN -->
<section class="section section-gray">
<div class="section-header">
  <div class="section-label"><span class="icon-label" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg></span> Produk Unggulan</div>
  <div class="section-title">Sayuran Paling Laris</div>
</div>
<div class="products-grid">
  <?php foreach ($produkUnggulan as $p): ?>
  <div class="product-card">
    <div class="product-img-wrap">
      <img src="<?= getProductImage($p['nama'], 400, $p['gambar'] ?? null) ?>" alt="<?= sanitize($p['nama']) ?>" loading="lazy">
      <div class="product-badge"><?= sanitize($p['badge'] ?? 'Segar') ?></div>
    </div>
    <div class="product-info">
      <div class="product-name"><?= sanitize($p['nama']) ?></div>
      <div class="product-desc"><?= sanitize($p['deskripsi'] ?? '') ?></div>
      <div class="product-price"><?= formatRupiah($p['harga']) ?> <span class="product-price-label">/<?= sanitize($p['satuan'] ?? 'kg') ?></span></div>
      <div class="product-grosir">Grosir: <?= formatRupiah($p['harga_grosir']) ?>/<?= sanitize($p['satuan'] ?? 'kg') ?> (min.<?= (int)$p['min_grosir'] ?>kg)</div>
      <div class="product-actions">
        <?php if (isAdmin()): ?>
          <button class="btn-cart" disabled
            style="width:100%;cursor:not-allowed;background:var(--n-200);color:var(--n-500);font-size:11px;border:none;font-family:var(--font-body);"
            title="Admin tidak dapat memesan produk">
            Admin tidak dapat memesan
          </button>
        <?php elseif (isLoggedIn()): ?>
          <form method="POST" action="<?= APP_URL ?>/ajax/keranjang.php" style="flex:1;width:100%;">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="produk_id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="redirect" value="<?= APP_URL ?>">
            <button type="submit" class="btn-cart" style="width:100%;">+ Keranjang</button>
          </form>
        <?php else: ?>
          <a href="#" onclick="showLoginModal('keranjang');return false;" class="btn-cart" style="width:100%;display:block;text-align:center;">+ Keranjang</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<div class="text-center" style="margin-top:36px;">
  <a class="btn btn-secondary" href="<?= APP_URL ?>/produk.php">Lihat Semua Produk →</a>
</div>
</section>

<!-- TESTIMONI -->
<?php
// Hitung statistik rating
$rStat = $pdo->query("SELECT COUNT(*) as total, ROUND(AVG(bintang),1) as avg_rating FROM rating")->fetch();
$totalRating = (int)($rStat['total'] ?? 0);
$avgRating   = (float)($rStat['avg_rating'] ?? 0);
$starCount   = [];
for ($s = 5; $s >= 1; $s--) {
    $r = $pdo->prepare("SELECT COUNT(*) FROM rating WHERE bintang=?");
    $r->execute([$s]);
    $starCount[$s] = (int)$r->fetchColumn();
}
// Ambil SEMUA rating dari DB (untuk filter JS)
$allRating = $pdo->query("SELECT r.*, u.nama, u.nama_warung FROM rating r JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC")->fetchAll();

// Data default jika belum ada rating
$defaultTestimoni = [
    ['id'=>'d1','bintang'=>5,'komentar'=>'Sayurannya selalu segar dan tiba tepat waktu setiap pagi. Warung saya jadi lebih ramai karena kualitas bahan yang bagus!','nama'=>'Ibu Sari Dewi','nama_warung'=>'Warung Sari — Bekasi'],
    ['id'=>'d2','bintang'=>5,'komentar'=>'Sudah 2 tahun berlangganan, tidak pernah kecewa. Pelayanannya sangat responsif dan harga bersaing.','nama'=>'Pak Karto','nama_warung'=>'Warung Pak Karto — Bogor'],
    ['id'=>'d3','bintang'=>4,'komentar'=>'Cabai merahnya selalu segar. Pengiriman jam 5 pagi sangat membantu persiapan warung saya setiap hari.','nama'=>'Mbok Narsih','nama_warung'=>'Warung Bu Narsih — Depok'],
];
$cards = count($allRating) > 0 ? $allRating : $defaultTestimoni;
$useDefault = count($allRating) === 0;
?>
<section class="section">
<div class="section-header">
  <div class="section-label"><span class="icon-label" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span> Testimoni</div>
  <div class="section-title">Kata Mereka Tentang Kami</div>
  <div class="section-subtitle">Kepercayaan warung menjadi motivasi kami untuk terus memberikan pelayanan terbaik.</div>
</div>

<!-- RATING SUMMARY PANEL -->
<?php if ($totalRating > 0): ?>
<div style="background:white; border:1px solid var(--slate-100); border-radius:var(--radius-xl); padding:32px 36px; margin-bottom:32px; display:grid; grid-template-columns:200px 1fr; gap:40px; align-items:center; box-shadow:var(--shadow-sm);">
  <!-- Kiri: skor besar -->
  <div style="text-align:center;">
    <div style="font-size:72px; font-weight:800; color:var(--slate-900); font-family:var(--font-display); line-height:1;">
      <?= number_format($avgRating, 1) ?>
    </div>
    <div style="display:flex; justify-content:center; gap:4px; margin:10px 0;">
      <?php for ($s = 1; $s <= 5; $s++): ?>
        <?php if ($s <= floor($avgRating)): ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <?php elseif ($s == ceil($avgRating) && $avgRating != floor($avgRating)): ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><defs><linearGradient id="half2"><stop offset="50%" stop-color="#f59e0b"/><stop offset="50%" stop-color="#d1d5db"/></linearGradient></defs><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" fill="url(#half2)" stroke="#f59e0b" stroke-width="1"/></svg>
        <?php else: ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#e5e7eb" stroke="#d1d5db" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
    <div style="font-size:13px; color:var(--slate-500);"><?= $totalRating ?> ulasan</div>
  </div>

  <!-- Kanan: bar persentase — klik untuk filter -->
  <div style="display:flex; flex-direction:column; gap:10px;">
    <?php for ($s = 5; $s >= 1; $s--):
      $cnt = $starCount[$s];
      $pct = $totalRating > 0 ? round($cnt / $totalRating * 100) : 0;
    ?>
    <div style="display:flex; align-items:center; gap:12px; cursor:pointer; border-radius:8px; padding:4px 6px; transition:background 0.2s;"
         onclick="filterStar(<?= $s ?>)"
         onmouseover="this.style.background='var(--slate-50)'"
         onmouseout="this.style.background='transparent'"
         title="Filter <?= $s ?> bintang">
      <div style="display:flex; align-items:center; gap:4px; width:52px; flex-shrink:0;">
        <span style="font-size:13px; font-weight:700; color:var(--slate-700);"><?= $s ?></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
      <div style="flex:1; background:var(--slate-100); border-radius:100px; height:10px; overflow:hidden;">
        <div style="height:100%; width:<?= $pct ?>%; background:<?= $pct>0?'#f59e0b':'transparent' ?>; border-radius:100px; transition:width 0.6s ease;"></div>
      </div>
      <div style="width:70px; flex-shrink:0; text-align:right;">
        <span style="font-size:13px; color:var(--slate-500);"><?= $cnt ?> (<?= $pct ?>%)</span>
      </div>
    </div>
    <?php endfor; ?>
  </div>
</div>
<?php endif; ?>

<!-- TOMBOL FILTER BINTANG -->
<?php if (!$useDefault): ?>
<div style="display:flex; align-items:center; gap:10px; margin-bottom:28px; flex-wrap:wrap;">
  <span style="font-size:13px; font-weight:600; color:var(--slate-500);">Filter:</span>

  <!-- Lihat Semua -->
  <button id="filter-btn-all" onclick="filterStar(0)"
    style="display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:100px; border:1.5px solid var(--slate-200); background:white; color:var(--slate-600); font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; font-family:var(--font-body);">
    Lihat Semua
    <span id="badge-all" style="background:var(--slate-100); border-radius:100px; padding:1px 8px; font-size:12px;"><?= $totalRating ?></span>
  </button>

  <!-- Filter per bintang -->
  <?php for ($s = 5; $s >= 1; $s--): ?>
  <button id="filter-btn-<?= $s ?>" onclick="filterStar(<?= $s ?>)"
    style="display:inline-flex; align-items:center; gap:5px; padding:8px 16px; border-radius:100px; border:1.5px solid var(--slate-200); background:white; color:var(--slate-600); font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; font-family:var(--font-body);"
    <?= $starCount[$s] === 0 ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : '' ?>>
    <?= $s ?>
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    <span style="background:var(--slate-100); border-radius:100px; padding:1px 7px; font-size:12px; color:var(--slate-500);"><?= $starCount[$s] ?></span>
  </button>
  <?php endfor; ?>

  <!-- Tombol Batal Filter -->
  <button id="btn-batal-filter" onclick="batalFilter()"
    style="display:none; align-items:center; gap:6px; padding:8px 16px; border-radius:100px; border:1.5px solid var(--red-500); background:var(--red-100); color:var(--red-800); font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; font-family:var(--font-body);">
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    Batal Filter
  </button>

  <!-- Counter tampil -->
  <span id="filter-count" style="font-size:13px; color:var(--slate-400); margin-left:auto;"></span>
</div>
<?php endif; ?>

<!-- KARTU TESTIMONI -->
<div class="testimonials-grid" id="testimoni-grid">
  <?php foreach ($cards as $idx => $t):
    $bintang = (int)($t['bintang'] ?? 5);
  ?>
  <div class="testimonial-card"
       data-bintang="<?= $bintang ?>"
       data-idx="<?= $idx ?>"
       style="transition:opacity 0.25s, transform 0.25s; <?= $idx >= 3 ? 'display:none;' : '' ?>">
    <!-- Bintang SVG -->
    <div style="display:flex; gap:3px; margin-bottom:12px; align-items:center;">
      <?php for ($s = 1; $s <= 5; $s++): ?>
        <?php if ($s <= $bintang): ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <?php else: ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="#e5e7eb" stroke="#d1d5db" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <?php endif; ?>
      <?php endfor; ?>
      <span style="font-size:12px; color:var(--slate-400); margin-left:4px;"><?= $bintang ?>/5</span>
    </div>
    <div class="testi-quote">"</div>
    <div class="testi-text"><?= sanitize($t['komentar'] ?? '') ?></div>
    <div class="testi-author">
      <div class="testi-avatar"><?= strtoupper(substr($t['nama'] ?? 'U', 0, 2)) ?></div>
      <div>
        <div class="testi-name"><?= sanitize($t['nama'] ?? '') ?></div>
        <div class="testi-store"><?= sanitize($t['nama_warung'] ?? '-') ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pesan jika hasil filter kosong -->
<div id="filter-empty" style="display:none; text-align:center; padding:40px 20px; color:var(--slate-400);">
  <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="display:block;margin:0 auto 12px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
  <div style="font-size:15px; font-weight:600; color:var(--slate-500);">Belum ada ulasan untuk bintang ini</div>
  <button onclick="batalFilter()" style="margin-top:14px; padding:8px 20px; background:var(--green-600); color:white; border:none; border-radius:100px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font-body);">Lihat Semua Ulasan</button>
</div>

<!-- Tombol Lihat Lebih Banyak (default state) -->
<?php $totalCards = count($cards); ?>
<?php if ($totalCards > 3): ?>
<div id="btn-lihat-lebih-wrap" style="text-align:center; margin-top:24px;">
  <button id="btn-lihat-lebih" onclick="lihatSemua()"
    style="padding:10px 28px; background:white; border:1.5px solid var(--green-500); color:var(--green-700); border-radius:100px; font-size:14px; font-weight:600; cursor:pointer; transition:all 0.2s; font-family:var(--font-body);">
    Lihat Semua <?= $totalCards ?> Ulasan
  </button>
</div>
<?php endif; ?>

</section>

<script>
const TOTAL_CARDS = <?= count($cards) ?>;
let currentFilter = -1; // -1 = default (3 kartu), 0 = semua, 1-5 = filter bintang

function resetBtnStyles() {
    const btnAll = document.getElementById('filter-btn-all');
    if (btnAll) {
        btnAll.style.background  = 'white';
        btnAll.style.color       = 'var(--slate-600)';
        btnAll.style.borderColor = 'var(--slate-200)';
    }
    for (let s = 1; s <= 5; s++) {
        const btn = document.getElementById('filter-btn-' + s);
        if (!btn) continue;
        btn.style.background  = 'white';
        btn.style.color       = 'var(--slate-600)';
        btn.style.borderColor = 'var(--slate-200)';
    }
}

function filterStar(bintang) {
    currentFilter = bintang;
    const cards   = document.querySelectorAll('#testimoni-grid .testimonial-card');
    const grid    = document.getElementById('testimoni-grid');
    const empty   = document.getElementById('filter-empty');
    const counter = document.getElementById('filter-count');
    const btnBatal = document.getElementById('btn-batal-filter');
    const btnLebih = document.getElementById('btn-lihat-lebih-wrap');
    let visible = 0;

    resetBtnStyles();

    cards.forEach(card => {
        const val  = parseInt(card.dataset.bintang);
        const show = (bintang === 0 || val === bintang);
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    // Update counter
    if (counter) {
        counter.textContent = bintang === 0
            ? 'Menampilkan semua ' + visible + ' ulasan'
            : 'Menampilkan ' + visible + ' ulasan bintang ' + bintang;
    }

    // Kosong
    if (visible === 0) {
        grid.style.display  = 'none';
        empty.style.display = 'block';
    } else {
        grid.style.display  = '';
        empty.style.display = 'none';
    }

    // Tombol batal filter muncul
    if (btnBatal) btnBatal.style.display = 'inline-flex';
    // Sembunyikan tombol "lihat lebih banyak"
    if (btnLebih) btnLebih.style.display = 'none';

    // Highlight tombol aktif
    resetBtnStyles();
    if (bintang === 0) {
        const btn = document.getElementById('filter-btn-all');
        if (btn) { btn.style.background = 'var(--green-500)'; btn.style.color = 'white'; btn.style.borderColor = 'var(--green-500)'; }
    } else {
        const btn = document.getElementById('filter-btn-' + bintang);
        if (btn) { btn.style.background = '#fef3c7'; btn.style.color = '#92400e'; btn.style.borderColor = '#fbbf24'; }
    }
}

function batalFilter() {
    currentFilter = -1;
    const cards   = document.querySelectorAll('#testimoni-grid .testimonial-card');
    const grid    = document.getElementById('testimoni-grid');
    const empty   = document.getElementById('filter-empty');
    const counter = document.getElementById('filter-count');
    const btnBatal = document.getElementById('btn-batal-filter');
    const btnLebih = document.getElementById('btn-lihat-lebih-wrap');

    // Kembali tampilkan hanya 3 pertama
    cards.forEach((card, idx) => {
        card.style.display = idx < 3 ? '' : 'none';
    });

    empty.style.display = 'none';
    grid.style.display  = '';
    if (counter) counter.textContent = '';
    if (btnBatal) btnBatal.style.display = 'none';
    if (btnLebih && TOTAL_CARDS > 3) btnLebih.style.display = 'block';

    resetBtnStyles();
}

function lihatSemua() {
    currentFilter = 0;
    const cards   = document.querySelectorAll('#testimoni-grid .testimonial-card');
    const counter = document.getElementById('filter-count');
    const btnBatal = document.getElementById('btn-batal-filter');
    const btnLebih = document.getElementById('btn-lihat-lebih-wrap');

    cards.forEach(card => card.style.display = '');
    if (counter) counter.textContent = 'Menampilkan semua ' + cards.length + ' ulasan';
    if (btnBatal) btnBatal.style.display = 'inline-flex';
    if (btnLebih) btnLebih.style.display = 'none';

    // Highlight tombol Lihat Semua
    resetBtnStyles();
    const btnAll = document.getElementById('filter-btn-all');
    if (btnAll) { btnAll.style.background = 'var(--green-500)'; btnAll.style.color = 'white'; btnAll.style.borderColor = 'var(--green-500)'; }
}

// Bar persentase panel juga bisa klik
document.addEventListener('DOMContentLoaded', function() {
    const counter = document.getElementById('filter-count');
    if (counter) counter.textContent = '';
});
</script>

<!-- ============================================================
     TENTANG KAMI
     ============================================================ -->
<section class="section" style="background:white;" id="tentang">
  <div style="max-width:900px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:52px;align-items:center;">
    <div>
      <div class="section-label">Siapa Kami</div>
      <h2 style="font-family:var(--font-display);font-size:2rem;font-weight:400;font-style:italic;color:var(--n-900);line-height:1.2;margin-bottom:16px;letter-spacing:-0.01em;">
        Supplier Sayur Segar<br>untuk Warung Anda
      </h2>
      <p style="font-size:15px;color:var(--n-500);line-height:1.8;margin-bottom:14px;font-weight:300;">
        Azam Heri Supplier adalah usaha penyedia sayuran dan buah segar yang berfokus melayani pedagang warung, pedagang pasar, dan rumah makan di Jakarta.
      </p>
      <p style="font-size:15px;color:var(--n-500);line-height:1.8;margin-bottom:24px;font-weight:300;">
        Kami bekerja sama langsung dengan petani lokal untuk memastikan produk selalu segar, berkualitas, dan dengan harga grosir yang kompetitif.
      </p>
      <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="<?= APP_URL ?>/produk.php" class="btn btn-primary btn-sm">Lihat Produk</a>
        <?php if (!isLoggedIn()): ?>
        <a href="<?= APP_URL ?>/register.php" class="btn btn-ghost btn-sm">Daftar Gratis</a>
        <?php endif; ?>
      </div>
    </div>
    <!-- Statistik -->
    <?php
    $statProduk   = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE status='aktif'")->fetchColumn();
    $statCustomer = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
    $statPesanan  = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status='selesai'")->fetchColumn();
    $statRating   = round((float)$pdo->query("SELECT COALESCE(AVG(bintang),5) FROM rating")->fetchColumn(), 1);
    ?>
    <div style="background:var(--brand-50);border-radius:20px;padding:28px;border:1px solid var(--brand-100);">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <?php foreach ([
          [$statProduk,   'Produk Tersedia'],
          [$statCustomer, 'Warung Partner'],
          [$statPesanan,  'Pesanan Selesai'],
          [$statRating.' / 5', 'Rating Kepuasan'],
        ] as [$val, $lbl]): ?>
        <div style="text-align:center;background:white;border-radius:12px;padding:18px;border:1px solid var(--brand-100);">
          <div style="font-size:24px;font-weight:800;color:var(--brand-700);letter-spacing:-0.02em;"><?= $val ?></div>
          <div style="font-size:12px;color:var(--n-400);margin-top:4px;"><?= $lbl ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     KEUNGGULAN
     ============================================================ -->
<section class="section section-gray">
  <div style="max-width:900px;margin:0 auto;">
    <div class="section-header">
      <div class="section-label">Keunggulan</div>
      <h2 class="section-title">Kenapa Pilih Kami?</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
      <?php
      $keunggulan = [
        ['Segar Setiap Hari',  'Produk dipanen & dikirim hari yang sama langsung dari petani mitra.', 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4 M7 10l5 5 5-5 M12 15V3'],
        ['Harga Grosir',       'Harga spesial untuk pembelian di atas minimum grosir per produk.',    'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
        ['Pesan Online 24/7',  'Pesan kapan saja lewat website, barang tiba pagi hari.',              'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
        ['COD / Transfer VA',  'Bayar tunai saat barang tiba atau transfer lewat Virtual Account.',   'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z'],
        ['Produk Lengkap',     'Lebih dari 35 jenis sayuran, buah, dan rempah tersedia setiap hari.', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-8 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z'],
        ['Respon Cepat',       'Tim kami siap membalas pesan dan pertanyaan dengan cepat & ramah.',   'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'],
      ];
      foreach ($keunggulan as [$judul, $desc, $svgPath]): ?>
      <div style="background:white;border-radius:12px;padding:20px;border:1px solid var(--n-150);">
        <div style="width:38px;height:38px;background:var(--brand-50);border:1px solid var(--brand-200);border-radius:9px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--brand-600)" stroke-width="2" stroke-linecap="round"><path d="<?= $svgPath ?>"/></svg>
        </div>
        <div style="font-size:13px;font-weight:700;color:var(--n-900);margin-bottom:5px;"><?= $judul ?></div>
        <p style="font-size:12px;color:var(--n-500);line-height:1.65;font-weight:300;"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============================================================
     KONTAK & MAPS
     ============================================================ -->
<section class="section" style="background:white;" id="kontak">
  <div style="max-width:900px;margin:0 auto;">
    <div class="section-header">
      <div class="section-label">Kontak & Lokasi</div>
      <h2 class="section-title">Hubungi & Temukan Kami</h2>
      <p class="section-subtitle">Hubungi lewat WhatsApp untuk respon cepat, atau kunjungi langsung lokasi kami.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
      <!-- Info kontak -->
      <div style="display:flex;flex-direction:column;gap:12px;">
        <?php
        $noHpIdx  = $pdo->query("SELECT nilai FROM pengaturan WHERE kunci='no_hp'")->fetchColumn() ?: '0812-3456-7890';
        $jamIdx   = $pdo->query("SELECT nilai FROM pengaturan WHERE kunci='jam_operasional'")->fetchColumn() ?: 'Senin–Sabtu, 04.00–20.00 WIB';
        $waNum    = preg_replace('/[^0-9]/', '', $noHpIdx);
        if (substr($waNum,0,1)==='0') $waNum='62'.substr($waNum,1);
        $waLinkIdx= "https://wa.me/{$waNum}?text=Halo%2C%20saya%20ingin%20bertanya%20tentang%20produk%20sayuran.";
        ?>
        <!-- WA -->
        <a href="<?= $waLinkIdx ?>" target="_blank" rel="noopener noreferrer"
           style="display:flex;align-items:center;gap:14px;background:var(--brand-50);border:1.5px solid var(--brand-200);border-radius:12px;padding:16px;text-decoration:none;transition:all 0.15s;"
           onmouseover="this.style.background='var(--brand-100)'" onmouseout="this.style.background='var(--brand-50)'">
          <div style="width:42px;height:42px;background:var(--brand-600);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          </div>
          <div>
            <div style="font-size:11px;font-weight:700;color:var(--brand-600);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">WhatsApp</div>
            <div style="font-size:15px;font-weight:700;color:var(--brand-800);"><?= sanitize($noHpIdx) ?></div>
            <div style="font-size:12px;color:var(--n-400);">Respon cepat 04.00–20.00 WIB</div>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--brand-400)" stroke-width="2" style="margin-left:auto;"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
        <!-- Alamat -->
        <div style="display:flex;align-items:flex-start;gap:14px;background:var(--n-50);border:1px solid var(--n-150);border-radius:12px;padding:16px;">
          <div style="width:42px;height:42px;background:var(--n-800);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div>
            <div style="font-size:11px;font-weight:700;color:var(--n-500);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">Alamat</div>
            <div style="font-size:14px;font-weight:600;color:var(--n-800);line-height:1.5;"><?= ALAMAT_CONST ?></div>
            <div style="font-size:12px;color:var(--n-400);margin-top:3px;">Pengiriman ke area Jabodetabek</div>
          </div>
        </div>
        <!-- Jam Operasional -->
        <div style="display:flex;align-items:center;gap:14px;background:var(--n-50);border:1px solid var(--n-150);border-radius:12px;padding:16px;">
          <div style="width:42px;height:42px;background:var(--n-800);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div>
            <div style="font-size:11px;font-weight:700;color:var(--n-500);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:3px;">Jam Operasional</div>
            <div style="font-size:14px;font-weight:600;color:var(--n-800);"><?= sanitize($jamIdx) ?></div>
            <div style="font-size:12px;color:var(--n-400);margin-top:3px;">Termasuk hari Sabtu</div>
          </div>
        </div>
      </div>
      <!-- Maps -->
      <div style="border-radius:16px;overflow:hidden;border:1px solid var(--n-200);box-shadow:var(--shadow-md);height:100%;min-height:280px;">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.3!2d106.7759614!3d-6.1956065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTEnNDQuMiJTIDEwNsKwNDYnMzMuNSJF!5e0!3m2!1sid!2sid!4v1700000000002!5m2!1sid!2sid"
          width="100%" height="100%" style="border:0;display:block;min-height:280px;"
          allowfullscreen loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>

    <!-- Link buka maps -->
    <div style="text-align:center;">
      <a href="https://www.google.com/maps?q=-6.1956065,106.7759614" target="_blank" rel="noopener noreferrer"
         style="display:inline-flex;align-items:center;gap:7px;font-size:13px;color:var(--brand-600);font-weight:600;text-decoration:none;padding:9px 20px;border:1px solid var(--brand-200);border-radius:99px;background:var(--brand-50);transition:all 0.15s;"
         onmouseover="this.style.background='var(--brand-100)'" onmouseout="this.style.background='var(--brand-50)'">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Buka di Google Maps
      </a>
    </div>
  </div>
</section>


<!-- CTA -->
<section class="cta-section">
<div style="margin-bottom:16px;opacity:0.85;"><svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg></div>
<h2>Siap Mulai Pemesanan?</h2>
<p>Daftarkan warung Anda dan nikmati harga grosir terbaik dengan pengiriman pagi hari langsung ke lokasi Anda.</p>
<a class="btn btn-primary" href="<?= isLoggedIn() ? APP_URL.'/produk.php' : APP_URL.'/register.php' ?>" style="font-size:16px;padding:16px 40px;">
  <?= isLoggedIn() ? '<span class="icon-btn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></span> Pesan Sekarang' : 'Daftar Gratis Sekarang' ?>
</a>
</section>

</div><!-- end page-wrapper -->



<?php include __DIR__ . '/includes/footer.php'; ?>
