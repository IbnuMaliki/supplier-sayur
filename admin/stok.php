<?php
require_once dirname(__DIR__) . '/admin/includes/admin_header.php';
$pageTitle = 'Riwayat Stok';

// Filter
$produkFilter = (int)($_GET['produk_id'] ?? 0);
$jenisFilter  = $_GET['jenis'] ?? '';
$limit = 50;
$offset = (int)($_GET['page'] ?? 0) * $limit;

$sql = "SELECT rs.*, p.nama as nama_produk, p.satuan
        FROM riwayat_stok rs
        JOIN produk p ON rs.produk_id = p.id
        WHERE 1=1";
$params = [];
if ($produkFilter) { $sql .= " AND rs.produk_id=?"; $params[] = $produkFilter; }
if ($jenisFilter)  { $sql .= " AND rs.jenis=?";     $params[] = $jenisFilter; }
$sql .= " ORDER BY rs.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$riwayat = $stmt->fetchAll();

// Total stok masuk/keluar per produk
$produkList = $pdo->query("SELECT id, nama FROM produk WHERE status='aktif' ORDER BY nama")->fetchAll();
$totalMasuk = (int)$pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM riwayat_stok WHERE jenis='masuk'")->fetchColumn();
$totalKeluar= (int)$pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM riwayat_stok WHERE jenis='keluar'")->fetchColumn();
$totalLog   = (int)$pdo->query("SELECT COUNT(*) FROM riwayat_stok")->fetchColumn();
?>

<div class="admin-page-title">Riwayat Stok</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;">
  <div style="background:white;border-radius:var(--r-lg);padding:18px;border:1px solid var(--n-150);box-shadow:var(--shadow-xs);">
    <div style="font-size:11px;color:var(--n-400);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Total Log</div>
    <div style="font-size:26px;font-weight:700;color:var(--n-900);"><?= number_format($totalLog) ?></div>
  </div>
  <div style="background:white;border-radius:var(--r-lg);padding:18px;border:1px solid var(--n-150);box-shadow:var(--shadow-xs);">
    <div style="font-size:11px;color:var(--brand-600);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Total Masuk</div>
    <div style="font-size:26px;font-weight:700;color:var(--brand-700);">+<?= number_format($totalMasuk) ?></div>
  </div>
  <div style="background:white;border-radius:var(--r-lg);padding:18px;border:1px solid var(--n-150);box-shadow:var(--shadow-xs);">
    <div style="font-size:11px;color:var(--red-600);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Total Keluar</div>
    <div style="font-size:26px;font-weight:700;color:var(--red-600);">-<?= number_format($totalKeluar) ?></div>
  </div>
</div>

<!-- Filter -->
<div style="background:white;border-radius:var(--r-lg);padding:16px 20px;border:1px solid var(--n-150);margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;width:100%;">
    <select name="produk_id" class="form-input" style="width:220px;">
      <option value="">Semua Produk</option>
      <?php foreach ($produkList as $pr): ?>
      <option value="<?= $pr['id'] ?>" <?= $produkFilter==$pr['id']?'selected':'' ?>><?= sanitize($pr['nama']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="jenis" class="form-input" style="width:160px;">
      <option value="">Semua Jenis</option>
      <option value="masuk"  <?= $jenisFilter==='masuk'?'selected':''  ?>>Stok Masuk</option>
      <option value="keluar" <?= $jenisFilter==='keluar'?'selected':'' ?>>Stok Keluar</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="<?= APP_URL ?>/admin/stok.php" class="btn btn-ghost btn-sm">Reset</a>
  </form>
</div>

<!-- Tabel -->
<div class="table-wrap">
  <div class="table-header">
    <div class="table-title">Log Perubahan Stok (<?= count($riwayat) ?> data)</div>
  </div>
  <?php if (empty($riwayat)): ?>
  <div class="empty-state" style="padding:40px;">
    <div class="empty-title">Belum ada riwayat stok</div>
    <div class="empty-sub">Riwayat akan tercatat otomatis saat stok berubah.</div>
  </div>
  <?php else: ?>
  <table class="data-table">
    <thead>
      <tr>
        <th>Waktu</th>
        <th>Produk</th>
        <th>Jenis</th>
        <th>Jumlah</th>
        <th>Stok Awal</th>
        <th>Stok Akhir</th>
        <th>Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($riwayat as $r): ?>
      <tr>
        <td style="font-size:12px;color:var(--n-400);white-space:nowrap;"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
        <td style="font-weight:600;"><?= sanitize($r['nama_produk']) ?></td>
        <td>
          <?php if ($r['jenis'] === 'masuk'): ?>
          <span class="status-badge" style="background:var(--brand-50);color:var(--brand-700);border:1px solid var(--brand-200);">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
            Masuk
          </span>
          <?php else: ?>
          <span class="status-badge" style="background:var(--red-50);color:var(--red-700);border:1px solid #fecaca;">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
            Keluar
          </span>
          <?php endif; ?>
        </td>
        <td style="font-weight:700;color:<?= $r['jenis']==='masuk'?'var(--brand-700)':'var(--red-600)' ?>;">
          <?= $r['jenis']==='masuk'?'+':'-' ?><?= number_format($r['jumlah']) ?> <?= sanitize($r['satuan']) ?>
        </td>
        <td style="color:var(--n-500);"><?= number_format($r['stok_awal']) ?></td>
        <td style="font-weight:600;"><?= number_format($r['stok_akhir']) ?></td>
        <td style="font-size:12px;color:var(--n-400);"><?= sanitize($r['keterangan'] ?? '-') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php include dirname(__DIR__) . '/admin/includes/admin_footer.php'; ?>
