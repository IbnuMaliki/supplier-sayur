<?php
$pageTitle = 'Pengaturan Tentang Kami';
require_once dirname(__DIR__) . '/admin/includes/admin_header.php';

// Simpan pengaturan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['nama_usaha','tagline','deskripsi_singkat','deskripsi_lengkap','alamat','no_hp','email_usaha','jam_operasional','tahun_berdiri'];
    foreach ($fields as $field) {
        $val = trim($_POST[$field] ?? '');
        $cek = $pdo->prepare("SELECT id FROM pengaturan WHERE kunci=?");
        $cek->execute([$field]);
        if ($cek->fetch()) {
            $pdo->prepare("UPDATE pengaturan SET nilai=? WHERE kunci=?")->execute([$val, $field]);
        } else {
            $pdo->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES (?,?)")->execute([$field, $val]);
        }
    }
    flashMessage('success', 'Pengaturan berhasil disimpan.');
    redirect(APP_URL . '/admin/tentang.php');
}

// Ambil data pengaturan
$settings = [];
$rows = $pdo->query("SELECT kunci, nilai FROM pengaturan")->fetchAll();
foreach ($rows as $r) $settings[$r['kunci']] = $r['nilai'];

function getSetting($key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}
?>

<div class="admin-page-title">Pengaturan Tentang Kami</div>
<p style="font-size:14px;color:var(--n-400);margin-top:-12px;margin-bottom:22px;">Konten ini ditampilkan di halaman <a href="<?= APP_URL ?>/tentang.php" target="_blank" style="color:var(--brand-600);">Tentang Kami</a></p>

<form method="POST">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">

  <!-- Kiri: Form -->
  <div>
    <div class="form-card" style="margin-bottom:16px;">
      <div style="font-size:14px;font-weight:600;color:var(--n-800);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--n-100);">Identitas Usaha</div>
      <div class="form-group">
        <label class="form-label">Nama Usaha</label>
        <input class="form-input" type="text" name="nama_usaha" value="<?= sanitize(getSetting('nama_usaha', APP_NAME)) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Tagline</label>
        <input class="form-input" type="text" name="tagline" value="<?= sanitize(getSetting('tagline', 'Sayuran Segar Berkualitas')) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Tahun Berdiri</label>
        <input class="form-input" type="text" name="tahun_berdiri" value="<?= sanitize(getSetting('tahun_berdiri', '2020')) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi Singkat</label>
        <textarea class="form-input" name="deskripsi_singkat" rows="2" style="resize:vertical;"><?= sanitize(getSetting('deskripsi_singkat', 'Supplier sayuran dan buah segar untuk warung dan pedagang pasar.')) ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Deskripsi Lengkap</label>
        <textarea class="form-input" name="deskripsi_lengkap" rows="4" style="resize:vertical;"><?= sanitize(getSetting('deskripsi_lengkap')) ?></textarea>
      </div>
    </div>

    <div class="form-card">
      <div style="font-size:14px;font-weight:600;color:var(--n-800);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--n-100);">Informasi Kontak</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">No. HP / WhatsApp</label>
          <input class="form-input" type="text" name="no_hp" value="<?= sanitize(getSetting('no_hp', '0812-3456-7890')) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email Usaha</label>
          <input class="form-input" type="email" name="email_usaha" value="<?= sanitize(getSetting('email_usaha', 'info@suppliersayur.com')) ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Alamat</label>
        <textarea class="form-input" name="alamat" rows="2" style="resize:vertical;"><?= sanitize(getSetting('alamat', 'Jl. H. Salihun No. 33, RT03, RW03, Kebon Jeruk, Jakarta Barat, DKI Jakarta')) ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Jam Operasional</label>
        <input class="form-input" type="text" name="jam_operasional" value="<?= sanitize(getSetting('jam_operasional', 'Senin–Sabtu, 04.00–20.00 WIB')) ?>">
      </div>
    </div>
  </div>

  <!-- Kanan: Preview & Tombol -->
  <div>
    <div class="form-card" style="position:sticky;top:80px;">
      <div style="font-size:13px;font-weight:600;color:var(--n-700);margin-bottom:12px;">Preview Info</div>
      <div style="background:var(--brand-50);border-radius:10px;padding:14px;font-size:12px;color:var(--n-600);line-height:1.8;">
        <div style="font-weight:700;color:var(--brand-700);font-size:14px;margin-bottom:4px;"><?= sanitize(getSetting('nama_usaha', APP_NAME)) ?></div>
        <div style="color:var(--n-400);margin-bottom:8px;"><?= sanitize(getSetting('tagline','Sayuran Segar Berkualitas')) ?></div>
        <div><?= sanitize(getSetting('no_hp','0812-3456-7890')) ?></div>
        <div><?= sanitize(getSetting('jam_operasional','Senin–Sabtu, 04.00–20.00 WIB')) ?></div>
      </div>
      <div style="margin-top:14px;">
        <button type="submit" class="btn btn-primary btn-full">Simpan Pengaturan</button>
        <a href="<?= APP_URL ?>/tentang.php" target="_blank" class="btn btn-ghost btn-full" style="margin-top:8px;">Lihat Halaman</a>
      </div>
    </div>
  </div>

</div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
