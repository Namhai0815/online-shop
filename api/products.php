<?php
require_once __DIR__ . '/../php/functions.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $category = sanitize($_GET['category'] ?? '');
            $search   = sanitize($_GET['search']   ?? '');
            $limit    = min((int)($_GET['limit']  ?? 12), 50);
            $offset   = max((int)($_GET['offset'] ?? 0),  0);
            if ($search !== '') {
                $db   = getDB();
                $like = '%' . $search . '%';
                $stmt = $db->prepare(
                    'SELECT * FROM vw_products
                     WHERE product_name LIKE ? OR description LIKE ? OR category_name LIKE ?
                     ORDER BY is_featured DESC, created_at DESC
                     LIMIT ? OFFSET ?'
                );
                $stmt->execute([$like, $like, $like, $limit, $offset]);
                $products = $stmt->fetchAll();
            } else {
                $products = getProducts($category, $limit, $offset);
            }
            jsonResponse(['success' => true, 'data' => $products]);

        case 'detail':
            $slug = sanitize($_GET['slug'] ?? '');
            if (!$slug) jsonError('Slug шаардлагатай.');
            $product = getProductBySlug($slug);
            if (!$product) jsonError('Бүтээгдэхүүн олдсонгүй.', 404);
            $related = getRelatedProducts($product['id']);
            jsonResponse(['success' => true, 'data' => $product, 'related' => $related]);

        case 'categories':
            jsonResponse(['success' => true, 'data' => getCategories()]);

        case 'stats':
            jsonResponse(['success' => true, 'data' => getCategoryStats()]);

        default:
            jsonError('Буруу action.');
    }
} catch (PDOException $e) {
    jsonError('Өгөгдлийн сан алдаа: ' . $e->getMessage(), 500);
}
