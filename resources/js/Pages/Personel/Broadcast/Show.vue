<template>
  <AuthenticatedLayout>
    <template #header-title>Lembar Instruksi & Perintah Kegiatan</template>

    <div class="max-w-3xl space-y-6">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 bg-slate-50/50 border-b border-[#E2E8F0] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
          <div class="space-y-1">
            <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase bg-blue-50 text-[#2563EB] border border-blue-100">
              Kategori: {{ broadcast.category }}
            </span>
            <h3 class="text-base font-extrabold text-slate-800 mt-1.5">{{ broadcast.title }}</h3>
          </div>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-[#E2E8F0] bg-slate-50/20 text-xs">
          <div class="space-y-1">
            <p class="text-slate-400 font-bold uppercase text-[10px]">Waktu Agenda</p>
            <p class="font-bold text-slate-800">{{ formatDate(broadcast.event_date) }}</p>
            <p class="text-slate-500 font-medium">Pukul {{ broadcast.event_time }} WIB</p>
          </div>
          <div class="space-y-1">
            <p class="text-slate-400 font-bold uppercase text-[10px]">Lokasi Penugasan</p>
            <p class="font-bold text-slate-800 uppercase leading-relaxed">{{ broadcast.location }}</p>
          </div>
          <div class="space-y-1">
            <p class="text-slate-400 font-bold uppercase text-[10px]">Batas Konfirmasi</p>
            <p class="font-bold text-red-600">{{ formatDateTime(broadcast.deadline) }} WIB</p>
          </div>
        </div>

        <div class="p-6 space-y-2.5">
          <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nota Perintah / Detail Komando</h4>
          <p class="text-xs text-slate-600 leading-relaxed bg-slate-50/50 border border-[#E2E8F0] p-4 rounded-xl whitespace-pre-line">
            {{ broadcast.description }}
          </p>
        </div>
      </div>

      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] bg-slate-50/40">
          <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Lembar Konfirmasi Kehadiran Anggota</h4>
        </div>

        <form @submit.prevent="submitResponse" class="p-6 space-y-5 text-xs">
          <div>
            <label class="block font-bold text-slate-500 uppercase mb-3">Status Kesiapan Anda</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label :class="form.status === 'HADIR' ? 'border-[#2563EB] bg-blue-50/30 ring-1 ring-[#2563EB]' : 'border-[#E2E8F0]'" class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition select-none">
                <input type="radio" v-model="form.status" value="HADIR" class="w-4 h-4 text-[#2563EB]" required />
                <div>
                  <p class="font-bold text-slate-800">Hadir</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Sanggup menghadiri penugasan tepat waktu.</p>
                </div>
              </label>

              <label :class="form.status === 'TIDAK_HADIR' ? 'border-red-500 bg-red-50/20 ring-1 ring-red-500' : 'border-[#E2E8F0]'" class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition select-none">
                <input type="radio" v-model="form.status" value="TIDAK_HADIR" class="w-4 h-4 text-red-600" required />
                <div>
                  <p class="font-bold text-slate-800">Tidak Hadir</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Berhalangan hadir atau berhalangan dinas.</p>
                </div>
              </label>
            </div>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" v-model="form.permit_letter" id="permit_letter" class="w-4 h-4 text-[#2563EB] border-[#E2E8F0] rounded focus:ring-[#2563EB] cursor-pointer" />
            <label for="permit_letter" class="text-xs font-bold text-slate-500 uppercase cursor-pointer select-none">Perlu surat izin instansi</label>
          </div>

          <div>
            <label class="block font-bold text-slate-500 uppercase mb-2">Alasan / Catatan Keterangan Tambahan</label>
            <textarea 
              v-model="form.notes" 
              rows="3" 
              class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] leading-relaxed transition" 
              placeholder="Tuliskan keterangan jika Anda mengajukan izin dinas, atau logistik pendukung yang disiapkan..."
            ></textarea>
          </div>

          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-[#E2E8F0]">
            <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-[#2563EB] hover:bg-[#1E40AF] text-white font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer text-xs">
              Kirim Konfirmasi Kehadiran
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({
  broadcast: Object,
  existing_response: Object
});

const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

// Ambil status lama jika personel sudah pernah mengisi sebelumnya
const form = useForm({
  status: props.existing_response?.status_attendance || 'HADIR',
  notes: props.existing_response?.notes || '',
  permit_letter: props.existing_response?.permit_letter === 'YA'
});

const submitResponse = () => {
  form.post(route('personel.broadcast.respond', props.broadcast.uuid), {
    preserveScroll: true,
    onBefore: () => showLoadingProgress('Mengirimkan data presensi jajaran...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Berhasil Dikirim', 'Respons presensi mobilisasi Anda telah tercatat di pusat data.');
    },
    onError: () => {
      closeLoading();
      alertError('Gagal Mengirim', 'Terjadi kesalahan sistem pangkalan data.');
    }
  });
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return '-';
  return new Date(dateTimeString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });
};
</script>