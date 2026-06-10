<?php
require_once dirname(__DIR__) . '/includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$ids    = $_GET['ids'] ?? '';
$idList = array_filter(array_map('intval', explode(',', $ids)));

if (empty($idList)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada ID']);
    exit;
}

// Buat placeholder
$placeholders = implode(',', array_fill(0, count($idList), '?'));
$params = array_merge($idList, [$userId]);

$stmt = $pdo->prepare("
    SELECT id, status, alasan_batal, dibatal_oleh, metode_bayar
    FROM pesanan
    WHERE id IN ($placeholders) AND user_id = ?
");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($rows as $r) {
    $result[$r['id']] = [
        'status'      => $r['status'],
        'alasan_batal'=> $r['alasan_batal'] ?? '',
        'dibatal_oleh'=> $r['dibatal_oleh'] ?? '',
        'metode_bayar'=> $r['metode_bayar'] ?? '',
    ];
}

echo json_encode(['success' => true, 'data' => $result]);
