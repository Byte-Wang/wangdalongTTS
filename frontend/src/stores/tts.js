import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../api'

export const useTTSStore = defineStore('tts', () => {
  const systemVoices = ref({ models: [], clone_texts: [] })
  const userVoices = ref([])
  const history = ref({ list: [], total: 0, page: 1, pages: 1 })
  const loading = ref(false)
  const synthesizing = ref(false)

  async function fetchSystemVoices() {
    const res = await api.get('/voice/system_voices.php')
    systemVoices.value = res.data
    return res.data
  }

  async function fetchUserVoices() {
    const res = await api.get('/voice/list.php')
    userVoices.value = res.data
    return res.data
  }

  async function cloneVoice(formData) {
    const res = await api.post('/voice/clone_voice.php', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    await fetchUserVoices()
    return res.data
  }

  async function deleteVoice(voiceId) {
    await api.post('/voice/delete.php', { voice_id: voiceId })
    await fetchUserVoices()
  }

  async function synthesize(params) {
    synthesizing.value = true
    try {
      const res = await api.post('/tts/synthesize.php', params)
      await fetchHistory()
      return res.data
    } finally {
      synthesizing.value = false
    }
  }

  async function fetchHistory(page = 1, limit = 20) {
    loading.value = true
    try {
      const res = await api.get('/tts/history.php', { params: { page, limit } })
      history.value = res.data
      return res.data
    } finally {
      loading.value = false
    }
  }

  async function deleteHistory(id) {
    await api.post('/tts/delete.php', { history_id: id })
    await fetchHistory()
  }

  return {
    systemVoices, userVoices, history, loading, synthesizing,
    fetchSystemVoices, fetchUserVoices, cloneVoice, deleteVoice,
    synthesize, fetchHistory, deleteHistory,
  }
})
