<template>
  <AuthenticatedLayout>
    <template #header-title>Pusat Notifikasi Sisfopers</template>

    <div class="max-w-4xl mx-auto space-y-4">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm">
        
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-4 mb-4">
          <div>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Kotak Masuk Pemberitahuan</h3>
            <p class="text-xs text-slate-400 mt-0.5">Seluruh riwayat instruksi, broadcast, dan verifikasi akun Anda.</p>
          </div>
          <button 
            @click="markAllAsRead" 
            class="text-xs font-bold text-[#2563EB] hover:underline cursor-pointer bg-blue-50 px-3 py-1.5 rounded-xl transition"
          >
            Tandai Semua Telah Dibaca
          </button>
        </div>

        <div v-if="notifications.data.length > 0" class="divide-y divide-[#E2E8F0]">
          <div 
            v-for="item in notifications.data" 
            :key="item.id" 
            :class="['p-4 flex items-start gap-4 transition hover:bg-slate-50/80 rounded-xl mt-1', !item.read_at ? 'bg-blue-50/30 border-l-4 border-[#2563EB]' : '']"
          >
            <div :class="getNotificationStyles(item.data.icon).bg" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 select-none">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="getNotificationStyles(item.data.icon).svg"></svg>
            </div>
            <div class="flex-1 space-y-0.5 text-xs">
              <div class="flex items-center justify-between gap-2">
                <span class="font-bold text-slate-800">{{ item.data.title || 'Pemberitahuan Sistem' }}</span>
                <span class="text-[10px] text-slate-400 font-medium">{{ formatDate(item.created_at) }}</span>
              </div>
              <p class="text-slate-600 leading-relaxed">{{ item.data.message }}</p>
              
              <div class="pt-2">
                <a 
                  :href="route('notifications.read', item.id)" 
                  class="inline-flex items-center gap-1 text-[#2563EB] font-bold hover:underline"
                >
                  Buka Tautan Tindakan &rarr;
                </a>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12 text-slate-400 italic text-xs font-medium">
          Tidak ada riwayat pemberitahuan masuk saat ini.
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps({
  notifications: Object,
  rolePrefix: String
});

const getNotificationStyles = (iconKey) => {
  const map = {
    'broadcast': {
      bg: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.685-.196-1.353-.49-1.985-.875L6 16v-4.836A7.49 7.49 0 014.25 6h15.5c-.383 1.937-1.383 3.654-2.75 4.836V16l-2.355-1.035a7.9 7.9 0 01-1.985.875M10.34 15.84A8.001 8.001 0 0012 16c.56 0 1.1-.1 1.66-.27M10.34 15.84v1.41c0 1.105.895 2 2 2s2-.895 2-2v-1.41" />`
    },
    'password': {
      bg: 'bg-amber-50 text-amber-600 border border-amber-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H3.75v-2.25A3.375 3.375 0 007.125 16.5h1.5v-1.5h1.5v-1.5h1.5c.22 0 .44-.089.6-.25l2.777-2.778c.404-.404.527-.9.43-1.563A6 6 0 0121.75 8.25z" />`
    },
    'profile': {
      bg: 'bg-blue-50 text-blue-600 border border-blue-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />`
    },
    'job': {
      bg: 'bg-indigo-50 text-indigo-600 border border-indigo-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.214.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.426.293-.68.374a23.953 23.953 0 01-8.32 0c-.254-.08-.486-.209-.68-.375m14.483-3.13c-.08-.398-.3-.72-.64-.902a24.58 24.58 0 01-13.18 0c-.34.182-.56.504-.64.902M15 6.75V4.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 4.5v2.25m6 0a48.667 48.667 0 00-6 0m6 0v1.125a2.25 2.25 0 01-2.25 2.25h-1.5a2.25 2.25 0 01-2.25-2.25V6.75" />`
    },
    'mfa': {
      bg: 'bg-red-50 text-red-600 border border-red-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />`
    },
    'success': {
      bg: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`
    }
  };
  return map[iconKey] || {
    bg: 'bg-slate-50 text-slate-500 border border-slate-100',
    svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />`
  };
};

const markAllAsRead = () => {
  router.post(route('notifications.read-all'), {}, {
    preserveScroll: true
  });
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', 
    month: 'short', 
    hour: '2-digit', 
    minute: '2-digit'
  }) + ' WIB';
};
</script>