<template>
  <DashShell active="leads">
    <h1 class="page-title">الطلبات والاستفسارات</h1>
    <p class="page-sub">استفسارات الزبائن من الموقع</p>

    <div class="tabs">
      <button class="tab" :class="{ on: !filter }" @click="filter = ''; load()">الكل</button>
      <button class="tab" :class="{ on: filter === 'new' }" @click="filter = 'new'; load()">جديدة</button>
      <button class="tab" :class="{ on: filter === 'replied' }" @click="filter = 'replied'; load()">تم الرد</button>
    </div>

    <div v-if="pending" class="empty">جارِ التحميل…</div>
    <div v-else-if="!leads.length" class="empty">لا توجد استفسارات</div>
    <table v-else>
      <thead><tr><th>الاسم</th><th>الهاتف</th><th>بخصوص</th><th>الرسالة</th><th>التاريخ</th><th>الحالة</th><th></th></tr></thead>
      <tbody>
        <tr v-for="l in leads" :key="l.id">
          <td>{{ l.name }}</td>
          <td dir="ltr" style="text-align:right;">{{ l.phone }}</td>
          <td>{{ l.vehicle ? `${l.vehicle.make} ${l.vehicle.model} ${l.vehicle.year}` : (l.subject || '—') }}</td>
          <td style="max-width:240px;">{{ l.message || '—' }}</td>
          <td style="color:var(--muted); font-size:12.5px;">{{ l.created_at?.slice(0, 10) }}</td>
          <td><span class="pill" :class="ST[l.status]?.[1]">{{ ST[l.status]?.[0] }}</span></td>
          <td style="white-space:nowrap;">
            <a :href="`tel:${l.phone}`" class="act" title="اتصال"><i class="ti ti-phone"></i></a>
            <a :href="`https://wa.me/${l.phone.replace(/\D/g, '')}`" target="_blank" class="act" title="واتساب" style="color:var(--green)"><i class="ti ti-brand-whatsapp"></i></a>
            <button v-if="l.status === 'new'" class="act" title="تعليم كمُجاب" @click="mark(l, 'replied')"><i class="ti ti-check"></i></button>
            <button v-else-if="l.status === 'replied'" class="act" title="إغلاق" @click="mark(l, 'closed')"><i class="ti ti-archive"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
  </DashShell>
</template>

<script setup>
const { api } = useApi()
const { fetchMe } = useAuth()

const leads = ref([])
const pending = ref(true)
const filter = ref('')

const ST = { new: ['جديد', 'pill-y'], replied: ['تم الرد', 'pill-g'], closed: ['مغلق', 'pill-gray'] }

async function load() {
  pending.value = true
  leads.value = await api('/dashboard/leads' + (filter.value ? `?status=${filter.value}` : ''))
  pending.value = false
}
async function mark(l, status) {
  await api(`/dashboard/leads/${l.id}/status`, { method: 'POST', body: { status } })
  await load()
}

onMounted(async () => {
  const me = await fetchMe()
  if (!me) return navigateTo('/login')
  await load()
})
</script>

<style scoped>
.tabs { display: flex; gap: 8px; margin-bottom: 18px; }
.tab { padding: 8px 18px; border-radius: 22px; font-size: 13.5px; cursor: pointer; border: 1px solid var(--line); background: #fff; font-family: inherit; }
.tab.on { background: var(--ink); color: #fff; border-color: var(--ink); }
</style>
