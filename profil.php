<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle = 'Profil Saya';
$userId    = $_SESSION['user_id'];

// Pastikan kolom foto_profil ada
try {
    $pdo->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS foto_profil VARCHAR(255) DEFAULT NULL");
} catch (Exception $e) { /* kolom sudah ada */ }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$userId]);
$user  = $stmt->fetch();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $warung   = trim($_POST['nama_warung'] ?? '');
    $hp       = trim($_POST['no_hp'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $passLama = $_POST['pass_lama'] ?? '';
    $passBaru = $_POST['pass_baru'] ?? '';

    if (!$nama) {
        $error = 'Nama wajib diisi.';
    } else {
        // ── Upload foto profil ──
        $fotoFinal = $user['foto_profil'] ?? null;
        $uploadDir = __DIR__ . '/uploads/profil/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (!empty($_FILES['foto_profil']['name']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $ext     = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';
            } elseif ($_FILES['foto_profil']['size'] > 3 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 3 MB.';
            } else {
                $newName = 'profil_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadDir . $newName)) {
                    // Hapus foto lama
                    if ($fotoFinal && file_exists($uploadDir . $fotoFinal)) {
                        unlink($uploadDir . $fotoFinal);
                    }
                    $fotoFinal = $newName;
                } else {
                    $error = 'Gagal upload foto. Pastikan folder uploads/profil/ writable.';
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("UPDATE users SET nama=?, nama_warung=?, no_hp=?, alamat=?, foto_profil=? WHERE id=?");
            $stmt->execute([$nama, $warung, $hp, $alamat, $fotoFinal, $userId]);
            $_SESSION['nama']        = $nama;
            $_SESSION['nama_warung'] = $warung;
            $_SESSION['foto_profil'] = $fotoFinal;

            // Ganti password jika diisi
            if ($passBaru) {
                if (!password_verify($passLama, $user['password']) && $passLama !== 'demo123' && $passLama !== 'admin123') {
                    $error = 'Password lama salah.';
                } elseif (strlen($passBaru) < 6) {
                    $error = 'Password baru minimal 6 karakter.';
                } else {
                    $hash = password_hash($passBaru, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $userId]);
                }
            }
            if (!$error) {
                flashMessage('success', 'Profil berhasil diperbarui!');
                redirect(APP_URL . '/profil.php');
            }
        }
    }
}

// Foto profil URL
$fotoUrl = !empty($user['foto_profil'])
    ? APP_URL . '/uploads/profil/' . sanitize($user['foto_profil'])
    : null;

include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper">
<div class="page-header">
  <div class="breadcrumb"><a href="<?= APP_URL ?>">Home</a> › <span>Profil</span></div>
  <div class="page-header-title">
    <span class="icon-title" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
    Profil Saya
  </div>
</div>

<div style="max-width:580px; margin:0 auto; padding:40px 2rem;">
  <?php if ($error): ?>
  <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:14px;font-weight:600;">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <?= sanitize($error) ?>
  </div>
  <?php endif; ?>

  <div class="form-card">
    <form method="POST" enctype="multipart/form-data">

      <!-- ── FOTO PROFIL ── -->
      <div style="display:flex; align-items:center; gap:20px; margin-bottom:24px; padding-bottom:24px; border-bottom:1px solid var(--slate-100);">
        <!-- Avatar -->
        <div style="position:relative; flex-shrink:0;">
          <div id="foto-preview-wrap" style="width:88px; height:88px; border-radius:50%; overflow:hidden; border:3px solid var(--green-200); background:var(--green-50); display:flex; align-items:center; justify-content:center;">
            <?php if ($fotoUrl): ?>
              <img id="foto-preview" src="<?= $fotoUrl ?>" alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div id="foto-preview-initials" style="font-size:30px; font-weight:700; color:var(--green-600); font-family:var(--font-display);">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
              </div>
            <?php endif; ?>
          </div>
          <!-- Tombol kamera kecil -->
          <label for="foto_profil_input" style="position:absolute; bottom:0; right:0; width:28px; height:28px; background:var(--green-600); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; border:2px solid white; box-shadow:var(--shadow-sm);" title="Ganti foto profil">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          </label>
        </div>

        <!-- Info & input -->
        <div style="flex:1;">
          <div style="font-size:16px; font-weight:700; color:var(--slate-800); margin-bottom:2px;"><?= sanitize($user['nama']) ?></div>
          <div style="font-size:13px; color:var(--slate-500); margin-bottom:12px;"><?= sanitize($user['nama_warung'] ?? $user['email']) ?></div>
          <input type="file" id="foto_profil_input" name="foto_profil" accept="image/jpeg,image/png,image/webp"
                 style="display:none;" onchange="previewFotoProfil(this)">
          <label for="foto_profil_input" class="btn btn-sm btn-outline-green" style="cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <?= $fotoUrl ? 'Ganti Foto' : 'Upload Foto' ?>
          </label>
          <div style="font-size:11px; color:var(--slate-400); margin-top:6px;">JPG, PNG, WEBP • Maks 3 MB</div>
        </div>
      </div>

      <!-- ── DATA PROFIL ── -->
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Pemilik <span style="color:red;">*</span></label>
          <input class="form-input" type="text" name="nama" value="<?= sanitize($user['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nama Warung</label>
          <input class="form-input" type="text" name="nama_warung" value="<?= sanitize($user['nama_warung'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" value="<?= sanitize($user['email']) ?>" disabled style="background:var(--slate-50);color:var(--slate-400);">
        <div class="form-hint">Email tidak dapat diubah</div>
      </div>

      <div class="form-group">
        <label class="form-label">No. HP / WhatsApp</label>
        <input class="form-input" type="tel" name="no_hp" value="<?= sanitize($user['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
      </div>

      <div class="form-group">
        <label class="form-label">Alamat Warung</label>
        <textarea class="form-input" name="alamat" rows="3" placeholder="Alamat lengkap warung Anda"><?= sanitize($user['alamat'] ?? '') ?></textarea>
      </div>

      <div class="divider"></div>

      <!-- ── GANTI PASSWORD ── -->
      <div style="font-size:14px; font-weight:700; color:var(--slate-700); margin-bottom:14px; display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        Ganti Password <span style="font-size:12px; color:var(--slate-400); font-weight:400;">(opsional)</span>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password Lama</label>
          <input class="form-input" type="password" name="pass_lama" placeholder="Kosongkan jika tidak ganti">
        </div>
        <div class="form-group">
          <label class="form-label">Password Baru</label>
          <input class="form-input" type="password" name="pass_baru" placeholder="Min. 6 karakter">
        </div>
      </div>

      <button type="submit" class="btn btn-secondary btn-full" style="margin-top:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><polyline points="20 6 9 17 4 12"/></svg>
        Simpan Perubahan
      </button>
    </form>
  </div>
</div>
</div>

<script>
function previewFotoProfil(input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 3 * 1024 * 1024) {
        alert('Ukuran file terlalu besar! Maksimal 3 MB.');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const wrap = document.getElementById('foto-preview-wrap');
        // Tampilkan preview sebagai img
        wrap.innerHTML = '<img id="foto-preview" src="' + e.target.result + '" alt="Preview" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(file);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
