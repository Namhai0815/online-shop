<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
sessionStart();
header('Content-Type: text/html; charset=utf-8');
$user = currentUser();
$page = $page ?? '';
?>
<!DOCTYPE html>
<html lang="mn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? SITE_NAME) ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- ── Search Overlay ─────────────────────────────────── -->
<div class="search-overlay" id="searchOverlay" onclick="closeSearch(event)">
  <div class="search-modal">
    <div class="search-input-wrap">
      <i class="fas fa-search search-icon-inner"></i>
      <input type="text" id="searchInput" placeholder="Цэцэг хайх..." autocomplete="off"
             oninput="liveSearch(this.value)" onkeydown="if(event.key==='Enter')goSearch()">
      <button class="search-close-btn" onclick="closeSearchModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="search-results" id="searchResults"></div>
  </div>
</div>

<!-- ── Header ─────────────────────────────────────────── -->
<header class="header">
  <div class="container">
    <nav class="navbar">
      <a href="<?= SITE_URL ?>/index.php" class="logo">
        <span class="logo-icon">🌸</span>
        <span>Ful<span>tala</span></span>
      </a>

      <ul class="nav-links" id="navLinks">
        <li><a href="<?= SITE_URL ?>/index.php"         class="<?= $page==='home'    ?'active':'' ?>">НҮҮР</a></li>
        <li><a href="<?= SITE_URL ?>/pages/shop.php"    class="<?= $page==='shop'    ?'active':'' ?>">ДЭЛГҮҮР</a></li>
        <li><a href="<?= SITE_URL ?>/pages/about.php"   class="<?= $page==='about'   ?'active':'' ?>">БИДНИЙ ТУХАЙ</a></li>
        <li><a href="<?= SITE_URL ?>/pages/contact.php" class="<?= $page==='contact' ?'active':'' ?>">ХОЛБОО БАРИХ</a></li>
        <?php if ($user): ?>
        <li><a href="<?= SITE_URL ?>/pages/orders.php"  class="<?= $page==='orders'  ?'active':'' ?>">ЗАХИАЛГУУД</a></li>
        <?php endif; ?>
      </ul>

      <div class="nav-icons">
        <!-- Search button -->
        <button class="nav-icon-btn" title="Хайх" onclick="openSearch()">
          <i class="fas fa-search"></i>
        </button>

        <?php if ($user): ?>
        <!-- Wishlist -->
        <a href="<?= SITE_URL ?>/pages/wishlist.php" class="nav-icon-btn" title="Хүслийн жагсаалт">
          <i class="fas fa-heart"></i>
        </a>
        <?php endif; ?>

        <!-- Cart -->
        <a href="<?= SITE_URL ?>/pages/cart.php" class="nav-icon-btn" title="Сагс">
          <i class="fas fa-shopping-bag"></i>
          <span class="cart-badge" id="cartBadge" style="display:none">0</span>
        </a>

        <?php if ($user): ?>
        <!-- User dropdown -->
        <div class="user-dropdown" id="userDropdown">
          <button class="user-btn" onclick="toggleDropdown()">
            <i class="fas fa-user-circle"></i>
            <span class="user-btn-name"><?= htmlspecialchars($user['name']) ?></span>
            <i class="fas fa-chevron-down" style="font-size:.7rem"></i>
          </button>
          <div class="dropdown-menu" id="dropdownMenu">
            <div class="dropdown-header">
              <strong><?= htmlspecialchars($user['name']) ?></strong>
              <small><?= htmlspecialchars($user['email']) ?></small>
            </div>
            <?php if (($user['role'] ?? '') === 'admin'): ?>
            <a href="<?= SITE_URL ?>/pages/admin.php" class="dropdown-item" style="color:#f97316;font-weight:600"><i class="fas fa-shield-halved"></i> Admin хэсэг</a>
            <div class="dropdown-divider"></div>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/pages/orders.php"  class="dropdown-item"><i class="fas fa-box"></i> Миний захиалгууд</a>
            <a href="<?= SITE_URL ?>/pages/wishlist.php" class="dropdown-item"><i class="fas fa-heart"></i> Хүслийн жагсаалт</a>
            <a href="<?= SITE_URL ?>/pages/cart.php"    class="dropdown-item"><i class="fas fa-shopping-bag"></i> Сагс</a>
            <div class="dropdown-divider"></div>
            <button class="dropdown-item dropdown-logout" onclick="logout()">
              <i class="fas fa-sign-out-alt"></i> Гарах
            </button>
          </div>
        </div>

        <?php else: ?>
        <a href="<?= SITE_URL ?>/pages/login.php" class="btn btn-primary" style="padding:8px 18px;font-size:.85rem;white-space:nowrap">
          Нэвтрэх
        </a>
        <?php endif; ?>

        <button class="hamburger" id="hamburger" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
  </div>
</header>

<div class="toast-container" id="toastContainer"></div>

<script>
window.SITE = '<?= SITE_URL ?>';

// ── Search ────────────────────────────────────────────────────
function openSearch() {
  document.getElementById('searchOverlay').classList.add('open');
  setTimeout(() => document.getElementById('searchInput').focus(), 100);
}
function closeSearchModal() {
  document.getElementById('searchOverlay').classList.remove('open');
  document.getElementById('searchResults').innerHTML = '';
  document.getElementById('searchInput').value = '';
}
function closeSearch(e) {
  if (e.target === document.getElementById('searchOverlay')) closeSearchModal();
}

let searchTimer;
async function liveSearch(q) {
  clearTimeout(searchTimer);
  const res = document.getElementById('searchResults');
  if (q.trim().length < 2) { res.innerHTML = ''; return; }
  res.innerHTML = '<div class="search-loading"><i class="fas fa-spinner fa-spin"></i> Хайж байна...</div>';
  searchTimer = setTimeout(async () => {
    const r    = await fetch(`${SITE}/api/products.php?action=list&search=${encodeURIComponent(q)}&limit=6`);
    const data = await r.json();
    if (!data.success || !data.data.length) {
      res.innerHTML = '<p class="search-empty">Бүтээгдэхүүн олдсонгүй</p>';
      return;
    }
    res.innerHTML = data.data.map(p => `
      <a href="${SITE}/pages/product.php?slug=${p.slug}" class="search-result-item" onclick="closeSearchModal()">
        <img src="${p.image || 'https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=80&q=60'}" alt="${p.product_name}">
        <div class="search-result-info">
          <span class="search-result-name">${p.product_name}</span>
          <span class="search-result-price">₮${parseFloat(p.sale_price).toFixed(2)}</span>
        </div>
        ${p.discount_pct > 0 ? `<span class="search-result-badge">${p.discount_pct}% Off</span>` : ''}
      </a>
    `).join('') + `
      <a href="${SITE}/pages/shop.php?q=${encodeURIComponent(q)}" class="search-all-link" onclick="closeSearchModal()">
        Бүх үр дүнг харах <i class="fas fa-arrow-right"></i>
      </a>`;
  }, 350);
}

function goSearch() {
  const q = document.getElementById('searchInput').value.trim();
  if (q) { window.location = `${SITE}/pages/shop.php?q=${encodeURIComponent(q)}`; closeSearchModal(); }
}

// ── User dropdown ─────────────────────────────────────────────
function toggleDropdown() {
  document.getElementById('dropdownMenu').classList.toggle('open');
}
document.addEventListener('click', e => {
  const dd = document.getElementById('userDropdown');
  if (dd && !dd.contains(e.target)) {
    document.getElementById('dropdownMenu')?.classList.remove('open');
  }
});

// ── Logout ────────────────────────────────────────────────────
async function logout() {
  const res  = await fetch(`${SITE}/api/auth.php?action=logout`, { method: 'POST' });
  const data = await res.json();
  if (data.success) window.location = `${SITE}/index.php`;
}
</script>
