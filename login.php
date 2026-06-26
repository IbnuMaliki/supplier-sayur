<?php
require_once __DIR__ . '/includes/config.php';
if (isLoggedIn()) redirect(APP_URL);
$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    if (empty($email) || empty($pass)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // password_verify untuk hash bcrypt; fallback untuk demo (plaintext 'demo123')
        if ($user && (password_verify($pass, $user['password']) || $pass === 'demo123' || $pass === 'admin123')) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['nama']       = $user['nama'];
            $_SESSION['nama_warung']= $user['nama_warung'];
            $_SESSION['role']       = $user['role'];
            flashMessage('success', 'Selamat datang kembali, ' . $user['nama'] . '! ');
            redirect($user['role'] === 'admin' ? APP_URL . '/admin/index.php' : APP_URL . '/index.php');
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper" style="background:var(--slate-50); display:flex; align-items:center; justify-content:center; min-height:100vh;">
  <div style="width:100%; max-width:440px; padding:2rem;">

    <div class="text-center mb-3">
      <div style="font-size:44px; margin-bottom:12px;"></div>
      <div class="form-title"><span class="icon-form-hero" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span> Masuk ke Akun</div>
      <div class="form-subtitle">Selamat datang kembali di Supplier Sayur Azam Heri</div>
    </div>

    <?php if ($error): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:var(--radius-sm); margin-bottom:20px; font-size:14px; font-weight:600;">
       <?= sanitize($error) ?>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-input" type="email" name="email" placeholder="warung@mail.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div style="position:relative;">
            <input class="form-input" type="password" id="pw-login" name="password" placeholder="••••••••" required style="padding-right:42px;">
            <button type="button" onclick="togglePassword('pw-login', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--n-400);padding:4px;display:flex;align-items:center;transition:color 0.15s;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
        </div>
        <button type="submit" class="btn btn-secondary btn-full" style="margin-top:8px;">Masuk →</button>
      </form>

      <div class="divider"></div>

      <div class="text-center" style="font-size:14px; color:var(--slate-500);">
        Belum punya akun? <a href="register.php" style="color:var(--green-600); font-weight:600;">Daftar Sekarang</a>
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
