<template>
  <div>
    <SiteHeader />

    <div v-if="pending" class="empty">{{ t('جارِ التحميل…') }}</div>
    <div v-else-if="!v" class="empty">{{ t('لم يتم العثور على السيارة') }}</div>

    <template v-else>
      <NuxtLink to="/cars" class="back"><i class="ti" :class="isEn ? 'ti-arrow-left' : 'ti-arrow-right'"></i> {{ t('رجوع للسيارات') }}</NuxtLink>
      <div class="vd">
        <div class="gal">
          <div class="main">
            <img v-if="images[active]" :src="images[active]" alt="" />
            <i v-else class="ti ti-car" style="font-size:60px;"></i>
          </div>
          <div v-if="images.length > 1" class="thumbs">
            <img v-for="(img, i) in images" :key="i" :src="img" :class="{ on: i === active }" @click="active = i" />
          </div>

          <div class="panel" style="margin-top:16px;">
            <h3 class="sec-t">{{ t('المواصفات') }}</h3>
            <div class="spec">
              <div class="s"><small>{{ t('الماركة') }}</small><b>{{ v.make }}</b></div>
              <div class="s"><small>{{ t('الموديل') }}</small><b>{{ v.model }}</b></div>
              <div class="s"><small>{{ t('السنة') }}</small><b>{{ v.year }}</b></div>
              <div class="s"><small>{{ t('الممشى') }}</small><b>{{ km }} {{ t('كم') }}</b></div>
              <div class="s"><small>{{ t('الوقود') }}</small><b>{{ t(FUELS[v.fuel]) }}</b></div>
              <div class="s"><small>{{ t('ناقل الحركة') }}</small><b>{{ v.transmission === 'automatic' ? t('أوتوماتيك') : t('عادي') }}</b></div>
              <div class="s" v-if="v.color"><small>{{ t('اللون') }}</small><b>{{ v.color }}</b></div>
              <div class="s" v-if="v.drivetrain"><small>{{ t('الدفع') }}</small><b>{{ v.drivetrain }}</b></div>
            </div>
            <template v-if="v.features?.length">
              <h3 class="sec-t" style="margin-top:20px;">{{ t('المميزات') }}</h3>
              <div class="feats"><span v-for="ftr in v.features" :key="ftr">{{ ftr }}</span></div>
            </template>
            <p v-if="v.description" style="margin-top:16px; font-size:14px; color:#4A4F5A;">{{ v.description }}</p>
          </div>
        </div>

        <div>
          <div class="panel">
            <span v-if="v.is_featured" class="pill pill-r" style="background:var(--accent); color:#fff;">{{ t('مميّزة') }}</span>
            <span v-if="v.status === 'reserved'" class="pill pill-y">{{ t('محجوزة') }}</span>
            <h1 style="font-size:24px; margin:12px 0 4px; font-weight:800;">{{ v.make }} {{ v.model }} — {{ v.year }}</h1>
            <div class="page-sub" style="margin-bottom:14px;">{{ km }} {{ t('كم') }} · {{ t(FUELS[v.fuel]) }} · {{ v.transmission === 'automatic' ? t('أوتوماتيك') : t('عادي') }}</div>
            <div class="price-big">${{ price }}</div>
            <small style="color:var(--muted)">{{ t('شامل ضمان المعرض') }}</small>
            <div style="margin-top:18px; display:flex; flex-direction:column; gap:10px;">
              <a v-if="waLink" :href="waText" target="_blank" class="btn btn-wa btn-block btn-lg"><i class="ti ti-brand-whatsapp"></i> {{ t('تواصل عبر واتساب') }}</a>
              <a v-if="settings.phone" :href="`tel:${settings.phone.replace(/\\s/g,'')}`" class="btn btn-dark btn-block btn-lg"><i class="ti ti-phone"></i> {{ t('اتصل بالمعرض') }}</a>
              <NuxtLink :to="`/contact?car=${v.id}`" class="btn btn-out btn-block"><i class="ti ti-message"></i> {{ t('أرسل استفسار') }}</NuxtLink>
            </div>
          </div>
          <div class="panel" style="margin-top:16px;">
            <h3 class="sec-t">{{ isEn ? (settings.showroom_name_en || 'Showroom') : (settings.showroom_name || 'المعرض') }}</h3>
            <div class="cl" v-if="loc('address')"><i class="ti ti-map-pin"></i> {{ loc('address') }}</div>
            <div class="cl" v-if="loc('hours')"><i class="ti ti-clock"></i> {{ loc('hours') }}</div>
            <div class="cl" style="border:none;"><i class="ti ti-shield-check"></i> {{ t('سيارات مفحوصة ومضمونة') }}</div>
          </div>
        </div>
      </div>
    </template>

    <SiteFooter />
  </div>
</template>

<script setup>
const route = useRoute()
const { api, fileUrl } = useApi()
const { settings, loadSettings, waLink } = useSettings()
const { t, isEn, loc } = useLang()

const v = ref(null)
const pending = ref(true)
const active = ref(0)

const FUELS = { petrol: 'بنزين', diesel: 'ديزل', hybrid: 'هايبرد', electric: 'كهرباء' }
const km = computed(() => Number(v.value?.mileage || 0).toLocaleString('en-US'))
const price = computed(() => Number(v.value?.price || 0).toLocaleString('en-US'))
const images = computed(() => (v.value?.media || []).map((m) => fileUrl(m.file_path)))
const waText = computed(() => {
  if (!waLink.value || !v.value) return null
  const msg = encodeURIComponent(`مرحباً، مهتم بسيارة ${v.value.make} ${v.value.model} ${v.value.year}`)
  return `${waLink.value}?text=${msg}`
})

onMounted(async () => {
  loadSettings()
  v.value = await api(`/site/vehicles/${route.params.id}`).catch(() => null)
  pending.value = false
})
</script>

<style scoped>
.back { display: inline-flex; align-items: center; gap: 6px; color: var(--accent); font-size: 14px; cursor: pointer; padding: 20px 44px 0; font-weight: 500; }
.vd { display: grid; grid-template-columns: 1.5fr 1fr; gap: 28px; padding: 16px 44px 40px; }
.gal .main { height: 380px; border-radius: 14px; overflow: hidden; background: #E3E6EB; display: flex; align-items: center; justify-content: center; color: #B4BAC6; }
.gal .main img { width: 100%; height: 100%; object-fit: cover; }
.thumbs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 12px; }
.thumbs img { height: 76px; width: 100%; object-fit: cover; border-radius: 9px; cursor: pointer; border: 2px solid transparent; }
.thumbs img.on { border-color: var(--accent); }
.spec { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.spec .s { background: var(--bg); border-radius: 9px; padding: 11px 13px; }
.spec .s small { color: var(--muted); font-size: 11.5px; display: block; }
.spec .s b { font-size: 14px; font-weight: 500; }
.price-big { font-size: 34px; font-weight: 800; color: var(--accent); }
.feats { display: flex; flex-wrap: wrap; gap: 8px; }
.feats span { background: var(--bg); font-size: 12.5px; padding: 5px 12px; border-radius: 20px; color: #4A4F5A; }
.cl { display: flex; align-items: center; gap: 10px; padding: 11px 0; border-bottom: 1px solid var(--line-2); font-size: 14px; }
.cl i { color: var(--accent); font-size: 18px; }
@media (max-width: 860px) { .vd { grid-template-columns: 1fr; padding: 16px 16px 30px; } .back { padding: 16px 16px 0; } }
</style>
