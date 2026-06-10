<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle = 'Keranjang';

$userId = $_SESSION['user_id'];

// Ambil keranjang
$stmt = $pdo->prepare("
    SELECT k.id, k.jumlah, p.id as produk_id, p.nama, p.harga, p.harga_grosir, p.min_grosir, p.stok, p.satuan, p.gambar
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    WHERE k.user_id = ?
    ORDER BY k.id
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $harga = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
    $total += $harga * $item['jumlah'];
}


include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper">

<div class="page-header">
<div class="breadcrumb"><a href="<?= APP_URL ?>">Home</a> › <span>Keranjang</span></div>
<div class="page-header-title"> Keranjang Belanja</div>
<div class="page-header-sub"><?= count($items) ?> produk dalam keranjang</div>
</div>

<div style="max-width:1000px; margin:0 auto; padding:36px 2rem;">
<?php if (count($items) === 0): ?>
<div class="empty-state">
  <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
  <div class="empty-title">Keranjang masih kosong</div>
  <div class="empty-sub">Yuk tambahkan sayuran segar ke keranjang Anda!</div>
  <a class="btn btn-secondary" href="produk.php">Belanja Sekarang</a>
</div>
<?php else: ?>

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start;">
  <!-- Tabel Keranjang -->
  <div class="table-wrap">
    <div class="table-header">
      <div class="table-title">Daftar Produk</div>
      <a href="produk.php" style="font-size:13px; color:var(--green-600); font-weight:600;">+ Tambah Produk</a>
    </div>
    <table class="cart-table">
      <thead>
        <tr>
          <th colspan="2">Produk</th>
          <th>Harga</th>
          <th>Jumlah</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item):
            $harga = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
            $sub   = $harga * $item['jumlah'];
            $img   = getProductImage($item['nama'], 100, $item['gambar'] ?? null);
          ?>
        <tr>
          <td style="width:70px;"><img class="cart-product-img" src="<?= $img ?>" alt="<?= sanitize($item['nama']) ?>"></td>
          <td>
            <div style="font-size:14px; font-weight:600; color:var(--slate-800);"><?= sanitize($item['nama']) ?></div>
            <?php if ($item['jumlah'] >= $item['min_grosir']): ?>
            <div style="font-size:11px; background:var(--green-100); color:var(--green-700); padding:2px 8px; border-radius:100px; display:inline-block; margin-top:4px; font-weight:700;">Harga Grosir</div>
            <?php endif; ?>
          </td>
          <td style="font-size:14px; color:var(--green-700); font-weight:600; white-space:nowrap;"><?= formatRupiah($harga) ?>/<?= $item['satuan'] ?></td>
          <td>
            <form method="POST" action="<?= APP_URL ?>/ajax/keranjang.php" style="display:inline;">
              <input type="hidden" name="action" value="set">
              <input type="hidden" name="produk_id" value="<?= $item['produk_id'] ?>">
              <input type="hidden" name="redirect" value="<?= APP_URL ?>/keranjang.php">
              <div class="qty-wrap">
                <button type="button" class="qty-btn" onclick="stepQty(this.closest('.qty-wrap').querySelector('.qty-input'), -1)">−</button>
                <input class="qty-input" type="number" name="jumlah" value="<?= $item['jumlah'] ?>" min="1" max="<?= $item['stok'] ?>" readonly>
                <button type="button" class="qty-btn" onclick="stepQty(this.closest('.qty-wrap').querySelector('.qty-input'), 1)">+</button>
              </div>
            </form>
          </td>
          <td style="font-size:15px; font-weight:700; color:var(--slate-800);"><?= formatRupiah($sub) ?></td>
          <td>
            <form method="POST" action="<?= APP_URL ?>/ajax/keranjang.php">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="produk_id" value="<?= $item['produk_id'] ?>">
              <input type="hidden" name="redirect" value="<?= APP_URL ?>/keranjang.php">
              <button type="submit"
                style="background:var(--red-100);border:none;color:var(--red-500);cursor:pointer;width:34px;height:34px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;transition:all 0.2s;"
                title="Hapus dari keranjang"
                onmouseover="this.style.background='var(--red-500)';this.style.color='white';"
                onmouseout="this.style.background='var(--red-100)';this.style.color='var(--red-500)';"
                onclick="return confirm('Hapus <?= addslashes(sanitize($item['nama'])) ?> dari keranjang?')">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                  <path d="M10 11v6"/><path d="M14 11v6"/>
                  <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Ringkasan -->
  <div style="background:white; border-radius:var(--radius-lg); border:1px solid var(--slate-100); padding:24px; position:sticky; top:90px;">
    <div style="font-size:16px; font-weight:700; color:var(--slate-800); margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--slate-100);">Ringkasan Pesanan</div>

    <?php foreach ($items as $item):
        $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
      ?>
    <div class="summary-item">
      <span><?= sanitize($item['nama']) ?> ×<?= $item['jumlah'] ?>kg</span>
      <span><?= formatRupiah($h * $item['jumlah']) ?></span>
    </div>
    <?php endforeach; ?>

    <div class="summary-item">
      <span style="color:var(--slate-500);">Ongkos Kirim</span>
      <span style="color:var(--green-600); font-weight:600;">GRATIS</span>
    </div>
    <div class="summary-total">
      <span>Total</span>
      <span style="color:var(--green-700);"><?= formatRupiah($total) ?></span>
    </div>

    <a class="btn btn-secondary btn-full" href="checkout.php" style="margin-top:20px;">Lanjut Checkout →</a>
    <a class="btn btn-outline-green btn-full" href="produk.php" style="margin-top:10px;">← Lanjut Belanja</a>

    <div style="margin-top:16px; padding:12px; background:var(--green-50); border-radius:var(--radius-sm); font-size:12px; color:var(--slate-500);">
       Harga grosir berlaku untuk pembelian minimum per jenis sayuran
      </div>
  </div>
</div>

<?php endif; ?>
</div>

</div>
<script>
function stepQty(input, delta) {
  var val  = parseInt(input.value) || 1;
  var min  = parseInt(input.min)   || 1;
  var max  = parseInt(input.max)   || 9999;
  var next = Math.min(Math.max(val + delta, min), max);
  if (next === val) return; // sudah di batas, tidak perlu submit
  input.value = next;
  input.closest('form').submit();
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
