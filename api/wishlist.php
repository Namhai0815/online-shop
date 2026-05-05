<?php
require_once __DIR__ . '/../php/functions.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

sessionStart();
requireLogin();

$user   = currentUser();
$userId = (int)$user['id'];
$action = $_GET['action'] ?? (getBody()['action'] ?? '');

try {
    switch ($action) {
        case 'get':
            $db   = getDB();
            $stmt = $db->prepare(
                'SELECT v.*, w.added_at
                 FROM   wishlist w
                 JOIN   vw_products v ON v.id = w.product_id
                 WHERE  w.user_id = ?
                 ORDER  BY w.added_at DESC'
            );
            $stmt->execute([$userId]);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);

        case 'toggle':
            $body = getBody();
            $pid  = (int)($body['product_id'] ?? 0);
            if (!$pid) jsonError('product_id шаардлагатай.');
            $db   = getDB();
            $chk  = $db->prepare('SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?');
            $chk->execute([$userId, $pid]);
            if ($chk->fetch()) {
                $db->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')
                   ->execute([$userId, $pid]);
                jsonResponse(['success' => true, 'added' => false]);
            } else {
                $db->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)')
                   ->execute([$userId, $pid]);
                jsonResponse(['success' => true, 'added' => true]);
            }

        default:
            jsonError('Буруу action.');
    }
} catch (PDOException $e) {
    jsonError('Өгөгдлийн сан алдаа: ' . $e->getMessage(), 500);
}
