<?php
/**
 * 下载音频文件
 * GET /api/tts/download.php?file=uploads/voices/tts_xxx.wav
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

$userId = require_auth();

$filePath = $_GET['file'] ?? '';

// 安全检查：防止路径穿越（连续两个以上的点号也一并处理）
$filePath = preg_replace('/\.\.+/', '', $filePath);
$fullPath = __DIR__ . '/../../' . ltrim($filePath, '/');

// 验证音频文件属于当前用户
$db   = DB::getInstance();
$stmt = $db->prepare("SELECT id FROM audio_history WHERE user_id = ? AND audio_path = ? LIMIT 1");
$stmt->execute([$userId, $filePath]);
if (!$stmt->fetch()) {
    json_error('无权访问该文件', 403);
}

if (!file_exists($fullPath)) {
    json_error('文件不存在', 404);
}

// 获取文件扩展名
$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'wav'  => 'audio/wav',
    'mp3'  => 'audio/mpeg',
    'm4a'  => 'audio/mp4',
];
$mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

$fileName = basename($fullPath);
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: public, max-age=86400');

readfile($fullPath);
exit;
