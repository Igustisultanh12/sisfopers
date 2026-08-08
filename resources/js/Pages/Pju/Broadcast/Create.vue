<template>
  <AuthenticatedLayout>
    <template #header-title>Buat Broadcast Kegiatan Pembina Matra</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto space-y-6">

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Kirim Broadcast Kegiatan Jajaran</h1>
          <p class="text-xs text-slate-400 mt-1">Penyampaian pengumuman tugas & siaga ke WhatsApp personel Komcad jajaran.</p>
        </div>
        <Link
          :href="route('pju.broadcast.index')"
          class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer"
        >
          Kembali
        </Link>
      </div>

      <div class="bg-white border border-slate-200/80 p-6 sm:p-8 rounded-3xl shadow-sm">
        <form @submit.prevent="submitBroadcast" class="space-y-5 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Judul Broadcast Kegiatan</label>
            <input
              v-model="form.title"
              type="text"
              required
              placeholder="Contoh: Apel Siaga & Verifikasi Data Personel Matra"
              class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] text-sm"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Target Penerima</label>
              <select
                v-model="form.target_type"
                required
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] bg-white font-medium"
              >
                <option value="MATRA">Khusus Matra Pembina</option>
                <option value="ALL">Seluruh Personel Komcad</option>
                <option value="ANGKATAN">Per Tahun Angkatan</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Matra Sasaran</label>
              <select
                v-model="form.matra"
                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] bg-white font-medium"
              >
                <option :value="userMatra || 'AD'">TNI {{ userMatra || 'AD' }}</option>
                <option value="AL">TNI AL</option>
                <option value="AU">TNI AU</option>
              </select>
            </div>
          </div>

          <div v-if="form.target_type === 'ANGKATAN'">
            <label class="block font-bold text-slate-700 mb-1">Tahun Angkatan</label>
            <input
              v-model="form.angkatan"
              type="text"
              placeholder="Contoh: 2026"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB]"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Isi Pesan Broadcast & Instruksi</label>
            <textarea
              v-model="form.content"
              rows="6"
              required
              placeholder="Tuliskan petunjuk, instruksi kegiatan, tempat, dan waktu secara rinci..."
              class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] text-sm leading-relaxed"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <Link
              :href="route('pju.broadcast.index')"
              class="px-5 py-3 text-slate-500 hover:text-slate-800 font-bold cursor-pointer"
            >
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-3 bg-[#2563EB] hover:bg-blue-600 disabled:opacity-60 text-white font-bold rounded-xl shadow-md transition cursor-pointer"
            >
              Kirim Broadcast via WhatsApp
            </button>
          </div>
        </form>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  userMatra: String,
});

const form = useForm({
  title: '',
  content: '',
  target_type: 'MATRA',
  matra: props.userMatra || 'AD',
  angkatan: '',
});

function submitBroadcast() {
  form.post(route('pju.broadcast.store'));
}
</script>
