<template>
  <AuthenticatedLayout>
    <template #header-title>{{ $page.props.auth.user.role.name === 'kordinator_matra' ? 'Pusat Komando Kegiatan Matra' : 'Pusat Komando Kegiatan Angkatan' }}</template>

    <div class="max-w-2xl bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">{{ $page.props.auth.user.role.name === 'kordinator_matra' ? `Penerbitan Maklumat Kegiatan Matra TNI ${myProfile.matra}` : `Penerbitan Maklumat Kegiatan Angkatan ${myProfile.angkatan}` }}</h3>
        <p class="text-xs text-slate-400 mt-0.5">{{ $page.props.auth.user.role.name === 'kordinator_matra' ? 'Instruksi akan dikirimkan secara khusus ke seluruh anggota matra Anda dan notifikasi WA akan dipicu.' : 'Instruksi akan dikirimkan secara khusus ke seluruh anggota angkatan Anda dan notifikasi WA akan dipicu.' }}</p>
      </div>

      <form @submit.prevent="submitBroadcast" class="p-6 space-y-5 text-xs">
        
        <div>
          <label class="block font-bold text-slate-500 uppercase mb-2">Judul Komando / Kegiatan</label>
          <input 
            type="text" 
            v-model="form.title" 
            :class="form.errors.title ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" 
            class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" 
            placeholder="Contoh: Latihan Taktis Matra Laut" 
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
            <label class="block font-bold text-slate-500 uppercase mb-2">Target Sasaran Anggota</label>
            <div class="px-4 py-2 bg-blue-50 border border-blue-100 rounded-xl text-sm text-[#2563EB] font-bold select-none h-[38px] flex items-center">
              {{ $page.props.auth.user.role.name === 'kordinator_matra' ? `Seluruh Anggota Matra TNI ${myProfile.matra === 'AD' ? 'Darat' : (myProfile.matra === 'AL' ? 'Laut' : 'Udara')}` : `Seluruh Anggota Komcad Angkatan ${myProfile.angkatan}` }}
            </div>
          </div>
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
              placeholder="Contoh: Lapangan Mako Armada II Surabaya" 
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
          <label class="block font-bold text-slate-500 uppercase mb-2">Isi Konten Perintah / Maklumat Kegiatan</label>
          <textarea 
            v-model="form.description" 
            rows="5" 
            :class="form.errors.description ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'"
            class="w-full px-4 py-2 border rounded-xl text-sm outline-none leading-relaxed transition" 
            placeholder="Tuliskan detail instruksi khusus..." 
            required
          ></textarea>
          <div v-if="form.errors.description" class="text-red-500 font-semibold mt-1.5 text-[11px] pl-1">
            ⚠️ {{ form.errors.description }}
          </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-[#E2E8F0]">
          <Link :href="route('kordinator.broadcast.index')" class="px-4 py-2 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 font-semibold rounded-xl transition cursor-pointer">Batal</Link>
          <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer">
            Publish & Sebarkan Komando
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

defineProps({
  myProfile: Object
});

const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

const form = useForm({
  title: '',
  category: '', 
  event_date: '',
  event_time: '',
  location: '',
  description: '',
  deadline: '',
});

const submitBroadcast = () => {
  form.post(route('kordinator.broadcast.store'), {
    onBefore: () => showLoadingProgress('Menyebarkan Komando & Mengirim WhatsApp Gateway...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Berhasil Disiarkan', 'Maklumat kegiatan baru telah terdistribusi.');
    },
    onError: (errors) => {
      closeLoading();
      const firstErrorKey = Object.keys(errors)[0];
      const errorMessage = errors[firstErrorKey] || 'Harap periksa kelengkapan parameter isian form.';
      alertError('Gagal Mengirim', errorMessage);
    }
  });
};
</script>
