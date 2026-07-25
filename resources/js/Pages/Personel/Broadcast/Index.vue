<template>
  <AuthenticatedLayout>
    <template #header-title>Pusat Mobilisasi & Kegiatan Komcad</template>

    <!-- ROMEI Style Flat Table Card -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40">
        <h3 class="text-base font-bold text-slate-800">Daftar Perintah Instruksi & Kegiatan</h3>
        <p class="text-xs text-slate-400 mt-0.5">Pantau instruksi latihan, apel, dan mobilisasi pertahanan resmi dari pusat komando.</p>
      </div>

      <div class="overflow-x-auto w-full">
        <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none">
            <th class="p-4">Detail Perintah / Kegiatan</th>
            <th class="p-4">Tanggal Pelaksanaan</th>
            <th class="p-4">Status Konfirmasi Anda</th>
            <th class="p-4 text-right">Tindakan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
          <tr v-for="item in broadcasts.data" :key="item.id" class="hover:bg-slate-50/30 transition">
            <td class="p-4">
              <div class="font-bold text-slate-800">{{ item.title }}</div>
              <div class="text-xs text-slate-400 mt-1 max-w-md truncate">{{ item.content }}</div>
            </td>
            <td class="p-4 text-slate-700 font-medium text-xs">
              {{ formatDate(item.event_date || item.created_at) }}
            </td>
            <td class="p-4">
              <!-- Jika sudah merespon, tampilkan badge statusnya -->
              <span v-if="item.responses?.length > 0" :class="item.responses[0].status === 'HADIR' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'" class="px-2.5 py-0.5 rounded-lg text-xs font-bold border">
                {{ item.responses[0].status === 'HADIR' ? 'Hadir' : 'Tidak Hadir' }}
              </span>
              <span v-else class="text-xs text-slate-400 italic">Belum Mengonfirmasi</span>
            </td>
            <td class="p-4 text-right">
              <button @click="openResponseModal(item)" class="text-xs font-bold text-[#2563EB] hover:underline bg-[#2563EB]/5 px-3 py-1.5 rounded-xl transition cursor-pointer border border-transparent hover:border-blue-100">
                Isi Presensi Kehadiran
              </button>
            </td>
          </tr>
          <tr v-if="broadcasts.data.length === 0">
            <td colspan="4" class="p-12 text-center text-slate-400 text-sm">Belum ada perintah instruksi kegiatan yang diterbitkan untuk matra Anda.</td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- MODAL POPUP KONFIRMASI HADIR (ROMEI MODAL STYLE) -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl max-w-md w-full p-6 shadow-xl space-y-5">
        <div>
          <h3 class="text-base font-bold text-slate-800">Konfirmasi Lembar Presensi</h3>
          <p class="text-xs text-slate-400 mt-0.5">Perintah: <span class="font-bold text-slate-600">{{ selectedBroadcast?.title }}</span></p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pernyataan Sikap / Kehadiran</label>
            <select v-model="form.status" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium">
              <option value="HADIR">Hadir</option>
              <option value="TIDAK_HADIR">Tidak Hadir</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="form.permit_letter" id="permit_letter" class="w-4 h-4 text-[#2563EB] border-[#E2E8F0] rounded focus:ring-[#2563EB] cursor-pointer" />
            <label for="permit_letter" class="text-xs font-bold text-slate-500 uppercase cursor-pointer select-none">Perlu surat izin instansi</label>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Alasan / Catatan Tambahan</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="Masukkan keterangan dinas luar, sakit, atau kesiapan kelengkapan logistik..."></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-2.5 pt-2 border-t border-[#E2E8F0]">
          <button @click="showModal = false" class="px-4 py-2 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">Batal</button>
          <button @click="submitResponse" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer">Kirim Status</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({ broadcasts: Object });
const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

const showModal = ref(false);
const selectedBroadcast = ref(null);

const form = useForm({
  status: 'HADIR',
  notes: '',
  permit_letter: false
});

const openResponseModal = (broadcast) => {
  selectedBroadcast.value = broadcast;
  // Jika sudah pernah merespon sebelumnya, muat datanya kembali ke form popup
  if (broadcast.responses && broadcast.responses.length > 0) {
    form.status = broadcast.responses[0].status_attendance || broadcast.responses[0].status || 'HADIR';
    form.notes = broadcast.responses[0].notes || '';
    form.permit_letter = broadcast.responses[0].permit_letter === 'YA';
  } else {
    form.status = 'HADIR';
    form.notes = '';
    form.permit_letter = false;
  }
  showModal.value = true;
};

const submitResponse = () => {
  showModal.value = false;
  form.post(route('personel.broadcast.respond', selectedBroadcast.value.uuid), {
    preserveScroll: true,
    onBefore: () => showLoadingProgress('Mengirimkan Lembar Pernyataan...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Berhasil Dikirim', 'Respons presensi mobilisasi Anda telah dicatat oleh sistem pusat.');
    },
    onError: () => {
      closeLoading();
      alertError('Gagal Mengirim', 'Terjadi gangguan otorisasi jaringan.');
    }
  });
};

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
</script>