<?php
if (!defined('APP_URL')) require_once __DIR__ . '/config.php';
?>
<footer class="footer">
  <div class="footer-grid">
    <div>
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
        <img src="<?= APP_URL ?>/assets/img/logo.png" alt="Logo Azam Heri Supplier"
             style="width:64px;height:64px;object-fit:contain;border-radius:50%;background:white;padding:6px;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
        <div>
          <div class="footer-brand-name" style="margin-bottom:2px;">Azam Heri Supplier</div>
          <div style="font-size:12px;color:var(--slate-500);">Sayuran Segar Berkualitas</div>
        </div>
      </div>
      <div class="footer-brand-desc">Menyediakan sayuran segar berkualitas tinggi langsung dari sumber terpercaya untuk warung dan usaha kuliner Anda di seluruh Jakarta.</div>
      <div class="footer-contact">
        <span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span> Jl. H. Salihun No. 33, RT03, RW03, Kebon Jeruk, Jakarta Barat, DKI Jakarta<br>
        <span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span> <a href="https://wa.me/6281234567890?text=Halo%2C%20saya%20ingin%20bertanya%20tentang%20produk%20sayuran%20Anda." target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:none;border-bottom:1px dashed rgba(255,255,255,0.3);transition:border-color 0.2s;" onmouseover="this.style.borderBottomColor='rgba(255,255,255,0.8)'" onmouseout="this.style.borderBottomColor='rgba(255,255,255,0.3)'">0812-3456-7890</a><br>
        <span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span> info@suppliersayur.com
      </div>
    </div>
    <div>
      <div class="footer-col-title">Menu</div>
      <a class="footer-link" href="<?= APP_URL ?>">Home</a>
      <a class="footer-link" href="<?= APP_URL ?>/produk.php">Produk</a>
      <?php if (!isLoggedIn()): ?>
      <a class="footer-link" href="#" onclick="showLoginModal('chat');return false;">Chat Supplier</a>
      <?php else: ?>
      <a class="footer-link" href="<?= APP_URL ?>/chat.php">Chat Supplier</a>
      <?php endif; ?>
      <a class="footer-link" href="<?= APP_URL ?>/#kontak">Kontak</a>
      <a class="footer-link" href="<?= APP_URL ?>/#tentang">Tentang Kami</a>
    </div>
    <div>
      <div class="footer-col-title">Akun</div>
      <?php if (isLoggedIn()): ?>
        <?php if (!isAdmin()): ?>
        <a class="footer-link" href="<?= APP_URL ?>/pesanan.php">Pesanan Saya</a>
        <?php endif; ?>
        <a class="footer-link" href="<?= APP_URL ?>/profil.php">Profil</a>
        <a class="footer-link" href="<?= APP_URL ?>/logout.php">Keluar</a>
      <?php else: ?>
        <a class="footer-link" href="<?= APP_URL ?>/login.php">Login</a>
        <a class="footer-link" href="<?= APP_URL ?>/register.php">Daftar</a>
      <?php endif; ?>
    </div>
    <div>
      <div class="footer-col-title">Info</div>
      <a class="footer-link" href="#">Cara Pemesanan</a>
      <a class="footer-link" href="#">Syarat &amp; Ketentuan</a>
      <a class="footer-link" href="#">FAQ</a>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-copy">&copy; <?= date('Y') ?> Azam Heri Supplier. All rights reserved.</div>
    <div class="footer-socials">
      <button class="social-btn" title="Facebook">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
      </button>
      <button class="social-btn" title="Instagram">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
      </button>
      <button class="social-btn" title="WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
      </button>
    </div>
  </div>
</footer>

<?php if (!isLoggedIn()): ?>
<!-- Modal Login Required -->
<div id="modal-login-required" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:9999;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this)closeLoginModal()">
  <div style="background:white;border-radius:20px;width:100%;max-width:360px;box-shadow:0 20px 60px rgba(0,0,0,0.2);overflow:hidden;animation:modalIn .2s ease;">
    <div style="background:linear-gradient(135deg,#16a34a,#15803d);padding:28px 24px 24px;text-align:center;">
      <div style="width:60px;height:60px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div style="font-size:18px;font-weight:800;color:white;margin-bottom:6px;">Harap Login Terlebih Dahulu</div>
      <div id="modal-login-desc" style="font-size:13px;color:rgba(255,255,255,0.85);line-height:1.5;">Silakan masuk ke akun Anda untuk menggunakan fitur ini.</div>
    </div>
    <div style="padding:24px;">
      <a href="<?= APP_URL ?>/login.php" id="modal-login-btn"
         style="display:block;width:100%;padding:13px;background:var(--green-600);color:white;font-size:15px;font-weight:700;border-radius:12px;text-align:center;text-decoration:none;margin-bottom:10px;transition:background 0.2s;"
         onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='var(--green-600)'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" style="vertical-align:middle;margin-right:6px;"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Login / Masuk
      </a>
      <a href="<?= APP_URL ?>/register.php"
         style="display:block;width:100%;padding:11px;background:white;color:var(--green-700);font-size:13px;font-weight:600;border-radius:12px;text-align:center;text-decoration:none;border:1.5px solid var(--green-200);">
        Belum punya akun? <strong>Daftar sekarang</strong>
      </a>
      <button onclick="closeLoginModal()" style="display:block;width:100%;margin-top:8px;padding:10px;background:none;border:none;color:var(--slate-400);font-size:13px;cursor:pointer;font-family:var(--font-body);">Batal</button>
    </div>
  </div>
</div>
<style>
@keyframes modalIn {
  from { opacity:0; transform:scale(0.93) translateY(10px); }
  to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>
<script>
function showLoginModal(fitur, redirectUrl) {
  const desc = {
    'keranjang': 'Tambahkan produk ke keranjang belanja Anda.',
    'chat':      'Gunakan fitur chat untuk berkomunikasi dengan supplier.',
    'pesanan':   'Lihat dan kelola pesanan Anda.',
  };
  document.getElementById('modal-login-desc').textContent =
    (desc[fitur] || 'Silakan masuk untuk menggunakan fitur ini.');
  if (redirectUrl) {
    document.getElementById('modal-login-btn').href = '<?= APP_URL ?>/login.php?redirect=' + encodeURIComponent(redirectUrl);
  } else {
    document.getElementById('modal-login-btn').href = '<?= APP_URL ?>/login.php';
  }
  const modal = document.getElementById('modal-login-required');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeLoginModal() {
  document.getElementById('modal-login-required').style.display = 'none';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeLoginModal();
});
</script>
<?php endif; ?>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
