<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Tentang & Kontak';
include __DIR__ . '/includes/header.php';

$totalProduk  = (int)$pdo->query("SELECT COUNT(*) FROM produk WHERE status='aktif'")->fetchColumn();
$totalCustomer= (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalPesanan = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status='selesai'")->fetchColumn();
$avgRating    = round((float)$pdo->query("SELECT COALESCE(AVG(bintang),5) FROM rating")->fetchColumn(), 1);

function getSetting2($pdo, $key, $default='') {
    $r = $pdo->prepare("SELECT nilai FROM pengaturan WHERE kunci=?");
    $r->execute([$key]);
    $v = $r->fetchColumn();
    return $v !== false ? $v : $default;
}
$noHp      = getSetting2($pdo, 'no_hp', '0812-3456-7890');
$jamOps    = getSetting2($pdo, 'jam_operasional', 'Senin–Sabtu, 04.00–20.00 WIB');
$alamat    = getSetting2($pdo, 'alamat', 'Jl. H. Salihun No. 33, RT03, RW03, Kebon Jeruk, Jakarta Barat, DKI Jakarta');
$emailUsaha= getSetting2($pdo, 'email_usaha', 'info@suppliersayur.com');
$waNum     = preg_replace('/[^0-9]/', '', $noHp);
if (substr($waNum, 0, 1) === '0') $waNum = '62' . substr($waNum, 1);
$waLink    = "https://wa.me/{$waNum}?text=Halo%2C%20saya%20ingin%20bertanya%20tentang%20produk%20sayuran%20Anda.";
?>

<div class="page-wrapper">

<!-- Page Header -->
<div class="page-header">
  <div style="max-width:860px;margin:0 auto;">
    <div class="breadcrumb"><a href="<?= APP_URL ?>">Home</a> <span>›</span> <span>Tentang & Kontak</span></div>
    <div class="page-header-title">Tentang & Kontak</div>
    <div class="page-header-sub">Kenali kami dan hubungi langsung untuk kebutuhan Anda</div>
  </div>
</div>

<!-- Tentang Kami -->
<section class="section" style="background:white;">
  <div style="max-width:900px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:52px;align-items:center;">
    <div>
      <div class="section-label">Siapa Kami</div>
      <h2 style="font-family:var(--font-display);font-size:2rem;font-weight:400;font-style:italic;color:var(--n-900);line-height:1.2;margin-bottom:16px;letter-spacing:-0.01em;">
        Supplier Sayur Segar<br>untuk Warung Anda
      </h2>
      <p style="font-size:15px;color:var(--n-500);line-height:1.8;margin-bottom:14px;font-weight:300;">
        Azam Heri Supplier adalah usaha penyedia sayuran dan buah segar yang berfokus melayani pedagang warung, pedagang pasar, dan rumah makan.
      </p>
      <p style="font-size:15px;color:var(--n-500);line-height:1.8;font-weight:300;">
        Kami bekerja sama langsung dengan petani lokal untuk memastikan produk selalu segar, berkualitas, dan dengan harga grosir yang kompetitif.
      </p>
    </div>
    <!-- Statistik -->
    <div style="background:var(--brand-50);border-radius:20px;padding:28px;border:1px solid var(--brand-100);">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <?php foreach ([
          [$totalProduk, 'Produk Tersedia'],
          [$totalCustomer, 'Warung Partner'],
          [$totalPesanan, 'Pesanan Selesai'],
          [$avgRating . ' / 5', 'Rating Kepuasan'],
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

<!-- Keunggulan -->
<section class="section section-gray">
  <div style="max-width:900px;margin:0 auto;">
    <div class="section-header">
      <div class="section-label">Keunggulan</div>
      <h2 class="section-title">Kenapa Pilih Kami?</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
      <?php
      $keunggulan = [
        ['Segar Setiap Hari',  'Produk dipanen dan dikirim hari yang sama langsung dari kebun petani mitra.',     'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4 M7 10l5 5 5-5 M12 15V3'],
        ['Harga Grosir',       'Dapatkan harga spesial untuk pembelian di atas minimum grosir.',                  'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
        ['Pesan Online 24/7',  'Sistem pemesanan online mudah. Pesan kapan saja, barang tiba pagi hari.',         'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z'],
        ['COD / Transfer VA',  'Bayar tunai saat barang tiba atau transfer lewat Virtual Account.',               'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z'],
        ['Produk Lengkap',     'Lebih dari 35 jenis sayuran, buah, dan rempah tersedia setiap hari.',             'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.3 2.3c-.6.6-.2 1.7.7 1.7H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-8 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z'],
        ['Respon Cepat',       'Tim kami siap membalas chat dan pertanyaan Anda dengan cepat dan ramah.',         'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'],
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

<!-- Kontak -->
<section class="section" style="background:white;">
  <div style="max-width:900px;margin:0 auto;">
    <div class="section-header">
      <div class="section-label">Hubungi Kami</div>
      <h2 class="section-title">Ada Pertanyaan?</h2>
      <p class="section-subtitle">Hubungi kami lewat WhatsApp untuk respon cepat, atau gunakan fitur Chat jika sudah punya akun.</p>
    </div>

    <!-- Banner WA + Chat -->
    <div style="background:linear-gradient(135deg,var(--brand-700),var(--brand-500));border-radius:16px;padding:28px 32px;margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;">
      <div>
        <div style="font-size:17px;font-weight:800;color:white;margin-bottom:6px;">Chat Langsung dengan Kami</div>
        <div style="font-size:13px;color:rgba(255,255,255,0.8);line-height:1.6;">
          Respon cepat lewat <strong style="color:white;">WhatsApp</strong> atau gunakan <strong style="color:white;">Chat</strong> di website.
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="<?= $waLink ?>" target="_blank" rel="noopener noreferrer"
           style="display:inline-flex;align-items:center;gap:8px;background:white;color:#15803d;padding:10px 20px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#15803d"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          Chat WhatsApp
        </a>
        <?php if (isLoggedIn() && !isAdmin()): ?>
        <a href="<?= APP_URL ?>/chat.php"
           style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);color:white;border:1.5px solid rgba(255,255,255,0.4);padding:10px 20px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Chat di Website
        </a>
        <?php else: ?>
        <a href="#" onclick="showLoginModal('chat');return false;"
           style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);color:white;border:1.5px solid rgba(255,255,255,0.4);padding:10px 20px;border-radius:100px;font-size:13px;font-weight:700;text-decoration:none;">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Chat di Website
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Info Kontak Grid -->
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;">
      <?php
      $kontakInfo = [
        ['WhatsApp', $noHp, 'Respon cepat 04.00–20.00 WIB', 'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z', $waLink],
        ['Email', $emailUsaha, 'Balasan dalam 1x24 jam', 'M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z M22 6l-10 7L2 6', 'mailto:'.$emailUsaha],
        ['Alamat', $alamat, 'Pengiriman ke area Jabodetabek', 'M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z M12 10m-3 0a3 3 0 1 0 6 0 3 3 0 0 0-6 0', null],
        ['Jam Operasional', $jamOps, 'Termasuk hari Sabtu', 'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z M12 6v6l4 2', null],
      ];
      foreach ($kontakInfo as [$judul, $nilai, $sub, $svgPath, $link]): ?>
      <div style="background:var(--n-50);border-radius:12px;padding:20px;border:1px solid var(--n-150);display:flex;align-items:flex-start;gap:14px;">
        <div style="width:40px;height:40px;background:var(--brand-600);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"><path d="<?= $svgPath ?>"/></svg>
        </div>
        <div>
          <div style="font-size:11px;font-weight:700;color:var(--n-400);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;"><?= $judul ?></div>
          <?php if ($link): ?>
          <a href="<?= $link ?>" target="_blank" style="font-size:15px;font-weight:700;color:var(--brand-700);text-decoration:none;display:block;"><?= sanitize($nilai) ?></a>
          <?php else: ?>
          <div style="font-size:14px;font-weight:600;color:var(--n-800);"><?= sanitize($nilai) ?></div>
          <?php endif; ?>
          <div style="font-size:12px;color:var(--n-400);margin-top:3px;"><?= $sub ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

</div>


<!-- Maps Section -->
<section style="background:var(--n-50);padding:48px 4rem;">
  <div style="max-width:900px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:20px;">
      <div class="section-label">Lokasi</div>
      <h2 class="section-title">Temukan Kami</h2>
    </div>
    <div style="border-radius:16px;overflow:hidden;border:1px solid var(--n-200);box-shadow:var(--shadow-md);">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.3!2d106.7759614!3d-6.1956065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTEnNDQuMiJTIDEwNsKwNDYnMzMuNSJF!5e0!3m2!1sid!2sid!4v1700000000001!5m2!1sid!2sid"
        width="100%" height="400" style="border:0;display:block;"
        allowfullscreen loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </div>
    <div style="display:flex;align-items:center;gap:10px;margin-top:14px;padding:14px 18px;background:white;border-radius:10px;border:1px solid var(--n-150);">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--brand-600)" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <span style="font-size:13px;color:var(--n-600);flex:1;">Jl. H. Salihun No. 33, RT03, RW03, Kebon Jeruk, Jakarta Barat, DKI Jakarta</span>
      <a href="https://www.google.com/maps?q=-6.1956065,106.7759614" target="_blank" rel="noopener noreferrer"
         style="font-size:13px;color:var(--brand-600);font-weight:600;text-decoration:none;white-space:nowrap;display:flex;align-items:center;gap:5px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Buka di Google Maps
      </a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
