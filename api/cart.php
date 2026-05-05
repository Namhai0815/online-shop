<?php
require_once __DIR__ . '/../php/functions.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

sessionStart();

$action = $_GET['action'] ?? (getBody()['action'] ?? '');
$user   = currentUser();

// Guest cart stored in session
function guestCartKey(): string { return 'guest_cart'; }

try {
    if ($user) {
        $userId = (int)$user['id'];

        switch ($action) {
            case 'get':
                $items = getCartItems($userId);
                $total = getCartTotal($userId);
                jsonResponse(['success' => true, 'items' => $items, 'total' => $total]);

            case 'add':
                $body = getBody();
                $pid  = (int)($body['product_id'] ?? 0);
                $qty  = max(1, (int)($body['qty'] ?? 1));
                if (!$pid) jsonError('product_id шаардлагатай.');

                $db = getDB();
                // Verify product exists and has stock
                $chk = $db->prepare('SELECT stock FROM products WHERE id = ?');
                $chk->execute([$pid]);
                $prod = $chk->fetch();
                if (!$prod) jsonError('Бүтээгдэхүүн олдсонгүй.', 404);
                if ($prod['stock'] < $qty) jsonError('Нөөц хүрэлцэхгүй байна.');

                $stmt = $db->prepare('CALL sp_add_to_cart(?, ?, ?)');
                $stmt->execute([$userId, $pid, $qty]);
                jsonResponse(['success' => true, 'message' => 'Сагсанд нэмэгдлээ.']);

            case 'update':
                $body   = getBody();
                $cartId = (int)($body['cart_id'] ?? 0);
                $qty    = max(1, (int)($body['qty'] ?? 1));
                $db     = getDB();
                $stmt   = $db->prepare('UPDATE cart SET qty = ? WHERE id = ? AND user_id = ?');
                $stmt->execute([$qty, $cartId, $userId]);
                jsonResponse(['success' => true, 'message' => 'Шинэчлэгдлээ.']);

            case 'remove':
                $body   = getBody();
                $cartId = (int)($body['cart_id'] ?? 0);
                $db     = getDB();
                $stmt   = $db->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
                $stmt->execute([$cartId, $userId]);
                jsonResponse(['success' => true, 'message' => 'Устгагдлаа.']);

            case 'clear':
                $db   = getDB();
                $stmt = $db->prepare('DELETE FROM cart WHERE user_id = ?');
                $stmt->execute([$userId]);
                jsonResponse(['success' => true, 'message' => 'Сагс цэвэрлэгдлээ.']);

            default:
                jsonError('Буруу action.');
        }
    } else {
        // Guest cart in session
        sessionStart();
        $cart = $_SESSION[guestCartKey()] ?? [];

        switch ($action) {
            case 'get':
                jsonResponse(['success' => true, 'items' => array_values($cart), 'total' => array_sum(array_column($cart, 'subtotal'))]);

            case 'add':
                $body = getBody();
                $pid  = (int)($body['product_id'] ?? 0);
                $qty  = max(1, (int)($body['qty'] ?? 1));
                if (!$pid) jsonError('product_id шаардлагатай.');

                $prod = getDB()->prepare('SELECT * FROM vw_products WHERE id = ?');
                $prod->execute([$pid]);
                $p = $prod->fetch();
                if (!$p) jsonError('Бүтээгдэхүүн олдсонгүй.', 404);

                if (isset($cart[$pid])) {
                    $cart[$pid]['qty'] += $qty;
                } else {
                    $cart[$pid] = [
                        'product_id'    => $pid,
                        'product_name'  => $p['product_name'],
                        'sale_price'    => $p['sale_price'],
                        'original_price'=> $p['original_price'],
                        'image'         => $p['image'],
                        'slug'          => $p['slug'],
                        'qty'           => $qty,
                    ];
                }
                $cart[$pid]['subtotal'] = $cart[$pid]['qty'] * $cart[$pid]['sale_price'];
                $_SESSION[guestCartKey()] = $cart;
                jsonResponse(['success' => true, 'message' => 'Сагсанд нэмэгдлээ.', 'count' => count($cart)]);

            case 'remove':
                $body = getBody();
                $pid  = (int)($body['product_id'] ?? 0);
                unset($cart[$pid]);
                $_SESSION[guestCartKey()] = $cart;
                jsonResponse(['success' => true]);

            case 'clear':
                $_SESSION[guestCartKey()] = [];
                jsonResponse(['success' => true]);

            default:
                jsonError('Буруу action.');
        }
    }
} catch (PDOException $e) {
    jsonError('Өгөгдлийн сан алдаа: ' . $e->getMessage(), 500);
}
