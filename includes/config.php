<?php
// ============================================================
// KONFIGURASI APLIKASI — Supplier Sayur Azam Heri
// ============================================================

// SESSION — harus paling awal sebelum output apapun
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', '/tmp/sessions');
    @mkdir('/tmp/sessions', 0777, true);
    session_start();
}

define('APP_NAME', 'Supplier Sayur Azam Heri');

// DATABASE — baca dari environment variable Railway, fallback ke XAMPP lokal
define('DB_HOST',    getenv('MYSQLHOST')     ?: 'localhost');
define('DB_USER',    getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS',    getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME',    getenv('MYSQLDATABASE') ?: 'supplier_sayur');
define('DB_PORT',    getenv('MYSQLPORT')     ?: '3306');
define('DB_CHARSET', 'utf8mb4');

// Koneksi PDO
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=".DB_CHARSET,
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error DB</title>
    <style>body{font-family:sans-serif;padding:40px;background:#fef2f2}
    .box{background:white;border-left:5px solid #ef4444;padding:24px;border-radius:8px;max-width:600px;margin:0 auto}
    h2{color:#991b1b}p{color:#555}code{background:#f3f4f6;padding:2px 6px;border-radius:4px}</style></head>
    <body><div class="box">
    <h2>&#10060; Koneksi Database Gagal</h2>
    <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
    <p>Pastikan:</p>
    <ul>
      <li>MySQL sudah <strong>Start</strong> di XAMPP Control Panel</li>
      <li>Database <code>supplier_sayur</code> sudah diimport via phpMyAdmin</li>
      <li>Username/password di <code>includes/config.php</code> sudah benar</li>
    </ul>
    </div></body></html>');
}

// AUTO-DETECT APP_URL
$_prot = 'http';
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_prot = 'https';
} elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $_prot = 'https';
} elseif (!empty($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'railway.app')) {
    $_prot = 'https';
}
$_host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_appAbs  = rtrim(str_replace('\\', '/', realpath(dirname(__DIR__))), '/');
$_docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
if (empty($_docRoot)) {
    $_docRoot = rtrim(str_replace('\\', '/', dirname(dirname(dirname(dirname(__DIR__))))), '/');
}
$_sub = str_replace($_docRoot, '', $_appAbs);
define('APP_URL',     $_prot . '://' . $_host . $_sub);
define('APP_ROOT',    $_appAbs);
define('UPLOAD_PATH', $_appAbs . '/uploads/produk/');
define('UPLOAD_URL',  APP_URL . '/uploads/produk/');
unset($_prot, $_host, $_appAbs, $_docRoot, $_sub);

// HELPER FUNCTIONS
function formatRupiah($angka) {
    return 'Rp ' . number_format((float)($angka ?? 0), 0, ',', '.');
}
function generateKodePesanan() {
    return 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
}
function generateVirtualAccount() {
    return '8801' . str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
}
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function requireLogin() {
    if (!isLoggedIn()) { header('Location: ' . APP_URL . '/login.php'); exit; }
}
function requireAdmin() {
    if (!isAdmin()) { header('Location: ' . APP_URL . '/index.php'); exit; }
}
function redirect($url) {
    header('Location: ' . $url); exit;
}
function sanitize($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function flashMessage($type, $msg) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f;
    }
    return null;
}
function getCartCount($pdo) {
    if (!isLoggedIn()) return 0;
    try {
        $s = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM keranjang WHERE user_id=?");
        $s->execute([$_SESSION['user_id']]);
        return (int)$s->fetchColumn();
    } catch (Exception $e) { return 0; }
}
function getProductImage($nama, $size = 400, $gambarDb = null) {
    if ($gambarDb && $gambarDb !== 'default.jpg') {
        return APP_URL . '/uploads/produk/' . $gambarDb;
    }
    $map = [
        'Cabai Merah Keriting' => 'photo-1596464716127-f2a82984de30',
        'Cabe Merah Keriting'  => 'photo-1596464716127-f2a82984de30',
        'Cabai Rawit Hijau'    => 'photo-1524593166156-312f362cada0',
        'Cabe Rawit Hijau'     => 'photo-1524593166156-312f362cada0',
        'Cabe Setan (Ori)'     => 'photo-1596464716127-f2a82984de30',
        'Timun Segar'          => 'photo-1568702846914-96b305d2aaeb',
        'Timun'                => 'photo-1568702846914-96b305d2aaeb',
        'Kol Putih'            => 'photo-1518779578993-ec3579fee39f',
        'Bayam Segar'          => 'photo-1576045057995-568f588f82fb',
        'Kangkung'             => 'photo-1603048588665-791ca8aea617',
        'Wortel'               => 'photo-1447175008436-054170c2e979',
        'Kentang'              => 'photo-1518977676601-b53f82aba655',
        'Tomat Merah'          => 'photo-1592924357228-91a4daadcfea',
        'Tomat Segar'          => 'photo-1592924357228-91a4daadcfea',
        'Tomat Hijau'          => 'photo-1592924357228-91a4daadcfea',
        'Bawang Merah'         => 'photo-1587049352846-4a222e784d38',
        'Bawang Putih'         => 'photo-1587049352846-4a222e784d38',
        'Daun Singkong'        => 'photo-1598170845058-32b9d6a5da37',
        'Daun Pepaya'          => 'photo-1598170845058-32b9d6a5da37',
        'Daun Kemangi'         => 'photo-1598170845058-32b9d6a5da37',
        'Daun Pisang'          => 'photo-1598170845058-32b9d6a5da37',
        'Terong Ungu'          => 'photo-1603048276879-f7f209f8a5c6',
        'Terong'               => 'photo-1603048276879-f7f209f8a5c6',
        'Jeruk Peras'          => 'photo-1592502712628-a27b14d67c51',
        'Jeruk Nipis'          => 'photo-1592502712628-a27b14d67c51',
        'Jahe'                 => 'photo-1615485500704-8e990f9900f8',
        'Kunyit'               => 'photo-1615485500704-8e990f9900f8',
        'Laos (Lengkuas)'      => 'photo-1615485500704-8e990f9900f8',
        'Sereh'                => 'photo-1572635196237-14b3f281503f',
        'Alpukat'              => 'photo-1523049673857-eb18f1d7b578',
        'Buah Naga'            => 'photo-1550258987-190a2d41a8ba',
        'Stroberi'             => 'photo-1543528176-61b239494933',
        'Jambu Kristal'        => 'photo-1550258987-190a2d41a8ba',
    ];
    $id = $map[$nama] ?? 'photo-1540420773420-3366772f4999';
    return "https://images.unsplash.com/{$id}?w={$size}&q=80";
}
