<template>
  <div class="page-container">
    <h1 class="page-title">生成历史</h1>

    <div v-if="ttsStore.history.list.length === 0 && !loading" class="card empty-state">
      <p>暂无生成记录</p>
      <p style="font-size:12px;margin-top:4px">前往 <router-link to="/">语音合成</router-link> 开始使用</p>
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
          <tr v-for="item in ttsStore.history.list" :key="item.id">
            <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              {{ item.text.substring(0, 40) }}{{ item.text.length > 40 ? '...' : '' }}
            </td>
            <td>{{ item.voice_name }}</td>
            <td><span class="voice-tag">{{ item.model }}</span></td>
            <td>{{ item.points_cost }}</td>
            <td style="font-size:13px;color:var(--text-secondary)">{{ formatTime(item.created_at) }}</td>
            <td>
              <div class="flex gap-sm">
                <button class="btn btn-outline btn-sm" @click="previewAudio(item)">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                </button>
                <button class="btn btn-outline btn-sm" @click="downloadItem(item)">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </button>
                <button class="btn btn-danger btn-sm" @click="doDelete(item.id)">删除</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- 分页 -->
      <div v-if="ttsStore.history.pages > 1" class="pagination">
        <button :disabled="currentPage <= 1" @click="goPage(currentPage - 1)">上一页</button>
        <button v-for="p in displayPages" :key="p" :class="{ active: p === currentPage }" @click="goPage(p)">{{ p }}</button>
        <button :disabled="currentPage >= ttsStore.history.pages" @click="goPage(currentPage + 1)">下一页</button>
      </div>
    </div>

    <!-- 音频预览 -->
    <div v-if="previewItem" class="result-card">
      <div class="flex justify-between items-center">
        <span style="font-weight:500">{{ previewItem.voice_name }} - {{ previewItem.model }}</span>
        <button class="btn btn-sm btn-outline" @click="previewItem = null">关闭</button>
      </div>
      <audio controls :src="'/' + previewItem.audio_path" style="width:100%;margin-top:12px"></audio>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useTTSStore } from '../stores/tts'

const ttsStore = useTTSStore()
const loading = ref(false)
const currentPage = ref(1)
const previewItem = ref(null)

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

function previewAudio(item) {
  previewItem.value = item
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
