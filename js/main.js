/* ============================================================
   Fultala Flower Shop – Main JavaScript
   Async/await fetch, cart badge, toast, UI helpers
   ============================================================ */

// ── Toast Notifications ────────────────────────────────────────
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <i class="fas ${icon} toast-icon"></i>
    <span class="toast-text">${message}</span>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('hide');
    setTimeout(() => toast.remove(), 320);
  }, 2800);
}

// ── Cart Badge ─────────────────────────────────────────────────
async function updateCartBadge() {
  try {
    const res  = await fetch(`${window.SITE || ''}/api/cart.php?action=get`);
    const data = await res.json();
    const badge = document.getElementById('cartBadge');
    if (!badge) return;
    const count = data.items ? data.items.length : 0;
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  } catch {}
}

// ── Add to Cart (global) ───────────────────────────────────────
async function addToCart(productId, qty = 1) {
  const SITE = window.SITE || '';
  try {
    const res  = await fetch(`${SITE}/api/cart.php?action=add`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId, qty })
    });
    const data = await res.json();
    showToast(data.message || (data.success ? 'Сагсанд нэмэгдлээ!' : 'Алдаа гарлаа'), data.success ? 'success' : 'error');
    if (data.success) updateCartBadge();
  } catch {
    showToast('Сүлжээний алдаа гарлаа', 'error');
  }
}

// ── Toggle Wishlist (global) ───────────────────────────────────
async function toggleWishlist(productId, btn) {
  const SITE = window.SITE || '';
  try {
    const res  = await fetch(`${SITE}/api/wishlist.php?action=toggle`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId })
    });
    const data = await res.json();
    if (!data.success && data.message?.includes('Нэвтрэх')) {
      showToast('Нэвтэрсний дараа хүслийн жагсаалтад нэмнэ үү', 'error');
      return;
    }
    if (btn) btn.classList.toggle('active', data.added);
    showToast(data.added ? '❤️ Хүслийн жагсаалтад нэмэгдлээ' : 'Жагсаалтаас хасагдлаа', 'success');
  } catch {
    showToast('Сүлжээний алдаа гарлаа', 'error');
  }
}

// ── Product Card HTML ──────────────────────────────────────────
function productCard(p) {
  const SITE      = window.SITE || '';
  const sale      = parseFloat(p.sale_price  ?? p.original_price);
  const original  = parseFloat(p.original_price);
  const discount  = parseInt(p.discount_pct ?? 0);
  const name      = p.product_name ?? p.name ?? '';
  const slug      = p.slug ?? '';
  const img       = p.image || 'https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=400&q=70';
  const pid       = p.id;

  return `
    <div class="product-card">
      <div class="product-image-wrap">
        <a href="${SITE}/pages/product.php?slug=${slug}">
          <img src="${img}" alt="${name}" loading="lazy">
        </a>
        ${discount > 0 ? `<span class="discount-badge">${discount}% Off</span>` : ''}
        <div class="product-actions">
          <button class="action-btn" onclick="addToCart(${pid})" title="Сагсанд нэмэх">
            <i class="fas fa-shopping-bag"></i>
          </button>
          <button class="action-btn" onclick="toggleWishlist(${pid}, this)" title="Хүслийн жагсаалт">
            <i class="fas fa-heart"></i>
          </button>
          <a class="action-btn" href="${SITE}/pages/product.php?slug=${slug}" title="Дэлгэрэнгүй">
            <i class="fas fa-search"></i>
          </a>
        </div>
      </div>
      <div class="product-info">
        <p class="product-name">${name}</p>
        <div class="product-prices">
          ${discount > 0 ? `<span class="price-original">₮${original.toFixed(2)}</span>` : ''}
          <span class="price-sale">₮${sale.toFixed(2)}</span>
        </div>
      </div>
    </div>`;
}

// ── Newsletter Subscribe (demo) ────────────────────────────────
function subscribe() {
  const email = document.getElementById('subscribeEmail')?.value?.trim();
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showToast('И-мэйл хаяг буруу байна', 'error');
    return;
  }
  showToast('✅ Та амжилттай бүртгүүллээ!', 'success');
  if (document.getElementById('subscribeEmail')) {
    document.getElementById('subscribeEmail').value = '';
  }
}

// ── Mobile Menu ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.getElementById('hamburger');
  const navLinks  = document.getElementById('navLinks');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('open');
    });
    document.addEventListener('click', e => {
      if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
        navLinks.classList.remove('open');
      }
    });
  }

  // Scroll-to-top button
  const scrollTopBtn = document.getElementById('scrollTop');
  if (scrollTopBtn) {
    window.addEventListener('scroll', () => {
      scrollTopBtn.classList.toggle('visible', window.scrollY > 300);
    });
  }

  // Init cart badge
  updateCartBadge();
});
