<?php
$title = 'Бидний Тухай – Fultala';
$page  = 'about';
require_once __DIR__ . '/../php/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1>Бидний Тухай</h1>
    <p class="breadcrumb"><a href="<?= SITE_URL ?>">Нүүр</a> / Бидний тухай</p>
  </div>
</section>

<div class="container" style="padding:60px 20px 80px">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;margin-bottom:80px">
    <div>
      <span class="hero-badge">🌸 Манай түүх</span>
      <h2 style="font-size:2rem;margin:12px 0 20px">Цэцгийн хайраар дүүрэн <span style="color:var(--primary)">10 жил</span></h2>
      <p style="color:var(--gray);line-height:1.8;margin-bottom:16px">
        Fultala нь 2015 онд Улаанбаатар хотод байгуулагдсан. Бид Монголын анхны онлайн цэцгийн дэлгүүр болж, хэрэглэгчдэд хамгийн свеж, чанартай цэцгийг хурдан, найдвартай хүргэж байна.
      </p>
      <p style="color:var(--gray);line-height:1.8;margin-bottom:24px">
        Манай багийн 50+ цэцгийн мэргэжилтнүүд өдөр бүр шилдэг чанарын цэцгийг сонгон, гоё гоё баглаа бэлддэг. Таны баяр, хүсэл, хайрыг илэрхийлэхэд бид зөвхөн хамгийн сайн сайханыг санал болгоно.
      </p>
      <a href="<?= SITE_URL ?>/pages/shop.php" class="btn btn-primary">
        <i class="fas fa-shopping-bag"></i> Манай дэлгүүр
      </a>
    </div>
    <div style="border-radius:20px;overflow:hidden;aspect-ratio:1">
      <img src="https://images.unsplash.com/photo-1487530811176-3780de880c2d?w=600&q=80" alt="Бидний тухай" style="width:100%;height:100%;object-fit:cover">
    </div>
  </div>

  <!-- Stats -->
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px;margin-bottom:80px;text-align:center">
    <?php foreach ([['500+','Бүтээгдэхүүн'],['10K+','Хэрэглэгч'],['50K+','Захиалга'],['4.9★','Үнэлгээ']] as $s): ?>
    <div style="background:var(--light-gray);border-radius:12px;padding:32px 20px">
      <div style="font-size:2.5rem;font-weight:800;color:var(--primary)"><?= $s[0] ?></div>
      <div style="color:var(--gray);margin-top:6px"><?= $s[1] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Values -->
  <h2 class="section-title">Манай <span>Үнэт зүйлс</span></h2>
  <div class="features-grid" style="margin-top:40px">
    <?php foreach ([
      ['fas fa-leaf','Байгальд ээлтэй','Бид байгаль орчинд ээлтэй аргаар цэцэг тариалж, боловсруулдаг.'],
      ['fas fa-star','Чанарын баталгаа','Хамгийн шилдэг чанарын цэцгийг л сонгон хэрэглэгчдэд хүргэдэг.'],
      ['fas fa-heart','Хайрын илэрхийлэл','Таны хайр, баяр баясгаланг цэцгээр дамжуулан илэрхийлнэ.'],
      ['fas fa-clock','Хурдан хүргэлт','Захиалгын дараа 2-4 цагийн дотор таны хүсэлт биелнэ.'],
    ] as $v): ?>
    <div class="feature-item">
      <i class="<?= $v[0] ?>"></i>
      <h4><?= $v[1] ?></h4>
      <p><?= $v[2] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../php/footer.php'; ?>
