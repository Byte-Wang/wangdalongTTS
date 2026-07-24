<template>
  <div class="page-container">
    <h1 class="page-title">语音合成</h1>

    <div class="synthesize-grid">
      <!-- 左侧：音色 & 模型选择 -->
      <div>
        <div class="card" style="margin-bottom:16px">
          <h3 style="margin-bottom:16px">音色来源</h3>
          <div class="tabs">
            <span :class="['tab-item', { active: voiceSource === 'system' }]" @click="voiceSource = 'system'">系统音色</span>
            <span :class="['tab-item', { active: voiceSource === 'clone' }]" @click="voiceSource = 'clone'">克隆音色</span>
          </div>

          <!-- 系统音色 -->
          <div v-if="voiceSource === 'system'">
            <div class="form-group">
              <label class="form-label">选择模型</label>
              <select v-model="selectedModel" class="input" @change="onModelChange">
                <option value="">-- 请选择模型 --</option>
                <option v-for="m in models" :key="m.key" :value="m.key">{{ m.name }}</option>
              </select>
            </div>

            <div v-if="currentModelVoices.length > 0">
              <label class="form-label" style="margin-bottom:8px">选择音色</label>
              <div class="voice-grid">
                <div v-for="v in currentModelVoices" :key="v.voice_id"
                  :class="['voice-card', { active: selectedVoice === v.voice_id }]"
                  @click="selectedVoice = v.voice_id">
                  <div class="voice-name">{{ v.name }}</div>
                  <div class="voice-desc">{{ v.desc }}</div>
                  <div v-if="v.gender !== '-'" class="voice-tag">{{ v.gender }}</div>
                  <div class="voice-tag" v-if="v.language">{{ v.language }}</div>
                </div>
              </div>
            </div>

            <p v-if="selectedModel && currentModelVoices.length === 0" style="color:var(--text-secondary);font-size:13px;margin-top:12px">
              该模型暂未预置系统音色，请使用克隆音色。
            </p>
          </div>

          <!-- 克隆音色 -->
          <div v-else>
            <div v-if="userVoices.length === 0" class="empty-state">
              <p>暂无克隆音色</p>
              <p style="font-size:12px;margin-top:4px">前往 <router-link to="/voices">音色管理</router-link> 创建</p>
            </div>
            <div v-else class="voice-grid">
              <div v-for="v in userVoices" :key="v.id"
                :class="['voice-card', { active: selectedVoice === v.voice_id }]"
                @click="selectCloneVoice(v)">
                <div class="voice-name">{{ v.name }}</div>
                <div class="voice-desc">{{ v.model }}</div>
                <div class="voice-tag" style="background:var(--primary-light);color:var(--primary)">克隆</div>
              </div>
            </div>
          </div>
        </div>

        <!-- 已选音色信息 -->
        <div v-if="selectedVoice" class="card" style="background:var(--primary-bg);border-color:var(--primary)">
          <p style="font-size:13px;color:var(--text-secondary)">当前音色</p>
          <p style="font-weight:600">{{ selectedVoiceDisplay }}</p>
          <p style="font-size:12px;color:var(--text-secondary)">模型：{{ selectedModel || activeCloneModel }}</p>
        </div>
      </div>

      <!-- 右侧：文本输入 & 合成 -->
      <div>
        <div class="card">
          <div class="form-group">
            <label class="form-label">输入要合成的文本</label>
            <textarea v-model="inputText" class="input" rows="6" placeholder="请输入要转为语音的文本内容..."></textarea>
            <div class="form-hint flex justify-between">
              <span>{{ textCharCount }} 字（预计消耗 {{ estimatedCost }} 积分）</span>
              <span>可用积分：{{ auth.points }}</span>
            </div>
          </div>

          <button class="btn btn-primary btn-lg" style="width:100%" :disabled="!canSynthesize" @click="doSynthesize">
            <span v-if="ttsStore.synthesizing" class="loading-spinner" style="width:16px;height:16px"></span>
            {{ ttsStore.synthesizing ? '合成中...' : '开始生成语音' }}
          </button>
        </div>

        <!-- 合成结果 -->
        <div v-if="result" class="result-card">
          <div class="flex justify-between items-center" style="margin-bottom:12px">
            <h3 style="color:var(--primary)">合成成功</h3>
            <span class="points-badge">消耗 {{ result.points_cost }} 积分</span>
          </div>

          <div class="voice-card" style="cursor:default;border-color:var(--border);margin-bottom:12px">
            <div class="voice-name">{{ result.voice_name || result.file_name }}</div>
            <div class="voice-desc">模型：{{ result.model }} | 文本长度：{{ result.text_length }} 字</div>
            <div class="voice-desc" style="font-size:11px;color:var(--text-secondary);margin-top:2px">
              {{ result.created_at || '' }}
            </div>
          </div>

          <audio controls :src="result.audio_url" style="width:100%;margin-bottom:12px">
            您的浏览器不支持音频播放
          </audio>

          <div class="flex gap-sm">
            <button class="btn btn-primary btn-sm" @click="downloadAudio">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              下载音频
            </button>
            <router-link to="/history" class="btn btn-outline btn-sm">查看全部历史</router-link>
            <span style="font-size:12px;color:var(--text-secondary);align-self:center;margin-left:auto">
              剩余 {{ result.points_remain }} 积分
            </span>
          </div>
        </div>

        <!-- 错误提示 -->
        <div v-if="errorMsg" class="card" style="margin-top:16px;border-color:var(--danger);background:#fef2f2">
          <p style="color:var(--danger);font-size:14px">{{ errorMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useTTSStore } from '../stores/tts'
import api from '../api'

const auth = useAuthStore()
const ttsStore = useTTSStore()

const voiceSource = ref('system')
const selectedModel = ref('')
const selectedVoice = ref('')
const activeCloneModel = ref('')
const inputText = ref('')
const result = ref(null)
const errorMsg = ref('')

const models = computed(() => ttsStore.systemVoices.models || [])
const userVoices = computed(() => ttsStore.userVoices)

const currentModelVoices = computed(() => {
  const m = models.value.find(m => m.key === selectedModel.value)
  return m ? m.voices : []
})

const selectedVoiceDisplay = computed(() => {
  // 系统音色
  const m = models.value.find(m => m.key === selectedModel.value)
  if (m) {
    const v = m.voices.find(v => v.voice_id === selectedVoice.value)
    if (v) return v.name
  }
  // 克隆音色
  const cv = userVoices.value.find(v => v.voice_id === selectedVoice.value)
  if (cv) return cv.name
  return selectedVoice.value
})

// 计算字数（中文1字=1，英文半角按0.5折算）
const textCharCount = computed(() => {
  return inputText.value.length
})

const estimatedCost = computed(() => {
  const text = inputText.value
  let cost = 0
  for (const ch of text) {
    const code = ch.charCodeAt(0)
    // 中文字符、全角标点
    if (code > 127 || (code >= 0xFF01 && code <= 0xFF60) || (code >= 0x3000 && code <= 0x303F)) {
      cost += 1
    } else if (ch !== ' ' && ch !== '\n') {
      cost += 0.5
    }
  }
  return Math.ceil(cost * 10) / 10
})

const canSynthesize = computed(() => {
  return !ttsStore.synthesizing &&
    selectedVoice.value &&
    inputText.value.trim() &&
    estimatedCost.value <= auth.points
})

function onModelChange() {
  selectedVoice.value = ''
}

function selectCloneVoice(v) {
  selectedVoice.value = v.voice_id
  activeCloneModel.value = v.model
}

async function doSynthesize() {
  const model = voiceSource.value === 'clone' ? activeCloneModel.value : selectedModel.value
  if (!model) {
    errorMsg.value = '请先选择模型'
    return
  }

  result.value = null
  errorMsg.value = ''
  try {
    const data = await ttsStore.synthesize({
      text: inputText.value.trim(),
      voice: selectedVoice.value,
      model: model,
    })
    result.value = data
    errorMsg.value = ''
    // 刷新积分
    await auth.fetchProfile()
  } catch (e) {
    errorMsg.value = '合成失败：' + e.message
    console.error('[TTS] 合成失败', e)
  }
}

function downloadAudio() {
  if (result.value) {
    const a = document.createElement('a')
    a.href = result.value.audio_url
    a.download = result.value.file_name
    a.click()
  }
}

onMounted(async () => {
  try {
    await ttsStore.fetchSystemVoices()
    await ttsStore.fetchUserVoices()
  } catch (e) {
    // 静默失败
  }
})
</script>
