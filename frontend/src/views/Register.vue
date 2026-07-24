<template>
  <div class="page-container" style="max-width:440px;padding-top:60px">
    <div class="card">
      <h2 style="text-align:center;margin-bottom:28px;font-size:22px">注册</h2>

      <div class="form-group">
        <label class="form-label">邮箱</label>
        <input v-model="form.email" class="input" type="email" placeholder="请输入邮箱" />
      </div>

      <div class="form-group">
        <label class="form-label">密码</label>
        <input v-model="form.password" class="input" type="password" placeholder="至少6位密码" />
      </div>

      <div class="form-group">
        <label class="form-label">验证码</label>
        <div class="flex gap-sm">
          <input v-model="form.code" class="input" placeholder="6位验证码" style="flex:1" />
          <button class="btn btn-outline btn-sm" :disabled="codeCountdown > 0" @click="sendCode" style="white-space:nowrap">
            {{ codeCountdown > 0 ? codeCountdown + 's' : '获取验证码' }}
          </button>
        </div>
      </div>

      <button class="btn btn-primary btn-lg" style="width:100%;margin-top:8px" :disabled="loading" @click="handleRegister">
        <span v-if="loading" class="loading-spinner" style="width:16px;height:16px"></span>
        {{ loading ? '注册中...' : '注 册' }}
      </button>

      <p v-if="errorMsg" style="color:var(--danger);font-size:13px;margin-top:12px;text-align:center">{{ errorMsg }}</p>

      <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-secondary)">
        已有账号？<router-link to="/login">去登录</router-link>
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
const loading = ref(false)
const errorMsg = ref('')
const codeCountdown = ref(0)
const form = reactive({ email: '', password: '', code: '' })

async function sendCode() {
  if (!form.email) { errorMsg.value = '请先输入邮箱'; return }
  try {
    await api.post('/auth/send_code.php', { email: form.email, type: 'register' })
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

async function handleRegister() {
  errorMsg.value = ''
  if (!form.email) { errorMsg.value = '请输入邮箱'; return }
  if (form.password.length < 6) { errorMsg.value = '密码至少6位'; return }
  if (form.code.length !== 6) { errorMsg.value = '请填写6位验证码'; return }
  loading.value = true
  try {
    const res = await api.post('/auth/register.php', { ...form })
    auth.setAuth(res.data.token, { email: res.data.email, points: res.data.points })
    router.push('/')
  } catch (e) {
    errorMsg.value = e.message
  } finally {
    loading.value = false
  }
}
</script>
