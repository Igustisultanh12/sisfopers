<template>
  <AuthenticatedLayout>
    <template #header-title>Hasil Respon Personel</template>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 font-sans space-y-6">
      <!-- Back Link & Header -->
      <div class="flex items-center justify-between">
        <Link 
          :href="getIndexRoute()" 
          class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
          Kembali ke Daftar Formulir
        </Link>
      </div>

      <!-- Form Information Summary Header -->
      <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-2">
              <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-blue-50 text-blue-700 border border-blue-200">
                {{ form.category }}
              </span>
              <span 
                class="px-2.5 py-0.5 rounded-md text-[10px] font-bold"
                :class="form.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
              >
                {{ form.is_active ? 'TERBUKA' : 'DITUTUP' }}
              </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">{{ form.title }}</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-3xl">{{ form.description || 'Tidak ada deskripsi tambahan.' }}</p>
          </div>

          <!-- Quick Stats Pills -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 shrink-0">
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 text-center">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Respon</p>
              <p class="text-lg font-extrabold text-slate-800 mt-0.5">{{ stats.total || 0 }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-200/80 rounded-2xl p-3 text-center">
              <p class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Terkirim</p>
              <p class="text-lg font-extrabold text-blue-800 mt-0.5">{{ stats.submitted || 0 }}</p>
            </div>
            <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-3 text-center">
              <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Lolos / Diterima</p>
              <p class="text-lg font-extrabold text-emerald-800 mt-0.5">{{ stats.accepted || 0 }}</p>
            </div>
            <div class="bg-rose-50 border border-rose-200/80 rounded-2xl p-3 text-center">
              <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider">Ditolak</p>
              <p class="text-lg font-extrabold text-rose-800 mt-0.5">{{ stats.rejected || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:w-80">
          <input 
            v-model="search" 
            @input="handleSearch"
            type="text" 
            placeholder="Cari nama atau NIKC/NRP..." 
            class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <select 
            v-model="statusFilter" 
            @change="applyFilter"
            class="text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="ALL">Semua Status Seleksi</option>
            <option value="SUBMITTED">Terkirim (Menunggu Verifikasi)</option>
            <option value="VERIFIED">Telah Diverifikasi</option>
            <option value="ACCEPTED">Lolos / Diterima</option>
            <option value="REJECTED">Ditolak / Tidak Lolos</option>
          </select>
        </div>
      </div>

      <!-- Responses Table (Nama and NIKC/NRP clickable) -->
      <div class="bg-white border border-[#E2E8F0] rounded-3xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-extrabold text-slate-600 uppercase tracking-wider">
              <tr>
                <th class="px-6 py-4 w-12 text-center">No</th>
                <th class="px-6 py-4">Nama Personel & Pangkat</th>
                <th class="px-6 py-4">NIKC / NRP</th>
                <th class="px-6 py-4">Matra / Angkatan</th>
                <th class="px-6 py-4">Tanggal Pengiriman</th>
                <th class="px-6 py-4 text-center">Status</th>
                <th class="px-6 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="(resp, index) in responses.data" 
                :key="resp.id"
                class="hover:bg-blue-50/40 transition cursor-pointer group"
                @click="openDetail(resp)"
              >
                <td class="px-6 py-4 text-center font-medium text-slate-400">
                  {{ (responses.current_page - 1) * responses.per_page + index + 1 }}
                </td>

                <!-- Nama Personel (Clickable) -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="resp.personel?.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(resp.personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(resp.personel?.full_name || 'P')}&background=e2e8f0&color=334155`" 
                      class="w-9 h-11 object-cover rounded-lg border border-slate-200 bg-slate-100 shrink-0" 
                    />
                    <div>
                      <p class="font-extrabold text-slate-900 group-hover:text-blue-600 transition">
                        {{ resp.personel?.full_name || '-' }}
                      </p>
                      <p class="text-[11px] text-slate-500 font-semibold mt-0.5">
                        {{ formatPangkat(resp.personel?.pangkat) }}
                      </p>
                    </div>
                  </div>
                </td>

                <!-- NIKC / NRP -->
                <td class="px-6 py-4 font-mono font-bold text-slate-700">
                  {{ resp.personel?.nikc || resp.personel?.nrp || '-' }}
                </td>

                <!-- Matra & Angkatan -->
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                    TNI {{ resp.personel?.matra || '-' }}
                  </span>
                  <span v-if="resp.personel?.angkatan" class="text-[11px] text-slate-500 ml-1.5 font-medium">
                    Th. {{ resp.personel.angkatan }}
                  </span>
                </td>

                <!-- Tanggal Submit -->
                <td class="px-6 py-4 text-slate-600 font-medium">
                  {{ formatDate(resp.submitted_at) }}
                </td>

                <!-- Status Badge -->
                <td class="px-6 py-4 text-center">
                  <span 
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide inline-block"
                    :class="{
                      'bg-amber-50 text-amber-700 border border-amber-200': resp.status === 'SUBMITTED',
                      'bg-blue-50 text-blue-700 border border-blue-200': resp.status === 'VERIFIED',
                      'bg-emerald-50 text-emerald-700 border border-emerald-200': resp.status === 'ACCEPTED',
                      'bg-rose-50 text-rose-700 border border-rose-200': resp.status === 'REJECTED',
                    }"
                  >
                    {{ getStatusLabel(resp.status) }}
                  </span>
                </td>

                <!-- Action Button -->
                <td class="px-6 py-4 text-right" @click.stop>
                  <button 
                    @click="openDetail(resp)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white text-xs font-bold transition cursor-pointer"
                  >
                    <span>Rincian</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                  </button>
                </td>
              </tr>

              <tr v-if="!responses.data || responses.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-xs">
                  Belum ada respon personel yang masuk untuk formulir ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="responses.links && responses.links.length > 3" class="p-4 border-t border-slate-100 flex justify-center">
          <div class="flex flex-wrap gap-1">
            <template v-for="(link, key) in responses.links" :key="key">
              <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                class="px-3 py-1.5 text-xs rounded-xl font-medium transition"
                :class="link.active ? 'bg-[#2563EB] text-white font-bold' : 'text-slate-600 hover:bg-slate-100'"
              />
              <span
                v-else
                v-html="link.label"
                class="px-3 py-1.5 text-xs text-slate-300 rounded-xl"
              />
            </template>
          </div>
        </div>
      </div>

      <!-- SLIDE-OVER DRAWER DETIL RESPON PERSONEL -->
      <div v-if="selectedResponse" class="fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop overlay -->
        <div @click="closeDetail" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
          <div class="w-screen max-w-2xl bg-white shadow-2xl flex flex-col">
            <!-- Drawer Header -->
            <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
              <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                  RINCIAN RESPON PERSONEL
                </span>
                <h2 class="text-base font-extrabold text-slate-900 mt-1">
                  {{ selectedResponse.personel?.full_name }}
                </h2>
                <p class="text-xs text-slate-500 font-mono">
                  NIKC: {{ selectedResponse.personel?.nikc || '-' }} | Diserahkan: {{ formatDate(selectedResponse.submitted_at) }}
                </p>
              </div>

              <button 
                @click="closeDetail" 
                class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
            </div>

            <!-- Drawer Content (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6 text-xs">
              <!-- 1. IDENTITAS PERSONEL (AUTO-FILLED) -->
              <div class="space-y-3">
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px]">1</span>
                  Data Diri Personel (Terverifikasi Sistem)
                </h3>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col sm:flex-row gap-4">
                  <img 
                    :src="selectedResponse.personel?.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(selectedResponse.personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedResponse.personel?.full_name || 'P')}&background=e2e8f0&color=334155`" 
                    class="w-20 h-26 object-cover rounded-xl border border-slate-200 bg-white shadow-xs shrink-0 mx-auto sm:mx-0" 
                  />

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 flex-1">
                    <div>
                      <span class="text-[10px] text-slate-400 font-bold block">Nama Lengkap</span>
                      <span class="font-bold text-slate-800">{{ selectedResponse.personel?.full_name || '-' }}</span>
                    </div>
                    <div>
                      <span class="text-[10px] text-slate-400 font-bold block">Pangkat / Matra</span>
                      <span class="font-bold text-slate-800">{{ formatPangkat(selectedResponse.personel?.pangkat) }} (TNI {{ selectedResponse.personel?.matra }})</span>
                    </div>
                    <div>
                      <span class="text-[10px] text-slate-400 font-bold block">NIK KTP</span>
                      <span class="font-mono text-slate-800 font-semibold">{{ selectedResponse.personel?.nik || '-' }}</span>
                    </div>
                    <div>
                      <span class="text-[10px] text-slate-400 font-bold block">NIKC / NRP</span>
                      <span class="font-mono text-slate-800 font-semibold">{{ selectedResponse.personel?.nikc || selectedResponse.personel?.nrp || '-' }}</span>
                    </div>
                    <div>
                      <span class="text-[10px] text-slate-400 font-bold block">Email Terdaftar</span>
                      <span class="text-slate-800">{{ selectedResponse.personel?.user?.email || '-' }}</span>
                    </div>
                    <div>
                      <span class="text-[10px] text-slate-400 font-bold block">Nomor WhatsApp</span>
                      <span class="text-slate-800 font-semibold">{{ selectedResponse.personel?.phone_number || '-' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                      <span class="text-[10px] text-slate-400 font-bold block">Alamat Domisili</span>
                      <span class="text-slate-700 leading-relaxed">{{ selectedResponse.personel?.address || '-' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 2. RIWAYAT PENDIDIKAN -->
              <div v-if="selectedResponse.personel?.riwayat_pendidikan && selectedResponse.personel.riwayat_pendidikan.length > 0" class="space-y-3">
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px]">2</span>
                  Riwayat Pendidikan
                </h3>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                  <table class="w-full text-left text-[11px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold">
                      <tr>
                        <th class="px-3 py-2">Jenjang</th>
                        <th class="px-3 py-2">Nama Sekolah / Lembaga</th>
                        <th class="px-3 py-2">Jurusan</th>
                        <th class="px-3 py-2">Tahun Lulus</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                      <tr v-for="edu in selectedResponse.personel.riwayat_pendidikan" :key="edu.id">
                        <td class="px-3 py-2 font-bold text-slate-800">{{ edu.jenjang }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ edu.nama_institusi }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ edu.jurusan || '-' }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ edu.tahun_lulus || '-' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- 3. BERKAS PERSYARATAN YANG DIUNGGAH -->
              <div class="space-y-3">
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px]">3</span>
                  Dokumen Persyaratan yang Diunggah
                </h3>

                <div v-if="selectedResponse.uploaded_files && Object.keys(selectedResponse.uploaded_files).length > 0" class="space-y-2">
                  <div 
                    v-for="(fileMeta, key) in selectedResponse.uploaded_files" 
                    :key="key"
                    class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center justify-between gap-3"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[10px]">
                        FILE
                      </div>
                      <div>
                        <p class="font-bold text-slate-800">{{ fileMeta.name }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ fileMeta.original_name }}</p>
                      </div>
                    </div>

                    <a 
                      :href="`/documents/private-stream?path=${encodeURIComponent(fileMeta.file_path)}`" 
                      target="_blank"
                      class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 font-bold text-[11px] shadow-2xs transition"
                    >
                      Buka Dokumen
                    </a>
                  </div>
                </div>

                <div v-else class="text-slate-400 italic text-[11px] p-3 bg-slate-50 rounded-xl border border-slate-100">
                  Tidak ada berkas persyaratan yang diunggah.
                </div>
              </div>

              <!-- 4. JAWABAN KUESIONER (TERENKRIPSI) -->
              <div class="space-y-3">
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px]">4</span>
                  Jawaban Kuesioner Personel
                </h3>

                <div v-if="selectedResponse.answers && Object.keys(selectedResponse.answers).length > 0" class="space-y-3">
                  <div 
                    v-for="(ans, qId) in selectedResponse.answers" 
                    :key="qId"
                    class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-1.5"
                  >
                    <p class="font-bold text-slate-800 text-xs">{{ ans.question }}</p>
                    <div class="bg-white p-3 rounded-xl border border-slate-100 text-slate-700 font-medium">
                      {{ ans.answer || '(Tidak dijawab)' }}
                    </div>
                  </div>
                </div>

                <div v-else class="text-slate-400 italic text-[11px] p-3 bg-slate-50 rounded-xl border border-slate-100">
                  Tidak ada jawaban kuesioner.
                </div>
              </div>

              <!-- 5. VERIFIKASI & KEPUTUSAN SELEKSI -->
              <div class="space-y-3 pt-3 border-t border-slate-200">
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-5 h-5 rounded-md bg-blue-600 text-white flex items-center justify-center text-[10px]">5</span>
                  Keputusan Verifikasi / Seleksi
                </h3>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Pilih Status Seleksi</label>
                    <select 
                      v-model="verificationForm.status" 
                      class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                      <option value="SUBMITTED">Terkirim (Menunggu Verifikasi)</option>
                      <option value="VERIFIED">Diverifikasi (Berkas Lengkap)</option>
                      <option value="ACCEPTED">Lolos Seleksi / Diterima</option>
                      <option value="REJECTED">Ditolak / Tidak Lolos</option>
                    </select>
                  </div>

                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-700">Catatan Verifikator / Alasan Keputusan</label>
                    <textarea 
                      v-model="verificationForm.verification_notes" 
                      rows="2"
                      placeholder="Contoh: Berkas persyaratan lengkap dan memenuhi kualifikasi operasi."
                      class="w-full text-xs bg-white border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    ></textarea>
                  </div>

                  <div class="flex justify-end pt-1">
                    <button 
                      type="button" 
                      @click="saveVerification"
                      :disabled="isSaving"
                      class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer disabled:opacity-50"
                    >
                      {{ isSaving ? 'Menyimpan...' : 'Simpan Status Seleksi' }}
                    </button>
                  </div>
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
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  form: Object,
  responses: Object,
  stats: Object,
  filters: Object,
  userRole: String,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'ALL');

const selectedResponse = ref(null);
const isSaving = ref(false);

const verificationForm = ref({
  status: 'SUBMITTED',
  verification_notes: '',
});

let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilter();
  }, 400);
};

const applyFilter = () => {
  router.get(
    window.location.pathname,
    {
      search: search.value,
      status: statusFilter.value,
    },
    { preserveState: true, replace: true }
  );
};

const getPrefix = () => {
  if (['ka_bacadnas','ses_bacadnas','kapus_komcad','pembina_matra','pembina_kodam','pembina_kodaeral','pembina_kodau','pembina_kodim','pembina_lanal','pembina_lanud'].includes(props.userRole)) {
    return 'pju';
  }
  if (props.userRole === 'kordinator_matra' || props.userRole === 'kordinator_angkatan') {
    return 'kordinator';
  }
  return 'admin';
};

const getIndexRoute = () => {
  return route(`${getPrefix()}.form.index`);
};

const openDetail = (resp) => {
  selectedResponse.value = resp;
  verificationForm.value.status = resp.status || 'SUBMITTED';
  verificationForm.value.verification_notes = resp.verification_notes || '';
};

const closeDetail = () => {
  selectedResponse.value = null;
};

const saveVerification = () => {
  if (!selectedResponse.value) return;
  isSaving.value = true;

  router.post(
    route(`${getPrefix()}.form.responses.status`, {
      uuid: props.form.uuid,
      responseUuid: selectedResponse.value.uuid,
    }),
    {
      status: verificationForm.value.status,
      verification_notes: verificationForm.value.verification_notes,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        isSaving.value = false;
        // Update live status in drawer
        selectedResponse.value.status = verificationForm.value.status;
        selectedResponse.value.verification_notes = verificationForm.value.verification_notes;
      },
      onError: () => {
        isSaving.value = false;
      }
    }
  );
};

const getStatusLabel = (status) => {
  return matchStatus(status);
};

const matchStatus = (status) => {
  switch (status) {
    case 'SUBMITTED': return 'Terkirim';
    case 'VERIFIED': return 'Diverifikasi';
    case 'ACCEPTED': return 'Lolos';
    case 'REJECTED': return 'Ditolak';
    default: return status || '-';
  }
};

const formatPangkat = (pangkat) => {
  if (!pangkat) return '-';
  return pangkat.endsWith(' KC') ? pangkat : `${pangkat} KC`;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }) + ' WIB';
};
</script>
