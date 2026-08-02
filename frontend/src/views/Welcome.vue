<template>
  <div class="welcome">
    <!-- Hero 区域 -->
    <section class="hero">
      <div class="hero-content">
        <div class="hero-icon">
          <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 18v-6a9 9 0 0118 0v6"/>
            <path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>
          </svg>
        </div>
        <h1 class="hero-title">DollyTTS语音合成</h1>
        <p class="hero-subtitle">
          基于阿里云强大的 AI 语音技术，将文字转化为自然流畅的语音，<br/>
          支持多种音色选择和声音复刻功能。
        </p>
        <router-link to="/tts" class="hero-btn">
          开始语音合成
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </router-link>
      </div>
      <div class="hero-visual">
        <!-- 装饰性声波动画 -->
        <div class="audio-wave">
          <span v-for="i in 12" :key="i" class="wave-bar" :style="{ animationDelay: (i * 0.08) + 's', height: (20 + Math.sin(i * 1.2) * 20 + Math.random() * 30) + 'px' }"></span>
        </div>
        <div class="audio-wave wave-2">
          <span v-for="i in 12" :key="i" class="wave-bar" :style="{ animationDelay: (i * 0.1 + 0.3) + 's', height: (15 + Math.cos(i * 0.9) * 18 + Math.random() * 25) + 'px' }"></span>
        </div>
      </div>
    </section>

    <!-- 功能卡片 -->
    <section class="features">
      <h2 class="section-title">核心功能</h2>
      <div class="feature-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/>
              <path d="M19 10v2a7 7 0 01-14 0v-2"/>
              <line x1="12" y1="19" x2="12" y2="23"/>
              <line x1="8" y1="23" x2="16" y2="23"/>
            </svg>
          </div>
          <h3>多音色语音合成</h3>
          <p>支持多种预设音色，涵盖男女声、不同年龄段和语言，输入文字即可生成自然语音。</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </div>
          <h3>声音复刻</h3>
          <p>上传或录制一小段音频，即可克隆专属音色，让 AI 用你的声音说话。</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <line x1="3" y1="9" x2="21" y2="9"/>
              <line x1="9" y1="21" x2="9" y2="9"/>
            </svg>
          </div>
          <h3>生成历史管理</h3>
          <p>所有合成记录自动保存，方便随时回听和下载，支持历史记录浏览。</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
          </div>
          <h3>多模型支持</h3>
          <p>集成 CosyVoice、Qwen Audio TTS 等多个主流 TTS 模型，满足不同场景需求。</p>
        </div>
      </div>
    </section>

    <!-- 底部 CTA -->
    <section class="cta">
      <h2>准备好了吗？</h2>
      <p>免费体验 AI 语音合成的魅力，即刻开始创作</p>
      <router-link to="/tts" class="hero-btn">开始使用</router-link>
    </section>
  </div>
</template>

<script setup>
// Welcome 页面，纯展示，无需逻辑
</script>

<style scoped>
.welcome {
  min-height: calc(100vh - 56px);
}

/* Hero */
.hero {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 60px;
  padding: 80px 40px 60px;
  max-width: 1000px;
  margin: 0 auto;
  min-height: 450px;
}

.hero-content {
  flex: 0 0 auto;
  text-align: center;
}

.hero-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 88px;
  height: 88px;
  border-radius: 22px;
  background: linear-gradient(135deg, #1a73e8, #4f9cf8);
  color: #fff;
  margin-bottom: 24px;
  box-shadow: 0 8px 30px rgba(26, 115, 232, 0.3);
}

.hero-title {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 16px;
  background: linear-gradient(135deg, var(--text, #1f2937) 30%, var(--primary, #1a73e8));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: 16px;
  color: var(--text-secondary);
  line-height: 1.8;
  margin-bottom: 32px;
}

.hero-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 15px 36px;
  background: linear-gradient(135deg, #1a73e8, #4f9cf8);
  color: #fff;
  border-radius: 40px;
  font-size: 17px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
  box-shadow: 0 4px 20px rgba(26, 115, 232, 0.35);
}
.hero-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(26, 115, 232, 0.45);
  text-decoration: none;
}

/* 声波动画 */
.hero-visual {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  opacity: 0.6;
}

.audio-wave {
  display: flex;
  align-items: center;
  gap: 4px;
}

.wave-bar {
  width: 3px;
  background: linear-gradient(to top, #1a73e8, #4f9cf8);
  border-radius: 2px;
  animation: wave 1.2s ease-in-out infinite alternate;
}

.wave-2 .wave-bar {
  animation-direction: alternate-reverse;
  opacity: 0.5;
}

@keyframes wave {
  from { transform: scaleY(0.3); }
  to { transform: scaleY(1); }
}

/* Features */
.features {
  padding: 60px 40px;
  max-width: 1000px;
  margin: 0 auto;
}

.section-title {
  text-align: center;
  font-size: 26px;
  font-weight: 600;
  margin-bottom: 40px;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 24px;
}

.feature-card {
  background: var(--card-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 28px 24px;
  text-align: center;
  transition: all 0.3s;
}
.feature-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  border-color: var(--primary);
}

.feature-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: var(--primary-light);
  color: var(--primary);
  margin-bottom: 16px;
}

.feature-card h3 {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 10px;
}

.feature-card p {
  font-size: 13px;
  color: var(--text-secondary);
  line-height: 1.7;
}

/* CTA */
.cta {
  text-align: center;
  padding: 60px 40px 80px;
  background: linear-gradient(180deg, transparent, var(--primary-bg) 60%);
}

.cta h2 {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 10px;
}

.cta p {
  font-size: 15px;
  color: var(--text-secondary);
  margin-bottom: 28px;
}

/* 响应式 */
@media (max-width: 768px) {
  .hero {
    flex-direction: column;
    padding: 40px 20px;
    gap: 30px;
    text-align: center;
  }
  .hero-title {
    font-size: 28px;
  }
  .hero-visual {
    display: none;
  }
  .features {
    padding: 40px 20px;
  }
  .feature-grid {
    grid-template-columns: 1fr;
  }
}
</style>
