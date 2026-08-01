<?php
/**
 * 删除历史记录及对应音频文件
 * POST /api/tts/delete.php
 * body: { "history_id": 123 }
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

$userId = require_auth();
$db     = DB::getInstance();

$input     = json_decode(file_get_contents('php://input'), true);
$historyId = (int) ($input['history_id'] ?? 0);

if ($historyId <= 0) {
    json_error('无效的记录 ID');
}

$stmt = $db->prepare('SELECT id, audio_path FROM audio_history WHERE id = ? AND user_id = ?');
$stmt->execute([$historyId, $userId]);
$record = $stmt->fetch();

if (!$record) {
    json_error('记录不存在', 404);
}

// 删除本地文件
$filePath = __DIR__ . '/../../' . $record['audio_path'];
if (file_exists($filePath)) {
    @unlink($filePath);
}

// 删除数据库记录
$stmt = $db->prepare('DELETE FROM audio_history WHERE id = ? AND user_id = ?');
$stmt->execute([$historyId, $userId]);

json_success(null, '已删除');
