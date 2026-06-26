<?php
// FILE TEST - bisa dihapus setelah berhasil
// Akses: http://localhost/web_programing%201/supplier-sayur/test.php

echo "<pre style='font-family:monospace;padding:20px;background:#f0fdf4;'>";
echo "<h2> Debug Info</h2>\n";

// Test 1: Config load
echo "=== 1. Load Config ===\n";
require_once __DIR__ . '/includes/config.php';
echo " Config loaded OK\n";
echo "APP_NAME  : " . APP_NAME . "\n";
echo "APP_URL   : " . APP_URL . "\n";
echo "APP_ROOT  : " . APP_ROOT . "\n";
echo "UPLOAD_PATH: " . UPLOAD_PATH . "\n";

// Test 2: Database
echo "\n=== 2. Database ===\n";
try {
    $result = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
    echo " DB Connected! Produk: $result rows\n";
    $result2 = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo " Users: $result2 rows\n";
    $result3 = $pdo->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
    echo " Kategori: $result3 rows\n";
} catch (Exception $e) {
    echo " DB Error: " . $e->getMessage() . "\n";
}

// Test 3: Session
echo "\n=== 3. Session ===\n";
echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? " Active" : " Inactive") . "\n";

// Test 4: Server info
echo "\n=== 4. Server Info ===\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SCRIPT_NAME  : " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "HTTP_HOST    : " . $_SERVER['HTTP_HOST'] . "\n";
echo "PHP version  : " . PHP_VERSION . "\n";

// Test 5: Folder & file exists
echo "\n=== 5. Folder Structure ===\n";
$checks = [
    'includes/config.php',
    'includes/header.php',
    'includes/footer.php',
    'assets/css/main.css',
    'assets/js/main.js',
    'uploads/produk/',
    'admin/index.php',
    'ajax/keranjang.php',
];
foreach ($checks as $c) {
    $path = __DIR__ . '/' . $c;
    $exists = file_exists($path);
    echo ($exists ? "" : "") . " $c\n";
}

echo "\n===  Semua OK! Hapus file test.php setelah selesai ===\n";
echo "</pre>";
