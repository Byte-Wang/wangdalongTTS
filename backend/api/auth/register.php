<?php
/**
 * 邮箱注册
 * POST /api/auth/register.php
 * body: { "email": "xxx@xx.com", "password": "xxx", "code": "123456" }
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

$input    = json_decode(file_get_contents('php://input'), true);
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$code     = trim($input['code'] ?? '');

// 校验
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('邮箱格式不正确');
}
if (strlen($password) < 6) {
    json_error('密码至少需要6位');
}
if (strlen($code) !== 6) {
    json_error('验证码格式不正确');
}

$db  = DB::getInstance();
$now = date('Y-m-d H:i:s');

// 校验验证码
$stmt = $db->prepare('SELECT id FROM email_verifications WHERE email = ? AND code = ? AND type = ? AND expires_at > ? AND used = 0 ORDER BY id DESC LIMIT 1');
$stmt->execute([$email, $code, 'register', $now]);
$verification = $stmt->fetch();

if (!$verification) {
    json_error('验证码无效或已过期');
}

// 检查邮箱是否已注册
$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    json_error('该邮箱已被注册');
}

// 创建用户
$passwordHash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare('INSERT INTO users (email, password_hash, email_verified, points) VALUES (?, ?, 1, 500)');
$stmt->execute([$email, $passwordHash]);
$userId = (int) $db->lastInsertId();

// 标记验证码已使用
$stmt = $db->prepare('UPDATE email_verifications SET used = 1 WHERE id = ?');
$stmt->execute([$verification['id']]);

// 生成 token
$token = generate_token($userId);

json_success([
    'user_id' => $userId,
    'email'   => $email,
    'token'   => $token,
    'points'  => 500,
], '注册成功');
