<template>
  <AuthenticatedLayout>
    <template #header-title>Open Tiket Pengaduan & Layanan</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto space-y-6">
      
      <!-- Banner Header -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900 rounded-3xl p-6 text-white shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 relative overflow-hidden">
        <div class="space-y-1.5 z-10">
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-500/20 text-blue-300 border border-blue-400/30 uppercase tracking-widest">
            LAYANAN PENGADUAN MANDIRI
          </span>
          <h1 class="text-xl font-black tracking-tight text-white">Open Tiket Layanan Personel</h1>
          <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
            Ajukan permohonan penggantian pasfoto profil, perbaikan biodata pribadi, atau pengajuan cetak ulang KTA dengan penomoran pelaporan otomatis resmi.
          </p>
        </div>

        <button
          @click="showModal = true"
          class="z-10 flex items-center gap-2 px-5 py-3 bg-[#2563EB] hover:bg-blue-600 text-white text-xs font-extrabold rounded-2xl shadow-lg shadow-blue-500/30 transition cursor-pointer shrink-0"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Buat Tiket Baru
        </button>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-xs font-semibold shadow-xs">
        <span>{{ $page.props.flash.success }}</span>
      </div>
      <div v-if="$page.props.flash?.error" class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-xs font-semibold shadow-xs">
        <span>{{ $page.props.flash.error }}</span>
      </div>

      <!-- Daftar Tiket Saya -->
      <div class="bg-white border border-[#E2E8F0] rounded-3xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] bg-slate-50/60 flex items-center justify-between">
          <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Riwayat Tiket Pengaduan Saya</h3>
          <span class="text-xs font-bold text-slate-500 bg-slate-200/60 px-2.5 py-1 rounded-xl">
            Total: {{ tickets.length }} Tiket
          </span>
        </div>

        <!-- Tabel Tampilan Desktop -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider whitespace-nowrap select-none">
                <th class="p-4">Nomor Tiket</th>
                <th class="p-4">Kategori Layanan</th>
                <th class="p-4">Tanggal Pengajuan</th>
                <th class="p-4">Keterangan / Alasan</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Lampiran</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E8F0] text-xs text-slate-600">
              <tr v-for="t in tickets" :key="t.id" class="hover:bg-slate-50/40 transition">
                <td class="p-4 whitespace-nowrap">
                  <span class="font-mono font-bold text-[#2563EB] bg-blue-50 px-2 py-1 rounded-lg border border-blue-100/60 text-[11px]">
                    {{ t.ticket_number }}
                  </span>
                </td>
                <td class="p-4 whitespace-nowrap">
                  <span class="font-bold text-slate-800">
                    {{ formatCategory(t.category) }}
                  </span>
                </td>
                <td class="p-4 whitespace-nowrap text-slate-500 font-medium">
                  {{ formatDate(t.created_at) }}
                </td>
                <td class="p-4 max-w-xs truncate text-slate-700">
                  {{ t.description || '-' }}
                </td>
                <td class="p-4 whitespace-nowrap">
                  <span :class="statusBadgeClass(t.status)" class="px-2.5 py-1 rounded-xl text-[10px] font-bold border inline-block whitespace-nowrap">
                    {{ formatStatus(t.status) }}
                  </span>
                </td>
                <td class="p-4 text-right whitespace-nowrap">
                  <a
                    v-if="t.attachment_path"
                    :href="`/documents/private-stream?path=${encodeURIComponent(t.attachment_path)}`"
                    target="_blank"
                    class="text-[#2563EB] font-bold hover:underline bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 text-[11px] inline-flex items-center gap-1"
                  >
                    Lihat Berkas
                  </a>
                  <span v-else class="text-slate-300">-</span>
                </td>
              </tr>
              <tr v-if="tickets.length === 0">
                <td colspan="6" class="p-12 text-center text-slate-400 italic">
                  Belum ada tiket pengaduan yang diajukan. Klik "Buat Tiket Baru" untuk mengajukan permohonan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Daftar Kartu Tampilan HP -->
        <div class="md:hidden divide-y divide-slate-100">
          <div v-for="t in tickets" :key="t.id" class="p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="font-mono font-bold text-[#2563EB] bg-blue-50 px-2 py-0.5 rounded border border-blue-100 text-[11px]">
                {{ t.ticket_number }}
              </span>
              <span :class="statusBadgeClass(t.status)" class="px-2 py-0.5 rounded-lg text-[9px] font-bold border">
                {{ formatStatus(t.status) }}
              </span>
            </div>
            <p class="text-xs font-bold text-slate-800">{{ formatCategory(t.category) }}</p>
            <p class="text-[11px] text-slate-500 line-clamp-2">{{ t.description || '-' }}</p>
            <div class="flex items-center justify-between text-[10px] text-slate-400 border-t border-slate-100 pt-2">
              <span>{{ formatDate(t.created_at) }}</span>
              <a
                v-if="t.attachment_path"
                :href="`/documents/private-stream?path=${encodeURIComponent(t.attachment_path)}`"
                target="_blank"
                class="text-[#2563EB] font-bold hover:underline"
              >
                📎 Lihat Berkas
              </a>
            </div>
          </div>
          <div v-if="tickets.length === 0" class="p-8 text-center text-slate-400 text-xs italic">
            Belum ada tiket pengaduan.
          </div>
        </div>

      </div>

    </div>

    <!-- MODAL FORM OPEN TIKET BARU -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white border border-slate-200 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 my-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="text-base font-extrabold text-slate-800">Form Open Tiket Layanan</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Sistem akan meng-generate nomor tiket pelaporan secara otomatis.</p>
          </div>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1 cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitTicket" class="space-y-4">
          <!-- Kategori Tiket -->
          <div>
            <label class="block text-xs font-extrabold text-slate-600 uppercase mb-1.5">Kategori Pengajuan / Layanan</label>
            <select
              v-model="form.category"
              class="w-full px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs font-bold outline-none focus:border-[#2563EB] text-slate-700"
              required
            >
              <option value="UBAH_FOTO">Pengajuan Ubah Pasfoto Profil</option>
              <option value="UBAH_DATA">Pengajuan Perubahan Biodata / Data Pribadi</option>
              <option value="CETAK_KTA">Pengajuan Cetak Ulang KTA</option>
            </select>
          </div>

          <!-- QUEST TEXT / ALASAN KETERANGAN -->
          <div v-if="form.category === 'UBAH_DATA' || form.category === 'CETAK_KTA' || form.category === 'UBAH_FOTO'">
            <label class="block text-xs font-extrabold text-slate-600 uppercase mb-1.5">
              {{ form.category === 'UBAH_FOTO' ? 'Catatan Tambahan (Opsional)' : 'Kotak Keterangan & Alasan Pengajuan' }}
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full p-3 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB] text-slate-700 leading-relaxed"
              :placeholder="form.category === 'UBAH_DATA' ? 'Tuliskan secara jelas data apa saja yang ingin diubah (contoh: Alamat domisili, NIK, Status) beserta alasannya...' : (form.category === 'CETAK_KTA' ? 'Jelaskan alasan pengajuan cetak ulang KTA (contoh: KTA Hilang / Rusak / Perubahan Pangkat)...' : 'Catatan opsional untuk admin...')"
              :required="form.category !== 'UBAH_FOTO'"
            ></textarea>
            <div v-if="form.errors.description" class="text-red-500 text-[11px] mt-1 font-semibold">{{ form.errors.description }}</div>
          </div>

          <!-- FILE UPLOAD BASED ON CATEGORY -->
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
            <label class="block text-xs font-extrabold text-slate-700 uppercase">
              {{ form.category === 'UBAH_FOTO' ? 'Unggah Pasfoto Baru (Wajib Image JPG/PNG max 2MB)' : (form.category === 'CETAK_KTA' ? 'Dokumen Pelengkap (Surat Kehilangan Kepolisian / Foto KTA Rusak)' : 'Unggah Dokumen Pendukung (Opsional)') }}
            </label>
            <input
              type="file"
              @change="handleFileChange"
              class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 cursor-pointer"
              :accept="form.category === 'UBAH_FOTO' ? 'image/jpeg,image/png' : 'image/jpeg,image/png,application/pdf'"
              :required="form.category === 'UBAH_FOTO'"
            />
            <p class="text-[10px] text-slate-400 italic">
              {{ form.category === 'UBAH_FOTO' ? 'Foto baru ini akan menggantikan foto profil lama Anda di server secara permanen jika disetujui.' : 'Mendukung format PDF, JPG, PNG hingga 5MB.' }}
            </p>
            <div v-if="form.errors.attachment" class="text-red-500 text-[11px] mt-1 font-semibold">{{ form.errors.attachment }}</div>
          </div>

          <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-600 disabled:opacity-60 text-white text-xs font-bold rounded-xl shadow-md transition cursor-pointer"
            >
              Kirim Tiket Pengaduan
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
import { ref } from 'vue';

const props = defineProps({
  personel: Object,
  tickets: Array,
});

const showModal = ref(false);

const form = useForm({
  category: 'UBAH_FOTO',
  description: '',
  attachment: null,
});

function handleFileChange(e) {
  form.attachment = e.target.files[0] || null;
}

function submitTicket() {
  form.post(route('personel.tickets.store'), {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    },
  });
}

function formatCategory(cat) {
  const map = {
    UBAH_FOTO: 'Pengajuan Ubah Pasfoto',
    UBAH_DATA: 'Pengajuan Perubahan Biodata',
    CETAK_KTA: 'Pengajuan Cetak Ulang KTA',
  };
  return map[cat] || cat;
}

function formatStatus(st) {
  const map = {
    DIPROSES: 'DIPROSES',
    DISETUJUI: 'DISETUJUI',
    DITOLAK: 'DITOLAK',
    SELESAI: 'SELESAI',
  };
  return map[st] || st;
}

function statusBadgeClass(st) {
  switch (st) {
    case 'DISETUJUI':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'SELESAI':
      return 'bg-blue-50 text-blue-700 border-blue-200';
    case 'DITOLAK':
      return 'bg-red-50 text-red-700 border-red-200';
    default:
      return 'bg-amber-50 text-amber-700 border-amber-200';
  }
}

function formatDate(dt) {
  if (!dt) return '-';
  const d = new Date(dt);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
