<template>
  <NuxtLink :to="`/car/${v.id}`" class="card">
    <div class="ph">
      <span v-if="v.is_featured" class="tag">{{ t('مميّزة') }}</span>
      <span v-if="v.status === 'reserved'" class="tag res">{{ t('محجوزة') }}</span>
      <img v-if="cover" :src="cover" alt="" loading="lazy" />
      <i v-else class="ti ti-car"></i>
    </div>
    <div class="bd">
      <h3>{{ v.make }} {{ v.model }}</h3>
      <div class="meta">
        <span><i class="ti ti-calendar"></i> {{ v.year }}</span>
        <span><i class="ti ti-road"></i> {{ km }} {{ t('كم') }}</span>
        <span><i class="ti ti-gas-station"></i> {{ t(FUELS[v.fuel] || v.fuel) }}</span>
      </div>
      <div class="ft">
        <span class="price">${{ price }}</span>
        <span class="more">{{ t('التفاصيل ‹') }}</span>
      </div>
    </div>
  </NuxtLink>
</template>

<script setup>
const props = defineProps({ v: { type: Object, required: true } })
const { fileUrl } = useApi()
const { t } = useLang()

const FUELS = { petrol: 'بنزين', diesel: 'ديزل', hybrid: 'هايبرد', electric: 'كهرباء' }
const km = computed(() => Number(props.v.mileage).toLocaleString('en-US'))
const price = computed(() => Number(props.v.price).toLocaleString('en-US'))
const cover = computed(() => props.v.cover?.file_path ? fileUrl(props.v.cover.file_path) : null)
</script>

<style scoped>
.card { background: #fff; border-radius: 14px; overflow: hidden; border: 1px solid var(--line); cursor: pointer; transition: .18s; display: block; }
.card:hover { transform: translateY(-3px); box-shadow: 0 14px 30px rgba(0,0,0,.1); }
.ph { height: 190px; position: relative; background: #E3E6EB; overflow: hidden; display: flex; align-items: center; justify-content: center; color: #B4BAC6; font-size: 44px; }
.ph img { width: 100%; height: 100%; object-fit: cover; }
.tag { position: absolute; top: 12px; right: 12px; background: var(--accent); color: #fff; font-size: 11px; font-weight: 700; padding: 3px 11px; border-radius: 20px; z-index: 1; }
.tag.res { background: var(--gold); color: #5A4718; }
.bd { padding: 15px 16px; }
h3 { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
.meta { display: flex; gap: 12px; font-size: 12.5px; color: var(--muted); margin-bottom: 12px; flex-wrap: wrap; }
.meta span { display: flex; align-items: center; gap: 4px; }
.ft { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid var(--line-2); }
.price { font-size: 20px; font-weight: 800; color: var(--accent); }
.more { font-size: 13px; border: 1px solid var(--line); border-radius: 8px; padding: 6px 12px; }
</style>
