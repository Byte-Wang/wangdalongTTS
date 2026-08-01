<?php
/**
 * 获取用户音色列表
 * GET /api/voice/list.php
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';

$userId = require_auth();
$db     = DB::getInstance();

$stmt = $db->prepare('SELECT id, name, voice_id, model, category, created_at FROM voices WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$voices = $stmt->fetchAll();

// 转换类型
foreach ($voices as &$v) {
    $v['id'] = (int) $v['id'];
}

json_success($voices);
