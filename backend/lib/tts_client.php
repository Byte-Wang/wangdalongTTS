<?php
/**
 * 阿里云百炼 TTS API 客户端封装
 */

require_once __DIR__ . '/../config/aliyun.php';

class AliyunTTSClient {
    private string $apiKey;
    private string $workspaceId;

    public function __construct() {
        $config = require __DIR__ . '/../config/aliyun.php';
        $this->apiKey      = $config['api_key'];
        $this->workspaceId = $config['workspace_id'];
        if (empty($this->apiKey)) {
            throw new RuntimeException('DASHSCOPE_API_KEY 未配置');
        }
    }

    /**
     * Qwen-Audio-TTS / CosyVoice 非实时语音合成
     * 返回音频 URL，有效期 24 小时
     */
    public function synthesizeByTTS(string $model, string $voice, string $text, string $format = 'wav', int $sampleRate = 24000): array {
        $endpoint = str_replace('{workspace}', $this->workspaceId, self::getConfig()['tts_endpoint']);

        $payload = [
            'model' => $model,
            'input' => [
                'text'        => $text,
                'voice'       => $voice,
                'format'      => $format,
                'sample_rate' => $sampleRate,
            ],
        ];

        $resp = $this->postJson($endpoint, $payload);
        $this->checkApiError($resp);

        // 返回音频 URL
        $url = $resp['output']['audio']['url'] ?? '';
        if (empty($url)) {
            throw new RuntimeException('未获取到音频 URL');
        }
        return ['audio_url' => $url];
    }

    /**
     * Qwen-TTS 语音合成
     */
    public function synthesizeByQwenTTS(string $model, string $voice, string $text): array {
        $config   = self::getConfig();
        $endpoint = $config['qwen_tts_endpoint'];

        $payload = [
            'model' => $model,
            'input' => [
                'text'  => $text,
                'voice' => $voice,
            ],
        ];

        $resp = $this->postJson($endpoint, $payload);
        $this->checkApiError($resp);

        $url = $resp['output']['audio']['url'] ?? '';
        if (empty($url)) {
            throw new RuntimeException('未获取到音频 URL');
        }
        return ['audio_url' => $url];
    }

    /**
     * 创建克隆音色（Qwen-Audio-TTS / CosyVoice 通用）
     * target_model: 绑定的语音合成模型
     * audio_url: 音频文件可访问 URL
     * prefix: 音色名称前缀
     */
    public function cloneVoiceByEnrollment(string $targetModel, string $audioUrl, string $prefix): array {
        $endpoint = $this->buildCloneEndpoint();

        $payload = [
            'model' => 'voice-enrollment',
            'input' => [
                'action'       => 'create_voice',
                'target_model' => $targetModel,
                'prefix'       => $prefix,
                'url'          => $audioUrl,
            ],
        ];

        $resp = $this->postJson($endpoint, $payload);
        $this->checkApiError($resp);

        $voiceId = $resp['output']['voice_id'] ?? '';
        if (empty($voiceId)) {
            throw new RuntimeException('音色创建失败，未返回 voice_id');
        }
        return ['voice_id' => $voiceId];
    }

    /**
     * 创建克隆音色（Qwen-TTS 专用）
     * 使用 base64 编码的音频数据
     */
    public function cloneVoiceByQwenEnrollment(string $targetModel, string $preferredName, string $audioDataUri): array {
        $endpoint = $this->buildCloneEndpoint();

        $payload = [
            'model' => 'qwen-voice-enrollment',
            'input' => [
                'action'         => 'create',
                'target_model'   => $targetModel,
                'preferred_name' => $preferredName,
                'audio'          => ['data' => $audioDataUri],
            ],
        ];

        $resp = $this->postJson($endpoint, $payload);
        $this->checkApiError($resp);

        $voice = $resp['output']['voice'] ?? '';
        if (empty($voice)) {
            throw new RuntimeException('音色创建失败，未返回 voice');
        }
        return ['voice_id' => $voice];
    }

    /**
     * 下载音频并保存到本地
     */
    public function downloadAudio(string $audioUrl, string $savePath): void {
        $dir = dirname($savePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new RuntimeException("无法创建目录: $dir");
            }
        }

        error_log('[TTS] 开始下载音频: ' . $audioUrl);
        $startTime = microtime(true);

        $content = @file_get_contents($audioUrl);
        if ($content === false) {
            $error = error_get_last();
            $errMsg = $error['message'] ?? '未知错误';
            error_log('[TTS] 音频下载失败: ' . $errMsg);
            throw new RuntimeException('音频下载失败: ' . $errMsg);
        }

        $elapsed = round((microtime(true) - $startTime) * 1000);
        error_log('[TTS] 下载完成，大小=' . strlen($content) . '字节，耗时=' . $elapsed . 'ms，保存至=' . $savePath);

        $written = file_put_contents($savePath, $content);
        if ($written === false || $written === 0) {
            error_log('[TTS] 文件写入失败: ' . $savePath . '，请检查目录权限');
            throw new RuntimeException("文件写入失败: $savePath，请检查目录权限");
        }

        error_log('[TTS] 文件保存成功，写入=' . $written . '字节');
    }

    // ---- 内部方法 ----

    private function postJson(string $url, array $data): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
        ]);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException("API 请求失败: $err");
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("API 响应解析失败，HTTP $httpCode");
        }
        return $decoded;
    }

    private function checkApiError(array $resp): void {
        if (!empty($resp['code']) && $resp['code'] !== 0) {
            $msg = $resp['message'] ?? '未知错误';
            throw new RuntimeException("阿里云 API 错误: $msg");
        }
    }

    private function buildCloneEndpoint(): string {
        $config = self::getConfig();
        if (!empty($this->workspaceId)) {
            return "https://{$this->workspaceId}.cn-beijing.maas.aliyuncs.com/api/v1/services/audio/tts/customization";
        }
        return $config['clone_endpoint'];
    }

    private static function getConfig(): array {
        return require __DIR__ . '/../config/aliyun.php';
    }
}
