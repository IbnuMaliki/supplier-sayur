<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Dashboard';

// Statistik
$totalPendapatan = $pdo->query("SELECT COALESCE(SUM(total_harga),0) FROM pesanan WHERE status='selesai'")->fetchColumn();
$totalPesanan    = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$totalCustomer   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$avgRating       = $pdo->query("SELECT ROUND(AVG(bintang),1) FROM rating")->fetchColumn();

// Pesananbulan ini
$pesananBulanIni = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

// Grafik 7 hari (pendapatan)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime($date));
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_harga),0) FROM pesanan WHERE DATE(created_at)=? AND status IN ('selesai','dikirim','diproses')");
    $stmt->execute([$date]);
    $val = (float)$stmt->fetchColumn();
    $chartData[] = ['label' => $label, 'value' => $val];
}
$maxVal = max(array_column($chartData, 'value')) ?: 1;

// Pesanan terbaru
$pesananTerbaru = $pdo->query("
    SELECT p.*, u.nama as nama_user, u.nama_warung
    FROM pesanan p JOIN users u ON p.user_id=u.id
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// Stok hampir habis
$stokHabis = $pdo->query("SELECT * FROM produk WHERE stok <= 20 AND status='aktif' ORDER BY stok ASC LIMIT 5")->fetchAll();

$statusLabel = [
    'menunggu_bayar'=>'<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu','diproses'=>' Diproses',
    'dikirim'=>' Dikirim','selesai'=>' Selesai','dibatalkan'=>' Dibatalkan'
];

include __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-title"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> Dashboard</div>

<!-- Stat Cards -->
<div class="stats-grid">
  <!-- Pendapatan -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#dcfce7;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
      </svg>
    </div>
    <div class="stat-value"><?= $totalPendapatan >= 1000000 ? 'Rp '.number_format($totalPendapatan/1000000,1).'Jt' : formatRupiah($totalPendapatan) ?></div>
    <div class="stat-label">Total Pendapatan</div>
    <div class="stat-delta delta-up">↑ Dari pesanan selesai</div>
  </div>
  <!-- Total Pesanan -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
      </svg>
    </div>
    <div class="stat-value"><?= $totalPesanan ?></div>
    <div class="stat-label">Total Pesanan</div>
    <div class="stat-delta delta-up">↑ <?= $pesananBulanIni ?> bulan ini</div>
  </div>
  <!-- Total Customer -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#fef3c7;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
    </div>
    <div class="stat-value"><?= $totalCustomer ?></div>
    <div class="stat-label">Total Customer</div>
    <div class="stat-delta delta-up">Warung terdaftar</div>
  </div>
  <!-- Rating -->
  <div class="stat-card">
    <div class="stat-icon" style="background:#f3e8ff;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#a855f7" stroke="#a855f7" stroke-width="1">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>
    </div>
    <div class="stat-value"><?= $avgRating ?: '-' ?></div>
    <div class="stat-label">Rating Rata-rata</div>
    <div class="stat-delta delta-up">Dari semua ulasan</div>
  </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:24px; margin-bottom:24px; align-items:start;">
<!-- Grafik -->
<div class="chart-card">
  <div class="chart-title">Pendapatan 7 Hari Terakhir</div>
  <div class="bar-chart">
    <?php foreach ($chartData as $d):
        $pct = $maxVal > 0 ? ($d['value'] / $maxVal * 100) : 0;
        $label = $d['value'] >= 1000000 ? 'Rp'.number_format($d['value']/1000000,1).'Jt' : ($d['value']>0?'Rp'.number_format($d['value']/1000,0).'K':'-');
      ?>
    <div class="bar-wrap">
      <div class="bar-val"><?= $label ?></div>
      <div class="bar" style="height:<?= max(4, $pct) ?>%;" title="<?= $d['label'] ?>: <?= formatRupiah($d['value']) ?>"></div>
      <div class="bar-lbl"><?= $d['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!--<span class="icon-warn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span> Stok Hampir Habis -->
<div class="chart-card" style="padding:20px;">
  <div class="chart-title"><span class="icon-warn" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span> Stok Hampir Habis</div>
  <?php if (count($stokHabis) === 0): ?>
  <div style="text-align:center; padding:20px; color:var(--slate-400); font-size:13px;">Semua stok mencukupi </div>
  <?php else: ?>
  <?php foreach ($stokHabis as $s): ?>
  <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--slate-50);">
    <div style="font-size:13px; font-weight:600; color:var(--slate-700);"><?= sanitize($s['nama']) ?></div>
    <div style="font-size:12px; font-weight:700; color:<?= $s['stok']==0?'var(--red-500)':'var(--amber-400)' ?>;">
      <?= $s['stok'] == 0 ? 'Habis' : $s['stok'].' kg' ?>
    </div>
  </div>
  <?php endforeach; ?>
  <a href="produk.php" class="btn btn-sm btn-outline-green btn-full" style="margin-top:14px;">Kelola Produk</a>
  <?php endif; ?>
</div>
</div>

<!--<span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span> Pesanan Terbaru -->
<div class="table-wrap">
<div class="table-header">
  <div class="table-title"><span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span> Pesanan Terbaru</div>
  <a href="pesanan.php" class="btn btn-sm btn-outline-green">Lihat Semua</a>
</div>
<table class="data-table">
  <thead>
    <tr><th>ID Pesanan</th><th>Customer</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
  </thead>
  <tbody>
    <?php foreach ($pesananTerbaru as $p): ?>
    <tr>
      <td style="font-weight:700;"><?= sanitize($p['kode_pesanan']) ?></td>
      <td>
        <div style="font-weight:600;"><?= sanitize($p['nama_user']) ?></div>
        <div style="font-size:12px;color:var(--slate-400);"><?= sanitize($p['nama_warung']??'-') ?></div>
      </td>
      <td style="font-weight:700;color:var(--green-700);"><?= formatRupiah($p['total_harga']) ?></td>
      <td><span class="status-badge status-<?= $p['status'] ?>"><?= $statusLabel[$p['status']] ?></span></td>
      <td style="font-size:13px;color:var(--slate-400);"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
      <td>
        <a href="pesanan.php?edit=<?= $p['id'] ?>" class="btn-action btn-edit">Update</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
