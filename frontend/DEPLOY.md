# 前端部署指南

## 环境要求

- Node.js 18+（推荐 20 LTS）
- npm 9+ 或 pnpm

## 一、本地开发

```bash
# 安装依赖
cd frontend
npm install

# 启动开发服务器
npm run dev
```

开发服务器默认运行在 `http://localhost:5173`。

Vite 已配置代理，将 `/api` 和 `/uploads` 请求转发到后端 `http://localhost:8080`，开发时无需额外配置跨域。

## 二、构建生产版本

```bash
npm run build
```

构建产物在 `dist/` 目录，包含：

```
dist/
├── index.html
└── assets/
    ├── index-xxx.css      # 样式文件
    ├── index-xxx.js       # 主入口 + Vue 运行时
    ├── Home-xxx.js        # 语音合成页
    ├── Login-xxx.js       # 登录页
    ├── Register-xxx.js    # 注册页
    ├── VoiceManage-xxx.js # 音色管理页
    ├── History-xxx.js     # 历史记录页
    └── tts-xxx.js         # TTS Store
```

## 三、部署到 Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;

    # 前端静态文件
    root /path/to/frontend/dist;
    index index.html;

    # SPA 路由处理 —— 所有非文件请求回退到 index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # 静态资源缓存
    location /assets/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # API 反向代理到 PHP 后端
    location /api/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # 超时设置（语音合成可能耗时较长）
        proxy_read_timeout 120s;
        proxy_connect_timeout 10s;
    }

    # 上传文件反向代理
    location /uploads/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
    }
}
```

### 前后端同域部署（推荐）

将前端 `dist/` 内容复制到后端目录，由后端服务器统一托管：

```bash
# 构建前端
cd frontend
npm run build

# 复制到后端
cp -r dist/* ../backend/
```

然后修改 Nginx 配置：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/backend;

    index index.html;

    # 前端 SPA 路由
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API 请求
    location /api/ {
        # PHP-FPM 处理
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        fastcgi_read_timeout 120s;
    }

    # 上传文件目录
    location /uploads/ {
        alias /path/to/backend/uploads/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # 禁止访问敏感目录
    location ~ /(config|lib|sql|\.) {
        deny all;
        return 403;
    }
}
```

### 同域部署时的 Vite 配置

同域部署时前端不需要代理，修改 [vite.config.js](file:///Users/wangzhijie/Desktop/工具/wangdalongTTS/frontend/vite.config.js)：

```js
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
    // 同域部署时删除 proxy 配置
  },
})
```

同时确认 [src/api/index.js](file:///Users/wangzhijie/Desktop/工具/wangdalongTTS/frontend/src/api/index.js) 中 `baseURL` 为 `/api`（已默认配置）。

## 四、部署到 Apache

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/frontend/dist

    <Directory /path/to/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # SPA 路由回退
        FallbackResource /index.html
    </Directory>

    # API 反向代理
    ProxyPass /api/ http://127.0.0.1:8080/api/
    ProxyPassReverse /api/ http://127.0.0.1:8080/api/
    ProxyPass /uploads/ http://127.0.0.1:8080/uploads/
    ProxyPassReverse /uploads/ http://127.0.0.1:8080/uploads/

    # 静态资源缓存
    <Location /assets/>
        Header set Cache-Control "public, max-age=2592000, immutable"
    </Location>
</VirtualHost>
```

需开启 Apache 模块：

```bash
sudo a2enmod rewrite proxy proxy_http headers
sudo systemctl restart apache2
```

## 五、环境变量配置（可选）

构建时可注入环境变量：

```bash
# 设置 API 基础路径
VITE_API_BASE_URL=/api npm run build
```

然后在 [src/api/index.js](file:///Users/wangzhijie/Desktop/工具/wangdalongTTS/frontend/src/api/index.js) 中使用 `import.meta.env.VITE_API_BASE_URL`。

## 六、HTTPS 配置

使用 Let's Encrypt 免费证书：

```bash
# 安装 certbot
sudo apt install certbot python3-certbot-nginx -y

# 自动配置 Nginx + SSL
sudo certbot --nginx -d your-domain.com

# 自动续期
sudo certbot renew --dry-run
```

## 七、性能优化

```nginx
# 开启 gzip 压缩
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript;
gzip_min_length 1000;
gzip_vary on;

# Brotli 压缩（更好的压缩率，需安装 ngx_brotli 模块）
brotli on;
brotli_types text/plain text/css application/json application/javascript;
```

## 八、验证部署

```bash
# 检查构建产物
ls -la dist/

# 本地预览生产构建
npx vite preview

# 访问 http://localhost:4173 确认功能正常
```

### 常见问题

**Q：刷新页面出现 404**

确认 Nginx 已配置 `try_files $uri $uri/ /index.html;` 或 Apache 已配置 `FallbackResource /index.html`。

**Q：API 请求报 401**

检查是否已注册并登录，token 存储在 localStorage 中。

**Q：语音合成超时**

合成大段文本时后端处理时间可能较长（30~120 秒），确认 Nginx `proxy_read_timeout` 和 PHP `max_execution_time` 设置足够大。
