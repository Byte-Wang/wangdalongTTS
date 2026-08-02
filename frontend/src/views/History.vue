<template>
<div class="page-root">
  <div class="page-bg"></div>
  <div class="page-container">
    <h1 class="page-title">生成历史</h1>
    <p class="page-subtitle">查看和管理你所有生成的音色</p>

    <div v-if="ttsStore.history.list.length === 0 && !loading" class="card empty-state">
      <p>暂无生成记录</p>
      <p style="font-size:12px;margin-top:4px">前往 <router-link to="/tts">语音合成</router-link> 开始使用</p>
    </div>

    <div v-else class="card">
      <table class="table">
        <thead>
          <tr>
            <th>合成文本</th>
            <th>音色</th>
            <th>模型</th>
            <th>消耗积分</th>
            <th>时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="item in ttsStore.history.list" :key="item.id">
            <tr>
              <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                {{ item.text.substring(0, 40) }}{{ item.text.length > 40 ? '...' : '' }}
              </td>
              <td>{{ item.voice_name }}</td>
              <td><span class="voice-tag">{{ item.model }}</span></td>
              <td>{{ item.points_cost }}</td>
              <td style="font-size:13px;color:var(--text-secondary)">{{ formatTime(item.created_at) }}</td>
              <td>
                <div class="flex gap-sm">
                  <button class="btn btn-outline btn-sm" @click="togglePreview(item)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                  </button>
                  <button class="btn btn-outline btn-sm" @click="downloadItem(item)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                  </button>
                  <button class="btn btn-danger btn-sm" @click="doDelete(item.id)">删除</button>
                </div>
              </td>
            </tr>
            <tr v-if="expandedId === item.id" :key="'prev-' + item.id" class="preview-row">
              <td colspan="6">
                <div class="preview-inline">
                  <audio :ref="el => { if (el) setAudioRef(el) }" controls autoplay :src="'/' + item.audio_path" class="preview-audio"></audio>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- 分页 -->
      <div v-if="ttsStore.history.pages > 1" class="pagination">
        <button :disabled="currentPage <= 1" @click="goPage(currentPage - 1)">上一页</button>
        <button v-for="p in displayPages" :key="p" :class="{ active: p === currentPage }" @click="goPage(p)">{{ p }}</button>
        <button :disabled="currentPage >= ttsStore.history.pages" @click="goPage(currentPage + 1)">下一页</button>
      </div>
    </div>

  </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useTTSStore } from '../stores/tts'

const ttsStore = useTTSStore()
const loading = ref(false)
const currentPage = ref(1)
const expandedId = ref(null)
let audioEl = null

function setAudioRef(el) {
  audioEl = el
}

const displayPages = computed(() => {
  const total = ttsStore.history.pages
  const pages = []
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= currentPage.value - 2 && i <= currentPage.value + 2)) {
      pages.push(i)
    }
  }
  return pages
})

async function goPage(p) {
  currentPage.value = p
  loading.value = true
  await ttsStore.fetchHistory(p)
  loading.value = false
}

async function togglePreview(item) {
  if (expandedId.value === item.id) {
    expandedId.value = null
    audioEl = null
  } else {
    expandedId.value = item.id
    await nextTick()
    if (audioEl) {
      audioEl.play().catch(() => {})
    }
  }
}

function downloadItem(item) {
  const a = document.createElement('a')
  a.href = '/' + item.audio_path
  a.download = item.audio_path.split('/').pop()
  a.click()
}

async function doDelete(id) {
  if (!confirm('确定删除该记录吗？')) return
  await ttsStore.deleteHistory(id)
}

function formatTime(t) {
  if (!t) return ''
  return t.replace('T', ' ').substring(0, 19)
}

onMounted(async () => {
  loading.value = true
  await ttsStore.fetchHistory()
  loading.value = false
})
</script>

<style scoped>
.page-bg {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: -1;
  background-color: #eaeffb;
  background-image: url('/背景.png');
  background-repeat: no-repeat;
  background-position: right top;
  background-size: 1100px;
}

.page-container {
  background: transparent;
}

.page-title {
  font-size: 44px;
}

.page-subtitle {
  margin-top: -4px;
  margin-bottom: 28px;
  font-size: 15px;
  color: #8899b4;
}

.preview-row td {
  padding: 0 16px 12px;
  border-bottom: 1px solid var(--border);
}

.preview-inline {
  background: #f4f7fc;
  border-radius: 10px;
  padding: 16px;
}

.preview-audio {
  width: 100%;
  height: 44px;
}
</style>
