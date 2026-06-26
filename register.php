<?php
require_once __DIR__ . '/includes/config.php';
if (isLoggedIn()) redirect(APP_URL);
$pageTitle = 'Daftar';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $warung     = trim($_POST['nama_warung'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $hp         = trim($_POST['no_hp'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $pass       = $_POST['password'] ?? '';
    $pass2      = $_POST['password2'] ?? '';

    if (!$nama || !$email || !$pass) {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($pass !== $pass2) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Cek email sudah ada
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, nama_warung, email, password, no_hp, alamat) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nama, $warung, $email, $hash, $hp, $alamat]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id']     = $userId;
            $_SESSION['nama']        = $nama;
            $_SESSION['nama_warung'] = $warung;
            $_SESSION['role']        = 'customer';
            flashMessage('success', 'Akun berhasil dibuat! Selamat berbelanja ');
            redirect(APP_URL);
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper" style="background:var(--slate-50); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem 0;">
  <div style="width:100%; max-width:500px; padding:2rem;">

    <div class="text-center mb-3">
      <div style="font-size:44px; margin-bottom:12px;"></div>
      <div class="form-title"><span class="icon-form-hero" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Daftar Warung Baru</div>
      <div class="form-subtitle">Bergabung dan nikmati harga grosir terbaik untuk warung Anda</div>
    </div>

    <?php if ($error): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; font-size:14px; font-weight:600;">
       <?= sanitize($error) ?>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nama Pemilik <span style="color:red;">*</span></label>
            <input class="form-input" type="text" name="nama" placeholder="Nama lengkap" value="<?= sanitize($_POST['nama'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nama Warung</label>
            <input class="form-input" type="text" name="nama_warung" placeholder="Nama warung Anda" value="<?= sanitize($_POST['nama_warung'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Email <span style="color:red;">*</span></label>
          <input class="form-input" type="email" name="email" placeholder="warung@mail.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">No. HP / WhatsApp</label>
          <input class="form-input" type="tel" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= sanitize($_POST['no_hp'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Alamat Warung</label>
          <textarea class="form-input" name="alamat" rows="2" placeholder="Alamat lengkap warung Anda"><?= sanitize($_POST['alamat'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password <span style="color:red;">*</span></label>
            <div style="position:relative;">
              <input class="form-input" type="password" id="pw-reg" name="password" placeholder="Min. 6 karakter" required style="padding-right:42px;">
              <button type="button" onclick="togglePassword('pw-reg', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--n-400);padding:4px;display:flex;align-items:center;transition:color 0.15s;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Konfirmasi Password</label>
            <div style="position:relative;">
              <input class="form-input" type="password" id="pw-reg2" name="password2" placeholder="Ulangi password" required style="padding-right:42px;">
              <button type="button" onclick="togglePassword('pw-reg2', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--n-400);padding:4px;display:flex;align-items:center;transition:color 0.15s;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-secondary btn-full" style="margin-top:8px;">Daftar Sekarang →</button>
      </form>
      <div class="divider"></div>
      <div class="text-center" style="font-size:14px; color:var(--slate-500);">
        Sudah punya akun? <a href="login.php" style="color:var(--green-600); font-weight:600;">Masuk di sini</a>
      </div>
    </div>

  </div>
</div>

<script>
function togglePassword(inputId, btn) {
  var input = document.getElementById(inputId);
  var isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  btn.style.color = isHidden ? 'var(--brand-600)' : 'var(--n-400)';
  btn.innerHTML = isHidden ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
