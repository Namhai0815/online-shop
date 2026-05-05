<?php
require_once __DIR__ . '/../php/functions.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

sessionStart();

$action = $_GET['action'] ?? (getBody()['action'] ?? '');

try {
    switch ($action) {
        // ── Register ─────────────────────────────────────────
        case 'register':
            $body = getBody();
            $name  = sanitize($body['name']  ?? '');
            $email = trim($body['email']  ?? '');
            $pass  = $body['password'] ?? '';
            $phone = sanitize($body['phone'] ?? '');

            if (!$name || !$email || !$pass) {
                jsonError('Нэр, и-мэйл, нууц үг шаардлагатай.');
            }
            if (!validateEmail($email)) {
                jsonError('И-мэйл хаяг буруу байна.');
            }
            if (strlen($pass) < 6) {
                jsonError('Нууц үг хамгийн багадаа 6 тэмдэгт байх ёстой.');
            }

            $db   = getDB();
            $chk  = $db->prepare('SELECT id FROM users WHERE email = ?');
            $chk->execute([$email]);
            if ($chk->fetch()) {
                jsonError('Энэ и-мэйл аль хэдийн бүртгэлтэй байна.');
            }

            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $ins  = $db->prepare(
                'INSERT INTO users (name, email, password_hash, phone) VALUES (?, ?, ?, ?)'
            );
            $ins->execute([$name, $email, $hash, $phone]);

            jsonResponse(['success' => true, 'message' => 'Амжилттай бүртгэгдлээ.']);

        // ── Login ─────────────────────────────────────────────
        case 'login':
            $body  = getBody();
            $email = trim($body['email']    ?? '');
            $pass  = $body['password'] ?? '';

            if (!$email || !$pass) jsonError('И-мэйл болон нууц үг шаардлагатай.');
            if (!validateEmail($email))  jsonError('И-мэйл хаяг буруу байна.');

            $db   = getDB();
            $stmt = $db->prepare(
                'SELECT id, name, email, password_hash, role FROM users WHERE email = ?'
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($pass, $user['password_hash'])) {
                jsonError('И-мэйл эсвэл нууц үг буруу байна.', 401);
            }

            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            jsonResponse(['success' => true, 'user' => $user]);

        // ── Logout ────────────────────────────────────────────
        case 'logout':
            session_destroy();
            jsonResponse(['success' => true, 'message' => 'Гарлаа.']);

        // ── Me ────────────────────────────────────────────────
        case 'me':
            $user = currentUser();
            if (!$user) jsonError('Нэвтрээгүй байна.', 401);
            jsonResponse(['success' => true, 'user' => $user]);

        default:
            jsonError('Буруу action.');
    }
} catch (PDOException $e) {
    jsonError('Өгөгдлийн сан алдаа: ' . $e->getMessage(), 500);
}
