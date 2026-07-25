<template>
  <AuthenticatedLayout>
    <template #header-title>Verifikasi Pengkinian Data Personel</template>

    <div class="space-y-6 max-w-6xl">
      <!-- Summary / Filter section -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Daftar Pengajuan Pengkinian Data</h3>
          
          <div class="flex flex-wrap items-center gap-3">
            <input
              v-model="search"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Cari nama / NIKC..."
              class="px-4 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
            />
            <select v-model="statusFilter" @change="handleSearch" class="px-4 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]">
              <option value="">Semua Status</option>
              <option value="PENDING">Menunggu Verifikasi</option>
              <option value="APPROVED">Disetujui</option>
              <option value="REJECTED">Ditolak</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabel Pengajuan -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden text-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-[#E2E8F0] text-slate-500 font-bold uppercase text-[10px]">
                <th class="p-4">No.</th>
                <th class="p-4">Personel</th>
                <th class="p-4">Kategori</th>
                <th class="p-4">Detail Data (NRP/Satuan)</th>
                <th class="p-4">Tanggal Pengajuan</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E8F0]">
              <tr v-for="(item, idx) in items.data" :key="item.id" class="hover:bg-slate-50/50 transition">
                <td class="p-4 text-slate-400 font-bold">{{ (items.current_page - 1) * items.per_page + idx + 1 }}.</td>
                <td class="p-4">
                  <p class="font-bold text-slate-800">{{ item.personel?.full_name || '-' }}</p>
                  <p class="text-[10px] text-slate-400">NIKC: {{ item.personel?.nikc || '-' }}</p>
                </td>
                <td class="p-4">
                  <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                    {{ item.jenis_pengkinian }}
                  </span>
                </td>
                <td class="p-4">
                  <p v-if="item.nrp" class="font-bold text-slate-700">NRP: {{ item.nrp }}</p>
                  <p v-if="item.satuan" class="text-slate-500">{{ item.satuan }} ({{ item.jabatan || '-' }})</p>
                  <p v-if="!item.nrp && !item.satuan" class="text-slate-400 italic">Surat Kematian</p>
                </td>
                <td class="p-4 text-slate-500">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</td>
                <td class="p-4 text-center">
                  <span
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold border"
                    :class="{
                      'bg-amber-50 text-amber-700 border-amber-200': item.status === 'PENDING',
                      'bg-green-50 text-green-700 border-green-200': item.status === 'APPROVED',
                      'bg-red-50 text-red-700 border-red-200': item.status === 'REJECTED'
                    }"
                  >
                    {{ item.status === 'PENDING' ? 'PENDING' : (item.status === 'APPROVED' ? 'APPROVED' : 'REJECTED') }}
                  </span>
                </td>
                <td class="p-4 text-center">
                  <button
                    @click="openModal(item)"
                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-bold rounded-lg transition cursor-pointer"
                  >
                    Detail / Verifikasi
                  </button>
                </td>
              </tr>
              <tr v-if="items.data.length === 0">
                <td colspan="7" class="p-12 text-center text-slate-400 italic">Belum ada data pengajuan pengkinian data.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL DETAIL & VERIFIKASI PENGKINIAN DATA -->
    <div v-if="showModal && selectedItem" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 animate-fade-in">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden text-xs">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
          <div>
            <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
              :class="selectedItem.status === 'APPROVED' ? 'bg-green-50 text-green-700 border-green-200' : (selectedItem.status === 'REJECTED' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200')">
              STATUS: {{ selectedItem.status }}
            </span>
            <h3 class="text-sm font-bold text-slate-800 mt-1">Detail Pengkinian Data Personel</h3>
          </div>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-base cursor-pointer">✕</button>
        </div>

        <!-- Body Modal -->
        <div v-if="!isRejecting" class="p-6 space-y-4">
          <!-- Detail Personel & Kategori -->
          <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-[#E2E8F0]">
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">Nama Personel</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.personel?.full_name }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">NIKC</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.personel?.nikc || '-' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Kategori Pengkinian</p>
              <p class="font-bold text-blue-600 mt-0.5">{{ selectedItem.jenis_pengkinian }}</p>
            </div>
            <div v-if="selectedItem.nrp">
              <p class="text-[10px] font-bold text-slate-400 uppercase">NRP</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.nrp }}</p>
            </div>
            <div v-if="selectedItem.satuan">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Satuan & Jabatan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.satuan }} - {{ selectedItem.jabatan || '-' }}</p>
            </div>
            <div v-if="selectedItem.tmt_pengangkatan">
              <p class="text-[10px] font-bold text-slate-400 uppercase">TMT Pengangkatan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.tmt_pengangkatan }}</p>
            </div>
            <div v-if="selectedItem.tmt_masuk_satuan">
              <p class="text-[10px] font-bold text-slate-400 uppercase">TMT Masuk Satuan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.tmt_masuk_satuan }}</p>
            </div>
          </div>

          <!-- Pratinjau Berkas -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Berkas Lampiran Pengkinian</p>
              <a :href="documentUrl(selectedItem.document_path)" target="_blank" class="text-blue-600 hover:underline font-bold text-[10px]">
                📂 Buka Tab Baru
              </a>
            </div>
            <div class="border border-[#E2E8F0] rounded-xl overflow-hidden bg-slate-50">
              <iframe :src="documentUrl(selectedItem.document_path)" class="w-full h-56 border-0"></iframe>
            </div>
          </div>
        </div>

        <!-- Form Alasan Penolakan -->
        <div v-else class="p-6 space-y-4">
          <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <p class="font-bold text-xs">Form Penolakan Pengkinian Data</p>
            <p class="text-[11px] mt-0.5 text-red-600">Berikan alasan penolakan agar personel dapat memperbaiki berkas atau data yang diajukan.</p>
          </div>
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase">Alasan Penolakan</label>
            <textarea
              v-model="rejectReason"
              rows="4"
              class="w-full px-4 py-3 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-red-500"
              placeholder="Tulis alasan penolakan..."
              required
            ></textarea>
          </div>
        </div>

        <!-- Footer Modal Aksi -->
        <div v-if="!isRejecting" class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
          <button
            v-if="selectedItem.status === 'PENDING'"
            @click="isRejecting = true"
            class="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl font-bold transition cursor-pointer"
          >
            ✕ Tolak Pengajuan
          </button>
          <div v-else></div>

          <div class="flex items-center gap-3">
            <button @click="closeModal" class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition cursor-pointer">
              Tutup
            </button>
            <button
              v-if="selectedItem.status === 'PENDING'"
              @click="processVerify"
              :disabled="processing"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-md transition cursor-pointer"
            >
              {{ processing ? 'Memproses...' : '✓ Setujui & Verifikasi' }}
            </button>
          </div>
        </div>

        <!-- Footer Modal Reject -->
        <div v-else class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50 flex items-center justify-end gap-3">
          <button @click="isRejecting = false" class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition cursor-pointer">
            Kembali
          </button>
          <button
            @click="processReject"
            :disabled="processing || !rejectReason.trim()"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-md transition cursor-pointer"
          >
            {{ processing ? 'Memproses...' : 'Kirim & Tolak' }}
          </button>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({
  items: Object,
  filters: Object,
});

const { alertSuccess, alertError } = useSwal();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showModal = ref(false);
const selectedItem = ref(null);
const isRejecting = ref(false);
const rejectReason = ref('');
const processing = ref(false);

const handleSearch = () => {
  router.get(route('admin.pengkinian-data.index'), {
    search: search.value,
    status: statusFilter.value,
  }, { preserveState: true, replace: true });
};

const openModal = (item) => {
  selectedItem.value = item;
  showModal.value = true;
  isRejecting.value = false;
  rejectReason.value = '';
};

const closeModal = () => {
  showModal.value = false;
  selectedItem.value = null;
  isRejecting.value = false;
  rejectReason.value = '';
};

const processVerify = () => {
  if (!selectedItem.value) return;
  processing.value = true;

  router.post(route('admin.pengkinian-data.verify', selectedItem.value.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil', 'Pengkinian data telah disetujui.');
      closeModal();
    },
    onError: () => {
      alertError('Gagal', 'Terjadi kesalahan saat memverifikasi.');
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};

const processReject = () => {
  if (!selectedItem.value || !rejectReason.value.trim()) return;
  processing.value = true;

  router.post(route('admin.pengkinian-data.reject', selectedItem.value.id), {
    reason: rejectReason.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil Ditolak', 'Pengajuan pengkinian data telah ditolak.');
      closeModal();
    },
    onError: () => {
      alertError('Gagal', 'Terjadi kesalahan saat menolak pengajuan.');
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};

const documentUrl = (path) => route('personel.document.download', { path });
</script>
