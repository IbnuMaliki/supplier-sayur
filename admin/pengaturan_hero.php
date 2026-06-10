<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Pengaturan Hero';

$uploadDir = dirname(__DIR__) . '/uploads/hero/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

try {
    $pdo->prepare("INSERT IGNORE INTO pengaturan (kunci,nilai) VALUES (?,?)")->execute(['hero_bg_file','']);
} catch(Exception $e) {}

// Hapus foto hero
if (isset($_GET['action']) && $_GET['action'] === 'hapus') {
    $old = $pdo->query("SELECT nilai FROM pengaturan WHERE kunci='hero_bg_file'")->fetchColumn();
    if ($old && file_exists($uploadDir.$old)) unlink($uploadDir.$old);
    $pdo->exec("UPDATE pengaturan SET nilai='' WHERE kunci='hero_bg_file'");
    flashMessage('success', 'Foto hero dihapus. Foto default akan digunakan.');
    redirect(APP_URL . '/admin/pengaturan_hero.php');
}

// Simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_FILES['hero_file']['name']) && $_FILES['hero_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['hero_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','webp']) && $_FILES['hero_file']['size'] <= 10*1024*1024) {
            $old = $pdo->query("SELECT nilai FROM pengaturan WHERE kunci='hero_bg_file'")->fetchColumn();
            if ($old && file_exists($uploadDir.$old)) unlink($uploadDir.$old);
            $newName = 'hero_'.time().'.'.$ext;
            if (move_uploaded_file($_FILES['hero_file']['tmp_name'], $uploadDir.$newName)) {
                $pdo->prepare("UPDATE pengaturan SET nilai=? WHERE kunci='hero_bg_file'")->execute([$newName]);
                flashMessage('success', 'Foto hero berhasil diupload!');
            } else {
                flashMessage('error', 'Gagal upload. Pastikan folder uploads/hero/ writable.');
            }
        } else {
            flashMessage('error', 'Format tidak didukung atau file >10MB. Gunakan JPG/PNG/WEBP.');
        }
    } else {
        flashMessage('error', 'Pilih file foto terlebih dahulu.');
    }
    redirect(APP_URL . '/admin/pengaturan_hero.php');
}

$heroFile = $pdo->query("SELECT nilai FROM pengaturan WHERE kunci='hero_bg_file'")->fetchColumn();
$heroUrl  = $heroFile ? APP_URL.'/uploads/hero/'.sanitize($heroFile) : '';
$defaultBg = 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80';

include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-top-bar">
  <div class="admin-page-title" style="margin-bottom:0;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:8px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    Foto Latar Homepage
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">

  <!-- Form Upload -->
  <div class="table-wrap">
    <div class="table-header"><div class="table-title">Upload Foto Latar Hero</div></div>
    <div style="padding:24px;">

      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius-md);padding:14px;margin-bottom:20px;font-size:13px;color:#1e40af;">
        <strong>Tips foto yang bagus:</strong><br>
        Gunakan foto sayuran segar, pasar, atau kebun. Ukuran ideal minimal <strong>1200×800px</strong>. Foto landscape (horizontal) lebih baik.
      </div>

      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Foto Latar <span style="color:var(--slate-400);font-weight:400;">(JPG/PNG/WEBP, maks 10 MB)</span></label>
          <label style="display:block;border:2.5px dashed var(--green-300);border-radius:var(--radius-lg);padding:36px 20px;text-align:center;cursor:pointer;background:var(--green-50);transition:all 0.25s;position:relative;"
                 onmouseover="this.style.borderColor='var(--green-500)';this.style.background='var(--green-100)'"
                 onmouseout="this.style.borderColor='var(--green-300)';this.style.background='var(--green-50)'">
            <input type="file" name="hero_file" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;"
                   onchange="previewHero(this)">
            <div id="upload-hint">
              <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--green-400)" stroke-width="1.5" style="display:block;margin:0 auto 10px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <div style="font-size:14px;font-weight:600;color:var(--green-700);">Klik atau drag &amp; drop foto</div>
              <div style="font-size:12px;color:var(--slate-500);margin-top:4px;">Foto sayuran/pasar/kebun</div>
            </div>
          </label>
        </div>
        <button type="submit" class="btn btn-secondary btn-full" style="font-size:15px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Upload &amp; Pasang Foto
        </button>
      </form>

      <?php if ($heroUrl): ?>
      <div style="margin-top:14px; display:flex; gap:10px;">
        <a href="<?= APP_URL ?>" target="_blank" class="btn btn-outline-green btn-sm" style="flex:1;justify-content:center;">
          Lihat Homepage
        </a>
        <a href="?action=hapus" class="btn btn-sm" style="flex:1;justify-content:center;background:var(--red-100);color:var(--red-800);border:none;"
           onclick="return confirm('Hapus foto ini? Foto default akan digunakan.')">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:3px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
          Pakai Foto Default
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Preview -->
  <div>
    <div style="font-size:13px;font-weight:600;color:var(--slate-600);margin-bottom:10px;">Preview tampilan hero:</div>
    <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-lg);position:relative;height:240px;">
      <img id="hero-preview-img" src="<?= $heroUrl ?: $defaultBg ?>" alt="Hero Preview"
           style="width:100%;height:100%;object-fit:cover;display:block;">
      <!-- Overlay simulasi -->
      <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(14,52,28,0.80) 0%,rgba(21,128,61,0.45) 60%,rgba(0,0,0,0.15) 100%);display:flex;flex-direction:column;justify-content:flex-end;padding:20px;">
        <div style="color:rgba(255,255,255,0.7);font-size:11px;letter-spacing:2px;text-transform:uppercase;margin-bottom:6px;">Supplier Terpercaya #1 Jakarta</div>
        <div style="color:white;font-size:22px;font-weight:700;font-family:var(--font-display);line-height:1.2;">Sayuran <span style="color:#a3e635;">Segar</span><br>Langsung dari Sumber</div>
      </div>
    </div>
    <div style="margin-top:10px;font-size:12px;color:var(--slate-500);text-align:center;">
      <?= $heroUrl ? 'Foto kustom aktif' : 'Menggunakan foto default' ?>
    </div>
  </div>
</div>

<script>
function previewHero(input) {
    if (!input.files[0]) return;
    if (input.files[0].size > 10*1024*1024) { alert('File terlalu besar! Maks 10 MB.'); input.value=''; return; }
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('hero-preview-img').src = e.target.result;
        document.getElementById('upload-hint').innerHTML =
            '<div style="font-size:14px;font-weight:700;color:var(--green-700);">'+input.files[0].name+'</div>'+
            '<div style="font-size:12px;color:var(--slate-500);margin-top:4px;">Klik untuk ganti</div>';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
