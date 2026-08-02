<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-card">
        <!-- 关闭按钮 -->
        <button class="modal-close" @click="$emit('close')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <!-- 登录模式 -->
        <template v-if="mode === 'login'">
          <h2 class="modal-title">登录</h2>

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
              <button class="btn btn-outline btn-sm" :disabled="codeCountdown > 0" @click="sendCode('login')" style="white-space:nowrap">
                {{ codeCountdown > 0 ? codeCountdown + 's' : '获取验证码' }}
              </button>
            </div>
          </div>

          <button class="btn btn-primary btn-lg" style="width:100%;margin-top:8px" :disabled="loading" @click="handleLogin">
            <span v-if="loading" class="loading-spinner" style="width:16px;height:16px"></span>
            {{ loading ? '登录中...' : '登 录' }}
          </button>

          <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-secondary)">
            还没有账号？<a href="#" @click.prevent="switchMode('register')">立即注册</a>
          </p>
        </template>

        <!-- 注册模式 -->
        <template v-else>
          <h2 class="modal-title">注册</h2>

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
              <button class="btn btn-outline btn-sm" :disabled="codeCountdown > 0" @click="sendCode('register')" style="white-space:nowrap">
                {{ codeCountdown > 0 ? codeCountdown + 's' : '获取验证码' }}
              </button>
            </div>
          </div>

          <button class="btn btn-primary btn-lg" style="width:100%;margin-top:8px" :disabled="loading" @click="handleRegister">
            <span v-if="loading" class="loading-spinner" style="width:16px;height:16px"></span>
            {{ loading ? '注册中...' : '注 册' }}
          </button>

          <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-secondary)">
            已有账号？<a href="#" @click.prevent="switchMode('login')">去登录</a>
          </p>
        </template>

        <!-- 错误提示 -->
        <p v-if="errorMsg" style="color:var(--danger);font-size:13px;margin-top:12px;text-align:center">{{ errorMsg }}</p>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../api'

const props = defineProps({
  visible: { type: Boolean, default: false },
})
const emit = defineEmits(['close', 'success'])

const auth = useAuthStore()

const mode = ref('login')
const loginType = ref('password')
const loading = ref(false)
const errorMsg = ref('')
const codeCountdown = ref(0)
const form = reactive({ email: '', password: '', code: '' })

// 弹窗关闭时重置状态
watch(() => props.visible, (val) => {
  if (!val) {
    mode.value = 'login'
    loginType.value = 'password'
    errorMsg.value = ''
    loading.value = false
    form.email = ''
    form.password = ''
    form.code = ''
  }
})

function switchMode(m) {
  mode.value = m
  errorMsg.value = ''
  form.password = ''
  form.code = ''
}

async function sendCode(type) {
  if (!form.email) { errorMsg.value = '请先输入邮箱'; return }
  try {
    await api.post('/auth/send_code.php', { email: form.email, type })
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
    emit('success')
    emit('close')
  } catch (e) {
    errorMsg.value = e.message
  } finally {
    loading.value = false
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
    emit('success')
    emit('close')
  } catch (e) {
    errorMsg.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  animation: fadeIn 0.2s ease;
}

.modal-card {
  position: relative;
  background: var(--card-bg, #fff);
  border-radius: 14px;
  padding: 32px 28px 24px;
  width: 420px;
  max-width: 90vw;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  animation: slideUp 0.25s ease;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-close {
  position: absolute;
  top: 12px;
  right: 12px;
  background: none;
  border: none;
  color: var(--text-secondary, #6b7280);
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.modal-close:hover {
  background: var(--bg, #f5f7fa);
  color: var(--text, #1f2937);
}

.modal-title {
  font-size: 22px;
  font-weight: 600;
  text-align: center;
  margin-bottom: 24px;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px) scale(0.96); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
