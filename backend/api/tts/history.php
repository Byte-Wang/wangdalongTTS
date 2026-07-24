<?php
/**
 * 获取语音生成历史
 * GET /api/tts/history.php?page=1&limit=20
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

$userId = require_auth();
$db     = DB::getInstance();

$page  = max(1, (int) ($_GET['page'] ?? 1));
$limit = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

// 总数
$stmt = $db->prepare('SELECT COUNT(*) FROM audio_history WHERE user_id = ?');
$stmt->execute([$userId]);
$total = (int) $stmt->fetchColumn();

// 列表
$stmt = $db->prepare('SELECT id, text, voice_name, voice_id, model, audio_path, text_length, points_cost, created_at FROM audio_history WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
$stmt->execute([$userId, $limit, $offset]);
$list = $stmt->fetchAll();

foreach ($list as &$item) {
    $item['id']          = (int) $item['id'];
    $item['text_length'] = (int) $item['text_length'];
    $item['points_cost'] = (float) $item['points_cost'];
}

json_success([
    'list'     => $list,
    'total'    => $total,
    'page'     => $page,
    'limit'    => $limit,
    'pages'    => (int) ceil($total / $limit),
]);
