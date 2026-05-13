<?php
$title = 'Миний Захиалгууд – Fultala';
$page  = 'orders';
require_once __DIR__ . '/../php/header.php';
if (!currentUser()) { header('Location: ' . SITE_URL . '/pages/login.php'); exit; }
?>

<section class="page-hero">
  <div class="container">
    <h1>Миний Захиалгууд</h1>
    <p class="breadcrumb"><a href="<?= SITE_URL ?>">Нүүр</a> / Захиалгууд</p>
  </div>
</section>

<div class="container" style="padding:50px 20px 80px">
  <div id="ordersLoading" class="spinner"></div>
  <div id="ordersContent" style="display:none">

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:40px" id="statsGrid"></div>

    <h3 style="margin-bottom:20px">Захиалгын жагсаалт</h3>
    <div id="ordersList"></div>
  </div>
  <div id="ordersEmpty" style="display:none" class="empty-state">
    <i class="fas fa-box-open"></i>
    <h3>Захиалга байхгүй байна</h3>
    <p>Та одоохондоо ямар нэгэн захиалга өгөөгүй байна</p>
    <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary" style="margin-top:20px">
      Дэлгүүрлэх
    </a>
  </div>
</div>

<script>
const SITE = window.SITE;
const STATUS_LABELS = {
  pending:'Хүлээгдэж байна', processing:'Боловсруулж байна',
  shipped:'Хүргэгдэж байна', delivered:'Хүргэгдсэн', cancelled:'Цуцлагдсан'
};
const STATUS_COLORS = {
  pending:'#f59e0b', processing:'#3b82f6', shipped:'#8b5cf6', delivered:'#10b981', cancelled:'#ef4444'
};

async function loadOrders() {
  const [ordersRes, statsRes] = await Promise.all([
    fetch(`${SITE}/api/orders.php?action=list`),
    fetch(`${SITE}/api/orders.php?action=stats`)
  ]);
  const ordersData = await ordersRes.json();
  const statsData  = await statsRes.json();

  document.getElementById('ordersLoading').style.display = 'none';

  if (statsData.success) {
    const s = statsData.data;
    document.getElementById('statsGrid').innerHTML = `
      <div style="background:var(--white);border:1px solid var(--border);border-radius:12px;padding:24px;text-align:center">
        <div style="font-size:2rem;font-weight:700;color:var(--primary)">${s.total_orders || 0}</div>
        <div style="color:var(--gray);font-size:.9rem">Нийт захиалга</div>
      </div>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:12px;padding:24px;text-align:center">
        <div style="font-size:2rem;font-weight:700;color:var(--primary)">₮${parseFloat(s.total_spent||0).toFixed(2)}</div>
        <div style="color:var(--gray);font-size:.9rem">Нийт зарцуулсан</div>
      </div>
      <div style="background:var(--white);border:1px solid var(--border);border-radius:12px;padding:24px;text-align:center">
        <div style="font-size:2rem;font-weight:700;color:var(--primary)">₮${parseFloat(s.avg_order||0).toFixed(2)}</div>
        <div style="color:var(--gray);font-size:.9rem">Дундаж захиалга</div>
      </div>
    `;
  }

  if (!ordersData.success || !ordersData.data?.length) {
    document.getElementById('ordersEmpty').style.display = 'block';
    return;
  }

  document.getElementById('ordersContent').style.display = 'block';
  const list = document.getElementById('ordersList');
  ordersData.data.forEach(o => {
    const color = STATUS_COLORS[o.status] || '#6b7280';
    const label = STATUS_LABELS[o.status] || o.status;
    list.insertAdjacentHTML('beforeend', `
      <div style="background:var(--white);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
          <div>
            <span style="font-weight:700;font-size:1rem">Захиалга #${o.id}</span>
            <span style="color:var(--gray);font-size:.85rem;margin-left:12px">${o.created_at}</span>
          </div>
          <span style="background:${color}20;color:${color};padding:4px 14px;border-radius:20px;font-size:.8rem;font-weight:700">${label}</span>
        </div>
        <hr style="margin:14px 0;border-color:var(--border)">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <span style="color:var(--gray);font-size:.9rem">${o.item_count} бүтээгдэхүүн</span>
          <span style="font-weight:700;color:var(--primary);font-size:1.1rem">₮${parseFloat(o.total_amount).toFixed(2)}</span>
        </div>
        <div style="margin-top:8px;font-size:.85rem;color:var(--gray)">
          <i class="fas fa-map-marker-alt"></i> ${o.address || '—'}
        </div>
      </div>
    `);
  });
}

loadOrders();
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
