<?php
require_once __DIR__ . '/../php/functions.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

sessionStart();
requireLogin();

$user   = currentUser();
$userId = (int)$user['id'];
$action = $_GET['action'] ?? (getBody()['action'] ?? '');

try {
    switch ($action) {
        case 'place':
            $body    = getBody();
            $address = sanitize($body['address'] ?? '');
            $notes   = sanitize($body['notes']   ?? '');
            if (!$address) jsonError('Хүргэлтийн хаяг шаардлагатай.');

            $db   = getDB();
            // Check cart is not empty
            $chk  = $db->prepare('SELECT COUNT(*) FROM cart WHERE user_id = ?');
            $chk->execute([$userId]);
            if ((int)$chk->fetchColumn() === 0) jsonError('Сагс хоосон байна.');

            $stmt = $db->prepare('CALL sp_place_order(?, ?, ?, @order_id)');
            $stmt->execute([$userId, $address, $notes]);
            $res  = $db->query('SELECT @order_id AS order_id')->fetch();

            jsonResponse(['success' => true, 'order_id' => $res['order_id'], 'message' => 'Захиалга амжилттай!']);

        case 'list':
            $db   = getDB();
            $stmt = $db->prepare(
                'SELECT o.id, o.total_amount, o.status, o.address, o.created_at,
                        COUNT(oi.id) AS item_count
                 FROM   orders o
                 LEFT JOIN order_items oi ON oi.order_id = o.id
                 WHERE  o.user_id = ?
                 GROUP  BY o.id
                 ORDER  BY o.created_at DESC'
            );
            $stmt->execute([$userId]);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);

        case 'detail':
            $orderId = (int)($_GET['id'] ?? 0);
            $db      = getDB();
            $stmt    = $db->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
            $stmt->execute([$orderId, $userId]);
            $order   = $stmt->fetch();
            if (!$order) jsonError('Захиалга олдсонгүй.', 404);

            $items = $db->prepare(
                'SELECT oi.*, v.product_name, v.image, v.slug
                 FROM   order_items oi
                 JOIN   vw_products v ON v.id = oi.product_id
                 WHERE  oi.order_id = ?'
            );
            $items->execute([$orderId]);
            $order['items'] = $items->fetchAll();
            jsonResponse(['success' => true, 'data' => $order]);

        case 'stats':
            jsonResponse(['success' => true, 'data' => getOrderStats($userId)]);

        default:
            jsonError('Буруу action.');
    }
} catch (PDOException $e) {
    jsonError('Өгөгдлийн сан алдаа: ' . $e->getMessage(), 500);
}
