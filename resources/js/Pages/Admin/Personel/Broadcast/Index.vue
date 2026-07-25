<template>
  <AuthenticatedLayout>
    <template #sidebar-menu>
      <Link :href="route('personel.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard Utama</Link>
      <Link :href="route('personel.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Broadcast Kegiatan</Link>
    </template>

    <template #header-title>Lembar Broadcast & Instruksi Penugasan</template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Sisi Kiri: List Agenda Masuk -->
      <div class="space-y-4">
        <div v-for="act in activities" :key="act.id" @click="selectActivity(act)" class="p-5 bg-white border border-[#E2E8F0] rounded-2xl shadow-sm cursor-pointer hover:border-[#2563EB] transition-all duration-200" :class="{'border-[#2563EB] ring-2 ring-[#2563EB]/5': selectedActivity?.id === act.id}">
          <div class="flex justify-between items-start">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-[#2563EB] border border-blue-100">{{ act.category }}</span>
            <span class="text-xs text-slate-400 font-medium">{{ formatDate(act.event_date) }}</span>
          </div>
          <h4 class="text-sm font-bold text-slate-800 mt-2.5 line-clamp-1">{{ act.title }}</h4>
          <p class="text-xs text-slate-400 mt-1 truncate">Lokasi: {{ act.location }}</p>
        </div>
      </div>

      <!-- Sisi Kanan: Detail & Form Konfirmasi Komitmen Kehadiran -->
      <div class="bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-sm h-fit">
        <div v-if="selectedActivity">
          <h3 class="text-base font-bold text-slate-800">{{ selectedActivity.title }}</h3>
          <div class="my-4 p-4 bg-slate-50 border border-[#E2E8F0] rounded-xl text-xs space-y-2 text-slate-600">
            <p><strong>Waktu Pelaksanaan:</strong> Pukul {{ selectedActivity.event_time }} WIB</p>
            <p><strong>Lokasi Penugasan:</strong> {{ selectedActivity.location }}</p>
            <p><strong>Batas Pengisian (Deadline):</strong> {{ new Date(selectedActivity.deadline).toLocaleString('id-ID') }} WIB</p>
          </div>
          <p class="text-xs text-slate-500 leading-relaxed mb-6 border-b pb-4">{{ selectedActivity.description }}</p>

          <!-- Form Konfirmasi Absensi Absensi -->
          <form @submit.prevent="submitResponse" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pernyataan Komitmen Kehadiran</label>
              <select v-model="form.status_attendance" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB]">
                <option value="HADIR">Saya Hadir Mengikuti Kegiatan</option>
                <option value="TIDAK_HADIR">Tidak Hadir (Tanpa Keterangan)</option>
                <option value="IZIN">Perlu Surat Izin Resmi (Berhalangan)</option>
              </select>
            </div>

            <div v-if="form.status_attendance === 'IZIN'">
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Unggah Berkas Bukti Izin (PDF/JPG, Max 2MB)</label>
              <input type="file" @input="form.permit_letter = $event.target.files[0]" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100" required />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Catatan Tambahan / Alasan</label>
              <textarea v-model="form.notes" rows="2" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="Masukkan keterangan tambahan jika diperlukan..."></textarea>
            </div>

            <button type="submit" :disabled="form.processing" class="w-full py-2 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-50 text-white font-bold rounded-xl text-xs transition shadow-md shadow-blue-500/10">
              Kirim Lembar Konfirmasi
            </button>
          </form>
        </div>
        <div v-else class="text-center py-20 text-xs text-slate-400">
          Silakan pilih daftar pengumuman broadcast di sebelah kiri untuk meninjau informasi penugasan.
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useSwal } from '@/Composables/useSwal';

defineProps({ activities: Array });
const { showLoadingProgress, updateLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

const selectedActivity = ref(null);

const form = useForm({
  status_attendance: 'HADIR',
  notes: '',
  permit_letter: null
});

const selectActivity = (act) => {
  selectedActivity.value = act;
  // Jika sudah pernah merespon, isi nilai awal form dengan respon yang ada
  if (act.responses && act.responses.length > 0) {
    form.status_attendance = act.responses[0].status_attendance;
    form.notes = act.responses[0].notes || '';
  } else {
    form.status_attendance = 'HADIR';
    form.notes = '';
  }
};

const submitResponse = () => {
  form.post(route('personel.broadcast.respond', selectedActivity.value.uuid), {
    forceFormData: true,
    onBefore: () => showLoadingProgress('Mengamankan Data Komitmen...'),
    onProgress: (p) => { if (p.percentage) updateLoadingProgress(p.percentage); },
    onSuccess: () => {
      closeLoading();
      alertSuccess('Konfirmasi Terkirim', 'Pernyataan kehadiran Anda berhasil disimpan ke server instansi.');
    },
    onError: () => { closeLoading(); alertError('Gagal Mengirim', 'Batas waktu respon mungkin telah ditutup.'); }
  });
};

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
</script>