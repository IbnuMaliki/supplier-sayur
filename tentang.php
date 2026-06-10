<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Tentang Kami';
include __DIR__ . '/includes/header.php';

// Ambil statistik
$totalProduk  = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE status='aktif'")->fetchColumn();
$totalCustomer= (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalPesanan = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status='selesai'")->fetchColumn();
$avgRating    = round((float)$pdo->query("SELECT COALESCE(AVG(bintang),5) FROM rating")->fetchColumn(), 1);
?>

<div class="page-wrapper">

<!-- Page Header -->
<div class="page-header">
  <div style="max-width:800px;margin:0 auto;">
    <div class="breadcrumb"><a href="<?= APP_URL ?>">Home</a> <span>›</span> <span>Tentang Kami</span></div>
    <div class="page-header-title">Tentang Kami</div>
    <div class="page-header-sub">Kenali lebih dekat Supplier Sayur Azam Heri</div>
  </div>
</div>

<!-- Hero About -->
<section class="section" style="background:white;">
  <div style="max-width:860px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:52px;align-items:center;">
    <div>
      <div class="section-label">Siapa Kami</div>
      <h2 style="font-family:var(--font-display);font-size:2rem;font-weight:400;font-style:italic;color:var(--n-900);line-height:1.2;margin-bottom:16px;letter-spacing:-0.01em;">
        Supplier Sayur Segar<br>untuk Warung Anda
      </h2>
      <p style="font-size:15px;color:var(--n-500);line-height:1.8;margin-bottom:16px;font-weight:300;">
        Azam Heri Supplier adalah usaha penyedia sayuran dan buah segar yang berfokus melayani pedagang warung, pedagang pasar, dan rumah makan di sekitar Jabodetabek.
      </p>
      <p style="font-size:15px;color:var(--n-500);line-height:1.8;font-weight:300;">
        Kami bekerja sama langsung dengan petani lokal untuk memastikan produk yang sampai ke tangan Anda selalu segar, berkualitas, dan dengan harga yang kompetitif — terutama harga grosir untuk pembelian dalam jumlah besar.
      </p>
    </div>
    <div style="background:var(--brand-50);border-radius:20px;padding:36px;border:1px solid var(--brand-100);">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <?php foreach ([
          [$totalProduk, 'Produk Tersedia', '🥬'],
          [$totalCustomer, 'Warung Partner', '🏪'],
          [$totalPesanan, 'Pesanan Selesai', '📦'],
          [$avgRating . '★', 'Rating Kepuasan', '⭐'],
        ] as [$val, $lbl, $ico]): ?>
        <div style="text-align:center;background:white;border-radius:12px;padding:20px;border:1px solid var(--brand-100);">
          <div style="font-size:28px;margin-bottom:6px;"><?= $ico ?></div>
          <div style="font-size:24px;font-weight:800;color:var(--brand-700);letter-spacing:-0.02em;"><?= $val ?></div>
          <div style="font-size:12px;color:var(--n-400);margin-top:4px;"><?= $lbl ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Visi Misi -->
<section class="section section-gray">
  <div style="max-width:860px;margin:0 auto;">
    <div class="section-header">
      <div class="section-label">Visi & Misi</div>
      <h2 class="section-title">Komitmen Kami</h2>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <div style="background:white;border-radius:16px;padding:28px;border:1px solid var(--n-150);">
        <div style="width:44px;height:44px;background:var(--brand-600);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <div style="font-size:16px;font-weight:700;color:var(--n-900);margin-bottom:10px;">Visi</div>
        <p style="font-size:14px;color:var(--n-500);line-height:1.75;font-weight:300;">Menjadi supplier sayuran dan buah segar terpercaya yang mendukung keberlangsungan usaha warung dan pedagang lokal di Indonesia.</p>
      </div>
      <div style="background:white;border-radius:16px;padding:28px;border:1px solid var(--n-150);">
        <div style="width:44px;height:44px;background:var(--brand-600);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div style="font-size:16px;font-weight:700;color:var(--n-900);margin-bottom:10px;">Misi</div>
        <ul style="font-size:14px;color:var(--n-500);line-height:2;font-weight:300;padding-left:16px;">
          <li>Menyediakan produk segar langsung dari petani</li>
          <li>Pengiriman pagi hari sebelum warung buka</li>
          <li>Harga grosir yang transparan dan kompetitif</li>
          <li>Pelayanan responsif dan ramah</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Keunggulan -->
<section class="section" style="background:white;">
  <div style="max-width:860px;margin:0 auto;">
    <div class="section-header">
      <div class="section-label">Keunggulan</div>
      <h2 class="section-title">Kenapa Pilih Kami?</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
      <?php
      $keunggulan = [
        ['Segar Setiap Hari', 'Produk dipanen dan dikirim hari yang sama langsung dari kebun petani mitra kami.', 'M8 7H3a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h13a1 1 0 0 0 1-1v-2M8 7V5a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v3M8 7h8m0 0h3a1 1 0 0 0 1 1v3'],
        ['Harga Grosir', 'Dapatkan harga spesial untuk pembelian di atas minimum grosir. Semakin banyak semakin hemat.', 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
        ['Pesan Online 24/7', 'Sistem pemesanan online yang mudah. Pesan kapan saja, barang tiba pagi hari.', 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
        ['COD / Transfer', 'Tersedia metode bayar COD dan Virtual Account sesuai kenyamanan Anda.', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z'],
        ['Produk Lengkap', 'Lebih dari 35 jenis sayuran, buah, dan rempah tersedia setiap hari.', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-8 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z'],
        ['Rating Terpercaya', 'Sistem ulasan transparan dari pelanggan nyata. Kepuasan Anda adalah prioritas kami.', 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
      ];
      foreach ($keunggulan as [$judul, $desc, $svgPath]): ?>
      <div style="background:var(--n-50);border-radius:14px;padding:22px;border:1px solid var(--n-150);">
        <div style="width:40px;height:40px;background:var(--brand-50);border:1px solid var(--brand-200);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand-600)" stroke-width="2"><path d="<?= $svgPath ?>"/></svg>
        </div>
        <div style="font-size:14px;font-weight:700;color:var(--n-900);margin-bottom:6px;"><?= $judul ?></div>
        <p style="font-size:13px;color:var(--n-500);line-height:1.65;font-weight:300;"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Kontak CTA -->
<section class="cta-section">
  <div style="position:relative;z-index:1;">
    <h2>Siap Bermitra dengan Kami?</h2>
    <p>Daftarkan warung Anda sekarang dan nikmati kemudahan pemesanan sayuran segar setiap hari.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <?php if (!isLoggedIn()): ?>
      <a href="<?= APP_URL ?>/register.php" class="btn btn-primary" style="font-size:15px;padding:13px 32px;">Daftar Gratis</a>
      <a href="<?= APP_URL ?>/kontak.php" class="btn btn-outline" style="font-size:15px;padding:13px 32px;">Hubungi Kami</a>
      <?php else: ?>
      <a href="<?= APP_URL ?>/produk.php" class="btn btn-primary" style="font-size:15px;padding:13px 32px;">Lihat Produk</a>
      <a href="<?= APP_URL ?>/kontak.php" class="btn btn-outline" style="font-size:15px;padding:13px 32px;">Hubungi Kami</a>
      <?php endif; ?>
    </div>
  </div>
</section>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
