<?php
// includes/header.php
if (!isset($pdo)) require_once __DIR__ . '/config.php';
$cartCount   = getCartCount($pdo);
$flash       = getFlash();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageT       = isset($pageTitle) ? sanitize($pageTitle) . ' — ' . APP_NAME : APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageT ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <a class="navbar-brand" href="<?= APP_URL ?>">
    <img class="navbar-logo-img" src="<?= APP_URL ?>/assets/img/logo.png" alt="Logo Azam Heri Supplier">
    <div>
      <div class="navbar-brand-text">Azam Heri Supplier</div>
      <div class="navbar-brand-sub">Sayuran Segar Berkualitas</div>
    </div>
  </a>
  <button class="navbar-toggle" id="navbar-toggle" onclick="toggleNavbar()" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
  <div class="navbar-nav">
    <a class="nav-link <?= $currentPage==='index'?'active':'' ?>" href="<?= APP_URL ?>">Home</a>
    <a class="nav-link <?= $currentPage==='produk'?'active':'' ?>" href="<?= APP_URL ?>/produk.php">Produk</a>
    <?php if (isLoggedIn() && !isAdmin()): ?>
    <a class="nav-link <?= $currentPage==='chat'?'active':'' ?>" href="<?= APP_URL ?>/chat.php">Chat</a>
    <?php endif; ?>
    <?php if (isAdmin()): ?>
    <a class="nav-link" href="<?= APP_URL ?>/admin/index.php">Admin Panel</a>
    <?php endif; ?>
    <?php if (isLoggedIn() && !isAdmin()): ?>
    <a class="nav-link <?= $currentPage==='pesanan'?'active':'' ?>" href="<?= APP_URL ?>/pesanan.php">Riwayat Pesanan</a>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
      <?php if (!isAdmin()): ?>
      <a class="cart-btn" href="<?= APP_URL ?>/keranjang.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <div class="cart-badge"><?= $cartCount ?></div>
      </a>
      <?php endif; ?>
      <div class="user-dropdown">
        <button class="user-avatar-btn" onclick="toggleUserMenu()" style="overflow:hidden; padding:0;">
          <?php
          $foto = $_SESSION['foto_profil'] ?? null;
          if (!isset($_SESSION['foto_profil'])) {
              $uStmt = $pdo->prepare("SELECT foto_profil FROM users WHERE id=?");
              $uStmt->execute([$_SESSION['user_id']]);
              $foto = $uStmt->fetchColumn();
              $_SESSION['foto_profil'] = $foto;
          }
          if ($foto):
          ?>
            <img src="<?= APP_URL ?>/uploads/profil/<?= sanitize($foto) ?>"
                 alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
          <?php else: ?>
            <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
          <?php endif; ?>
        </button>
        <div class="user-menu" id="user-menu">
          <div class="user-menu-header">
            <div class="user-menu-name"><?= sanitize($_SESSION['nama'] ?? '') ?></div>
            <div class="user-menu-email"><?= sanitize($_SESSION['nama_warung'] ?? (isAdmin() ? 'Administrator' : 'Customer')) ?></div>
          </div>
          <div class="user-menu-divider"></div>
          <a class="user-menu-item" href="<?= APP_URL ?>/profil.php"><span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span> Profil Saya</a>
          <div class="user-menu-divider"></div>
          <a class="user-menu-item danger" href="<?= APP_URL ?>/logout.php"><span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span> Keluar</a>
        </div>
      </div>
    <?php else: ?>
      <a class="cart-btn" href="#" onclick="showLoginModal('keranjang');return false;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <div class="cart-badge">0</div>
      </a>
      <a class="nav-link" href="<?= APP_URL ?>/login.php">Masuk</a>
      <a class="nav-link special" href="<?= APP_URL ?>/register.php">Daftar</a>
    <?php endif; ?>
  </div>
</nav>

<!-- FLASH MESSAGE -->
<?php if ($flash): ?>
<div class="flash-message flash-<?= sanitize($flash['type']) ?>" id="flash-msg">
  <?= $flash['type'] === 'success' ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' ?> <?= sanitize($flash['msg']) ?>
</div>
<?php endif; ?>

<script>
function toggleNavbar() {
  const nav = document.querySelector('.navbar-nav');
  const btn = document.getElementById('navbar-toggle');
  nav.classList.toggle('open');
  btn.classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const nav = document.querySelector('.navbar-nav');
  const btn = document.getElementById('navbar-toggle');
  if (!nav.contains(e.target) && !btn.contains(e.target)) {
    nav.classList.remove('open');
    btn.classList.remove('open');
  }
});
</script>
