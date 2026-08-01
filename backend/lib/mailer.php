<?php
/**
 * 简易 SMTP 邮件发送客户端
 * 无需第三方依赖，使用 PHP 内置 socket 实现
 */
class Mailer {
    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromName;
    private string $fromEmail;

    /** @var resource|null */
    private $socket = null;

    private int $timeout = 10;

    public function __construct() {
        $config = require __DIR__ . '/../config/email.php';
        $this->host       = $config['host'];
        $this->port       = (int) $config['port'];
        $this->username   = $config['username'];
        $this->password   = $config['password'];
        $this->encryption = $config['encryption'];
        $this->fromName   = $config['from_name'];
        $this->fromEmail  = $config['from_email'] ?: $this->username;
    }

    /**
     * 发送邮件
     * @param string|array $to       收件人
     * @param string       $subject  主题
     * @param string       $body     正文（支持 HTML）
     * @return bool
     * @throws Exception
     */
    public function send($to, string $subject, string $body): bool {
        if (is_array($to)) {
            $to = implode(', ', $to);
        }

        $this->connect();
        $this->handshake();
        $this->auth();

        // MAIL FROM
        $this->command("MAIL FROM:<{$this->fromEmail}>", 250);

        // RCPT TO（支持多个收件人）
        $recipients = array_map('trim', explode(',', $to));
        foreach ($recipients as $rcpt) {
            $this->command("RCPT TO:<{$rcpt}>", 250);
        }

        // DATA
        $this->command('DATA', 354);

        $message = $this->buildMessage($to, $subject, $body);
        $this->command($message . "\r\n.", 250);

        $this->command('QUIT', 221);
        $this->close();

        return true;
    }

    private function connect(): void {
        if ($this->encryption === 'ssl') {
            $host = 'ssl://' . $this->host;
        } else {
            $host = $this->host;
        }

        $this->socket = @stream_socket_client(
            "{$host}:{$this->port}",
            $errno, $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$this->socket) {
            throw new Exception("SMTP 连接失败: {$errstr} ({$errno})");
        }

        $this->readResponse(220);
    }

    private function handshake(): void {
        $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->command("EHLO {$serverName}", 250);
    }

    private function auth(): void {
        if (empty($this->username) || empty($this->password)) {
            throw new Exception('SMTP 账号或密码未配置，请检查 config/email.php');
        }

        $this->command('AUTH LOGIN', 334);
        $this->command(base64_encode($this->username), 334);
        $this->command(base64_encode($this->password), 235);
    }

    private function buildMessage(string $to, string $subject, string $body): string {
        $headers = [
            "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$this->fromEmail}>",
            "To: {$to}",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "X-Mailer: WangDaLong-TTS",
            "Date: " . date('r'),
        ];

        // 如果是纯文本，用 text/plain
        if (strip_tags($body) === $body) {
            $headers[4] = "Content-Type: text/plain; charset=UTF-8";
        }

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function command(string $cmd, int $expectedCode): string {
        if ($cmd) {
            fwrite($this->socket, $cmd . "\r\n");
        }
        return $this->readResponse($expectedCode);
    }

    private function readResponse(int $expectedCode): string {
        $response = '';
        while ($line = fgets($this->socket, 512)) {
            $response .= $line;
            // 多行响应的最后一行以 3位数字+空格 开头
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new Exception("SMTP 响应异常: [{$code}] " . trim($response));
        }

        return $response;
    }

    private function close(): void {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    public function __destruct() {
        $this->close();
    }
}
