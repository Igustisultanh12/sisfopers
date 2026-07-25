<template>
  <div 
    class="min-h-screen flex items-center justify-center p-6 text-[#334155] font-sans bg-cover bg-center relative transition-all duration-300"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/75 z-0"></div>

    <div class="relative z-10 w-full max-w-lg text-center space-y-8 animate-float-slow">
      <!-- Logo TNI / Tri Matra -->
      <div class="flex flex-col items-center justify-center gap-4">
        <img 
          v-if="settings?.logo_tni" 
          :src="settings.logo_tni" 
          class="h-24 lg:h-32 object-contain drop-shadow-[0_10px_20px_rgba(0,0,0,0.5)]" 
        />
        <div v-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center gap-4 flex-wrap mt-2">
          <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-10 object-contain drop-shadow-md" />
          <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-10 object-contain drop-shadow-md" />
          <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-10 object-contain drop-shadow-md" />
        </div>
      </div>

      <!-- Glassmorphic Maintenance Card -->
      <div 
        class="p-8 lg:p-12 rounded-3xl shadow-2xl transition-all duration-300 border backdrop-blur-md"
        :class="settings?.login_background 
          ? 'bg-slate-900/60 border-white/10 text-white shadow-black/40' 
          : 'bg-white border-slate-200 shadow-slate-200/50 text-[#334155]'"
      >
        <!-- Maintenance Illustration / Icon -->
        <div class="mb-6 flex justify-center">
          <div class="w-20 h-20 bg-amber-500/10 rounded-full flex items-center justify-center border border-amber-500/25 text-amber-500 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
        </div>

        <!-- Warning Texts -->
        <div class="space-y-4">
          <h2 class="text-2xl font-black uppercase tracking-tight">Layanan Ditangguhkan</h2>
          <h3 class="text-base font-semibold text-amber-500">
            Mohon maaf, Aplikasi Sisfoperskc dalam perbaikan
          </h3>
          <p 
            class="text-xs leading-relaxed max-w-sm mx-auto"
            :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'"
          >
            Sistem Informasi Personel Komponen Cadangan saat ini sedang dalam proses pemeliharaan berkala atau peningkatan performa infrastruktur keamanan. Silakan coba masuk kembali beberapa saat lagi.
          </p>
        </div>

        <!-- Action Button (Logout) -->
        <div class="mt-8">
          <button 
            @click="handleLogout"
            class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white hover:text-white border border-slate-700/50 rounded-xl text-xs font-bold uppercase tracking-wider transition duration-300 flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-black/10"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Keluar dari Sistem
          </button>
        </div>
      </div>

      <!-- App Name & Copyright -->
      <div 
        class="text-xs space-y-1"
        :class="settings?.login_background ? 'text-slate-400' : 'text-slate-400'"
      >
        <p class="font-bold tracking-widest uppercase">{{ settings?.app_name || 'SISFOPERSKC' }} INTEGRASI TNI</p>
        <p>© 2026 {{ settings?.app_name || 'SISFOPERSKC' }}. All Rights Reserved.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
  settings: Object
});

const handleLogout = () => {
  router.post(route('logout'));
};
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}
.animate-float-slow {
  animation: float 4s ease-in-out infinite;
}
</style>
