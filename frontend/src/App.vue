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
          <span class="points-badge">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="12" r="10"/>
              <text x="12" y="16" text-anchor="middle" font-size="10" fill="white" font-weight="bold">积</text>
            </svg>
            {{ auth.points }} 积分
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
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

function logout() {
  auth.clearAuth()
  router.push('/login')
}
</script>
