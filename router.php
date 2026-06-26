<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

if (file_exists($file) && !is_dir($file)) {
    return false; // serve file statik langsung
}

// Kalau URI adalah direktori, cari index.php di dalamnya
if (is_dir($file)) {
    $indexFile = rtrim($file, '/') . '/index.php';
    if (file_exists($indexFile)) {
        require $indexFile;
        exit;
    }
}
require __DIR__ . '/index.php';
