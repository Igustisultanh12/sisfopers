<template>
  <AuthenticatedLayout>
    <template #header-title>Broadcast Informasi & Mobilisasi Personel</template>

    <div class="flex justify-end mb-6">
      <Link :href="route('admin.broadcast.create')" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white font-semibold text-xs rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer">
        Buat Broadcast Baru
      </Link>
    </div>

    <!-- ROMEI Style Table Grid Container -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto w-full">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none">
            <th class="p-4">Judul Kegiatan / Komando</th>
            <th class="p-4">Kategori</th>
            <th class="p-4">Waktu & Tanggal Agenda</th>
            <th class="p-4">Target Sasaran</th>
            <th class="p-4 text-center">Partisipan Respon</th>
            <th class="p-4 text-right">Opsi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
          <tr v-for="item in broadcasts.data" :key="item.id" class="hover:bg-slate-50/30 transition">
            <td class="p-4">
              <div class="font-bold text-slate-800 line-clamp-1">{{ item.title }}</div>
              <div class="text-xs text-slate-400 mt-0.5 truncate">Lokasi: {{ item.location }}</div>
            </td>
            <td class="p-4">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                {{ item.category }}
              </span>
            </td>
            <td class="p-4">
              <div class="font-semibold text-slate-700">{{ formatDate(item.event_date) }}</div>
              <div class="text-xs text-slate-400 mt-0.5">Pukul {{ item.event_time }} WIB</div>
            </td>
            <td class="p-4">
              <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-100/40">
                {{ item.target_type }} {{ item.target_value ? `(${item.target_value})` : '' }}
              </span>
            </td>
            <td class="p-4 text-center">
              <span class="font-bold text-slate-800">{{ item.responses_count }}</span>
              <span class="text-slate-400 text-xs"> / {{ item.targets_count }} Anggota</span>
            </td>
            <td class="p-4 text-right">
              <div class="flex items-center justify-end gap-3">
                <Link :href="route('admin.broadcast.show', item.uuid)" class="text-xs font-bold text-[#2563EB] hover:underline">
                  Monitoring
                </Link>
                <Link :href="route('admin.broadcast.edit', item.uuid)" class="text-xs font-bold text-amber-600 hover:underline">
                  Edit
                </Link>
                <button @click="deleteBroadcast(item)" class="text-xs font-bold text-red-600 hover:underline cursor-pointer border-none bg-transparent">
                  Hapus
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="broadcasts.data.length === 0">
            <td colspan="6" class="p-12 text-center text-slate-400 text-sm">Tidak ditemukan rekam data maklumat broadcast instansi.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

defineProps({ broadcasts: Object });

const { confirmAction, showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

const deleteBroadcast = async (item) => {
  const isConfirmed = await confirmAction(
    'Hapus Broadcast?',
    `Apakah Anda yakin ingin menghapus broadcast "${item.title}"? Seluruh data respon kehadiran terkait juga akan dihapus.`,
    'Ya, Hapus'
  );

  if (isConfirmed) {
    router.delete(route('admin.broadcast.destroy', item.uuid), {
      onBefore: () => showLoadingProgress('Menghapus broadcast kegiatan...'),
      onSuccess: () => {
        closeLoading();
        alertSuccess('Berhasil Dihapus', 'Broadcast kegiatan telah berhasil dihapus dari sistem.');
      },
      onError: () => {
        closeLoading();
        alertError('Gagal Menghapus', 'Terjadi kesalahan sistem saat mencoba menghapus.');
      }
    });
  }
};
</script>