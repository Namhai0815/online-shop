<?php require_once __DIR__ . '/config.php'; ?>
<!-- Features Strip -->
<section class="features-strip">
  <div class="container">
    <div class="features-grid">
      <div class="feature-item">
        <i class="fas fa-truck"></i>
        <h4>Үнэгүй хүргэлт</h4>
        <p>$50-аас дээш захиалгад үнэгүй</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-undo"></i>
        <h4>Буцаалт</h4>
        <p>7 хоногийн дотор буцаана</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-shield-alt"></i>
        <h4>Аюулгүй төлбөр</h4>
        <p>100% найдвартай гүйлгээ</p>
      </div>
      <div class="feature-item">
        <i class="fas fa-headset"></i>
        <h4>24/7 Тусламж</h4>
        <p>Цаг үргэлж танд туслана</p>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?= SITE_URL ?>/index.php" class="logo">
          <span class="logo-icon">🌸</span>
          <span style="color:#fff">Ful<span style="color:var(--primary)">tala</span></span>
        </a>
        <p>Монголын хамгийн шилдэг онлайн цэцгийн дэлгүүр. Таны хайрыг илэрхийлэх цэцгийг бид хүргэнэ.</p>
        <div class="social-links">
          <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Холбоосууд</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/index.php">Нүүр хуудас</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php">Дэлгүүр</a></li>
          <li><a href="<?= SITE_URL ?>/pages/about.php">Бидний тухай</a></li>
          <li><a href="<?= SITE_URL ?>/pages/contact.php">Холбоо барих</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Ангилал</h4>
        <ul>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?category=roses">Сарнай</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?category=mixed-bouquets">Хольмог баглаа</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?category=tropical">Тропик цэцэг</a></li>
          <li><a href="<?= SITE_URL ?>/pages/shop.php?category=orchids">Орхид</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Мэдээлэл авах</h4>
        <p style="font-size:.85rem;margin-bottom:12px;">Шинэ бүтээгдэхүүн, хямдралын мэдээлэл авах:</p>
        <div class="footer-subscribe">
          <input type="email" placeholder="И-мэйл хаяг..." id="subscribeEmail">
          <button class="btn btn-primary" style="width:100%;justify-content:center" onclick="subscribe()">
            <i class="fas fa-paper-plane"></i> Бүртгүүлэх
          </button>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Fultala Flower Shop. Бүх эрх хуулиар хамгаалагдсан.</p>
    </div>
  </div>
</footer>

<!-- Scroll to top -->
<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  <i class="fas fa-chevron-up"></i>
</button>

<script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
