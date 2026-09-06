<template>
  <DashShell active="overview">
    <div class="rowb">
      <div><h1 class="page-title">نظرة عامة</h1><p class="page-sub" style="margin:0;">إدارة معرضك</p></div>
      <NuxtLink to="/admin/add" class="btn btn-accent"><i class="ti ti-plus"></i> إضافة سيارة</NuxtLink>
    </div>

    <div v-if="stats" class="stats">
      <div class="stat"><div class="n">{{ stats.vehicles }}</div><div class="l">سيارة معروضة</div></div>
      <div class="stat"><div class="n">{{ stats.available }}</div><div class="l">متوفرة</div></div>
      <div class="stat"><div class="n">{{ stats.views }}</div><div class="l">مشاهدة</div></div>
      <div class="stat" :class="{ hot: stats.new_leads }"><div class="n">{{ stats.new_leads }}</div><div class="l">استفسار جديد</div></div>
    </div>

    <h3 class="sec-t">سياراتي</h3>
    <div v-if="pending" class="empty">جارِ التحميل…</div>
    <div v-else-if="!vehicles.length" class="empty">لا توجد سيارات. <NuxtLink to="/admin/add" class="link">أضف أول سيارة</NuxtLink></div>
    <table v-else>
      <thead><tr><th>السيارة</th><th>السنة</th><th>السعر</th><th>الحالة</th><th>مميّزة</th><th>مشاهدات</th><th></th></tr></thead>
      <tbody>
        <tr v-for="v in vehicles" :key="v.id">
          <td>{{ v.make }} {{ v.model }}</td>
          <td>{{ v.year }}</td>
          <td>${{ Number(v.price).toLocaleString('en-US') }}</td>
          <td><span class="pill" :class="ST[v.status]?.[1]">{{ ST[v.status]?.[0] }}</span></td>
          <td><i class="ti" :class="v.is_featured ? 'ti-star-filled' : 'ti-star'" :style="{ color: v.is_featured ? 'var(--gold)' : 'var(--line)' }"></i></td>
          <td>{{ v.views_count }}</td>
          <td style="white-space:nowrap;">
            <NuxtLink :to="`/car/${v.id}`" class="act" title="عرض"><i class="ti ti-eye"></i></NuxtLink>
            <NuxtLink :to="`/admin/edit/${v.id}`" class="act" title="تعديل"><i class="ti ti-edit"></i></NuxtLink>
            <button class="act danger" title="حذف" @click="remove(v)"><i class="ti ti-trash"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
  </DashShell>
</template>

<script setup>
const { api } = useApi()
const { fetchMe } = useAuth()

const stats = ref(null)
const vehicles = ref([])
const pending = ref(true)

const ST = { available: ['متوفرة', 'pill-g'], reserved: ['محجوزة', 'pill-y'], sold: ['مباعة', 'pill-r'], hidden: ['مخفية', 'pill-gray'] }

async function load() {
  ;[stats.value, vehicles.value] = await Promise.all([api('/dashboard/stats'), api('/dashboard/vehicles')])
  pending.value = false
}
async function remove(v) {
  if (!confirm(`حذف ${v.make} ${v.model}؟`)) return
  await api(`/dashboard/vehicles/${v.id}`, { method: 'DELETE' })
  await load()
}

onMounted(async () => {
  const me = await fetchMe()
  if (!me) return navigateTo('/login')
  await load()
})
</script>

<style scoped>
.rowb { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
.stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.stat { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 16px; }
.stat.hot { border-color: var(--accent); background: #FEF6F5; }
.stat .n { font-size: 26px; font-weight: 800; }
.stat .l { font-size: 12.5px; color: var(--muted); }
@media (max-width: 820px) { .stats { grid-template-columns: 1fr 1fr; } }
</style>
