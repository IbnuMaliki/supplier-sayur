<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Produk';

$katSlug = $_GET['kategori'] ?? 'all';
$search  = trim($_GET['q'] ?? '');

$sql    = "SELECT p.*, k.nama AS kategori_nama, k.slug AS kategori_slug FROM produk p LEFT JOIN kategori k ON p.kategori_id=k.id WHERE p.status='aktif'";
$params = [];
if ($katSlug !== 'all') { $sql .= " AND k.slug=?"; $params[] = $katSlug; }
if ($search)            { $sql .= " AND p.nama LIKE ?"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produkList = $stmt->fetchAll();

$kategori = $pdo->query("SELECT * FROM kategori ORDER BY id")->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper">

<div class="page-header">
<div class="breadcrumb"><a href="<?= APP_URL ?>">Home</a> <span>›</span> <span>Produk</span></div>
<div class="page-header-title"> Katalog Produk</div>
<div class="page-header-sub">Sayuran segar pilihan tersedia setiap hari</div>
</div>

<section class="section">
<form method="GET" style="margin-bottom:24px;display:flex;gap:12px;max-width:520px;">
  <input class="form-input" type="text" name="q" placeholder=" Cari produk sayuran..." value="<?= sanitize($search) ?>" style="flex:1;">
  <?php if ($katSlug !== 'all'): ?><input type="hidden" name="kategori" value="<?= sanitize($katSlug) ?>"><?php endif; ?>
  <button class="btn btn-secondary" type="submit">Cari</button>
</form>

<?php
function kategoriIcon($slug) {
    $icons = [
        'sayuran-hijau' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 22c1.25-1.25 2.5-2.5 3.5-4 1-1.5 1.5-3 1.5-5a5 5 0 0 1 10 0c0 2-.5 3.5-1.5 5-1 1.5-2.25 2.75-3.5 4"/><path d="M12 22V12"/><path d="M7 12c0-3.31 2.24-6 5-6"/></svg>',
        'bumbu'         => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2a5 5 0 0 1 5 5c0 3-2 5.5-5 8-3-2.5-5-5-5-8a5 5 0 0 1 5-5z"/><line x1="12" y1="15" x2="12" y2="22"/></svg>',
        'umbi-akar'     => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><ellipse cx="12" cy="14" rx="5" ry="7"/><line x1="12" y1="7" x2="12" y2="3"/><line x1="9" y1="5" x2="12" y2="3"/><line x1="15" y1="5" x2="12" y2="3"/></svg>',
        'buah-sayur'    => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="14" r="7"/><path d="M12 7V3"/><path d="M9 5c0-1.5 3-3 3-3s3 1.5 3 3"/></svg>',
        'buah-segar'    => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="13" r="8"/><path d="M12 5V2"/><path d="M10 3c0-1 4-2 4 0"/></svg>',
        'rempah'        => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 22V8"/><path d="M5 12c0-4 3.5-7 7-7s7 3 7 7"/><path d="M8 17c0-2.5 1.8-4 4-4s4 1.5 4 4"/></svg>',
    ];
    return $icons[$slug] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
}
?>
<div class="filter-bar">
  <a class="filter-btn <?= $katSlug==='all'?'active':'' ?>" href="<?= APP_URL ?>/produk.php<?= $search?'?q='.urlencode($search):'' ?>">Semua</a>
  <?php foreach ($kategori as $k): ?>
  <a class="filter-btn <?= $katSlug===$k['slug']?'active':'' ?>"
       href="<?= APP_URL ?>/produk.php?kategori=<?= sanitize($k['slug']) ?><?= $search?'&q='.urlencode($search):'' ?>">
    <?= kategoriIcon($k['slug'] ?? '') ?> <?= sanitize($k['nama']) ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if (count($produkList) === 0): ?>
<div class="empty-state">
  <div class="empty-icon"></div>
  <div class="empty-title">Produk tidak ditemukan</div>
  <div class="empty-sub">Coba kata kunci lain atau pilih kategori berbeda</div>
  <a class="btn btn-secondary" href="<?= APP_URL ?>/produk.php"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Lihat Semua Produk</a>
</div>
<?php else: ?>
<div class="products-grid">
  <?php foreach ($produkList as $p): ?>
  <div class="product-card" data-cat="<?= sanitize($p['kategori_slug'] ?? '') ?>">
    <div class="product-img-wrap">
      <img src="<?= getProductImage($p['nama'], 400, $p['gambar'] ?? null) ?>" alt="<?= sanitize($p['nama']) ?>" loading="lazy">
      <div class="product-badge <?= $p['stok']==0?'out':'' ?>">
        <?= $p['stok']==0 ? 'Stok Habis' : sanitize($p['badge'] ?? 'Segar') ?>
      </div>
    </div>
    <div class="product-info">
      <div class="product-name"><?= sanitize($p['nama']) ?></div>
      <div class="product-desc"><?= sanitize($p['deskripsi'] ?? '') ?></div>
      <div class="product-price"><?= formatRupiah($p['harga']) ?> <span class="product-price-label">/<?= sanitize($p['satuan'] ?? 'kg') ?></span></div>
      <div class="product-grosir">Grosir: <?= formatRupiah($p['harga_grosir']) ?>/<?= sanitize($p['satuan'] ?? 'kg') ?> (min.<?= (int)$p['min_grosir'] ?>kg)</div>
      <div style="font-size:12px;color:var(--slate-400);margin-top:4px;">
        Stok: <?php if ($p['stok'] > 0): ?>
          <span style="color:var(--green-600);font-weight:600;"><?= (int)$p['stok'] ?> kg tersedia</span>
        <?php else: ?>
          <span style="color:var(--red-500);font-weight:600;">Habis</span>
        <?php endif; ?>
      </div>
      <div class="product-actions">
        <?php if ($p['stok'] > 0): ?>
          <?php if (isAdmin()): ?>
            <button class="btn-cart" disabled
              style="flex:1;cursor:not-allowed;background:var(--slate-200);color:var(--slate-500);font-size:11px;border-radius:var(--radius-sm);padding:10px;border:none;font-family:var(--font-body);"
              title="Admin tidak dapat memesan produk">
              Admin tidak dapat memesan
            </button>
          <?php elseif (isLoggedIn()): ?>
            <form method="POST" action="<?= APP_URL ?>/ajax/keranjang.php" style="flex:1;">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="produk_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="redirect" value="<?= APP_URL ?>/produk.php">
              <button type="submit" class="btn-cart" style="width:100%;">+ Keranjang</button>
            </form>
          <?php else: ?>
            <a href="#" onclick="showLoginModal('keranjang');return false;" class="btn-cart" style="flex:1;display:block;text-align:center;">+ Keranjang</a>
          <?php endif; ?>
        <?php else: ?>
          <button class="btn-cart" disabled style="flex:1;cursor:not-allowed;">Stok Habis</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</section>

</div>


<?php include __DIR__ . '/includes/footer.php'; ?>
