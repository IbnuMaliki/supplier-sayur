<?php
require_once dirname(__DIR__) . '/includes/config.php';

// Support both POST form-data and JSON
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

$action   = $data['action'] ?? '';
$produkId = (int)($data['produk_id'] ?? 0);
$redirect = $data['redirect'] ?? null;

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.', 'redirect' => APP_URL . '/login.php']);
    exit;
}

if (isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Admin tidak dapat memesan produk.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Verifikasi produk ada
if ($produkId) {
    $pStmt = $pdo->prepare("SELECT * FROM produk WHERE id=? AND status='aktif'");
    $pStmt->execute([$produkId]);
    $produk = $pStmt->fetch();
}

switch ($action) {
    case 'add':
        if (!$produk) {
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            exit;
        }
        if ($produk['stok'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Stok habis.']);
            exit;
        }
        // Cek sudah ada di keranjang
        $existing = $pdo->prepare("SELECT id, jumlah FROM keranjang WHERE user_id=? AND produk_id=?");
        $existing->execute([$userId, $produkId]);
        $row = $existing->fetch();
        if ($row) {
            $newQty = min($row['jumlah'] + 1, $produk['stok']);
            $pdo->prepare("UPDATE keranjang SET jumlah=? WHERE id=?")->execute([$newQty, $row['id']]);
        } else {
            $pdo->prepare("INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES (?,?,1)")->execute([$userId, $produkId]);
        }
        $count = getCartCount($pdo);
        if ($redirect) { flashMessage('success', $produk['nama'] . ' ditambahkan ke keranjang '); header('Location: ' . $redirect); exit; }
        echo json_encode(['success' => true, 'message' => 'Produk ditambahkan ke keranjang.', 'cart_count' => $count]);
        break;

    case 'update':
    case 'set':
        $delta  = (int)($data['delta'] ?? 0);
        $jumlah = (int)($data['jumlah'] ?? 0);
        $existing = $pdo->prepare("SELECT id, jumlah FROM keranjang WHERE user_id=? AND produk_id=?");
        $existing->execute([$userId, $produkId]);
        $row = $existing->fetch();
        if (!$row) { echo json_encode(['success' => false, 'message' => 'Item tidak ada di keranjang.']); exit; }

        $newQty = $jumlah ?: ($row['jumlah'] + $delta);
        if ($newQty <= 0) {
            $pdo->prepare("DELETE FROM keranjang WHERE id=?")->execute([$row['id']]);
        } else {
            $maxQty = $produk ? $produk['stok'] : 9999;
            $newQty = min($newQty, $maxQty);
            $pdo->prepare("UPDATE keranjang SET jumlah=? WHERE id=?")->execute([$newQty, $row['id']]);
        }
        if ($redirect) { header('Location: ' . $redirect); exit; }
        echo json_encode(['success' => true, 'cart_count' => getCartCount($pdo)]);
        break;

    case 'remove':
        $pdo->prepare("DELETE FROM keranjang WHERE user_id=? AND produk_id=?")->execute([$userId, $produkId]);
        if ($redirect) { flashMessage('success', 'Produk dihapus dari keranjang.'); header('Location: ' . $redirect); exit; }
        echo json_encode(['success' => true, 'cart_count' => getCartCount($pdo)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal.']);
}
