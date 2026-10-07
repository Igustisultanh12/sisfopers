<template>
  <AuthenticatedLayout>
    <template #header-title>Dashboard Analitik Utama</template>

    <!-- Ringkasan Card Statistik Utama -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="(val, key) in cardItems" :key="key" class="bg-white p-6 rounded-2xl border border-[#E2E8F0] shadow-sm flex items-center justify-between">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ formatKey(key) }}</p>
          <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ val }}</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-[#2563EB]/5 flex items-center justify-center text-[#2563EB]">
          <!-- Icon placeholder dynamic or matching SaaS style -->
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </div>
      </div>
    </div>

    <!-- Ruang Panel Grafik Analitis (Grid Layout) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Trend Login Aktif (Line/Bar Simulative) -->
      <div class="lg:col-span-2 bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Tren Kepadatan Akses Sistem (7 Hari Terakhir)</h4>
          <span class="text-xs font-semibold text-green-600 bg-green-50 px-2.5 py-1 rounded-full">Sistem Normal</span>
        </div>
        <div class="h-64 flex items-end gap-3 pt-6 px-2">
          <!-- Flat SVG Bar Chart Implementation for Clean SaaS Rendering without heavy chartjs overhead -->
          <div v-for="(count, idx) in charts.trend.data" :key="idx" class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
            <div class="w-full bg-[#2563EB] rounded-t-md opacity-85 hover:opacity-100 transition-all duration-200" :style="{ height: `${(count / Math.max(...charts.trend.data, 1)) * 80}%` }"></div>
            <span class="text-[10px] font-medium text-slate-400 truncate w-full text-center">{{ charts.trend.labels[idx] }}</span>
          </div>
        </div>
      </div>

      <!-- Distribusi Kekuatan Matra (Pie/Donut Simulative) -->
      <div class="bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-sm flex flex-col justify-between">
        <div>
          <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-6">Proporsi Anggota Per Matra</h4>
          <div class="space-y-4">
            <div v-for="(label, idx) in charts.matra.labels" :key="idx" class="space-y-1">
              <div class="flex justify-between text-xs font-medium">
                <span class="text-slate-600">{{ label }}</span>
                <span class="font-bold text-slate-800">{{ charts.matra.data[idx] }} Personel</span>
              </div>
              <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div class="bg-[#2563EB] h-full rounded-full" :style="{ width: `${(charts.matra.data[idx] / Math.max(stats.total_personel, 1)) * 100}%` }"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="text-center pt-4 border-t border-[#E2E8F0] mt-4">
          <p class="text-xs text-slate-400">Total Data Teragregasi Nasional</p>
        </div>
      </div>
    </div>

    <!-- Tabel Aktivitas Audit Trail Pilihan Terbaru -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Aktivitas Sistem Terakhir (Audit Trail)</h4>
          <p class="text-xs text-slate-400 mt-0.5">Rekam jejak forensik operasional data dan verifikasi sistem terkini</p>
        </div>
        <Link
          :href="route('admin.monitoring.activity')"
          class="text-xs font-semibold text-[#2563EB] hover:text-blue-700 hover:underline inline-flex items-center gap-1 w-fit"
        >
          Lihat Seluruh Log
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Link>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-semibold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider whitespace-nowrap">
              <th class="p-4">Operator</th>
              <th class="p-4">Aksi Operasi</th>
              <th class="p-4">Alamat IP</th>
              <th class="p-4">Waktu Operasional</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-xs text-slate-600">
            <tr v-for="act in recentActivities" :key="act.id" class="hover:bg-slate-50/40 transition">
              <td class="p-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <!-- Nama Operator / Personel -->
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800 text-xs">
                      {{ act.full_name || act.user?.personel?.full_name || act.user?.username || act.username }}
                    </span>
                    <span
                      v-if="act.pangkat || act.user?.personel?.pangkat"
                      class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"
                    >
                      {{ act.pangkat || act.user?.personel?.pangkat }}
                    </span>
                  </div>
                  <!-- NRP/NIKC & Role -->
                  <div class="text-[11px] text-slate-500 font-mono mt-0.5 flex items-center gap-1.5">
                    <span class="text-slate-400 font-sans font-medium text-[10px]">NRP/NIKC:</span>
                    <span class="font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">
                      {{ act.nikc || act.user?.personel?.nikc || act.username || '-' }}
                    </span>
                    <span
                      v-if="act.role_name || act.user?.role?.name"
                      class="text-[10px] font-sans font-semibold text-blue-600 uppercase"
                    >
                      • {{ act.role_name || act.user?.role?.name?.replace('_', ' ') }}
                    </span>
                  </div>
                </div>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span :class="badgeClass(act.action)" class="px-2.5 py-1 rounded-lg text-[10px] font-bold border inline-block">
                  {{ act.action }}
                </span>
              </td>
              <td class="p-4 text-slate-500 font-mono whitespace-nowrap">{{ act.ip_address || '-' }}</td>
              <td class="p-4 text-slate-400 whitespace-nowrap">{{ formatDateTime(act.created_at) }}</td>
            </tr>
            <tr v-if="!recentActivities || recentActivities.length === 0">
              <td colspan="4" class="p-8 text-center text-slate-400 italic">
                Belum ada catatan aktivitas sistem yang terekam.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  stats: Object,
  charts: Object,
  recentActivities: Array
});

const cardItems = computed(() => ({
  'Total Personel': props.stats.total_personel,
  'Anggota Aktif': props.stats.personel_aktif,
  'Antrean Pending': props.stats.pending_verification,
  'Broadcast Aktif': props.stats.broadcast_aktif
}));

const formatKey = (key) => key;

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  }) + ' WIB';
};

const badgeClass = (action) => {
  const act = (action || '').toUpperCase();
  if (act.includes('CREATE')) {
    return 'bg-emerald-50 text-emerald-700 border-emerald-200';
  }
  if (act.includes('UPDATE')) {
    return 'bg-blue-50 text-blue-700 border-blue-200';
  }
  if (act.includes('DELETE')) {
    return 'bg-rose-50 text-rose-700 border-rose-200';
  }
  if (act.includes('LOGIN') || act.includes('LOGOUT')) {
    return 'bg-amber-50 text-amber-700 border-amber-200';
  }
  if (act.includes('VERIFY') || act.includes('APPROVE')) {
    return 'bg-purple-50 text-purple-700 border-purple-200';
  }
  if (act.includes('REJECT')) {
    return 'bg-red-50 text-red-700 border-red-200';
  }
  return 'bg-slate-100 text-slate-700 border-slate-200';
};
</script>