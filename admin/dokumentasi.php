<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Kelola Dokumentasi';

$uploadDir = dirname(__DIR__) . '/uploads/dokumentasi/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// ── HAPUS ──────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $row = $pdo->prepare("SELECT * FROM dokumentasi WHERE id=?");
    $row->execute([$delId]);
    $old = $row->fetch();
    if ($old && $old['tipe'] === 'upload' && file_exists($uploadDir . $old['gambar'])) {
        unlink($uploadDir . $old['gambar']);
    }
    $pdo->prepare("DELETE FROM dokumentasi WHERE id=?")->execute([$delId]);
    flashMessage('success', 'Foto berhasil dihapus.');
    redirect(APP_URL . '/admin/dokumentasi.php');
}

// ── TOGGLE AKTIF/NONAKTIF ──────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $pdo->prepare("UPDATE dokumentasi SET aktif = 1 - aktif WHERE id=?")->execute([(int)$_GET['id']]);
    flashMessage('success', 'Status foto diperbarui.');
    redirect(APP_URL . '/admin/dokumentasi.php');
}

// ── SIMPAN (tambah / edit) ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $judul      = trim($_POST['judul'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $urutan     = (int)($_POST['urutan'] ?? 0);
    $aktif      = isset($_POST['aktif']) ? 1 : 0;

    if (!$judul) {
        flashMessage('error', 'Judul foto wajib diisi.');
        redirect(APP_URL . '/admin/dokumentasi.php');
    }

    $gambarFinal    = '';
    $tipeFinal      = 'upload';
    $gambarExisting = trim($_POST['gambar_existing'] ?? '');
    $tipeExisting   = trim($_POST['tipe_existing'] ?? 'upload');

    if (!empty($_FILES['gambar_upload']['name']) && $_FILES['gambar_upload']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['gambar_upload']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            flashMessage('error', 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.');
            redirect(APP_URL . '/admin/dokumentasi.php');
        }
        if ($_FILES['gambar_upload']['size'] > 5 * 1024 * 1024) {
            flashMessage('error', 'Ukuran file terlalu besar. Maksimal 5 MB.');
            redirect(APP_URL . '/admin/dokumentasi.php');
        }
        $newName = 'dok_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($_FILES['gambar_upload']['tmp_name'], $uploadDir . $newName)) {
            if ($id > 0 && $gambarExisting && $tipeExisting === 'upload') {
                $oldFile = $uploadDir . $gambarExisting;
                if (file_exists($oldFile)) unlink($oldFile);
            }
            $gambarFinal = $newName;
            $tipeFinal   = 'upload';
        } else {
            flashMessage('error', 'Upload gagal.');
            redirect(APP_URL . '/admin/dokumentasi.php');
        }
    } elseif ($gambarExisting) {
        $gambarFinal = $gambarExisting;
        $tipeFinal   = $tipeExisting;
    } else {
        flashMessage('error', 'Foto wajib diupload.');
        redirect(APP_URL . '/admin/dokumentasi.php');
    }

    if ($id > 0) {
        $pdo->prepare("UPDATE dokumentasi SET judul=?,keterangan=?,gambar=?,tipe=?,urutan=?,aktif=? WHERE id=?")
            ->execute([$judul, $keterangan, $gambarFinal, $tipeFinal, $urutan, $aktif, $id]);
        flashMessage('success', 'Foto berhasil diperbarui!');
    } else {
        $pdo->prepare("INSERT INTO dokumentasi (judul,keterangan,gambar,tipe,urutan,aktif) VALUES (?,?,?,?,?,?)")
            ->execute([$judul, $keterangan, $gambarFinal, $tipeFinal, $urutan, $aktif]);
        flashMessage('success', 'Foto baru berhasil ditambahkan!');
    }
    redirect(APP_URL . '/admin/dokumentasi.php');
}

// ── DATA ───────────────────────────────────────────────────
$daftarDok  = $pdo->query("SELECT * FROM dokumentasi ORDER BY urutan ASC, id ASC")->fetchAll();
$totalAktif = $pdo->query("SELECT COUNT(*) FROM dokumentasi WHERE aktif=1")->fetchColumn();

include __DIR__ . '/includes/admin_header.php';
?>

<style>
.upload-zone {
    border: 2.5px dashed var(--green-300); border-radius: var(--radius-lg);
    padding: 32px 20px; text-align: center; cursor: pointer;
    transition: all 0.25s; background: var(--green-50); position: relative;
}
.upload-zone:hover, .upload-zone.dragover { border-color: var(--green-500); background: var(--green-100); }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.upload-zone-text { font-size: 14px; font-weight: 600; color: var(--green-700); margin-bottom: 4px; }
.upload-zone-sub  { font-size: 12px; color: var(--slate-500); }
.preview-box {
    width: 100%; height: 160px; border-radius: var(--radius-md); overflow: hidden;
    border: 2px solid var(--green-200); background: var(--slate-100);
    display: flex; align-items: center; justify-content: center;
}
.preview-box img { width: 100%; height: 100%; object-fit: cover; }
.dok-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 2000;
    align-items: center; justify-content: center; padding: 16px;
}
.dok-modal-overlay.show { display: flex; }
.dok-modal {
    background: white; border-radius: var(--radius-xl);
    width: 100%; max-width: 560px;
    max-height: 90vh; overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.dok-modal-header {
    padding: 18px 24px; border-bottom: 1px solid var(--slate-100);
    display: flex; justify-content: space-between; align-items: center;
    position: sticky; top: 0; background: white; z-index: 1;
}
.dok-modal-body { padding: 24px; }
.dok-modal-close {
    background: none; border: none; font-size: 22px;
    cursor: pointer; color: var(--slate-400); line-height: 1;
}
</style>

<div class="admin-top-bar">
  <div class="admin-page-title" style="margin-bottom:0;">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    Foto Dokumentasi
  </div>
  <div style="display:flex;align-items:center;gap:12px;">
    <div style="font-size:13px;color:var(--slate-500);">
      <span style="font-weight:700;color:<?= $totalAktif>=5?'var(--amber-400)':'var(--green-600)' ?>;"><?= $totalAktif ?>/5</span> foto aktif
    </div>
    <button onclick="bukaModalTambah()" class="btn btn-secondary btn-sm">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Foto
    </button>
  </div>
</div>

<div class="table-wrap">
  <div class="table-header">
    <div class="table-title">Daftar Foto (<?= count($daftarDok) ?> foto)</div>
    <div style="font-size:12px;color:var(--slate-400);">Maks. 5 foto aktif di homepage</div>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th style="width:90px;">Preview</th>
        <th>Judul Foto</th>
        <th>Keterangan</th>
        <th style="width:70px;text-align:center;">Urutan</th>
        <th style="width:100px;">Status</th>
        <th style="width:140px;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($daftarDok) === 0): ?>
      <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--slate-400);">Belum ada foto.</td></tr>
      <?php endif; ?>
      <?php foreach ($daftarDok as $i => $d):
        $src = $d['tipe'] === 'upload'
            ? APP_URL . '/uploads/dokumentasi/' . sanitize($d['gambar'])
            : sanitize($d['gambar']);
      ?>
      <tr style="<?= !$d['aktif'] ? 'opacity:0.5;' : '' ?>">
        <td>
          <div style="position:relative;width:72px;height:52px;border-radius:6px;overflow:hidden;border:1px solid var(--slate-200);">
            <img src="<?= $src ?>" alt="<?= sanitize($d['judul']) ?>" style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--slate-100)\'><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#94a3b8\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg></div>'">
            <div style="position:absolute;top:2px;left:2px;background:var(--green-600);color:white;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;"><?= $i+1 ?></div>
          </div>
        </td>
        <td><div style="font-weight:600;color:var(--slate-800);"><?= sanitize($d['judul']) ?></div></td>
        <td style="font-size:13px;color:var(--slate-500);"><?= sanitize($d['keterangan'] ?? '-') ?></td>
        <td style="text-align:center;font-size:16px;font-weight:700;color:var(--green-600);"><?= $d['urutan'] ?></td>
        <td>
          <a href="?action=toggle&id=<?= $d['id'] ?>"
             style="display:inline-block;padding:4px 10px;border-radius:100px;font-size:12px;font-weight:700;text-decoration:none;
                    background:<?= $d['aktif']?'var(--green-100)':'var(--slate-100)' ?>;
                    color:<?= $d['aktif']?'var(--green-800)':'var(--slate-500)' ?>;">
            <?= $d['aktif'] ? 'Tampil' : 'Disembunyikan' ?>
          </a>
        </td>
        <td>
          <button class="btn-action btn-edit"
            onclick="bukaModalEdit(<?= $d['id'] ?>, '<?= addslashes(sanitize($d['judul'])) ?>', '<?= addslashes(sanitize($d['keterangan']??'')) ?>', <?= $d['urutan'] ?>, <?= $d['aktif'] ?>, '<?= sanitize($d['gambar']) ?>', '<?= $d['tipe'] ?>', '<?= $src ?>')">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:3px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Ganti
          </button>
          <a href="?action=delete&id=<?= $d['id'] ?>" class="btn-action btn-del"
             onclick="return confirm('Hapus foto \'<?= addslashes(sanitize($d['judul'])) ?>\'?')">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            Hapus
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- MODAL -->
<div class="dok-modal-overlay" id="modalDok">
  <div class="dok-modal">
    <div class="dok-modal-header">
      <div style="font-size:16px;font-weight:700;" id="modal-title">Tambah Foto Baru</div>
      <button class="dok-modal-close" onclick="tutupModal()">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="dok-modal-body">
      <form method="POST" enctype="multipart/form-data" id="form-dok">
        <input type="hidden" name="id" id="input-id" value="0">
        <input type="hidden" name="gambar_existing" id="input-gambar-existing" value="">
        <input type="hidden" name="tipe_existing" id="input-tipe-existing" value="upload">

        <div class="form-group">
          <label class="form-label" id="label-upload">Upload Foto *</label>
          <div class="upload-zone" id="upload-zone"
               ondragover="this.classList.add('dragover');event.preventDefault();"
               ondragleave="this.classList.remove('dragover');"
               ondrop="handleDrop(event);">
            <input type="file" name="gambar_upload" id="file-input"
                   accept="image/jpeg,image/png,image/webp"
                   onchange="handleFileSelect(this)">
            <div id="upload-content">
              <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--green-400)" stroke-width="1.5" style="margin-bottom:10px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <div class="upload-zone-text">Klik atau drag & drop foto</div>
              <div class="upload-zone-sub">JPG, PNG, WEBP • Maks 5 MB</div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Preview Foto</label>
          <div class="preview-box" id="preview-box">
            <div id="preview-placeholder" style="text-align:center;color:var(--slate-400);font-size:13px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px;opacity:0.4;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <div>Preview muncul setelah foto dipilih</div>
            </div>
            <img id="preview-img" src="" alt="Preview" style="display:none;width:100%;height:100%;object-fit:cover;">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Judul Foto *</label>
          <input class="form-input" type="text" name="judul" id="input-judul" placeholder="cth: Pemilihan Sayuran di Pasar" required>
        </div>
        <div class="form-group">
          <label class="form-label">Keterangan (teks hover)</label>
          <input class="form-input" type="text" name="keterangan" id="input-keterangan" placeholder="cth: Pemilihan langsung di pasar">
        </div>
        <div class="form-group">
          <label class="form-label">Urutan Tampil</label>
          <input class="form-input" type="number" name="urutan" id="input-urutan" min="1" max="99" value="<?= count($daftarDok) + 1 ?>">
          <div class="form-hint">1 = paling kiri (foto besar). 2–5 = foto kecil kanan</div>
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="aktif" id="input-aktif" value="1" checked style="width:18px;height:18px;accent-color:var(--green-600);">
            <span style="font-size:14px;font-weight:600;color:var(--slate-700);">Tampilkan di Homepage</span>
          </label>
        </div>

        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-secondary" style="flex:1;" id="btn-simpan">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Upload & Tambahkan
          </button>
          <button type="button" class="btn btn-outline-green" onclick="tutupModal()">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function bukaModalTambah() {
  document.getElementById('modal-title').textContent = 'Tambah Foto Baru';
  document.getElementById('input-id').value = '0';
  document.getElementById('input-gambar-existing').value = '';
  document.getElementById('input-tipe-existing').value = 'upload';
  document.getElementById('input-judul').value = '';
  document.getElementById('input-keterangan').value = '';
  document.getElementById('input-urutan').value = <?= count($daftarDok) + 1 ?>;
  document.getElementById('input-aktif').checked = true;
  document.getElementById('label-upload').textContent = 'Upload Foto *';
  document.getElementById('btn-simpan').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Upload & Tambahkan';
  resetPreview();
  document.getElementById('modalDok').classList.add('show');
}

function bukaModalEdit(id, judul, keterangan, urutan, aktif, gambar, tipe, src) {
  document.getElementById('modal-title').textContent = 'Ganti Foto: ' + judul;
  document.getElementById('input-id').value = id;
  document.getElementById('input-gambar-existing').value = gambar;
  document.getElementById('input-tipe-existing').value = tipe;
  document.getElementById('input-judul').value = judul;
  document.getElementById('input-keterangan').value = keterangan;
  document.getElementById('input-urutan').value = urutan;
  document.getElementById('input-aktif').checked = aktif == 1;
  document.getElementById('label-upload').textContent = 'Upload Foto Baru (opsional)';
  document.getElementById('btn-simpan').innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><polyline points="20 6 9 17 4 12"/></svg> Simpan Perubahan';
  const img = document.getElementById('preview-img');
  const ph  = document.getElementById('preview-placeholder');
  img.src = src; img.style.display = 'block';
  ph.style.display = 'none';
  document.getElementById('modalDok').classList.add('show');
}

function tutupModal() {
  document.getElementById('modalDok').classList.remove('show');
}

function resetPreview() {
  const img = document.getElementById('preview-img');
  const ph  = document.getElementById('preview-placeholder');
  img.src = ''; img.style.display = 'none';
  ph.style.display = 'block';
  document.getElementById('upload-content').innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--green-400)" stroke-width="1.5" style="margin-bottom:10px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    <div class="upload-zone-text">Klik atau drag & drop foto</div>
    <div class="upload-zone-sub">JPG, PNG, WEBP • Maks 5 MB</div>`;
}

function handleFileSelect(input) {
  if (input.files[0]) showPreview(input.files[0]);
}

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('upload-zone').classList.remove('dragover');
  const file = e.dataTransfer.files[0];
  if (!file || !file.type.startsWith('image/')) { alert('File bukan gambar.'); return; }
  const dt = new DataTransfer();
  dt.items.add(file);
  document.getElementById('file-input').files = dt.files;
  showPreview(file);
}

function showPreview(file) {
  if (file.size > 5 * 1024 * 1024) { alert('Ukuran file terlalu besar! Maks 5 MB.'); return; }
  const reader = new FileReader();
  reader.onload = function(e) {
    const img  = document.getElementById('preview-img');
    const ph   = document.getElementById('preview-placeholder');
    const zone = document.getElementById('upload-content');
    img.src = e.target.result; img.style.display = 'block';
    if (ph) ph.style.display = 'none';
    zone.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--green-500)" stroke-width="2" style="margin-bottom:6px;"><polyline points="20 6 9 17 4 12"/></svg>
      <div style="font-size:13px;font-weight:700;color:var(--green-700);">${file.name}</div>
      <div style="font-size:11px;color:var(--slate-500);margin-top:3px;">Klik untuk ganti</div>`;
    const judulInput = document.getElementById('input-judul');
    if (!judulInput.value) {
      let nama = file.name.replace(/\.[^.]+$/, '').replace(/[-_]/g, ' ');
      judulInput.value = nama.charAt(0).toUpperCase() + nama.slice(1);
    }
  };
  reader.readAsDataURL(file);
}

document.getElementById('modalDok').addEventListener('click', function(e) {
  if (e.target === this) tutupModal();
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
