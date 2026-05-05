<?php
require_once __DIR__ . '/config.php';

// ── Response helpers ──────────────────────────────────────────
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $message, int $code = 400): void {
    jsonResponse(['success' => false, 'message' => $message], $code);
}

// ── Input sanitization ────────────────────────────────────────
function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function getBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ── Email validation ──────────────────────────────────────────
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        && strlen($email) <= 200;
}

// ── Session helpers ───────────────────────────────────────────
function sessionStart(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function currentUser(): ?array {
    sessionStart();
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (currentUser() === null) {
        jsonError('Нэвтрэх шаардлагатай.', 401);
    }
}

// ── Product helpers ───────────────────────────────────────────
function getProducts(string $category = '', int $limit = 12, int $offset = 0): array {
    $db   = getDB();
    $stmt = $db->prepare('CALL sp_get_products(?, ?, ?)');
    $stmt->execute([$category ?: null, $limit, $offset]);
    return $stmt->fetchAll();
}

function getProductBySlug(string $slug): ?array {
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM vw_products WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row  = $stmt->fetch();
    return $row ?: null;
}

function getRelatedProducts(int $productId, int $limit = 4): array {
    $db   = getDB();
    $stmt = $db->prepare('CALL sp_related_products(?, ?)');
    $stmt->execute([$productId, $limit]);
    return $stmt->fetchAll();
}

function getCategoryStats(): array {
    return getDB()->query('SELECT * FROM vw_category_stats')->fetchAll();
}

function getCategories(): array {
    return getDB()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

// ── Cart helpers ──────────────────────────────────────────────
function getCartItems(int $userId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT c.id, c.qty, v.product_name, v.sale_price, v.original_price,
                v.discount_pct, v.image, v.slug, v.stock,
                (c.qty * v.sale_price) AS subtotal
         FROM   cart c
         JOIN   vw_products v ON v.id = c.product_id
         WHERE  c.user_id = ?'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getCartTotal(int $userId): float {
    $items = getCartItems($userId);
    return array_sum(array_column($items, 'subtotal'));
}

// ── Order stats (aggregation example) ────────────────────────
function getOrderStats(int $userId): array {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT COUNT(*)           AS total_orders,
                SUM(total_amount)  AS total_spent,
                AVG(total_amount)  AS avg_order
         FROM   orders
         WHERE  user_id = ?'
    );
    $stmt->execute([$userId]);
    return $stmt->fetch();
}
