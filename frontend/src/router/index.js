import { createRouter, createWebHashHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Welcome',
    component: () => import('../views/Welcome.vue'),
    meta: { title: 'DollyTTS语音合成 - AI语音合成 | AI声音克隆' },
  },
  {
    path: '/tts',
    name: 'Home',
    component: () => import('../views/Home.vue'),
    meta: { title: '语音合成 - AI文字转语音在线生成' },
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { title: '登录', guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/Register.vue'),
    meta: { title: '注册', guest: true },
  },
  {
    path: '/voices',
    name: 'VoiceManage',
    component: () => import('../views/VoiceManage.vue'),
    meta: { title: 'AI声音克隆 - 在线复刻声音 | 克隆声音' },
  },
  {
    path: '/history',
    name: 'History',
    component: () => import('../views/History.vue'),
    meta: { title: '生成历史', requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title
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
