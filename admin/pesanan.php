<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Kelola Pesanan';

// Hapus pesanan
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    // Kembalikan stok jika bukan selesai/dibatalkan
    $cek = $pdo->prepare("SELECT status FROM pesanan WHERE id=?");
    $cek->execute([$delId]);
    $ps = $cek->fetch();
    if ($ps && !in_array($ps['status'], ['selesai','dibatalkan'])) {
        $details = $pdo->prepare("SELECT produk_id, jumlah FROM detail_pesanan WHERE pesanan_id=?");
        $details->execute([$delId]);
        foreach ($details->fetchAll() as $d) {
            $pdo->prepare("UPDATE produk SET stok=stok+? WHERE id=?")->execute([$d['jumlah'], $d['produk_id']]);
        }
    }
    $pdo->prepare("DELETE FROM detail_pesanan WHERE pesanan_id=?")->execute([$delId]);
    $pdo->prepare("DELETE FROM rating WHERE pesanan_id=?")->execute([$delId]);
    $pdo->prepare("DELETE FROM pesanan WHERE id=?")->execute([$delId]);
    flashMessage('success', 'Pesanan berhasil dihapus.');
    redirect(APP_URL . '/admin/pesanan.php');
}

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_bayar'])) {
    $pesananId = (int)$_POST['pesanan_id'];
    // Hanya boleh konfirmasi jika status masih menunggu_bayar dan bukan COD
    $cek = $pdo->prepare("SELECT id, status, metode_bayar FROM pesanan WHERE id=?");
    $cek->execute([$pesananId]);
    $row = $cek->fetch();
    $isCash = $row && (str_contains($row['metode_bayar'], 'COD') || str_contains($row['metode_bayar'], 'Bayar di Tempat'));
    if ($row && $row['status'] === 'menunggu_bayar' && !$isCash) {
        $pdo->prepare("UPDATE pesanan SET status='diproses' WHERE id=?")->execute([$pesananId]);
        flashMessage('success', 'Pembayaran dikonfirmasi. Pesanan sekarang berstatus Diproses.');
    } else {
        flashMessage('error', 'Pesanan tidak dapat dikonfirmasi.');
    }
    redirect(APP_URL . '/admin/pesanan.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pesananId  = (int)$_POST['pesanan_id'];
    $newStatus  = $_POST['status'];
    $alasan     = trim($_POST['alasan_batal'] ?? '');
    $allowed    = ['menunggu_bayar','diproses','dikirim','selesai','dibatalkan'];
    if (in_array($newStatus, $allowed)) {
        $dibatalOleh = ($newStatus === 'dibatalkan') ? 'admin' : null;
        $pdo->prepare("UPDATE pesanan SET status=?, alasan_batal=?, dibatal_oleh=? WHERE id=?")->execute([$newStatus, $alasan, $dibatalOleh, $pesananId]);
        if ($newStatus === 'dibatalkan') {
            $details = $pdo->prepare("SELECT produk_id, jumlah FROM detail_pesanan WHERE pesanan_id=?");
            $details->execute([$pesananId]);
            foreach ($details->fetchAll() as $d) {
                $stokSkrg = (int)$pdo->query("SELECT stok FROM produk WHERE id={$d['produk_id']}")->fetchColumn();
                $pdo->prepare("UPDATE produk SET stok=stok+? WHERE id=?")->execute([$d['jumlah'], $d['produk_id']]);
                $pdo->prepare("INSERT INTO riwayat_stok (produk_id,jenis,jumlah,stok_awal,stok_akhir,keterangan) VALUES (?,?,?,?,?,?)")
                    ->execute([$d['produk_id'], 'masuk', $d['jumlah'], $stokSkrg, $stokSkrg+$d['jumlah'], 'Stok kembali - pesanan dibatalkan']);
            }
        }
        flashMessage('success', 'Status pesanan berhasil diperbarui.');
    }
    redirect(APP_URL . '/admin/pesanan.php');
}

// Filter
$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, u.nama as nama_user, u.nama_warung, u.no_hp as hp_user FROM pesanan p JOIN users u ON p.user_id=u.id WHERE 1=1";
$params = [];
if ($statusFilter) { $sql .= " AND p.status=?"; $params[] = $statusFilter; }
if ($search) { $sql .= " AND (p.kode_pesanan LIKE ? OR u.nama LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pesananList = $stmt->fetchAll();

$statusOptions = [''=>'Semua Status','menunggu_bayar'=>'<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu','diproses'=>' Diproses','dikirim'=>' Dikirim','selesai'=>' Selesai','dibatalkan'=>' Dibatalkan'];

include __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-title"> Kelola Pesanan</div>

<!-- Filter Bar -->
<div style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
<form method="GET" style="display:flex; gap:10px; flex:1; flex-wrap:wrap;">
  <input class="form-input" type="text" name="q" placeholder=" Cari ID pesanan / nama customer..." value="<?= sanitize($search) ?>" style="flex:1; min-width:200px;">
  <select class="form-input" name="status" style="width:180px;">
    <?php foreach ($statusOptions as $val => $lbl): ?>
    <option value="<?= $val ?>" <?= $statusFilter===$val?'selected':'' ?>><?= $lbl ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-secondary" type="submit">Filter</button>
  <?php if ($search || $statusFilter): ?><a class="btn btn-outline-green" href="pesanan.php">Reset</a><?php endif; ?>
</form>
</div>

<!-- Tabel Pesanan -->
<div class="table-wrap">
<div class="table-header">
  <div class="table-title">Pesanan (<?= count($pesananList) ?>)</div>
</div>
<table class="data-table">
  <thead>
    <tr><th>ID Pesanan</th><th>Customer</th><th>Info Pengiriman</th><th>Total</th><th>VA</th><th>Status</th><th>Waktu</th><th>Update Status</th></tr>
  </thead>
  <tbody>
    <?php if (count($pesananList) === 0): ?>
    <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--slate-400);">Tidak ada pesanan ditemukan</td></tr>
    <?php endif; ?>
    <?php foreach ($pesananList as $p): ?>
    <tr>
      <td>
        <div style="font-weight:700;"><?= sanitize($p['kode_pesanan']) ?></div>
        <div style="font-size:11px; color:var(--slate-400);"><?= date('d M Y', strtotime($p['created_at'])) ?></div>
      </td>
      <td>
        <div style="font-weight:600;"><?= sanitize($p['nama_user']) ?></div>
        <div style="font-size:12px; color:var(--slate-400);"><?= sanitize($p['nama_warung']??'-') ?></div>
        <div style="font-size:12px; color:var(--slate-400);"><?= sanitize($p['hp_user']??'') ?></div>
      </td>
      <td style="max-width:200px;">
        <div style="display:flex;align-items:flex-start;gap:5px;margin-bottom:4px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--slate-400)" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <div style="font-size:11px;color:var(--slate-600);line-height:1.5;"><?= sanitize($p['alamat_pengiriman']) ?></div>
        </div>
        <?php if (!empty($p['no_hp'])): ?>
        <div style="display:flex;align-items:center;gap:5px;margin-bottom:3px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--slate-400)" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <div style="font-size:11px;color:var(--slate-600);"><?= sanitize($p['no_hp']) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($p['catatan'])): ?>
        <div style="display:flex;align-items:flex-start;gap:5px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="var(--amber-500,#f59e0b)" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <div style="font-size:11px;color:var(--slate-500);font-style:italic;"><?= sanitize($p['catatan']) ?></div>
        </div>
        <?php endif; ?>
      </td>
      <td style="font-weight:700; color:var(--green-700);"><?= formatRupiah($p['total_harga']) ?></td>
      <td>
        <div style="font-size:12px;font-weight:600;color:var(--slate-700);"><?= sanitize($p['metode_bayar'] ?? 'VA Mandiri') ?></div>
        <?php if ($p['nomor_va']): ?>
        <div style="font-family:monospace;font-size:11px;color:var(--slate-400);margin-top:2px;"><?= sanitize($p['nomor_va']) ?></div>
        <?php endif; ?>
      </td>
      <td>
        <span class="status-badge status-<?= $p['status'] ?>"><?= $statusOptions[$p['status']] ?></span>
        <?php if ($p['status'] === 'dibatalkan'): ?>
        <div style="margin-top:6px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 10px;">
          <div style="display:flex;align-items:center;gap:5px;margin-bottom:3px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span style="font-size:10px;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:0.5px;">
              Dibatalkan oleh <?= $p['dibatal_oleh'] === 'customer' ? 'Customer' : 'Admin' ?>
            </span>
          </div>
          <?php if (!empty($p['alasan_batal'])): ?>
          <?php
            // Bersihkan prefix lama jika ada
            $alasanTampil = preg_replace('/^Dibatalkan oleh customer:\s*/i', '', $p['alasan_batal'] ?? '');
            $alasanTampil = preg_replace('/^Dibatalkan oleh customer\s*/i', '', $alasanTampil);
          ?>
          <div style="font-size:11px;color:#7f1d1d;line-height:1.5;">
            "<?= sanitize(trim($alasanTampil)) ?>"
          </div>
          <?php else: ?>
          <div style="font-size:11px;color:#b91c1c;font-style:italic;">Tidak ada alasan</div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </td>
      <td style="font-size:12px; color:var(--slate-400);"><?= date('H:i', strtotime($p['created_at'])) ?></td>
      <td>
      <div style="display:flex;flex-direction:column;gap:6px;min-width:80px;">
        <button class="btn-action btn-edit" onclick="document.getElementById('modal-<?= $p['id'] ?>').style.display='flex'" style="width:100%;text-align:center;">Update</button>
        <?php if ($p['status'] !== 'dibatalkan'): ?>
        <a href="struk.php?id=<?= $p['id'] ?>" target="_blank" class="btn-action" style="width:100%;text-align:center;background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">Struk</a>
        <?php endif; ?>
        <a href="?action=delete&id=<?= $p['id'] ?>"
     class="btn-action btn-del"
     style="width:100%;text-align:center;"
     onclick="return confirm('Hapus pesanan <?= addslashes(sanitize($p['kode_pesanan'])) ?>?\\n\\nData pesanan akan dihapus permanen.')">
    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
    Hapus
  </a>
</div>
      </td>
    </tr>
    <!-- Modal Update Status -->
    <div id="modal-<?= $p['id'] ?>" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:2000;align-items:center;justify-content:center;padding:20px;">
      <div style="background:white;border-radius:var(--radius-xl);width:100%;max-width:480px;box-shadow:var(--shadow-lg);overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid var(--slate-100);display:flex;justify-content:space-between;align-items:center;">
          <div style="font-family:var(--font-display);font-size:18px;font-weight:700;">Update Pesanan #<?= sanitize($p['kode_pesanan']) ?></div>
          <button onclick="document.getElementById('modal-<?= $p['id'] ?>').style.display='none'" style="background:none;border:none;font-size:22px;cursor:pointer;color:var(--slate-400);"></button>
        </div>
        <div style="padding:24px;">
          <!-- Detail produk -->
          <?php
            $dStmt = $pdo->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id=?");
            $dStmt->execute([$p['id']]);
            $details = $dStmt->fetchAll();
            ?>
          <div style="background:var(--slate-50);border-radius:var(--radius-sm);padding:12px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:700;color:var(--slate-500);margin-bottom:8px;">ITEM PESANAN</div>
            <?php foreach ($details as $d): ?>
            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;">
              <span><?= sanitize($d['nama_produk']) ?> × <?= $d['jumlah'] ?>kg</span>
              <span style="font-weight:600;"><?= formatRupiah($d['subtotal']) ?></span>
            </div>
            <?php endforeach; ?>
            <div style="border-top:1px solid var(--slate-200);margin-top:8px;padding-top:8px;display:flex;justify-content:space-between;font-weight:700;">
              <span>Total</span><span style="color:var(--green-700);"><?= formatRupiah($p['total_harga']) ?></span>
            </div>
          </div>
          <?php
            $isCashOrder = str_contains($p['metode_bayar'] ?? '', 'COD') || str_contains($p['metode_bayar'] ?? '', 'Bayar di Tempat');
            $isVA        = !$isCashOrder && $p['status'] === 'menunggu_bayar';
          ?>
          <?php if ($isVA): ?>
          <form method="POST" style="margin-bottom:10px;" onsubmit="return confirm('Konfirmasi pembayaran VA untuk pesanan <?= sanitize($p['kode_pesanan']) ?>?')">
            <input type="hidden" name="konfirmasi_bayar" value="1">
            <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
            <button type="submit" style="width:100%;padding:11px;background:linear-gradient(135deg,#16a34a,#15803d);color:white;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font-body);display:flex;align-items:center;justify-content:center;gap:7px;margin-bottom:2px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Konfirmasi Pembayaran VA
            </button>
            <div style="font-size:11px;color:var(--slate-400);text-align:center;margin-bottom:10px;">Klik jika transfer sudah masuk ke rekening</div>
          </form>
          <div style="height:1px;background:var(--slate-200);margin-bottom:10px;"></div>
          <?php endif; ?>
          <form method="POST">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
            <div class="form-group">
              <label class="form-label">Status Baru</label>
              <select class="form-input" name="status" id="status-sel-<?= $p['id'] ?>" onchange="document.getElementById('alasan-wrap-<?= $p['id'] ?>').style.display=this.value==='dibatalkan'?'block':'none'">
                <?php foreach ($statusOptions as $v => $l): if (!$v) continue; ?>
                <option value="<?= $v ?>" <?= $p['status']===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div id="alasan-wrap-<?= $p['id'] ?>" style="display:<?= $p['status']==='dibatalkan'?'block':'none' ?>;">
              <div class="form-group">
                <label class="form-label">Alasan Pembatalan</label>
                <textarea class="form-input" name="alasan_batal" rows="2" placeholder="Contoh: Stok habis, tidak bisa dipenuhi..."><?= sanitize($p['alasan_batal']??'') ?></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-secondary btn-full">Simpan Status</button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
