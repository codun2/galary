<template>
  <DashShell active="catalog">
    <h1 class="page-title">الأنواع والموديلات</h1>
    <p class="page-sub">حدّد أنواع السيارات (الماركات) والموديلات التابعة لكل نوع — تظهر كقوائم عند إضافة سيارة</p>

    <!-- add make -->
    <div class="panel" style="max-width:520px; margin-bottom:20px;">
      <h3 class="sec-t">إضافة نوع جديد</h3>
      <form style="display:flex; gap:10px;" @submit.prevent="addMake">
        <input v-model="newMake" placeholder="مثال: كيا" style="flex:1;" />
        <button class="btn btn-accent" :disabled="busy || !newMake.trim()"><i class="ti ti-plus"></i> إضافة</button>
      </form>
    </div>

    <div v-if="pending" class="empty">جارِ التحميل…</div>
    <div v-else-if="!makes.length" class="empty">لا توجد أنواع بعد — أضف أول نوع فوق</div>

    <div class="mgrid">
      <div v-for="m in makes" :key="m.id" class="mcard">
        <div class="mhead">
          <b><i class="ti ti-car"></i> {{ m.name }}</b>
          <button class="act danger" title="حذف النوع" @click="delMake(m)"><i class="ti ti-trash"></i></button>
        </div>
        <div class="models">
          <span v-for="mo in m.models" :key="mo.id" class="mo">
            {{ mo.name }}
            <i class="ti ti-x" @click="delModel(m, mo)"></i>
          </span>
          <span v-if="!m.models.length" class="none">لا موديلات بعد</span>
        </div>
        <form class="addmo" @submit.prevent="addModel(m)">
          <input v-model="m._new" placeholder="موديل جديد…" />
          <button class="btn btn-out" :disabled="!m._new?.trim()"><i class="ti ti-plus"></i></button>
        </form>
      </div>
    </div>

    <!-- شريط شعارات الماركات -->
    <div class="panel" style="margin-top:28px;">
      <h3 class="sec-t"><i class="ti ti-carousel-horizontal" style="color:var(--accent);"></i> شريط شعارات الماركات (يظهر بالصفحة الرئيسية)</h3>
      <p class="page-sub" style="margin-bottom:16px;">أضف شعارات الشركات العالمية — صورة مرفوعة أو رابط شعار جاهز.</p>

      <div class="blist">
        <div v-for="b in brands" :key="b.id" class="bitem">
          <img v-if="b.logo_path || b.logo_url" :src="b.logo_path ? fileUrl(b.logo_path) : b.logo_url" :alt="b.name" />
          <span>{{ b.name }}</span>
          <button class="bx" @click="delBrand(b)" title="حذف"><i class="ti ti-x"></i></button>
        </div>
        <span v-if="!brands.length" class="none">لا شعارات بعد</span>
      </div>

      <div class="baddrow">
        <input v-model="nb.name" placeholder="اسم الماركة (Toyota…)" style="flex:1;" />
        <label class="btn btn-out" style="white-space:nowrap;">
          <i class="ti" :class="nb.logo_path ? 'ti-check' : 'ti-upload'"></i> {{ nb.logo_path ? 'تم الرفع' : 'رفع شعار' }}
          <input type="file" hidden accept="image/*,.svg" @change="uploadLogo" />
        </label>
        <span style="color:var(--muted); font-size:12px;">أو</span>
        <input v-model="nb.logo_url" placeholder="رابط الشعار https://…" dir="ltr" style="flex:1.4;" />
        <button class="btn btn-accent" @click="addBrand" :disabled="!nb.name.trim()"><i class="ti ti-plus"></i> إضافة</button>
      </div>
    </div>
  </DashShell>
</template>

<script setup>
const { api, fileUrl } = useApi()
const { fetchMe } = useAuth()

const makes = ref([])
const brands = ref([])
const nb = reactive({ name: '', logo_path: '', logo_url: '' })
const newMake = ref('')
const pending = ref(true)
const busy = ref(false)

async function load() {
  ;[makes.value, brands.value] = await Promise.all([
    api('/dashboard/catalog').then((d) => d.map((m) => ({ ...m, _new: '' }))),
    api('/dashboard/brands'),
  ])
  pending.value = false
}

async function uploadLogo(e) {
  const file = e.target.files?.[0]; if (!file) return
  const fd = new FormData(); fd.append('file', file)
  const r = await api('/uploads', { method: 'POST', body: fd })
  nb.logo_path = r.path
  nb.logo_url = ''
}
async function addBrand() {
  await api('/dashboard/brands', { method: 'POST', body: { ...nb } })
  Object.assign(nb, { name: '', logo_path: '', logo_url: '' })
  await load()
}
async function delBrand(b) {
  if (!confirm(`حذف شعار ${b.name}؟`)) return
  await api(`/dashboard/brands/${b.id}`, { method: 'DELETE' })
  await load()
}
async function addMake() {
  busy.value = true
  try { await api('/dashboard/makes', { method: 'POST', body: { name: newMake.value.trim() } }); newMake.value = ''; await load() }
  catch (e) { alert(e?.data?.message || 'النوع موجود مسبقاً') }
  finally { busy.value = false }
}
async function delMake(m) {
  if (!confirm(`حذف "${m.name}" وكل موديلاته؟`)) return
  await api(`/dashboard/makes/${m.id}`, { method: 'DELETE' })
  await load()
}
async function addModel(m) {
  await api(`/dashboard/makes/${m.id}/models`, { method: 'POST', body: { name: m._new.trim() } })
  m._new = ''
  await load()
}
async function delModel(m, mo) {
  await api(`/dashboard/models/${mo.id}`, { method: 'DELETE' })
  await load()
}

onMounted(async () => {
  const me = await fetchMe()
  if (!me) return navigateTo('/login')
  await load()
})
</script>

<style scoped>
.mgrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
.mcard { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 18px; }
.mhead { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.mhead b { font-size: 16px; display: flex; align-items: center; gap: 7px; }
.mhead i { color: var(--accent); }
.models { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; min-height: 30px; }
.mo { background: var(--bg); border: 1px solid var(--line); font-size: 13px; padding: 4px 11px; border-radius: 20px; display: inline-flex; align-items: center; gap: 6px; }
.mo i { font-size: 13px; color: var(--muted); cursor: pointer; }
.mo i:hover { color: var(--accent); }
.none { font-size: 12.5px; color: var(--muted); }
.addmo { display: flex; gap: 8px; }
.addmo input { flex: 1; padding: 8px 12px; font-size: 13px; }
.blist { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; min-height: 40px; }
.bitem { display: flex; align-items: center; gap: 9px; background: var(--bg); border: 1px solid var(--line); border-radius: 10px; padding: 8px 13px; font-size: 13.5px; font-weight: 600; color: #4A4F5A; }
.bitem img { height: 26px; width: auto; }
.bx { border: none; background: none; color: var(--muted); cursor: pointer; font-size: 14px; padding: 0; }
.bx:hover { color: var(--accent); }
.baddrow { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
</style>
