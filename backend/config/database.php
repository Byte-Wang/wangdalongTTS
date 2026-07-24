<?php
/**
 * 数据库配置文件
 */
return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '3306',
    'dbname'   => getenv('DB_NAME') ?: 'tts_wangdalong_c',
    'username' => getenv('DB_USER') ?: 'tts_wangdalong_c',
    'password' => getenv('DB_PASS') ?: '',
    'charset'  => 'utf8mb4',
];
