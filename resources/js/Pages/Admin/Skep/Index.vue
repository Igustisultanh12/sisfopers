<template>
  <AuthenticatedLayout>
    <template #header-title>Manajemen Database & Pengajuan SKEP</template>

    <!-- Navigasi Tab Premium -->
    <div class="flex gap-4 border-b border-[#E2E8F0] mb-6 select-none bg-white px-6 pt-3 rounded-2xl shadow-xs">
      <button 
        @click="activeTab = 'database'" 
        :class="activeTab === 'database' ? 'text-[#2563EB] border-b-2 border-[#2563EB] font-bold py-3.5' : 'text-slate-400 py-3.5 hover:text-slate-600 font-medium'" 
        class="text-sm transition cursor-pointer"
      >
        Database SKEP (Valid NIKC)
      </button>
      <button 
        @click="activeTab = 'requests'" 
        :class="activeTab === 'requests' ? 'text-[#2563EB] border-b-2 border-[#2563EB] font-bold py-3.5' : 'text-slate-400 py-3.5 hover:text-slate-600 font-medium'" 
        class="text-sm transition relative cursor-pointer"
      >
        Pengajuan Verifikasi SKEP (PDF)
        <span v-if="pendingRequestsCount > 0" class="absolute top-1.5 -right-2 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-[#EF4444] text-white animate-pulse">
          {{ pendingRequestsCount }}
        </span>
      </button>
    </div>

    <!-- TAB 1: DATABASE SKEP -->
    <div v-if="activeTab === 'database'" class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
          <input 
            type="text" 
            v-model="searchQuery" 
            @keyup.enter="handleSearch" 
            class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none w-full sm:w-64 focus:border-[#2563EB]" 
            placeholder="Cari Nama Lengkap atau NIKC..." 
          />
          <select 
            v-model="filterMatra" 
            @change="handleSearch" 
            class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-600 w-full sm:w-auto bg-white"
          >
            <option value="">Semua Matra</option>
            <option value="AD">TNI AD</option>
            <option value="AL">TNI AL</option>
            <option value="AU">TNI AU</option>
          </select>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
          <button 
            @click="showImportModal = true" 
            class="w-full sm:w-auto text-center px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white font-semibold text-xs rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer"
          >
            Impor Excel SKEP
          </button>
        </div>
      </div>

      <!-- Tabel Database SKEP -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto w-full">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none">
              <th class="p-4">Nama Lengkap</th>
              <th class="p-4">Tanggal Lahir</th>
              <th class="p-4">NIKC (Nomor Induk)</th>
              <th class="p-4">Pangkat</th>
              <th class="p-4">Matra</th>
              <th class="p-4">Angkatan</th>
              <th class="p-4 text-right">Opsi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
            <tr v-for="item in skepData.data" :key="item.id" class="hover:bg-slate-50/30 transition">
              <td class="p-4 font-bold text-slate-800">{{ item.nama_lengkap }}</td>
              <td class="p-4 font-semibold text-slate-700">{{ formatDate(item.dob) }}</td>
              <td class="p-4">
                <span class="font-mono font-bold text-[#2563EB] bg-blue-50 px-2 py-0.5 rounded border border-blue-100/60 text-xs">
                  {{ item.nikc }}
                </span>
              </td>
              <td class="p-4 font-semibold text-slate-700">{{ item.pangkat || '-' }}</td>
              <td class="p-4">
                <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-100/40">
                  TNI {{ item.matra }}
                </span>
              </td>
              <td class="p-4 font-semibold text-slate-700">{{ item.angkatan }}</td>
              <td class="p-4 text-right">
                <button 
                  @click="deleteSkep(item.id)" 
                  class="text-xs font-semibold text-[#EF4444] hover:underline cursor-pointer bg-red-50/50 hover:bg-red-50 px-2.5 py-1 rounded-lg border border-transparent hover:border-red-100 transition"
                >
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="skepData.data.length === 0">
              <td colspan="7" class="p-12 text-center text-slate-400 text-sm">Tidak ada record data database SKEP Komcad.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginator Database -->
      <div v-if="skepData.links.length > 3" class="flex justify-center gap-1 mt-4 select-none">
        <button 
          v-for="(link, i) in skepData.links" 
          :key="i"
          @click="navigatePage('skep', link.url)"
          :disabled="!link.url || link.active"
          :class="[
            link.active ? 'bg-[#2563EB] text-white font-bold' : 'bg-white hover:bg-slate-50 text-slate-700',
            !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
          ]"
          class="px-3 py-1.5 border border-[#E2E8F0] rounded-lg text-xs transition"
          v-html="link.label"
        />
      </div>
    </div>

    <!-- TAB 2: PENGAJUAN VERIFIKASI SKEP -->
    <div v-if="activeTab === 'requests'" class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <input 
          type="text" 
          v-model="searchRequestQuery" 
          @keyup.enter="handleSearchRequest" 
          class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none w-full sm:w-64 focus:border-[#2563EB]" 
          placeholder="Cari nama, NIKC, atau NIK..."
        />
      </div>

      <!-- Tabel Pengajuan -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto w-full">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none">
              <th class="p-4">Pengaju</th>
              <th class="p-4">Detail Pengajuan</th>
              <th class="p-4">Kontak / WA</th>
              <th class="p-4">Berkas PDF</th>
              <th class="p-4">Status</th>
              <th class="p-4 text-right">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
            <tr v-for="req in skepRequests.data" :key="req.id" class="hover:bg-slate-50/30 transition">
              <td class="p-4">
                <div class="font-bold text-slate-800">{{ req.nama_lengkap }}</div>
                <div class="text-xs text-slate-400 mt-0.5">NIK: {{ req.nik }}</div>
              </td>
              <td class="p-4">
                <div class="text-xs">
                  NIKC: <span class="font-mono font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">{{ req.nikc }}</span>
                </div>
                <div class="text-xs text-slate-400 mt-1">
                  Pangkat: {{ req.pangkat || '-' }} | Matra: TNI {{ req.matra }} | Angkatan: {{ req.angkatan }}
                </div>
              </td>
              <td class="p-4 font-semibold text-slate-700">{{ req.phone_number }}</td>
              <td class="p-4">
                <a 
                  :href="`/documents/private/${req.skep_file}`" 
                  target="_blank" 
                  class="text-xs font-bold text-[#2563EB] hover:underline flex items-center gap-1 bg-[#2563EB]/5 px-2.5 py-1 rounded-lg w-max"
                >
                  📄 Lihat Berkas
                </a>
              </td>
              <td class="p-4">
                <span 
                  :class="{
                    'bg-amber-50 text-amber-700 border-amber-100': req.status === 'PENDING',
                    'bg-green-50 text-green-700 border-green-100': req.status === 'APPROVED',
                    'bg-red-50 text-red-700 border-red-100': req.status === 'REJECTED'
                  }" 
                  class="px-2.5 py-0.5 rounded text-[11px] font-bold border uppercase"
                >
                  {{ req.status }}
                </span>
              </td>
              <td class="p-4 text-right">
                <button 
                  v-if="req.status === 'PENDING'"
                  @click="openVerifyModal(req)" 
                  class="text-xs font-bold text-[#2563EB] hover:underline bg-[#2563EB]/5 hover:bg-[#2563EB]/10 px-3 py-1.5 rounded-xl border border-transparent hover:border-blue-100 transition cursor-pointer"
                >
                  Tinjau Pengajuan
                </button>
                <div v-else class="text-xs text-slate-400">
                  Ditinjau oleh: <span class="font-semibold text-slate-600">{{ req.verifier?.username || '-' }}</span>
                </div>
              </td>
            </tr>
            <tr v-if="skepRequests.data.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-400 text-sm">Tidak ada antrean pengajuan verifikasi berkas SKEP.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginator Pengajuan -->
      <div v-if="skepRequests.links.length > 3" class="flex justify-center gap-1 mt-4 select-none">
        <button 
          v-for="(link, i) in skepRequests.links" 
          :key="i"
          @click="navigatePage('request', link.url)"
          :disabled="!link.url || link.active"
          :class="[
            link.active ? 'bg-[#2563EB] text-white font-bold' : 'bg-white hover:bg-slate-50 text-slate-700',
            !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
          ]"
          class="px-3 py-1.5 border border-[#E2E8F0] rounded-lg text-xs transition"
          v-html="link.label"
        />
      </div>
    </div>

    <!-- MODAL 1: IMPOR EXCEL SKEP -->
    <div v-if="showImportModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl max-w-md w-full p-6 shadow-xl space-y-5">
        <div>
          <h3 class="text-base font-bold text-slate-800">Impor Database SKEP</h3>
          <p class="text-xs text-slate-400 mt-0.5">Unggah file berformat Excel (.xls atau .xlsx) berisi data pra-verifikasi personel.</p>
        </div>

        <form @submit.prevent="submitImport" class="space-y-4">
          <div class="p-4 border-2 border-dashed border-[#E2E8F0] hover:border-[#2563EB] rounded-xl text-center space-y-2 transition">
            <input 
              type="file" 
              ref="excelInput" 
              @change="handleExcelChange" 
              class="hidden" 
              accept=".xls,.xlsx" 
              required 
            />
            <div @click="$refs.excelInput.click()" class="cursor-pointer space-y-2 py-4">
              <span class="text-3xl">📥</span>
              <p class="text-xs font-bold text-slate-500">
                {{ selectedFile ? selectedFile.name : 'Klik untuk memilih File Excel' }}
              </p>
              <p class="text-[10px] text-slate-400">Pastikan urutan kolom: Nama Lengkap, NIKC, Pangkat, Angkatan, Matra, Tanggal Lahir</p>
            </div>
          </div>

          <div class="flex justify-end gap-2.5 pt-2 border-t border-[#E2E8F0]">
            <button 
              type="button" 
              @click="closeImportModal" 
              class="px-4 py-2 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="importing" 
              class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer"
            >
              Mulai Impor
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: VERIFIKASI SKEP REQUEST (PDF PREVIEW & VERIFY DECISION) -->
    <div v-if="showVerifyModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl max-w-4xl w-full p-6 shadow-xl flex flex-col md:flex-row gap-6 max-h-[90vh] overflow-hidden">
        
        <!-- Sisi Kiri: Preview Berkas SKEP PDF -->
        <div class="flex-1 flex flex-col min-h-[300px] md:min-h-0">
          <div class="mb-3 flex justify-between items-center">
            <h4 class="text-sm font-bold text-slate-800">Lampiran Berkas SKEP (PDF)</h4>
            <a 
              :href="`/documents/private/${selectedRequest?.skep_file}`" 
              target="_blank" 
              class="text-xs font-bold text-[#2563EB] hover:underline"
            >
              Buka di Tab Baru ↗
            </a>
          </div>
          <div class="flex-1 bg-slate-100 rounded-xl overflow-hidden border border-[#E2E8F0]">
            <iframe 
              :src="`/documents/private/${selectedRequest?.skep_file}`" 
              class="w-full h-full border-0"
            ></iframe>
          </div>
        </div>

        <!-- Sisi Kanan: Lembar Otorisasi Tindakan -->
        <div class="w-full md:w-80 shrink-0 flex flex-col justify-between overflow-y-auto">
          <div class="space-y-5">
            <div>
              <span class="text-[10px] bg-slate-100 px-2 py-1 rounded font-bold text-slate-500 uppercase tracking-wider">Lembar Tinjauan</span>
              <h3 class="text-base font-bold text-slate-800 mt-2">Detail & Verifikasi Berkas</h3>
            </div>

            <div class="bg-slate-50/60 p-4 rounded-xl border border-[#E2E8F0] text-xs space-y-2 text-slate-600">
              <p><strong>Nama Lengkap:</strong> {{ selectedRequest?.nama_lengkap }}</p>
              <p><strong>NIK:</strong> {{ selectedRequest?.nik }}</p>
              <p><strong>Tanggal Lahir:</strong> {{ formatDate(selectedRequest?.dob) }}</p>
              <p><strong>NIKC Pengaju:</strong> <span class="font-mono font-bold text-slate-800">{{ selectedRequest?.nikc }}</span></p>
              <p><strong>Matra:</strong> TNI {{ selectedRequest?.matra }}</p>
              <p><strong>Pangkat:</strong> {{ selectedRequest?.pangkat || '-' }}</p>
              <p><strong>Angkatan:</strong> {{ selectedRequest?.angkatan }}</p>
              <p><strong>WhatsApp:</strong> {{ selectedRequest?.phone_number }}</p>
            </div>

            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Keputusan Akhir</label>
                <select v-model="verifyForm.status" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium">
                  <option value="APPROVED">SETUJUI & AKTIFKAN NIKC</option>
                  <option value="REJECTED">TOLAK BERKAS PENGAJUAN</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Catatan Verifikator / Alasan</label>
                <textarea v-model="verifyForm.admin_notes" rows="4" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="Masukkan catatan penolakan atau keterangan rincian alokasi..."></textarea>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-2.5 pt-4 border-t border-[#E2E8F0] mt-5">
            <button 
              @click="closeVerifyModal" 
              class="px-4 py-2 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
            <button 
              @click="submitVerification" 
              class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer"
            >
              Kirim Keputusan
            </button>
          </div>
        </div>

      </div>
    </div>

  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({
  skepData: Object,
  skepRequests: Object,
  filters: Object
});

const page = usePage();
const { confirmAction, alertSuccess, alertError, showLoadingProgress, closeLoading } = useSwal();

// Tab Active State
const activeTab = ref('database');

// Search & Filter state
const searchQuery = ref(props.filters.search || '');
const filterMatra = ref(props.filters.matra || '');
const searchRequestQuery = ref(props.filters.search_request || '');

// Import Modal State
const showImportModal = ref(false);
const selectedFile = ref(null);
const excelInput = ref(null);
const importing = ref(false);

// Verify Modal State
const showVerifyModal = ref(false);
const selectedRequest = ref(null);
const verifyForm = ref({
  status: 'APPROVED',
  admin_notes: ''
});

// Count pending requests tactically
const pendingRequestsCount = computed(() => {
  return props.skepRequests?.data?.filter(r => r.status === 'PENDING').length || 0;
});

const formatDate = (value) => {
  if (!value) return '-';
  try {
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
  } catch (e) {
    return value;
  }
};

// Navigation / Search handlers
const handleSearch = () => {
  router.get(route('admin.skep.index'), {
    search: searchQuery.value,
    matra: filterMatra.value,
    search_request: searchRequestQuery.value
  }, {
    preserveState: true,
    replace: true
  });
};

const handleSearchRequest = () => {
  router.get(route('admin.skep.index'), {
    search: searchQuery.value,
    matra: filterMatra.value,
    search_request: searchRequestQuery.value
  }, {
    preserveState: true,
    replace: true
  });
};

const navigatePage = (tab, url) => {
  if (!url) return;
  router.get(url, {}, {
    preserveState: true
  });
};

// Import file action
const handleExcelChange = (e) => {
  selectedFile.value = e.target.files[0];
};

const closeImportModal = () => {
  showImportModal.value = false;
  selectedFile.value = null;
};

const submitImport = () => {
  if (!selectedFile.value) return;

  const data = new FormData();
  data.append('file', selectedFile.value);

  showImportModal.value = false;
  importing.value = true;

  router.post(route('admin.skep.import'), data, {
    onBefore: () => showLoadingProgress('Mengimpor data Excel SKEP...'),
    onSuccess: () => {
      closeLoading();
      importing.value = false;
      selectedFile.value = null;
      alertSuccess('Impor Berhasil', 'Database SKEP telah diperbarui dari file Excel.');
    },
    onError: (errors) => {
      closeLoading();
      importing.value = false;
      const errMsg = errors.error || 'Gagal memproses file Excel.';
      alertError('Impor Gagal', errMsg);
    }
  });
};

// Delete Skep Data
const deleteSkep = (id) => {
  confirmAction(
    'Hapus Data SKEP?',
    'Data ini tidak akan bisa digunakan oleh calon pendaftar yang belum registrasi.',
    'Ya, Hapus!',
    () => {
      router.delete(route('admin.skep.destroy', id), {
        onBefore: () => showLoadingProgress('Menghapus data SKEP...'),
        onSuccess: () => {
          closeLoading();
          alertSuccess('Berhasil Dihapus', 'Data SKEP berhasil dihapus dari database.');
        },
        onError: () => {
          closeLoading();
          alertError('Gagal Hapus', 'Terjadi kesalahan sistem.');
        }
      });
    }
  );
};

// Verify Request modal actions
const openVerifyModal = (req) => {
  selectedRequest.value = req;
  verifyForm.value.status = 'APPROVED';
  verifyForm.value.admin_notes = '';
  showVerifyModal.value = true;
};

const closeVerifyModal = () => {
  showVerifyModal.value = false;
  selectedRequest.value = null;
};

const submitVerification = () => {
  showVerifyModal.value = false;
  
  router.post(route('admin.skep.verify', selectedRequest.value.id), verifyForm.value, {
    onBefore: () => showLoadingProgress('Memproses Keputusan Verifikasi Berkas...'),
    onSuccess: () => {
      closeLoading();
      closeVerifyModal();
      alertSuccess('Verifikasi Berhasil', 'Keputusan berkas pendaftar telah terkirim dan tercatat.');
    },
    onError: (errors) => {
      closeLoading();
      const errMsg = errors.error || 'Terjadi gangguan otorisasi jaringan.';
      alertError('Proses Gagal', errMsg);
    }
  });
};
</script>

<style scoped>
/* Transisi mulus untuk modal */
.fixed {
  animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
</style>
