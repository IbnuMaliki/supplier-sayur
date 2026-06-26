<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle = 'Checkout';
$userId = $_SESSION['user_id'];

// Ambil data keranjang
$stmt = $pdo->prepare("
    SELECT k.jumlah, p.id as produk_id, p.nama, p.harga, p.harga_grosir, p.min_grosir, p.stok, p.satuan
    FROM keranjang k JOIN produk p ON k.produk_id=p.id
    WHERE k.user_id=?
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

if (count($items) === 0) {
    flashMessage('error', 'Keranjang kosong. Silakan tambah produk terlebih dahulu.');
    redirect(APP_URL . '/produk.php');
}

// Ambil data user
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

// Hitung total
$total = 0;
foreach ($items as $item) {
    $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
    $total += $h * $item['jumlah'];
}
$va = generateVirtualAccount();

// Proses checkout
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $hp         = trim($_POST['no_hp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $catatan    = trim($_POST['catatan'] ?? '');
    $metodeTipe = trim($_POST['metode_tipe'] ?? 'va');
    $metodeBank = trim($_POST['metode_bank'] ?? 'mandiri');

    // Bentuk label metode pembayaran yang disimpan
    $namaBank = [
        'mandiri'=>'Bank Mandiri','bca'=>'Bank BCA','bni'=>'Bank BNI',
        'bri'=>'Bank BRI','bsi'=>'Bank BSI',
    ];
    if ($metodeTipe === 'cash') {
        $labelMetode = 'Bayar di Tempat (COD)';
    } else {
        $labelMetode = 'Virtual Account ' . ($namaBank[$metodeBank] ?? 'Mandiri');
    }

    if (!$nama || !$hp || !$alamat) {
        $error = 'Nama, no HP, dan alamat wajib diisi.';
    } else {
        try {
            $pdo->beginTransaction();
            $kode  = generateKodePesanan();
            $vaNum = ($metodeTipe === 'va') ? generateVirtualAccount() : null;

            // Buat pesanan — simpan metode_bayar lengkap
            $stmt = $pdo->prepare("INSERT INTO pesanan (kode_pesanan,user_id,nama_penerima,no_hp,alamat_pengiriman,catatan,total_harga,metode_bayar,nomor_va) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$kode, $userId, $nama, $hp, $alamat, $catatan, $total, $labelMetode, $vaNum]);
            $pesananId = $pdo->lastInsertId();

            // Detail pesanan
            foreach ($items as $item) {
                $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
                $sub = $h * $item['jumlah'];
                $stmt = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id,produk_id,nama_produk,harga_satuan,jumlah,subtotal) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$pesananId, $item['produk_id'], $item['nama'], $h, $item['jumlah'], $sub]);
                // Kurangi stok
                $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id=?")->execute([$item['jumlah'], $item['produk_id']]);
            }

            // Kosongkan keranjang
            $pdo->prepare("DELETE FROM keranjang WHERE user_id=?")->execute([$userId]);

            $pdo->commit();
            $pesanSukses = ($metodeTipe === 'cash')
                ? "Pesanan $kode berhasil dibuat! Siapkan uang tunai saat barang tiba."
                : "Pesanan $kode berhasil dibuat! Segera lakukan pembayaran.";
            flashMessage('success', $pesanSukses);
            redirect(APP_URL . '/pesanan.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Gagal memproses pesanan. Coba lagi.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
<div class="breadcrumb"><a href="<?= APP_URL ?>">Home</a> › <a href="keranjang.php">Keranjang</a> › <span>Checkout</span></div>
<div class="page-header-title"> Checkout</div>
<div class="page-header-sub">Lengkapi informasi pengiriman Anda</div>
</div>

<?php if ($error): ?>
<div style="max-width:1000px;margin:20px auto 0;padding:0 2rem;">
<div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:var(--radius-sm);font-size:14px;font-weight:600;"> <?= sanitize($error) ?></div>
</div>
<?php endif; ?>

<form method="POST">
<div class="checkout-grid">
<!-- Kiri: Form -->
<div>
  <div class="checkout-section">
    <div class="checkout-section-title"> Informasi Pengiriman</div>
    <div class="form-group">
      <label class="form-label">Nama Penerima</label>
      <input class="form-input" type="text" name="nama" value="<?= sanitize($_POST['nama'] ?? $user['nama']) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label">No. HP / WhatsApp</label>
      <input class="form-input" type="tel" name="no_hp" value="<?= sanitize($_POST['no_hp'] ?? $user['no_hp']) ?>" placeholder="08xxxxxxxxxx" required>
    </div>
    <div class="form-group">
      <label class="form-label">Alamat Pengiriman</label>
      <textarea class="form-input" name="alamat" rows="3" required><?= sanitize($_POST['alamat'] ?? $user['alamat']) ?></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">Catatan (opsional)</label>
      <textarea class="form-input" name="catatan" rows="2" placeholder="Contoh: Antar sebelum jam 7 pagi..."><?= sanitize($_POST['catatan'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="checkout-section">
    <div class="checkout-section-title">
      <span class="icon-section" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
      Metode Pembayaran
    </div>

    <!-- Tab VA / Cash -->
    <div style="display:flex; gap:10px; margin-bottom:18px; flex-wrap:wrap;">
      <button type="button" id="tab-va" onclick="switchMetode('va')"
        style="flex:1;min-width:120px;padding:12px;border-radius:var(--radius-md);border:1.5px solid var(--green-500);background:var(--green-50);color:var(--green-700);font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s;font-family:var(--font-body);">
        Virtual Account
        <div style="font-size:11px;font-weight:400;margin-top:2px;color:var(--green-600);">Transfer lewat ATM / Mobile Banking</div>
      </button>

      <button type="button" id="tab-cash" onclick="switchMetode('cash')"
        style="flex:1;min-width:120px;padding:12px;border-radius:var(--radius-md);border:1.5px solid var(--slate-200);background:white;color:var(--slate-600);font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s;font-family:var(--font-body);">
        Cash / COD
        <div style="font-size:11px;font-weight:400;margin-top:2px;color:var(--slate-400);">Bayar tunai saat barang tiba</div>
      </button>
    </div>

    <!-- Panel VA -->
    <div id="panel-va">
      <div style="font-size:13px;font-weight:600;color:var(--slate-500);margin-bottom:10px;">Pilih Bank:</div>
      <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:18px;">
        <?php
        $banks = [
          ['kode'=>'mandiri','nama'=>'Bank Mandiri','warna'=>'#003d79','label'=>'MAND','grad'=>'#003d79,#001f4d'],
          ['kode'=>'bca',    'nama'=>'Bank BCA',    'warna'=>'#005b9f','label'=>'BCA', 'grad'=>'#005b9f,#00469e'],
          ['kode'=>'bni',    'nama'=>'Bank BNI',    'warna'=>'#f26522','label'=>'BNI', 'grad'=>'#f26522,#d4521a'],
          ['kode'=>'bri',    'nama'=>'Bank BRI',    'warna'=>'#00529b','label'=>'BRI', 'grad'=>'#00529b,#003d79'],
          ['kode'=>'bsi',    'nama'=>'Bank BSI',    'warna'=>'#3d9b4b','label'=>'BSI', 'grad'=>'#3d9b4b,#2d7a38'],
        ];
        foreach ($banks as $b): ?>
        <button type="button" id="bank-btn-<?= $b['kode'] ?>"
          onclick="selectBank('<?= $b['kode'] ?>','<?= $b['nama'] ?>','<?= $b['warna'] ?>','<?= $b['label'] ?>','<?= $b['grad'] ?>','va')"
          style="display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:var(--radius-md);border:1.5px solid <?= $b['kode']==='mandiri'?'var(--green-500)':'var(--slate-200)' ?>;background:<?= $b['kode']==='mandiri'?'var(--green-50)':'white' ?>;cursor:pointer;transition:all 0.2s;font-family:var(--font-body);">
          <div style="width:36px;height:36px;border-radius:6px;background:<?= $b['warna'] ?>;color:white;font-weight:800;font-size:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $b['label'] ?></div>
          <span style="font-size:13px;font-weight:600;color:var(--slate-700);"><?= $b['nama'] ?></span>
          <?php if ($b['kode']==='mandiri'): ?><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green-500)" stroke-width="2.5" id="check-<?= $b['kode'] ?>"><polyline points="20 6 9 17 4 12"/></svg><?php else: ?><span id="check-<?= $b['kode'] ?>" style="display:none;"></span><?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>

      <div class="va-card" id="va-card-preview" style="margin-top:0;">
        <div class="va-bank-name" id="va-bank-name">BANK MANDIRI — VIRTUAL ACCOUNT</div>
        <div class="va-number"><?= $va ?></div>
        <div class="va-amount"><?= formatRupiah($total) ?></div>
        <div class="va-expiry">Berlaku 24 jam setelah pesanan dibuat</div>
      </div>
    </div>

    <!-- Panel Cash / COD -->
    <div id="panel-cash" style="display:none;">
      <div style="background:white;border:1.5px solid #d1fae5;border-radius:16px;padding:24px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
        <div style="width:64px;height:64px;border-radius:50%;background:#ecfdf5;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div style="font-size:17px;font-weight:800;color:var(--slate-800);margin-bottom:6px;">Bayar di Tempat (COD)</div>
        <div style="font-size:13px;color:var(--slate-500);line-height:1.7;margin-bottom:18px;">
          Siapkan uang tunai saat kurir tiba.<br>Tidak perlu transfer atau scan QR.
        </div>
        <div style="background:var(--green-50);border:1px solid #bbf7d0;border-radius:10px;padding:14px 20px;display:inline-block;margin-bottom:16px;">
          <div style="font-size:12px;color:var(--slate-500);margin-bottom:4px;">Total yang harus dibayar</div>
          <div style="font-size:24px;font-weight:800;color:var(--green-700);"><?= formatRupiah($total) ?></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;text-align:left;background:var(--slate-50);border-radius:10px;padding:14px 16px;">
          <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--slate-600);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="flex-shrink:0;margin-top:1px;"><polyline points="20 6 9 17 4 12"/></svg>
            Pesanan akan diproses setelah dikonfirmasi admin
          </div>
          <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--slate-600);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="flex-shrink:0;margin-top:1px;"><polyline points="20 6 9 17 4 12"/></svg>
            Bayar langsung ke kurir saat barang tiba
          </div>
          <div style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--slate-600);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" style="flex-shrink:0;margin-top:1px;"><polyline points="20 6 9 17 4 12"/></svg>
            Siapkan uang pas untuk mempermudah transaksi
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden inputs -->
    <input type="hidden" name="metode_tipe" id="input-metode-tipe" value="va">
    <input type="hidden" name="metode_bank" id="input-metode-bank" value="mandiri">

    <div style="font-size:12px;color:var(--slate-400);margin-top:12px;line-height:1.6;" id="info-pembayaran">
      Nomor VA / kode pembayaran final dikirimkan setelah pesanan dikonfirmasi.
    </div>
  </div>
</div>

<script>
const bankGradients = {
  mandiri:'#003d79,#001f4d',bca:'#005b9f,#00469e',bni:'#f26522,#d4521a',
  bri:'#00529b,#003d79',bsi:'#3d9b4b,#2d7a38',
};
let currentTipe = 'va';
let currentBank = 'mandiri';

function switchMetode(tipe) {
  currentTipe = tipe;
  document.getElementById('input-metode-tipe').value = tipe;
  ['va','cash'].forEach(t => {
    const tab = document.getElementById('tab-'+t);
    if (!tab) return;
    tab.style.borderColor = t===tipe ? 'var(--green-500)' : 'var(--slate-200)';
    tab.style.background  = t===tipe ? 'var(--green-50)'  : 'white';
    tab.style.color       = t===tipe ? 'var(--green-700)' : 'var(--slate-600)';
    tab.querySelector('div').style.color = t===tipe ? 'var(--green-600)' : 'var(--slate-400)';
  });
  document.getElementById('panel-va').style.display   = tipe==='va'   ? 'block' : 'none';
  document.getElementById('panel-cash').style.display = tipe==='cash' ? 'block' : 'none';
  const infoBayar = document.getElementById('info-pembayaran');
  if (infoBayar) infoBayar.style.display = tipe==='cash' ? 'none' : 'block';
  if (tipe==='va') {
    selectBank('mandiri','Bank Mandiri','#003d79','MAND','#003d79,#001f4d','va');
  } else {
    document.getElementById('input-metode-bank').value = '';
  }
}

function selectBank(kode, nama, warna, label, grad, tipe) {
  currentBank = kode;
  document.getElementById('input-metode-bank').value = kode;
  if (tipe==='va') {
    // Reset VA buttons
    ['mandiri','bca','bni','bri','bsi'].forEach(k => {
      const b = document.getElementById('bank-btn-'+k);
      if (b) { b.style.borderColor='var(--slate-200)'; b.style.background='white'; }
      const ck = document.getElementById('check-'+k);
      if (ck) ck.style.display='none';
    });
    const btn = document.getElementById('bank-btn-'+kode);
    if (btn) { btn.style.borderColor='var(--green-500)'; btn.style.background='var(--green-50)'; }
    const ck = document.getElementById('check-'+kode);
    if (ck) ck.style.display='inline';
    // Update VA card
    const card = document.getElementById('va-card-preview');
    const g = grad || (bankGradients[kode] || '#003d79,#001f4d');
    if (card) card.style.background = 'linear-gradient(135deg,'+g+')';
    const bn = document.getElementById('va-bank-name');
    if (bn) bn.textContent = nama.toUpperCase() + ' — VIRTUAL ACCOUNT';
  }
}
</script>

<!-- Kanan: Ringkasan -->
<div>
  <div class="order-summary-card">
    <div style="font-size:16px; font-weight:700; color:var(--slate-800); margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--slate-100);"> Ringkasan Pesanan</div>

    <?php foreach ($items as $item):
        $h = $item['jumlah'] >= $item['min_grosir'] ? $item['harga_grosir'] : $item['harga'];
        $sub = $h * $item['jumlah'];
      ?>
    <div class="summary-item">
      <span><?= sanitize($item['nama']) ?> ×<?= $item['jumlah'] ?>kg</span>
      <span><?= formatRupiah($sub) ?></span>
    </div>
    <?php endforeach; ?>

    <div class="summary-item"><span>Ongkos Kirim</span><span style="color:var(--green-600);font-weight:600;">GRATIS</span></div>
    <div class="summary-total"><span>Total Bayar</span><span style="color:var(--green-700);"><?= formatRupiah($total) ?></span></div>

    <button type="submit" class="btn btn-secondary btn-full" style="margin-top:20px; font-size:15px;"> Konfirmasi Pesanan</button>
    <a href="keranjang.php" class="btn btn-outline-green btn-full" style="margin-top:10px;">← Kembali ke Keranjang</a>
  </div>
</div>
</div>
</form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
