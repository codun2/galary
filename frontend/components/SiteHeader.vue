<template>
  <div class="nav">
    <NuxtLink to="/" class="brand">
      <div class="logo"><i class="ti ti-car"></i></div>
      <div><div class="bt">{{ isEn ? (settings.showroom_name_en || 'Auto Gallery') : (settings.showroom_name || 'أوتو غاليري') }}</div><div class="bs">AUTO GALLERY</div></div>
    </NuxtLink>
    <div class="links">
      <NuxtLink to="/">{{ t('الرئيسية') }}</NuxtLink>
      <NuxtLink to="/cars">{{ t('سياراتنا') }}</NuxtLink>
      <NuxtLink to="/contact">{{ t('تواصل') }}</NuxtLink>
    </div>
    <div class="right">
      <span v-if="settings.phone" class="phone"><i class="ti ti-phone"></i> {{ settings.phone }}</span>
      <button class="langbtn" @click="toggleLang">{{ isEn ? 'عربي' : 'EN' }}</button>
      <NuxtLink to="/login" class="btn btn-accent">{{ t('دخول المعرض') }}</NuxtLink>
    </div>
  </div>
</template>

<script setup>
const { settings, loadSettings } = useSettings()
const { t, isEn, toggleLang } = useLang()
onMounted(loadSettings)
</script>

<style scoped>
.nav { background: var(--ink); color: #fff; display: flex; align-items: center; justify-content: space-between; padding: 15px 28px; }
.brand { display: flex; align-items: center; gap: 11px; cursor: pointer; }
.logo { width: 40px; height: 40px; border-radius: 10px; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.bt { font-size: 19px; font-weight: 800; }
.bs { font-size: 10px; color: var(--muted); letter-spacing: 3px; }
.links { display: flex; gap: 24px; font-size: 14.5px; color: #C3C8D2; }
.links a:hover { color: #fff; }
.right { display: flex; align-items: center; gap: 14px; }
.phone { color: #C3C8D2; font-size: 14px; display: flex; align-items: center; gap: 6px; }
.langbtn { font-family: inherit; background: transparent; border: 1px solid #3A3F4A; color: #fff; font-size: 13px; padding: 7px 14px; border-radius: 9px; cursor: pointer; }
.langbtn:hover { background: var(--ink-2); }
@media (max-width: 820px) { .links, .phone { display: none; } }
</style>
