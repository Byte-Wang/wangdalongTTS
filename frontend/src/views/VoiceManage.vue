<template>
<div class="page-root">
  <div class="page-bg"></div>
  <div class="page-container">
    <div style="margin-bottom:4px">
      <h1 class="page-title" style="margin:0">音色管理</h1>
    </div>
    <p class="page-subtitle">管理创建的音色，支持克隆你的个人音色，效果真实、自然</p>

    <!-- 已有音色列表 -->
    <div v-if="userVoices.length > 0" class="card">
      <div class="flex justify-between items-center" style="margin-bottom:16px">
        <h3 style="margin:0">我的音色（{{ userVoices.length }}）</h3>
        <button class="btn btn-primary" @click="showClonePanel = true">+ 创建克隆音色</button>
      </div>
      <table class="table">
        <thead>
          <tr><th>音色名称</th><th>音色 ID</th><th>绑定模型</th><th>创建时间</th><th>操作</th></tr>
        </thead>
        <tbody>
          <tr v-for="v in userVoices" :key="v.id">
            <td style="font-weight:500">{{ v.name }}</td>
            <td style="font-size:12px;color:var(--text-secondary)">{{ v.voice_id }}</td>
            <td><span class="voice-tag">{{ v.model }}</span></td>
            <td style="font-size:13px;color:var(--text-secondary)">{{ formatTime(v.created_at) }}</td>
            <td>
              <button class="btn btn-danger btn-sm" @click="doDelete(v.id)">删除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="card empty-state">
      <p>还没有创建克隆音色</p>
      <button class="btn btn-primary" style="margin-top:12px" @click="showClonePanel = true">+ 创建克隆音色</button>
    </div>

    <!-- 登录弹窗 -->
    <AuthModal :visible="showAuthModal" @close="showAuthModal = false" @success="onAuthSuccess" />

    <!-- 创建音色弹窗 -->
    <div v-if="showClonePanel" class="modal-overlay" @click.self="showClonePanel = false">
      <div class="modal-panel">
        <div class="modal-header">
          <h3>声音复刻</h3>
          <button class="modal-close" @click="showClonePanel = false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <div class="clone-row">
            <div class="form-group clone-half">
              <label class="form-label">音色名称</label>
              <input v-model="cloneForm.name" class="input" placeholder="为你的音色起个名字" />
            </div>
            <div class="form-group clone-half">
              <label class="form-label">绑定模型</label>
              <select v-model="cloneForm.model" class="input">
                <option value="">-- 选择模型 --</option>
                <option v-for="m in cloneModels" :key="m.key" :value="m.key">{{ m.name }}</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">选择录音文案</label>
            <div class="text-buttons">
              <button
                v-for="(t, i) in cloneTexts"
                :key="i"
                :class="['text-btn', { active: cloneForm.text === t }]"
                @click="cloneForm.text = t"
              >
                文案{{ i + 1 }}
              </button>
            </div>
            <div v-if="cloneForm.text" class="text-full">
              {{ cloneForm.text }}
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              <span>上传音频</span>
              <span class="form-hint">支持 WAV、MP3、M4A，推荐 10~20 秒，最大 10MB</span>
            </label>

            <div :class="['recorder-wrap', { recording: isRecording }]" style="margin-bottom:12px">
              <div v-if="!isRecording && !recordedBlob">
                <p style="font-size:13px;color:var(--text-secondary);margin-bottom:12px">在线录音（推荐）</p>
                <button class="btn btn-outline" @click="startRecording">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                  开始录音
                </button>
              </div>
              <div v-else-if="isRecording">
                <p style="color:var(--danger);font-size:14px;margin-bottom:8px">录音中... <span style="font-weight:600">{{ recordingTime }}s</span></p>
                <button class="btn btn-danger btn-sm" @click="stopRecording">停止录音</button>
              </div>
              <div v-else-if="recordedBlob">
                <p style="color:var(--success);font-size:14px;margin-bottom:8px">录音完成（{{ recordedDuration }} 秒）</p>
                <audio :src="recordedAudioUrl" controls style="width:100%;margin-bottom:8px"></audio>
                <div class="flex gap-sm justify-center">
                  <button class="btn btn-outline btn-sm" @click="retryRecord">重新录音</button>
                  <button class="btn btn-primary btn-sm" @click="useRecordedAudio">使用此录音</button>
                </div>
              </div>
            </div>

            <div style="text-align:center;color:var(--text-secondary);font-size:13px;margin:8px 0">或</div>

            <div class="flex gap-sm items-center">
              <input ref="fileInputRef" type="file" accept=".wav,.mp3,.m4a" @change="onFileChange" style="display:none" />
              <button class="btn btn-outline btn-sm" @click="$refs.fileInputRef.click()">选择文件</button>
              <span v-if="cloneForm.file" style="font-size:13px">{{ cloneForm.file.name }}</span>
              <span v-else style="font-size:13px;color:var(--text-secondary)">未选择文件</span>
            </div>
          </div>

          <button class="btn btn-primary" :disabled="!canClone || cloning" @click="doClone" style="width:100%;margin-top:8px">
            <span v-if="cloning" class="loading-spinner" style="width:16px;height:16px"></span>
            {{ cloning ? '音色创建中...' : '开始复刻音色' }}
          </button>
          <p v-if="cloneError" style="color:var(--danger);font-size:13px;margin-top:8px">{{ cloneError }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useTTSStore } from '../stores/tts'
import { useAuthStore } from '../stores/auth'
import AuthModal from '../components/AuthModal.vue'

const ttsStore = useTTSStore()
const authStore = useAuthStore()
const showClonePanel = ref(false)
const cloning = ref(false)
const cloneError = ref('')
const fileInputRef = ref(null)
const showAuthModal = ref(false)

const cloneForm = reactive({ name: '', model: '', text: '', file: null })

// 录音相关
const isRecording = ref(false)
const recordedBlob = ref(null)
const recordedAudioUrl = ref('')
const recordedDuration = ref(0)
const recordingTime = ref(0)
let mediaRecorder = null
let recordTimer = null

const cloneModels = computed(() => {
  const m = ttsStore.systemVoices.models || []
  return m.filter(m => ['qwen_audio_tts', 'cosyvoice', 'qwen_tts'].includes(m.category))
})

const cloneTexts = computed(() => ttsStore.systemVoices.clone_texts || [])
const userVoices = computed(() => ttsStore.userVoices)

const canClone = computed(() => {
  return cloneForm.name && cloneForm.model && cloneForm.text && (cloneForm.file || recordedBlob.value)
})

// WebM → WAV 转换
function writeString(view, offset, str) {
  for (let i = 0; i < str.length; i++) {
    view.setUint8(offset + i, str.charCodeAt(i))
  }
}

async function webmToWav(blob) {
  const audioContext = new (window.AudioContext || window.webkitAudioContext)()
  const arrayBuffer = await blob.arrayBuffer()
  const audioBuffer = await audioContext.decodeAudioData(arrayBuffer)
  audioContext.close()

  const numChannels = audioBuffer.numberOfChannels
  const sampleRate = audioBuffer.sampleRate
  const bitsPerSample = 16
  const data = audioBuffer.getChannelData(0)
  const dataLength = data.length * (bitsPerSample / 8)
  const buffer = new ArrayBuffer(44 + dataLength)
  const view = new DataView(buffer)

  writeString(view, 0, 'RIFF')
  view.setUint32(4, 36 + dataLength, true)
  writeString(view, 8, 'WAVE')
  writeString(view, 12, 'fmt ')
  view.setUint32(16, 16, true)
  view.setUint16(20, 1, true)
  view.setUint16(22, numChannels, true)
  view.setUint32(24, sampleRate, true)
  view.setUint32(28, sampleRate * numChannels * bitsPerSample / 8, true)
  view.setUint16(32, numChannels * bitsPerSample / 8, true)
  view.setUint16(34, bitsPerSample, true)
  writeString(view, 36, 'data')
  view.setUint32(40, dataLength, true)

  let offset = 44
  for (let i = 0; i < data.length; i++) {
    const sample = Math.max(-1, Math.min(1, data[i]))
    view.setInt16(offset, sample < 0 ? sample * 0x8000 : sample * 0x7FFF, true)
    offset += 2
  }

  return new Blob([buffer], { type: 'audio/wav' })
}

async function startRecording() {
  try {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('当前浏览器不支持录音功能，请使用 Chrome、Edge 或 Firefox，并确保已配置 HTTPS')
      return
    }

    const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
    mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' })
    const chunks = []
    mediaRecorder.ondataavailable = e => chunks.push(e.data)
    mediaRecorder.onstop = () => {
      recordedBlob.value = new Blob(chunks, { type: 'audio/webm' })
      recordedAudioUrl.value = URL.createObjectURL(recordedBlob.value)
      stream.getTracks().forEach(t => t.stop())
    }
    mediaRecorder.start()
    isRecording.value = true
    recordingTime.value = 0
    recordTimer = setInterval(() => recordingTime.value++, 1000)
  } catch (e) {
    const msg = e.message || String(e)
    if (msg.includes('secure') || msg.includes('HTTPS') || msg.includes('NotAllowed')) {
      alert('录音需要 HTTPS 安全连接。请使用文件上传代替，或为网站配置 SSL 证书。\n\n错误详情：' + msg)
    } else if (msg.includes('NotFound') || msg.includes('device')) {
      alert('未检测到麦克风设备，请检查设备连接。')
    } else {
      alert('无法访问麦克风：' + msg)
    }
  }
}

function stopRecording() {
  mediaRecorder?.stop()
  isRecording.value = false
  recordedDuration.value = recordingTime.value
  clearInterval(recordTimer)
}

function retryRecord() {
  recordedBlob.value = null
  recordedAudioUrl.value = ''
  recordedDuration.value = 0
}

async function useRecordedAudio() {
  const wavBlob = await webmToWav(recordedBlob.value)
  cloneForm.file = new File([wavBlob], 'recording.wav', { type: 'audio/wav' })
  recordedBlob.value = null
  recordedAudioUrl.value = ''
}

function onFileChange(e) {
  cloneForm.file = e.target.files[0] || null
  recordedBlob.value = null
  recordedAudioUrl.value = ''
}

async function doClone() {
  // 未登录时弹出登录弹窗
  if (!authStore.isLoggedIn) {
    showAuthModal.value = true
    return
  }

  if (!confirm('声音复刻成功后将扣除 50 积分，是否确认开始复刻？')) return

  cloneError.value = ''
  cloning.value = true
  try {
    const fd = new FormData()
    fd.append('name', cloneForm.name)
    fd.append('model', cloneForm.model)
    fd.append('audio_file', cloneForm.file)
    await ttsStore.cloneVoice(fd)
    await authStore.fetchProfile()
    cloneForm.name = ''
    cloneForm.file = null
    cloneForm.model = cloneModels.value.length > 0 ? cloneModels.value[0].key : ''
    recordedBlob.value = null
    recordedAudioUrl.value = ''
    showClonePanel.value = false
    alert('音色创建成功！')
  } catch (e) {
    cloneError.value = e.message
  } finally {
    cloning.value = false
  }
}

function onAuthSuccess() {
  showAuthModal.value = false
  // 登录成功后刷新音色列表
  ttsStore.fetchUserVoices().catch(() => {})
}

async function doDelete(id) {
  if (!confirm('确定删除该音色吗？')) return
  await ttsStore.deleteVoice(id)
}

function formatTime(t) {
  if (!t) return ''
  return t.replace('T', ' ').substring(0, 19)
}

onMounted(async () => {
  try {
    await ttsStore.fetchSystemVoices()
    if (authStore.isLoggedIn) {
      await ttsStore.fetchUserVoices()
    }
    if (cloneModels.value.length > 0 && !cloneForm.model) {
      cloneForm.model = cloneModels.value[0].key
    }
  } catch (e) { /* ignore */ }
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

/* ── 创建弹窗 ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(4px);
}

.modal-panel {
  width: min(620px, 94vw);
  max-height: 90vh;
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
  overflow-y: auto;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 0;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 700;
  color: #1d2d55;
  margin: 0;
}

.modal-close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 10px;
  background: #f1f4f8;
  color: #5e6f8d;
  cursor: pointer;
  transition: background 0.2s;
}

.modal-close:hover {
  background: #e4e8f0;
}

.modal-body {
  padding: 20px 24px 24px;
}

.clone-row {
  display: flex;
  gap: 16px;
}

.clone-half {
  flex: 1;
  min-width: 0;
}

.text-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.text-btn {
  padding: 8px 16px;
  border: 1.5px solid #dce3ee;
  border-radius: 10px;
  background: #f9fafc;
  color: #5e6f8d;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.text-btn:hover {
  border-color: #2b6ef5;
  color: #2b6ef5;
  background: #eef3ff;
}

.text-btn.active {
  border-color: #2b6ef5;
  background: #2b6ef5;
  color: #fff;
}

.text-full {
  margin-top: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  background: #f4f7fc;
  color: #3b4a6b;
  font-size: 14px;
  line-height: 1.8;
}

@media (max-width: 480px) {
  .clone-row {
    flex-direction: column;
  }
}
</style>
