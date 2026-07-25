<template>
  <AuthenticatedLayout>
    <template #sidebar-menu>
      <Link :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard</Link>
      <Link :href="route('admin.personel.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Master Personel</Link>
      <Link :href="route('admin.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Broadcast Kegiatan</Link>
      <Link :href="route('admin.setting.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Pengaturan Sistem</Link>
    </template>

    <template #header-title>Ubah Komando & Mobilisasi Pasukan</template>

    <div class="max-w-2xl bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Amandemen Maklumat / Perintah Kegiatan</h3>
        <p class="text-xs text-slate-400 mt-0.5">Perubahan instruksi akan diperbarui pada halaman log lembar tugas personel yang bersangkutan.</p>
      </div>

      <form @submit.prevent="updateBroadcast" class="p-6 space-y-5 text-xs">
        
        <div>
          <label class="block font-bold text-slate-500 uppercase mb-2">Judul Komando / Kegiatan</label>
          <input 
            type="text" 
            v-model="form.title" 
            :class="form.errors.title ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" 
            class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" 
            placeholder="Contoh: Latihan Gabungan Integrasi" 
            required 
          />
          <div v-if="form.errors.title" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
            ⚠️ {{ form.errors.title }}
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-500 uppercase mb-2">Kategori Kegiatan</label>
            <select 
              v-model="form.category" 
              :class="form.errors.category ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
              class="w-full px-4 py-2 border bg-white rounded-xl text-sm outline-none text-slate-700 font-medium transition"
              required
            >
              <option value="">Pilih Kategori Kegiatan--</option>
              <option value="latihan">LATIHAN GABUNGAN / TEKNIS</option>
              <option value="apel">APEL KESIAPSIAGAAN / KEKUATAN</option>
              <option value="mobilisasi">MOBILISASI / OPERASIONAL AKTIF</option>
              <option value="pengumuman">PENGUMUMAN / INFORMASI UMUM</option>
            </select>
            <div v-if="form.errors.category" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
              ⚠️ {{ form.errors.category }}
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-500 uppercase mb-2">Jenis Target Sasaran</label>
            <select 
              v-model="form.target_type" 
              :class="form.errors.target_type ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
              class="w-full px-4 py-2 border bg-white rounded-xl text-sm outline-none text-slate-700 font-medium transition"
              required
              @change="handleTargetTypeChange"
            >
              <option value="ALL">SEMUA PERSONEL (GABUNGAN)</option>
              <option value="MATRA">BERDASARKAN MATRA</option>
              <option value="ANGKATAN">BERDASARKAN ANGKATAN</option>
            </select>
            <div v-if="form.errors.target_type" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
              ⚠️ {{ form.errors.target_type }}
            </div>
          </div>
        </div>

        <div v-if="form.target_type === 'MATRA'" class="animate-fade-in">
          <label class="block font-bold text-slate-500 uppercase mb-2">Pilih Matra Spesifik</label>
          <select v-model="form.target_value" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none text-slate-700 font-medium transition" required>
            <option value="AD">TNI AD (ANGKATAN DARAT)</option>
            <option value="AL">TNI AL (ANGKATAN LAUT)</option>
            <option value="AU">TNI AU (ANGKATAN UDARA)</option>
          </select>
        </div>

        <div v-if="form.target_type === 'ANGKATAN'" class="animate-fade-in">
          <label class="block font-bold text-slate-500 uppercase mb-2">Tahun Angkatan Komcad</label>
          <input type="text" v-model="form.target_value" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none transition" placeholder="Contoh: 2022, 2025" required />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-500 uppercase mb-2">Tanggal Pelaksanaan</label>
            <input 
              type="date" 
              v-model="form.event_date" 
              :class="form.errors.event_date ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
              class="w-full px-4 py-2 border bg-white rounded-xl text-sm outline-none text-slate-700 font-medium transition" 
              required 
            />
            <div v-if="form.errors.event_date" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
              ⚠️ {{ form.errors.event_date }}
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-500 uppercase mb-2">Waktu / Jam Mulai (WIB)</label>
            <input 
              type="time" 
              v-model="form.event_time" 
              :class="form.errors.event_time ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
              class="w-full px-4 py-2 border bg-white rounded-xl text-sm outline-none text-slate-700 font-medium transition" 
              required 
            />
            <div v-if="form.errors.event_time" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
              ⚠️ {{ form.errors.event_time }}
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-bold text-slate-500 uppercase mb-2">Lokasi / Tempat Kegiatan</label>
            <input 
              type="text" 
              v-model="form.location" 
              :class="form.errors.location ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
              class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" 
              placeholder="Contoh: Lapangan Mako Lantamal V Surabaya" 
              required 
            />
            <div v-if="form.errors.location" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
              ⚠️ {{ form.errors.location }}
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-500 uppercase mb-2">Batas Akhir Respon (Deadline)</label>
            <input 
              type="datetime-local" 
              v-model="form.deadline" 
              :class="form.errors.deadline ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
              class="w-full px-4 py-2 border bg-white rounded-xl text-sm outline-none text-slate-700 font-medium transition" 
              required 
            />
            <div v-if="form.errors.deadline" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
              ⚠️ {{ form.errors.deadline }}
            </div>
          </div>
        </div>

        <div>
          <label class="block font-bold text-slate-500 uppercase mb-2">Isi Konten Perintah / Maklumat Dokumentasi</label>
          <textarea 
            v-model="form.description" 
            rows="5" 
            :class="form.errors.description ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
            class="w-full px-4 py-2 border rounded-xl text-sm outline-none leading-relaxed transition" 
            placeholder="Tuliskan detail instruksi, lokasi titik kumpul koordinat, pakaian dinas lapangan (PDL) yang digunakan, serta perlengkapan logistik wajib bawa..." 
            required
          ></textarea>
          <div v-if="form.errors.description" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
            ⚠️ {{ form.errors.description }}
          </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-[#E2E8F0]">
          <Link :href="route('admin.broadcast.index')" class="px-4 py-2 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 font-semibold rounded-xl transition cursor-pointer">Batal</Link>
          <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({ broadcast: Object });
const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

// Format datetime-local ke format yang didukung input HTML (YYYY-MM-DDTHH:MM)
const formatDateTimeLocal = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const pad = (num) => String(num).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const form = useForm({
  title: props.broadcast.title || '',
  category: props.broadcast.category || '', 
  event_date: props.broadcast.event_date || '',
  event_time: props.broadcast.event_time || '',
  location: props.broadcast.location || '',
  description: props.broadcast.description || '',
  deadline: formatDateTimeLocal(props.broadcast.deadline),
  target_type: props.broadcast.target_type || 'ALL',
  target_value: props.broadcast.target_value || null
});

const handleTargetTypeChange = () => {
  if (form.target_type === 'ALL') {
    form.target_value = null;
  } else if (form.target_type === 'MATRA') {
    form.target_value = 'AL';
  } else {
    form.target_value = '';
  }
};

const updateBroadcast = () => {
  form.put(route('admin.broadcast.update', props.broadcast.uuid), {
    onBefore: () => showLoadingProgress('Menyimpan perubahan broadcast...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Berhasil Diperbarui', 'Maklumat kegiatan telah berhasil diperbarui dan disinkronkan.');
    },
    onError: (errors) => {
      closeLoading();
      const firstErrorKey = Object.keys(errors)[0];
      const errorMessage = errors[firstErrorKey] || 'Harap periksa kelengkapan parameter isian form.';
      alertError('Gagal Memperbarui', errorMessage);
    }
  });
};
</script>
