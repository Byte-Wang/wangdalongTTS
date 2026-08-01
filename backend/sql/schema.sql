-- =============================================
-- 语音合成平台 - 数据库建表语句
-- =============================================

CREATE DATABASE IF NOT EXISTS `tts_platform` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tts_platform`;

-- 用户表
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(128) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL COMMENT '预留手机号字段',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '邮箱是否已验证',
  `points` DECIMAL(10,1) NOT NULL DEFAULT 500.0 COMMENT '积分余额',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 邮箱验证码表
CREATE TABLE `email_verifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(128) NOT NULL,
  `code` VARCHAR(10) NOT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'register' COMMENT 'register 注册 / login 登录',
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email_type` (`email`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 用户音色表
CREATE TABLE `voices` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL COMMENT '用户自定义音色名称',
  `voice_id` VARCHAR(255) NOT NULL COMMENT '阿里云返回的音色ID',
  `model` VARCHAR(100) NOT NULL COMMENT '绑定的语音合成模型',
  `category` VARCHAR(20) NOT NULL DEFAULT 'clone' COMMENT 'system 系统音色 / clone 克隆音色',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_user_voice` (`user_id`, `voice_id`),
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 语音生成历史表
CREATE TABLE `audio_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `text` TEXT NOT NULL COMMENT '合成文本内容',
  `voice_name` VARCHAR(100) NOT NULL COMMENT '使用的音色名称',
  `voice_id` VARCHAR(255) NOT NULL COMMENT '使用的音色ID',
  `model` VARCHAR(100) NOT NULL COMMENT '使用的模型',
  `audio_path` VARCHAR(500) NOT NULL COMMENT '音频文件本地路径',
  `text_length` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文本字符数',
  `points_cost` DECIMAL(10,1) NOT NULL DEFAULT 0 COMMENT '消耗积分',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
