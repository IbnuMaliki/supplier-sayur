<?php
$_configPath = dirname(dirname(__DIR__)) . '/includes/config.php';
if (!defined('APP_URL')) require_once $_configPath;
requireAdmin();
$flash     = getFlash();
$adminPage = basename($_SERVER['PHP_SELF'], '.php');

// Notifikasi badge
$notifPesanan = (int)$pdo->query("SELECT COUNT(*) FROM pesanan WHERE status='menunggu_bayar'")->fetchColumn();
$notifChat    = (int)$pdo->query("SELECT COUNT(*) FROM chat WHERE pengirim='customer' AND dibaca=0")->fetchColumn();
$notifTotal   = $notifPesanan + $notifChat;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — Admin | ' . APP_NAME : 'Admin | ' . APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/main.css">
<style>
body { padding-top: 70px; }
.admin-top-navbar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 70px;
  background: var(--slate-900); display: flex; align-items: center;
  padding: 0 24px; justify-content: space-between;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.admin-top-brand { display:flex; align-items:center; gap:12px; }
.admin-top-brand img { width:40px; height:40px; object-fit:contain; border-radius:50%; background:white; padding:3px; }
.admin-top-brand-text { font-family: var(--font-display); font-size: 15px; font-weight: 400; font-style: italic; color: var(--brand-300, #92cd9f); letter-spacing: -0.01em; }
.admin-top-brand-sub { font-size:11px; color:var(--slate-500); }
.admin-top-actions { display: flex; align-items: center; gap: 12px; }
.admin-top-user { display: flex; align-items: center; gap: 10px; color: var(--slate-300); font-size: 14px; }
</style>
</head>
<body>

<nav class="admin-top-navbar">
  <div class="admin-top-brand">
    <img src="<?= APP_URL ?>/assets/img/logo.png" alt="Logo">
    <div>
      <div class="admin-top-brand-text"><span class="icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span> Admin Panel</div>
      <div class="admin-top-brand-sub"><?= APP_NAME ?></div>
    </div>
  </div>
  <div class="admin-top-actions">
    <a href="<?= APP_URL ?>" style="border:1px solid rgba(255,255,255,0.2);color:var(--slate-300);background:transparent;padding:6px 14px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;">Lihat Toko</a>
    <div class="admin-top-user">
      <div style="width:34px;height:34px;border-radius:50%;background:var(--green-600);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:white;">
        <?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?>
      </div>
      <?= sanitize($_SESSION['nama'] ?? 'Admin') ?>
    </div>
    <a href="<?= APP_URL ?>/logout.php" style="background:var(--red-500);color:white;padding:6px 14px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;">Keluar</a>
  </div>
</nav>

<?php if ($flash): ?>
<div class="flash-message flash-<?= sanitize($flash['type']) ?>" id="flash-msg" style="top:80px;">
  <?= sanitize($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="admin-wrapper">
<div class="admin-sidebar">
  <div class="admin-sidebar-logo">
    <div class="admin-logo-text">Azam Heri Supplier</div>
    <div class="admin-logo-sub">Panel Administrasi</div>
  </div>
  <nav class="admin-nav">
    <a class="admin-nav-item <?= $adminPage==='index'       ?'active':'' ?>" href="<?= APP_URL ?>/admin/">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> Dashboard
    </a>
    <a class="admin-nav-item <?= $adminPage==='produk'      ?'active':'' ?>" href="<?= APP_URL ?>/admin/produk.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg> Kelola Produk
    </a>
    <a class="admin-nav-item <?= $adminPage==='pesanan'     ?'active':'' ?>" href="<?= APP_URL ?>/admin/pesanan.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Kelola Pesanan
    </a>
    <a class="admin-nav-item <?= $adminPage==='dokumentasi' ?'active':'' ?>" href="<?= APP_URL ?>/admin/dokumentasi.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg> Foto Dokumentasi
    </a>
    <a class="admin-nav-item <?= $adminPage==='laporan'     ?'active':'' ?>" href="<?= APP_URL ?>/admin/laporan.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg> Laporan
    </a>
    <a class="admin-nav-item <?= $adminPage==='customers'   ?'active':'' ?>" href="<?= APP_URL ?>/admin/customers.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Customers
    </a>
    <a class="admin-nav-item <?= $adminPage==='chat_admin'  ?'active':'' ?>" href="<?= APP_URL ?>/admin/chat_admin.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Pesan Masuk
    </a>
    <a class="admin-nav-item <?= $adminPage==='stok'?'active':'' ?>" href="<?= APP_URL ?>/admin/stok.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Riwayat Stok
    </a>
    <a class="admin-nav-item <?= $adminPage==='tentang'?'active':'' ?>" href="<?= APP_URL ?>/admin/tentang.php">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Tentang Kami
    </a>
    <a class="admin-nav-item <?= $adminPage==='pengaturan_hero'?'active':'' ?>" href="<?= APP_URL ?>/admin/pengaturan_hero.php">
      <svg class="admin-nav-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      Foto Hero
    </a>
  </nav>
</div>
<div class="admin-content">
