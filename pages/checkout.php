<?php
$title = 'Захиалга өгөх – Fultala';
$page  = 'checkout';
require_once __DIR__ . '/../php/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>Захиалга өгөх</h1>
    <p class="breadcrumb">
      <a href="<?= SITE_URL ?>">Нүүр</a> / <a href="<?= SITE_URL ?>/pages/cart.php">Сагс</a> / Захиалга
    </p>
  </div>
</section>

<div class="container">
  <div class="checkout-layout">
    <div>
      <!-- Delivery Info -->
      <div class="checkout-section">
        <h3><i class="fas fa-map-marker-alt" style="color:var(--primary)"></i> Хүргэлтийн мэдээлэл</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Нэр</label>
            <input type="text" class="form-control" id="firstName" placeholder="Таны нэр">
          </div>
          <div class="form-group">
            <label>Утасны дугаар</label>
            <input type="tel" class="form-control" id="phone" placeholder="99XXXXXX">
          </div>
        </div>
        <div class="form-group">
          <label>И-мэйл хаяг</label>
          <input type="email" class="form-control" id="email" placeholder="example@mail.com">
          <span class="error-msg" id="emailErr">И-мэйл хаяг буруу байна</span>
        </div>
        <div class="form-group">
          <label>Хүргэлтийн хаяг</label>
          <textarea class="form-control" id="address" rows="3" placeholder="Дүүрэг, хороо, байр, тоот..."></textarea>
          <span class="error-msg" id="addrErr">Хаяг шаардлагатай</span>
        </div>
        <div class="form-group">
          <label>Нэмэлт тэмдэглэл</label>
          <textarea class="form-control" id="notes" rows="2" placeholder="Захиалгын талаар нэмэлт мэдээлэл..."></textarea>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="checkout-section">
        <h3><i class="fas fa-credit-card" style="color:var(--primary)"></i> Төлбөрийн арга</h3>
        <div style="display:flex;flex-direction:column;gap:12px">
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:14px;border:2px solid var(--primary);border-radius:8px;background:#fff5f5">
            <input type="radio" name="payment" value="cash" checked> 💵 Бэлэн мөнгө (хүргэлтийн үед)
          </label>
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:14px;border:2px solid var(--border);border-radius:8px">
            <input type="radio" name="payment" value="card"> 💳 Дансаар (QPay / SocialPay)
          </label>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div>
      <div class="cart-summary">
        <h3 style="margin-bottom:20px">Захиалгын хураангуй</h3>
        <div id="checkoutItems"></div>
        <hr style="margin:16px 0;border-color:var(--border)">
        <div class="summary-row">
          <span>Дэд нийт</span>
          <span id="checkoutSubtotal">$0.00</span>
        </div>
        <div class="summary-row">
          <span>Хүргэлт</span>
          <span id="checkoutShipping">$5.00</span>
        </div>
        <div class="summary-row total">
          <span>Нийт дүн</span>
          <span class="price-sale" id="checkoutTotal">$5.00</span>
        </div>
        <div id="orderAlert" class="alert"></div>
        <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px" onclick="placeOrder()">
          <i class="fas fa-check"></i> Захиалга баталгаажуулах
        </button>
      </div>
    </div>
  </div>
</div>

<script>
const SITE = window.SITE;

async function loadOrderSummary() {
  const res  = await fetch(`${SITE}/api/cart.php?action=get`);
  const data = await res.json();
  if (!data.success || !data.items?.length) {
    window.location = `${SITE}/pages/cart.php`;
    return;
  }
  const items = document.getElementById('checkoutItems');
  data.items.forEach(item => {
    items.insertAdjacentHTML('beforeend', `
      <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:.9rem">
        <span>${item.product_name} × ${item.qty}</span>
        <span>$${(item.sale_price * item.qty).toFixed(2)}</span>
      </div>
    `);
  });
  const shipping = parseFloat(data.total) >= 50 ? 0 : 5;
  document.getElementById('checkoutSubtotal').textContent = `$${parseFloat(data.total).toFixed(2)}`;
  document.getElementById('checkoutShipping').textContent = shipping === 0 ? 'Үнэгүй' : `$${shipping.toFixed(2)}`;
  document.getElementById('checkoutTotal').textContent    = `$${(parseFloat(data.total) + shipping).toFixed(2)}`;
}

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

async function placeOrder() {
  const address = document.getElementById('address').value.trim();
  const email   = document.getElementById('email').value.trim();
  const notes   = document.getElementById('notes').value.trim();
  let   valid   = true;

  document.getElementById('addrErr').classList.toggle('show', !address);
  if (!address) valid = false;

  if (email && !validateEmail(email)) {
    document.getElementById('emailErr').classList.add('show');
    valid = false;
  } else {
    document.getElementById('emailErr').classList.remove('show');
  }

  if (!valid) return;

  const btn = document.querySelector('[onclick="placeOrder()"]');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Боловсруулж байна...';

  const res  = await fetch(`${SITE}/api/orders.php?action=place`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ address, notes })
  });
  const data = await res.json();

  const alertEl = document.getElementById('orderAlert');
  alertEl.className = 'alert show ' + (data.success ? 'alert-success' : 'alert-error');
  alertEl.textContent = data.message || (data.success ? 'Амжилттай!' : 'Алдаа гарлаа');

  if (data.success) {
    updateCartBadge();
    setTimeout(() => { window.location = `${SITE}/pages/orders.php`; }, 1500);
  } else {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Захиалга баталгаажуулах';
  }
}

loadOrderSummary();
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
