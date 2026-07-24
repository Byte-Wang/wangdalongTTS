<?php
/**
 * 获取用户信息（积分、邮箱等）
 * GET /api/user/profile.php
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

$userId = require_auth();
$db     = DB::getInstance();

$stmt = $db->prepare('SELECT id, email, phone, email_verified, points, created_at FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    json_error('用户不存在', 404);
}

json_success([
    'user_id'        => (int) $user['id'],
    'email'          => $user['email'],
    'phone'          => $user['phone'],
    'email_verified' => (bool) $user['email_verified'],
    'points'         => (float) $user['points'],
    'created_at'     => $user['created_at'],
]);
