<?php
/**
 * JWT 鉴权中间件
 * 使用简单的 HMAC-SHA256 签名 token
 */

define('AUTH_SECRET', getenv('AUTH_SECRET') ?: 'tts_platform_secret_key_2026');
define('TOKEN_EXPIRE', 86400 * 7); // token 有效期 7 天

/**
 * 生成 JWT token
 */
function generate_token(int $userId): string {
    $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode([
        'user_id' => $userId,
        'exp'     => time() + TOKEN_EXPIRE,
        'iat'     => time(),
    ]));
    $signature = base64url_encode(
        hash_hmac('sha256', "$header.$payload", AUTH_SECRET, true)
    );
    return "$header.$payload.$signature";
}

/**
 * 验证 token，返回 user_id，失败返回 null
 */
function verify_token(string $token): ?int {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $payload, $signature] = $parts;
    $expected = base64url_encode(
        hash_hmac('sha256', "$header.$payload", AUTH_SECRET, true)
    );
    if (!hash_equals($expected, $signature)) return null;

    $data = json_decode(base64url_decode($payload), true);
    if (!$data || ($data['exp'] ?? 0) < time()) return null;

    return (int) $data['user_id'];
}

/**
 * 从请求头中获取当前用户 ID，未登录则拦截
 */
function require_auth(): int {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
        json_error('未登录或 token 无效', 401);
    }
    $userId = verify_token($matches[1]);
    if ($userId === null) {
        json_error('token 已过期或无效', 401);
    }
    return $userId;
}

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}
