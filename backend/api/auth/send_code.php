<?php
/**
 * 发送邮箱验证码
 * POST /api/auth/send_code.php
 * body: { "email": "xxx@xx.com", "type": "register" }
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/mailer.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$type  = trim($input['type'] ?? 'register');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('邮箱格式不正确');
}
if (!in_array($type, ['register', 'login'])) {
    json_error('验证码类型无效');
}

$now = date('Y-m-d H:i:s');
$db  = DB::getInstance();

// 检查1分钟内是否已发送
$stmt = $db->prepare('SELECT id FROM email_verifications WHERE email = ? AND type = ? AND created_at > DATE_SUB(?, INTERVAL 1 MINUTE)');
$stmt->execute([$email, $type, $now]);
if ($stmt->fetch()) {
    json_error('发送过于频繁，请1分钟后再试');
}

// 生成6位验证码
$code = random_int(100000, 999999);

$stmt = $db->prepare('INSERT INTO email_verifications (email, code, type, expires_at) VALUES (?, ?, ?, DATE_ADD(?, INTERVAL 10 MINUTE))');
$stmt->execute([$email, $code, $type, $now]);

// 发送邮件（使用 SMTP 服务）
$subject = $type === 'register' ? '【王大龙语音合成工具】注册验证码' : '【王大龙语音合成工具】登录验证码';
$body    = "您的验证码是：<b>{$code}</b>，有效期10分钟。如非本人操作请忽略。";

try {
    $mailer   = new Mailer();
    $mailSent = $mailer->send($email, $subject, $body);
} catch (Exception $e) {
    error_log("SMTP 发送失败: " . $e->getMessage());
    $mailSent = false;
}

// 开发环境下将验证码打印到日志
error_log("验证码发送到 {$email}: {$code}");

json_success([
    'sent'    => $mailSent,
    'message' => $mailSent ? '验证码已发送' : '邮件发送失败，请检查邮件服务器配置',
]);
