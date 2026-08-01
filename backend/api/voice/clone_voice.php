<?php
/**
 * 克隆音色（上传音频文件或使用录音 URL）
 * POST /api/voice/clone_voice.php
 * body: {
 *   "name": "我的音色",
 *   "model": "qwen-audio-3.0-tts-flash",
 *   "audio_url": "https://xxx.wav"   // 二选一：音频 URL
 *   或上传文件
 * }
 */

require_once __DIR__ . '/../../lib/response.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/tts_client.php';
require_once __DIR__ . '/../../lib/logger.php';

$userId = require_auth();
$db     = DB::getInstance();

// 克隆音色固定消耗 50 积分
$cloneCost = 50;

// 查询用户积分
$stmt = $db->prepare('SELECT points FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ((float) $user['points'] < $cloneCost) {
    json_error('积分不足，当前剩余 ' . $user['points'] . ' 积分，克隆音色需要 ' . $cloneCost . ' 积分');
}

$name     = trim($_POST['name'] ?? '');
$model    = trim($_POST['model'] ?? '');
$audioUrl = trim($_POST['audio_url'] ?? '');

if (empty($name)) {
    json_error('音色名称不能为空');
}
if (empty($model)) {
    json_error('请选择绑定的模型');
}

// 处理音频：优先使用上传文件，其次使用 URL
if (empty($audioUrl) && isset($_FILES['audio_file'])) {
    $file = $_FILES['audio_file'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // 校验文件类型和大小
    $allowed = ['wav', 'mp3', 'm4a'];
    if (!in_array($ext, $allowed)) {
        json_error('仅支持 WAV、MP3、M4A 格式');
    }
    if ($file['size'] > 10 * 1024 * 1024) {
        json_error('文件大小不能超过 10MB');
    }

    $uploadDir = __DIR__ . '/../../uploads/voices/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName  = 'clone_' . $userId . '_' . time() . '.' . $ext;
    $filePath  = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        json_error('文件上传失败');
    }

    // 构建对外的可访问 URL（阿里云服务器需要能访问到）
    $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $audioUrl  = $scheme . '://' . $host . '/backend/uploads/voices/' . $fileName;

    Logger::info('音频文件可访问URL: ' . $audioUrl);
}

if (empty($audioUrl)) {
    json_error('请上传音频文件或提供音频 URL');
}

/**
 * 根据中文名生成合法的英文 prefix
 * 阿里云要求：纯英文字母和数字，不超过 10 个字符
 */
function generatePrefix(string $name): string {
    // 提取名称中的英文字母和数字
    preg_match_all('/[a-zA-Z0-9]+/', $name, $matches);
    $enPart = implode('', $matches[0] ?? []);
    if (strlen($enPart) >= 2) {
        // 截取前 10 个字符
        return substr(strtolower($enPart), 0, 10);
    }
    // 纯中文名：随机 5 位英文数字
    return substr(bin2hex(random_bytes(3)), 0, 5);
}

try {
    Logger::info('开始声音复刻，模型=' . $model . '，音色名称=' . $name);
    $client = new AliyunTTSClient();

    // 判断模型类别选择克隆方式
    $category = getModelCategory($model);

    if ($category === 'qwen_tts') {
        // Qwen-TTS 系列：使用 qwen-voice-enrollment 接口，需要 base64
        $extension = pathinfo(parse_url($audioUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
        $mimeType = $extension === 'mp3' ? 'audio/mpeg' : ($extension === 'm4a' ? 'audio/mp4' : 'audio/wav');
        $audioContent = @file_get_contents($audioUrl);
        if ($audioContent === false) {
            json_error('无法获取音频文件');
        }
        $dataUri = 'data:' . $mimeType . ';base64,' . base64_encode($audioContent);
        $preferredName = generatePrefix($name);
        $result  = $client->cloneVoiceByQwenEnrollment($model, $preferredName, $dataUri);
    } else {
        // Qwen-Audio-TTS / CosyVoice 系列：使用 voice-enrollment 接口
        $prefix = generatePrefix($name);
        $result = $client->cloneVoiceByEnrollment($model, $audioUrl, $prefix);
    }

    $voiceId = $result['voice_id'];

    // 保存到数据库
    $stmt = $db->prepare('INSERT INTO voices (user_id, name, voice_id, model, category) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $name, $voiceId, $model, 'clone']);

    // 扣除积分
    $stmt = $db->prepare('UPDATE users SET points = points - ? WHERE id = ?');
    $stmt->execute([$cloneCost, $userId]);

    Logger::info('声音复刻成功，voice_id=' . $voiceId);

    json_success([
        'id'       => (int) $db->lastInsertId(),
        'name'     => $name,
        'voice_id' => $voiceId,
        'model'    => $model,
        'category' => 'clone',
    ], '音色创建成功');

} catch (Exception $e) {
    Logger::error('声音复刻失败: ' . $e->getMessage());
    json_error('音色创建失败: ' . $e->getMessage(), 500);
}

/**
 * 判断模型所属类别
 */
function getModelCategory(string $model): string {
    if (strpos($model, 'qwen-audio') !== false) return 'qwen_audio_tts';
    if (strpos($model, 'cosyvoice') !== false) return 'cosyvoice';
    if (strpos($model, 'qwen') !== false && strpos($model, 'tts') !== false) return 'qwen_tts';
    return 'cosyvoice'; // 默认
}
