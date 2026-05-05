<?php
$title = 'Хүслийн жагсаалт – Fultala';
$page  = 'wishlist';
require_once __DIR__ . '/../php/header.php';
if (!currentUser()) { header('Location: ' . SITE_URL . '/pages/login.php'); exit; }
?>

<section class="page-hero">
  <div class="container">
    <h1>Хүслийн Жагсаалт</h1>
    <p class="breadcrumb"><a href="<?= SITE_URL ?>">Нүүр</a> / Хүслийн жагсаалт</p>
  </div>
</section>

<div class="container" style="padding:50px 20px 80px">
  <div id="wishLoading" class="spinner"></div>
  <div class="product-grid" id="wishGrid"></div>
  <div id="wishEmpty" style="display:none" class="empty-state">
    <i class="fas fa-heart"></i>
    <h3>Хүслийн жагсаалт хоосон байна</h3>
    <p>Дуртай бүтээгдэхүүнээ хүслийн жагсаалтдаа нэмнэ үү</p>
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary" style="margin-top:20px">Дэлгүүрлэх</a>
  </div>
</div>

<script>
const SITE = window.SITE;
async function loadWishlist() {
  const res  = await fetch(`${SITE}/api/wishlist.php?action=get`);
  const data = await res.json();
  document.getElementById('wishLoading').style.display = 'none';
  if (!data.success || !data.data?.length) {
    document.getElementById('wishEmpty').style.display = 'block';
    return;
  }
  const grid = document.getElementById('wishGrid');
  data.data.forEach(p => grid.insertAdjacentHTML('beforeend', productCard(p)));
}
loadWishlist();
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
