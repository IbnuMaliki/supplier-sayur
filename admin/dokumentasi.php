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
        redirect(APP_URL . '/admin/dokumentasi.php' . ($id ? "?edit=$id" : ''));
    }

    $gambarFinal    = '';
    $tipeFinal      = 'upload';
    $gambarExisting = trim($_POST['gambar_existing'] ?? '');
    $tipeExisting   = trim($_POST['tipe_existing'] ?? 'upload');

    if (!empty($_FILES['gambar_upload']['name']) && $_FILES['gambar_upload']['error'] === UPLOAD_ERR_OK) {
        // === Ada file baru diupload ===
        $ext     = strtolower(pathinfo($_FILES['gambar_upload']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            flashMessage('error', 'Format tidak didukung. Gunakan JPG, PNG, atau WEBP.');
            redirect(APP_URL . '/admin/dokumentasi.php' . ($id ? "?edit=$id" : ''));
        }
        if ($_FILES['gambar_upload']['size'] > 5 * 1024 * 1024) {
            flashMessage('error', 'Ukuran file terlalu besar. Maksimal 5 MB.');
            redirect(APP_URL . '/admin/dokumentasi.php' . ($id ? "?edit=$id" : ''));
        }

        $newName = 'dok_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
        if (move_uploaded_file($_FILES['gambar_upload']['tmp_name'], $uploadDir . $newName)) {
            // Hapus file lama jika edit
            if ($id > 0 && $gambarExisting && $tipeExisting === 'upload') {
                $oldFile = $uploadDir . $gambarExisting;
                if (file_exists($oldFile)) unlink($oldFile);
            }
            $gambarFinal = $newName;
            $tipeFinal   = 'upload';
        } else {
            flashMessage('error', 'Upload gagal. Pastikan folder uploads/dokumentasi/ bisa ditulis (writable).');
            redirect(APP_URL . '/admin/dokumentasi.php' . ($id ? "?edit=$id" : ''));
        }

    } elseif ($gambarExisting) {
        // === Tidak ada upload baru, pakai gambar lama ===
        $gambarFinal = $gambarExisting;
        $tipeFinal   = $tipeExisting;

    } else {
        flashMessage('error', 'Foto wajib diupload.');
        redirect(APP_URL . '/admin/dokumentasi.php' . ($id ? "?edit=$id" : ''));
    }

    if ($id > 0) {
        $pdo->prepare("UPDATE dokumentasi SET judul=?,keterangan=?,gambar=?,tipe=?,urutan=?,aktif=? WHERE id=?")
            ->execute([$judul, $keterangan, $gambarFinal, $tipeFinal, $urutan, $aktif, $id]);
        flashMessage('success', ' Foto berhasil diperbarui! Cek hasilnya di homepage.');
    } else {
        $pdo->prepare("INSERT INTO dokumentasi (judul,keterangan,gambar,tipe,urutan,aktif) VALUES (?,?,?,?,?,?)")
            ->execute([$judul, $keterangan, $gambarFinal, $tipeFinal, $urutan, $aktif]);
        flashMessage('success', ' Foto baru berhasil ditambahkan!');
    }
    redirect(APP_URL . '/admin/dokumentasi.php');
}

// ── DATA ───────────────────────────────────────────────────
$editData = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM dokumentasi WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $editData = $s->fetch();
}
$daftarDok  = $pdo->query("SELECT * FROM dokumentasi ORDER BY urutan ASC, id ASC")->fetchAll();
$totalAktif = $pdo->query("SELECT COUNT(*) FROM dokumentasi WHERE aktif=1")->fetchColumn();

include __DIR__ . '/includes/admin_header.php';
?>

<style>
.upload-zone {
    border: 2.5px dashed var(--green-300); border-radius: var(--radius-lg);
    padding: 40px 20px; text-align: center; cursor: pointer;
    transition: all 0.25s; background: var(--green-50); position: relative;
}
.upload-zone:hover, .upload-zone.dragover {
    border-color: var(--green-500); background: var(--green-100);
}
.upload-zone input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.upload-zone-icon { font-size: 44px; margin-bottom: 12px; }
.upload-zone-text { font-size: 15px; font-weight: 600; color: var(--green-700); margin-bottom: 6px; }
.upload-zone-sub  { font-size: 13px; color: var(--slate-500); }
.step-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--green-600); color: white; font-size: 13px; font-weight: 700;
    flex-shrink: 0;
}
.step-row { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 18px; }
.step-content { font-size: 14px; color: var(--slate-700); line-height: 1.6; }
.step-content strong { color: var(--slate-900); }
.preview-box {
    position: relative; width: 100%; height: 180px;
    border-radius: var(--radius-md); overflow: hidden;
    border: 2px solid var(--green-200); background: var(--slate-100);
    display: flex; align-items: center; justify-content: center;
}
.preview-box img { width: 100%; height: 100%; object-fit: cover; }
.preview-placeholder { text-align: center; color: var(--slate-400); font-size: 13px; }
</style>

<div class="admin-top-bar">
  <div class="admin-page-title" style="margin-bottom:0;"> Foto Dokumentasi</div>
  <div style="display:flex;align-items:center;gap:12px;">
      <div style="font-size:13px;color:var(--slate-500);">
          <span style="font-weight:700;color:<?= $totalAktif>=5?'var(--amber-400)':'var(--green-600)' ?>;"><?= $totalAktif ?>/5</span> foto aktif ditampilkan
        </div>
      <?php if (!$editData): ?>
      <a href="#form-tambah" class="btn btn-secondary btn-sm">+ Tambah Foto</a>
      <?php endif; ?>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     PANDUAN SINGKAT
═══════════════════════════════════════════════ -->
<div style="background:white;border:1px solid var(--green-200);border-radius:var(--radius-lg);padding:22px 24px;margin-bottom:24px;">
  <div style="font-size:14px;font-weight:700;color:var(--green-800);margin-bottom:14px;"> Cara Ganti Foto Dokumentasi</div>
  <div class="step-row">
      <div class="step-badge">1</div>
      <div class="step-content">Scroll ke tabel di bawah → klik <strong> Ganti Foto</strong> pada foto yang ingin diganti</div>
  </div>
  <div class="step-row">
      <div class="step-badge">2</div>
      <div class="step-content">Form edit akan muncul → klik kotak upload / drag &amp; drop foto dari komputer atau HP Anda</div>
  </div>
  <div class="step-row">
      <div class="step-badge">3</div>
      <div class="step-content">Pastikan <strong>preview foto</strong> sudah benar → klik <strong> Simpan Perubahan</strong></div>
  </div>
  <div class="step-row" style="margin-bottom:0;">
      <div class="step-badge">4</div>
      <div class="step-content">Buka homepage Anda → foto baru sudah tampil otomatis </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════
     TABEL DAFTAR FOTO
═══════════════════════════════════════════════ -->
<div class="table-wrap" style="margin-bottom:32px;">
  <div class="table-header">
      <div class="table-title">Daftar Foto (<?= count($daftarDok) ?> foto)</div>
      <div style="font-size:12px;color:var(--slate-400);">Maksimal 5 foto aktif tampil di homepage</div>
  </div>
  <table class="data-table">
      <thead>
          <tr>
              <th style="width:110px;">Preview</th>
              <th>Judul Foto</th>
              <th>Keterangan</th>
              <th style="width:80px;text-align:center;">Urutan</th>
              <th style="width:110px;">Status</th>
              <th style="width:160px;">Aksi</th>
          </tr>
      </thead>
      <tbody>
      <?php if (count($daftarDok) === 0): ?>
          <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--slate-400);">
              Belum ada foto. Tambahkan foto pertama di bawah ↓
            </td></tr>
      <?php endif; ?>
      <?php foreach ($daftarDok as $i => $d):
            $src = $d['tipe'] === 'upload'
                ? APP_URL . '/uploads/dokumentasi/' . sanitize($d['gambar'])
                : sanitize($d['gambar']);
        ?>
          <tr style="<?= !$d['aktif'] ? 'opacity:0.5;' : '' ?>">
              <td>
                  <div style="position:relative;width:90px;height:60px;border-radius:6px;overflow:hidden;border:1px solid var(--slate-200);">
                      <img src="<?= $src ?>" alt="<?= sanitize($d['judul']) ?>"
                             style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:20px;background:var(--slate-100)\'></div>'">
                      <div style="position:absolute;top:3px;left:3px;background:var(--green-600);color:white;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">
                          <?= $i+1 ?>
                      </div>
                  </div>
              </td>
              <td>
                  <div style="font-weight:600;color:var(--slate-800);"><?= sanitize($d['judul']) ?></div>
                  <div style="font-size:11px;color:var(--slate-400);margin-top:3px;"> File upload lokal</div>
              </td>
              <td style="font-size:13px;color:var(--slate-500);"><?= sanitize($d['keterangan'] ?? '-') ?></td>
              <td style="text-align:center;">
                  <span style="font-size:18px;font-weight:700;color:var(--green-600);"><?= $d['urutan'] ?></span>
              </td>
              <td>
                  <a href="?action=toggle&id=<?= $d['id'] ?>"
                       style="display:inline-block;padding:4px 12px;border-radius:100px;font-size:12px;font-weight:700;text-decoration:none;
                              background:<?= $d['aktif']?'var(--green-100)':'var(--slate-100)' ?>;
                              color:<?= $d['aktif']?'var(--green-800)':'var(--slate-500)' ?>;">
                      <?= $d['aktif'] ? ' Tampil' : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Disembunyikan' ?>
                  </a>
              </td>
              <td>
                  <a href="?edit=<?= $d['id'] ?>#form-tambah" class="btn-action btn-edit"> Ganti Foto</a>
                  <a href="?action=delete&id=<?= $d['id'] ?>" class="btn-action btn-del"
                       onclick="return confirm('Hapus foto \'<?= addslashes(sanitize($d['judul'])) ?>\'?\nFoto akan dihapus permanen.')"></a>
              </td>
          </tr>
      <?php endforeach; ?>
      </tbody>
  </table>
</div>

<!-- ═══════════════════════════════════════════════
     FORM UPLOAD / EDIT
═══════════════════════════════════════════════ -->
<div class="table-wrap" id="form-tambah">
  <div class="table-header">
      <div class="table-title">
          <?php if ($editData): ?>
           Ganti Foto: <strong><?= sanitize($editData['judul']) ?></strong>
          <?php else: ?>
           Tambah Foto Baru
            <?php endif; ?>
      </div>
      <?php if ($editData): ?>
      <a href="<?= APP_URL ?>/admin/dokumentasi.php" class="btn btn-outline-green btn-sm"> Batal</a>
      <?php endif; ?>
  </div>

  <div style="padding:28px;">
      <form method="POST" enctype="multipart/form-data" id="form-dok">
          <input type="hidden" name="id"              value="<?= $editData['id'] ?? 0 ?>">
          <input type="hidden" name="gambar_existing" value="<?= sanitize($editData['gambar'] ?? '') ?>">
          <input type="hidden" name="tipe_existing"   value="<?= sanitize($editData['tipe'] ?? 'upload') ?>">

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start;">

              <!-- Kiri: Upload -->
              <div>
                  <div class="form-label" style="margin-bottom:10px;">
                       <?= $editData ? 'Upload Foto Baru (opsional jika tidak mau ganti)' : 'Upload Foto dari Komputer *' ?>
                  </div>

                  <div class="upload-zone" id="upload-zone"
                         ondragover="this.classList.add('dragover');event.preventDefault();"
                         ondragleave="this.classList.remove('dragover');"
                         ondrop="handleDrop(event);">
                      <input type="file" name="gambar_upload" id="file-input"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="handleFileSelect(this)">
                      <div id="upload-content">
                          <div class="upload-zone-icon"></div>
                          <div class="upload-zone-text">Klik di sini atau drag &amp; drop foto</div>
                          <div class="upload-zone-sub">JPG, PNG, WEBP • Maksimal 5 MB</div>
                      </div>
                  </div>

                  <div style="margin-top:8px;font-size:12px;color:var(--slate-400);text-align:center;">
                       Foto dari HP bisa langsung dipilih jika membuka di browser HP
                    </div>
              </div>

              <!-- Kanan: Preview -->
              <div>
                  <div class="form-label" style="margin-bottom:10px;"> Preview Foto</div>
                  <div class="preview-box" id="preview-box">
                      <?php if ($editData && $editData['gambar']): 
                            $prevSrc = $editData['tipe'] === 'upload'
                                ? APP_URL . '/uploads/dokumentasi/' . sanitize($editData['gambar'])
                                : sanitize($editData['gambar']);
                        ?>
                      <img id="preview-img" src="<?= $prevSrc ?>" alt="Preview">
                      <?php else: ?>
                      <div class="preview-placeholder" id="preview-placeholder">
                          <div style="font-size:36px;margin-bottom:8px;"></div>
                          <div>Preview foto akan muncul<br>setelah dipilih</div>
                      </div>
                      <img id="preview-img" src="" alt="Preview" style="display:none;width:100%;height:100%;object-fit:cover;">
                      <?php endif; ?>
                  </div>
                  <div id="file-info" style="margin-top:8px;font-size:12px;color:var(--green-700);font-weight:600;display:none;"></div>
              </div>
          </div>

          <!-- Detail Foto -->
          <div style="border-top:1px solid var(--slate-100);margin-top:24px;padding-top:24px;">
              <div style="font-size:14px;font-weight:700;color:var(--slate-700);margin-bottom:16px;"> Detail Foto</div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                  <div class="form-group">
                      <label class="form-label">Judul Foto <span style="color:red;">*</span></label>
                      <input class="form-input" type="text" name="judul"
                               placeholder="cth: Pemilihan Sayuran di Pasar"
                               value="<?= sanitize($editData['judul'] ?? '') ?>" required>
                      <div class="form-hint">Nama/deskripsi kegiatan pada foto ini</div>
                  </div>
                  <div class="form-group">
                      <label class="form-label">Keterangan (teks hover)</label>
                      <input class="form-input" type="text" name="keterangan"
                               placeholder="cth:  Pemilihan langsung di pasar"
                               value="<?= sanitize($editData['keterangan'] ?? '') ?>">
                      <div class="form-hint">Muncul saat mouse hover di atas foto</div>
                  </div>
                  <div class="form-group">
                      <label class="form-label">Urutan Tampil</label>
                      <input class="form-input" type="number" name="urutan" min="1" max="99"
                               value="<?= $editData['urutan'] ?? (count($daftarDok) + 1) ?>">
                      <div class="form-hint">1 = paling kiri, foto terbesar. 2-5 = foto kecil sebelah kanan</div>
                  </div>
                  <div class="form-group" style="display:flex;align-items:flex-end;">
                      <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 0;">
                          <input type="checkbox" name="aktif" value="1"
                                   <?= (!$editData || $editData['aktif']) ? 'checked' : '' ?>
                                 style="width:20px;height:20px;accent-color:var(--green-600);cursor:pointer;">
                          <div>
                              <div style="font-size:14px;font-weight:600;color:var(--slate-700);">Tampilkan di Homepage</div>
                              <div style="font-size:12px;color:var(--slate-400);">Centang agar foto muncul di halaman utama</div>
                          </div>
                      </label>
                  </div>
              </div>
          </div>

          <!-- Tombol Simpan -->
          <div style="display:flex;gap:12px;margin-top:8px;align-items:center;flex-wrap:wrap;">
              <button type="submit" class="btn btn-secondary" style="padding:13px 32px;font-size:15px;">
                  <?= $editData ? ' Simpan Perubahan' : ' Upload &amp; Tambahkan' ?>
              </button>
              <?php if ($editData): ?>
              <a href="<?= APP_URL ?>/admin/dokumentasi.php" class="btn btn-outline-green">Batal</a>
              <?php endif; ?>
              <div style="font-size:13px;color:var(--slate-400);margin-left:auto;">
                  Setelah simpan, cek hasilnya di <a href="<?= APP_URL ?>" target="_blank" style="color:var(--green-600);font-weight:600;">homepage ↗</a>
              </div>
          </div>
      </form>
  </div>
</div>

<script>
// Preview gambar sebelum upload
function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    showPreview(file);
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('upload-zone').classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) {
        alert('File bukan gambar. Gunakan JPG, PNG, atau WEBP.');
        return;
    }
    // Set file ke input
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('file-input').files = dt.files;
    showPreview(file);
}

function showPreview(file) {
    // Validasi ukuran
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 5 MB.\nFile Anda: ' + (file.size/1024/1024).toFixed(1) + ' MB');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const img    = document.getElementById('preview-img');
        const ph     = document.getElementById('preview-placeholder');
        const zone   = document.getElementById('upload-content');
        const info   = document.getElementById('file-info');

        img.src = e.target.result;
        img.style.display = 'block';
        if (ph) ph.style.display = 'none';

        // Update upload zone
        zone.innerHTML = `
            <div style="font-size:36px;margin-bottom:8px;"></div>
          <div style="font-size:14px;font-weight:700;color:var(--green-700);">${file.name}</div>
          <div style="font-size:12px;color:var(--slate-500);margin-top:4px;">Klik untuk ganti foto</div>`;

        // Info file
        info.style.display = 'block';
        info.innerHTML = ` ${file.name} · ${(file.size/1024).toFixed(0)} KB`;
    };
    reader.readAsDataURL(file);
}

// Scroll ke form jika mode edit
<?php if ($editData): ?>
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.getElementById('form-tambah').scrollIntoView({behavior:'smooth', block:'start'});
    }, 200);
});
<?php endif; ?>

// Auto-isi judul dari nama file (jika judul masih kosong)
document.getElementById('file-input').addEventListener('change', function() {
    const judulInput = document.querySelector('input[name="judul"]');
    if (!judulInput.value && this.files[0]) {
        let nama = this.files[0].name.replace(/\.[^.]+$/, '').replace(/[-_]/g, ' ');
        nama = nama.charAt(0).toUpperCase() + nama.slice(1);
        judulInput.value = nama;
    }
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
