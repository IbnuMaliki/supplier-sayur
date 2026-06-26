<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Customers';

// Hapus customer
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    // Jangan hapus admin
    $cek = $pdo->prepare("SELECT role FROM users WHERE id=?");
    $cek->execute([$delId]);
    $u = $cek->fetch();
    if ($u && $u['role'] === 'customer') {
        // Hapus chat, rating, keranjang, detail_pesanan, pesanan, lalu user
        $pdo->prepare("DELETE FROM chat WHERE user_id=?")->execute([$delId]);
        $pdo->prepare("DELETE FROM keranjang WHERE user_id=?")->execute([$delId]);
        // Hapus rating yg terkait pesanan customer ini
        $pdo->prepare("DELETE r FROM rating r JOIN pesanan p ON r.pesanan_id=p.id WHERE p.user_id=?")->execute([$delId]);
        // Hapus detail pesanan lalu pesanan
        $pdo->prepare("DELETE dp FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id=p.id WHERE p.user_id=?")->execute([$delId]);
        $pdo->prepare("DELETE FROM pesanan WHERE user_id=?")->execute([$delId]);
        // Hapus foto profil jika ada
        $foto = $pdo->prepare("SELECT foto_profil FROM users WHERE id=?");
        $foto->execute([$delId]);
        $fotoFile = $foto->fetchColumn();
        if ($fotoFile) {
            $fotoPath = dirname(__DIR__) . '/uploads/profil/' . $fotoFile;
            if (file_exists($fotoPath)) unlink($fotoPath);
        }
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='customer'")->execute([$delId]);
        flashMessage('success', 'Akun customer berhasil dihapus.');
    } else {
        flashMessage('error', 'Tidak dapat menghapus akun ini.');
    }
    redirect(APP_URL . '/admin/customers.php');
}

$customers = $pdo->query("
    SELECT u.*,
           COUNT(p.id) as total_pesanan,
           COALESCE(SUM(CASE WHEN p.status='selesai' THEN p.total_harga END),0) as total_belanja
    FROM users u
    LEFT JOIN pesanan p ON u.id=p.user_id
    WHERE u.role='customer'
    GROUP BY u.id
    ORDER BY total_belanja DESC
")->fetchAll();

include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-top-bar">
  <div class="admin-page-title" style="margin-bottom:0;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:6px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Data Customers
  </div>
  <div style="font-size:13px; color:var(--slate-500);"><?= count($customers) ?> customer terdaftar</div>
</div>

<div class="table-wrap">
  <div class="table-header">
    <div class="table-title">Daftar Customer (<?= count($customers) ?>)</div>
    <div style="font-size:12px; color:var(--slate-400);">Hapus akun customer yang sudah tidak aktif</div>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Nama &amp; Warung</th>
        <th>Email</th>
        <th>No. HP</th>
        <th>Total Pesanan</th>
        <th>Total Belanja</th>
        <th>Bergabung</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($customers) === 0): ?>
      <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--slate-400);">Belum ada customer terdaftar</td></tr>
      <?php endif; ?>
      <?php foreach ($customers as $i => $c): ?>
      <tr>
        <td style="color:var(--slate-400);"><?= $i+1 ?></td>
        <td>
          <!-- Avatar + nama -->
          <div style="display:flex; align-items:center; gap:10px;">
            <?php if (!empty($c['foto_profil'])): ?>
            <img src="<?= APP_URL ?>/uploads/profil/<?= sanitize($c['foto_profil']) ?>"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid var(--green-200);"
                 alt="<?= sanitize($c['nama']) ?>">
            <?php else: ?>
            <div style="width:36px;height:36px;border-radius:50%;background:var(--green-100);color:var(--green-700);font-weight:700;font-size:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <?= strtoupper(substr($c['nama'],0,1)) ?>
            </div>
            <?php endif; ?>
            <div>
              <div style="font-weight:600;color:var(--slate-800);"><?= sanitize($c['nama']) ?></div>
              <div style="font-size:12px;color:var(--slate-400);"><?= sanitize($c['nama_warung']??'-') ?></div>
            </div>
          </div>
        </td>
        <td style="font-size:13px;"><?= sanitize($c['email']) ?></td>
        <td style="font-size:13px;"><?= sanitize($c['no_hp']??'-') ?></td>
        <td style="font-weight:600; text-align:center;"><?= $c['total_pesanan'] ?></td>
        <td style="font-weight:700; color:var(--green-700);"><?= formatRupiah($c['total_belanja']) ?></td>
        <td style="font-size:12px; color:var(--slate-400);"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
        <td>
          <a href="?action=delete&id=<?= $c['id'] ?>"
             class="btn-action btn-del"
             onclick="return confirm('Hapus akun customer:\n<?= addslashes(sanitize($c['nama'])) ?> (<?= addslashes(sanitize($c['nama_warung']??'')) ?>)?\n\nSemua data pesanan, chat, dan rating customer ini akan ikut terhapus permanen.\nLanjutkan?')"
             style="display:inline-flex;align-items:center;gap:5px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            Hapus
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Info -->
<div style="margin-top:16px; padding:14px 18px; background:#fef3c7; border:1px solid #fde68a; border-radius:var(--radius-md); font-size:13px; color:#92400e; display:flex; align-items:flex-start; gap:10px;">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
  <span><strong>Perhatian:</strong> Menghapus akun customer akan menghapus semua data terkait secara permanen, termasuk riwayat pesanan, chat, dan rating. Tindakan ini tidak dapat dibatalkan.</span>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
