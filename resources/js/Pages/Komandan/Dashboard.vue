<template>
  <AuthenticatedLayout>
    <template #sidebar-menu>
      <Link :href="route('komandan.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Dashboard Utama</Link>
      <Link :href="route('komandan.report.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Laporan Instansial</Link>
    </template>

    <template #header-title>Panel Pengawasan Komandan Satuan</template>

    <!-- Header Sambutan Instansi -->
    <div class="mb-8 bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-sm flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-slate-800">Selamat Datang, Komandan</h2>
        <p class="text-xs text-slate-400 mt-1">Anda memiliki hak akses pengawasan penuh terhadap kekuatan personel Komponen Cadangan Nasional secara nasional.</p>
      </div>
      <span class="text-xs font-bold text-[#2563EB] bg-[#2563EB]/5 px-3 py-1.5 rounded-xl border border-blue-100/30">Mode: Read-Only Pengawasan</span>
    </div>

    <!-- Statistik Ringkas Kekuatan Kekuatan -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-2xl border border-[#E2E8F0] shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Kekuatan Personel</p>
        <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ stats.total_personel }}</h3>
      </div>
      <div class="bg-white p-6 rounded-2xl border border-[#E2E8F0] shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Anggota Status Aktif</p>
        <h3 class="text-2xl font-bold text-green-600 mt-1">{{ stats.personel_aktif }}</h3>
      </div>
      <div class="bg-white p-6 rounded-2xl border border-[#E2E8F0] shadow-sm">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Broadcast Kegiatan Terlaksana</p>
        <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ stats.broadcast_selesai }}</h3>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ stats: Object });
</script>