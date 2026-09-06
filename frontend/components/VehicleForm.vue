<template>
  <div style="max-width:860px;">
    <h1 class="page-title">{{ isEdit ? 'تعديل السيارة' : 'إضافة سيارة' }}</h1>
    <p class="page-sub">بيانات السيارة كما ستظهر للزبائن في الموقع</p>

    <div v-if="error" class="err">{{ error }}</div>

    <form @submit.prevent="save">
      <div class="panel">
        <h3 class="sec-t">بيانات السيارة</h3>
        <div class="g3">
          <div class="frow"><label>الماركة</label><input v-model="f.make" placeholder="مرسيدس" required /></div>
          <div class="frow"><label>الموديل</label><input v-model="f.model" placeholder="GLC 300" required /></div>
          <div class="frow"><label>السنة</label><input v-model.number="f.year" type="number" required /></div>
          <div class="frow"><label>الممشى (كم)</label><input v-model.number="f.mileage" type="number" required /></div>
          <div class="frow"><label>الوقود</label>
            <select v-model="f.fuel"><option value="petrol">بنزين</option><option value="diesel">ديزل</option><option value="hybrid">هايبرد</option><option value="electric">كهرباء</option></select>
          </div>
          <div class="frow"><label>ناقل الحركة</label>
            <select v-model="f.transmission"><option value="automatic">أوتوماتيك</option><option value="manual">عادي</option></select>
          </div>
          <div class="frow"><label>اللون</label><input v-model="f.color" placeholder="أسود" /></div>
          <div class="frow"><label>الدفع</label><input v-model="f.drivetrain" placeholder="دفع رباعي" /></div>
          <div class="frow"><label>السعر ($)</label><input v-model.number="f.price" type="number" required /></div>
        </div>
        <div class="frow"><label>الوصف</label><textarea v-model="f.description" rows="3" placeholder="سيارة بحالة ممتازة…"></textarea></div>
        <div class="frow" style="margin-bottom:0;"><label>المميزات (افصل بينها بفاصلة)</label><input v-model="featuresText" placeholder="فتحة سقف، مقاعد جلد، كاميرا خلفية" /></div>
      </div>

      <div class="panel">
        <h3 class="sec-t">الحالة والعرض</h3>
        <div class="g2">
          <div class="frow"><label>الحالة</label>
            <select v-model="f.status"><option value="available">متوفرة</option><option value="reserved">محجوزة</option><option value="sold">مباعة</option><option value="hidden">مخفية</option></select>
          </div>
          <div class="frow" style="display:flex; align-items:flex-end;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0;">
              <input type="checkbox" v-model="f.is_featured" style="width:auto;" /> سيارة مميّزة (تظهر أولاً)
            </label>
          </div>
        </div>
      </div>

      <div class="panel">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <h3 class="sec-t" style="margin:0;">صور السيارة</h3>
          <span style="font-size:12px; color:var(--muted);">{{ media.length }} / 12</span>
        </div>
        <div v-if="media.length" class="gwrap">
          <div class="gmain"><img :src="fileUrl(media[0])" alt="" /><span class="gtag"><i class="ti ti-star-filled"></i> الرئيسية</span></div>
          <div class="gstrip">
            <div v-for="(m, i) in media" :key="m" class="gthumb">
              <img :src="fileUrl(m)" alt="" />
              <div class="gacts">
                <button v-if="i !== 0" type="button" @click="makeCover(i)" title="اجعلها رئيسية"><i class="ti ti-star"></i></button>
                <button type="button" @click="media.splice(i, 1)" title="حذف"><i class="ti ti-trash"></i></button>
              </div>
            </div>
          </div>
        </div>
        <label class="upbox" style="margin-top:12px;">
          <i class="ti" :class="uploading ? 'ti-loader' : 'ti-photo-plus'"></i>
          {{ uploading ? 'جارِ الرفع…' : 'اضغط لرفع صور (أكثر من صورة معاً)' }}
          <input type="file" hidden multiple accept="image/*" @change="upload" :disabled="uploading" />
        </label>
      </div>

      <div style="display:flex; justify-content:space-between; margin-top:16px;">
        <NuxtLink to="/admin" class="btn btn-out">إلغاء</NuxtLink>
        <button class="btn btn-accent btn-lg" :disabled="busy">{{ busy ? '...' : (isEdit ? 'حفظ التعديلات' : 'نشر السيارة') }}</button>
      </div>
    </form>
  </div>
</template>

<script setup>
const props = defineProps({ vehicleId: { type: [String, Number], default: null } })
const { api, fileUrl } = useApi()
const { fetchMe } = useAuth()

const isEdit = computed(() => !!props.vehicleId)
const media = ref([])
const busy = ref(false)
const uploading = ref(false)
const error = ref('')
const featuresText = ref('')

const f = reactive({
  make: '', model: '', year: 2023, mileage: 0, fuel: 'petrol', transmission: 'automatic',
  color: '', drivetrain: '', price: null, description: '', status: 'available', is_featured: false,
})

function makeCover(i) {
  const [img] = media.value.splice(i, 1)
  media.value.unshift(img)
}

async function upload(e) {
  const files = Array.from(e.target.files)
  if (!files.length) return
  uploading.value = true
  try {
    for (const file of files) {
      if (media.value.length >= 12) break
      const fd = new FormData()
      fd.append('file', file)
      const r = await api('/uploads', { method: 'POST', body: fd })
      media.value.push(r.path)
    }
  } catch { error.value = 'تعذّر رفع بعض الصور' }
  finally { uploading.value = false; e.target.value = '' }
}

async function save() {
  busy.value = true
  error.value = ''
  try {
    const body = { ...f, media: media.value }
    body.features = featuresText.value.split('،').join(',').split(',').map((s) => s.trim()).filter(Boolean)
    if (isEdit.value) await api(`/dashboard/vehicles/${props.vehicleId}`, { method: 'PUT', body })
    else await api('/dashboard/vehicles', { method: 'POST', body })
    await navigateTo('/admin')
  } catch (e) {
    error.value = e?.data?.message || 'تعذّر الحفظ — راجع الحقول'
  } finally { busy.value = false }
}

onMounted(async () => {
  const me = await fetchMe()
  if (!me) return navigateTo('/login')
  if (isEdit.value) {
    const v = await api(`/dashboard/vehicles/${props.vehicleId}`).catch(() => null)
    if (v) {
      Object.keys(f).forEach((k) => {
        if (v[k] !== null && v[k] !== undefined) f[k] = k === 'is_featured' ? Boolean(v[k]) : v[k]
      })
      featuresText.value = (v.features || []).join('، ')
      media.value = (v.media || []).map((m) => m.file_path)
    }
  }
})
</script>

<style scoped>
.gwrap { margin-bottom: 4px; }
.gmain { position: relative; height: 250px; border-radius: 12px; overflow: hidden; background: #E3E6EB; }
.gmain img { width: 100%; height: 100%; object-fit: cover; }
.gtag { position: absolute; top: 10px; right: 10px; background: var(--accent); color: #fff; font-size: 11px; font-weight: 700; padding: 4px 11px; border-radius: 20px; display: flex; align-items: center; gap: 4px; }
.gstrip { display: flex; gap: 10px; margin-top: 10px; overflow-x: auto; padding-bottom: 4px; }
.gthumb { position: relative; flex: 0 0 100px; height: 72px; border-radius: 9px; overflow: hidden; }
.gthumb img { width: 100%; height: 100%; object-fit: cover; }
.gacts { position: absolute; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; gap: 8px; opacity: 0; transition: .15s; }
.gthumb:hover .gacts { opacity: 1; }
.gacts button { width: 28px; height: 28px; border-radius: 7px; border: none; background: #fff; cursor: pointer; color: var(--ink); }
</style>
