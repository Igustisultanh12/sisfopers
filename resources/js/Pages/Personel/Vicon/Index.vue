<template>
  <AuthenticatedLayout title="Vicon & Rapat Dinas Personel">
    <div class="space-y-6 pb-12">
      <!-- HEADER UTAMA -->
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-1">
        <div class="flex items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-200">
            Jalur Satelit Terenkripsi
          </span>
          <span class="text-xs text-slate-400 font-medium">&bull; Agora RTC Enterprise</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
          Vicon & Rapat Dinas Komcad
        </h1>
        <p class="text-xs text-slate-500">
          Daftar sesi rapat dinas dan koordinasi video tatap muka yang mengundang Anda.
        </p>
      </div>

      <!-- 1. RAPAT AKTIF SAAT INI (BISA LANGSUNG BERGABUNG) -->
      <div class="space-y-4">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
          Rapat Sedang Berlangsung ({{ activeRooms.length }})
        </h2>

        <div v-if="activeRooms.length === 0" class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center space-y-2 shadow-xs">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
          <p class="text-xs font-bold text-slate-700">Belum Ada Sesi Rapat Aktif</p>
          <p class="text-[11px] text-slate-400 max-w-sm mx-auto">
            Saat ini tidak ada sesi rapat dinas yang sedang berlangsung untuk Anda.
          </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div 
            v-for="room in activeRooms" 
            :key="room.id"
            class="bg-white rounded-3xl border-2 border-emerald-500/40 p-5 shadow-md flex flex-col justify-between space-y-4"
          >
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                  SEDANG BERLANGSUNG
                </span>
                <span class="text-[10px] font-mono text-slate-400 font-bold">
                  {{ room.room_code }}
                </span>
              </div>

              <div>
                <h3 class="text-sm font-bold text-slate-900 line-clamp-1">
                  {{ room.title }}
                </h3>
                <p v-if="room.description" class="text-xs text-slate-500 line-clamp-2 mt-1">
                  {{ room.description }}
                </p>
              </div>

              <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <div>
                  <span class="text-slate-400">Host:</span>
                  <span class="font-bold text-slate-700 ml-1">{{ room.host?.name || 'Admin Dinas' }}</span>
                </div>
                <div>
                  <span class="text-slate-400">Peserta:</span>
                  <span class="font-bold text-slate-700 ml-1">{{ room.participants?.length || 1 }} orang</span>
                </div>
              </div>
            </div>

            <div class="pt-2">
              <Link 
                :href="route('personel.vicon.room', room.uuid)"
                class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>Gabung Rapat Sekarang</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. RAPAT TERJADWAL -->
      <div v-if="scheduledRooms.length > 0" class="space-y-4 pt-4">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
          Rapat Terjadwal Mendatang ({{ scheduledRooms.length }})
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div 
            v-for="room in scheduledRooms" 
            :key="room.id"
            class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4"
          >
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                  TERJADWAL
                </span>
                <span class="text-[10px] font-mono text-slate-400 font-bold">
                  {{ room.room_code }}
                </span>
              </div>

              <div>
                <h3 class="text-sm font-bold text-slate-900">
                  {{ room.title }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                  Waktu: {{ formatDateTime(room.scheduled_at) }}
                </p>
              </div>

              <div class="text-[11px] text-slate-500">
                <span>Penyelenggara: {{ room.host?.name || 'Admin Dinas' }}</span>
              </div>
            </div>

            <div class="pt-2">
              <button 
                disabled
                class="w-full py-2 px-4 bg-slate-100 text-slate-400 text-xs font-semibold rounded-xl cursor-not-allowed"
              >
                Menunggu Sesi Dimulai oleh Host
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. RIWAYAT RAPAT SELESAI -->
      <div v-if="pastRooms.data.length > 0" class="space-y-4 pt-4">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Riwayat Rapat yang Pernah Diikuti
        </h2>

        <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-xs">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
              <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <tr>
                  <th class="px-5 py-3.5">Judul Rapat</th>
                  <th class="px-5 py-3.5">Kode Ruang</th>
                  <th class="px-5 py-3.5">Host</th>
                  <th class="px-5 py-3.5">Waktu Mulai</th>
                  <th class="px-5 py-3.5">Waktu Selesai</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="past in pastRooms.data" :key="past.id" class="hover:bg-slate-50/50 transition">
                  <td class="px-5 py-3.5 font-bold text-slate-900">
                    {{ past.title }}
                  </td>
                  <td class="px-5 py-3.5 font-mono text-slate-500">
                    {{ past.room_code }}
                  </td>
                  <td class="px-5 py-3.5">
                    {{ past.host?.name || 'Admin' }}
                  </td>
                  <td class="px-5 py-3.5 text-slate-500">
                    {{ formatDateTime(past.started_at) }}
                  </td>
                  <td class="px-5 py-3.5 text-slate-500">
                    {{ formatDateTime(past.ended_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  activeRooms: { type: Array, default: () => [] },
  scheduledRooms: { type: Array, default: () => [] },
  pastRooms: { type: Object, default: () => ({ data: [] }) },
});

const formatDateTime = (dt) => {
  if (!dt) return '-';
  const d = new Date(dt);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
