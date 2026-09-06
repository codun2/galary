<template>
  <div>
    <SiteHeader />
    <div class="phead center"><h1>{{ t('تواصل معنا') }}</h1><p>فريقنا جاهز لمساعدتك في اختيار سيارتك</p></div>

    <div class="wrap">
      <div class="cgrid">
        <div class="panel">
          <div v-if="sent" class="ok-box">
            <i class="ti ti-circle-check" style="font-size:34px; display:block; margin-bottom:8px;"></i>
            {{ t('وصلنا استفسارك — سنتواصل معك قريباً') }}
          </div>
          <template v-else>
            <h3 class="sec-t">{{ t('أرسل استفسار') }}</h3>
            <div v-if="error" class="err">{{ error }}</div>
            <div class="g2">
              <div class="frow"><label>{{ t('الاسم') }}</label><input v-model="f.name" required /></div>
              <div class="frow"><label>{{ t('رقم الهاتف') }}</label><input v-model="f.phone" placeholder="0599..." required /></div>
            </div>
            <div class="frow"><label>{{ isEn ? 'Car you are interested in' : 'السيارة المهتم بها' }}</label><input v-model="f.subject" placeholder="مثال: مرسيدس GLC" /></div>
            <div class="frow"><label>{{ t('رسالتك') }}</label><textarea v-model="f.message" rows="4"></textarea></div>
            <button class="btn btn-accent btn-block btn-lg" @click="send" :disabled="busy || !f.name || !f.phone">{{ t('إرسال') }}</button>
          </template>
        </div>

        <div class="panel" style="height:fit-content;">
          <div class="cl" v-if="settings.phone"><i class="ti ti-phone"></i><div><b>الهاتف</b><small>{{ settings.phone }}</small></div></div>
          <a class="cl" v-if="waLink" :href="waLink" target="_blank"><i class="ti ti-brand-whatsapp"></i><div><b>واتساب</b><small>راسلنا مباشرة</small></div></a>
          <div class="cl" v-if="settings.address"><i class="ti ti-map-pin"></i><div><b>العنوان</b><small>{{ settings.address }}</small></div></div>
          <div class="cl" style="border:none;" v-if="settings.hours"><i class="ti ti-clock"></i><div><b>الدوام</b><small>{{ settings.hours }}</small></div></div>
        </div>
      </div>
    </div>

    <SiteFooter />
  </div>
</template>

<script setup>
const { t, isEn } = useLang()
const { api } = useApi()
const { settings, loadSettings, waLink } = useSettings()
const route = useRoute()

const sent = ref(false)
const busy = ref(false)
const error = ref('')
const f = reactive({ name: '', phone: '', subject: '', message: '', vehicle_id: route.query.car || null })

async function send() {
  busy.value = true
  error.value = ''
  try {
    await api('/site/lead', { method: 'POST', body: { ...f } })
    sent.value = true
  } catch (e) {
    error.value = e?.data?.message || 'تعذّر الإرسال — تأكد من البيانات'
  } finally { busy.value = false }
}

onMounted(async () => {
  loadSettings()
  if (route.query.car) {
    const v = await api(`/site/vehicles/${route.query.car}`).catch(() => null)
    if (v) f.subject = `${v.make} ${v.model} ${v.year}`
  }
})
</script>

<style scoped>
.phead { background: var(--ink); color: #fff; padding: 34px 44px; }
.phead h1 { font-size: 28px; font-weight: 800; }
.phead p { color: #C3C8D2; }
.wrap { padding: 28px 44px; max-width: 980px; margin: 0 auto; }
.cgrid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 24px; }
.cl { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--line-2); font-size: 14px; }
.cl i { width: 42px; height: 42px; border-radius: 10px; background: var(--bg); color: var(--accent); display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0; }
.cl b { display: block; } .cl small { color: var(--muted); font-size: 13px; }
@media (max-width: 860px) { .cgrid { grid-template-columns: 1fr; } .wrap { padding: 20px 16px; } }
</style>
