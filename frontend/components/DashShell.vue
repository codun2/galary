<template>
  <div class="dash">
    <aside class="side">
      <div class="su">
        <div class="av"><i class="ti ti-car"></i></div>
        <div><b>{{ user?.name || 'المعرض' }}</b><small>حساب المعرض</small></div>
      </div>
      <nav>
        <NuxtLink to="/admin" :class="{ on: active === 'overview' }"><i class="ti ti-layout-dashboard"></i> نظرة عامة</NuxtLink>
        <NuxtLink to="/admin/add" :class="{ on: active === 'add' }"><i class="ti ti-plus"></i> إضافة سيارة</NuxtLink>
        <NuxtLink to="/admin/catalog" :class="{ on: active === 'catalog' }"><i class="ti ti-list-details"></i> الأنواع والموديلات</NuxtLink>
        <NuxtLink to="/admin/leads" :class="{ on: active === 'leads' }">
          <i class="ti ti-message"></i> الطلبات والاستفسارات
          <span v-if="newLeads" class="nbadge">{{ newLeads }}</span>
        </NuxtLink>
        <NuxtLink to="/admin/settings" :class="{ on: active === 'settings' }"><i class="ti ti-settings"></i> إعدادات المعرض</NuxtLink>
      </nav>
      <div class="side-foot">
        <NuxtLink to="/" class="sf"><i class="ti ti-external-link"></i> عرض الموقع</NuxtLink>
        <button class="sf" @click="logout"><i class="ti ti-logout"></i> خروج</button>
      </div>
    </aside>
    <main class="dmain"><slot /></main>
  </div>
</template>

<script setup>
defineProps({ active: { type: String, default: 'overview' } })
const { user, logout } = useAuth()
const { api } = useApi()

const newLeads = ref(0)
onMounted(async () => {
  try { newLeads.value = (await api('/dashboard/stats')).new_leads } catch {}
})
</script>

<style scoped>
.dash { display: grid; grid-template-columns: 235px minmax(0, 1fr); min-height: 100vh; }
.side { background: var(--ink); color: #C3C8D2; padding: 22px 0; display: flex; flex-direction: column; }
.su { padding: 0 20px 18px; display: flex; align-items: center; gap: 11px; border-bottom: 1px solid var(--ink-3); margin-bottom: 12px; }
.av { width: 42px; height: 42px; border-radius: 11px; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 21px; flex-shrink: 0; }
.su b { color: #fff; font-size: 14px; display: block; }
.su small { font-size: 11px; color: var(--muted); }
nav { display: flex; flex-direction: column; flex: 1; }
nav a { display: flex; align-items: center; gap: 11px; padding: 12px 20px; font-size: 14px; cursor: pointer; border-inline-start: 3px solid transparent; color: #C3C8D2; }
nav a:hover { background: var(--ink-2); color: #fff; }
nav a.on { background: var(--ink-2); color: #fff; border-inline-start-color: var(--accent); }
.nbadge { background: var(--accent); color: #fff; font-size: 11px; font-weight: 700; min-width: 18px; height: 18px; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 0 5px; margin-inline-start: auto; }
.side-foot { border-top: 1px solid var(--ink-3); padding-top: 12px; }
.sf { display: flex; align-items: center; gap: 11px; padding: 11px 20px; font-size: 13.5px; color: #8A909C; background: none; border: none; cursor: pointer; width: 100%; font-family: inherit; }
.sf:hover { color: #fff; }
.dmain { padding: 26px; background: var(--bg); }
@media (max-width: 820px) { .dash { grid-template-columns: 1fr; } .side { flex-direction: row; flex-wrap: wrap; padding: 12px; } nav { flex-direction: row; flex-wrap: wrap; } .side-foot { border: none; } }
</style>
