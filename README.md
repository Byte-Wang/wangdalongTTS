# 语音合成平台（TTS Platform）

基于阿里云百炼大模型的多音色语音合成与声音复刻平台，支持 Qwen-Audio-TTS、CosyVoice、Qwen-TTS 三大系列模型。

## 功能

- **语音合成**：文本转语音，支持 80+ 系统音色（含中文方言、多语种）
- **声音复刻**：上传 10~20 秒音频，克隆专属音色
- **账号系统**：邮箱注册 + 验证码激活
- **积分系统**：中文字符 1 积分/字，英文数字 0.5 积分/字，新用户 500 积分
- **历史管理**：预览、下载、删除历史语音

## 技术栈

| 层级 | 技术 |
|------|------|
| 前端 | Vue 3 + Vite + Pinia + Vue Router |
| 后端 | PHP 7.4+（纯原生，无框架） |
| 数据库 | MySQL 5.7+ |
| API | 阿里云百炼 DashScope |

## 项目结构

```
├── frontend/          # Vue 3 前端
│   └── src/
│       ├── api/       # Axios 封装
│       ├── stores/    # Pinia 状态管理
│       └── views/     # 页面组件
├── backend/           # PHP 后端
│   ├── api/           # 接口（auth/voice/tts/user）
│   ├── lib/           # 公共库（鉴权/日志/TTS客户端）
│   ├── config/        # 配置文件
│   ├── sql/           # 建表语句
│   ├── uploads/       # 音频文件存储
│   └── logs/          # 本地日志（按天切割）
└── README.md
```

## 快速部署

### 1. 环境要求

- PHP 7.4+（需启用 `curl`、`openssl`、`pdo_mysql`、`fileinfo` 扩展）
- MySQL 5.7+
- Node.js 18+

### 2. 数据库初始化

```sql
-- 执行建表语句
mysql -u root -p < backend/sql/schema.sql
```

### 3. 配置

编辑 `backend/config/aliyun.php`，填入阿里云百炼的 API Key 和 Workspace ID：

```php
'api_key'      => 'sk-你的APIKey',
'workspace_id' => '你的WorkspaceId',
```

编辑 `backend/config/database.php`，填入数据库连接信息。

### 4. 部署文件

将项目部署到服务器（以 `tts.wangdalong.com` 为例）：

```
站点根目录/
├── index.html          # Vue 编译产物
├── assets/
└── backend/            # PHP 后端
    ├── api/
    ├── lib/
    ├── config/
    ├── uploads/
    └── logs/
```

### 5. Nginx 配置示例

```nginx
server {
    listen 443 ssl;
    server_name tts.wangdalong.com;
    root /www/wwwroot/tts.wangdalong.com;

    # 前端 SPA
    location / {
        try_files $uri $uri/ /index.html;
    }

    # 后端 API（去掉 /backend 前缀转发给 PHP）
    location /backend/api/ {
        rewrite ^/backend/(.*)$ /$1 break;
        fastcgi_pass unix:/tmp/php-cgi-74.sock;
        fastcgi_param SCRIPT_FILENAME $document_root/backend/$fastcgi_script_name;
        include fastcgi_params;
    }

    # 上传文件
    location /backend/uploads/ {
        alias /www/wwwroot/tts.wangdalong.com/backend/uploads/;
    }
}
```

### 6. 设置目录权限

```bash
chmod -R 755 backend/uploads backend/logs
```

### 7. 构建前端

```bash
cd frontend && npm install && npx vite build
# 将 dist/ 下的文件放到站点根目录
```

### 8. 配置 SSL 证书（录音功能必需）

浏览器录音功能要求 HTTPS，建议通过宝塔面板申请 Let's Encrypt 免费证书。

## 查看日志

```bash
tail -f backend/logs/tts_$(date +%Y-%m-%d).log
```

## 许可证

MIT
