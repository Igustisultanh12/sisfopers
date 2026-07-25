<template>
  <AuthenticatedLayout>
    <template #sidebar-menu>
      <Link :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard</Link>
      <Link :href="route('admin.personel.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Master Personel</Link>
      <Link :href="route('admin.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Broadcast Kegiatan</Link>
      <Link :href="route('admin.setting.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Pengaturan Sistem</Link>
    </template>

    <template #header-title>Detail Pemantauan Mobilisasi</template>

    <div class="space-y-6 max-w-5xl">
      <div class="flex items-center justify-between">
        <Link :href="route('admin.broadcast.index')" class="text-xs font-bold text-slate-500 hover:text-[#2563EB] flex items-center gap-1.5 transition">
          &larr; Kembali ke Daftar Broadcast
        </Link>
      </div>

      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 bg-slate-50/50 border-b border-[#E2E8F0] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
          <div class="space-y-1">
            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-blue-100 text-[#2563EB]">
              Kategori: {{ broadcast.category }}
            </span>
            <h3 class="text-base font-extrabold text-slate-800 mt-1">{{ broadcast.title }}</h3>
          </div>
          <span class="text-xs font-mono font-bold text-slate-400 select-none bg-white px-3 py-1 border border-[#E2E8F0] rounded-xl shadow-2xs">
            UUID: {{ broadcast.uuid.substring(0,8) }}...
          </span>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-xs border-b border-[#E2E8F0]">
          <div class="p-4 bg-slate-50/60 border border-[#E2E8F0] rounded-xl space-y-1">
            <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Waktu & Tanggal Agenda</p>
            <p class="font-bold text-slate-800 text-sm">{{ formatDate(broadcast.event_date) }}</p>
            <p class="font-medium text-slate-500">Pukul {{ broadcast.event_time }} WIB</p>
          </div>
          <div class="p-4 bg-slate-50/60 border border-[#E2E8F0] rounded-xl space-y-1">
            <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Lokasi Penugasan</p>
            <p class="font-bold text-slate-800 text-sm uppercase leading-relaxed">{{ broadcast.location }}</p>
          </div>
          <div class="p-4 bg-slate-50/60 border border-[#E2E8F0] rounded-xl space-y-1">
            <p class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Batas Respons Personel</p>
            <p class="font-bold text-red-600 text-sm">{{ formatDateTime(broadcast.deadline) }} WIB</p>
          </div>
        </div>

        <div class="p-6 space-y-2">
          <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Nota Maklumat Lengkap / Instruksi</h4>
          <p class="text-xs text-slate-600 leading-relaxed bg-slate-50/40 border border-[#E2E8F0] p-4 rounded-xl whitespace-pre-line">
            {{ broadcast.description }}
          </p>
        </div>
      </div>

      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between bg-slate-50/30 gap-3">
          <div class="flex items-center gap-3">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Log Lembar Kendali Kehadiran Anggota</h4>
            <a :href="route('admin.report.broadcast.excel', broadcast.uuid)" class="py-1 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-lg shadow-sm transition flex items-center gap-1.5 cursor-pointer">
              <span>📊</span> Ekspor Excel (XLS)
            </a>
            <a :href="route('admin.report.broadcast.pdf', broadcast.uuid)" class="py-1 px-3 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded-lg shadow-sm transition flex items-center gap-1.5 cursor-pointer">
              <span>📄</span> Ekspor PDF
            </a>
          </div>
          <div class="flex items-center gap-4 text-[11px] font-bold">
            <span class="text-blue-600">Target Mobilisasi: {{ stats.total }}</span>
            <span class="text-green-600">Siap Hadir: {{ stats.hadir }}</span>
            <span class="text-red-600">Tidak Hadir: {{ stats.tidak_hadir }}</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50/80 border-b border-[#E2E8F0] text-slate-400 font-bold uppercase select-none">
                <th class="py-3 px-5">Nama Personel</th>
                <th class="py-3 px-5">Pangkat / Matra</th>
                <th class="py-3 px-5">Status Respon</th>
                <th class="py-3 px-5">Waktu Konfirmasi</th>
                <th class="py-3 px-5">Alasan / Catatan Izin</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E8F0]">
              <tr v-for="res in broadcast.responses" :key="res.id" class="hover:bg-slate-50/50 transition">
                <td class="py-3.5 px-5 font-bold text-slate-800">{{ res.personel?.full_name || 'Prada Personel' }}</td>
                <td class="py-3.5 px-5 font-medium text-slate-500 uppercase">
                  {{ res.personel?.pangkat || '-' }} / TNI {{ res.personel?.matra }}
                </td>
                <td class="py-3.5 px-5">
                  <span :class="res.status_attendance === 'HADIR' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'" class="px-2.5 py-0.5 border rounded-md font-bold text-[10px]">
                    {{ res.status_attendance === 'HADIR' ? 'Hadir' : 'Tidak Hadir' }}
                  </span>
                </td>
                <td class="py-3.5 px-5 text-slate-400 font-medium">{{ formatDateTime(res.created_at) }}</td>
                <td class="py-3.5 px-5 text-slate-600 font-medium italic">{{ res.notes || '-' }}</td>
              </tr>
              <tr v-if="broadcast.responses?.length === 0">
                <td colspan="5" class="text-center py-12 text-slate-400 italic font-medium">Belum ada personel jajaran yang mengirimkan konfirmasi presensi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  broadcast: Object,
  stats: Object
});

const formatDate = (dateString) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return '-';
  return new Date(dateTimeString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });
};
</script>
