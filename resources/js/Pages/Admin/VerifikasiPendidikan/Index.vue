<template>
  <AuthenticatedLayout>
    <template #header-title>Verifikasi Riwayat Pendidikan</template>

    <div class="space-y-6 text-[#334155]">
      <!-- Filter Bar -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 flex flex-col sm:flex-row gap-3 items-start sm:items-center">
        <div class="flex-1 relative">
          <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input
            v-model="search"
            @keyup.enter="applyFilter"
            type="text"
            placeholder="Cari nama personel, NIKC, institusi, program studi..."
            class="w-full pl-9 pr-4 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
          />
        </div>
        <select v-model="jenisFilter" @change="applyFilter" class="border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs outline-none focus:border-[#2563EB]">
          <option value="">Semua Jenis</option>
          <option value="AKADEMIK">Akademik</option>
          <option value="DIKLAT">Diklat</option>
          <option value="MILITER">Militer</option>
        </select>
        <button @click="applyFilter" class="px-4 py-2 bg-[#2563EB] text-white text-xs font-bold rounded-xl cursor-pointer hover:bg-blue-700 transition">
          Cari
        </button>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
          <div class="text-2xl font-extrabold text-amber-700">{{ items.total }}</div>
          <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wide mt-1">Personel Mengajukan</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
          <div class="text-2xl font-extrabold text-blue-700">{{ countUnverifiedEdu }}</div>
          <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wide mt-1">Total Pendidikan Belum Diverifikasi</div>
        </div>
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center col-span-2 sm:col-span-1">
          <button @click="expandAll" class="w-full h-full py-2 flex flex-col items-center justify-center text-xs font-bold text-slate-600 hover:text-slate-900 transition focus:outline-none">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
            <span class="mt-1">Expand atau Collapse Semua</span>
          </button>
        </div>
      </div>

      <!-- Tabel -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-[#E2E8F0] flex items-center justify-between">
          <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Daftar Pengajuan Pendidikan</h2>
          <span class="text-[10px] text-slate-400">{{ items.total }} Personel</span>
        </div>

        <div v-if="items.data.length === 0" class="p-12 text-center text-slate-400 text-sm">
          <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="font-bold text-slate-500">Semua riwayat pendidikan sudah diverifikasi.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-[#E2E8F0]">
              <tr>
                <th class="px-5 py-3.5 text-left font-bold text-slate-500 uppercase tracking-wide w-12"></th>
                <th class="px-4 py-3.5 text-left font-bold text-slate-500 uppercase tracking-wide">Personel</th>
                <th class="px-4 py-3.5 text-left font-bold text-slate-500 uppercase tracking-wide">Pangkat / Matra</th>
                <th class="px-4 py-3.5 text-left font-bold text-slate-500 uppercase tracking-wide">Kontak</th>
                <th class="px-4 py-3.5 text-center font-bold text-slate-500 uppercase tracking-wide">Jumlah Pengajuan</th>
                <th class="px-4 py-3.5 text-center font-bold text-slate-500 uppercase tracking-wide w-36">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E8F0]">
              <template v-for="personel in items.data" :key="personel.id">
                <!-- Baris Personel Utama -->
                <tr class="hover:bg-slate-50/50 transition cursor-pointer" @click="toggleExpand(personel.id)">
                  <td class="px-5 py-4 text-center">
                    <div class="flex items-center justify-center">
                      <svg class="w-3 h-3 text-slate-400 transition-transform duration-200 transform" :class="{ 'rotate-90': isExpanded(personel.id) }" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                  </td>
                  <td class="px-4 py-4">
                    <div class="font-bold text-slate-800 text-sm">{{ personel.full_name }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">{{ personel.nikc ?? '-' }}</div>
                  </td>
                  <td class="px-4 py-4">
                    <div class="font-semibold text-slate-700">{{ personel.pangkat }}</div>
                    <div class="text-slate-400 text-[10px]">TNI {{ personel.matra }} — Ang. {{ personel.angkatan }}</div>
                  </td>
                  <td class="px-4 py-4">
                    <div class="text-slate-600">{{ personel.phone_number }}</div>
                    <div class="text-[10px] text-slate-400">{{ personel.user?.email }}</div>
                  </td>
                  <td class="px-4 py-4 text-center">
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-bold text-[10px]">
                      {{ personel.riwayat_pendidikan?.length ?? 0 }} Pengajuan
                    </span>
                  </td>
                  <td class="px-4 py-4 text-center" @click.stop>
                    <Link
                      :href="route('admin.personel.education.index', { uuid: personel.uuid })"
                      class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-bold rounded-lg transition cursor-pointer"
                    >
                      Detail Profil
                    </Link>
                  </td>
                </tr>

                <!-- Baris Sub-Tabel Pengajuan Pendidikan (Terbuka Jika Expanded) -->
                <tr v-if="isExpanded(personel.id)">
                  <td colspan="6" class="bg-slate-50/50 p-4 border-t border-b border-slate-100">
                    <div class="bg-white border border-[#E2E8F0] rounded-xl overflow-hidden shadow-xs ml-4">
                      <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-[#E2E8F0]">
                          <tr>
                            <th class="px-4 py-2 text-left font-bold text-slate-500 uppercase tracking-wide w-28">Jenis</th>
                            <th class="px-4 py-2 text-left font-bold text-slate-500 uppercase tracking-wide w-24">Jenjang</th>
                            <th class="px-4 py-2 text-left font-bold text-slate-500 uppercase tracking-wide">Institusi / Program Studi</th>
                            <th class="px-4 py-2 text-center font-bold text-slate-500 uppercase tracking-wide w-24">Tahun Lulus</th>
                            <th class="px-4 py-2 text-center font-bold text-slate-500 uppercase tracking-wide w-20">Berkas</th>
                            <th class="px-4 py-2 text-center font-bold text-slate-500 uppercase tracking-wide w-48">Verifikasi</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E2E8F0]">
                          <tr v-for="edu in personel.riwayat_pendidikan" :key="edu.id" class="hover:bg-slate-50/30 transition">
                            <td class="px-4 py-3">
                              <span :class="{
                                'bg-blue-50 text-blue-700 border-blue-200': edu.jenis === 'AKADEMIK',
                                'bg-emerald-50 text-emerald-700 border-emerald-200': edu.jenis === 'DIKLAT',
                                'bg-amber-50 text-amber-700 border-amber-200': edu.jenis === 'MILITER',
                              }" class="px-2 py-0.5 rounded border text-[9px] font-bold uppercase">
                                {{ edu.jenis }}
                              </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 font-medium">{{ edu.jenjang }}</td>
                            <td class="px-4 py-3">
                              <div class="font-semibold text-slate-700">{{ edu.nama_institusi }}</div>
                              <div class="text-[10px] text-slate-400 mt-0.5">{{ edu.program_studi }}</div>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ edu.tahun_lulus ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                              <a
                                v-if="edu.file_ijazah_path"
                                :href="route('personel.document.download', { path: edu.file_ijazah_path })"
                                target="_blank"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[10px] font-bold transition cursor-pointer"
                              >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Pratinjau
                              </a>
                              <span v-else class="text-slate-300 text-[10px]">Tidak ada</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                              <div class="flex items-center justify-center gap-2">
                                <button
                                  @click="openVerifModal(edu, personel)"
                                  class="px-2.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-extrabold rounded-lg transition cursor-pointer flex items-center gap-1"
                                >
                                  Verifikasi
                                </button>
                                <Link
                                  :href="route('admin.personel.education.index', { uuid: personel.uuid })"
                                  class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-bold rounded-lg transition cursor-pointer"
                                >
                                  Edit/Detail
                                </Link>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="items.last_page > 1" class="p-4 border-t border-[#E2E8F0] flex items-center justify-between">
          <span class="text-[10px] text-slate-400">Halaman {{ items.current_page }} dari {{ items.last_page }}</span>
          <div class="flex gap-2">
            <Link
              v-if="items.prev_page_url"
              :href="items.prev_page_url"
              class="px-3 py-1.5 border border-[#E2E8F0] rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
            >← Sebelumnya</Link>
            <Link
              v-if="items.next_page_url"
              :href="items.next_page_url"
              class="px-3 py-1.5 border border-[#E2E8F0] rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
            >Berikutnya →</Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Detail Pengajuan & Verifikasi Pendidikan -->
    <div v-if="showVerifModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden transform transition-all duration-300">
        <!-- Header Modal -->
        <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50/70 flex items-center justify-between">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">
            {{ isRejecting ? 'Tolak Pengajuan Pendidikan' : 'Detail Pengajuan Pendidikan' }}
          </h3>
          <button @click="closeVerifModal" class="text-slate-400 hover:text-slate-600 focus:outline-none text-base cursor-pointer">
            &times;
          </button>
        </div>

        <!-- TAMPILAN UTAMA DETAIL -->
        <div v-if="!isRejecting" class="p-6 space-y-4 text-xs overflow-y-auto max-h-[80vh]">
          <!-- Personel Info -->
          <div class="border-b border-[#E2E8F0] pb-3">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Personel Pemohon</p>
            <p class="text-sm font-bold text-slate-800 mt-0.5">{{ targetPersonel?.full_name }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">NIKC: {{ targetPersonel?.nikc }}</p>
          </div>

          <!-- Detail Pendidikan -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Jenis Pendidikan</p>
              <p class="font-bold text-slate-700 mt-0.5">{{ selectedEdu?.jenis }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Jenjang</p>
              <p class="font-bold text-slate-700 mt-0.5">{{ selectedEdu?.jenjang }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Nama Institusi / Sekolah</p>
              <p class="font-bold text-slate-800 mt-0.5 text-sm leading-relaxed">{{ selectedEdu?.nama_institusi }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Program Studi / Jurusan</p>
              <p class="font-bold text-slate-700 mt-0.5 leading-relaxed">{{ selectedEdu?.program_studi || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Tahun Lulus</p>
              <p class="font-bold text-slate-700 mt-0.5">{{ selectedEdu?.tahun_lulus ?? '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Nomor Ijazah</p>
              <p class="font-bold text-slate-700 mt-0.5">{{ selectedEdu?.nomor_ijazah || '-' }}</p>
            </div>
          </div>

          <!-- File Berkas Ijazah & Pratinjau Terintegrasi (Iframe/Image) -->
          <div class="bg-slate-50 border border-[#E2E8F0] p-4 rounded-xl space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Berkas Ijazah / Sertifikat</p>
                <p class="text-slate-500 mt-0.5">Format berkas terunggah: PDF/JPG/PNG.</p>
              </div>
              <div class="flex gap-2">
                <a
                  v-if="selectedEdu?.file_ijazah_path"
                  :href="route('personel.document.download', { path: selectedEdu.file_ijazah_path })"
                  target="_blank"
                  class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-[10px] transition cursor-pointer flex items-center gap-1"
                >
                  Buka Tab Baru
                </a>
                <span v-else class="text-slate-400 font-bold text-[10px]">Tidak ada berkas</span>
              </div>
            </div>

            <!-- Frame Pratinjau Dokumen -->
            <div v-if="previewUrl" class="border border-[#E2E8F0] rounded-lg overflow-hidden bg-white">
              <iframe :src="previewUrl" class="w-full h-80 border-0 bg-slate-50" allow="autoplay"></iframe>
            </div>
          </div>
        </div>

        <!-- TAMPILAN FORM PENOLAKAN -->
        <div v-else class="p-6 space-y-4 text-xs">
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Personel Pemohon</p>
            <p class="text-sm font-bold text-slate-800 mt-0.5">{{ targetPersonel?.full_name }}</p>
          </div>

          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-600 uppercase">Alasan Penolakan</label>
            <textarea
              v-model="rejectReason"
              rows="4"
              class="w-full px-4 py-3 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-red-500"
              placeholder="Tulis alasan penolakan di sini... (Contoh: Berkas ijazah buram / tidak terbaca, mohon unggah berkas baru yang terbaca jelas.)"
              required
            ></textarea>
            <p class="text-[10px] text-slate-400 leading-normal">
              Alasan penolakan ini akan langsung dikirimkan ke nomor WhatsApp personel bersangkutan sebagai panduan bagi mereka untuk mengunggah ulang data yang valid.
            </p>
          </div>
        </div>

        <!-- Footer Modal Aksi (Normal) -->
        <div v-if="!isRejecting" class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50/50 flex items-center justify-between">
          <button
            @click="isRejecting = true"
            class="px-4 py-2 border border-red-200 hover:bg-red-50 text-red-600 font-bold rounded-xl cursor-pointer transition text-xs"
          >
            Tolak Pengajuan
          </button>
          <div class="flex gap-3">
            <button
              @click="closeVerifModal"
              class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold cursor-pointer transition text-xs"
            >
              Batal
            </button>
            <button
              @click="processVerify"
              :disabled="processingId === selectedEdu?.id"
              class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-white font-extrabold rounded-xl shadow-md shadow-emerald-500/10 cursor-pointer transition text-xs"
            >
              {{ processingId === selectedEdu?.id ? 'Memproses...' : 'Verifikasi Sekarang' }}
            </button>
          </div>
        </div>

        <!-- Footer Modal Aksi (Sedang Menulis Alasan Penolakan) -->
        <div v-else class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50/50 flex items-center justify-end gap-3">
          <button
            @click="isRejecting = false"
            class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold cursor-pointer transition text-xs"
          >
            Kembali
          </button>
          <button
            @click="processReject"
            :disabled="processingId === selectedEdu?.id || !rejectReason.trim()"
            class="px-4 py-2 bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white font-extrabold rounded-xl shadow-md shadow-red-500/10 cursor-pointer transition text-xs"
          >
            {{ processingId === selectedEdu?.id ? 'Memproses...' : 'Kirim & Tolak Permanen' }}
          </button>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useSwal } from '@/Composables/useSwal.js';

const props = defineProps({
  items: Object,
  filters: Object,
});

const { alertSuccess, alertError } = useSwal();

const search      = ref(props.filters?.search ?? '');
const jenisFilter = ref(props.filters?.jenis ?? '');
const processingId = ref(null);

// State Modal Detail Verifikasi & Penolakan
const showVerifModal = ref(false);
const selectedEdu = ref(null);
const targetPersonel = ref(null);
const isRejecting = ref(false);
const rejectReason = ref('');

// Computed url pratinjau dokumen inline
const previewUrl = computed(() => {
  if (!selectedEdu.value?.file_ijazah_path) return null;
  return route('personel.document.download', { path: selectedEdu.value.file_ijazah_path });
});

const openVerifModal = (edu, personel) => {
  selectedEdu.value = edu;
  targetPersonel.value = personel;
  showVerifModal.value = true;
};

const closeVerifModal = () => {
  showVerifModal.value = false;
  selectedEdu.value = null;
  targetPersonel.value = null;
  isRejecting.value = false;
  rejectReason.value = '';
};

// State untuk collapse/expand baris
const expandedIds = ref([]);

const toggleExpand = (id) => {
  const index = expandedIds.value.indexOf(id);
  if (index > -1) {
    expandedIds.value.splice(index, 1);
  } else {
    expandedIds.value.push(id);
  }
};

const isExpanded = (id) => expandedIds.value.includes(id);

const expandAll = () => {
  if (expandedIds.value.length === props.items.data.length) {
    expandedIds.value = []; // collapse all
  } else {
    expandedIds.value = props.items.data.map(p => p.id); // expand all
  }
};

const countUnverifiedEdu = computed(() => {
  let total = 0;
  props.items.data.forEach(p => {
    total += p.riwayat_pendidikan?.length ?? 0;
  });
  return total;
});

const applyFilter = () => {
  router.get(route('admin.education.verif-list'), {
    search: search.value || undefined,
    jenis: jenisFilter.value || undefined,
  }, { preserveState: true, replace: true });
};

const processVerify = () => {
  if (!selectedEdu.value) return;

  const eduId = selectedEdu.value.id;
  processingId.value = eduId;
  
  router.post(route('admin.education.verify', { education: eduId }), {}, {
    onSuccess: () => {
      alertSuccess('Berhasil', 'Riwayat pendidikan telah diverifikasi.');
      processingId.value = null;
      closeVerifModal();
    },
    onError: () => {
      alertError('Gagal', 'Terjadi kesalahan saat memverifikasi.');
      processingId.value = null;
    },
    onFinish: () => {
      processingId.value = null;
    },
  });
};

const processReject = () => {
  if (!selectedEdu.value || !rejectReason.value.trim()) return;

  const eduId = selectedEdu.value.id;
  processingId.value = eduId;

  router.post(route('admin.education.reject', { education: eduId }), {
    reason: rejectReason.value
  }, {
    onSuccess: () => {
      alertSuccess('Berhasil', 'Pengajuan pendidikan berhasil ditolak dan pemberitahuan telah dikirim via WhatsApp.');
      processingId.value = null;
      closeVerifModal();
    },
    onError: () => {
      alertError('Gagal', 'Terjadi kesalahan saat menolak pengajuan.');
      processingId.value = null;
    },
    onFinish: () => {
      processingId.value = null;
    }
  });
};
</script>
