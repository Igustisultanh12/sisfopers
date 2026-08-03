<template>
  <AuthenticatedLayout>
    <template #header-title>Aktivitas Sistem Terakhir (Audit Trail)</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Audit Trail Aktivitas Sistem</h1>
          <p class="text-xs text-slate-400 mt-1">Jejak rekaman forensik seluruh operasi manipulasi data (Create, Update, Delete, Verify, Login) pada basis data.</p>
        </div>
      </div>

      <!-- Filters & Search Bar -->
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari kata kunci: Nama, NIKC, Username, Aksi, IP Address..."
            class="w-full pl-4 pr-10 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB]"
          />
          <button
            @click="handleSearch"
            class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600 font-bold"
          >
            Cari
          </button>
        </div>

        <select
          v-model="filterAction"
          @change="handleSearch"
          class="px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB] text-slate-700 font-medium"
        >
          <option value="">Semua Jenis Aksi</option>
          <option value="CREATE">CREATE (Tambah Data)</option>
          <option value="UPDATE">UPDATE (Perubahan Data)</option>
          <option value="DELETE">DELETE (Hapus Data)</option>
          <option value="LOGIN">LOGIN (Masuk Sistem)</option>
          <option value="LOGOUT">LOGOUT (Keluar Sistem)</option>
          <option value="APPROVE">APPROVE (Persetujuan)</option>
          <option value="REJECT">REJECT (Penolakan)</option>
          <option value="VERIFY">VERIFY (Verifikasi Berkas)</option>
        </select>
      </div>

      <!-- Tabel Audit Trail -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none whitespace-nowrap">
              <th class="p-4">Pelaku Aktivitas</th>
              <th class="p-4">Jenis Aksi</th>
              <th class="p-4">Entitas Objek</th>
              <th class="p-4">Alamat IP</th>
              <th class="p-4">Tanggal & Waktu</th>
              <th class="p-4 text-right">Rincian Data</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-xs text-slate-600">
            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition">
              <td class="p-4 whitespace-nowrap">
                <div v-if="log.user">
                  <p class="font-bold text-slate-800">
                    {{ log.user.personel?.full_name || log.user.username }}
                  </p>
                  <p class="text-[10px] text-slate-400 mt-0.5">
                    <span v-if="log.user.personel?.nikc" class="font-mono font-semibold text-slate-600">NIKC: {{ log.user.personel.nikc }} | </span>
                    <span class="uppercase font-semibold text-blue-600">{{ log.user.role?.name?.replace('_', ' ') || 'USER' }}</span>
                  </p>
                </div>
                <div v-else class="italic text-slate-400">
                  Sistem Otomatis / Pengunjung
                </div>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span :class="badgeClass(log.action)" class="px-2.5 py-1 rounded-xl text-[10px] font-bold border inline-block">
                  {{ log.action }}
                </span>
              </td>
              <td class="p-4 whitespace-nowrap font-mono text-slate-700">
                <span v-if="log.model_type" class="bg-slate-100 px-2 py-1 rounded border border-slate-200">
                  {{ log.model_type }} #{{ log.model_id || '-' }}
                </span>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="p-4 whitespace-nowrap font-mono text-slate-500">
                {{ log.ip_address || '-' }}
              </td>
              <td class="p-4 whitespace-nowrap text-slate-500 font-medium">
                {{ formatDate(log.created_at) }}
              </td>
              <td class="p-4 text-right whitespace-nowrap">
                <button
                  v-if="log.old_values || log.new_values"
                  @click="openDetail(log)"
                  class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-[#2563EB] text-[11px] font-bold rounded-lg border border-blue-200 transition cursor-pointer"
                >
                  Rincian Perubahan
                </button>
                <span v-else class="text-slate-300">-</span>
              </td>
            </tr>
            <tr v-if="logs.data.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-400 italic">
                Tidak ada data aktivitas sistem yang sesuai dengan pencarian.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginasi -->
        <div class="p-4 bg-slate-50 border-t border-[#E2E8F0] flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
          <span>Menampilkan {{ logs.from || 0 }} sampai {{ logs.to || 0 }} dari total {{ logs.total }} catatan aktivitas</span>
          <div class="flex gap-2">
            <Link
              v-for="(link, i) in logs.links"
              :key="i"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-[#2563EB] text-white border-[#2563EB]' : (link.url ? 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' : 'bg-slate-100 text-slate-300 border-slate-100 cursor-not-allowed')"
            />
          </div>
        </div>
      </div>

    </div>

    <!-- MODAL RINCIAN PERUBAHAN DATA (OLD VS NEW VALUES) -->
    <div v-if="selectedLog" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white border border-slate-200 rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-5 my-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="text-base font-extrabold text-slate-800">Rincian Perubahan Audit Log</h3>
            <p class="font-mono text-xs text-[#2563EB] font-bold mt-0.5">Aksi: {{ selectedLog.action }} ({{ selectedLog.model_type }} #{{ selectedLog.model_id }})</p>
          </div>
          <button @click="selectedLog = null" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1 cursor-pointer">✕</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
          <!-- Nilai Lama -->
          <div class="space-y-2">
            <h4 class="font-extrabold text-red-600 uppercase text-[11px] bg-red-50 px-3 py-1.5 rounded-lg border border-red-200">
              Data Sebelum Perubahan (Old)
            </h4>
            <pre class="p-3 bg-slate-900 text-slate-200 rounded-xl overflow-x-auto font-mono text-[10px] max-h-60 leading-relaxed">{{ formatJson(selectedLog.old_values) }}</pre>
          </div>

          <!-- Nilai Baru -->
          <div class="space-y-2">
            <h4 class="font-extrabold text-emerald-600 uppercase text-[11px] bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
              Data Setelah Perubahan (New)
            </h4>
            <pre class="p-3 bg-slate-900 text-slate-200 rounded-xl overflow-x-auto font-mono text-[10px] max-h-60 leading-relaxed">{{ formatJson(selectedLog.new_values) }}</pre>
          </div>
        </div>

        <div class="flex justify-end pt-3 border-t border-slate-100">
          <button
            @click="selectedLog = null"
            class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Tutup Rincian
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  logs: Object,
  filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const filterAction = ref(props.filters?.action || '');
const selectedLog = ref(null);

function handleSearch() {
  router.get(
    route('admin.monitoring.activity'),
    {
      search: searchQuery.value,
      action: filterAction.value,
    },
    { preserveState: true, replace: true }
  );
}

function openDetail(log) {
  selectedLog.value = log;
}

function formatJson(val) {
  if (!val) return 'Tidak ada data.';
  try {
    return JSON.stringify(val, null, 2);
  } catch (e) {
    return String(val);
  }
}

const badgeClass = (action) => {
  switch (action) {
    case 'CREATE':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'UPDATE':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'DELETE':
      return 'bg-red-50 text-red-700 border-red-200';
    case 'APPROVE':
    case 'VERIFY':
      return 'bg-blue-50 text-blue-700 border-blue-200';
    case 'REJECT':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    default:
      return 'bg-slate-100 text-slate-700 border-slate-200';
  }
};

const formatDate = (datetime) => {
  if (!datetime) return '-';
  return new Date(datetime).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) + ' WIB';
};
</script>