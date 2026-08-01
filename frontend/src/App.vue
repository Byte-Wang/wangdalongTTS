<template>
  <div id="app">
    <nav class="navbar">
      <router-link to="/" class="navbar-brand">
        <img src="/logo.png" alt="logo" width="24" height="24" />
        王大龙语音合成工具
      </router-link>

      <ul v-if="auth.isLoggedIn" class="navbar-nav">
        <li><router-link to="/">语音合成</router-link></li>
        <li><router-link to="/voices">音色管理</router-link></li>
        <li><router-link to="/history">生成历史</router-link></li>
      </ul>

      <div class="navbar-actions">
        <template v-if="auth.isLoggedIn">
          <span class="points-badge" @click.stop="togglePointsPopover">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="12" r="10"/>
              <text x="12" y="16" text-anchor="middle" font-size="10" fill="white" font-weight="bold">积</text>
            </svg>
            {{ auth.points }} 积分
            <!-- 积分气泡卡片 -->
            <div v-if="showPointsPopover" class="points-popover" @click.stop>
              <div class="popover-arrow"></div>
              <p class="popover-text">项目内测中，暂不提供充值功能</p>
              <p class="popover-text">如果免费积分体验后觉得还不错，可联系作者，扫码添加微信：</p>
              <img src="/wechat.png" alt="微信二维码" class="popover-qrcode" />
            </div>
          </span>
          <span style="font-size:13px;color:var(--text-secondary)">{{ auth.user?.email }}</span>
          <button class="btn btn-outline btn-sm" @click="logout">退出</button>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-outline btn-sm">登录</router-link>
          <router-link to="/register" class="btn btn-primary btn-sm">注册</router-link>
        </template>
      </div>
    </nav>

    <main>
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const showPointsPopover = ref(false)

function togglePointsPopover() {
  showPointsPopover.value = !showPointsPopover.value
}

function closePointsPopover(e) {
  if (showPointsPopover.value) {
    showPointsPopover.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closePointsPopover)
})

onUnmounted(() => {
  document.removeEventListener('click', closePointsPopover)
})

function logout() {
  auth.clearAuth()
  router.push('/login')
}
</script>

<style scoped>
.points-badge {
  position: relative;
  cursor: pointer;
  display: inline-flex;
}

.points-popover {
  position: absolute;
  top: calc(100% + 10px);
  right: -10px;
  background: #fff;
  border: 1px solid var(--border-color, #e0e0e0);
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
  z-index: 1000;
  min-width: 220px;
  text-align: center;
}

.popover-arrow {
  position: absolute;
  top: -6px;
  right: 24px;
  width: 12px;
  height: 12px;
  background: #fff;
  border-left: 1px solid var(--border-color, #e0e0e0);
  border-top: 1px solid var(--border-color, #e0e0e0);
  transform: rotate(45deg);
}

.popover-text {
  margin: 0 0 10px 0;
  font-size: 13px;
  color: var(--text-secondary, #666);
  line-height: 1.5;
}

.popover-text:last-of-type {
  margin-bottom: 12px;
}

.popover-qrcode {
  width: 160px;
  height: 160px;
  border-radius: 8px;
  border: 1px solid var(--border-color, #eee);
}
</style>
