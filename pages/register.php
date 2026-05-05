<?php
$title = 'Бүртгүүлэх – Fultala';
$page  = 'register';
require_once __DIR__ . '/../php/header.php';
if (currentUser()) { header('Location: ' . SITE_URL); exit; }
?>

<div class="form-page">
  <div class="form-card">
    <h2>Бүртгүүлэх</h2>
    <p class="form-sub">Шинэ данс үүсгэж, давуу эрхтэй хэрэглэгч болно уу.</p>

    <div id="regAlert" class="alert"></div>

    <form id="regForm" onsubmit="return false">
      <div class="form-row">
        <div class="form-group">
          <label>Нэр</label>
          <input type="text" class="form-control" id="name" placeholder="Таны нэр" required>
          <span class="error-msg" id="nameErr">Нэр шаардлагатай</span>
        </div>
        <div class="form-group">
          <label>Утасны дугаар</label>
          <input type="tel" class="form-control" id="phone" placeholder="99XXXXXX">
        </div>
      </div>
      <div class="form-group">
        <label>И-мэйл хаяг</label>
        <input type="email" class="form-control" id="email" placeholder="example@mail.com" required>
        <span class="error-msg" id="emailErr">И-мэйл хаяг буруу байна</span>
      </div>
      <div class="form-group">
        <label>Нууц үг</label>
        <input type="password" class="form-control" id="password" placeholder="Хамгийн багадаа 6 тэмдэгт" required>
        <span class="error-msg" id="passErr">Нууц үг хамгийн багадаа 6 тэмдэгт байх ёстой</span>
      </div>
      <div class="form-group">
        <label>Нууц үг давтах</label>
        <input type="password" class="form-control" id="password2" placeholder="Нууц үгээ дахин оруулна уу" required>
        <span class="error-msg" id="pass2Err">Нууц үг таарахгүй байна</span>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center" id="regBtn"
        onclick="register()">
        <i class="fas fa-user-plus"></i> Бүртгүүлэх
      </button>
    </form>

    <p class="form-footer">
      Бүртгэлтэй юу? <a href="<?= SITE_URL ?>/pages/login.php">Нэвтрэх</a>
    </p>
  </div>
</div>

<script>
const SITE = window.SITE;

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showErr(id, show) {
  document.getElementById(id).classList.toggle('show', show);
  const field = id.replace('Err', '');
  const el = document.getElementById(field);
  if (el) el.classList.toggle('error', show);
}

async function register() {
  const name  = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const pass  = document.getElementById('password').value;
  const pass2 = document.getElementById('password2').value;
  let   valid = true;

  showErr('nameErr',  !name);              if (!name)  valid = false;
  showErr('emailErr', !validateEmail(email)); if (!validateEmail(email)) valid = false;
  showErr('passErr',  pass.length < 6);   if (pass.length < 6) valid = false;
  showErr('pass2Err', pass !== pass2);     if (pass !== pass2)  valid = false;

  if (!valid) return;

  const btn = document.getElementById('regBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Бүртгэж байна...';

  const res  = await fetch(`${SITE}/api/auth.php?action=register`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ name, email, password: pass, phone })
  });
  const data = await res.json();

  const alertEl = document.getElementById('regAlert');
  alertEl.className = 'alert show ' + (data.success ? 'alert-success' : 'alert-error');
  alertEl.textContent = data.message;

  if (data.success) {
    setTimeout(() => { window.location = `${SITE}/pages/login.php`; }, 1500);
  } else {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-user-plus"></i> Бүртгүүлэх';
  }
}
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
