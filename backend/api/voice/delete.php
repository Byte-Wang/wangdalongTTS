<?php
/**
 * 删除音色
 * POST /api/voice/delete.php
 * body: { "voice_id": 123 }
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

$userId = require_auth();
$db     = DB::getInstance();

$input   = json_decode(file_get_contents('php://input'), true);
$voiceId = (int) ($input['voice_id'] ?? 0);

if ($voiceId <= 0) {
    json_error('无效的音色 ID');
}

// 确认是当前用户的音色
$stmt = $db->prepare('SELECT id FROM voices WHERE id = ? AND user_id = ?');
$stmt->execute([$voiceId, $userId]);
if (!$stmt->fetch()) {
    json_error('音色不存在', 404);
}

$stmt = $db->prepare('DELETE FROM voices WHERE id = ? AND user_id = ?');
$stmt->execute([$voiceId, $userId]);

json_success(null, '音色已删除');
