<?php
require_once __DIR__ . '/../php/functions.php';
$slug    = htmlspecialchars($_GET['slug'] ?? '');
$product = $slug ? getProductBySlug($slug) : null;
if (!$product) { header('Location: ' . SITE_URL . '/pages/shop.php'); exit; }
$related = getRelatedProducts($product['id'], 4);
$title   = $product['product_name'] . ' – Fultala';
$page    = 'shop';
require_once __DIR__ . '/../php/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1><?= htmlspecialchars($product['product_name']) ?></h1>
    <p class="breadcrumb">
      <a href="<?= SITE_URL ?>">Нүүр</a> /
      <a href="<?= SITE_URL ?>/pages/shop.php">Дэлгүүр</a> /
      <?= htmlspecialchars($product['product_name']) ?>
    </p>
  </div>
</section>

<div class="container">
  <div class="product-detail">
    <div class="product-detail-image">
      <?php if ($product['image']): ?>
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
      <?php else: ?>
        <img src="https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=600&q=80" alt="Цэцэг">
      <?php endif; ?>
    </div>

    <div class="product-detail-info">
      <p class="product-category-tag"><?= htmlspecialchars($product['category_name']) ?></p>
      <h1 class="product-detail-title"><?= htmlspecialchars($product['product_name']) ?></h1>

      <div class="product-detail-prices">
        <span class="price-sale">$<?= number_format($product['sale_price'], 2) ?></span>
        <?php if ($product['discount_pct'] > 0): ?>
        <span class="price-original">$<?= number_format($product['original_price'], 2) ?></span>
        <span class="discount-badge" style="position:static"><?= $product['discount_pct'] ?>% Off</span>
        <?php endif; ?>
      </div>

      <p class="product-desc"><?= nl2br(htmlspecialchars($product['description'] ?? 'Тайлбар байхгүй байна.')) ?></p>

      <div class="qty-selector">
        <button class="qty-btn" onclick="changeQty(-1)">−</button>
        <input type="number" id="qty-input" value="1" min="1" max="<?= (int)$product['stock'] ?>">
        <button class="qty-btn" onclick="changeQty(1)">+</button>
      </div>

      <p style="color:var(--gray);font-size:.85rem;margin-bottom:20px">
        <i class="fas fa-box"></i> Нөөц: <?= (int)$product['stock'] ?> ширхэг
      </p>

      <div class="detail-actions">
        <button class="btn btn-primary" onclick="addToCart(<?= $product['id'] ?>)">
          <i class="fas fa-shopping-bag"></i> Сагсанд нэмэх
        </button>
        <button class="btn btn-outline" onclick="toggleWishlist(<?= $product['id'] ?>)" id="wishBtn">
          <i class="fas fa-heart"></i> Хүслийн жагсаалт
        </button>
      </div>
    </div>
  </div>

  <?php if ($related): ?>
  <section style="padding:60px 0">
    <h2 class="section-title">Холбоотой <span>Бүтээгдэхүүн</span></h2>
    <p class="section-sub">Ижил ангиллын бусад цэцгүүд</p>
    <div class="product-grid">
      <?php foreach ($related as $rp):
        $salep = $rp['sale_price'] ?? ($rp['original_price'] * (1 - $rp['discount_pct']/100));
      ?>
      <div class="product-card">
        <div class="product-image-wrap">
          <a href="<?= SITE_URL ?>/pages/product.php?slug=<?= $rp['slug'] ?>">
            <img src="<?= $rp['image'] ?: 'https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=400&q=70' ?>"
                 alt="<?= htmlspecialchars($rp['product_name']) ?>" loading="lazy">
          </a>
          <?php if ($rp['discount_pct'] > 0): ?>
          <span class="discount-badge"><?= $rp['discount_pct'] ?>% Off</span>
          <?php endif; ?>
          <div class="product-actions">
            <button class="action-btn" onclick="addToCart(<?= $rp['id'] ?>)" title="Сагсанд"><i class="fas fa-shopping-bag"></i></button>
            <button class="action-btn" onclick="toggleWishlist(<?= $rp['id'] ?>)" title="Хүслийн жагсаалт"><i class="fas fa-heart"></i></button>
            <a class="action-btn" href="<?= SITE_URL ?>/pages/product.php?slug=<?= $rp['slug'] ?>" title="Үзэх"><i class="fas fa-search"></i></a>
          </div>
        </div>
        <div class="product-info">
          <p class="product-name"><?= htmlspecialchars($rp['product_name']) ?></p>
          <div class="product-prices">
            <span class="price-original">$<?= number_format($rp['original_price'],2) ?></span>
            <span class="price-sale">$<?= number_format($salep, 2) ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<script>
const SITE = window.SITE;
function changeQty(delta) {
  const inp = document.getElementById('qty-input');
  const max = parseInt(inp.max);
  inp.value = Math.max(1, Math.min(max, parseInt(inp.value) + delta));
}
async function addToCart(pid) {
  const qty = parseInt(document.getElementById('qty-input')?.value || 1);
  const res  = await fetch(`${SITE}/api/cart.php?action=add`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ product_id: pid, qty })
  });
  const data = await res.json();
  showToast(data.message || (data.success ? 'Нэмэгдлээ' : 'Алдаа'), data.success ? 'success' : 'error');
  if (data.success) updateCartBadge();
}
async function toggleWishlist(pid) {
  const res  = await fetch(`${SITE}/api/wishlist.php?action=toggle`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ product_id: pid })
  });
  const data = await res.json();
  if (!data.success && data.message?.includes('Нэвтрэх')) {
    showToast('Хүслийн жагсаалтад нэмэхийн тулд нэвтэрнэ үү.', 'error');
    return;
  }
  showToast(data.added ? 'Хүслийн жагсаалтад нэмэгдлээ' : 'Жагсаалтаас хасагдлаа', 'success');
}
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
