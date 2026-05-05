<?php
$title = 'Миний Сагс – Fultala';
$page  = 'cart';
require_once __DIR__ . '/../php/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>Миний Сагс</h1>
    <p class="breadcrumb"><a href="<?= SITE_URL ?>">Нүүр</a> / Сагс</p>
  </div>
</section>

<div class="container">
  <div class="cart-layout" id="cartLayout">
    <div>
      <div id="cartLoading" class="spinner"></div>
      <div id="cartContent" style="display:none">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Бүтээгдэхүүн</th>
              <th>Үнэ</th>
              <th>Тоо</th>
              <th>Нийт</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="cartBody"></tbody>
        </table>
        <div style="display:flex;gap:12px;margin-top:20px;flex-wrap:wrap">
          <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Дэлгүүр рүү буцах
          </a>
          <button class="btn btn-dark" onclick="clearCart()">
            <i class="fas fa-trash"></i> Сагс цэвэрлэх
          </button>
        </div>
      </div>
      <div id="cartEmpty" style="display:none" class="empty-state">
        <i class="fas fa-shopping-bag"></i>
        <h3>Сагс хоосон байна</h3>
        <p>Цэцэг сонгон сагсандаа нэмнэ үү</p>
        <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary" style="margin-top:20px">
          <i class="fas fa-store"></i> Дэлгүүрлэх
        </a>
      </div>
    </div>

    <div>
      <div class="cart-summary" id="cartSummary" style="display:none">
        <h3 style="margin-bottom:20px">Захиалгын дүн</h3>
        <div class="summary-row">
          <span>Нийт дүн</span>
          <span id="subtotal">$0.00</span>
        </div>
        <div class="summary-row">
          <span>Хүргэлт</span>
          <span id="shipping">$5.00</span>
        </div>
        <div class="summary-row total">
          <span>Нийт</span>
          <span class="price-sale" id="grandTotal">$5.00</span>
        </div>
        <a href="<?= SITE_URL ?>/pages/checkout.php" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px">
          <i class="fas fa-credit-card"></i> Захиалга өгөх
        </a>
        <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:10px">
          Дэлгүүрлэлт үргэлжлүүлэх
        </a>
      </div>
    </div>
  </div>
</div>

<script>
const SITE = window.SITE;

async function loadCart() {
  const res  = await fetch(`${SITE}/api/cart.php?action=get`);
  const data = await res.json();
  document.getElementById('cartLoading').style.display = 'none';

  if (!data.success || !data.items || data.items.length === 0) {
    document.getElementById('cartEmpty').style.display = 'block';
    return;
  }

  document.getElementById('cartContent').style.display = 'block';
  document.getElementById('cartSummary').style.display = 'block';
  renderCart(data.items, data.total);
}

function renderCart(items, total) {
  const tbody = document.getElementById('cartBody');
  tbody.innerHTML = '';
  items.forEach(item => {
    const pid    = item.product_id || item.id;
    const name   = item.product_name;
    const price  = parseFloat(item.sale_price);
    const qty    = item.qty;
    const sub    = parseFloat(item.subtotal || price * qty);
    const img    = item.image || 'https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=100&q=60';
    const cid    = item.id;

    tbody.insertAdjacentHTML('beforeend', `
      <tr id="row-${cid}">
        <td>
          <div class="cart-product">
            <img src="${img}" alt="${name}">
            <div>
              <p class="cart-product-name">${name}</p>
              <p class="cart-product-price">$${price.toFixed(2)}</p>
            </div>
          </div>
        </td>
        <td>$${price.toFixed(2)}</td>
        <td>
          <div class="cart-qty">
            <input type="number" value="${qty}" min="1" style="width:60px;padding:6px;border:1px solid var(--border);border-radius:4px"
              onchange="updateQty(${cid}, this.value)">
          </div>
        </td>
        <td><strong>$${sub.toFixed(2)}</strong></td>
        <td>
          <button class="remove-btn" onclick="removeItem(${cid}, ${pid})">
            <i class="fas fa-times"></i>
          </button>
        </td>
      </tr>
    `);
  });

  const shipping = total >= 50 ? 0 : 5;
  document.getElementById('subtotal').textContent   = `$${parseFloat(total).toFixed(2)}`;
  document.getElementById('shipping').textContent   = shipping === 0 ? 'Үнэгүй' : `$${shipping.toFixed(2)}`;
  document.getElementById('grandTotal').textContent = `$${(parseFloat(total) + shipping).toFixed(2)}`;
}

async function removeItem(cid, pid) {
  const body = cid ? { cart_id: cid } : { product_id: pid };
  const res  = await fetch(`${SITE}/api/cart.php?action=remove`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(body)
  });
  const data = await res.json();
  if (data.success) { loadCart(); updateCartBadge(); showToast('Устгагдлаа', 'success'); }
}

async function updateQty(cid, qty) {
  await fetch(`${SITE}/api/cart.php?action=update`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ cart_id: cid, qty: parseInt(qty) })
  });
  loadCart();
  updateCartBadge();
}

async function clearCart() {
  if (!confirm('Сагсыг бүрэн цэвэрлэх үү?')) return;
  const res  = await fetch(`${SITE}/api/cart.php?action=clear`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({})
  });
  const data = await res.json();
  if (data.success) { location.reload(); }
}

loadCart();
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
