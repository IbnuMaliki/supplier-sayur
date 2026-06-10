<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Laporan';

$bulan = $_GET['bulan'] ?? date('Y-m');
[$tahun, $bln] = explode('-', $bulan . '-01');

// Ringkasan bulan
$ringkasan = $pdo->prepare("
    SELECT
        COUNT(*) as total_pesanan,
        COALESCE(SUM(CASE WHEN status='selesai' THEN total_harga END), 0) as pendapatan,
        COUNT(CASE WHEN status='selesai' THEN 1 END) as selesai,
        COUNT(CASE WHEN status='dibatalkan' THEN 1 END) as dibatalkan,
        COUNT(CASE WHEN status='dikirim' THEN 1 END) as dikirim,
        COUNT(CASE WHEN status='diproses' THEN 1 END) as diproses,
        COUNT(CASE WHEN status='menunggu_bayar' THEN 1 END) as menunggu
    FROM pesanan WHERE DATE_FORMAT(created_at,'%Y-%m')=?
");
$ringkasan->execute([$bulan]);
$ring = $ringkasan->fetch();

// Produk terlaris bulan ini
$terlaris = $pdo->prepare("
    SELECT d.nama_produk, SUM(d.jumlah) as total_qty, SUM(d.subtotal) as total_rev
    FROM detail_pesanan d
    JOIN pesanan p ON d.pesanan_id=p.id
    WHERE DATE_FORMAT(p.created_at,'%Y-%m')=? AND p.status != 'dibatalkan'
    GROUP BY d.nama_produk ORDER BY total_qty DESC LIMIT 5
");
$terlaris->execute([$bulan]);
$terlarisData = $terlaris->fetchAll();

// Daftar transaksi
$transaksi = $pdo->prepare("
    SELECT p.*, u.nama as nama_user, u.nama_warung
    FROM pesanan p JOIN users u ON p.user_id=u.id
    WHERE DATE_FORMAT(p.created_at,'%Y-%m')=?
    ORDER BY p.created_at DESC
");
$transaksi->execute([$bulan]);
$transaksiList = $transaksi->fetchAll();

$statusLabel = ['menunggu_bayar'=>'<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu','diproses'=>' Diproses','dikirim'=>' Dikirim','selesai'=>' Selesai','dibatalkan'=>' Dibatalkan'];

// ── Export PDF (harus sebelum include header) ──────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    $namaBulan = date('F Y', mktime(0,0,0,(int)$bln,1,(int)$tahun));
    $totalPDF  = array_sum(array_column(array_filter($transaksiList, fn($t) => $t['status']==='selesai'), 'total_harga'));

    // Ambil riwayat stok bulan ini
    $stmtStok = $pdo->prepare("
        SELECT rs.*, p.nama as nama_produk, p.satuan
        FROM riwayat_stok rs
        JOIN produk p ON rs.produk_id = p.id
        WHERE YEAR(rs.created_at)=? AND MONTH(rs.created_at)=?
        ORDER BY rs.created_at DESC
    ");
    $stmtStok->execute([$tahun, $bln]);
    $riwayatStokPDF = $stmtStok->fetchAll();
    $totalMasukPDF  = array_sum(array_column(array_filter($riwayatStokPDF, fn($r)=>$r['jenis']==='masuk'), 'jumlah'));
    $totalKeluarPDF = array_sum(array_column(array_filter($riwayatStokPDF, fn($r)=>$r['jenis']==='keluar'), 'jumlah'));
    $statusLabelPDF = [
        'menunggu_bayar' => 'Menunggu',
        'diproses'       => 'Diproses',
        'dikirim'        => 'Dikirim',
        'selesai'        => 'Selesai',
        'dibatalkan'     => 'Dibatalkan',
    ];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan <?= $namaBulan ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; font-size:12px; color:#1a1a1a; padding:32px; background:white; }
.header { text-align:center; margin-bottom:28px; border-bottom:3px solid #27743a; padding-bottom:18px; }
.header h1 { font-size:22px; color:#27743a; margin-bottom:5px; }
.header p  { color:#78746c; font-size:11px; }
.stats { display:flex; gap:14px; margin-bottom:24px; }
.stat { flex:1; border:1px solid #e3e1dd; border-radius:8px; padding:14px; text-align:center; }
.stat-label { font-size:10px; color:#78746c; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px; }
.stat-val   { font-size:20px; font-weight:800; color:#27743a; }
table { width:100%; border-collapse:collapse; }
th { background:#27743a; color:white; padding:9px 10px; font-size:11px; text-align:left; }
td { padding:8px 10px; border-bottom:1px solid #f0f0ee; font-size:11px; vertical-align:middle; }
tr:nth-child(even) td { background:#fafaf9; }
.selesai    { background:#e0f2e6; color:#1e5c2d; padding:2px 9px; border-radius:99px; font-weight:600; }
.diproses   { background:#eff6ff; color:#1d4ed8; padding:2px 9px; border-radius:99px; font-weight:600; }
.dikirim    { background:#f0faf3; color:#27743a; padding:2px 9px; border-radius:99px; font-weight:600; }
.dibatalkan { background:#fef2f2; color:#b91c1c; padding:2px 9px; border-radius:99px; font-weight:600; }
.menunggu_bayar { background:#fffbeb; color:#92400e; padding:2px 9px; border-radius:99px; font-weight:600; }
.footer { margin-top:24px; padding-top:12px; border-top:1px solid #e3e1dd; display:flex; justify-content:space-between; font-size:10px; color:#a0a09a; }
@media print { body{padding:10px;} button{display:none;} }
</style>
</head>
<body>
<div class="header">
  <h1>Laporan Penjualan</h1>
  <p><?= APP_NAME ?> &nbsp;|&nbsp; Periode: <?= $namaBulan ?> &nbsp;|&nbsp; Dicetak: <?= date('d M Y H:i') ?> WIB</p>
</div>
<div class="stats">
  <div class="stat"><div class="stat-label">Total Transaksi</div><div class="stat-val"><?= count($transaksiList) ?></div></div>
  <div class="stat"><div class="stat-label">Selesai</div><div class="stat-val"><?= count(array_filter($transaksiList,fn($t)=>$t['status']==='selesai')) ?></div></div>
  <div class="stat"><div class="stat-label">Total Pendapatan</div><div class="stat-val">Rp <?= number_format($totalPDF,0,',','.') ?></div></div>
</div>
<table>
  <thead>
    <tr><th>Tanggal</th><th>ID Pesanan</th><th>Customer</th><th>Warung</th><th>Total</th><th>Status</th></tr>
  </thead>
  <tbody>
    <?php foreach ($transaksiList as $t): ?>
    <tr>
      <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
      <td style="font-weight:700;"><?= sanitize($t['kode_pesanan']) ?></td>
      <td><?= sanitize($t['nama_user']) ?></td>
      <td><?= sanitize($t['nama_warung']??'-') ?></td>
      <td style="font-weight:700;">Rp <?= number_format($t['total_harga'],0,',','.') ?></td>
      <td><span class="<?= $t['status'] ?>"><?= $statusLabelPDF[$t['status']] ?? $t['status'] ?></span></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<!-- RIWAYAT STOK -->
<div style="margin-top:32px; padding-top:24px; border-top:2px solid #27743a;">
  <h2 style="font-size:18px;color:#27743a;margin-bottom:6px;">Riwayat Stok</h2>
  <p style="font-size:11px;color:#78746c;margin-bottom:18px;">Periode: <?= $namaBulan ?></p>

  <div style="display:flex;gap:14px;margin-bottom:20px;">
    <div style="flex:1;border:1px solid #e3e1dd;border-radius:8px;padding:12px;text-align:center;">
      <div style="font-size:10px;color:#78746c;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Total Log</div>
      <div style="font-size:18px;font-weight:800;color:#27743a;"><?= count($riwayatStokPDF) ?></div>
    </div>
    <div style="flex:1;border:1px solid #e3e1dd;border-radius:8px;padding:12px;text-align:center;">
      <div style="font-size:10px;color:#78746c;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Total Masuk</div>
      <div style="font-size:18px;font-weight:800;color:#27743a;">+<?= number_format($totalMasukPDF) ?></div>
    </div>
    <div style="flex:1;border:1px solid #e3e1dd;border-radius:8px;padding:12px;text-align:center;">
      <div style="font-size:10px;color:#78746c;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Total Keluar</div>
      <div style="font-size:18px;font-weight:800;color:#dc2626;">-<?= number_format($totalKeluarPDF) ?></div>
    </div>
  </div>

  <?php if (empty($riwayatStokPDF)): ?>
  <p style="text-align:center;color:#a09c94;font-size:13px;padding:20px;">Belum ada riwayat stok pada periode ini.</p>
  <?php else: ?>
  <table>
    <thead>
      <tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Jumlah</th><th>Stok Awal</th><th>Stok Akhir</th><th>Keterangan</th></tr>
    </thead>
    <tbody>
      <?php foreach ($riwayatStokPDF as $rs): ?>
      <tr>
        <td><?= date('d/m/Y H:i', strtotime($rs['created_at'])) ?></td>
        <td style="font-weight:600;"><?= sanitize($rs['nama_produk']) ?></td>
        <td>
          <span class="<?= $rs['jenis']==='masuk' ? 'selesai' : 'dibatalkan' ?>">
            <?= $rs['jenis']==='masuk' ? 'Masuk' : 'Keluar' ?>
          </span>
        </td>
        <td style="font-weight:700;color:<?= $rs['jenis']==='masuk'?'#27743a':'#dc2626' ?>;">
          <?= $rs['jenis']==='masuk'?'+':'-' ?><?= number_format($rs['jumlah']) ?> <?= sanitize($rs['satuan']) ?>
        </td>
        <td><?= number_format($rs['stok_awal']) ?></td>
        <td style="font-weight:600;"><?= number_format($rs['stok_akhir']) ?></td>
        <td style="font-size:11px;color:#78746c;"><?= sanitize($rs['keterangan']??'-') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<div class="footer">
  <span><?= APP_NAME ?> — Sistem Supplier Sayur</span>
  <span>Dokumen digenerate otomatis <?= date('d M Y H:i') ?></span>
</div>
<script>window.onload = function(){ window.print(); }</script>
</body>
</html>
<?php
    exit;
}

include __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-top-bar">
<div class="admin-page-title" style="margin-bottom:0;"> Laporan Transaksi</div>
<form method="GET" style="display:flex; gap:8px; align-items:center;">
  <label style="font-size:13px; color:var(--slate-600); font-weight:600;">Periode:</label>
  <input class="form-input" type="month" name="bulan" value="<?= sanitize($bulan) ?>" style="width:160px; padding:8px 12px;">
  <button class="btn btn-secondary btn-sm" type="submit">Filter</button>
</form>
</div>

<!-- Ringkasan -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
  <!-- Pendapatan -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#dcfce7;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
      </svg>
    </div>
    <div class="stat-value"><?= $ring['pendapatan'] >= 1000000 ? 'Rp '.number_format($ring['pendapatan']/1000000,1).'Jt' : formatRupiah($ring['pendapatan']) ?></div>
    <div class="stat-label">Pendapatan</div>
  </div>
  <!-- Total Pesanan -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
      </svg>
    </div>
    <div class="stat-value"><?= $ring['total_pesanan'] ?></div>
    <div class="stat-label">Total Pesanan</div>
  </div>
  <!-- Selesai -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#dcfce7;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>
    <div class="stat-value"><?= $ring['selesai'] ?></div>
    <div class="stat-label">Selesai</div>
  </div>
  <!-- Dibatalkan -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#fee2e2;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </div>
    <div class="stat-value"><?= $ring['dibatalkan'] ?></div>
    <div class="stat-label">Dibatalkan</div>
  </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px; margin-bottom:24px; align-items:start;">
<!-- Produk Terlaris -->
<div class="table-wrap">
  <div class="table-header"><div class="table-title"> Produk Terlaris Bulan Ini</div></div>
  <?php if (count($terlarisData) === 0): ?>
  <div style="padding:30px; text-align:center; color:var(--slate-400);">Belum ada data</div>
  <?php else: ?>
  <table class="data-table">
    <thead><tr><th>#</th><th>Produk</th><th>Qty Terjual</th><th>Revenue</th></tr></thead>
    <tbody>
      <?php foreach ($terlarisData as $i => $t): ?>
      <tr>
        <td style="font-weight:700; color:var(--green-600);"><?= $i+1 ?></td>
        <td style="font-weight:600;"><?= sanitize($t['nama_produk']) ?></td>
        <td><?= $t['total_qty'] ?> kg</td>
        <td style="font-weight:600; color:var(--green-700);"><?= formatRupiah($t['total_rev']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Status Breakdown -->
<div class="chart-card">
  <div class="chart-title"> Breakdown Status</div>
  <?php
    $breakdown = [
      ['label'=>'Menunggu','val'=>$ring['menunggu'],'color'=>'#fbbf24'],
      ['label'=>'Diproses','val'=>$ring['diproses'],'color'=>'#60a5fa'],
      ['label'=>'Dikirim','val'=>$ring['dikirim'],'color'=>'#34d399'],
      ['label'=>'Selesai','val'=>$ring['selesai'],'color'=>'#22c55e'],
      ['label'=>'Dibatalkan','val'=>$ring['dibatalkan'],'color'=>'#f87171'],
    ];
    $total = max($ring['total_pesanan'], 1);
    foreach ($breakdown as $b):
      $pct = round($b['val']/$total*100);
    ?>
  <div style="margin-bottom:12px;">
    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
      <span style="font-weight:600;"><?= $b['label'] ?></span>
      <span style="color:var(--slate-500);"><?= $b['val'] ?> (<?= $pct ?>%)</span>
    </div>
    <div style="background:var(--slate-100);border-radius:100px;height:8px;">
      <div style="background:<?= $b['color'] ?>;height:100%;border-radius:100px;width:<?= $pct ?>%;transition:width 0.8s;"></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
</div>

<!-- Tabel Transaksi -->
<div class="table-wrap">
<div class="table-header">
  <div class="table-title"> Daftar Transaksi — <?= date('F Y', mktime(0,0,0,(int)$bln,1,(int)$tahun)) ?></div>
  <a href="laporan.php?bulan=<?= $bulan ?>&export=pdf" class="btn btn-sm" style="background:var(--red-600);color:white;" target="_blank">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      Export PDF
    </a>
</div>
<table class="data-table">
  <thead>
    <tr><th>Tanggal</th><th>ID Pesanan</th><th>Customer</th><th>Total</th><th>Status</th></tr>
  </thead>
  <tbody>
    <?php if (count($transaksiList) === 0): ?>
    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--slate-400);">Tidak ada transaksi pada periode ini</td></tr>
    <?php endif; ?>
    <?php foreach ($transaksiList as $t): ?>
    <tr>
      <td style="font-size:13px;color:var(--slate-500);"><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
      <td style="font-weight:700;"><?= sanitize($t['kode_pesanan']) ?></td>
      <td>
        <div style="font-weight:600;"><?= sanitize($t['nama_user']) ?></div>
        <div style="font-size:12px;color:var(--slate-400);"><?= sanitize($t['nama_warung']??'-') ?></div>
      </td>
      <td style="font-weight:700;color:var(--green-700);"><?= formatRupiah($t['total_harga']) ?></td>
      <td><span class="status-badge status-<?= $t['status'] ?>"><?= $statusLabel[$t['status']] ?></span></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php

?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
