<template>
  <AuthenticatedLayout>
    <template #header-title>Monitoring Audit Trail Aktivitas</template>

    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0]">
        <h3 class="text-base font-bold text-slate-800">Log Aktivitas Sistem</h3>
        <p class="text-xs text-slate-400 mt-0.5">Jejak audit forensik forensik seluruh operasi manipulasi basis data (CRUD).</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 font-semibold text-xs border-b border-[#E2E8F0] uppercase tracking-wider">
              <th class="p-4">Pengguna</th>
              <th class="p-4">Aksi</th>
              <th class="p-4">Entitas Objek</th>
              <th class="p-4">Alamat IP</th>
              <th class="p-4">Tanggal & Jam</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition">
              <td class="p-4 font-medium text-slate-800">{{ log.user ? log.user.username : 'Sistem Otomatis' }}</td>
              <td class="p-4">
                <span :class="badgeClass(log.action)" class="px-2 py-0.5 rounded text-xs font-semibold">
                  {{ log.action }}
                </span>
              </td>
              <td class="p-4 font-mono text-xs max-w-xs truncate">{{ log.model_type }} (ID: {{ log.model_id }})</td>
              <td class="p-4 text-slate-500">{{ log.ip_address }}</td>
              <td class="p-4 text-slate-500">{{ formatDate(log.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Komponen Navigasi Paginasi Sederhana -->
      <div class="p-4 bg-slate-50 border-t border-[#E2E8F0] flex justify-between items-center text-xs text-slate-500">
        <span>Menampilkan halaman {{ logs.current_page }} dari {{ logs.last_page }}</span>
        <div class="flex gap-2">
          <Link :href="logs.prev_page_url || '#'" :disabled="!logs.prev_page_url" class="px-3 py-1 bg-white border rounded shadow-sm hover:bg-slate-50 transition" :class="{'opacity-40 cursor-not-allowed': !logs.prev_page_url}">Sebelumnya</Link>
          <Link :href="logs.next_page_url || '#'" :disabled="!logs.next_page_url" class="px-3 py-1 bg-white border rounded shadow-sm hover:bg-slate-50 transition" :class="{'opacity-40 cursor-not-allowed': !logs.next_page_url}">Selanjutnya</Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ logs: Object });

const badgeClass = (action) => {
  if (action === 'CREATE') return 'bg-green-50 text-green-700 border border-green-200';
  if (action === 'UPDATE') return 'bg-amber-50 text-amber-700 border border-amber-200';
  if (action === 'DELETE') return 'bg-red-50 text-red-700 border border-red-200';
  return 'bg-blue-50 text-blue-700 border border-blue-200';
};

const formatDate = (datetime) => {
  return new Date(datetime).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) + ' WIB';
};
</script>