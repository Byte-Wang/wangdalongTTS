import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Welcome',
    component: () => import('../views/Welcome.vue'),
    meta: {
      title: 'DollyTTS语音合成 - AI语音合成 | AI声音克隆',
      description: 'DollyTTS语音合成平台，基于阿里云AI技术，提供在线语音合成、AI声音克隆、声音复刻服务。支持多种音色，文字转语音一键生成。',
    },
  },
  {
    path: '/tts',
    name: 'Home',
    component: () => import('../views/Home.vue'),
    meta: {
      title: '语音合成 - DollyTTS AI文字转语音在线生成',
      description: 'DollyTTS在线语音合成，输入文字一键生成自然流畅的语音。支持多种AI音色和TTS模型，提供CosyVoice、Qwen Audio TTS等主流模型。',
    },
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: {
      title: '登录 - DollyTTS语音合成',
      description: '登录DollyTTS语音合成平台，使用AI语音合成和声音克隆服务。',
      guest: true,
    },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/Register.vue'),
    meta: {
      title: '注册 - DollyTTS语音合成',
      description: '注册DollyTTS语音合成账号，即送500积分，免费体验AI语音合成和声音克隆服务。',
      guest: true,
    },
  },
  {
    path: '/voices',
    name: 'VoiceManage',
    component: () => import('../views/VoiceManage.vue'),
    meta: {
      title: 'AI声音克隆 - 在线复刻声音 | 克隆声音 - DollyTTS',
      description: 'DollyTTS声音复刻平台，上传或录制一小段音频即可克隆专属音色。支持CosyVoice、Qwen等多模型，效果真实自然。',
    },
  },
  {
    path: '/history',
    name: 'History',
    component: () => import('../views/History.vue'),
    meta: {
      title: '生成历史 - DollyTTS语音合成',
      description: '查看和管理你的AI语音合成历史记录，播放和下载已生成的语音文件。',
      requiresAuth: true,
    },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title

  // 动态更新 meta description
  const descTag = document.querySelector('meta[name="description"]')
  if (descTag && to.meta.description) {
    descTag.setAttribute('content', to.meta.description)
  }

  // 动态更新 canonical
  const canonicalTag = document.querySelector('link[rel="canonical"]')
  if (canonicalTag) {
    canonicalTag.setAttribute('href', 'https://dollytts.com' + to.path)
  }

  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    next('/')
  } else if (to.meta.guest && token) {
    next('/')
  } else {
    next()
  }
})

export default router
