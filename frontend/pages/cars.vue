<template>
  <div>
    <SiteHeader />
    <div class="phead"><h1>سياراتنا</h1><p>كل السيارات المتوفّرة في المعرض</p></div>

    <div class="listwrap">
      <aside class="filters">
        <h3>تصفية النتائج</h3>
        <div class="frow"><label>بحث</label><input v-model="f.search" placeholder="ماركة أو موديل" @keyup.enter="load" /></div>
        <div class="frow"><label>الماركة</label>
          <select v-model="f.make"><option value="">الكل</option><option v-for="m in makes" :key="m" :value="m">{{ m }}</option></select>
        </div>
        <div class="frow"><label>الوقود</label>
          <select v-model="f.fuel"><option value="">الكل</option><option value="petrol">بنزين</option><option value="diesel">ديزل</option><option value="hybrid">هايبرد</option><option value="electric">كهرباء</option></select>
        </div>
        <div class="frow"><label>ناقل الحركة</label>
          <select v-model="f.transmission"><option value="">الكل</option><option value="automatic">أوتوماتيك</option><option value="manual">عادي</option></select>
        </div>
        <div class="frow"><label>السنة من</label><input v-model="f.year_min" type="number" placeholder="2020" /></div>
        <div class="frow"><label>أقصى سعر ($)</label><input v-model="f.price_max" type="number" placeholder="60000" /></div>
        <button class="btn btn-dark btn-block" @click="load">تطبيق</button>
        <button class="btn btn-out btn-block" style="margin-top:8px;" @click="reset">مسح</button>
      </aside>

      <div>
        <div class="listbar">
          <span>{{ total }} سيارة</span>
          <select v-model="f.sort" @change="load" style="width:auto;">
            <option value="latest">الأحدث</option>
            <option value="price_asc">السعر: الأقل</option>
            <option value="price_desc">السعر: الأعلى</option>
          </select>
        </div>
        <div v-if="pending" class="empty">جارِ التحميل…</div>
        <div v-else-if="!items.length" class="empty">لا توجد نتائج مطابقة</div>
        <div v-else class="grid">
          <CarCard v-for="v in items" :key="v.id" :v="v" />
        </div>
      </div>
    </div>

    <SiteFooter />
  </div>
</template>

<script setup>
const { api } = useApi()
const route = useRoute()

const items = ref([])
const total = ref(0)
const pending = ref(true)
const f = reactive({
  search: '', make: route.query.make || '', model: route.query.model || '',
  fuel: route.query.fuel || '', transmission: route.query.transmission || '',
  year_min: route.query.year_min || '', price_max: route.query.price_max || '',
  mileage_max: route.query.mileage_max || '',
  sort: 'latest',
})
const makes = ref([])

async function load() {
  pending.value = true
  const p = new URLSearchParams()
  Object.entries(f).forEach(([k, v]) => { if (v) p.append(k, v) })
  const r = await api('/site/vehicles?' + p)
  items.value = r.data
  total.value = r.total
  if (!makes.value.length) makes.value = [...new Set(r.data.map((c) => c.make))]
  pending.value = false
}
function reset() {
  Object.assign(f, { search: '', make: '', fuel: '', transmission: '', year_min: '', price_max: '', sort: 'latest' })
  load()
}

onMounted(load)
</script>

<style scoped>
.phead { background: var(--ink); color: #fff; padding: 26px 44px; }
.phead h1 { font-size: 26px; font-weight: 800; }
.phead p { color: #C3C8D2; }
.listwrap { display: grid; grid-template-columns: 250px 1fr; gap: 24px; padding: 28px 44px; }
.filters { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px; height: fit-content; }
.filters h3 { font-size: 16px; font-weight: 700; margin-bottom: 16px; }
.listbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; color: var(--muted); font-size: 14px; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
@media (max-width: 860px) { .listwrap { grid-template-columns: 1fr; padding: 20px 16px; } }
</style>
