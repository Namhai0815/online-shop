<?php
$title = 'Нэвтрэх – Fultala';
$page  = 'login';
require_once __DIR__ . '/../php/header.php';
if (currentUser()) { header('Location: ' . SITE_URL); exit; }
?>

<div class="form-page">
  <div class="form-card">
    <h2>Нэвтрэх</h2>
    <p class="form-sub">Тавтай морилно уу! Та бүртгэлтэй дансаараа нэвтэрнэ үү.</p>

    <div id="loginAlert" class="alert"></div>

    <form id="loginForm" onsubmit="return false">
      <div class="form-group">
        <label for="email"><i class="fas fa-envelope"></i> И-мэйл хаяг</label>
        <input type="email" class="form-control" id="email" placeholder="example@mail.com" required>
        <span class="error-msg" id="emailErr">И-мэйл хаяг буруу байна</span>
      </div>
      <div class="form-group">
        <label for="password"><i class="fas fa-lock"></i> Нууц үг</label>
        <input type="password" class="form-control" id="password" placeholder="Нууц үгээ оруулна уу" required>
        <span class="error-msg" id="passErr">Нууц үг хоосон байна</span>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center" id="loginBtn"
        onclick="login()">
        <i class="fas fa-sign-in-alt"></i> Нэвтрэх
      </button>
    </form>

    <p class="form-footer">
      Бүртгэлгүй юу? <a href="<?= SITE_URL ?>/pages/register.php">Бүртгүүлэх</a>
    </p>
  </div>
</div>

<script>
const SITE = window.SITE;

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

async function login() {
  const email = document.getElementById('email').value.trim();
  const pass  = document.getElementById('password').value;
  let   valid = true;

  if (!validateEmail(email)) {
    document.getElementById('emailErr').classList.add('show');
    document.getElementById('email').classList.add('error');
    valid = false;
  } else {
    document.getElementById('emailErr').classList.remove('show');
    document.getElementById('email').classList.remove('error');
  }

  if (!pass) {
    document.getElementById('passErr').classList.add('show');
    document.getElementById('password').classList.add('error');
    valid = false;
  } else {
    document.getElementById('passErr').classList.remove('show');
    document.getElementById('password').classList.remove('error');
  }

  if (!valid) return;

  const btn = document.getElementById('loginBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Нэвтэрч байна...';

  const res  = await fetch(`${SITE}/api/auth.php?action=login`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ email, password: pass })
  });
  const data = await res.json();

  const alertEl = document.getElementById('loginAlert');
  alertEl.className = 'alert show ' + (data.success ? 'alert-success' : 'alert-error');
  alertEl.textContent = data.message || (data.success ? 'Амжилттай нэвтэрлээ!' : 'И-мэйл эсвэл нууц үг буруу');

  if (data.success) {
    const dest = data.user?.role === 'admin' ? `${SITE}/pages/admin.php` : `${SITE}/`;
    setTimeout(() => { window.location = dest; }, 1000);
  } else {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Нэвтрэх';
  }
}

// Enter key support
document.addEventListener('keydown', e => { if (e.key === 'Enter') login(); });
</script>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
