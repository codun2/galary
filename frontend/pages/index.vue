<template>
  <div>
    <SiteHeader />

    <!-- hero -->
    <div class="hero">
      <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1600&q=80" alt="" />
      <div class="ov"></div>
      <div class="in">
        <span class="tag">معرض سيارات معتمد</span>
        <h1>سيارتك القادمة تنتظرك في {{ settings.showroom_name || 'أوتو غاليري' }}</h1>
        <p>{{ settings.about || 'تشكيلة مختارة من أفضل السيارات المفحوصة بأسعار تنافسية وضمان المعرض.' }}</p>
        <div class="acts">
          <NuxtLink to="/cars" class="btn btn-accent btn-lg">تصفّح السيارات</NuxtLink>
          <a v-if="waLink" :href="waLink" target="_blank" class="btn btn-ghost btn-lg"><i class="ti ti-brand-whatsapp"></i> تواصل معنا</a>
        </div>
      </div>
    </div>

    <!-- quick search -->
    <form class="searchwrap" @submit.prevent="goSearch">
      <div class="fld"><label>الماركة</label>
        <select v-model="q.make"><option value="">كل الماركات</option><option v-for="m in makes" :key="m" :value="m">{{ m }}</option></select>
      </div>
      <div class="fld"><label>السعر حتى</label>
        <select v-model="q.price_max"><option value="">أي سعر</option><option value="20000">$20,000</option><option value="40000">$40,000</option><option value="60000">$60,000</option><option value="100000">$100,000</option></select>
      </div>
      <div class="fld"><label>السنة من</label>
        <select v-model="q.year_min"><option value="">أي سنة</option><option v-for="y in years" :key="y" :value="y">{{ y }}</option></select>
      </div>
      <div class="fld"><label>ناقل الحركة</label>
        <select v-model="q.transmission"><option value="">الكل</option><option value="automatic">أوتوماتيك</option><option value="manual">عادي</option></select>
      </div>
      <button class="btn btn-accent btn-lg"><i class="ti ti-search"></i> بحث</button>
    </form>

    <!-- featured -->
    <div class="sec">
      <div class="sec-head">
        <div><h2>سيارات مميّزة</h2><p>أحدث ما وصل إلى معرضنا</p></div>
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
      <div class="sec-head" style="justify-content:center;"><h2>ليش {{ settings.showroom_name || 'أوتو غاليري' }}؟</h2></div>
      <div class="why">
        <div class="w"><i class="ti ti-shield-check"></i><h4>سيارات مفحوصة</h4><p>كل سيارة تمرّ بفحص شامل قبل العرض.</p></div>
        <div class="w"><i class="ti ti-tag"></i><h4>أسعار واضحة</h4><p>بلا مفاجآت — السعر المعروض هو السعر.</p></div>
        <div class="w"><i class="ti ti-file-certificate"></i><h4>ضمان المعرض</h4><p>ضمان على المحرك والقير حسب السيارة.</p></div>
        <div class="w"><i class="ti ti-headset"></i><h4>خدمة ما بعد البيع</h4><p>فريقنا معك حتى بعد الاستلام.</p></div>
      </div>
    </div>

    <!-- cta -->
    <div class="cta">
      <h2>عندك سيارة تبي تبيعها؟</h2>
      <p>نشتري سيارتك أو نعرضها في معرضنا — تواصل معنا اليوم.</p>
      <NuxtLink to="/contact" class="btn btn-accent btn-lg">تواصل الآن</NuxtLink>
    </div>

    <SiteFooter />
  </div>
</template>

<script setup>
const { api } = useApi()
const { settings, loadSettings, waLink } = useSettings()

const cars = ref([])
const pending = ref(true)
const q = reactive({ make: '', price_max: '', year_min: '', transmission: '' })

const makes = computed(() => [...new Set(cars.value.map((c) => c.make))])
const years = [2018, 2019, 2020, 2021, 2022, 2023, 2024]

function goSearch() {
  const query = {}
  Object.entries(q).forEach(([k, v]) => { if (v) query[k] = v })
  navigateTo({ path: '/cars', query })
}

onMounted(async () => {
  loadSettings()
  try { cars.value = await api('/site/vehicles/featured') } catch {}
  pending.value = false
})
</script>

<style scoped>
.hero { position: relative; min-height: 430px; background: #0E0F13; overflow: hidden; display: flex; align-items: center; }
.hero img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: .5; }
.hero .ov { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(14,15,19,.95) 20%, rgba(14,15,19,.4)); }
.hero .in { position: relative; padding: 50px 44px; color: #fff; max-width: 640px; }
.hero .tag { display: inline-block; background: var(--accent); font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-bottom: 16px; }
.hero h1 { font-size: 36px; font-weight: 800; line-height: 1.3; margin-bottom: 12px; }
.hero p { font-size: 15.5px; color: #C3C8D2; margin-bottom: 24px; max-width: 520px; }
.acts { display: flex; gap: 12px; flex-wrap: wrap; }
.searchwrap { background: #fff; margin: -36px 44px 0; position: relative; z-index: 2; border-radius: 14px; box-shadow: 0 12px 30px rgba(0,0,0,.12); padding: 18px; display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 12px; align-items: end; }
.fld label { font-size: 12px; }
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
@media (max-width: 860px) { .searchwrap { grid-template-columns: 1fr 1fr; margin: -36px 16px 0; } .sec, .cta { padding: 34px 16px; } .hero .in { padding: 40px 20px; } .hero h1 { font-size: 27px; } }
</style>
