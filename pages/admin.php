<?php
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/functions.php';
sessionStart();

$user = currentUser();
if (!$user) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}
if (($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    die('<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:100px">
    <h2 style="color:#e11d48">⛔ Admin эрх хэрэгтэй</h2>
    <p><a href="' . SITE_URL . '">Нүүр хуудас руу буцах</a></p>
    </body></html>');
}
?>
<!DOCTYPE html>
<html lang="mn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin – Fultala</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f8fafc;color:#1e293b}
.wrap{display:flex;min-height:100vh}

/* Sidebar */
.sidebar{width:230px;background:#1e293b;color:#94a3b8;flex-shrink:0;display:flex;flex-direction:column}
.sb-logo{padding:22px 18px;border-bottom:1px solid #334155}
.sb-logo h1{color:#fff;font-size:1.15rem;font-weight:700}
.sb-logo h1 span{color:#f97316}
.sb-logo small{color:#64748b;font-size:.72rem}
.sb-nav{padding:10px 0;flex:1}
.sb-item{display:flex;align-items:center;gap:10px;padding:11px 18px;cursor:pointer;transition:.15s;font-size:.88rem;border-left:3px solid transparent}
.sb-item:hover{background:#334155;color:#e2e8f0}
.sb-item.active{background:#334155;color:#fff;border-left-color:#f97316}
.sb-item i{width:16px;text-align:center}

/* Main */
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{background:#fff;border-bottom:1px solid #e2e8f0;padding:0 24px;height:58px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.topbar h2{font-size:.95rem;font-weight:600}
.tb-right{display:flex;align-items:center;gap:10px}
.tb-user{font-size:.83rem;color:#64748b;display:flex;align-items:center;gap:6px}
.content{padding:24px;overflow-y:auto;flex:1}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.scard{background:#fff;border-radius:12px;padding:18px;border:1px solid #e2e8f0;display:flex;flex-direction:column;gap:6px}
.scard-top{display:flex;align-items:center;justify-content:space-between}
.scard-label{font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:500}
.scard-val{font-size:1.75rem;font-weight:700;color:#1e293b}
.sicon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem}
.sicon.or{background:#fff7ed;color:#f97316}
.sicon.gn{background:#f0fdf4;color:#22c55e}
.sicon.bl{background:#eff6ff;color:#3b82f6}
.sicon.re{background:#fff1f2;color:#f43f5e}

/* Toolbar */
.toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;gap:12px;flex-wrap:wrap}
.searchbox{display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 14px}
.searchbox input{border:none;outline:none;font-size:.88rem;width:200px;background:transparent}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:.85rem;font-weight:500;cursor:pointer;border:none;transition:.15s;text-decoration:none}
.btn-primary{background:#f97316;color:#fff}
.btn-primary:hover{background:#ea6c0b}
.btn-danger{background:#fee2e2;color:#ef4444}
.btn-danger:hover{background:#fecaca}
.btn-edit{background:#eff6ff;color:#3b82f6}
.btn-edit:hover{background:#dbeafe}
.btn-ghost{background:#f1f5f9;color:#475569}
.btn-ghost:hover{background:#e2e8f0}
.btn-sm{padding:6px 11px;font-size:.8rem}

/* Table */
.tcard{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden}
table{width:100%;border-collapse:collapse}
th{background:#f8fafc;padding:11px 14px;text-align:left;font-size:.74rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap}
td{padding:11px 14px;border-bottom:1px solid #f1f5f9;font-size:.86rem;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafafa}
.thumb{width:46px;height:46px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0}
.thumb-ph{width:46px;height:46px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#cbd5e1;font-size:1.1rem}
.badge{display:inline-block;padding:3px 8px;border-radius:20px;font-size:.73rem;font-weight:600}
.b-orange{background:#fff7ed;color:#f97316}
.b-green{background:#f0fdf4;color:#16a34a}
.b-blue{background:#eff6ff;color:#3b82f6}
.acts{display:flex;gap:5px}
.empty td{text-align:center;padding:56px;color:#94a3b8;font-size:.9rem}
.empty td i{display:block;font-size:2rem;margin-bottom:10px;color:#cbd5e1}

/* Modal */
.backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:999;padding:16px}
.backdrop.open{display:flex}
.modal{background:#fff;border-radius:16px;width:100%;max-width:600px;max-height:92vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.18)}
.mhead{padding:18px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:#fff;z-index:1}
.mhead h3{font-size:.95rem;font-weight:600}
.mclose{background:none;border:none;font-size:1.3rem;cursor:pointer;color:#94a3b8;line-height:1;padding:2px 6px;border-radius:4px}
.mclose:hover{background:#f1f5f9;color:#374151}
.mbody{padding:22px}
.fgrid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.fg{display:flex;flex-direction:column;gap:5px}
.fg.full{grid-column:1/-1}
.flabel{font-size:.8rem;font-weight:500;color:#374151}
.fc{border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:.88rem;outline:none;transition:border-color .15s;width:100%;font-family:inherit}
.fc:focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.1)}
textarea.fc{resize:vertical;min-height:76px}
.fcheck{display:flex;align-items:center;gap:8px;font-size:.88rem;cursor:pointer}
.fcheck input{width:15px;height:15px;accent-color:#f97316;cursor:pointer}

/* Upload area */
.uarea{border:2px dashed #d1d5db;border-radius:10px;padding:18px;text-align:center;cursor:pointer;transition:.15s;position:relative}
.uarea:hover{border-color:#f97316;background:#fff7ed}
.uarea input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer}
.uarea i{font-size:1.6rem;color:#cbd5e1;margin-bottom:6px;display:block}
.uarea p{font-size:.82rem;color:#94a3b8}
.uarea small{font-size:.75rem;color:#cbd5e1}
.ipreview{width:100%;height:150px;object-fit:cover;border-radius:8px;margin-top:8px;display:none;border:1px solid #e2e8f0}

.mfoot{padding:14px 22px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px}

/* Confirm modal */
.cmodal{max-width:360px}
.cbody{padding:28px 22px;text-align:center}
.cbody i{font-size:2.8rem;color:#ef4444;margin-bottom:12px;display:block}
.cbody h3{margin-bottom:8px;font-size:1rem}
.cbody p{color:#64748b;font-size:.85rem}

/* Toast */
.toasts{position:fixed;bottom:20px;right:20px;display:flex;flex-direction:column;gap:7px;z-index:9999}
.atoast{display:flex;align-items:center;gap:9px;background:#1e293b;color:#fff;padding:11px 16px;border-radius:10px;font-size:.85rem;box-shadow:0 4px 16px rgba(0,0,0,.18);animation:tin .22s ease}
.atoast.success{border-left:3px solid #22c55e}
.atoast.error{border-left:3px solid #ef4444}
@keyframes tin{from{transform:translateX(50px);opacity:0}to{transform:none;opacity:1}}

@media(max-width:900px){.stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.sidebar{display:none}.fgrid{grid-template-columns:1fr}.stats{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="wrap">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sb-logo">
      <h1>🌸 Ful<span>tala</span></h1>
      <small>Admin Panel</small>
    </div>
    <nav class="sb-nav">
      <div class="sb-item active">
        <i class="fas fa-box"></i> Бүтээгдэхүүн
      </div>
      <div class="sb-item" onclick="window.open('<?= SITE_URL ?>', '_blank')">
        <i class="fas fa-store"></i> Дэлгүүр харах
      </div>
    </nav>
  </aside>

  <!-- Main -->
  <div class="main">
    <div class="topbar">
      <h2>Бүтээгдэхүүн удирдлага</h2>
      <div class="tb-right">
        <div class="tb-user"><i class="fas fa-user-circle"></i><?= htmlspecialchars($user['name']) ?></div>
        <button class="btn btn-ghost btn-sm" onclick="adminLogout()">
          <i class="fas fa-sign-out-alt"></i> Гарах
        </button>
      </div>
    </div>

    <div class="content">

      <!-- Stats -->
      <div class="stats">
        <div class="scard">
          <div class="scard-top">
            <span class="scard-label">Нийт бүтээгдэхүүн</span>
            <div class="sicon or"><i class="fas fa-box"></i></div>
          </div>
          <div class="scard-val" id="stTotal">—</div>
        </div>
        <div class="scard">
          <div class="scard-top">
            <span class="scard-label">Ангилал</span>
            <div class="sicon bl"><i class="fas fa-tags"></i></div>
          </div>
          <div class="scard-val" id="stCats">—</div>
        </div>
        <div class="scard">
          <div class="scard-top">
            <span class="scard-label">Нийт нөөц</span>
            <div class="sicon gn"><i class="fas fa-cubes"></i></div>
          </div>
          <div class="scard-val" id="stStock">—</div>
        </div>
        <div class="scard">
          <div class="scard-top">
            <span class="scard-label">Онцлох</span>
            <div class="sicon re"><i class="fas fa-star"></i></div>
          </div>
          <div class="scard-val" id="stFeat">—</div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <div class="searchbox">
          <i class="fas fa-search" style="color:#94a3b8"></i>
          <input type="text" id="searchInput" placeholder="Бүтээгдэхүүн хайх..." oninput="filterTable(this.value)">
        </div>
        <button class="btn btn-primary" onclick="openAdd()">
          <i class="fas fa-plus"></i> Бүтээгдэхүүн нэмэх
        </button>
      </div>

      <!-- Table -->
      <div class="tcard">
        <table>
          <thead>
            <tr>
              <th>Зураг</th>
              <th>Нэр</th>
              <th>Ангилал</th>
              <th>Үнэ / Хямдарсан</th>
              <th>Хямдрал</th>
              <th>Нөөц</th>
              <th>Онцлох</th>
              <th>Үйлдэл</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <tr class="empty"><td colspan="8"><i class="fas fa-spinner fa-spin"></i>Ачаалж байна...</td></tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<!-- Add / Edit Modal -->
<div class="backdrop" id="pModal">
  <div class="modal">
    <div class="mhead">
      <h3 id="mTitle">Бүтээгдэхүүн нэмэх</h3>
      <button class="mclose" onclick="closeModal()">&times;</button>
    </div>
    <div class="mbody">
      <input type="hidden" id="fId">
      <div class="fgrid">
        <div class="fg full">
          <label class="flabel">Бүтээгдэхүүний нэр *</label>
          <input type="text" class="fc" id="fName" placeholder="жишээ: Улаан Сарнайн Баглаа">
        </div>
        <div class="fg">
          <label class="flabel">Ангилал *</label>
          <select class="fc" id="fCat"></select>
        </div>
        <div class="fg">
          <label class="flabel">Үндсэн үнэ (₮) *</label>
          <input type="number" class="fc" id="fPrice" min="0" step="0.01" placeholder="0.00">
        </div>
        <div class="fg">
          <label class="flabel">Хямдрал (%)</label>
          <input type="number" class="fc" id="fDisc" min="0" max="100" value="0">
        </div>
        <div class="fg">
          <label class="flabel">Нөөц (ширхэг)</label>
          <input type="number" class="fc" id="fStock" min="0" value="0">
        </div>
        <div class="fg full">
          <label class="flabel">Тайлбар</label>
          <textarea class="fc" id="fDesc" placeholder="Бүтээгдэхүүний тайлбар..."></textarea>
        </div>
        <div class="fg full">
          <label class="flabel">Зураг</label>
          <div class="uarea" id="uploadArea">
            <input type="file" id="fileInput" accept="image/*" onchange="handleFile(this)">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Зураг оруулах (JPG, PNG, WEBP)</p>
            <small>Дарж сонго эсвэл чирж авчир — хамгийн ихдээ 5MB</small>
          </div>
          <img id="imgPrev" class="ipreview" alt="preview">
          <input type="text" class="fc" id="fImg" placeholder="Эсвэл зурагны URL оруул" style="margin-top:8px" oninput="prevUrl(this.value)">
        </div>
        <div class="fg full">
          <label class="fcheck">
            <input type="checkbox" id="fFeat">
            Онцлох бүтээгдэхүүнд харуулах
          </label>
        </div>
      </div>
    </div>
    <div class="mfoot">
      <button class="btn btn-ghost" onclick="closeModal()">Болих</button>
      <button class="btn btn-primary" id="saveBtn" onclick="save()">
        <i class="fas fa-save"></i> Хадгалах
      </button>
    </div>
  </div>
</div>

<!-- Delete Confirm Modal -->
<div class="backdrop" id="delModal">
  <div class="modal cmodal">
    <div class="mhead">
      <h3>Устгах уу?</h3>
      <button class="mclose" onclick="closeDel()">&times;</button>
    </div>
    <div class="cbody">
      <i class="fas fa-triangle-exclamation"></i>
      <h3 id="delName"></h3>
      <p style="margin-top:6px">Устгасны дараа буцааж сэргээх боломжгүй.</p>
    </div>
    <div class="mfoot">
      <button class="btn btn-ghost" onclick="closeDel()">Болих</button>
      <button class="btn btn-danger" id="delBtn" onclick="doDelete()">
        <i class="fas fa-trash"></i> Устгах
      </button>
    </div>
  </div>
</div>

<div class="toasts" id="toasts"></div>

<script>
const API  = '<?= SITE_URL ?>/api/admin.php';
const SITE = '<?= SITE_URL ?>';
let products = [], categories = [], delId = null;

// ── Toast ────────────────────────────────────────────────────────
function toast(msg, type = 'success') {
  const w = document.getElementById('toasts');
  const t = document.createElement('div');
  t.className = `atoast ${type}`;
  t.innerHTML = `<i class="fas ${type==='success'?'fa-circle-check':'fa-circle-xmark'}"></i>${msg}`;
  w.appendChild(t);
  setTimeout(() => t.remove(), 3200);
}

// ── Data ─────────────────────────────────────────────────────────
async function loadProducts() {
  try {
    const r = await fetch(`${API}?action=list`);
    const d = await r.json();
    if (!d.success) { toast(d.message, 'error'); return; }
    products = d.data;
    renderTable(products);
    updateStats();
  } catch {
    toast('Серверт холбогдоход алдаа гарлаа', 'error');
  }
}

async function loadCategories() {
  const r = await fetch(`${API}?action=categories`);
  const d = await r.json();
  if (!d.success) return;
  categories = d.data;
  const sel = document.getElementById('fCat');
  sel.innerHTML = categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
}

function updateStats() {
  document.getElementById('stTotal').textContent = products.length;
  document.getElementById('stCats').textContent  = new Set(products.map(p => p.category_id)).size;
  document.getElementById('stStock').textContent = products.reduce((s, p) => s + parseInt(p.stock||0), 0);
  document.getElementById('stFeat').textContent  = products.filter(p => p.is_featured == 1).length;
}

// ── Render ───────────────────────────────────────────────────────
function renderTable(list) {
  const tb = document.getElementById('tbody');
  if (!list.length) {
    tb.innerHTML = '<tr class="empty"><td colspan="8"><i class="fas fa-box-open"></i>Бүтээгдэхүүн олдсонгүй</td></tr>';
    return;
  }
  tb.innerHTML = list.map(p => {
    const sale = (parseFloat(p.original_price) * (1 - parseInt(p.discount_pct)/100)).toFixed(2);
    const src  = p.image ? (p.image.startsWith('http') ? p.image : SITE + '/' + p.image) : '';
    const img  = src
      ? `<img src="${src}" class="thumb" alt="" onerror="this.style.display='none'">`
      : `<div class="thumb-ph"><i class="fas fa-image"></i></div>`;
    return `<tr>
      <td>${img}</td>
      <td><strong>${esc(p.name)}</strong><br><small style="color:#94a3b8">${p.slug}</small></td>
      <td><span class="badge b-blue">${esc(p.category_name)}</span></td>
      <td>₮${parseFloat(p.original_price).toFixed(2)}<br><span style="color:#f97316;font-weight:600">₮${sale}</span></td>
      <td>${parseInt(p.discount_pct) > 0 ? `<span class="badge b-orange">${p.discount_pct}%</span>` : '—'}</td>
      <td>${p.stock}</td>
      <td>${p.is_featured==1 ? '<span class="badge b-green">★ Тийм</span>' : '—'}</td>
      <td><div class="acts">
        <button class="btn btn-edit btn-sm" onclick="openEdit(${p.id})"><i class="fas fa-pen"></i></button>
        <button class="btn btn-danger btn-sm" onclick="openDel(${p.id},'${esc(p.name)}')"><i class="fas fa-trash"></i></button>
      </div></td>
    </tr>`;
  }).join('');
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function filterTable(q) {
  const lq = q.toLowerCase();
  renderTable(products.filter(p =>
    p.name.toLowerCase().includes(lq) || p.category_name.toLowerCase().includes(lq)
  ));
}

// ── Add modal ────────────────────────────────────────────────────
function openAdd() {
  document.getElementById('mTitle').textContent   = 'Бүтээгдэхүүн нэмэх';
  document.getElementById('fId').value            = '';
  document.getElementById('fName').value          = '';
  document.getElementById('fPrice').value         = '';
  document.getElementById('fDisc').value          = '0';
  document.getElementById('fStock').value         = '0';
  document.getElementById('fDesc').value          = '';
  document.getElementById('fImg').value           = '';
  document.getElementById('fFeat').checked        = false;
  document.getElementById('fileInput').value      = '';
  document.getElementById('imgPrev').style.display = 'none';
  document.getElementById('pModal').classList.add('open');
  document.getElementById('fName').focus();
}

// ── Edit modal ───────────────────────────────────────────────────
function openEdit(id) {
  const p = products.find(x => x.id == id);
  if (!p) return;
  document.getElementById('mTitle').textContent    = 'Бүтээгдэхүүн засах';
  document.getElementById('fId').value             = p.id;
  document.getElementById('fName').value           = p.name;
  document.getElementById('fCat').value            = p.category_id;
  document.getElementById('fPrice').value          = p.original_price;
  document.getElementById('fDisc').value           = p.discount_pct;
  document.getElementById('fStock').value          = p.stock;
  document.getElementById('fDesc').value           = p.description || '';
  document.getElementById('fFeat').checked         = p.is_featured == 1;
  document.getElementById('fileInput').value       = '';
  const img = p.image || '';
  document.getElementById('fImg').value            = img;
  const prev = document.getElementById('imgPrev');
  if (img) { prev.src = img.startsWith('http') ? img : SITE + '/' + img; prev.style.display = 'block'; }
  else prev.style.display = 'none';
  document.getElementById('pModal').classList.add('open');
}

function closeModal() { document.getElementById('pModal').classList.remove('open'); }

// ── Save ─────────────────────────────────────────────────────────
async function save() {
  const name  = document.getElementById('fName').value.trim();
  const catId = document.getElementById('fCat').value;
  const price = document.getElementById('fPrice').value;
  if (!name)  { toast('Нэр оруулна уу', 'error'); return; }
  if (!catId) { toast('Ангилал сонгоно уу', 'error'); return; }
  if (!price || parseFloat(price) <= 0) { toast('Үнэ оруулна уу', 'error'); return; }

  const btn = document.getElementById('saveBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Хадгалж байна...';

  let imageUrl = document.getElementById('fImg').value.trim();
  const fileInput = document.getElementById('fileInput');
  if (fileInput.files[0]) {
    const fd = new FormData();
    fd.append('image', fileInput.files[0]);
    try {
      const ur = await fetch(`${API}?action=upload`, { method: 'POST', body: fd });
      const ud = await ur.json();
      if (ud.success) imageUrl = ud.full_url;
      else { toast(ud.message, 'error'); resetBtn(); return; }
    } catch { toast('Зураг upload хийхэд алдаа гарлаа', 'error'); resetBtn(); return; }
  }

  const id     = document.getElementById('fId').value;
  const action = id ? 'update' : 'create';
  const body   = {
    name,
    category_id:    parseInt(catId),
    original_price: parseFloat(price),
    discount_pct:   parseInt(document.getElementById('fDisc').value || 0),
    stock:          parseInt(document.getElementById('fStock').value || 0),
    description:    document.getElementById('fDesc').value.trim(),
    image:          imageUrl || null,
    is_featured:    document.getElementById('fFeat').checked,
  };
  if (id) body.id = parseInt(id);

  try {
    const res  = await fetch(`${API}?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (data.success) { toast(data.message); closeModal(); loadProducts(); }
    else toast(data.message || 'Алдаа гарлаа', 'error');
  } catch { toast('Серверт холбогдоход алдаа гарлаа', 'error'); }
  resetBtn();
}

function resetBtn() {
  const btn = document.getElementById('saveBtn');
  btn.disabled = false;
  btn.innerHTML = '<i class="fas fa-save"></i> Хадгалах';
}

// ── Delete ───────────────────────────────────────────────────────
function openDel(id, name) {
  delId = id;
  document.getElementById('delName').textContent = name;
  document.getElementById('delModal').classList.add('open');
}
function closeDel() { document.getElementById('delModal').classList.remove('open'); delId = null; }

async function doDelete() {
  if (!delId) return;
  const btn = document.getElementById('delBtn');
  btn.disabled = true;
  try {
    const res  = await fetch(`${API}?action=delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: delId }),
    });
    const data = await res.json();
    if (data.success) { toast(data.message); closeDel(); loadProducts(); }
    else toast(data.message || 'Алдаа гарлаа', 'error');
  } catch { toast('Серверт холбогдоход алдаа гарлаа', 'error'); }
  btn.disabled = false;
}

// ── Image ────────────────────────────────────────────────────────
function handleFile(input) {
  if (!input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    const p = document.getElementById('imgPrev');
    p.src = e.target.result;
    p.style.display = 'block';
    document.getElementById('fImg').value = '';
  };
  reader.readAsDataURL(input.files[0]);
}

function prevUrl(url) {
  const p = document.getElementById('imgPrev');
  if (url) { p.src = url; p.style.display = 'block'; }
  else p.style.display = 'none';
}

// ── Backdrop close ───────────────────────────────────────────────
document.getElementById('pModal').addEventListener('click', e => { if (e.target === document.getElementById('pModal')) closeModal(); });
document.getElementById('delModal').addEventListener('click', e => { if (e.target === document.getElementById('delModal')) closeDel(); });

// ── Logout ───────────────────────────────────────────────────────
async function adminLogout() {
  await fetch(`${SITE}/api/auth.php?action=logout`, { method: 'POST' });
  window.location = `${SITE}/pages/login.php`;
}

// ── Init ─────────────────────────────────────────────────────────
loadCategories();
loadProducts();
</script>
</body>
</html>
