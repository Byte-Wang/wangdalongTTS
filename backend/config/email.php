<?php
/**
 * 邮件 SMTP 配置
 * 部署时修改下方配置即可
 */
return [
    // SMTP 服务器地址
    'host'     => getenv('SMTP_HOST') ?: 'smtp.qq.com',

    // SMTP 端口（SSL: 465, TLS: 587）
    'port'     => getenv('SMTP_PORT') ?: 465,

    // 发件人邮箱地址
    'username' => getenv('SMTP_USER') ?: 'wangdalong7777@qq.com',

    // 邮箱密码 / 授权码
    'password' => getenv('SMTP_PASS') ?: 'ayprwxawurmubfee',

    // 加密方式：ssl / tls / （空表示不加密）
    'encryption' => getenv('SMTP_ENCRYPTION') ?: 'ssl',

    // 发件人名称
    'from_name' => '王大龙语音合成工具',

    // 发件人地址
    'from_email' => getenv('SMTP_USER') ?: 'wangdalong7777@qq.com',
];
