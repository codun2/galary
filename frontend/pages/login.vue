<template>
  <div>
    <div class="topbar">
      <NuxtLink to="/" class="brand"><div class="logo"><i class="ti ti-car"></i></div><b>أوتو غاليري</b></NuxtLink>
    </div>
    <div class="auth">
      <div class="panel">
        <h1 class="center" style="font-size:22px; font-weight:800; margin-bottom:4px;">دخول المعرض</h1>
        <p class="center page-sub">أدخل بيانات حساب المعرض</p>
        <div v-if="error" class="err">{{ error }}</div>
        <form @submit.prevent="submit">
          <div class="frow"><label>البريد الإلكتروني</label><input v-model="email" type="email" required /></div>
          <div class="frow"><label>كلمة المرور</label><input v-model="password" type="password" required /></div>
          <button class="btn btn-accent btn-block btn-lg" :disabled="busy">{{ busy ? '...' : 'دخول' }}</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
const { login } = useAuth()
const email = ref('')
const password = ref('')
const busy = ref(false)
const error = ref('')

async function submit() {
  busy.value = true
  error.value = ''
  try {
    await login(email.value, password.value)
    await navigateTo('/admin')
  } catch (e) {
    error.value = e?.data?.message || 'بيانات الدخول غير صحيحة'
  } finally { busy.value = false }
}
</script>

<style scoped>
.topbar { background: var(--ink); padding: 15px 28px; }
.brand { display: flex; align-items: center; gap: 10px; color: #fff; }
.logo { width: 38px; height: 38px; border-radius: 9px; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.brand b { font-size: 18px; font-weight: 800; }
.auth { max-width: 420px; margin: 56px auto; padding: 0 16px; }
</style>
