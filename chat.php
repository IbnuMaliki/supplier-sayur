<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();

// Admin tidak bisa menggunakan halaman chat customer
if (isAdmin()) {
    flashMessage('error', 'Admin tidak dapat menggunakan fitur chat customer. Gunakan menu Pesan Masuk di Admin Panel untuk membalas pesan.');
    redirect(APP_URL . '/admin/chat_admin.php');
}

$pageTitle = 'Chat Supplier';
$userId = $_SESSION['user_id'];

// Kirim pesan customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {
    $pesan = trim($_POST['pesan'] ?? '');
    if ($pesan !== '') {
        // Simpan pesan customer
        $stmt = $pdo->prepare("INSERT INTO chat (user_id, pengirim, pesan) VALUES (?,?,?)");
        $stmt->execute([$userId, 'customer', $pesan]);

        // Auto-reply hanya jika belum ada balasan supplier HARI INI
        $cekHariIni = $pdo->prepare("
            SELECT COUNT(*) FROM chat 
            WHERE user_id=? AND pengirim='supplier' 
            AND DATE(created_at) = CURDATE()
        ");
        $cekHariIni->execute([$userId]);
        $sudahBalasHariIni = (int)$cekHariIni->fetchColumn();

        if ($sudahBalasHariIni === 0) {
            // Balasan pertama hari ini: sapaan selamat datang
            $autoReply = 'Selamat datang di Supplier Sayur Azam Heri! Ada yang bisa kami bantu?';
            $stmt2 = $pdo->prepare("INSERT INTO chat (user_id, pengirim, pesan) VALUES (?,?,?)");
            $stmt2->execute([$userId, 'supplier', $autoReply]);
        }
        // Jika sudah pernah balas hari ini → tidak ada auto-reply lagi
    }
    redirect(APP_URL . '/chat.php');
}

// Ambil riwayat chat
$stmt = $pdo->prepare("SELECT * FROM chat WHERE user_id=? ORDER BY created_at ASC");
$stmt->execute([$userId]);
$messages = $stmt->fetchAll();

// Tandai pesan supplier sebagai dibaca
$pdo->prepare("UPDATE chat SET dibaca=1 WHERE user_id=? AND pengirim='supplier'")->execute([$userId]);

include __DIR__ . '/includes/header.php';
?>
<div class="page-wrapper">
<div class="chat-layout">
<!-- Sidebar -->
<div class="chat-sidebar">
  <div class="chat-sidebar-header"> Pesan</div>
  <div class="chat-contact active">
    <div class="contact-avatar" style="background:var(--green-600);">AH</div>
    <div>
      <div class="contact-name">Supplier Azam Heri</div>
      <div class="contact-last">● Online</div>
    </div>
  </div>
</div>

<!-- Area Chat -->
<div class="chat-main">
  <div class="chat-header">
    <div class="contact-avatar" style="background:var(--green-600);">AH</div>
    <div>
      <div style="font-size:15px; font-weight:700; color:var(--slate-800);">Supplier Sayur Azam Heri</div>
      <div class="supplier-online">● Online • Respon dalam hitungan menit</div>
    </div>
    <div style="margin-left:auto;">
      <a href="produk.php" class="btn btn-sm btn-outline-green"> Lihat Produk</a>
    </div>
  </div>

  <div class="chat-messages" id="chat-messages">
    <?php if (count($messages) === 0): ?>
    <div class="msg from-supplier">
      <div class="msg-bubble">Halo! Selamat datang di Supplier Sayur Azam Heri <br>Ada yang bisa kami bantu? Kami siap melayani kebutuhan sayuran segar untuk warung Anda!</div>
      <div class="msg-time">Baru saja</div>
    </div>
    <?php else: ?>
    <?php foreach ($messages as $msg): ?>
    <div class="msg from-<?= $msg['pengirim'] === 'customer' ? 'user' : 'supplier' ?>">
      <div class="msg-bubble"><?= nl2br(sanitize($msg['pesan'])) ?></div>
      <div class="msg-time"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <form method="POST">
    <div class="chat-input-wrap">
      <input class="chat-input" id="chat-input" type="text" name="pesan" placeholder="Ketik pesan..." autocomplete="off" required>
      <button type="submit" id="chat-send-btn" class="chat-send-btn">
        <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
      </button>
    </div>
  </form>
</div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
