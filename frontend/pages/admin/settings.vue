<template>
  <DashShell active="settings">
    <h1 class="page-title">إعدادات المعرض</h1>
    <p class="page-sub">تظهر هذه المعلومات للزبائن في الموقع</p>

    <div v-if="saved" class="ok-box" style="margin-bottom:16px;">تم حفظ الإعدادات ✓</div>

    <div class="tabs">
      <button class="tab" :class="{ on: tab === 'info' }" @click="tab = 'info'"><i class="ti ti-building-store"></i> معلومات المعرض</button>
      <button class="tab" :class="{ on: tab === 'texts' }" @click="tab = 'texts'"><i class="ti ti-typography"></i> نصوص الموقع</button>
      <button class="tab" :class="{ on: tab === 'why' }" @click="tab = 'why'"><i class="ti ti-stars"></i> بطاقات "ليش نحن"</button>
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
      <div class="frow"><label>العنوان بالإنجليزية</label><input v-model="f.address_en" dir="ltr" placeholder="Showrooms St., City" /></div>
      <div class="frow"><label>ساعات الدوام بالإنجليزية</label><input v-model="f.hours_en" dir="ltr" placeholder="Sat–Thu · 9am – 9pm" /></div>
      <div class="frow"><label>نبذة عن المعرض</label><textarea v-model="f.about" rows="3"></textarea></div>
      <div class="frow"><label>النبذة بالإنجليزية</label><textarea v-model="f.about_en" rows="3" dir="ltr" placeholder="A car showroom offering..."></textarea></div>
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

    <!-- بطاقات ليش نحن (ديناميكية) -->
    <div v-show="tab === 'why'" style="max-width:820px;">
      <p class="page-sub" style="margin-bottom:14px;">أضف أو احذف البطاقات التي تظهر في قسم "ليش نحن" على الصفحة الرئيسية — اختر أيقونة لكل بطاقة واكتب النص بالعربي والإنجليزي.</p>

      <div class="panel wcard" v-for="(c, i) in cards" :key="i">
        <div class="wcard-head">
          <span class="wcard-n">بطاقة {{ i + 1 }}</span>
          <div class="wcard-tools">
            <button class="mini" title="تحريك للأعلى" :disabled="i === 0" @click="move(i, -1)"><i class="ti ti-arrow-up"></i></button>
            <button class="mini" title="تحريك للأسفل" :disabled="i === cards.length - 1" @click="move(i, 1)"><i class="ti ti-arrow-down"></i></button>
            <button class="mini danger" title="حذف البطاقة" @click="removeCard(i)"><i class="ti ti-trash"></i></button>
          </div>
        </div>

        <div class="wrow">
          <div class="icon-pick">
            <label>الأيقونة</label>
            <div class="icon-preview"><i :class="`ti ${c.icon || 'ti-check'}`"></i></div>
            <div class="icon-grid">
              <button v-for="ic in ICONS" :key="ic" class="ig" :class="{ on: c.icon === ic }" @click="c.icon = ic" :title="ic"><i :class="`ti ${ic}`"></i></button>
            </div>
          </div>

          <div class="wfields">
            <div class="pair">
              <div><small>العنوان (عربي)</small><input v-model="c.title" placeholder="سيارات مفحوصة" /></div>
              <div><small>Title (English)</small><input v-model="c.title_en" dir="ltr" placeholder="Inspected Cars" /></div>
            </div>
            <div class="pair">
              <div><small>النص (عربي)</small><textarea v-model="c.text" rows="2" placeholder="وصف قصير للميزة"></textarea></div>
              <div><small>Text (English)</small><textarea v-model="c.text_en" rows="2" dir="ltr" placeholder="Short feature description"></textarea></div>
            </div>
          </div>
        </div>
      </div>

      <button class="btn btn-out btn-lg" style="margin-bottom:16px;" @click="addCard"><i class="ti ti-plus"></i> إضافة بطاقة</button>
      <div><button class="btn btn-accent btn-lg" @click="save" :disabled="busy">{{ busy ? '...' : 'حفظ البطاقات' }}</button></div>
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
  { title: 'قسم "ليش نحن"', fields: [
    { key: 'why_title', label: 'عنوان القسم' },
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

// ---- بطاقات "ليش نحن" ----
const ICONS = [
  'ti-shield-check', 'ti-tag', 'ti-file-certificate', 'ti-headset', 'ti-cash', 'ti-refresh',
  'ti-car', 'ti-gauge', 'ti-award', 'ti-thumb-up', 'ti-clock', 'ti-map-pin',
  'ti-key', 'ti-discount-2', 'ti-star', 'ti-heart', 'ti-shield', 'ti-tools',
  'ti-wallet', 'ti-certificate', 'ti-users', 'ti-truck-delivery', 'ti-percentage', 'ti-check',
]
const DEFAULT_WHY = [
  { icon: 'ti-shield-check', title: 'سيارات مفحوصة', title_en: 'Inspected Cars', text: 'كل سيارة تمرّ بفحص شامل قبل العرض.', text_en: 'Every car passes a full inspection before listing.' },
  { icon: 'ti-tag', title: 'أسعار واضحة', title_en: 'Clear Pricing', text: 'بلا مفاجآت — السعر المعروض هو السعر.', text_en: 'No surprises — the listed price is the price.' },
  { icon: 'ti-file-certificate', title: 'ضمان المعرض', title_en: 'Showroom Warranty', text: 'ضمان على المحرك والقير حسب السيارة.', text_en: 'Engine & gearbox warranty depending on the car.' },
  { icon: 'ti-headset', title: 'خدمة ما بعد البيع', title_en: 'After-Sales Service', text: 'فريقنا معك حتى بعد الاستلام.', text_en: 'Our team stays with you after delivery.' },
  { icon: 'ti-cash', title: 'تمويل ميسّر', title_en: 'Easy Financing', text: 'خيارات تقسيط وتمويل مرنة.', text_en: 'Flexible installment & financing options.' },
  { icon: 'ti-refresh', title: 'استبدال سيارتك', title_en: 'Trade-In', text: 'نستبدل سيارتك القديمة بأخرى من معرضنا.', text_en: 'Trade your old car for one from our showroom.' },
]
const cards = ref([])

function addCard() { cards.value.push({ icon: 'ti-check', title: '', title_en: '', text: '', text_en: '' }) }
function removeCard(i) { cards.value.splice(i, 1) }
function move(i, d) {
  const j = i + d
  if (j < 0 || j >= cards.value.length) return
  const arr = cards.value
  ;[arr[i], arr[j]] = [arr[j], arr[i]]
}

async function save() {
  busy.value = true
  saved.value = false
  try {
    const why_cards = JSON.stringify(cards.value)
    await api('/dashboard/settings', { method: 'PUT', body: { ...f, why_cards } })
    saved.value = true
    useState('sr_settings').value = { ...f, why_cards }
  } finally { busy.value = false }
}

onMounted(async () => {
  const me = await fetchMe()
  if (!me) return navigateTo('/login')
  const s = await api('/dashboard/settings')
  Object.keys(f).forEach((k) => { if (s[k]) f[k] = s[k] })
  try {
    const arr = JSON.parse(s.why_cards || '[]')
    cards.value = Array.isArray(arr) && arr.length ? arr : JSON.parse(JSON.stringify(DEFAULT_WHY))
  } catch { cards.value = JSON.parse(JSON.stringify(DEFAULT_WHY)) }
})
</script>

<style scoped>
.tabs { display: flex; gap: 8px; margin-bottom: 18px; }
.tab { font-family: inherit; padding: 9px 18px; border-radius: 22px; font-size: 13.5px; cursor: pointer; border: 1px solid var(--line); background: #fff; display: inline-flex; align-items: center; gap: 7px; }
.tab.on { background: var(--ink); color: #fff; border-color: var(--ink); }
.pair { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.pair small { display: block; font-size: 11px; color: var(--muted); margin-bottom: 4px; }
@media (max-width: 700px) { .pair { grid-template-columns: 1fr; } }

/* بطاقات ليش نحن */
.wcard { margin-bottom: 14px; }
.wcard-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.wcard-n { font-weight: 700; font-size: 14px; }
.wcard-tools { display: flex; gap: 6px; }
.mini { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--line); background: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; color: var(--muted); }
.mini:hover:not(:disabled) { background: var(--bg); color: var(--ink); }
.mini:disabled { opacity: .4; cursor: not-allowed; }
.mini.danger:hover { background: #FEF3F2; color: #E11D48; border-color: #FBD5D5; }
.wrow { display: grid; grid-template-columns: 220px 1fr; gap: 18px; }
.icon-pick > label { font-size: 12px; color: var(--muted); display: block; margin-bottom: 6px; }
.icon-preview { width: 100%; height: 54px; border-radius: 10px; background: var(--bg); display: flex; align-items: center; justify-content: center; margin-bottom: 8px; }
.icon-preview i { font-size: 28px; color: var(--accent); }
.icon-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 5px; }
.ig { aspect-ratio: 1; border: 1px solid var(--line); border-radius: 8px; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--ink); }
.ig:hover { background: var(--bg); }
.ig.on { background: var(--ink); color: #fff; border-color: var(--ink); }
.wfields { display: flex; flex-direction: column; gap: 12px; }
.wfields .pair small { display: block; font-size: 11px; color: var(--muted); margin-bottom: 4px; }
@media (max-width: 700px) { .wrow { grid-template-columns: 1fr; } .icon-grid { grid-template-columns: repeat(8, 1fr); } }
</style>
