<template>
  <div v-if="brands.length" class="strip" :class="{ ltr: isEn }">
    <div class="track">
      <div v-for="(b, i) in loop" :key="i" class="bi">
        <img v-if="logoOf(b)" :src="logoOf(b)" :alt="b.name" loading="lazy" @error="failed.add(b.id)" />
        <span v-if="!logoOf(b) || failed.has(b.id)" class="nm">{{ b.name }}</span>
        <span v-else class="nm sm">{{ b.name }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
const { api, fileUrl } = useApi()
const { isEn } = useLang()

const brands = ref([])
const failed = reactive(new Set())

const loop = computed(() => [...brands.value, ...brands.value])
const logoOf = (b) => (failed.has(b.id) ? null : (b.logo_path ? fileUrl(b.logo_path) : b.logo_url))

onMounted(async () => {
  try { brands.value = await api('/site/brands') } catch {}
})
</script>

<style scoped>
.strip { background: #fff; border-block: 1px solid var(--line); padding: 26px 0; overflow: hidden; }
.track { display: flex; width: max-content; animation: scroll-rtl 42s linear infinite; }
.strip.ltr .track { animation-name: scroll-ltr; }
.strip:hover .track { animation-play-state: paused; }
.bi { display: flex; align-items: center; gap: 12px; margin: 0 40px; opacity: .8; transition: .2s; }
.bi:hover { opacity: 1; }
.bi img { height: 46px; width: auto; }
.nm { font-size: 20px; font-weight: 700; color: #8A909C; white-space: nowrap; letter-spacing: .5px; }
.nm.sm { font-size: 15px; font-weight: 600; }
/* حلقة لا نهائية سلسة — RTL يتحرك من اليمين لليسار، LTR بالعكس */
@keyframes scroll-rtl { from { transform: translateX(50%); } to { transform: translateX(0); } }
@keyframes scroll-ltr { from { transform: translateX(0); } to { transform: translateX(-50%); } }
</style>
