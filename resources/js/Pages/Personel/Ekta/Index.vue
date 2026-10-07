<template>
  <AuthenticatedLayout>
    <template #header-title>Kartu Tanda Anggota Elektronik (E-KTA)</template>

    <div class="space-y-6 max-w-4xl mx-auto py-6">
      <!-- Banner Informasi Pengembang -->
      <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 sm:p-8 text-center space-y-4 shadow-sm">
        <div class="w-16 h-16 bg-amber-100 text-amber-700 rounded-2xl flex items-center justify-center mx-auto border border-amber-200">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>

        <div>
          <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-200/80 text-amber-900 border border-amber-300">
            Fase Penyiapan Sistem
          </span>
          <h2 class="text-xl font-black text-slate-800 mt-2">
            Mohon Maaf Fitur Ini Dalam Tahap Pengembangan
          </h2>
          <p class="text-xs text-slate-600 mt-2 max-w-lg mx-auto leading-relaxed">
            Layanan penerbitan dan unduhan mandiri Kartu Tanda Anggota Elektronik (E-KTA) Komponen Cadangan saat ini sedang dalam proses finalisasi integrasi digital oleh Administrator Mabes Komcad RI.
          </p>
        </div>

        <div class="pt-2 flex justify-center gap-3">
          <Link
            :href="route('personel.dashboard')"
            class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
          >
            Kembali ke Dashboard Saya
          </Link>
        </div>
      </div>

      <!-- Preview Siluet Kartu Komcad Digital -->
      <div class="relative rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center opacity-70 bg-slate-50">
        <div class="max-w-md mx-auto space-y-2">
          <div class="w-12 h-12 bg-slate-200 rounded-xl mx-auto flex items-center justify-center text-slate-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
          </div>
          <h4 class="text-sm font-bold text-slate-700">Digital Smart Card ID Komcad</h4>
          <p class="text-xs text-slate-400">Kartu Tanda Anggota digital Anda akan otomatis dapat diakses di menu ini setelah proses otorisasi Mabes selesai.</p>
        </div>
      </div>
    </div>

    <!-- Modal Popup Otomatis: "Mohon Maaf fitur ini dalam tahap pengembangan" -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-fade-in">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-center space-y-5 animate-scale-up">
        <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mx-auto border border-amber-200/60">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
          <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">Tahap Pengembangan</span>
          <h3 class="text-lg font-black text-slate-800 mt-2">Mohon Maaf fitur ini dalam tahap pengembangan</h3>
          <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Layanan penerbitan dan unduhan Kartu Tanda Anggota Elektronik (E-KTA) Komponen Cadangan saat ini sedang dalam proses finalisasi integrasi digital oleh Administrator Mabes Komcad RI.
          </p>
        </div>
        <div class="pt-2 flex flex-col gap-2">
          <button @click="showModal = false" class="w-full py-3 px-4 bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-500/20 transition cursor-pointer">
            Saya Mengerti
          </button>
          <Link :href="route('personel.dashboard')" class="w-full py-2.5 px-4 text-slate-500 hover:text-slate-800 font-bold text-xs rounded-xl transition text-center">
            Kembali ke Dashboard
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  personel: Object,
  is_under_development: Boolean,
  development_notice: String,
  development_description: String,
});

const showModal = ref(true);
</script>
