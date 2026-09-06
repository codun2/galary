<template>
  <DashShell active="settings">
    <h1 class="page-title">إعدادات المعرض</h1>
    <p class="page-sub">تظهر هذه المعلومات للزبائن في الموقع</p>

    <div v-if="saved" class="ok-box" style="margin-bottom:16px;">تم حفظ الإعدادات ✓</div>

    <div class="tabs">
      <button class="tab" :class="{ on: tab === 'info' }" @click="tab = 'info'"><i class="ti ti-building-store"></i> معلومات المعرض</button>
      <button class="tab" :class="{ on: tab === 'texts' }" @click="tab = 'texts'"><i class="ti ti-typography"></i> نصوص الموقع</button>
    </div>

    <!-- معلومات المعرض -->
    <div v-show="tab === 'info'" class="panel" style="max-width:720px;">
      <div class="g2">
        <div class="frow"><label>اسم المعرض</label><input v-model="f.showroom_name" /></div>
        <div class="frow"><label>اسم المعرض بالإنجليزية</label><input v-model="f.showroom_name_en" dir="ltr" placeholder="Auto Gallery" /></div>
        <div class="frow"><label>الهاتف</label><input v-model="f.phone" /></div>
        <div class="frow"><label>واتساب (بالصيغة الدولية)</label><input v-model="f.whatsapp" placeholder="9705..." dir="ltr" /></div>
        <div class="frow"><label>ساعات الدوام</label><input v-model="f.hours" /></div>
      </div>
      <div class="frow"><label>العنوان</label><input v-model="f.address" /></div>
      <div class="frow"><label>نبذة عن المعرض</label><textarea v-model="f.about" rows="3"></textarea></div>
      <button class="btn btn-accent btn-lg" @click="save" :disabled="busy">{{ busy ? '...' : 'حفظ الإعدادات' }}</button>
    </div>

    <!-- نصوص الموقع -->
    <div v-show="tab === 'texts'" style="max-width:760px;">
      <div class="panel" v-for="grp in TEXT_GROUPS" :key="grp.title" style="margin-bottom:16px;">
        <h3 class="sec-t">{{ grp.title }}</h3>
        <div v-for="fld in grp.fields" :key="fld.key" class="frow dual">
          <label>{{ fld.label }}</label>
          <div class="pair">
            <div>
              <small>عربي</small>
              <textarea v-if="fld.long" v-model="f[fld.key]" rows="2"></textarea>
              <input v-else v-model="f[fld.key]" />
            </div>
            <div>
              <small>English</small>
              <textarea v-if="fld.long" v-model="f[fld.key + '_en']" rows="2" dir="ltr"></textarea>
              <input v-else v-model="f[fld.key + '_en']" dir="ltr" />
            </div>
          </div>
        </div>
      </div>
      <button class="btn btn-accent btn-lg" @click="save" :disabled="busy">{{ busy ? '...' : 'حفظ النصوص' }}</button>
    </div>
  </DashShell>
</template>

<script setup>
const { api } = useApi()
const { fetchMe } = useAuth()

const busy = ref(false)
const saved = ref(false)
const tab = ref('info')

const TEXT_GROUPS = [
  { title: 'الواجهة الرئيسية (Hero)', fields: [
    { key: 'hero_badge', label: 'الشارة العلوية' },
    { key: 'hero_title', label: 'العنوان الرئيسي' },
    { key: 'hero_subtitle', label: 'النص التعريفي', long: true },
    { key: 'search_title', label: 'عنوان صندوق البحث' },
  ]},
  { title: 'قسم السيارات المميّزة', fields: [
    { key: 'featured_title', label: 'العنوان' },
    { key: 'featured_subtitle', label: 'السطر التوضيحي' },
  ]},
  { title: 'قسم "ليش نحن" (٤ بطاقات)', fields: [
    { key: 'why_title', label: 'عنوان القسم' },
    { key: 'why1_title', label: 'بطاقة ١ — العنوان' }, { key: 'why1_text', label: 'بطاقة ١ — النص', long: true },
    { key: 'why2_title', label: 'بطاقة ٢ — العنوان' }, { key: 'why2_text', label: 'بطاقة ٢ — النص', long: true },
    { key: 'why3_title', label: 'بطاقة ٣ — العنوان' }, { key: 'why3_text', label: 'بطاقة ٣ — النص', long: true },
    { key: 'why4_title', label: 'بطاقة ٤ — العنوان' }, { key: 'why4_text', label: 'بطاقة ٤ — النص', long: true },
  ]},
  { title: 'الدعوة أسفل الصفحة (CTA)', fields: [
    { key: 'cta_title', label: 'العنوان' },
    { key: 'cta_text', label: 'النص', long: true },
    { key: 'cta_button', label: 'نص الزر' },
  ]},
  { title: 'الفوتر', fields: [
    { key: 'footer_text', label: 'نص الفوتر', long: true },
  ]},
]

const BASE_KEYS = ['showroom_name', 'phone', 'whatsapp', 'address', 'hours', 'about',
  ...TEXT_GROUPS.flatMap((g) => g.fields.map((x) => x.key))]
const ALL_KEYS = [...BASE_KEYS, ...BASE_KEYS.map((k) => k + '_en')]
const f = reactive(Object.fromEntries(ALL_KEYS.map((k) => [k, ''])))

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

<style scoped>
.tabs { display: flex; gap: 8px; margin-bottom: 18px; }
.tab { font-family: inherit; padding: 9px 18px; border-radius: 22px; font-size: 13.5px; cursor: pointer; border: 1px solid var(--line); background: #fff; display: inline-flex; align-items: center; gap: 7px; }
.tab.on { background: var(--ink); color: #fff; border-color: var(--ink); }
.pair { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.pair small { display: block; font-size: 11px; color: var(--muted); margin-bottom: 4px; }
@media (max-width: 700px) { .pair { grid-template-columns: 1fr; } }
</style>
