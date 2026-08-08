<template>
  <AuthenticatedLayout>
    <template #header-title>Monitoring Broadcast Kegiatan (PJU)</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

      <!-- Header & Action -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Broadcast Kegiatan & Kesiapsiagaan</h1>
          <p class="text-xs text-slate-400 mt-1">Pemantauan dan penyampaian pengumuman/broadcast ke personel jajaran Komcad.</p>
        </div>
        <Link
          v-if="canCreate"
          :href="route('pju.broadcast.create')"
          class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-600 text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer self-start sm:self-auto"
        >
          + Buat Broadcast Kegiatan Baru
        </Link>
      </div>

      <!-- List Broadcast Cards -->
      <div class="space-y-4">
        <div
          v-for="b in broadcasts.data"
          :key="b.id"
          class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-3"
        >
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
              <span class="text-[10px] font-extrabold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 uppercase">
                TARGET: {{ b.target_type }} <span v-if="b.matra">({{ b.matra }})</span>
              </span>
              <h3 class="font-extrabold text-slate-800 text-base mt-1">{{ b.title }}</h3>
            </div>
            <span class="text-xs text-slate-400 font-medium">
              Dikirim: {{ formatDate(b.sent_at || b.created_at) }}
            </span>
          </div>

          <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ b.content }}</p>

          <div class="pt-2 flex items-center justify-between text-[11px] text-slate-400 font-medium border-t border-slate-100/60">
            <span>Pengirim: <strong class="text-slate-700">{{ b.creator?.username || 'PJU Pembina Matra' }}</strong></span>
            <span>Total Penerima Disertakan: <strong class="text-blue-600">{{ b.responses?.length || 0 }} Personel</strong></span>
          </div>
        </div>

        <div v-if="broadcasts.data.length === 0" class="bg-white border border-slate-200 p-12 text-center text-slate-400 italic rounded-2xl">
          Belum ada pesan broadcast kegiatan yang dikirimkan.
        </div>
      </div>

      <!-- Paginasi -->
      <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
        <span>Menampilkan {{ broadcasts.from || 0 }} sampai {{ broadcasts.to || 0 }} dari {{ broadcasts.total }} broadcast</span>
        <div class="flex gap-2">
          <Link
            v-for="(link, i) in broadcasts.links"
            :key="i"
            :href="link.url || '#'"
            v-html="link.label"
            class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
            :class="link.active ? 'bg-[#2563EB] text-white border-[#2563EB]' : (link.url ? 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' : 'bg-slate-100 text-slate-300 border-slate-100 cursor-not-allowed')"
          />
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  broadcasts: Object,
  canCreate: Boolean,
});

const formatDate = (dt) => {
  if (!dt) return '-';
  return new Date(dt).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) + ' WIB';
};
</script>
