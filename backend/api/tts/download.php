<?php
/**
 * 下载音频文件
 * GET /api/tts/download.php?file=uploads/voices/tts_xxx.wav
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/auth.php';

// 下载不需要登录也可以，但需要验证来源
$filePath = $_GET['file'] ?? '';

// 安全检查：防止路径穿越
$filePath = preg_replace('/\.\./', '', $filePath);
$fullPath = __DIR__ . '/../../' . ltrim($filePath, '/');

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
