<template>
  <AuthenticatedLayout>
    <template #header-title>Monitoring Jejak Otentikasi Akses</template>

    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0]">
        <h3 class="text-base font-bold text-slate-800">Log Autentikasi Pengguna</h3>
        <p class="text-xs text-slate-400 mt-0.5">Catatan forensik otomatis geolokasi alamat IP dan user agent perangkat klien saat masuk sistem.</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none">
              <th class="p-4">Nama Akun Pengguna</th>
              <th class="p-4">Alamat IP</th>
              <th class="p-4">Peramban (Browser)</th>
              <th class="p-4">Sistem Operasi (OS)</th>
              <th class="p-4">Waktu Otentikasi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/30 transition">
              <td class="p-4 font-bold text-slate-800">{{ log.user ? log.user.username : 'Unknown User' }}</td>
              <td class="p-4 font-mono text-xs text-slate-500">{{ log.ip_address }}</td>
              <td class="p-4 font-medium text-slate-700">{{ log.browser }}</td>
              <td class="p-4"><span class="px-2 py-0.5 bg-slate-100 rounded text-xs text-slate-700 font-medium">{{ log.os }}</span></td>
              <td class="p-4 text-slate-400 text-xs">{{ formatDateTime(log.login_at) }}</td>
            </tr>
            <tr v-if="logs.data.length === 0">
              <td colspan="5" class="p-12 text-center text-slate-400 text-sm">Tidak ditemukan rekam jejak riwayat log masuk.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ logs: Object });
const formatDateTime = (d) => new Date(d).toLocaleString('id-ID') + ' WIB';
</script>