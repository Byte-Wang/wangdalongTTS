<?php
/**
 * 阿里云百炼 API 配置
 */
return [
    // API Key —— 优先从环境变量读取，若无环境变量可直接写死
    // 在宝塔面板获取：https://bailian.console.aliyun.com/?tab=model#/api-key
    'api_key'        => getenv('DASHSCOPE_API_KEY') ?: '',

    // 业务空间 ID —— 优先从环境变量读取，若无环境变量可直接写死
    // 获取地址：https://help.aliyun.com/zh/model-studio/obtain-the-app-id-and-workspace-id
    'workspace_id'   => getenv('ALIYUN_WORKSPACE_ID') ?: '',

    // 非实时语音合成端点（Qwen-Audio-TTS / CosyVoice）
    'tts_endpoint'   => 'https://{workspace}.cn-beijing.maas.aliyuncs.com/api/v1/services/audio/tts/SpeechSynthesizer',

    // Qwen-TTS 语音合成端点
    'qwen_tts_endpoint' => 'https://dashscope.aliyuncs.com/api/v1/services/aigc/multimodal-generation/generation',

    // 声音复刻端点（Qwen-Audio-TTS / CosyVoice / Qwen-TTS）
    'clone_endpoint' => 'https://dashscope.aliyuncs.com/api/v1/services/audio/tts/customization',

    // 已支持的系统音色
    'system_voices_file' => __DIR__ . '/system_voices.json',
];
