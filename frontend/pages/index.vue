<template>
  <div>
    <SiteHeader />

    <!-- hero -->
    <div class="hero">
      <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1600&q=80" alt="" />
      <div class="ov"></div>
      <div class="in">
        <span class="tag">{{ settings.hero_badge || 'معرض سيارات معتمد' }}</span>
        <h1>{{ settings.hero_title || `سيارتك القادمة تنتظرك في ${settings.showroom_name || 'أوتو غاليري'}` }}</h1>
        <p>{{ settings.hero_subtitle || settings.about || 'تشكيلة مختارة من أفضل السيارات المفحوصة بأسعار تنافسية وضمان المعرض.' }}</p>
        <div class="acts">
          <NuxtLink to="/cars" class="btn btn-accent btn-lg">تصفّح السيارات</NuxtLink>
          <a v-if="waLink" :href="waLink" target="_blank" class="btn btn-ghost btn-lg"><i class="ti ti-brand-whatsapp"></i> تواصل معنا</a>
        </div>
      </div>
    </div>

    <!-- quick search (OpenLane-style card) -->
    <form class="searchcard" @submit.prevent="goSearch">
      <div class="sc-head"><i class="ti ti-search"></i> {{ settings.search_title || 'ابحث عن سيارتك' }}</div>
      <div class="sc-grid">
        <div class="fld">
          <label>الماركة</label>
          <select v-model="q.make" @change="q.model = ''">
            <option value="">كل الماركات</option>
            <option v-for="m in facets.makes" :key="m.make" :value="m.make">{{ m.make }}</option>
          </select>
        </div>
        <div class="fld">
          <label>الموديل</label>
          <select v-model="q.model" :disabled="!q.make">
            <option value="">{{ q.make ? 'كل الموديلات' : 'اختر الماركة أولاً' }}</option>
            <option v-for="mo in modelsOf" :key="mo" :value="mo">{{ mo }}</option>
          </select>
        </div>
        <div class="fld">
          <label>الوقود</label>
          <select v-model="q.fuel">
            <option value="">الكل</option><option value="petrol">بنزين</option><option value="diesel">ديزل</option><option value="hybrid">هايبرد</option><option value="electric">كهرباء</option>
          </select>
        </div>
        <div class="fld">
          <label>السنة من</label>
          <select v-model="q.year_min"><option value="">أي سنة</option><option v-for="y in facets.years" :key="y" :value="y">{{ y }}</option></select>
        </div>
        <div class="fld">
          <label>ناقل الحركة</label>
          <select v-model="q.transmission"><option value="">الكل</option><option value="automatic">أوتوماتيك</option><option value="manual">عادي</option></select>
        </div>
        <div class="fld">
          <label>الممشى حتى</label>
          <select v-model="q.mileage_max">
            <option value="">أي ممشى</option><option value="30000">30,000 كم</option><option value="60000">60,000 كم</option><option value="100000">100,000 كم</option><option value="150000">150,000 كم</option>
          </select>
        </div>
      </div>
      <button class="btn btn-accent btn-lg btn-block sc-show">
        <i class="ti ti-car"></i>
        {{ counting ? '…' : `عرض ${matchCount} ${carWord}` }}
      </button>
      <NuxtLink to="/cars" class="btn btn-out btn-block sc-adv">بحث متقدّم</NuxtLink>
    </form>

    <!-- featured -->
    <div class="sec">
      <div class="sec-head">
        <div><h2>{{ settings.featured_title || 'سيارات مميّزة' }}</h2><p>{{ settings.featured_subtitle || 'أحدث ما وصل إلى معرضنا' }}</p></div>
        <NuxtLink to="/cars" class="link">عرض الكل ‹</NuxtLink>
      </div>
      <div v-if="pending" class="empty">جارِ التحميل…</div>
      <div v-else-if="!cars.length" class="empty">لا توجد سيارات معروضة حالياً</div>
      <div v-else class="grid">
        <CarCard v-for="v in cars" :key="v.id" :v="v" />
      </div>
    </div>

    <!-- why us -->
    <div class="sec" style="padding-top:0;">
      <div class="sec-head" style="justify-content:center;"><h2>{{ settings.why_title || `ليش ${settings.showroom_name || 'أوتو غاليري'}؟` }}</h2></div>
      <div class="why">
        <div class="w"><i class="ti ti-shield-check"></i><h4>{{ settings.why1_title || 'سيارات مفحوصة' }}</h4><p>{{ settings.why1_text || 'كل سيارة تمرّ بفحص شامل قبل العرض.' }}</p></div>
        <div class="w"><i class="ti ti-tag"></i><h4>{{ settings.why2_title || 'أسعار واضحة' }}</h4><p>{{ settings.why2_text || 'بلا مفاجآت — السعر المعروض هو السعر.' }}</p></div>
        <div class="w"><i class="ti ti-file-certificate"></i><h4>{{ settings.why3_title || 'ضمان المعرض' }}</h4><p>{{ settings.why3_text || 'ضمان على المحرك والقير حسب السيارة.' }}</p></div>
        <div class="w"><i class="ti ti-headset"></i><h4>{{ settings.why4_title || 'خدمة ما بعد البيع' }}</h4><p>{{ settings.why4_text || 'فريقنا معك حتى بعد الاستلام.' }}</p></div>
      </div>
    </div>

    <!-- cta -->
    <div class="cta">
      <h2>{{ settings.cta_title || 'عندك سيارة تبي تبيعها؟' }}</h2>
      <p>{{ settings.cta_text || 'نشتري سيارتك أو نعرضها في معرضنا — تواصل معنا اليوم.' }}</p>
      <NuxtLink to="/contact" class="btn btn-accent btn-lg">{{ settings.cta_button || 'تواصل الآن' }}</NuxtLink>
    </div>

    <SiteFooter />
  </div>
</template>

<script setup>
const { api } = useApi()
const { settings, loadSettings, waLink } = useSettings()

const cars = ref([])
const pending = ref(true)
const q = reactive({ make: '', model: '', fuel: '', year_min: '', transmission: '', mileage_max: '' })

const facets = ref({ makes: [], years: [], total: 0 })
const matchCount = ref(0)
const counting = ref(false)
let countTimer = null

const modelsOf = computed(() => facets.value.makes.find((m) => m.make === q.make)?.models || [])
const carWord = computed(() => {
  const n = matchCount.value
  if (n === 1) return 'سيارة واحدة'
  if (n === 2) return 'سيارتين'
  return n <= 10 ? 'سيارات' : 'سيارة'
})

async function refreshCount() {
  counting.value = true
  try {
    const p = new URLSearchParams()
    Object.entries(q).forEach(([k, v]) => { if (v) p.append(k, v) })
    const r = await api('/site/vehicles?' + p)
    matchCount.value = r.total
  } catch {}
  counting.value = false
}
watch(q, () => {
  clearTimeout(countTimer)
  countTimer = setTimeout(refreshCount, 350)
})

function goSearch() {
  const query = {}
  Object.entries(q).forEach(([k, v]) => { if (v) query[k] = v })
  navigateTo({ path: '/cars', query })
}

onMounted(async () => {
  loadSettings()
  try {
    ;[cars.value, facets.value] = await Promise.all([
      api('/site/vehicles/featured'),
      api('/site/facets'),
    ])
    matchCount.value = facets.value.total
  } catch {}
  pending.value = false
})
</script>

<style scoped>
.hero { position: relative; min-height: 560px; background: #0E0F13; overflow: visible; display: flex; align-items: center; }
.hero img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .5; }
.hero .ov { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(14,15,19,.95) 20%, rgba(14,15,19,.45)); }
.hero .in { position: relative; padding: 50px 44px; color: #fff; max-width: 620px; margin-inline-end: auto; }
.hero .tag { display: inline-block; background: var(--accent); font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-bottom: 16px; }
.hero h1 { font-size: 36px; font-weight: 800; line-height: 1.3; margin-bottom: 12px; }
.hero p { font-size: 15.5px; color: #C3C8D2; margin-bottom: 24px; max-width: 520px; }
.acts { display: flex; gap: 12px; flex-wrap: wrap; }
.searchcard { background: #fff; width: min(520px, calc(100% - 32px)); margin: -520px 44px 48px auto; position: relative; z-index: 2; border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,.25); padding: 24px; margin-left: 44px; margin-right: auto; }
.sc-head { font-size: 17px; font-weight: 800; display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
.sc-head i { color: var(--accent); }
.sc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
.fld label { font-size: 12px; margin-bottom: 5px; }
.fld select:disabled { background-color: var(--bg); color: var(--muted); cursor: not-allowed; }
.sc-show { border-radius: 30px; font-size: 15.5px; font-weight: 700; padding: 13px; }
.sc-adv { border-radius: 30px; margin-top: 10px; border: 1.5px solid var(--accent); color: var(--accent); font-weight: 600; }
.sc-adv:hover { background: #FEF3F2; }
.sec { padding: 44px 44px; }
.sec-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; }
.sec-head h2 { font-size: 26px; font-weight: 800; }
.sec-head p { color: var(--muted); font-size: 14px; margin-top: 4px; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.why { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
.why .w { text-align: center; padding: 26px 18px; background: #fff; border: 1px solid var(--line); border-radius: 14px; }
.why .w i { font-size: 30px; color: var(--accent); }
.why .w h4 { font-size: 16px; font-weight: 700; margin: 12px 0 6px; }
.why .w p { font-size: 13px; color: var(--muted); line-height: 1.7; }
.cta { background: var(--ink); color: #fff; padding: 48px 44px; text-align: center; }
.cta h2 { font-size: 26px; font-weight: 800; margin-bottom: 8px; }
.cta p { color: #C3C8D2; margin-bottom: 22px; }
@media (max-width: 1150px) { .searchcard { margin: -40px auto 40px; } .hero { min-height: 430px; } }
@media (max-width: 860px) { .sec, .cta { padding: 34px 16px; } .hero .in { padding: 40px 20px; } .hero h1 { font-size: 27px; } .sc-grid { grid-template-columns: 1fr; } }
</style>
