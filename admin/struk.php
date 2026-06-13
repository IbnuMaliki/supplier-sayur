<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: pesanan.php'); exit; }

// Ambil data pesanan
$stmt = $pdo->prepare("
    SELECT p.*, u.nama as nama_user, u.nama_warung, u.no_hp as hp_user, u.email as email_user
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { header('Location: pesanan.php'); exit; }

// Ambil detail produk
$dStmt = $pdo->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id = ? ORDER BY id ASC");
$dStmt->execute([$id]);
$details = $dStmt->fetchAll();

$statusLabel = [
    'menunggu_bayar' => 'Menunggu Pembayaran',
    'diproses'       => 'Diproses',
    'dikirim'        => 'Dikirim',
    'selesai'        => 'Selesai',
    'dibatalkan'     => 'Dibatalkan',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk Pesanan <?= sanitize($p['kode_pesanan']) ?></title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Courier New', Courier, monospace;
    background: #f5f5f5;
    display: flex;
    justify-content: center;
    padding: 24px 16px;
  }
  .struk-wrap {
    background: white;
    width: 320px;
    padding: 24px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.1);
  }

  /* Header toko */
  .struk-header { text-align: center; margin-bottom: 14px; }
  .struk-logo { font-size: 15px; font-weight: bold; letter-spacing: 0.5px; }
  .struk-sub { font-size: 10px; color: #555; line-height: 1.6; margin-top: 4px; }

  .divider { border: none; border-top: 1px dashed #999; margin: 10px 0; }
  .divider-solid { border: none; border-top: 1px solid #333; margin: 10px 0; }

  /* Info pesanan */
  .info-row { display: flex; justify-content: space-between; font-size: 11px; margin: 3px 0; }
  .info-label { color: #666; }
  .info-val { font-weight: bold; text-align: right; max-width: 55%; word-break: break-word; }

  /* Tabel produk */
  .item-header {
    display: flex; justify-content: space-between;
    font-size: 10px; font-weight: bold; color: #666;
    text-transform: uppercase; margin-bottom: 6px;
  }
  .item-row { margin-bottom: 8px; }
  .item-name { font-size: 11px; font-weight: bold; }
  .item-detail {
    display: flex; justify-content: space-between;
    font-size: 11px; color: #444; margin-top: 1px;
  }

  /* Total */
  .total-row {
    display: flex; justify-content: space-between;
    font-size: 13px; font-weight: bold; margin: 4px 0;
  }
  .ongkir-row {
    display: flex; justify-content: space-between;
    font-size: 11px; color: #555; margin: 3px 0;
  }
  .total-big { font-size: 15px; color: #1a5c35; }

  /* VA box */
  .va-box {
    background: #f0fdf4;
    border: 1px dashed #4ade80;
    border-radius: 6px;
    padding: 10px;
    text-align: center;
    margin: 10px 0;
  }
  .va-label { font-size: 10px; color: #555; margin-bottom: 3px; }
  .va-number { font-size: 16px; font-weight: bold; letter-spacing: 2px; color: #166534; }
  .va-bank { font-size: 10px; color: #555; margin-top: 3px; }

  /* Footer */
  .struk-footer { text-align: center; margin-top: 14px; }
  .struk-footer p { font-size: 10px; color: #666; line-height: 1.8; }

  /* Tombol — tidak ikut tercetak */
  .print-actions {
    display: flex; gap: 10px; justify-content: center;
    margin-top: 20px;
  }
  .btn-print {
    background: #166534; color: white;
    border: none; padding: 10px 24px;
    border-radius: 6px; font-size: 13px;
    cursor: pointer; font-family: sans-serif;
  }
  .btn-back {
    background: #e2e8f0; color: #334155;
    border: none; padding: 10px 24px;
    border-radius: 6px; font-size: 13px;
    cursor: pointer; font-family: sans-serif;
    text-decoration: none; display: inline-block;
  }

  @media print {
    body { background: white; padding: 0; }
    .struk-wrap { box-shadow: none; border-radius: 0; }
    .print-actions { display: none; }
  }
</style>
</head>
<body>

<div>
  <div class="struk-wrap" id="struk">

    <!-- Header Toko -->
    <div class="struk-header">
      <div class="struk-logo">AZAM HERI SUPPLIER</div>
      <div class="struk-sub">
        Supplier Sayur Segar untuk Warung Anda<br>
        Jl. H. Salihun No. 33, Kebon Jeruk, Jakarta Barat<br>
        WA: 0812-3456-7890
      </div>
    </div>

    <hr class="divider-solid">

    <!-- Info Pesanan -->
    <div class="info-row">
      <span class="info-label">Kode</span>
      <span class="info-val"><?= sanitize($p['kode_pesanan']) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Tanggal</span>
      <span class="info-val"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Status</span>
      <span class="info-val"><?= $statusLabel[$p['status']] ?? $p['status'] ?></span>
    </div>

    <hr class="divider">

    <!-- Info Customer -->
    <div class="info-row">
      <span class="info-label">Pembeli</span>
      <span class="info-val"><?= sanitize($p['nama_user']) ?></span>
    </div>
    <?php if (!empty($p['nama_warung'])): ?>
    <div class="info-row">
      <span class="info-label">Warung</span>
      <span class="info-val"><?= sanitize($p['nama_warung']) ?></span>
    </div>
    <?php endif; ?>
    <div class="info-row">
      <span class="info-label">Penerima</span>
      <span class="info-val"><?= sanitize($p['nama_penerima']) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">No. HP</span>
      <span class="info-val"><?= sanitize($p['no_hp']) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Alamat</span>
      <span class="info-val"><?= sanitize($p['alamat_pengiriman']) ?></span>
    </div>
    <?php if (!empty($p['catatan'])): ?>
    <div class="info-row">
      <span class="info-label">Catatan</span>
      <span class="info-val" style="font-style:italic;"><?= sanitize($p['catatan']) ?></span>
    </div>
    <?php endif; ?>

    <hr class="divider">

    <!-- Detail Produk -->
    <div class="item-header">
      <span>Produk</span>
      <span>Subtotal</span>
    </div>
    <?php foreach ($details as $d): ?>
    <div class="item-row">
      <div class="item-name"><?= sanitize($d['nama_produk']) ?></div>
      <div class="item-detail">
        <span><?= $d['jumlah'] ?> × <?= formatRupiah($d['harga_satuan']) ?></span>
        <span><?= formatRupiah($d['subtotal']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>

    <hr class="divider">

    <!-- Total -->
    <div class="ongkir-row">
      <span>Ongkos Kirim</span>
      <span style="color:#16a34a; font-weight:bold;">GRATIS</span>
    </div>
    <div class="total-row total-big">
      <span>TOTAL</span>
      <span><?= formatRupiah($p['total_harga']) ?></span>
    </div>

    <hr class="divider">

    <!-- Metode Bayar / VA -->
    <div class="info-row">
      <span class="info-label">Metode Bayar</span>
      <span class="info-val"><?= sanitize($p['metode_bayar'] ?? '-') ?></span>
    </div>
    <?php if (!empty($p['nomor_va'])): ?>
    <div class="va-box">
      <div class="va-label">Nomor Virtual Account</div>
      <div class="va-number"><?= sanitize($p['nomor_va']) ?></div>
      <div class="va-bank"><?= sanitize($p['metode_bayar'] ?? '') ?></div>
    </div>
    <?php endif; ?>

    <hr class="divider-solid">

    <!-- Footer -->
    <div class="struk-footer">
      <p>
        Terima kasih sudah memesan!<br>
        Barang diantar pagi hari sesuai jadwal.<br>
        Pertanyaan? WA 0812-3456-7890<br><br>
        <strong>-- Azam Heri Supplier --</strong>
      </p>
    </div>

  </div>

  <!-- Tombol Aksi -->
  <div class="print-actions">
    <a class="btn-back" href="pesanan.php">← Kembali</a>
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
  </div>
</div>

</body>
</html>
