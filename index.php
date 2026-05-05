<?php
$title = 'Fultala – Монголын онлайн цэцгийн дэлгүүр';
$page  = 'home';
require_once __DIR__ . '/php/header.php';
?>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-text">
        <span class="hero-badge">🌸 Шинэ цуглуулга ирлээ!</span>
        <h1 class="hero-title">
          Хамгийн <span>Гоё Цэцэг</span><br>Таны Гэрт
        </h1>
        <p class="hero-desc">
          Бид тан руу хамгийн свеж, гоё, эрдэнэт цэцгийг шуурхай хүргэнэ.
          Хайрын илэрхийлэл бүр нэг цэцэгнээс эхэлдэг.
        </p>
        <div class="hero-actions">
          <a href="pages/shop.php" class="btn btn-primary">
            <i class="fas fa-shopping-bag"></i> Одоо худалдаж авах
          </a>
          <a href="pages/about.php" class="btn btn-outline">
            Бидний тухай
          </a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <strong>500+</strong>
            <span>Бүтээгдэхүүн</span>
          </div>
          <div class="hero-stat">
            <strong>10K+</strong>
            <span>Хэрэглэгч</span>
          </div>
          <div class="hero-stat">
            <strong>4.9★</strong>
            <span>Үнэлгээ</span>
          </div>
        </div>
      </div>
      <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=600&q=80" alt="Цэцгийн баглаа">
        <div class="hero-float-badge top-left">
          🌹 Сарнай<br><small style="color:var(--primary);font-weight:700">30% хямдрал</small>
        </div>
        <div class="hero-float-badge bot-right">
          🚚 Өнөөдөр<br><small style="color:#10b981;font-weight:700">хүргэнэ</small>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="categories-section">
  <div class="container">
    <h2 class="section-title"><span>Ангилал</span>-аар хайх</h2>
    <p class="section-sub">Таны хайж буй цэцгийн ангиллыг сонгоно уу</p>
    <div class="category-pills" id="categoryPills">
      <button class="pill active" data-cat="">Бүгд</button>
    </div>
  </div>
</section>

<!-- Products -->
<section class="products-section">
  <div class="container">
    <h2 class="section-title"><span>Бүх</span> Бүтээгдэхүүн</h2>
    <p class="section-sub">
      Монголын хамгийн сайн чанарын цэцгийг та бидний дэлгүүрээс сонгон авна уу
    </p>

    <div id="productsLoading" class="spinner"></div>
    <div class="product-grid" id="productGrid"></div>

    <div class="load-more-wrap">
      <button class="btn btn-outline" id="loadMoreBtn" style="display:none" onclick="loadMore()">
        <i class="fas fa-plus"></i> Цааш үзэх
      </button>
    </div>
  </div>
</section>

<!-- Banner Strip -->
<section class="banner-strip">
  <div class="container">
    <h2>Онцгой санал 🌺</h2>
    <p>Бүх захиалгад 30% хямдрал — зөвхөн энэ 7 хоногт!</p>
    <a href="pages/shop.php" class="btn-white">Одоо авах</a>
  </div>
</section>

<!-- Featured Products -->
<section class="products-section" style="padding-top:80px">
  <div class="container">
    <h2 class="section-title">Онцлох <span>Бүтээгдэхүүн</span></h2>
    <p class="section-sub">Хамгийн их хүсэлттэй, шилдэг цэцгийн сонголт</p>
    <div class="product-grid" id="featuredGrid"></div>
  </div>
</section>

<script>
const SITE = window.SITE;
let currentOffset = 0;
let currentCategory = '';
const LIMIT = 8;

async function loadCategories() {
  const res  = await fetch(`${SITE}/api/products.php?action=categories`);
  const data = await res.json();
  if (!data.success) return;
  const pills = document.getElementById('categoryPills');
  data.data.forEach(cat => {
    const btn = document.createElement('button');
    btn.className = 'pill';
    btn.dataset.cat = cat.slug;
    btn.textContent = cat.name;
    btn.onclick = () => filterByCategory(cat.slug, btn);
    pills.appendChild(btn);
  });
}

async function loadProducts(cat = '', offset = 0, append = false) {
  if (offset === 0) document.getElementById('productsLoading').style.display = 'block';
  try {
    const res  = await fetch(`${SITE}/api/products.php?action=list&category=${cat}&limit=${LIMIT}&offset=${offset}`);
    const data = await res.json();
    if (!data.success) return;
    const grid = document.getElementById('productGrid');
    if (!append) grid.innerHTML = '';
    data.data.forEach(p => grid.insertAdjacentHTML('beforeend', productCard(p)));
    const btn = document.getElementById('loadMoreBtn');
    btn.style.display = data.data.length === LIMIT ? 'inline-flex' : 'none';
  } catch(e) {
    console.error('loadProducts error:', e);
  } finally {
    document.getElementById('productsLoading').style.display = 'none';
  }
}

async function loadFeatured() {
  const res  = await fetch(`${SITE}/api/products.php?action=list&limit=4`);
  const data = await res.json();
  if (!data.success) return;
  const grid = document.getElementById('featuredGrid');
  data.data.filter(p => p.is_featured == 1).slice(0,4).forEach(p => {
    grid.insertAdjacentHTML('beforeend', productCard(p));
  });
}

function filterByCategory(slug, btnEl) {
  currentCategory = slug;
  currentOffset   = 0;
  document.querySelectorAll('.pill').forEach(b => b.classList.remove('active'));
  btnEl.classList.add('active');
  loadProducts(slug, 0, false);
}

function loadMore() {
  currentOffset += LIMIT;
  loadProducts(currentCategory, currentOffset, true);
}

document.addEventListener('DOMContentLoaded', () => {
  loadCategories();
  loadProducts();
  loadFeatured();
});
</script>

<?php require_once __DIR__ . '/php/footer.php'; ?>
