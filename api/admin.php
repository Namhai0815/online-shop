<?php
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/functions.php';

header('Content-Type: application/json; charset=utf-8');
sessionStart();

$user = currentUser();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    jsonError('Зөвшөөрөл хүрэхгүй', 403);
}

$db     = getDB();
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

function makeSlug(string $str): string {
    $ascii = preg_replace('/[^a-z0-9]+/', '-', mb_strtolower(preg_replace('/[^\x20-\x7E]/u', '', $str)));
    $ascii = trim($ascii, '-');
    return ($ascii && strlen($ascii) > 2) ? $ascii : 'tsetseg-' . time();
}

switch ($action) {

    case 'list':
        $stmt = $db->query(
            'SELECT p.id, p.name, p.slug, p.description, p.original_price, p.discount_pct,
                    p.stock, p.image, p.is_featured, p.category_id,
                    c.name AS category_name,
                    ROUND(p.original_price * (1 - p.discount_pct/100), 2) AS sale_price
             FROM products p
             JOIN categories c ON c.id = p.category_id
             ORDER BY p.created_at DESC'
        );
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'categories':
        $stmt = $db->query('SELECT * FROM categories ORDER BY name');
        jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    case 'create':
        if ($method !== 'POST') jsonError('POST хэрэгтэй');
        $b    = getBody();
        $name = trim($b['name'] ?? '');
        if (!$name) jsonError('Нэр оруулна уу');
        $catId = intval($b['category_id'] ?? 0);
        if (!$catId) jsonError('Ангилал сонгоно уу');

        $slug  = makeSlug($name);
        $check = $db->prepare('SELECT id FROM products WHERE slug=?');
        $check->execute([$slug]);
        if ($check->fetch()) $slug .= '-' . time();

        $stmt = $db->prepare(
            'INSERT INTO products (category_id, name, slug, description, original_price, discount_pct, stock, image, is_featured)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $catId,
            $name,
            $slug,
            trim($b['description'] ?? ''),
            floatval($b['original_price'] ?? 0),
            intval($b['discount_pct'] ?? 0),
            intval($b['stock'] ?? 0),
            !empty($b['image']) ? $b['image'] : null,
            !empty($b['is_featured']) ? 1 : 0,
        ]);
        jsonResponse(['success' => true, 'message' => 'Бүтээгдэхүүн нэмэгдлээ', 'id' => $db->lastInsertId()]);
        break;

    case 'update':
        if ($method !== 'POST') jsonError('POST хэрэгтэй');
        $b  = getBody();
        $id = intval($b['id'] ?? 0);
        if (!$id) jsonError('ID олдсонгүй');

        $fields = [
            'category_id'    => intval($b['category_id'] ?? 0),
            'name'           => trim($b['name'] ?? ''),
            'description'    => trim($b['description'] ?? ''),
            'original_price' => floatval($b['original_price'] ?? 0),
            'discount_pct'   => intval($b['discount_pct'] ?? 0),
            'stock'          => intval($b['stock'] ?? 0),
            'is_featured'    => !empty($b['is_featured']) ? 1 : 0,
        ];
        if (array_key_exists('image', $b)) {
            $fields['image'] = !empty($b['image']) ? $b['image'] : null;
        }

        $sets = implode(', ', array_map(fn($k) => "$k=?", array_keys($fields)));
        $vals = array_values($fields);
        $vals[] = $id;
        $db->prepare("UPDATE products SET $sets WHERE id=?")->execute($vals);
        jsonResponse(['success' => true, 'message' => 'Амжилттай шинэчлэгдлээ']);
        break;

    case 'delete':
        if ($method !== 'POST') jsonError('POST хэрэгтэй');
        $b  = getBody();
        $id = intval($b['id'] ?? 0);
        if (!$id) jsonError('ID олдсонгүй');
        $db->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Устгагдлаа']);
        break;

    case 'upload':
        if ($method !== 'POST') jsonError('POST хэрэгтэй');
        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            jsonError('Файл олдсонгүй эсвэл upload алдаа');
        }
        $file    = $_FILES['image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed)) jsonError('Зөвхөн JPG, PNG, WEBP зөвшөөрнө');
        if ($file['size'] > 5 * 1024 * 1024) jsonError('Файлын хэмжээ 5MB-аас их байж болохгүй');
        $dir = __DIR__ . '/../images/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = 'img_' . uniqid() . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $fname)) jsonError('Хадгалахад алдаа гарлаа');
        jsonResponse(['success' => true, 'url' => 'images/' . $fname, 'full_url' => SITE_URL . '/images/' . $fname]);
        break;

    default:
        jsonError('Үйлдэл олдсонгүй');
}
