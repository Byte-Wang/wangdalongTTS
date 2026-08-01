<?php
/**
 * 邮箱登录（支持密码登录和验证码登录）
 * POST /api/auth/login.php
 * body: { "email": "xxx@xx.com", "password": "xxx" }
 * 或: { "email": "xxx@xx.com", "code": "123456" }
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

header('Content-Type: application/json; charset=utf-8');

$input    = json_decode(file_get_contents('php://input'), true);
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$code     = trim($input['code'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('邮箱格式不正确');
}

$db = DB::getInstance();
$stmt = $db->prepare('SELECT id, email, password_hash, email_verified, points FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    json_error('账号不存在');
}

// 密码登录
if (!empty($password)) {
    if (!password_verify($password, $user['password_hash'])) {
        json_error('密码错误');
    }
} elseif (!empty($code)) {
    // 验证码登录
    $stmt = $db->prepare('SELECT id FROM email_verifications WHERE email = ? AND code = ? AND type = ? AND expires_at > ? AND used = 0 ORDER BY id DESC LIMIT 1');
    $stmt->execute([$email, $code, 'login', date('Y-m-d H:i:s')]);
    $verification = $stmt->fetch();
    if (!$verification) {
        json_error('验证码无效或已过期');
    }
    $stmt = $db->prepare('UPDATE email_verifications SET used = 1 WHERE id = ?');
    $stmt->execute([$verification['id']]);
} else {
    json_error('请提供密码或验证码');
}

if (!$user['email_verified']) {
    json_error('邮箱未验证，请先完成验证');
}

$token = generate_token((int) $user['id']);

json_success([
    'user_id' => (int) $user['id'],
    'email'   => $user['email'],
    'token'   => $token,
    'points'  => (float) $user['points'],
], '登录成功');
