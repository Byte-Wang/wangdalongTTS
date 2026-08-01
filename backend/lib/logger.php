<?php
/**
 * 本地日志工具
 * 日志文件写入 backend/logs/ 目录，按天切割
 */

class Logger {
    private static ?string $logDir = null;

    /**
     * 获取日志目录
     */
    private static function dir(): string {
        if (self::$logDir === null) {
            self::$logDir = __DIR__ . '/../logs';
            if (!is_dir(self::$logDir)) {
                @mkdir(self::$logDir, 0755, true);
            }
        }
        return self::$logDir;
    }

    /**
     * 写入日志
     */
    public static function write(string $level, string $message): void {
        $time  = date('Y-m-d H:i:s');
        $date  = date('Y-m-d');
        $file  = self::dir() . '/tts_' . $date . '.log';
        $line  = "[$time] [$level] $message" . PHP_EOL;
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message): void {
        self::write('INFO', $message);
    }

    public static function error(string $message): void {
        self::write('ERROR', $message);
    }

    public static function warn(string $message): void {
        self::write('WARN', $message);
    }

    public static function debug(string $message): void {
        self::write('DEBUG', $message);
    }
}
