<?php
require_once dirname(__DIR__) . '/includes/config.php';
requireAdmin();
$pageTitle = 'Pesan Masuk';

$selectedUserId = (int)($_GET['user_id'] ?? 0);

// Kirim balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedUserId) {
    $pesan = trim($_POST['pesan'] ?? '');
    if ($pesan) {
        $pdo->prepare("INSERT INTO chat (user_id, pengirim, pesan) VALUES (?,?,?)")->execute([$selectedUserId, 'supplier', $pesan]);
        $pdo->prepare("UPDATE chat SET dibaca=1 WHERE user_id=? AND pengirim='customer'")->execute([$selectedUserId]);
    }
    redirect(APP_URL . '/admin/chat_admin.php?user_id=' . $selectedUserId);
}

// Daftar user yang pernah chat
$chatUsers = $pdo->query("
    SELECT DISTINCT c.user_id, u.nama, u.nama_warung,
           (SELECT pesan FROM chat WHERE user_id=c.user_id ORDER BY created_at DESC LIMIT 1) as last_msg,
           (SELECT created_at FROM chat WHERE user_id=c.user_id ORDER BY created_at DESC LIMIT 1) as last_time,
           (SELECT COUNT(*) FROM chat WHERE user_id=c.user_id AND pengirim='customer' AND dibaca=0) as unread
    FROM chat c JOIN users u ON c.user_id=u.id
    ORDER BY last_time DESC
")->fetchAll();

// Pesan dari user terpilih
$messages = [];
if ($selectedUserId) {
    $stmt = $pdo->prepare("SELECT * FROM chat WHERE user_id=? ORDER BY created_at ASC");
    $stmt->execute([$selectedUserId]);
    $messages = $stmt->fetchAll();
    $pdo->prepare("UPDATE chat SET dibaca=1 WHERE user_id=? AND pengirim='customer'")->execute([$selectedUserId]);
}

include __DIR__ . '/includes/admin_header.php';
?>
<div class="admin-page-title"> Pesan Masuk</div>

<?php if ($selectedUserId && count($messages) > 0): ?>
<!-- MODE CHAT — mobile: full screen chat + tombol back -->
<div class="chat-layout" style="height:calc(100vh - 150px); border:1px solid var(--slate-200); border-radius:var(--radius-lg); overflow:hidden;">
  <!-- Sidebar users — tersembunyi di mobile saat chat aktif -->
  <div class="chat-sidebar">
    <div class="chat-sidebar-header">Semua Percakapan (<?= count($chatUsers) ?>)</div>
    <?php foreach ($chatUsers as $u): ?>
    <a href="chat_admin.php?user_id=<?= $u['user_id'] ?>" class="chat-contact <?= $selectedUserId===$u['user_id']?'active':'' ?>" style="text-decoration:none;">
      <div class="contact-avatar"><?= strtoupper(substr($u['nama'],0,1)) ?></div>
      <div style="flex:1;min-width:0;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div class="contact-name"><?= sanitize($u['nama']) ?></div>
          <?php if ($u['unread'] > 0): ?>
          <div style="background:var(--green-500);color:white;border-radius:50%;width:18px;height:18px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $u['unread'] ?></div>
          <?php endif; ?>
        </div>
        <div class="contact-last"><?= sanitize(substr($u['last_msg']??'',0,40)) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Chat area -->
  <div class="chat-main">
    <?php
    $userInfo = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $userInfo->execute([$selectedUserId]);
    $chatUser = $userInfo->fetch();
    ?>
    <div class="chat-header">
      <!-- Tombol back — hanya muncul di mobile -->
      <a href="chat_admin.php" class="chat-back-btn" title="Kembali">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
      <div class="contact-avatar"><?= strtoupper(substr($chatUser['nama'],0,1)) ?></div>
      <div>
        <div style="font-size:15px;font-weight:700;color:var(--slate-800);"><?= sanitize($chatUser['nama']) ?></div>
        <div style="font-size:12px;color:var(--slate-400);"><?= sanitize($chatUser['nama_warung']??$chatUser['email']) ?></div>
      </div>
    </div>
    <div class="chat-messages" id="chat-messages">
      <?php foreach ($messages as $m): ?>
      <div class="msg from-<?= $m['pengirim']==='supplier'?'user':'supplier' ?>">
        <div class="msg-bubble">
          <?php if ($m['pengirim']==='supplier'): ?><div style="font-size:10px;opacity:0.7;margin-bottom:4px;">Admin Supplier</div><?php endif; ?>
          <?= nl2br(sanitize($m['pesan'])) ?>
        </div>
        <div class="msg-time"><?= date('d M H:i', strtotime($m['created_at'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <form method="POST">
      <div class="chat-input-wrap">
        <input class="chat-input" id="chat-input" type="text" name="pesan" placeholder="Ketik balasan..." required autocomplete="off">
        <button type="submit" id="chat-send-btn" class="chat-send-btn">
          <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- MODE LIST — mobile: tampilkan daftar customer -->
<div class="chat-layout" style="height:calc(100vh - 150px); border:1px solid var(--slate-200); border-radius:var(--radius-lg); overflow:hidden;">
  <div class="chat-sidebar chat-sidebar-mobile-full">
    <div class="chat-sidebar-header">Semua Percakapan (<?= count($chatUsers) ?>)</div>
    <?php foreach ($chatUsers as $u): ?>
    <a href="chat_admin.php?user_id=<?= $u['user_id'] ?>" class="chat-contact" style="text-decoration:none;">
      <div class="contact-avatar"><?= strtoupper(substr($u['nama'],0,1)) ?></div>
      <div style="flex:1;min-width:0;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <div class="contact-name"><?= sanitize($u['nama']) ?></div>
          <?php if ($u['unread'] > 0): ?>
          <div style="background:var(--green-500);color:white;border-radius:50%;width:18px;height:18px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $u['unread'] ?></div>
          <?php endif; ?>
        </div>
        <div class="contact-last"><?= sanitize(substr($u['last_msg']??'',0,40)) ?></div>
      </div>
    </a>
    <?php endforeach; ?>
    <?php if (count($chatUsers) === 0): ?>
    <div style="padding:30px;text-align:center;color:var(--slate-400);font-size:13px;">Belum ada pesan masuk</div>
    <?php endif; ?>
  </div>
  <!-- Panel kanan — hanya di desktop -->
  <div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--slate-400);" class="chat-empty-desktop">
    <div class="empty-state">
      <div class="empty-title">Pilih percakapan</div>
      <div class="empty-sub">Klik nama customer untuk membuka pesan</div>
    </div>
  </div>
</div>
<?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
