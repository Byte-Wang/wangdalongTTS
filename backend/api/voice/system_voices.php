<?php
/**
 * 获取系统音色列表和预设克隆文案
 * GET /api/voice/system_voices.php
 */

require_once __DIR__ . '/../../lib/response.php';

$configFile = __DIR__ . '/../../config/system_voices.json';
$config     = json_decode(file_get_contents($configFile), true);

// 同时获取预设克隆文案
json_success([
    'models'      => $config['models'] ?? [],
    'clone_texts' => $config['clone_texts'] ?? [],
]);
