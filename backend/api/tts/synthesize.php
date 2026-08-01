<?php
/**
 * 语音合成
 * POST /api/tts/synthesize.php
 * body: {
 *   "text": "要合成的文本",
 *   "voice": "longanhuan_v3.6",
 *   "model": "qwen-audio-3.0-tts-flash",
 *   "format": "wav",
 *   "sample_rate": 24000
 * }
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/tts_client.php';
require_once __DIR__ . '/../../lib/logger.php';

$userId = require_auth();
$db     = DB::getInstance();

$input      = json_decode(file_get_contents('php://input'), true);
$text       = trim($input['text'] ?? '');
$voice      = trim($input['voice'] ?? '');
$model      = trim($input['model'] ?? 'qwen-audio-3.0-tts-flash');
$format     = trim($input['format'] ?? 'wav');
$sampleRate = (int) ($input['sample_rate'] ?? 24000);

if (empty($text)) {
    json_error('请输入要合成的文本');
}
if (empty($voice)) {
    json_error('请选择音色');
}

// 计算积分消耗
// 汉字/全角字符 = 1 积分，英文字母/数字/半角字符 = 0.5 积分
$pointsCost = calculatePointsCost($text);

// 查询用户积分
$stmt = $db->prepare('SELECT points FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ((float) $user['points'] < $pointsCost) {
    json_error('积分不足，当前剩余 ' . $user['points'] . ' 积分，本次需要 ' . $pointsCost . ' 积分');
}

try {
    $client = new AliyunTTSClient();

    // 根据模型类型选择合成方式
    $category = getModelCategory($model);

    Logger::info('开始语音合成，模型=' . $model . '，音色=' . $voice . '，文本长度=' . mb_strlen($text, 'UTF-8'));
    $startTime = microtime(true);

    if ($category === 'qwen_tts') {
        $result = $client->synthesizeByQwenTTS($model, $voice, $text);
    } else {
        $result = $client->synthesizeByTTS($model, $voice, $text, $format, $sampleRate);
    }

    $apiElapsed = round((microtime(true) - $startTime) * 1000);
    $audioUrl = $result['audio_url'];
    Logger::info('阿里云合成完成，音频URL=' . $audioUrl . '，耗时=' . $apiElapsed . 'ms');

    // 下载音频到本地
    $ext = $format;
    if ($category === 'qwen_tts') {
        $ext = 'wav';
    }
    $fileName = 'tts_' . $userId . '_' . time() . '_' . uniqid() . '.' . $ext;
    $savePath = __DIR__ . '/../../uploads/voices/' . $fileName;
    Logger::info('准备保存到: ' . $savePath);
    $client->downloadAudio($audioUrl, $savePath);

    if (!file_exists($savePath)) {
        Logger::error('文件保存后仍不存在: ' . $savePath);
        throw new RuntimeException('文件保存后仍不存在: ' . $savePath);
    }
    $fileSize = filesize($savePath);
    Logger::info('文件确认存在，大小=' . $fileSize . '字节');

    // 扣除积分
    $stmt = $db->prepare('UPDATE users SET points = points - ? WHERE id = ?');
    $stmt->execute([$pointsCost, $userId]);

    // 获取音色名称（尝试从用户音色表或系统音色中查找）
    $voiceName = getVoiceName($db, $userId, $voice, $model);

    // 保存历史记录
    $stmt = $db->prepare('INSERT INTO audio_history (user_id, text, voice_name, voice_id, model, audio_path, text_length, points_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $textLength = mb_strlen($text, 'UTF-8');
    $stmt->execute([$userId, $text, $voiceName, $voice, $model, 'backend/uploads/voices/' . $fileName, $textLength, $pointsCost]);

    // 获取剩余积分
    $stmt = $db->prepare('SELECT points FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $remaining = $stmt->fetch();

    Logger::info('全部完成，历史ID=' . $db->lastInsertId() . '，剩余积分=' . $remaining['points']);

    json_success([
        'history_id'    => (int) $db->lastInsertId(),
        'audio_url'     => '/backend/uploads/voices/' . $fileName,
        'file_name'     => $fileName,
        'text_length'   => $textLength,
        'points_cost'   => $pointsCost,
        'points_remain' => (float) $remaining['points'],
        'voice_name'    => $voiceName,
        'model'         => $model,
        'created_at'    => date('Y-m-d H:i:s'),
    ], '语音合成成功');

} catch (Exception $e) {
    Logger::error('合成失败: ' . $e->getMessage());
    json_error('语音合成失败: ' . $e->getMessage(), 500);
}

/**
 * 计算积分消耗
 */
function calculatePointsCost(string $text): float {
    $cost = 0;
    $len  = mb_strlen($text, 'UTF-8');

    for ($i = 0; $i < $len; $i++) {
        $char = mb_substr($text, $i, 1, 'UTF-8');
        // 判断是否为全角字符（汉字、全角标点、中文标点等）
        $bytes = strlen($char);
        // 中文汉字和全角字符占 3 字节(UTF-8)，英文/数字占 1 字节
        if ($bytes >= 3 || isFullWidth($char)) {
            $cost += 1;
        } else {
            $cost += 0.5;
        }
    }
    return ceil($cost * 10) / 10; // 保留1位小数
}

function isFullWidth(string $char): bool {
    $code = mb_ord($char, 'UTF-8');
    if ($code === null) return false;
    // 全角 ASCII、全角片假名、半角片假名、CJK 标点等
    return ($code >= 0xFF01 && $code <= 0xFF60) ||
           ($code >= 0xFFE0 && $code <= 0xFFE6) ||
           ($code >= 0x3000 && $code <= 0x303F);
}

function getModelCategory(string $model): string {
    if (strpos($model, 'qwen-audio') !== false) return 'qwen_audio_tts';
    if (strpos($model, 'cosyvoice') !== false) return 'cosyvoice';
    if (strpos($model, 'qwen') !== false && strpos($model, 'tts') !== false) return 'qwen_tts';
    return 'cosyvoice';
}

function getVoiceName(PDO $db, int $userId, string $voiceId, string $model): string {
    // 先从用户音色中查找
    $stmt = $db->prepare('SELECT name FROM voices WHERE user_id = ? AND voice_id = ? LIMIT 1');
    $stmt->execute([$userId, $voiceId]);
    $row = $stmt->fetch();
    if ($row) return $row['name'];

    // 从系统音色中查找
    $config = json_decode(file_get_contents(__DIR__ . '/../../config/system_voices.json'), true);
    foreach ($config['models'] as $m) {
        if ($m['key'] === $model) {
            foreach ($m['voices'] as $v) {
                if ($v['voice_id'] === $voiceId) return $v['name'];
            }
        }
    }
    return $voiceId;
}
