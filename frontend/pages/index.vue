<template>
  <div>
    <SiteHeader />

    <!-- hero -->
    <div class="hero">
      <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1600&q=80" alt="" />
      <div class="ov"></div>
      <div class="in">
        <span class="tag">{{ cms('hero_badge', 'معرض سيارات معتمد') }}</span>
        <h1>{{ cms('hero_title', 'سيارتك القادمة تنتظرك في أوتو غاليري') }}</h1>
        <p>{{ cms('hero_subtitle', 'تشكيلة مختارة من أفضل السيارات المفحوصة بأسعار تنافسية وضمان المعرض.') }}</p>
        <div class="acts">
          <NuxtLink to="/cars" class="btn btn-accent btn-lg">{{ t('تصفّح السيارات') }}</NuxtLink>
          <a v-if="waLink" :href="waLink" target="_blank" class="btn btn-ghost btn-lg"><i class="ti ti-brand-whatsapp"></i> {{ t('تواصل معنا') }}</a>
        </div>
      </div>

      <!-- quick search card (inside hero) -->
      <form class="searchcard" @submit.prevent="goSearch">
      <div class="sc-head"><i class="ti ti-search"></i> {{ cms('search_title', 'ابحث عن سيارتك') }}</div>
      <div class="sc-grid">
        <div class="fld">
          <label>{{ t('الماركة') }}</label>
          <select v-model="q.make" @change="q.model = ''">
            <option value="">{{ t('كل الماركات') }}</option>
            <option v-for="m in facets.makes" :key="m.make" :value="m.make">{{ m.make }}</option>
          </select>
        </div>
        <div class="fld">
          <label>{{ t('الموديل') }}</label>
          <select v-model="q.model" :disabled="!q.make">
            <option value="">{{ q.make ? t('كل الموديلات') : t('اختر الماركة أولاً') }}</option>
            <option v-for="mo in modelsOf" :key="mo" :value="mo">{{ mo }}</option>
          </select>
        </div>
        <div class="fld">
          <label>{{ t('الوقود') }}</label>
          <select v-model="q.fuel">
            <option value="">{{ t('الكل') }}</option><option value="petrol">{{ t('بنزين') }}</option><option value="diesel">{{ t('ديزل') }}</option><option value="hybrid">{{ t('هايبرد') }}</option><option value="electric">{{ t('كهرباء') }}</option>
          </select>
        </div>
        <div class="fld">
          <label>{{ t('السنة من') }}</label>
          <select v-model="q.year_min"><option value="">{{ t('أي سنة') }}</option><option v-for="y in facets.years" :key="y" :value="y">{{ y }}</option></select>
        </div>
        <div class="fld">
          <label>{{ t('ناقل الحركة') }}</label>
          <select v-model="q.transmission"><option value="">{{ t('الكل') }}</option><option value="automatic">{{ t('أوتوماتيك') }}</option><option value="manual">{{ t('عادي') }}</option></select>
        </div>
        <div class="fld">
          <label>{{ t('الممشى حتى') }}</label>
          <select v-model="q.mileage_max">
            <option value="">{{ t('أي ممشى') }}</option><option value="30000">30,000 {{ t('كم') }}</option><option value="60000">60,000 {{ t('كم') }}</option><option value="100000">100,000 {{ t('كم') }}</option><option value="150000">150,000 {{ t('كم') }}</option>
          </select>
        </div>
      </div>
      <button class="btn btn-accent btn-lg btn-block sc-show">
        <i class="ti ti-car"></i>
        {{ counting ? '…' : (isEn ? `Show ${matchCount} ${matchCount === 1 ? 'vehicle' : 'vehicles'}` : `عرض ${matchCount} ${carWord}`) }}
      </button>
      <NuxtLink to="/cars" class="btn btn-out btn-block sc-adv">{{ t('بحث متقدّم') }}</NuxtLink>
      </form>
    </div>

    <!-- brand logos strip -->
    <BrandStrip />

    <!-- featured -->
    <div class="sec">
      <div class="sec-head">
        <div><h2>{{ cms('featured_title', 'سيارات مميّزة') }}</h2><p>{{ cms('featured_subtitle', 'أحدث ما وصل إلى معرضنا') }}</p></div>
        <NuxtLink to="/cars" class="link">{{ t('عرض الكل ‹') }}</NuxtLink>
      </div>
      <div v-if="pending" class="empty">{{ t('جارِ التحميل…') }}</div>
      <div v-else-if="!cars.length" class="empty">{{ t('لا توجد سيارات معروضة حالياً') }}</div>
      <div v-else class="grid">
        <CarCard v-for="v in cars" :key="v.id" :v="v" />
      </div>
    </div>

    <!-- why us -->
    <div class="sec" style="padding-top:0;">
      <div class="sec-head" style="justify-content:center;"><h2>{{ cms('why_title', 'ليش أوتو غاليري؟') }}</h2></div>
      <div class="why">
        <div class="w"><i class="ti ti-shield-check"></i><h4>{{ cms('why1_title') || 'سيارات مفحوصة' }}</h4><p>{{ cms('why1_text') || 'كل سيارة تمرّ بفحص شامل قبل العرض.' }}</p></div>
        <div class="w"><i class="ti ti-tag"></i><h4>{{ cms('why2_title') || 'أسعار واضحة' }}</h4><p>{{ cms('why2_text') || 'بلا مفاجآت — السعر المعروض هو السعر.' }}</p></div>
        <div class="w"><i class="ti ti-file-certificate"></i><h4>{{ cms('why3_title') || 'ضمان المعرض' }}</h4><p>{{ cms('why3_text') || 'ضمان على المحرك والقير حسب السيارة.' }}</p></div>
        <div class="w"><i class="ti ti-headset"></i><h4>{{ cms('why4_title') || 'خدمة ما بعد البيع' }}</h4><p>{{ cms('why4_text') || 'فريقنا معك حتى بعد الاستلام.' }}</p></div>
      </div>
    </div>

    <!-- cta -->
    <div class="cta">
      <h2>{{ cms('cta_title', 'عندك سيارة تبي تبيعها؟') }}</h2>
      <p>{{ cms('cta_text', 'نشتري سيارتك أو نعرضها في معرضنا — تواصل معنا اليوم.') }}</p>
      <NuxtLink to="/contact" class="btn btn-accent btn-lg">{{ cms('cta_button', 'تواصل الآن') }}</NuxtLink>
    </div>

    <SiteFooter />
  </div>
</template>

<script setup>
const { api } = useApi()
const { settings, loadSettings, waLink } = useSettings()
const { t, cms, isEn } = useLang()

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
.hero { position: relative; background: #0E0F13; overflow: hidden; display: flex; align-items: center; gap: 30px; padding: 48px 44px; flex-wrap: wrap; }
.hero img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .45; }
.hero .ov { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(14,15,19,.96) 25%, rgba(14,15,19,.4)); }
.hero .in { position: relative; color: #fff; flex: 1; min-width: 300px; max-width: 620px; }
.hero .tag { display: inline-block; background: var(--accent); font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-bottom: 16px; }
.hero h1 { font-size: 36px; font-weight: 800; line-height: 1.3; margin-bottom: 12px; }
.hero p { font-size: 15.5px; color: #C3C8D2; margin-bottom: 24px; max-width: 520px; }
.acts { display: flex; gap: 12px; flex-wrap: wrap; }
.searchcard { position: relative; z-index: 2; background: #fff; width: 460px; max-width: 100%; border-radius: 18px; box-shadow: 0 20px 50px rgba(0,0,0,.3); padding: 24px; }
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
@media (max-width: 960px) { .hero { flex-direction: column; align-items: stretch; } .searchcard { width: 100%; } }
@media (max-width: 860px) { .sec, .cta { padding: 34px 16px; } .hero { padding: 36px 16px; } .hero h1 { font-size: 27px; } .sc-grid { grid-template-columns: 1fr 1fr; } }
</style>
