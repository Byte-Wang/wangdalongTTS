# 后端部署指南

## 环境要求

- PHP 7.4 或更高版本（推荐 PHP 8.0+）
- MySQL 5.7 或更高版本（推荐 MySQL 8.0）
- PHP 扩展：`pdo_mysql`、`curl`、`json`、`mbstring`、`fileinfo`
- 可访问阿里云百炼 API 的网络环境

## 一、检查 PHP 环境

```bash
# 确认 PHP 版本
php -v

# 确认必要扩展已安装
php -m | grep -E 'pdo_mysql|curl|json|mbstring|fileinfo'
```

## 二、数据库初始化

```bash
# 方式一：直接导入 SQL 文件
mysql -u root -p < sql/schema.sql

# 方式二：登录后导入
mysql -u root -p
# 进入 MySQL 后执行：
source /path/to/backend/sql/schema.sql;
```

执行后会自动创建 `tts_platform` 数据库和以下四张表：

| 表名 | 说明 |
|------|------|
| `users` | 用户信息（邮箱、密码、积分等） |
| `email_verifications` | 邮箱验证码记录 |
| `voices` | 用户自定义音色 |
| `audio_history` | 语音生成历史 |

## 三、配置环境变量

### 必填项

```bash
# 阿里云百炼 API Key（在北京地域控制台获取）
export DASHSCOPE_API_KEY="sk-xxxxxxxxxxxxxxxxxxxxxxxx"

# 阿里云百炼业务空间 ID
export ALIYUN_WORKSPACE_ID="ws_xxxxxxxx"

# JWT Token 签名密钥（请使用随机字符串替换）
export AUTH_SECRET="your_random_secret_key_here"
```

### 可选项

```bash
# 数据库配置（默认值如下，可根据需要修改）
export DB_HOST="127.0.0.1"
export DB_PORT="3306"
export DB_NAME="tts_platform"
export DB_USER="root"
export DB_PASS="your_password"

# 应用访问 URL（用于声音复刻时生成音频的可访问地址）
export APP_URL="https://your-domain.com"
```

### 生产环境配置建议

将环境变量写入系统服务文件或 `.env` 文件。以 `systemd` 为例：

```
# /etc/systemd/system/tts-api.service
[Service]
Environment="DASHSCOPE_API_KEY=sk-xxx"
Environment="ALIYUN_WORKSPACE_ID=ws_xxx"
Environment="AUTH_SECRET=random_string"
Environment="DB_PASS=your_password"
Environment="APP_URL=https://your-domain.com"
```

## 四、配置上传目录权限

```bash
# 确保 uploads 目录可写
chmod -R 755 uploads/
chown -R www-data:www-data uploads/   # Nginx/Apache 用户
```

## 五、启动服务

### 开发环境（PHP 内置服务器）

```bash
cd backend
php -S 0.0.0.0:8080
```

### 生产环境（Nginx）

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/backend;

    # 静态文件（上传的音频）
    location /uploads/ {
        alias /path/to/backend/uploads/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # API 请求转发
    location /api/ {
        # 允许跨域
        add_header Access-Control-Allow-Origin *;
        add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
        add_header Access-Control-Allow-Headers "Content-Type, Authorization";

        if ($request_method = OPTIONS) {
            return 200;
        }

        # PHP 处理
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # 禁止直接访问配置文件
    location ~ /(config|lib|sql)/ {
        deny all;
        return 403;
    }
}
```

### 生产环境（Apache）

项目已包含 `.htaccess` 文件，直接部署到 Apache 目录即可。确保已开启 `mod_rewrite`：

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 六、配置邮件发送

项目默认使用 PHP `mail()` 函数发送验证码邮件。生产环境建议配置 SMTP。

### 使用 SMTP（推荐）

修改 `api/auth/send_code.php`，替换 `mail()` 调用为 SMTP 发送。可使用 PHPMailer 库：

```bash
composer require phpmailer/phpmailer
```

然后修改发送逻辑使用 SMTP 发送。

### Linux 服务器 mail 配置

```bash
# 安装 sendmail / postfix
sudo apt install postfix -y
sudo systemctl enable postfix
```

## 七、安全建议

1. **API Key 安全**：不要将 API Key 硬编码在代码中，始终使用环境变量
2. **HTTPS**：生产环境必须配置 SSL 证书，使用 HTTPS
3. **文件上传限制**：已在代码中限制音频文件 ≤ 10MB，格式仅允许 WAV/MP3/M4A
4. **SQL 注入防护**：所有数据库操作使用 PDO 预处理语句
5. **密码存储**：使用 `password_hash()` + BCRYPT 加密
6. **Token 安全**：JWT 签名密钥必须使用足够长的随机字符串
7. **上传目录**：禁止在 uploads 目录下执行 PHP 脚本

```nginx
# Nginx 禁止 uploads 目录执行 PHP
location /uploads/ {
    location ~ \.php$ { deny all; }
}
```

## 八、验证部署

```bash
# 测试 API 是否正常
curl http://localhost:8080/api/voice/system_voices.php

# 应返回系统音色列表的 JSON 数据
```

### 常见问题

**Q：提示 `DASHSCOPE_API_KEY 未配置`**

确认环境变量已设置：`echo $DASHSCOPE_API_KEY`

**Q：数据库连接失败**

检查 `config/database.php` 中的配置是否与环境变量或实际数据库一致。

**Q：声音复刻创建音色失败**

1. 确认音频文件格式符合阿里云要求（WAV 16bit / MP3 / M4A，10~20 秒）
2. 确认 `APP_URL` 配置正确，阿里云需要能访问到上传的音频 URL
3. 检查 `ALIYUN_WORKSPACE_ID` 是否正确
