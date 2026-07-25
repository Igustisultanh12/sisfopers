<template>
  <AuthenticatedLayout>
    <template #header-title>Pusat Pelaporan Dokumen Instansial</template>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl">
      <!-- Card Download Master Personel -->
      <div class="bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-sm space-y-4 flex flex-col justify-between">
        <div class="space-y-1.5">
          <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">01. Berkas Pelaporan Master Personel</h4>
          <p class="text-xs text-slate-400 leading-relaxed">Ekspor seluruh database induk anggota komponen cadangan nasional yang aktif terverifikasi biometrik.</p>
        </div>
        <div class="flex gap-2 pt-2">
          <a :href="route('admin.report.personel.excel')" @click="triggerSwalDownload" class="flex-1 py-2.5 text-center bg-white border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition shadow-sm cursor-pointer">
            Unduh Excel (.xlsx)
          </a>
          <a :href="route('admin.report.personel.pdf')" @click="triggerSwalDownload" class="flex-1 py-2.5 text-center bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer">
            Unduh Cetak PDF
          </a>
        </div>
      </div>

      <!-- Card Download Log Audit Keamanan -->
      <div class="bg-white border border-[#E2E8F0] p-6 rounded-2xl shadow-sm space-y-4 flex flex-col justify-between">
        <div class="space-y-1.5">
          <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wide">02. Berkas Forensik Audit Trail</h4>
          <p class="text-xs text-slate-400 leading-relaxed">Ekspor dokumentasi jejak audit sistem kepatuhan internal terhadap seluruh manipulasi baris data (CRUD).</p>
        </div>
        <div class="flex gap-2 pt-2">
          <button @click="alertMaintenance" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">
            Buka Arsip Log Pengawasan
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { alertSuccess, alertError } = useSwal();

const triggerSwalDownload = () => {
  // Memberikan feedback instan premium saat mesin server melakukan rendering berkas biner
  setTimeout(() => {
    alertSuccess('Permintaan Diterima', 'Berkas dokumen laporan Anda sedang diproses oleh engine server dan diunduh otomatis.');
  }, 300);
};

const alertMaintenance = () => {
  alertError('Fitur Terbatas', 'Arsip fisik log audit trail hanya dapat diekspor langsung via terminal super administrator aaPanel.');
};
</script>