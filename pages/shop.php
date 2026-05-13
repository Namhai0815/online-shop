<?php
$title = 'Дэлгүүр – Fultala';
$page  = 'shop';
require_once __DIR__ . '/../php/header.php';
$categories = getCategories();
$activeCategory = htmlspecialchars($_GET['category'] ?? '');
$searchQuery    = htmlspecialchars($_GET['q'] ?? '');
?>

<section class="page-hero">
  <div class="container">
    <h1>Дэлгүүр</h1>
    <p class="breadcrumb"><a href="<?= SITE_URL ?>">Нүүр</a> / Дэлгүүр</p>
  </div>
</section>

<div class="container">
  <div class="shop-layout">
    <!-- Sidebar Filters -->
    <aside class="sidebar">
      <div class="filter-card">
        <p class="filter-title">Ангилал</p>
        <ul class="filter-list">
          <li>
            <label>
              <input type="radio" name="cat" value="" <?= $activeCategory==='' ? 'checked':'' ?>> Бүгд
            </label>
          </li>
          <?php foreach ($categories as $cat): ?>
          <li>
            <label>
              <input type="radio" name="cat" value="<?= $cat['slug'] ?>"
                <?= $activeCategory===$cat['slug'] ? 'checked':'' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </label>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="filter-card">
        <p class="filter-title">Үнийн хязгаар</p>
        <div class="price-range">
          <input type="number" id="priceMin" placeholder="Мин ₮" min="0">
          <input type="number" id="priceMax" placeholder="Хамгийн их ₮" min="0">
        </div>
        <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:12px" onclick="applyFilters()">
          Шүүх
        </button>
      </div>

      <div class="filter-card">
        <p class="filter-title">Хямдрал</p>
        <ul class="filter-list">
          <li><label><input type="checkbox" id="discountOnly"> Зөвхөн хямдралтай</label></li>
        </ul>
      </div>
    </aside>

    <!-- Products -->
    <div>
      <!-- Search bar inside shop -->
      <div class="shop-search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="shopSearchInput" placeholder="Бүтээгдэхүүн хайх..."
               value="<?= $searchQuery ?>" oninput="onSearchInput(this.value)">
        <?php if ($searchQuery): ?>
        <button onclick="clearSearch()" title="Цэвэрлэх"><i class="fas fa-times"></i></button>
        <?php endif; ?>
      </div>

      <div class="shop-toolbar">
        <span class="results-count" id="resultsCount">Ачаалж байна...</span>
        <select class="sort-select" id="sortSelect" onchange="applyFilters()">
          <option value="newest">Шинэ эхэндээ</option>
          <option value="price_asc">Үнэ өсөх</option>
          <option value="price_desc">Үнэ буурах</option>
          <option value="discount">Хямдрал ихтэй</option>
        </select>
      </div>

      <div id="productsLoading" class="spinner"></div>
      <div class="product-grid" id="productGrid"></div>

      <div class="load-more-wrap">
        <button class="btn btn-outline" id="loadMoreBtn" style="display:none" onclick="loadMore()">
          <i class="fas fa-plus"></i> Цааш үзэх
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const SITE      = window.SITE;
const INIT_CAT  = '<?= $activeCategory ?>';
const INIT_Q    = '<?= addslashes($searchQuery) ?>';
let offset = 0, category = INIT_CAT, searchQ = INIT_Q;
const LIMIT = 12;
let allProducts = [];
// searchTimer is declared in header.php's global scope

document.querySelectorAll('input[name="cat"]').forEach(r => {
  r.addEventListener('change', () => {
    category = r.value;
    searchQ  = '';
    const si = document.getElementById('shopSearchInput');
    if (si) si.value = '';
    offset = 0;
    loadProducts();
  });
});

async function loadProducts() {
  document.getElementById('productsLoading').style.display = 'block';
  document.getElementById('productGrid').innerHTML = '';
  try {
    const url = searchQ
      ? `${SITE}/api/products.php?action=list&search=${encodeURIComponent(searchQ)}&limit=100`
      : `${SITE}/api/products.php?action=list&category=${category}&limit=100`;
    const res  = await fetch(url);
    const data = await res.json();
    if (!data.success) return;
    allProducts = data.data;
    renderProducts();
  } catch(e) {
    document.getElementById('productGrid').innerHTML =
      '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-wifi"></i><h3>Холболт алдаа</h3><p>Сервертэй холбогдоход алдаа гарлаа</p></div>';
  } finally {
    document.getElementById('productsLoading').style.display = 'none';
  }
}

function renderProducts() {
  const minP    = parseFloat(document.getElementById('priceMin').value) || 0;
  const maxP    = parseFloat(document.getElementById('priceMax').value) || Infinity;
  const discOnly= document.getElementById('discountOnly').checked;
  const sort    = document.getElementById('sortSelect').value;

  let filtered = allProducts.filter(p => {
    const price = parseFloat(p.sale_price);
    if (price < minP || price > maxP) return false;
    if (discOnly && parseInt(p.discount_pct) === 0) return false;
    return true;
  });

  if (sort === 'price_asc')  filtered.sort((a,b) => a.sale_price - b.sale_price);
  if (sort === 'price_desc') filtered.sort((a,b) => b.sale_price - a.sale_price);
  if (sort === 'discount')   filtered.sort((a,b) => b.discount_pct - a.discount_pct);

  const grid = document.getElementById('productGrid');
  grid.innerHTML = '';

  if (!filtered.length) {
    grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-search"></i><h3>Үр дүн олдсонгүй</h3><p>Өөр утгаар хайж үзнэ үү</p></div>';
  } else {
    filtered.slice(0, offset + LIMIT).forEach(p => grid.insertAdjacentHTML('beforeend', productCard(p)));
  }

  document.getElementById('resultsCount').textContent = `${filtered.length} бүтээгдэхүүн олдлоо`;
  document.getElementById('loadMoreBtn').style.display = filtered.length > offset + LIMIT ? 'inline-flex' : 'none';
}

function onSearchInput(val) {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    searchQ  = val.trim();
    category = '';
    document.querySelectorAll('input[name="cat"]').forEach(r => r.checked = r.value === '');
    offset = 0;
    loadProducts();
  }, 400);
}

function clearSearch() {
  searchQ = '';
  document.getElementById('shopSearchInput').value = '';
  offset = 0;
  loadProducts();
}

function applyFilters() { offset = 0; renderProducts(); }
function loadMore()      { offset += LIMIT; renderProducts(); }

document.addEventListener('DOMContentLoaded', loadProducts);
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
