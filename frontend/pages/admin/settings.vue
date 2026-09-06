<template>
  <DashShell active="settings">
    <h1 class="page-title">إعدادات المعرض</h1>
    <p class="page-sub">تظهر هذه المعلومات للزبائن في الموقع</p>

    <div v-if="saved" class="ok-box" style="margin-bottom:16px;">تم حفظ الإعدادات ✓</div>

    <div class="panel" style="max-width:720px;">
      <div class="g2">
        <div class="frow"><label>اسم المعرض</label><input v-model="f.showroom_name" /></div>
        <div class="frow"><label>الهاتف</label><input v-model="f.phone" /></div>
        <div class="frow"><label>واتساب (بالصيغة الدولية)</label><input v-model="f.whatsapp" placeholder="9705..." dir="ltr" /></div>
        <div class="frow"><label>ساعات الدوام</label><input v-model="f.hours" /></div>
      </div>
      <div class="frow"><label>العنوان</label><input v-model="f.address" /></div>
      <div class="frow"><label>نبذة عن المعرض</label><textarea v-model="f.about" rows="3"></textarea></div>
      <button class="btn btn-accent btn-lg" @click="save" :disabled="busy">{{ busy ? '...' : 'حفظ الإعدادات' }}</button>
    </div>
  </DashShell>
</template>

<script setup>
const { api } = useApi()
const { fetchMe } = useAuth()

const busy = ref(false)
const saved = ref(false)
const f = reactive({ showroom_name: '', phone: '', whatsapp: '', address: '', hours: '', about: '' })

async function save() {
  busy.value = true
  saved.value = false
  try {
    await api('/dashboard/settings', { method: 'PUT', body: { ...f } })
    saved.value = true
    useState('sr_settings').value = { ...f }
  } finally { busy.value = false }
}

onMounted(async () => {
  const me = await fetchMe()
  if (!me) return navigateTo('/login')
  const s = await api('/dashboard/settings')
  Object.keys(f).forEach((k) => { if (s[k]) f[k] = s[k] })
})
</script>
