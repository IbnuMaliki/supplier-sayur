// ============================================================
// SUPPLIER SAYUR AZAM HERI — Main JavaScript
// ============================================================

// Navbar scroll effect
window.addEventListener('scroll', () => {
  const navbar = document.getElementById('navbar');
  if (navbar) navbar.classList.toggle('scrolled', window.scrollY > 20);
});

// User menu toggle
function toggleUserMenu() {
  const menu = document.getElementById('user-menu');
  if (menu) menu.classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const menu = document.getElementById('user-menu');
  if (menu && !e.target.closest('.user-dropdown')) {
    menu.classList.remove('open');
  }
});

// Auto-hide flash message
document.addEventListener('DOMContentLoaded', function() {
  const flash = document.getElementById('flash-msg');
  if (flash) {
    setTimeout(() => { flash.style.opacity = '0'; flash.style.transform = 'translateX(30px)'; }, 3500);
    setTimeout(() => { flash.remove(); }, 4000);
  }
});

// Product filter (produk.php)
function filterProducts(cat, btn) {
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.product-card').forEach(card => {
    if (cat === 'all' || card.dataset.cat === cat) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}

// Keranjang: update qty via AJAX
function updateQty(produkId, delta) {
  fetch('ajax/keranjang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'update', produk_id: produkId, delta: delta })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Gagal update keranjang');
  })
  .catch(() => alert('Terjadi kesalahan. Coba lagi.'));
}

function removeItem(produkId) {
  if (!confirm('Hapus item dari keranjang?')) return;
  fetch('ajax/keranjang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'remove', produk_id: produkId })
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); })
  .catch(() => alert('Terjadi kesalahan.'));
}

// Rating stars interactive
function initStarRating() {
  const stars = document.querySelectorAll('.star-rate');
  const input = document.getElementById('rating-value');
  stars.forEach((star, idx) => {
    star.addEventListener('mouseenter', () => {
      stars.forEach((s, i) => s.classList.toggle('active', i <= idx));
    });
    star.addEventListener('click', () => {
      if (input) input.value = idx + 1;
      stars.forEach((s, i) => { s.dataset.selected = i <= idx ? '1' : '0'; });
    });
    star.addEventListener('mouseleave', () => {
      stars.forEach(s => s.classList.toggle('active', s.dataset.selected === '1'));
    });
  });
}
document.addEventListener('DOMContentLoaded', initStarRating);

// Chat auto-scroll
function scrollChatToBottom() {
  const msgs = document.getElementById('chat-messages');
  if (msgs) msgs.scrollTop = msgs.scrollHeight;
}
document.addEventListener('DOMContentLoaded', scrollChatToBottom);

// Chat send on Enter
document.addEventListener('DOMContentLoaded', function() {
  const chatInput = document.getElementById('chat-input');
  if (chatInput) {
    chatInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chat-send-btn')?.click();
      }
    });
  }
});

// Admin: confirm delete
function confirmDelete(msg) {
  return confirm(msg || 'Yakin ingin menghapus data ini?');
}

// Image preview on file input
function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  if (!preview) return;
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(file);
  }
}

// Format rupiah
function formatRp(angka) {
  return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
}

// Admin chart bars animation
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.bar').forEach(bar => {
    const h = bar.style.height;
    bar.style.height = '0';
    setTimeout(() => { bar.style.transition = 'height 0.7s ease'; bar.style.height = h; }, 100);
  });
});
