<template>
  <AuthenticatedLayout>
    <!-- Header dinamis berdasarkan peran koordinator -->
    <template #header-title>
      Dasbor Pembinaan Komcad — 
      <span v-if="role === 'kordinator_angkatan'">Angkatan {{ myProfile.angkatan }}</span>
      <span v-else>Matra TNI {{ myProfile.matra === 'AD' ? 'Darat' : (myProfile.matra === 'AL' ? 'Laut' : 'Udara') }}</span>
    </template>

    <!-- Kartu Statistik Widget -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Kekuatan Jajaran</p>
          <h3 class="text-2xl font-extrabold text-slate-800 mt-2">{{ stats.total_personel }}</h3>
        </div>
        <span class="text-3xl bg-blue-50 p-3 rounded-2xl">👥</span>
      </div>
      <div class="bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Akun Aktif</p>
          <h3 class="text-2xl font-extrabold text-green-600 mt-2">{{ stats.total_aktif }}</h3>
        </div>
        <span class="text-3xl bg-green-50 p-3 rounded-2xl">✓</span>
      </div>
      <div class="bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Terverifikasi OTP</p>
          <h3 class="text-2xl font-extrabold text-[#2563EB] mt-2">{{ stats.total_verified }}</h3>
        </div>
        <span class="text-3xl bg-blue-50 p-3 rounded-2xl">🔑</span>
      </div>
    </div>

    <!-- Pencarian & Filter Jajaran -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
        <input 
          type="text" 
          v-model="searchQuery" 
          @keyup.enter="handleSearch" 
          class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none w-full sm:w-64 focus:border-[#2563EB]" 
          placeholder="Cari nama, NIKC, atau NIK..."
        />
        
        <!-- Filter Matra hanya tampil untuk Koordinator Angkatan -->
        <select 
          v-if="role === 'kordinator_angkatan'"
          v-model="filterMatra" 
          @change="handleSearch" 
          class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-600 w-full sm:w-auto bg-white"
        >
          <option value="">Semua Matra</option>
          <option value="AD">TNI AD</option>
          <option value="AL">TNI AL</option>
          <option value="AU">TNI AU</option>
        </select>

        <!-- Filter Angkatan hanya tampil untuk Koordinator Matra -->
        <input 
          v-if="role === 'kordinator_matra'"
          type="text"
          v-model="filterAngkatan"
          @keyup.enter="handleSearch"
          class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none w-full sm:w-32 focus:border-[#2563EB]"
          placeholder="Thn Angkatan..."
        />
      </div>
    </div>

    <!-- Tabel Jajaran Anggota Terkoordinasi -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto w-full mb-6">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none whitespace-nowrap">
            <th class="p-4">Pasfoto</th>
            <th class="p-4">Identitas Resmi</th>
            <th class="p-4" v-if="role === 'kordinator_angkatan'">Matra</th>
            <th class="p-4" v-if="role === 'kordinator_matra'">Angkatan</th>
            <th class="p-4">OTP Verification</th>
            <th class="p-4">Catatan Pembinaan</th>
            <th class="p-4 text-right">Opsi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
          <tr v-for="personel in jajaran.data" :key="personel.id" class="hover:bg-slate-50/30 transition">
            <td class="p-4 whitespace-nowrap">
              <img :src="personel.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" class="w-9 h-12 object-cover rounded-lg bg-slate-100 shadow-sm border border-[#E2E8F0]" />
            </td>
            <td class="p-4 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <span class="font-bold text-slate-800">{{ personel.full_name }}</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-wide select-none shrink-0">
                  {{ personel.pangkat || '-' }}
                </span>
              </div>
              <div class="text-xs text-slate-400 mt-1 flex items-center gap-1.5 whitespace-nowrap">
                <span>NIK: <span class="font-medium text-slate-600">{{ personel.nik }}</span></span>
                <span class="text-slate-300">|</span>
                <span>
                  NIKC: 
                  <span v-if="personel.nikc" class="font-mono font-bold text-[#2563EB] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100/60 text-[11px]">
                    {{ personel.nikc }}
                  </span>
                  <span v-else class="font-bold text-slate-300 px-1">-</span>
                </span>
              </div>
            </td>
            <td class="p-4 whitespace-nowrap" v-if="role === 'kordinator_angkatan'">
              <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-100/40 inline-block whitespace-nowrap">
                TNI {{ personel.matra }}
              </span>
            </td>
            <td class="p-4 font-semibold text-slate-700 whitespace-nowrap" v-if="role === 'kordinator_matra'">{{ personel.angkatan }}</td>
            <td class="p-4 whitespace-nowrap">
              <span :class="personel.face_verified ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100'" class="px-2.5 py-0.5 rounded text-[11px] font-semibold inline-block whitespace-nowrap">
                {{ personel.face_verified ? 'OTP Verified' : 'Belum Verifikasi OTP' }}
              </span>
            </td>
            <td class="p-4">
              <div class="max-w-[200px] truncate text-xs text-slate-400 italic">
                {{ personel.catatan_pembinaan || 'Belum ada catatan pembinaan.' }}
              </div>
            </td>
            <td class="p-4 text-right">
              <button 
                @click="view360Profil(personel)" 
                class="text-xs font-bold text-[#2563EB] hover:underline bg-[#2563EB]/5 hover:bg-[#2563EB]/10 px-3 py-1.5 rounded-xl border border-transparent hover:border-blue-100 transition cursor-pointer"
              >
                Tinjau & Bina
              </button>
            </td>
          </tr>
          <tr v-if="jajaran.data.length === 0">
            <td colspan="7" class="p-12 text-center text-slate-400 text-sm">Tidak ada record data personel di bawah wewenang koordinasi Anda.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginator Jajaran -->
    <div v-if="jajaran.links.length > 3" class="flex justify-center gap-1 select-none">
      <button 
        v-for="(link, i) in jajaran.links" 
        :key="i"
        @click="navigatePage(link.url)"
        :disabled="!link.url || link.active"
        :class="[
          link.active ? 'bg-[#2563EB] text-white font-bold' : 'bg-white hover:bg-slate-50 text-slate-700',
          !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
        ]"
        class="px-3 py-1.5 border border-[#E2E8F0] rounded-lg text-xs transition"
        v-html="link.label"
      />
    </div>

    <!-- SLIDE-OVER MODAL 360° PROFIL & PEMBINAAN -->
    <div v-if="slideOpen" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-xs transition-opacity" @click="slideOpen = false"></div>
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
          <div class="pointer-events-auto w-screen max-w-xl border-l border-[#E2E8F0] bg-white shadow-2xl flex flex-col">
            
            <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/50 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <img 
                  :src="selectedPersonel.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(selectedPersonel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" 
                  class="w-10 h-10 object-cover rounded-xl bg-slate-100 border border-[#E2E8F0] shadow-sm shrink-0" 
                />
                <div>
                  <h3 class="text-sm font-bold text-slate-800">Evaluasi & Profil {{ selectedPersonel.full_name }}</h3>
                  <p class="text-xs text-slate-400 mt-0.5">NIKC: {{ selectedPersonel.nikc || '-' }}</p>
                </div>
              </div>
              <button @click="slideOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            <div class="px-6 border-b border-[#E2E8F0] flex gap-5 text-xs font-bold select-none bg-white">
              <button @click="currentTab = 'profile'" :class="currentTab === 'profile' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5' : 'text-slate-400 py-4.5 hover:text-slate-600'">IDENTITAS & PEMBINAAN</button>
              <button @click="currentTab = 'sinyalmen'" :class="currentTab === 'sinyalmen' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5' : 'text-slate-400 py-4.5 hover:text-slate-600'">SINYALMEN FISIK</button>
              <button @click="currentTab = 'activities'" :class="currentTab === 'activities' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5' : 'text-slate-400 py-4.5 hover:text-slate-600'">RESPONS KEGIATAN ({{ selectedPersonel.broadcast_responses?.length || 0 }})</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/40 space-y-5">
              
              <!-- Tab 1: Profil & Form Pembinaan -->
              <div v-if="currentTab === 'profile'" class="space-y-4">
                <div class="flex gap-4 items-start bg-white p-4 border border-[#E2E8F0] rounded-xl shadow-xs">
                  <img :src="selectedPersonel.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(selectedPersonel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" class="w-16 h-20 object-cover rounded-lg border border-[#E2E8F0] bg-slate-50" />
                  <div class="space-y-1 text-xs">
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      {{ selectedPersonel.full_name }}
                      <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-[#2563EB] border border-blue-100/60">{{ selectedPersonel.pangkat || '-' }}</span>
                    </h4>
                    <p class="text-slate-400">Komponen Cadangan Matra {{ selectedPersonel.matra }} (Angkatan {{ selectedPersonel.angkatan }})</p>
                    <p class="text-slate-400">Email: {{ selectedPersonel.user?.email || '-' }} | WA: {{ selectedPersonel.phone_number }}</p>
                  </div>
                </div>

                <!-- Formulir Catatan Pembinaan Koordinator -->
                <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 text-xs space-y-3">
                  <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[10px] text-[#2563EB]">FORMULIR EVALUASI & CATATAN PEMBINAAN</h4>
                  <p class="text-[10px] text-slate-400">Catatan ini hanya dapat dilihat oleh Koordinator dan Administrator untuk keperluan evaluasi pembinaan berkala.</p>
                  <form @submit.prevent="submitCoachingNotes" class="space-y-3">
                    <textarea 
                      v-model="notesForm.notes" 
                      rows="5" 
                      class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] leading-relaxed text-slate-700" 
                      placeholder="Tuliskan evaluasi sikap, pelanggaran, maupun prestasi penugasan anggota di sini..."
                    ></textarea>
                    <div class="flex justify-end">
                      <button 
                        type="submit" 
                        :disabled="notesForm.processing"
                        class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-50 text-white font-bold text-xs rounded-lg shadow-sm cursor-pointer transition"
                      >
                        Simpan Catatan
                      </button>
                    </div>
                  </form>
                </div>

                <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 text-xs space-y-3">
                  <h4 class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Identitas & Domisili</h4>
                  <div class="grid grid-cols-2 gap-3.5">
                    <div><p class="text-slate-400">NIK</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.nik }}</p></div>
                    <div><p class="text-slate-400">Tempat, Tanggal Lahir</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.pob }}, {{ formatDate(selectedPersonel.dob) }}</p></div>
                    <div><p class="text-slate-400">Jenis Kelamin</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p></div>
                    <div><p class="text-slate-400">Sumber Rekrutmen</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.sumber_rekrutmen || '-' }}</p></div>
                    <div class="col-span-2"><p class="text-slate-400">Alamat Rumah</p><p class="font-semibold text-slate-700 mt-0.5 leading-relaxed">{{ selectedPersonel.address }}, KEL. {{ selectedPersonel.village }}, KEC. {{ selectedPersonel.district }}, {{ selectedPersonel.city }}, {{ selectedPersonel.province }}</p></div>
                  </div>
                </div>
              </div>

              <!-- Tab 2: Sinyalmen Fisik -->
              <div v-if="currentTab === 'sinyalmen'">
                <div v-if="selectedPersonel.sinyalmen" class="bg-white border border-[#E2E8F0] rounded-xl p-5 text-xs grid grid-cols-2 gap-4">
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Tinggi Badan</p><p class="font-bold text-slate-800 text-sm mt-0.5">{{ selectedPersonel.sinyalmen.tinggi_badan }} cm</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Berat Badan</p><p class="font-bold text-slate-800 text-sm mt-0.5">{{ selectedPersonel.sinyalmen.berat_badan }} kg</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Golongan Darah</p><p class="font-bold text-slate-800 text-sm mt-0.5">{{ selectedPersonel.sinyalmen.golongan_darah || '-' }}</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Bentuk Rambut</p><p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.rambut || '-' }}</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Bola Mata</p><p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.mata || '-' }}</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Ciri Khas Khusus</p><p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.ciri_khas || '-' }}</p></div>
                  <div class="col-span-2 p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Kondisi Cacat Tubuh</p><p class="font-bold text-red-600 mt-0.5">{{ selectedPersonel.sinyalmen.cacat_tubuh || 'Tidak Ada / Sehat Walafiat' }}</p></div>
                </div>
                <div v-else class="text-center p-8 bg-white border border-[#E2E8F0] rounded-xl text-slate-400 text-xs font-medium">
                  Anggota belum melengkapi data isian sinyalmen fisik.
                </div>
              </div>

              <!-- Tab 3: Riwayat Broadcast Responses -->
              <div v-if="currentTab === 'activities'" class="space-y-3">
                <div v-for="response in selectedPersonel.broadcast_responses" :key="response.id" class="p-4 bg-white border border-[#E2E8F0] rounded-xl flex items-center justify-between gap-4 shadow-2xs">
                  <div class="text-xs space-y-1 max-w-sm">
                    <p class="font-bold text-slate-800 truncate">{{ response.broadcast?.title || 'Mobilisasi Komcad' }}</p>
                    <p class="text-slate-400 text-[11px]">Dikonfirmasi pada: {{ formatFullDate(response.created_at) }}</p>
                    <p v-if="response.notes" class="text-slate-500 italic mt-1 bg-slate-50 p-2 rounded border border-dashed border-[#E2E8F0]">"{{ response.notes }}"</p>
                  </div>
                  <span :class="response.status === 'HADIR' ? 'bg-green-50 text-green-700 border-green-200' : response.status === 'IZIN' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200'" class="px-2.5 py-0.5 rounded-lg font-bold border text-[10px] select-none shrink-0">
                    {{ response.status }}
                  </span>
                </div>

                <div v-if="!selectedPersonel.broadcast_responses || selectedPersonel.broadcast_responses.length === 0" class="text-center p-12 bg-white border border-[#E2E8F0] rounded-xl text-slate-400 text-xs font-medium">
                  Belum ada rekam jejak presensi kegiatan dari anggota ini.
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({
  myProfile: Object,
  role: String,
  jajaran: Object,
  stats: Object,
  distribution: Object,
  filters: Object
});

const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

const searchQuery = ref(props.filters.search || '');
const filterMatra = ref(props.filters.matra || '');
const filterAngkatan = ref(props.filters.angkatan || '');

const slideOpen = ref(false);
const currentTab = ref('profile');
const selectedPersonel = ref(null);

const notesForm = useForm({
  notes: ''
});

const handleSearch = () => {
  router.get(route('kordinator.dashboard'), {
    search: searchQuery.value,
    matra: filterMatra.value,
    angkatan: filterAngkatan.value
  }, {
    preserveState: true,
    replace: true
  });
};

const navigatePage = (url) => {
  if (!url) return;
  router.get(url, {}, { preserveState: true });
};

const view360Profil = (personel) => {
  selectedPersonel.value = personel;
  notesForm.notes = personel.catatan_pembinaan || '';
  currentTab.value = 'profile';
  slideOpen.value = true;
};

const submitCoachingNotes = () => {
  notesForm.post(route('kordinator.personel.catatan', selectedPersonel.value.uuid), {
    preserveScroll: true,
    onBefore: () => showLoadingProgress('Menyimpan Catatan Pembinaan...'),
    onSuccess: () => {
      closeLoading();
      // Perbarui catatan lokal untuk representasi visual instan
      selectedPersonel.value.catatan_pembinaan = notesForm.notes;
      alertSuccess('Tersimpan', 'Catatan pembinaan personel berhasil direkam.');
    },
    onError: (errors) => {
      closeLoading();
      const errMsg = errors.error || 'Gagal menyimpan catatan evaluasi.';
      alertError('Gagal Menyimpan', errMsg);
    }
  });
};

const formatDate = (d) => {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatFullDate = (d) => {
  if (!d) return '-';
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
};
</script>

<style scoped>
/* Animasi modal slide-over */
.pointer-events-auto {
  animation: slideIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideIn {
  from { transform: translateX(100%); }
  to { transform: translateX(0); }
}
</style>
