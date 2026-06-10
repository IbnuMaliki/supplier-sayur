<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Kelola Produk';

// Handle aksi
$action = $_GET['action'] ?? '';
$editId = (int)($_GET['edit'] ?? 0);

// Hapus produk
if ($action === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    $pdo->prepare("UPDATE produk SET status='nonaktif' WHERE id=?")->execute([$delId]);
    flashMessage('success', 'Produk berhasil dinonaktifkan.');
    redirect(APP_URL . '/admin/produk.php');
}

// Simpan / Update produk
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = (int)($_POST['id'] ?? 0);
    $nama       = trim($_POST['nama'] ?? '');
    $katId      = (int)($_POST['kategori_id'] ?? 0);
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $harga      = (float)($_POST['harga'] ?? 0);
    $hargaGros  = (float)($_POST['harga_grosir'] ?? 0);
    $minGrosir  = (int)($_POST['min_grosir'] ?? 10);
    $stok       = (int)($_POST['stok'] ?? 0);
    $satuan     = trim($_POST['satuan'] ?? 'kg');
    $badge      = trim($_POST['badge'] ?? 'Segar');
    $status     = $_POST['status'] ?? 'aktif';

    if (!$nama || !$harga) {
        flashMessage('error', 'Nama dan harga wajib diisi.');
    } else {
        // Handle upload gambar
        $gambar = $_POST['gambar_existing'] ?? 'default.jpg';
        if (!empty($_FILES['gambar']['name'])) {
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $newName = 'produk_' . time() . '.' . $ext;
                $uploadDir = dirname(__DIR__) . '/uploads/produk/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . $newName)) {
                    $gambar = $newName;
                }
            }
        }

        if ($id > 0) {
            // Ambil stok lama untuk catat riwayat
            $stokLama = (int)$pdo->prepare("SELECT stok FROM produk WHERE id=?")->execute([$id]) ? (int)$pdo->query("SELECT stok FROM produk WHERE id=$id")->fetchColumn() : 0;
            $stmt = $pdo->prepare("UPDATE produk SET kategori_id=?,nama=?,deskripsi=?,harga=?,harga_grosir=?,min_grosir=?,stok=?,satuan=?,badge=?,status=?,gambar=? WHERE id=?");
            $stmt->execute([$katId, $nama, $deskripsi, $harga, $hargaGros, $minGrosir, $stok, $satuan, $badge, $status, $gambar, $id]);
            // Catat riwayat stok jika berubah
            if ($stok != $stokLama) {
                $jenis = $stok > $stokLama ? 'masuk' : 'keluar';
                $selisih = abs($stok - $stokLama);
                $pdo->prepare("INSERT INTO riwayat_stok (produk_id,jenis,jumlah,stok_awal,stok_akhir,keterangan) VALUES (?,?,?,?,?,?)")
                    ->execute([$id, $jenis, $selisih, $stokLama, $stok, 'Update stok via admin']);
            }
            flashMessage('success', 'Produk berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO produk (kategori_id,nama,deskripsi,harga,harga_grosir,min_grosir,stok,satuan,badge,status,gambar) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$katId, $nama, $deskripsi, $harga, $hargaGros, $minGrosir, $stok, $satuan, $badge, $status, $gambar]);
            $newProdukId = $pdo->lastInsertId();
            if ($stok > 0) {
                $pdo->prepare("INSERT INTO riwayat_stok (produk_id,jenis,jumlah,stok_awal,stok_akhir,keterangan) VALUES (?,?,?,?,?,?)")
                    ->execute([$newProdukId, 'masuk', $stok, 0, $stok, 'Stok awal produk baru']);
            }
            flashMessage('success', 'Produk berhasil ditambahkan.');
        }
        redirect(APP_URL . '/admin/produk.php');
    }
}

// Ambil data edit
$editData = null;
if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id=?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch();
}

// Ambil semua produk
$produkList = $pdo->query("
    SELECT p.*, k.nama as kategori_nama
    FROM produk p LEFT JOIN kategori k ON p.kategori_id=k.id
    ORDER BY p.status ASC, p.id DESC
")->fetchAll();

$kategori = $pdo->query("SELECT * FROM kategori ORDER BY id")->fetchAll();

include __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-top-bar">
<div class="admin-page-title" style="margin-bottom:0;"> Kelola Produk</div>
<button class="btn btn-secondary" onclick="bukaProdukModal()">
  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
  Tambah Produk
</button>
</div>

<!-- Tabel Produk -->
<div class="table-wrap" style="margin-bottom:32px;">
<div class="table-header">
  <div class="table-title">Daftar Produk (<?= count($produkList) ?>)</div>
</div>
<table class="data-table">
  <thead>
    <tr><th>Gambar</th><th>Nama Produk</th><th>Kategori</th><th>Harga/kg</th><th>Harga Grosir</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
  </thead>
  <tbody>
    <?php foreach ($produkList as $p): ?>
    <tr>
      <td><img src="<?= APP_URL ?>/uploads/produk/<?= sanitize($p['gambar']) ?>" onerror="this.src='<?= APP_URL ?>/assets/img/default-product.jpg'" class="img-thumb"></td>
      <td style="font-weight:600;"><?= sanitize($p['nama']) ?></td>
      <td>
        <?php if ($p['kategori_nama']): ?>
        <span style="background:var(--green-100);color:var(--green-800);padding:2px 10px;border-radius:100px;font-size:12px;font-weight:600;"><?= sanitize($p['kategori_nama']) ?></span>
        <?php endif; ?>
      </td>
      <td><?= formatRupiah($p['harga']) ?></td>
      <td><?= formatRupiah($p['harga_grosir']) ?></td>
      <td>
        <?php if ($p['stok'] == 0): ?>
        <span style="color:var(--red-500);font-weight:700;">Habis</span>
        <?php elseif ($p['stok'] <= 20): ?>
        <span style="color:var(--amber-400);font-weight:700;"><?= $p['stok'] ?> kg </span>
        <?php else: ?>
        <span style="color:var(--green-600);font-weight:600;"><?= $p['stok'] ?> kg</span>
        <?php endif; ?>
      </td>
      <td>
        <span style="background:<?= $p['status']==='aktif'?'var(--green-100)':'var(--slate-100)' ?>;color:<?= $p['status']==='aktif'?'var(--green-800)':'var(--slate-500)' ?>;padding:3px 10px;border-radius:100px;font-size:12px;font-weight:700;">
          <?= $p['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?>
        </span>
      </td>
      <td>
        <button class="btn-action btn-edit" onclick="editProdukModal(<?= $p['id'] ?>,<?= htmlspecialchars(json_encode($p['nama']),ENT_QUOTES) ?>,<?= $p['kategori_id']??0 ?>,<?= $p['harga'] ?>,<?= $p['harga_grosir'] ?>,<?= $p['min_grosir'] ?>,<?= $p['stok'] ?>,<?= htmlspecialchars(json_encode($p['satuan']),ENT_QUOTES) ?>,<?= htmlspecialchars(json_encode($p['badge']??''),ENT_QUOTES) ?>,<?= htmlspecialchars(json_encode($p['status']),ENT_QUOTES) ?>,<?= htmlspecialchars(json_encode($p['deskripsi']??''),ENT_QUOTES) ?>,<?= htmlspecialchars(json_encode($p['gambar']??''),ENT_QUOTES) ?>)">Edit</button>
        <a href="produk.php?action=delete&id=<?= $p['id'] ?>" class="btn-action btn-del" onclick="return confirmDelete('Nonaktifkan produk <?= addslashes($p['nama']) ?>?')">Hapus</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Modal Tambah / Edit Produk -->
<div id="modal-produk-admin" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9000;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)tutupProdukModal()">
  <div style="background:white;border-radius:18px;width:100%;max-width:680px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,0.18);animation:modalProdIn .2s ease;">
    <!-- Header modal -->
    <div style="padding:18px 24px;border-bottom:1px solid var(--n-150);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:var(--n-50);">
      <div style="font-size:16px;font-weight:700;color:var(--n-900);" id="modal-produk-title">Tambah Produk Baru</div>
      <button onclick="tutupProdukModal()" style="width:30px;height:30px;border-radius:99px;background:var(--n-200);border:none;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:var(--n-600);">×</button>
    </div>
    <!-- Body modal -->
    <div style="padding:24px;overflow-y:auto;flex:1;">
      <form method="POST" enctype="multipart/form-data" id="form-produk-modal">
        <input type="hidden" name="id" id="modal-id" value="0">
        <input type="hidden" name="gambar_existing" id="modal-gambar-existing" value="default.jpg">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="form-group">
            <label class="form-label">Nama Produk *</label>
            <input class="form-input" type="text" name="nama" id="modal-nama" required placeholder="Contoh: Cabai Merah Keriting">
          </div>
          <div class="form-group">
            <label class="form-label">Kategori</label>
            <select class="form-input" name="kategori_id" id="modal-kategori">
              <option value="">— Pilih Kategori —</option>
              <?php foreach ($kategori as $k): ?>
              <option value="<?= $k['id'] ?>"><?= sanitize($k['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Harga Normal /kg *</label>
            <input class="form-input" type="number" name="harga" id="modal-harga" placeholder="45000" required>
          </div>
          <div class="form-group">
            <label class="form-label">Harga Grosir /kg</label>
            <input class="form-input" type="number" name="harga_grosir" id="modal-harga-grosir" placeholder="40000">
          </div>
          <div class="form-group">
            <label class="form-label">Min. Qty Grosir (kg)</label>
            <input class="form-input" type="number" name="min_grosir" id="modal-min-grosir" value="10">
          </div>
          <div class="form-group">
            <label class="form-label">Stok (kg)</label>
            <input class="form-input" type="number" name="stok" id="modal-stok" value="0">
          </div>
          <div class="form-group">
            <label class="form-label">Satuan</label>
            <input class="form-input" type="text" name="satuan" id="modal-satuan" value="kg">
          </div>
          <div class="form-group">
            <label class="form-label">Badge Label</label>
            <input class="form-input" type="text" name="badge" id="modal-badge" placeholder="Best Seller, Segar, Promo...">
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select class="form-input" name="status" id="modal-status">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Upload Gambar</label>
            <input class="form-input" type="file" name="gambar" accept="image/*" onchange="previewImage(this,'modal-img-preview')" style="padding:8px;">
            <img id="modal-img-preview" src="" style="display:none;width:70px;height:70px;object-fit:cover;border-radius:8px;margin-top:8px;border:1px solid var(--n-200);">
          </div>
        </div>
        <div class="form-group" style="margin-top:4px;">
          <label class="form-label">Deskripsi Produk</label>
          <textarea class="form-input" name="deskripsi" id="modal-deskripsi" rows="3" style="resize:vertical;"></textarea>
        </div>
        <!-- Footer form -->
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;padding-top:14px;border-top:1px solid var(--n-100);">
          <button type="button" onclick="tutupProdukModal()" class="btn btn-ghost btn-sm">Batal</button>
          <button type="submit" class="btn btn-secondary" id="modal-submit-btn">Tambah Produk</button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
@keyframes modalProdIn {
  from { opacity:0; transform:translateY(14px) scale(0.97); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}
</style>
<script>
var APP_URL_PROD = '<?= APP_URL ?>';

function bukaProdukModal() {
  // Reset form untuk tambah baru
  document.getElementById('modal-produk-title').textContent = 'Tambah Produk Baru';
  document.getElementById('modal-submit-btn').textContent   = 'Tambah Produk';
  document.getElementById('modal-id').value           = '0';
  document.getElementById('modal-gambar-existing').value = 'default.jpg';
  document.getElementById('modal-nama').value         = '';
  document.getElementById('modal-harga').value        = '';
  document.getElementById('modal-harga-grosir').value = '';
  document.getElementById('modal-min-grosir').value   = '10';
  document.getElementById('modal-stok').value         = '0';
  document.getElementById('modal-satuan').value       = 'kg';
  document.getElementById('modal-badge').value        = 'Segar';
  document.getElementById('modal-deskripsi').value    = '';
  document.getElementById('modal-kategori').value     = '';
  document.getElementById('modal-status').value       = 'aktif';
  var prev = document.getElementById('modal-img-preview');
  prev.src = ''; prev.style.display = 'none';
  tampilModal();
}

function editProdukModal(id, nama, katId, harga, hargaGrosir, minGrosir, stok, satuan, badge, status, deskripsi, gambar) {
  document.getElementById('modal-produk-title').textContent = 'Edit Produk: ' + nama;
  document.getElementById('modal-submit-btn').textContent   = 'Simpan Perubahan';
  document.getElementById('modal-id').value                = id;
  document.getElementById('modal-gambar-existing').value   = gambar || 'default.jpg';
  document.getElementById('modal-nama').value              = nama;
  document.getElementById('modal-harga').value             = harga;
  document.getElementById('modal-harga-grosir').value      = hargaGrosir;
  document.getElementById('modal-min-grosir').value        = minGrosir;
  document.getElementById('modal-stok').value              = stok;
  document.getElementById('modal-satuan').value            = satuan;
  document.getElementById('modal-badge').value             = badge;
  document.getElementById('modal-deskripsi').value         = deskripsi;
  document.getElementById('modal-status').value            = status;
  // Set kategori
  var sel = document.getElementById('modal-kategori');
  for (var i=0; i<sel.options.length; i++) {
    if (sel.options[i].value == katId) { sel.selectedIndex = i; break; }
  }
  // Preview gambar
  var prev = document.getElementById('modal-img-preview');
  if (gambar && gambar !== 'default.jpg') {
    prev.src = APP_URL_PROD + '/uploads/produk/' + gambar;
    prev.style.display = 'block';
  } else {
    prev.src = ''; prev.style.display = 'none';
  }
  tampilModal();
}

function tampilModal() {
  var m = document.getElementById('modal-produk-admin');
  m.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  setTimeout(function(){ document.getElementById('modal-nama').focus(); }, 100);
}
function tutupProdukModal() {
  document.getElementById('modal-produk-admin').style.display = 'none';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') tutupProdukModal();
});

// Jika ada error validasi dari server, buka modal otomatis
<?php if ($editData): ?>
window.addEventListener('DOMContentLoaded', function() {
  editProdukModal(
    <?= $editData['id'] ?>,
    <?= json_encode($editData['nama']) ?>,
    <?= $editData['kategori_id']??0 ?>,
    <?= $editData['harga'] ?>,
    <?= $editData['harga_grosir'] ?>,
    <?= $editData['min_grosir'] ?>,
    <?= $editData['stok'] ?>,
    <?= json_encode($editData['satuan']) ?>,
    <?= json_encode($editData['badge']) ?>,
    <?= json_encode($editData['status']) ?>,
    <?= json_encode($editData['deskripsi']??'') ?>,
    <?= json_encode($editData['gambar']??'') ?>
  );
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
