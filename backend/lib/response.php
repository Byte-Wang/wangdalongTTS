<?php
/**
 * 统一 JSON 响应封装
 */

function json_success($data = null, string $message = '成功'): void {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code'    => 0,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $httpCode = 400, int $code = -1): void {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code'    => $code,
        'message' => $message,
        'data'    => null,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
