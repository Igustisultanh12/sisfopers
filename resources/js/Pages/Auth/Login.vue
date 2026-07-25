<template>
  <div 
    class="min-h-screen flex text-[#334155] font-sans bg-cover bg-center relative transition-all duration-300"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>
    
    <!-- Bagian Kiri: Logo Tri Matra & Informasi Aplikasi -->
    <div 
      class="hidden lg:flex lg:w-1/2 flex-col justify-between p-16 transition-all duration-300 z-10"
      :class="settings?.login_background ? 'text-white' : 'bg-gradient-to-b from-[#2563EB]/5 to-transparent'"
    >
      <div></div>
      
      <!-- Live Preview Logo Tri Matra atau Abstract Graphic -->
      <div class="my-auto max-w-lg space-y-8 flex flex-col items-center text-center mx-auto">
        <!-- Logo TNI Utama -->
        <div v-if="settings?.logo_tni" class="flex justify-center animate-float-slow">
          <img :src="settings.logo_tni" class="h-32 lg:h-40 object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.6)]" />
        </div>
        
        <!-- Logo Tiga Matra Sejajar -->
        <div v-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center justify-center gap-6 flex-wrap animate-float-slow delay-200">
          <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-16 object-contain drop-shadow-md transition hover:scale-110" />
          <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-16 object-contain drop-shadow-md transition hover:scale-110" />
          <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-16 object-contain drop-shadow-md transition hover:scale-110" />
        </div>
        
        <div v-if="!settings?.logo_tni && !settings?.logo_ad && !settings?.logo_al && !settings?.logo_au" class="relative w-72 h-72 bg-white/60 backdrop-blur-md rounded-3xl border border-white p-6 shadow-xl shadow-blue-500/5 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="w-12 h-3 bg-[#2563EB]/20 rounded-full"></span>
            <span class="w-3 h-3 rounded-full bg-[#22C55E]"></span>
          </div>
          <div class="space-y-2">
            <div class="h-4 bg-[#1E293B]/10 rounded-md w-full"></div>
            <div class="h-4 bg-[#1E293B]/10 rounded-md w-5/6"></div>
          </div>
          <div class="h-24 bg-gradient-to-t from-[#2563EB]/10 to-[#2563EB]/0 rounded-xl border border-dashed border-[#2563EB]/20 flex items-center justify-center text-xs text-[#2563EB] font-medium">
            SaaS Enterprise Dashboard System Active
          </div>
        </div>

        <div>
          <h2 class="text-3xl font-extrabold tracking-tight uppercase" :class="settings?.login_background ? 'text-white drop-shadow-md' : 'text-slate-800'">
            {{ settings?.app_name || 'SISFOPERSKC' }} INTEGRASI TNI
          </h2>
          <p class="text-sm mt-3 leading-relaxed max-w-md mx-auto" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-500'">
            Sistem informasi personel yang terintegrasi, valid, dan akuntabel untuk pengelolaan administrasi personel komponen cadangan.
          </p>
        </div>
      </div>

      <p class="text-xs" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-400'">
        © 2026 {{ settings?.app_name || 'SISFOPERSKC' }}. All Rights Reserved.
      </p>
    </div>

    <!-- Bagian Kanan: Card Form Login -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 z-10">
      <div 
        class="w-full max-w-md p-10 rounded-2xl shadow-2xl transition-all duration-300 animate-float-card"
        :class="settings?.login_background 
          ? 'bg-slate-900/40 backdrop-blur-md border border-white/10 text-white' 
          : 'bg-white border border-[#E2E8F0] shadow-slate-200/50 text-[#334155]'"
      >
        <div class="mb-8">
          <h3 class="text-xl font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">Masuk Akun</h3>
          <p class="text-xs mt-1" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
            Gunakan akun internal Anda untuk mengakses sistem dashboard.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
              NIKC
            </label>
            <input 
              type="text" 
              v-model="form.username" 
              class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2" 
              :class="settings?.login_background 
                ? 'bg-white/10 border-white/10 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                : 'bg-white border-[#E2E8F0] focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
              placeholder="Masukkan NIKC"
              required 
            />
            <p v-if="errors.username" class="text-xs text-[#EF4444] mt-1 font-medium">{{ errors.username }}</p>
          </div>

          <div>
            <div class="flex justify-between items-center mb-2">
              <label class="block text-xs font-semibold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                Kata Kunci
              </label>
              <Link :href="route('password.reset-otp')" class="text-xs font-medium hover:underline" :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'">
                Lupa Password?
              </Link>
            </div>
            <input 
              type="password" 
              v-model="form.password" 
              class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2" 
              :class="settings?.login_background 
                ? 'bg-white/10 border-white/10 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                : 'bg-white border-[#E2E8F0] focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
              placeholder="••••••••" 
              required 
            />
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm select-none cursor-pointer" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
              <input 
                type="checkbox" 
                v-model="form.remember" 
                class="w-4 h-4 rounded focus:ring-offset-0" 
                :class="settings?.login_background ? 'bg-white/10 border-white/10 text-orange-500 focus:ring-0' : 'text-[#2563EB] border-[#E2E8F0] focus:ring-[#2563EB]'"
              />
              Ingat Saya
            </label>
          </div>

          <button 
            type="submit" 
            :disabled="form.processing" 
            class="w-full py-3 px-4 text-white text-sm font-semibold rounded-lg shadow-lg transition duration-150 disabled:opacity-50 cursor-pointer flex justify-center items-center gap-2"
            :class="settings?.login_background 
              ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' 
              : 'bg-[#2563EB] hover:bg-[#1E40AF] shadow-blue-500/10'"
          >
            <span>Masuk Sistem</span>
            <span v-if="settings?.login_background">➜</span>
          </button>
        </form>

        <div class="mt-6 text-center border-t pt-6" :class="settings?.login_background ? 'border-white/10' : 'border-[#E2E8F0]'">
          <p class="text-xs" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
            Belum memiliki akun Komponen Cadangan? 
            <Link 
              :href="route('register')" 
              class="font-semibold hover:underline"
              :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'"
            >
              Daftar Sekarang
            </Link>
          </p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
  errors: Object
});

const page = usePage();
const settings = computed(() => page.props.settings || {});

const form = useForm({
  username: '',
  password: '',
  remember: false
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password')
  });
};
</script>

<style scoped>
.animate-float-slow {
  animation: float-logo 5s ease-in-out infinite;
}

.animate-float-card {
  animation: float-card 6s ease-in-out infinite;
}

@keyframes float-logo {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

@keyframes float-card {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}
</style>
