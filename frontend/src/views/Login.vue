<template>
  <div class="page-container" style="max-width:440px;padding-top:60px">
    <div class="card">
      <h2 style="text-align:center;margin-bottom:28px;font-size:22px">登录</h2>

      <div class="form-group">
        <label class="form-label">邮箱</label>
        <input v-model="form.email" class="input" type="email" placeholder="请输入邮箱" />
      </div>

      <div class="tabs">
        <span :class="['tab-item', { active: loginType === 'password' }]" @click="loginType = 'password'">密码登录</span>
        <span :class="['tab-item', { active: loginType === 'code' }]" @click="loginType = 'code'">验证码登录</span>
      </div>

      <div v-if="loginType === 'password'" class="form-group">
        <label class="form-label">密码</label>
        <input v-model="form.password" class="input" type="password" placeholder="请输入密码" />
      </div>

      <div v-else class="form-group">
        <label class="form-label">验证码</label>
        <div class="flex gap-sm">
          <input v-model="form.code" class="input" placeholder="6位验证码" style="flex:1" />
          <button class="btn btn-outline btn-sm" :disabled="codeCountdown > 0" @click="sendCode" style="white-space:nowrap">
            {{ codeCountdown > 0 ? codeCountdown + 's' : '获取验证码' }}
          </button>
        </div>
      </div>

      <button class="btn btn-primary btn-lg" style="width:100%;margin-top:8px" :disabled="loading" @click="handleLogin">
        <span v-if="loading" class="loading-spinner" style="width:16px;height:16px"></span>
        {{ loading ? '登录中...' : '登 录' }}
      </button>

      <p v-if="errorMsg" style="color:var(--danger);font-size:13px;margin-top:12px;text-align:center">{{ errorMsg }}</p>

      <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-secondary)">
        还没有账号？<router-link to="/register">立即注册</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../api'

const router = useRouter()
const auth = useAuthStore()
const loginType = ref('password')
const loading = ref(false)
const errorMsg = ref('')
const codeCountdown = ref(0)
const form = reactive({ email: '', password: '', code: '' })

async function sendCode() {
  if (!form.email) { errorMsg.value = '请先输入邮箱'; return }
  try {
    await api.post('/auth/send_code.php', { email: form.email, type: 'login' })
    codeCountdown.value = 60
    const timer = setInterval(() => {
      codeCountdown.value--
      if (codeCountdown.value <= 0) clearInterval(timer)
    }, 1000)
    errorMsg.value = ''
  } catch (e) {
    errorMsg.value = e.message
  }
}

async function handleLogin() {
  errorMsg.value = ''
  if (!form.email) { errorMsg.value = '请输入邮箱'; return }
  loading.value = true
  try {
    const payload = { email: form.email }
    if (loginType.value === 'password') {
      payload.password = form.password
    } else {
      payload.code = form.code
    }
    const res = await api.post('/auth/login.php', payload)
    auth.setAuth(res.data.token, { email: res.data.email, points: res.data.points })
    router.push('/')
  } catch (e) {
    errorMsg.value = e.message
  } finally {
    loading.value = false
  }
}
</script>
