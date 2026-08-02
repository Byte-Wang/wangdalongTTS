<template>
  <div class="welcome">
    <section class="hero">
      <div class="hero-shell">
        <div class="hero-copy">
          <div class="hero-brand">
            <img src="/logo.png" alt="DollyTTS AI语音合成" class="hero-brand-icon" loading="eager" decoding="async" />
            <span>DollyTTS 语音合成</span>
          </div>

          <h1 class="hero-title">
            <span class="hero-title-accent">DollyTTS</span>
            <span>语音合成</span>
          </h1>

          <p class="hero-subtitle">
            基于阿里云强大的 AI 语音技术，将文字转化为自然流畅的语音，
            支持多音色选择、声音复刻和多模型生成。
          </p>

          <div class="hero-actions">
            <router-link to="/tts" class="hero-btn hero-btn-primary">
              开始
            </router-link>
            <router-link to="/voices" class="hero-btn hero-btn-secondary">
              声音复刻
            </router-link>
          </div>

          <div class="bonus-card">
            <div class="bonus-copy">
              <strong>注册即送 500 积分</strong>
              <span>可免费生成 500 字语音</span>
            </div>
            <img src="/礼物盒.png" alt="注册福利" class="bonus-gift" />
          </div>
        </div>
      </div>
    </section>

    <section class="features">
      <div
        v-for="feature in features"
        :key="feature.title"
        class="feature-card"
      >
        <img :src="feature.image" :alt="feature.title" class="feature-icon" loading="lazy" decoding="async" />
        <div class="feature-text">
          <h3>{{ feature.title }}</h3>
          <p>{{ feature.description }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
const features = [
  {
    title: '多音色语音合成',
    description: '支持多种预设音色，温柔男女声、不同年龄段和语言，输入文字即可生成自然语音。',
    image: '/语音合成.png',
  },
  {
    title: '声音复刻',
    description: '上传或录制一小段音频，即可克隆专属音色，让 AI 用你的声音说话。',
    image: '/声音复刻.png',
  },
  {
    title: '多模型支持',
    description: '集成 CosyVoice、Qwen Audio TTS 等多个主流 TTS 模型，满足不同场景需求。',
    image: '/多模型.png',
  },
]
</script>

<style scoped>
.welcome {
  min-height: calc(100vh - 56px);
  padding: 32px 24px 56px;
  background:
    linear-gradient(180deg, rgba(247, 250, 255, 0.92) 0%, rgba(241, 245, 255, 0.96) 100%),
    #f4f7fd;
}

.hero {
  max-width: 1360px;
  margin: 0 auto;
}

.hero-shell {
  position: relative;
  display: flex;
  align-items: center;
  min-height: 620px;
  padding: 72px 56px 120px;
  border-radius: 32px;
  overflow: hidden;
  background:
    linear-gradient(90deg, rgba(243, 247, 255, 0.96) 0%, rgba(243, 247, 255, 0.84) 30%, rgba(243, 247, 255, 0.45) 48%, rgba(243, 247, 255, 0.12) 72%, rgba(243, 247, 255, 0.04) 100%),
    url('/首页背景.png') center/cover no-repeat;
  box-shadow: 0 24px 60px rgba(58, 95, 168, 0.12);
}

.hero-shell::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 76%, rgba(157, 200, 255, 0.2), transparent 32%),
    radial-gradient(circle at 85% 18%, rgba(116, 164, 255, 0.22), transparent 24%);
  pointer-events: none;
}

.hero-copy,
.features {
  position: relative;
  z-index: 1;
}

.hero-copy {
  max-width: 520px;
}

.hero-brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 26px;
  color: #2f4c84;
  font-size: 18px;
  font-weight: 600;
}

.hero-brand-icon {
  width: 26px;
  height: 26px;
  object-fit: contain;
}

.hero-title {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 10px;
  margin-bottom: 22px;
  color: #1d2d55;
  font-size: clamp(40px, 5vw, 62px);
  line-height: 1.08;
  font-weight: 800;
}

.hero-title-accent {
  color: #2b6ef5;
}

.hero-subtitle {
  max-width: 520px;
  margin-bottom: 34px;
  color: #4d6289;
  font-size: 18px;
  line-height: 1.9;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  margin-bottom: 28px;
}

.hero-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 148px;
  padding: 15px 34px;
  border-radius: 999px;
  font-size: 17px;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
}

.hero-btn:hover {
  transform: translateY(-2px);
  text-decoration: none;
}

.hero-btn-primary {
  color: #fff;
  background: linear-gradient(135deg, #2d72ff, #629cff);
  box-shadow: 0 14px 30px rgba(45, 114, 255, 0.28);
}

.hero-btn-primary:hover {
  box-shadow: 0 18px 36px rgba(45, 114, 255, 0.34);
}

.hero-btn-secondary {
  color: #2f63d8;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(126, 166, 255, 0.42);
  box-shadow: 0 10px 24px rgba(86, 118, 179, 0.12);
}

.bonus-card {
  display: inline-flex;
  align-items: center;
  gap: 18px;
  padding: 18px 22px;
  border-radius: 24px;
  background: linear-gradient(135deg, rgba(61, 130, 255, 0.96), rgba(111, 98, 255, 0.92));
  box-shadow: 0 18px 36px rgba(82, 112, 219, 0.24);
}

.bonus-copy {
  display: flex;
  flex-direction: column;
  gap: 4px;
  color: #fff;
}

.bonus-copy strong {
  font-size: 18px;
  font-weight: 800;
}

.bonus-copy span {
  font-size: 14px;
  opacity: 0.92;
}

.bonus-gift {
  width: 78px;
  height: 78px;
  object-fit: contain;
}

.features {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
  max-width: 1220px;
  margin: -68px auto 0;
}

.feature-card {
  display: flex;
  align-items: center;
  gap: 18px;
  min-height: 180px;
  padding: 28px 24px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.92);
  border: 1px solid rgba(224, 233, 248, 0.9);
  box-shadow: 0 16px 36px rgba(87, 116, 171, 0.1);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.feature-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 24px 44px rgba(87, 116, 171, 0.14);
}

.feature-icon {
  width: 72px;
  height: 72px;
  flex-shrink: 0;
  object-fit: contain;
}

.feature-text h3 {
  margin-bottom: 12px;
  color: #253b65;
  font-size: 24px;
  font-weight: 700;
}

.feature-text p {
  color: #5e6f8d;
  font-size: 15px;
  line-height: 1.9;
}

@media (max-width: 1100px) {
  .welcome {
    padding: 20px 16px 40px;
  }

  .hero-shell {
    min-height: auto;
    padding: 44px 24px 120px;
  }

  .hero-copy {
    max-width: none;
  }

  .features {
    grid-template-columns: 1fr;
    margin-top: -84px;
  }
}

@media (max-width: 768px) {
  .hero-shell {
    padding: 32px 18px 112px;
    border-radius: 24px;
    background:
      linear-gradient(180deg, rgba(243, 247, 255, 0.96) 0%, rgba(243, 247, 255, 0.86) 40%, rgba(243, 247, 255, 0.7) 100%),
      url('/首页背景.png') 68% center/cover no-repeat;
  }

  .hero-brand {
    margin-bottom: 20px;
    font-size: 15px;
  }

  .hero-title {
    font-size: 34px;
    gap: 6px;
  }

  .hero-subtitle {
    font-size: 15px;
    line-height: 1.8;
  }

  .hero-actions {
    gap: 10px;
  }

  .hero-btn {
    min-width: 124px;
    padding: 13px 24px;
    font-size: 15px;
  }

  .bonus-card {
    width: 100%;
    justify-content: space-between;
    padding: 16px 18px;
  }

  .bonus-copy strong {
    font-size: 16px;
  }

  .bonus-copy span {
    font-size: 13px;
  }

  .bonus-gift {
    width: 60px;
    height: 60px;
  }

  .features {
    gap: 16px;
    margin-top: -92px;
  }

  .feature-card {
    min-height: auto;
    padding: 22px 18px;
  }

  .feature-text h3 {
    font-size: 18px;
  }

  .feature-text p {
    font-size: 14px;
    line-height: 1.7;
  }
}
</style>
