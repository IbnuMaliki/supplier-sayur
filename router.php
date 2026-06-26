<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

if (file_exists($file) && !is_dir($file)) {
    return false; // serve file statik langsung
}

require __DIR__ . '/index.php';
