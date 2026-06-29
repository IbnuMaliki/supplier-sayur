<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Dashboard';

// Statistik
$totalPendapatan = $pdo->query("SELECT COALESCE(SUM(total_harga),0) FROM pesanan WHERE status='selesai'")->fetchColumn();
$totalPesanan    = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$totalCustomer   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$avgRating       = $pdo->query("SELECT ROUND(AVG(bintang),1) FROM rating")->fetchColumn();
$pesananBulanIni = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

// Filter periode
$periode = $_GET['periode'] ?? 'mingguan';

if ($periode === 'bulanan') {
    // 6 bulan terakhir
    $chartData = [];
    for ($i = 5; $i >= 0; $i--) {
        $year  = date('Y', strtotime("-$i months"));
        $month = date('m', strtotime("-$i months"));
        $label = date('M', strtotime("-$i months"));
        $stmt  = $pdo->prepare("SELECT COALESCE(SUM(total_harga),0) FROM pesanan WHERE YEAR(created_at)=? AND MONTH(created_at)=? AND status IN ('selesai','dikirim','diproses')");
        $stmt->execute([$year, $month]);
        $chartData[] = ['label' => $label, 'value' => (float)$stmt->fetchColumn()];
    }
    // Jumlah pesanan per bulan
    $chartPesanan = [];
    for ($i = 5; $i >= 0; $i--) {
        $year  = date('Y', strtotime("-$i months"));
        $month = date('m', strtotime("-$i months"));
        $stmt  = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE YEAR(created_at)=? AND MONTH(created_at)=?");
        $stmt->execute([$year, $month]);
        $chartPesanan[] = (int)$stmt->fetchColumn();
    }
} else {
    // 7 hari terakhir (default mingguan)
    $chartData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date  = date('Y-m-d', strtotime("-$i days"));
        $label = date('D', strtotime($date));
        $stmt  = $pdo->prepare("SELECT COALESCE(SUM(total_harga),0) FROM pesanan WHERE DATE(created_at)=? AND status IN ('selesai','dikirim','diproses')");
        $stmt->execute([$date]);
        $chartData[] = ['label' => $label, 'value' => (float)$stmt->fetchColumn()];
    }
    // Jumlah pesanan per hari
    $chartPesanan = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE DATE(created_at)=?");
        $stmt->execute([$date]);
        $chartPesanan[] = (int)$stmt->fetchColumn();
    }
}

$chartLabels = json_encode(array_column($chartData, 'label'));
$chartValues = json_encode(array_column($chartData, 'value'));
$chartPesananJson = json_encode($chartPesanan);

// Pesanan terbaru
$pesananTerbaru = $pdo->query("
    SELECT p.*, u.nama as nama_user, u.nama_warung
    FROM pesanan p JOIN users u ON p.user_id=u.id
    ORDER BY p.created_at DESC LIMIT 8
")->fetchAll();

// Stok hampir habis
$stokHabis = $pdo->query("SELECT * FROM produk WHERE stok <= 20 AND status='aktif' ORDER BY stok ASC LIMIT 5")->fetchAll();

$statusLabel = [
    'menunggu_bayar'      => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu',
    'menunggu_pembayaran' => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Menunggu Bayar',
    'dibayar'             => ' Dibayar',
    'diproses'            => ' Diproses',
    'dikirim'             => ' Dikirim',
    'selesai'             => ' Selesai',
    'dibatalkan'          => ' Dibatalkan',
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
<!-- Filter Periode -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
  <span style="font-size:13px;font-weight:600;color:var(--slate-500);">Periode:</span>
  <a href="?periode=mingguan" 
     style="padding:7px 18px;border-radius:100px;font-size:13px;font-weight:600;text-decoration:none;transition:all 0.2s;
            background:<?= $periode==='mingguan'?'var(--green-600)':'var(--slate-100)' ?>;
            color:<?= $periode==='mingguan'?'white':'var(--slate-600)' ?>;">
    Mingguan
  </a>
  <a href="?periode=bulanan"
     style="padding:7px 18px;border-radius:100px;font-size:13px;font-weight:600;text-decoration:none;transition:all 0.2s;
            background:<?= $periode==='bulanan'?'var(--green-600)':'var(--slate-100)' ?>;
            color:<?= $periode==='bulanan'?'white':'var(--slate-600)' ?>;">
    Bulanan
  </a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px;align-items:start;">
<!-- Grafik -->
<div class="chart-card" style="padding:20px;">
  <div class="chart-title" style="margin-bottom:16px;">
    Pendapatan <?= $periode==='bulanan'?'6 Bulan':'7 Hari' ?> Terakhir
  </div>
  <div style="position:relative;height:220px;">
    <canvas id="chartPendapatan"></canvas>
  </div>
  <div style="margin-top:20px;border-top:1px solid var(--slate-100);padding-top:16px;">
    <div class="chart-title" style="margin-bottom:12px;">
      Jumlah Pesanan <?= $periode==='bulanan'?'6 Bulan':'7 Hari' ?> Terakhir
    </div>
    <div style="position:relative;height:160px;">
      <canvas id="chartPesanan"></canvas>
    </div>
  </div>
</div>

<!-- Stok Hampir Habis -->
<div class="chart-card" style="padding:20px;">
  <div class="chart-title">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    Stok Hampir Habis
  </div>
  <?php if (count($stokHabis) === 0): ?>
  <div style="text-align:center;padding:20px;color:var(--slate-400);font-size:13px;">Semua stok mencukupi ✅</div>
  <?php else: ?>
  <?php foreach ($stokHabis as $s): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--slate-50);">
    <div style="font-size:13px;font-weight:600;color:var(--slate-700);"><?= sanitize($s['nama']) ?></div>
    <div style="font-size:12px;font-weight:700;color:<?= $s['stok']==0?'#ef4444':'#f59e0b' ?>;">
      <?= $s['stok']==0?'Habis':$s['stok'].' kg' ?>
    </div>
  </div>
  <?php endforeach; ?>
  <a href="produk.php" class="btn btn-sm btn-outline-green btn-full" style="margin-top:14px;">Kelola Produk</a>
  <?php endif; ?>
</div>
</div>

<!-- Tabel Pesanan Terbaru -->
<div class="table-wrap">
<div class="table-header">
  <div class="table-title">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
    Pesanan Terbaru
  </div>
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
      <td><span class="status-badge status-<?= $p['status'] ?>"><?= $statusLabel[$p['status']] ?? $p['status'] ?></span></td>
      <td style="font-size:13px;color:var(--slate-400);"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
      <td>
        <a href="pesanan.php" class="btn-action btn-edit">Update</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
@media (max-width:768px) {
  div[style*="grid-template-columns:2fr 1fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>
<script>
const labels  = <?= $chartLabels ?>;
const values  = <?= $chartValues ?>;
const pesanan = <?= $chartPesananJson ?>;

new Chart(document.getElementById('chartPendapatan'), {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      label: 'Pendapatan (Rp)',
      data: values,
      backgroundColor: 'rgba(22,163,74,0.7)',
      borderColor: '#16a34a',
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { callback: v => v>=1000000?'Rp'+v/1000000+'Jt':v>=1000?'Rp'+v/1000+'K':'Rp'+v, font:{size:11} },
        grid: { color:'#f1f5f9' }
      },
      x: { grid:{display:false}, ticks:{font:{size:11}} }
    }
  }
});

new Chart(document.getElementById('chartPesanan'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'Jumlah Pesanan',
      data: pesanan,
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59,130,246,0.1)',
      borderWidth: 2,
      pointBackgroundColor: '#3b82f6',
      pointRadius: 4,
      fill: true,
      tension: 0.4,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { callbacks: { label: ctx => ctx.parsed.y + ' pesanan' } }
    },
    scales: {
      y: { beginAtZero:true, ticks:{stepSize:1,font:{size:11}}, grid:{color:'#f1f5f9'} },
      x: { grid:{display:false}, ticks:{font:{size:11}} }
    }
  }
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
