<template>
  <AuthenticatedLayout>
    <template #header-title>Dashboard Executive Monitoring PJU</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

      <!-- Executive Welcome Card -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 p-6 sm:p-8 rounded-3xl text-white shadow-xl space-y-3 relative overflow-hidden">
        <div class="relative z-10">
          <span class="text-[10px] font-extrabold uppercase tracking-widest bg-blue-500/20 text-blue-300 border border-blue-400/30 px-3 py-1 rounded-lg inline-block">
            {{ user.jabatan_pju || 'PEJABAT UTAMA KOMPOSISI KEKUATAN' }}
          </span>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight mt-2">
            Selamat Datang, {{ user.personel?.full_name || user.username }}
          </h1>
          <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
            Portal Pemantauan Eksekutif Kekuatan Personel Komponen Cadangan TNI. 
            <span v-if="user.matra">Terlingkup khusus untuk Jajaran TNI {{ user.matra }}.</span>
            <span v-else>Memiliki wewenang pemantauan nasional penuh seluruh Matra.</span>
          </p>
        </div>
      </div>

      <!-- Executive Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl shadow-xs space-y-2">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total Kekuatan Personel</span>
          <p class="text-2xl font-black text-slate-800">{{ stats.total_personel }}</p>
          <p class="text-[10px] text-slate-500 font-semibold">Personel Aktif Komcad</p>
        </div>

        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl shadow-xs space-y-2">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Jajaran TNI AD</span>
          <p class="text-2xl font-black text-emerald-700">{{ stats.personel_ad }}</p>
          <p class="text-[10px] text-emerald-600 font-semibold">Personel Matra Darat</p>
        </div>

        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl shadow-xs space-y-2">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Jajaran TNI AL</span>
          <p class="text-2xl font-black text-blue-700">{{ stats.personel_al }}</p>
          <p class="text-[10px] text-blue-600 font-semibold">Personel Matra Laut</p>
        </div>

        <div class="bg-white border border-slate-200/80 p-5 rounded-2xl shadow-xs space-y-2">
          <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Jajaran TNI AU</span>
          <p class="text-2xl font-black text-sky-700">{{ stats.personel_au }}</p>
          <p class="text-[10px] text-sky-600 font-semibold">Personel Matra Udara</p>
        </div>
      </div>

      <!-- Shortcut Cards for PJU -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <Link
          :href="route('pju.personel.index')"
          class="bg-white border border-slate-200/80 hover:border-blue-300 p-6 rounded-2xl shadow-xs transition group cursor-pointer space-y-3"
        >
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
            📋
          </div>
          <div>
            <h3 class="font-extrabold text-slate-800 group-hover:text-blue-600 transition text-sm">Master Personel Jajaran</h3>
            <p class="text-xs text-slate-400 mt-1">Pantau seluruh data fisik, sinyalmen, dan riwayat pendidikan personel.</p>
          </div>
        </Link>

        <Link
          :href="route('pju.report.index')"
          class="bg-white border border-slate-200/80 hover:border-blue-300 p-6 rounded-2xl shadow-xs transition group cursor-pointer space-y-3"
        >
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
            📊
          </div>
          <div>
            <h3 class="font-extrabold text-slate-800 group-hover:text-blue-600 transition text-sm">Laporan & Pelaporan Dokumen</h3>
            <p class="text-xs text-slate-400 mt-1">Unduh laporan resmi PDF & Excel berstandar dinas militer.</p>
          </div>
        </Link>

        <Link
          :href="route('pju.broadcast.index')"
          class="bg-white border border-slate-200/80 hover:border-blue-300 p-6 rounded-2xl shadow-xs transition group cursor-pointer space-y-3"
        >
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
            📢
          </div>
          <div>
            <h3 class="font-extrabold text-slate-800 group-hover:text-blue-600 transition text-sm">Broadcast Kegiatan & Kesiapsiagaan</h3>
            <p class="text-xs text-slate-400 mt-1">Pantau & kirim pesan siaga kegiatan ke personel jajaran matra.</p>
          </div>
        </Link>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  user: Object,
  stats: Object,
});
</script>
