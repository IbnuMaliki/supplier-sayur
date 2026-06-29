<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle = 'Riwayat Pesanan';
$userId = $_SESSION['user_id'];

// Proses rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_pesanan'])) {
    $pesananId = (int)$_POST['pesanan_id'];
    $bintang   = (int)$_POST['bintang'];
    $komentar  = trim($_POST['komentar'] ?? '');
    if ($bintang >= 1 && $bintang <= 5) {
        $check = $pdo->prepare("SELECT id FROM rating WHERE pesanan_id=?");
        $check->execute([$pesananId]);
        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO rating (pesanan_id, user_id, bintang, komentar) VALUES (?,?,?,?)");
            $stmt->execute([$pesananId, $userId, $bintang, $komentar]);
            flashMessage('success', 'Terima kasih atas penilaian Anda!');
        }
    }
    redirect(APP_URL . '/pesanan.php');
}

// Proses BATALKAN PESANAN oleh customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batalkan_pesanan'])) {
    $pesananId = (int)$_POST['pesanan_id'];
    $alasan    = trim($_POST['alasan_batal_customer'] ?? '');

    // Hanya bisa batalkan jika status masih menunggu_bayar
    $cek = $pdo->prepare("SELECT id, status FROM pesanan WHERE id=? AND user_id=? AND status='menunggu_bayar'");
    $cek->execute([$pesananId, $userId]);
    if ($cek->fetch()) {
        // Kembalikan stok
        $details = $pdo->prepare("SELECT produk_id, jumlah FROM detail_pesanan WHERE pesanan_id=?");
        $details->execute([$pesananId]);
        foreach ($details->fetchAll() as $d) {
            $pdo->prepare("UPDATE produk SET stok=stok+? WHERE id=?")->execute([$d['jumlah'], $d['produk_id']]);
        }
        $alasanFinal = $alasan ?: '';
        $pdo->prepare("UPDATE pesanan SET status='dibatalkan', alasan_batal=?, dibatal_oleh='customer' WHERE id=?")->execute([$alasanFinal, $pesananId]);
        flashMessage('success', 'Pesanan berhasil dibatalkan.');
    } else {
        flashMessage('error', 'Pesanan tidak dapat dibatalkan. Hanya pesanan yang belum dibayar yang bisa dibatalkan.');
    }
    redirect(APP_URL . '/pesanan.php');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['beli_lagi'])) {
    $pesananId = (int)$_POST['pesanan_id'];
    // Verifikasi pesanan milik user ini dan statusnya selesai
    $cek = $pdo->prepare("SELECT id FROM pesanan WHERE id=? AND user_id=? AND status='selesai'");
    $cek->execute([$pesananId, $userId]);
    if ($cek->fetch()) {
        // Ambil detail pesanan
        $details = $pdo->prepare("SELECT produk_id, jumlah FROM detail_pesanan WHERE pesanan_id=?");
        $details->execute([$pesananId]);
        $items = $details->fetchAll();
        $added = 0;
        foreach ($items as $item) {
            // Cek stok tersedia
            $stok = $pdo->prepare("SELECT stok FROM produk WHERE id=? AND status='aktif' AND stok>0");
            $stok->execute([$item['produk_id']]);
            $produk = $stok->fetch();
            if ($produk) {
                $qty = min($item['jumlah'], $produk['stok']);
                // Cek sudah ada di keranjang
                $existing = $pdo->prepare("SELECT id, jumlah FROM keranjang WHERE user_id=? AND produk_id=?");
                $existing->execute([$userId, $item['produk_id']]);
                $row = $existing->fetch();
                if ($row) {
                    $newQty = min($row['jumlah'] + $qty, $produk['stok']);
                    $pdo->prepare("UPDATE keranjang SET jumlah=? WHERE id=?")->execute([$newQty, $row['id']]);
                } else {
                    $pdo->prepare("INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES (?,?,?)")->execute([$userId, $item['produk_id'], $qty]);
                }
                $added++;
            }
        }
        if ($added > 0) {
            flashMessage('success', $added . ' produk berhasil ditambahkan ke keranjang!');
        } else {
            flashMessage('error', 'Semua produk dari pesanan ini sedang tidak tersedia / stok habis.');
        }
    }
    redirect(APP_URL . '/keranjang.php');
}

// Ambil pesanan
$stmt = $pdo->prepare("

    SELECT p.*, 
           (SELECT COUNT(*) FROM rating r WHERE r.pesanan_id=p.id) as sudah_rating
    FROM pesanan p
    WHERE p.user_id=?
    ORDER BY p.created_at DESC
");
$stmt->execute([$userId]);
$pesananList = $stmt->fetchAll();

// Filter aktif
$filterAktif = $_GET['filter'] ?? 'semua';
$filterMap = [
    'semua'       => null,
    'belum_bayar' => 'menunggu_bayar',
    'diproses'    => 'diproses',
    'dikirim'     => 'dikirim',
    'selesai'     => 'selesai',
    'dibatalkan'  => 'dibatalkan',
];
if ($filterAktif !== 'semua' && isset($filterMap[$filterAktif])) {
    $statusFilter = $filterMap[$filterAktif];
    if ($filterAktif === 'belum_bayar') {
        // Belum bayar: menunggu_bayar ATAU COD (cash) yang statusnya menunggu bayar
        $pesananList = array_filter($pesananList, fn($p) =>
            $p['status'] === 'menunggu_bayar'
        );
    } else {
        $pesananList = array_filter($pesananList, fn($p) => $p['status'] === $statusFilter);
    }
}
$pesananList = array_values($pesananList);

// Status steps
$isCashOrder = str_contains($p['metode_bayar'] ?? '', 'COD') || str_contains($p['metode_bayar'] ?? '', 'Bayar di Tempat');
$statusSteps = $isCashOrder
    ? ['diproses','dikirim','selesai']
    : ['menunggu_pembayaran','diproses','dikirim','selesai'];
$statusLabel = [
    'menunggu_bayar'      => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu Bayar',
    'menunggu_pembayaran' => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu Pembayaran',
    'dibayar'             => ' Dibayar',
    'diproses'            => ' Diproses',
    'dikirim'             => ' Dikirim',
    'selesai'             => ' Selesai',
    'dibatalkan'          => ' Dibatalkan',
];
$timelineLabel = [
    'menunggu_pembayaran' => 'Menunggu Pembayaran',
    'dibayar'             => 'Dibayar',
    'diproses'            => 'Diproses',
    'dikirim'             => 'Dikirim',
    'selesai'             => 'Selesai',
];
$timelineIcon = ['','','','',''];

include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
<div class="breadcrumb"><a href="<?= APP_URL ?>">Home</a> › <span>Riwayat Pesanan</span></div>
<div class="page-header-title"> Riwayat Pesanan</div>
<div class="page-header-sub">Pantau status pesanan sayuran Anda</div>
</div>

<div style="max-width:760px; margin:0 auto; padding:36px 2rem;">

<!-- Filter Tabs -->
<?php
$tabs = [
    'semua'       => 'Semua',
    'belum_bayar' => 'Belum Bayar',
    'diproses'    => 'Diproses',
    'dikirim'     => 'Dikirim',
    'selesai'     => 'Selesai',
    'dibatalkan'  => 'Dibatalkan',
];
?>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:28px;background:var(--slate-50);padding:6px;border-radius:14px;border:1px solid var(--slate-200);">
  <?php foreach ($tabs as $key => $label): ?>
  <a href="?filter=<?= $key ?>"
     style="flex:1;min-width:80px;padding:9px 4px;border-radius:10px;text-align:center;font-size:13px;font-weight:<?= $filterAktif===$key?'700':'500' ?>;text-decoration:none;transition:all 0.18s;
            background:<?= $filterAktif===$key?'white':'transparent' ?>;
            color:<?= $filterAktif===$key?'var(--green-700)':'var(--slate-500)' ?>;
            box-shadow:<?= $filterAktif===$key?'0 1px 6px rgba(0,0,0,0.10)':'none' ?>;">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if (count($pesananList) === 0): ?>
<div class="empty-state">
  <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
  <div class="empty-title"><?= $filterAktif === 'semua' ? 'Belum ada pesanan' : 'Tidak ada pesanan di kategori ini' ?></div>
  <div class="empty-sub"><?= $filterAktif === 'semua' ? 'Yuk mulai belanja sayuran segar untuk warung Anda!' : 'Coba pilih filter lain atau mulai belanja sekarang.' ?></div>
  <?php if ($filterAktif === 'semua'): ?>
  <a class="btn btn-secondary" href="produk.php">Belanja Sekarang</a>
  <?php else: ?>
  <a class="btn btn-secondary" href="?filter=semua">Lihat Semua Pesanan</a>
  <?php endif; ?>
</div>
<?php else: ?>

<?php foreach ($pesananList as $p):
    // Ambil detail
    $dStmt = $pdo->prepare("SELECT dp.*, p.gambar FROM detail_pesanan dp LEFT JOIN produk p ON dp.produk_id=p.id WHERE dp.pesanan_id=?");
    $dStmt->execute([$p['id']]);
    $details = $dStmt->fetchAll();
    $currentIdx = array_search($p['status'], $statusSteps);
    $ratingData = null;
    if ($p['sudah_rating']) {
        $rStmt = $pdo->prepare("SELECT * FROM rating WHERE pesanan_id=?");
        $rStmt->execute([$p['id']]);
        $ratingData = $rStmt->fetch();
    }
  ?>
<div class="order-card" id="order-card-<?= $p['id'] ?>" data-id="<?= $p['id'] ?>" data-status="<?= $p['status'] ?>">
  <!-- Header -->
  <div class="order-card-header">
    <div>
      <div class="order-id"><?= sanitize($p['kode_pesanan']) ?></div>
      <div class="order-date"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></div>
    </div>
    <div class="status-badge status-<?= $p['status'] ?>" id="badge-<?= $p['id'] ?>"><?= $statusLabel[$p['status']] ?></div>
  </div>

  <!-- Detail Items -->
  <div class="order-card-body">
    <?php foreach ($details as $d): ?>
    <div class="order-item">
      <img class="order-item-img" src="<?= getProductImage($d['nama_produk'], 80, $d['gambar'] ?? null) ?>" alt="<?= sanitize($d['nama_produk']) ?>">
      <div>
        <div class="order-item-name"><?= sanitize($d['nama_produk']) ?></div>
        <div class="order-item-detail"><?= $d['jumlah'] ?> kg × <?= formatRupiah($d['harga_satuan']) ?></div>
      </div>
      <div style="margin-left:auto; font-size:14px; font-weight:600; color:var(--slate-700);"><?= formatRupiah($d['subtotal']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Info Pembayaran sesuai metode yang dipilih -->
  <?php if ($p['status'] === 'menunggu_bayar'): ?>
  <?php
    $metode  = $p['metode_bayar'] ?? 'Virtual Account Mandiri';
    $isCash  = str_contains($metode, 'COD') || str_contains($metode, 'Bayar di Tempat');
  ?>
  <div style="padding:16px 20px; background:var(--green-50); border-top:1px solid var(--slate-100);">
    <div style="font-size:13px; font-weight:700; color:var(--green-800); margin-bottom:10px; display:flex; align-items:center; gap:6px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Info Pembayaran
    </div>
    <?php if ($isCash): ?>
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:44px;height:44px;border-radius:10px;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2'><rect x='1' y='4' width='22' height='16' rx='2'/><line x1='1' y1='10' x2='23' y2='10'/></svg></div>
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--slate-800);">Bayar di Tempat (COD)</div>
          <div style="font-size:12px;color:var(--slate-500);">Siapkan uang tunai saat kurir tiba</div>
          <div style="font-size:16px;font-weight:800;color:var(--green-700);margin-top:4px;"><?= formatRupiah($p['total_harga']) ?></div>
        </div>
      </div>
    <?php else: ?>
      <div style="font-size:12px;color:var(--slate-500);margin-bottom:4px;">Transfer ke <strong><?= sanitize($metode) ?>:</strong></div>
      <div style="font-size:20px;font-weight:800;color:var(--green-700);letter-spacing:2px;font-family:monospace;"><?= sanitize($p['nomor_va'] ?? '-') ?></div>
      <div style="font-size:13px;color:var(--slate-500);margin-top:4px;">Jumlah: <strong style="color:var(--green-700);"><?= formatRupiah($p['total_harga']) ?></strong></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Timeline -->
  <?php if ($p['status'] !== 'dibatalkan'): ?>
  <div class="order-timeline">
    <div class="timeline-steps">
      <?php foreach ($statusSteps as $idx => $step):
          $cls = '';
          if ($currentIdx !== false) {
            if ($idx < $currentIdx) $cls = 'done';
            elseif ($idx === $currentIdx) $cls = 'current done';
            else $cls = '';
          }
        ?>
      <div class="timeline-step <?= $cls ?>">
        <div class="timeline-dot"><?= $timelineIcon[$idx] ?></div>
        <div class="timeline-label"><?= $timelineLabel[$step] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php else: ?>
  <div style="padding:16px 20px; background:#fef2f2; border-top:1px solid #fecaca;">
    <?php
      $oleh = $p['dibatal_oleh'] ?? null;
      $labelOleh = $oleh === 'admin' ? 'Admin / Supplier' : ($oleh === 'customer' ? 'Anda (Customer)' : 'Sistem');
      $iconOleh  = $oleh === 'admin'
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
    ?>
    <div style="display:flex;align-items:center;gap:6px;margin-bottom:<?= $p['alasan_batal'] ? '8px' : '0' ?>;">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      <span style="font-size:13px;font-weight:700;color:#991b1b;">Pesanan Dibatalkan</span>
      <span style="font-size:11px;background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;border-radius:20px;padding:2px 8px;font-weight:600;">
        Oleh: <?= $labelOleh ?>
      </span>
    </div>
    <?php if ($p['alasan_batal']): ?>
    <div style="display:flex;align-items:flex-start;gap:7px;background:white;border:1px solid #fecaca;border-radius:8px;padding:10px 12px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#b91c1c" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <div>
        <div style="font-size:10px;font-weight:700;color:#b91c1c;margin-bottom:2px;text-transform:uppercase;letter-spacing:0.5px;">Alasan</div>
        <div style="font-size:12px;color:#7f1d1d;line-height:1.5;"><?= sanitize($p['alasan_batal']) ?></div>
      </div>
    </div>
    <?php else: ?>
    <div style="font-size:12px;color:#b91c1c;font-style:italic;">Tidak ada alasan yang diberikan.</div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Footer -->
  <div class="order-card-footer">
    <div>
      <div class="order-total-label">Total Pesanan</div>
      <div class="order-total-amount"><?= formatRupiah($p['total_harga']) ?></div>
    </div>
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
      <?php if ($p['status'] === 'selesai' && !$p['sudah_rating']): ?>
      <button class="btn btn-sm btn-secondary" onclick="document.getElementById('rating-modal-<?= $p['id'] ?>').style.display='flex'">
        <span class="icon-btn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span> Beri Nilai
      </button>
      <?php elseif ($ratingData): ?>
      <div style="font-size:13px; color:var(--slate-400); display:flex; align-items:center; gap:4px;">
        <?php for ($s=1;$s<=5;$s++): ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="<?= $s<=$ratingData['bintang']?'#f59e0b':'#e5e7eb' ?>" stroke="<?= $s<=$ratingData['bintang']?'#f59e0b':'#d1d5db' ?>" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <?php endfor; ?>
        <span style="font-size:12px; margin-left:2px; color:var(--slate-400);">Sudah dinilai</span>
      </div>
      <button onclick="document.getElementById('lihat-rating-<?= $p['id'] ?>').style.display='flex'"
        style="background:var(--amber-50,#fffbeb);border:1.5px solid #fde68a;color:#92400e;font-size:12px;font-weight:600;padding:5px 12px;border-radius:100px;cursor:pointer;display:flex;align-items:center;gap:5px;font-family:var(--font-body);transition:all 0.2s;"
        onmouseover="this.style.background='#fef3c7'" onmouseout="this.style.background='var(--amber-50,#fffbeb)'">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Lihat Penilaian
      </button>
      <?php endif; ?>

      <?php if ($p['status'] === 'selesai'): ?>
      <!-- Tombol Beli Lagi -->
      <form method="POST" style="margin:0;">
        <input type="hidden" name="beli_lagi" value="1">
        <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
        <button type="submit" class="btn btn-sm btn-secondary"
          style="background:var(--green-600); color:white; border:none;"
          title="Tambahkan produk pesanan ini ke keranjang">
          <span aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:4px;"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></span>Beli Lagi
        </button>
      </form>
      <?php endif; ?>

      <?php if ($p['status'] === 'menunggu_bayar'): ?>
      <!-- Tombol Batalkan Pesanan — hanya saat menunggu bayar -->
      <button class="btn btn-sm"
        style="background:var(--red-100);color:var(--red-800);border:1px solid #fecaca;"
        onclick="document.getElementById('batal-modal-<?= $p['id'] ?>').style.display='flex'">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Batalkan Pesanan
      </button>
      <?php endif; ?>

      <a class="btn btn-sm btn-outline-green" href="<?= APP_URL ?>/chat.php">
        <span class="icon-btn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span> Chat Supplier
      </a>
    </div>
  </div>
</div>

<!-- Rating Modal (per pesanan) -->
<?php if ($p['status'] === 'selesai' && !$p['sudah_rating']): ?>
<div id="rating-modal-<?= $p['id'] ?>" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:2000; align-items:center; justify-content:center; padding:20px;">
  <div style="background:white; border-radius:var(--radius-xl); width:100%; max-width:440px; box-shadow:var(--shadow-lg); overflow:hidden;">
    <!-- Header -->
    <div style="padding:20px 24px; border-bottom:1px solid var(--slate-100); display:flex; justify-content:space-between; align-items:center;">
      <div style="display:flex;align-items:center;gap:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <span style="font-family:var(--font-display);font-size:20px;font-weight:700;color:var(--slate-900);">Beri Penilaian</span>
      </div>
      <!-- Tombol X -->
      <button onclick="document.getElementById('rating-modal-<?= $p['id'] ?>').style.display='none'"
        style="width:32px;height:32px;background:var(--slate-100);border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;"
        onmouseover="this.style.background='#fee2e2';this.style.color='#dc2626'"
        onmouseout="this.style.background='var(--slate-100)';this.style.color='var(--slate-500)'">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <!-- Body -->
    <div style="padding:24px;">
      <div style="font-size:13px;color:var(--slate-500);margin-bottom:4px;">Pesanan <?= sanitize($p['kode_pesanan']) ?></div>
      <div style="font-size:14px;color:var(--slate-700);margin-bottom:22px;">Bagaimana pengalaman belanja Anda?</div>

      <form method="POST">
        <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
        <input type="hidden" name="rate_pesanan" value="1">
        <input type="hidden" name="bintang" id="bintang-val-<?= $p['id'] ?>" value="0">

        <!-- Bintang SVG interaktif (kiri ke kanan 1-5) -->
        <div style="display:flex;gap:6px;margin-bottom:8px;justify-content:center;" id="stars-wrap-<?= $p['id'] ?>">
          <?php for ($s = 1; $s <= 5; $s++): ?>
          <button type="button"
            onclick="setStar(<?= $p['id'] ?>,<?= $s ?>)"
            onmouseover="hoverStar(<?= $p['id'] ?>,<?= $s ?>)"
            onmouseout="resetStar(<?= $p['id'] ?>)"
            style="background:none;border:none;cursor:pointer;padding:3px;line-height:0;">
            <svg id="star-<?= $p['id'] ?>-<?= $s ?>"
                 xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24"
                 fill="#e5e7eb" stroke="#d1d5db" stroke-width="1"
                 style="display:block;transition:fill 0.12s,stroke 0.12s,transform 0.12s;">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
          </button>
          <?php endfor; ?>
        </div>

        <!-- Label deskripsi -->
        <div id="star-label-<?= $p['id'] ?>"
             style="text-align:center;font-size:14px;font-weight:600;color:var(--slate-400);min-height:22px;margin-bottom:20px;">
          Pilih bintang penilaian
        </div>

        <!-- Komentar -->
        <div class="form-group">
          <label class="form-label">Komentar <span style="color:var(--slate-400);font-weight:400;">(opsional)</span></label>
          <textarea class="form-input" name="komentar" rows="3"
            placeholder="cth: sayurannya segar, pengiriman tepat waktu..."></textarea>
        </div>

        <!-- Tombol aksi -->
        <div style="display:flex;gap:10px;margin-top:4px;">
          <button type="button" class="btn btn-outline-green" style="flex:1;"
            onclick="document.getElementById('rating-modal-<?= $p['id'] ?>').style.display='none'">
            Batal
          </button>
          <button type="submit" class="btn btn-secondary" style="flex:2;"
            onclick="if(!+document.getElementById('bintang-val-<?= $p['id'] ?>').value){alert('Pilih bintang terlebih dahulu!');return false;}">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Kirim Penilaian
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Batalkan Pesanan — hanya untuk status menunggu_bayar -->
<?php if ($p['status'] === 'menunggu_bayar'): ?>
<div id="batal-modal-<?= $p['id'] ?>"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:2000; align-items:center; justify-content:center; padding:20px;">
  <div style="background:white; border-radius:var(--radius-xl); width:100%; max-width:440px; box-shadow:var(--shadow-lg); overflow:hidden;">
    <div style="padding:20px 24px; border-bottom:1px solid var(--slate-100); display:flex; justify-content:space-between; align-items:center; background:#fef2f2;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:36px;height:36px;border-radius:50%;background:#ef4444;display:flex;align-items:center;justify-content:center;">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <div>
          <div style="font-size:17px;font-weight:700;color:#991b1b;">Batalkan Pesanan?</div>
          <div style="font-size:12px;color:#dc2626;"><?= sanitize($p['kode_pesanan']) ?></div>
        </div>
      </div>
      <button onclick="document.getElementById('batal-modal-<?= $p['id'] ?>').style.display='none'"
        style="width:30px;height:30px;background:#fee2e2;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div style="padding:24px;">
      <div style="background:#fee2e2;border-radius:var(--radius-md);padding:12px 16px;margin-bottom:20px;font-size:13px;color:#991b1b;display:flex;gap:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span>Pesanan yang dibatalkan tidak dapat dikembalikan. Stok produk akan dikembalikan otomatis.</span>
      </div>
      <form method="POST">
        <input type="hidden" name="batalkan_pesanan" value="1">
        <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
        <div class="form-group">
          <label class="form-label">Alasan Pembatalan <span style="color:var(--slate-400);font-weight:400;">(opsional)</span></label>
          <!-- Pilihan cepat -->
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;" id="alasan-chips-<?= $p['id'] ?>">
            <?php foreach(['Salah pilih produk','Ingin ganti produk lain','Tidak jadi beli','Lainnya'] as $opt): ?>
            <button type="button"
              onclick="pilihAlasan(<?= $p['id'] ?>, this, '<?= $opt ?>')"
              style="padding:6px 14px;border-radius:100px;border:1.5px solid var(--slate-200);background:white;font-size:12px;font-weight:500;cursor:pointer;transition:all 0.2s;font-family:var(--font-body);color:var(--slate-600);">
              <?= $opt ?>
            </button>
            <?php endforeach; ?>
          </div>
          <textarea class="form-input" name="alasan_batal_customer" id="alasan-txt-<?= $p['id'] ?>"
            rows="2" placeholder="Tulis alasan pembatalan..."></textarea>
        </div>
        <div style="display:flex;gap:10px;margin-top:4px;">
          <button type="button" class="btn btn-outline-green" style="flex:1;"
            onclick="document.getElementById('batal-modal-<?= $p['id'] ?>').style.display='none'">
            Kembali
          </button>
          <button type="submit" class="btn" style="flex:2;background:#ef4444;color:white;border:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Ya, Batalkan Pesanan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($p['sudah_rating'] && $ratingData): ?>
<!-- Modal Lihat Penilaian -->
<div id="lihat-rating-<?= $p['id'] ?>" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:2000;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)this.style.display='none'">
  <div style="background:white;border-radius:20px;width:100%;max-width:400px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
    <!-- Header -->
    <div style="background:linear-gradient(135deg,#f59e0b,#d97706);padding:24px;text-align:center;position:relative;">
      <button onclick="document.getElementById('lihat-rating-<?= $p['id'] ?>').style.display='none'"
        style="position:absolute;top:14px;right:14px;background:rgba(255,255,255,0.2);border:none;color:white;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;line-height:1;">×</button>
      <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.85);margin-bottom:8px;">Penilaian Anda</div>
      <div style="font-size:40px;font-weight:900;color:white;line-height:1;"><?= $ratingData['bintang'] ?></div>
      <div style="display:flex;justify-content:center;gap:4px;margin:8px 0 4px;">
        <?php for ($s=1;$s<=5;$s++): ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
             fill="<?= $s<=$ratingData['bintang']?'#fff':'rgba(255,255,255,0.3)' ?>"
             stroke="<?= $s<=$ratingData['bintang']?'#fff':'rgba(255,255,255,0.3)' ?>" stroke-width="1">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>
        <?php endfor; ?>
      </div>
      <?php
        $labelBintang = [1=>'Mengecewakan',2=>'Kurang Memuaskan',3=>'Cukup',4=>'Memuaskan',5=>'Sangat Memuaskan'];
      ?>
      <div style="font-size:13px;color:rgba(255,255,255,0.9);font-weight:600;"><?= $labelBintang[$ratingData['bintang']] ?? '' ?></div>
    </div>
    <!-- Body -->
    <div style="padding:24px;">
      <div style="font-size:11px;font-weight:700;color:var(--slate-400);text-transform:uppercase;letter-spacing:0.7px;margin-bottom:8px;">Pesanan</div>
      <div style="font-size:13px;font-weight:600;color:var(--slate-700);margin-bottom:16px;"><?= sanitize($p['kode_pesanan']) ?> · <?= date('d M Y', strtotime($p['created_at'])) ?></div>
      <div style="font-size:11px;font-weight:700;color:var(--slate-400);text-transform:uppercase;letter-spacing:0.7px;margin-bottom:8px;">Komentar</div>
      <?php if (!empty($ratingData['komentar'])): ?>
      <div style="background:var(--slate-50);border:1px solid var(--slate-200);border-radius:10px;padding:14px;font-size:13px;color:var(--slate-700);line-height:1.6;font-style:italic;">
        "<?= sanitize($ratingData['komentar']) ?>"
      </div>
      <?php else: ?>
      <div style="font-size:13px;color:var(--slate-400);font-style:italic;">Tidak ada komentar.</div>
      <?php endif; ?>
      <div style="font-size:11px;color:var(--slate-400);margin-top:14px;">
        Dinilai pada <?= date('d M Y H:i', strtotime($ratingData['created_at'] ?? $p['updated_at'])) ?> WIB
      </div>
    </div>
    <div style="padding:0 24px 20px;">
      <button onclick="document.getElementById('lihat-rating-<?= $p['id'] ?>').style.display='none'"
        style="width:100%;padding:11px;background:var(--slate-100);border:none;border-radius:10px;font-size:13px;font-weight:600;color:var(--slate-600);cursor:pointer;font-family:var(--font-body);">Tutup</button>
    </div>
  </div>
</div>
<?php endif; ?>

<?php endforeach; ?>
<?php endif; ?>
</div>
</div>

<script>
// ── Bintang interaktif untuk modal rating ──
function pilihAlasan(orderId, btn, teks) {
    // Toggle chip aktif
    const chips = document.querySelectorAll('#alasan-chips-'+orderId+' button');
    chips.forEach(c => {
        c.style.background   = 'white';
        c.style.borderColor  = 'var(--slate-200)';
        c.style.color        = 'var(--slate-600)';
    });
    btn.style.background  = '#fee2e2';
    btn.style.borderColor = '#ef4444';
    btn.style.color       = '#991b1b';
    // Isi textarea
    const ta = document.getElementById('alasan-txt-'+orderId);
    if (ta) ta.value = teks === 'Lainnya' ? '' : teks;
    if (teks === 'Lainnya' && ta) ta.focus();
}

const starLabels = {1:'Mengecewakan',2:'Kurang Memuaskan',3:'Cukup',4:'Memuaskan',5:'Sangat Memuaskan'};
const starColors  = {1:'#ef4444',2:'#f97316',3:'#eab308',4:'#22c55e',5:'#16a34a'};

function setStar(orderId, val) {
    document.getElementById('bintang-val-' + orderId).value = val;
    // Simpan state terpilih di setiap bintang
    for (let i = 1; i <= 5; i++) {
        const s = document.getElementById('star-' + orderId + '-' + i);
        s.dataset.selected = i <= val ? '1' : '0';
        s.setAttribute('fill', i <= val ? '#f59e0b' : '#e5e7eb');
        s.setAttribute('stroke', i <= val ? '#f59e0b' : '#d1d5db');
        s.style.transform = i <= val ? 'scale(1.1)' : 'scale(1)';
    }
    const lbl = document.getElementById('star-label-' + orderId);
    lbl.textContent = starLabels[val] || '';
    lbl.style.color = starColors[val] || 'var(--slate-600)';
}

function hoverStar(orderId, val) {
    for (let i = 1; i <= 5; i++) {
        const s = document.getElementById('star-' + orderId + '-' + i);
        s.setAttribute('fill', i <= val ? '#fbbf24' : '#e5e7eb');
        s.setAttribute('stroke', i <= val ? '#fbbf24' : '#d1d5db');
    }
    const lbl = document.getElementById('star-label-' + orderId);
    lbl.textContent = starLabels[val] || '';
    lbl.style.color = '#f59e0b';
}

function resetStar(orderId) {
    // Kembali ke state terpilih
    const cur = parseInt(document.getElementById('bintang-val-' + orderId).value) || 0;
    for (let i = 1; i <= 5; i++) {
        const s = document.getElementById('star-' + orderId + '-' + i);
        s.setAttribute('fill', i <= cur ? '#f59e0b' : '#e5e7eb');
        s.setAttribute('stroke', i <= cur ? '#f59e0b' : '#d1d5db');
    }
    const lbl = document.getElementById('star-label-' + orderId);
    if (cur > 0) {
        lbl.textContent = starLabels[cur];
        lbl.style.color = starColors[cur];
    } else {
        lbl.textContent = 'Pilih bintang penilaian';
        lbl.style.color = 'var(--slate-400)';
    }
}

// Animasi toast
const toastStyle = document.createElement('style');
toastStyle.textContent = `
@keyframes toastIn  { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
@keyframes toastOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(40px); } }
`;
document.head.appendChild(toastStyle);

// ============================================================
// POLLING — cek perubahan status pesanan setiap 10 detik
// ============================================================
(function() {
    const APP_URL = '<?= APP_URL ?>';

    const statusLabel = {
    menunggu_bayar      : 'Menunggu Bayar',
    menunggu_pembayaran : 'Menunggu Pembayaran',
    dibayar             : 'Dibayar',
    diproses            : 'Diproses',
    dikirim             : 'Dikirim',
    selesai             : 'Selesai',
    dibatalkan          : 'Dibatalkan',
    };
    const statusClass = {
    menunggu_bayar      : 'status-menunggu_bayar',
    menunggu_pembayaran : 'status-menunggu_bayar',
    dibayar             : 'status-diproses',
    diproses            : 'status-diproses',
    dikirim             : 'status-dikirim',
    selesai             : 'status-selesai',
    dibatalkan          : 'status-dibatalkan',
    };
    const pesanToast = {
        diproses   : 'Pesanan Anda sedang diproses oleh supplier!',
        dikirim    : 'Pesanan Anda sedang dalam pengiriman!',
        selesai    : 'Pesanan Anda telah selesai! Jangan lupa beri penilaian.',
        dibatalkan : 'Pesanan Anda telah dibatalkan.',
    };

    // Ambil semua ID order card yang ada di halaman ini
    function getOrderIds() {
        return [...document.querySelectorAll('.order-card[data-id]')]
            .filter(el => {
                const s = el.dataset.status;
                // Hanya poll status yang masih "aktif" (bukan terminal)
                return s !== 'selesai' && s !== 'dibatalkan';
            })
            .map(el => el.dataset.id);
    }

    // Tampilkan toast notifikasi
    function showToast(msg, type) {
        const colors = {
            info    : { bg:'#1e40af', border:'#3b82f6' },
            success : { bg:'#15803d', border:'#22c55e' },
            warning : { bg:'#b45309', border:'#f59e0b' },
            danger  : { bg:'#b91c1c', border:'#f87171' },
        };
        const c = colors[type] || colors.info;

        const wrap = document.getElementById('toast-container') || (() => {
            const d = document.createElement('div');
            d.id = 'toast-container';
            d.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;max-width:340px;';
            document.body.appendChild(d);
            return d;
        })();

        const t = document.createElement('div');
        t.style.cssText = `background:${c.bg};border-left:4px solid ${c.border};color:white;padding:14px 18px;border-radius:10px;font-size:13px;font-weight:600;line-height:1.5;box-shadow:0 4px 20px rgba(0,0,0,0.3);animation:toastIn .3s ease;`;
        t.textContent = msg;
        wrap.appendChild(t);

        setTimeout(() => {
            t.style.animation = 'toastOut .3s ease forwards';
            setTimeout(() => t.remove(), 300);
        }, 5000);
    }

    // Update tampilan badge status di halaman (tanpa reload)
    function updateCard(id, newStatus) {
        const card  = document.getElementById('order-card-' + id);
        const badge = document.getElementById('badge-' + id);
        if (!card || !badge) return;

        const oldStatus = card.dataset.status;
        if (oldStatus === newStatus) return; // tidak ada perubahan

        // Update badge
        badge.className = 'status-badge ' + (statusClass[newStatus] || '');
        badge.innerHTML = statusLabel[newStatus] || newStatus;

        // Update data attribute
        card.dataset.status = newStatus;

        // Highlight card sebentar
        card.style.transition = 'box-shadow 0.4s, border-color 0.4s';
        card.style.boxShadow  = '0 0 0 3px rgba(22,163,74,0.35)';
        card.style.borderColor = '#22c55e';
        setTimeout(() => {
            card.style.boxShadow   = '';
            card.style.borderColor = '';
        }, 2500);

        // Toast notif
        const type = newStatus === 'dibatalkan' ? 'danger'
                   : newStatus === 'selesai'    ? 'success'
                   : 'info';
        if (pesanToast[newStatus]) showToast(pesanToast[newStatus], type);

        // Kalau jadi selesai, reload halaman setelah 2 detik
        // supaya tombol "Beri Nilai" muncul
        if (newStatus === 'selesai' || newStatus === 'dibatalkan') {
            setTimeout(() => location.reload(), 2500);
        }
    }

    // Jalankan polling
    function poll() {
        const ids = getOrderIds();
        if (ids.length === 0) return; // semua sudah terminal, stop

        fetch(APP_URL + '/ajax/cek_status.php?ids=' + ids.join(','))
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                Object.entries(res.data).forEach(([id, info]) => {
                    updateCard(id, info.status);
                });
            })
            .catch(() => {}); // silent fail
    }

    // Mulai polling setelah 3 detik, lalu setiap 10 detik
    setTimeout(() => {
        poll();
        setInterval(poll, 10000);
    }, 3000);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
