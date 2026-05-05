<?php
$title = 'Холбоо Барих – Fultala';
$page  = 'contact';
require_once __DIR__ . '/../php/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>Холбоо Барих</h1>
    <p class="breadcrumb"><a href="<?= SITE_URL ?>">Нүүр</a> / Холбоо барих</p>
  </div>
</section>

<div class="container" style="padding:60px 20px 80px">
  <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:50px;align-items:start">
    <div>
      <h2 style="margin-bottom:24px">Бидэнтэй холбоо барина уу</h2>
      <?php foreach ([
        ['fas fa-map-marker-alt','Хаяг','Сүхбаатар дүүрэг, 1-р хороо, Найрамдлын гудамж 15, Улаанбаатар'],
        ['fas fa-phone','Утас','+976 9911-2233'],
        ['fas fa-envelope','И-мэйл','info@fultala.mn'],
        ['fas fa-clock','Ажлын цаг','Даваа–Бямба: 09:00–20:00'],
      ] as $c): ?>
      <div style="display:flex;gap:14px;margin-bottom:24px">
        <div style="width:44px;height:44px;background:rgba(232,76,61,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="<?= $c[0] ?>" style="color:var(--primary)"></i>
        </div>
        <div>
          <p style="font-weight:600;margin-bottom:4px"><?= $c[1] ?></p>
          <p style="color:var(--gray);font-size:.9rem"><?= $c[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="form-card" style="box-shadow:var(--shadow)">
      <h3 style="margin-bottom:20px">Мессеж илгээх</h3>
      <div id="contactAlert" class="alert"></div>
      <div class="form-row">
        <div class="form-group">
          <label>Нэр</label>
          <input type="text" class="form-control" id="cName" placeholder="Таны нэр">
        </div>
        <div class="form-group">
          <label>Утас</label>
          <input type="tel" class="form-control" id="cPhone" placeholder="99XXXXXX">
        </div>
      </div>
      <div class="form-group">
        <label>И-мэйл</label>
        <input type="email" class="form-control" id="cEmail" placeholder="example@mail.com">
        <span class="error-msg" id="cEmailErr">И-мэйл хаяг буруу байна</span>
      </div>
      <div class="form-group">
        <label>Гарчиг</label>
        <input type="text" class="form-control" id="cSubject" placeholder="Асуудлын гарчиг">
      </div>
      <div class="form-group">
        <label>Мессеж</label>
        <textarea class="form-control" id="cMessage" rows="4" placeholder="Таны мессеж..."></textarea>
        <span class="error-msg" id="cMsgErr">Мессеж шаардлагатай</span>
      </div>
      <button class="btn btn-primary" style="width:100%;justify-content:center" onclick="sendMessage()">
        <i class="fas fa-paper-plane"></i> Илгээх
      </button>
    </div>
  </div>
</div>

<script>
function validateEmail(e) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e); }

function sendMessage() {
  const email = document.getElementById('cEmail').value.trim();
  const msg   = document.getElementById('cMessage').value.trim();
  let   valid = true;

  if (email && !validateEmail(email)) {
    document.getElementById('cEmailErr').classList.add('show'); valid = false;
  } else document.getElementById('cEmailErr').classList.remove('show');

  if (!msg) {
    document.getElementById('cMsgErr').classList.add('show'); valid = false;
  } else document.getElementById('cMsgErr').classList.remove('show');

  if (!valid) return;

  // Simulate send
  const alertEl = document.getElementById('contactAlert');
  alertEl.className = 'alert show alert-success';
  alertEl.textContent = 'Мессеж амжилттай илгээгдлээ. Бид удалгүй холбогдно!';
  document.getElementById('cMessage').value = '';
}
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
