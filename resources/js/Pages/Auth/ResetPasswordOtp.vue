<template>
  <div 
    class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 font-sans bg-cover bg-center relative transition-all duration-300 flex items-center justify-center"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>

    <div 
      class="max-w-4xl w-full mx-auto rounded-2xl shadow-xl transition-all duration-300 relative z-10 overflow-hidden"
      :class="settings?.login_background 
        ? 'bg-slate-900/60 backdrop-blur-md border border-white/10 text-white' 
        : 'bg-white border border-[#E2E8F0] shadow-slate-100/60 text-[#334155]'"
    >
      
      <!-- Card Header (Sama Persis seperti Registrasi) -->
      <div 
        class="p-8 border-b flex items-center justify-between transition-all duration-300"
        :class="settings?.login_background 
          ? 'border-white/10 bg-white/5' 
          : 'border-[#E2E8F0] bg-gradient-to-r from-[#2563EB]/5 to-transparent'"
      >
        <div class="flex items-center gap-4">
          <img v-if="settings?.logo_tni" :src="settings.logo_tni" class="h-12 object-contain drop-shadow" />
          <div v-else-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center gap-1.5">
            <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-8 object-contain" />
            <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-8 object-contain" />
            <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-8 object-contain" />
          </div>
          <div>
            <h2 class="text-xl font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
              Reset Kata Sandi Komponen Cadangan
            </h2>
            <p class="text-xs mt-1" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
              Masukkan NIKC Anda dan 6 digit kode OTP Reset Password yang diterbitkan oleh administrator.
            </p>
          </div>
        </div>
        <Link 
          :href="route('login')" 
          class="text-xs font-semibold hover:underline flex items-center gap-1 transition"
          :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'"
        >
          Kembali ke Login
        </Link>
      </div>

      <!-- Card Body -->
      <div class="p-8 space-y-6">
        <div 
          class="p-6 rounded-2xl space-y-5 transition-all duration-300"
          :class="settings?.login_background ? 'bg-white/5 border border-white/10' : 'bg-blue-50/40 border border-blue-100/70'"
        >
          <div>
            <label 
              class="block text-xs font-bold uppercase tracking-wider mb-1"
              :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'"
            >
              01. VERIFIKASI KODE OTP ADMIN & RESET PASSWORD
            </label>
            <p class="text-xs mb-4" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
              Lengkapi form autentikasi di bawah ini dengan kode OTP 6 digit yang diterbitkan oleh Admin.
            </p>
          </div>

          <!-- Flash success message dari server -->
          <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-xl text-emerald-300 text-xs font-medium">
            <span>✅ {{ $page.props.flash.success }}</span>
          </div>

          <form @submit.prevent="submit" class="space-y-5">
            <!-- Row 1: NIKC & OTP -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- NIKC -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                  NOMOR NIKC ANDA <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.username"
                  type="text"
                  placeholder="Masukkan NIKC (17 digit)"
                  class="w-full px-4 py-3 text-sm rounded-xl outline-none transition-all duration-200"
                  :class="settings?.login_background 
                    ? 'bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:border-blue-400 focus:bg-white/20' 
                    : 'bg-white border border-[#E2E8F0] text-slate-800 focus:border-[#2563EB] shadow-sm'"
                  required
                  autocomplete="username"
                />
                <p v-if="form.errors.username" class="text-red-400 text-xs mt-1.5 font-semibold">⚠️ {{ form.errors.username }}</p>
              </div>

              <!-- OTP Reset Password -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                  KODE OTP DARI ADMIN <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.otp"
                  type="text"
                  maxlength="6"
                  placeholder="6 Digit OTP"
                  class="w-full px-4 py-3 text-sm font-bold tracking-widest text-center rounded-xl outline-none transition-all duration-200 font-mono"
                  :class="settings?.login_background 
                    ? 'bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:border-blue-400 focus:bg-white/20' 
                    : 'bg-white border border-[#E2E8F0] text-slate-800 focus:border-[#2563EB] shadow-sm'"
                  required
                  autocomplete="off"
                />
                <p v-if="form.errors.otp" class="text-red-400 text-xs mt-1.5 font-semibold">⚠️ {{ form.errors.otp }}</p>
              </div>
            </div>

            <!-- Row 2: Password Baru & Konfirmasi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Password Baru -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                  KATA SANDI BARU <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.password"
                  type="password"
                  placeholder="Minimal 8 karakter"
                  class="w-full px-4 py-3 text-sm rounded-xl outline-none transition-all duration-200"
                  :class="settings?.login_background 
                    ? 'bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:border-blue-400 focus:bg-white/20' 
                    : 'bg-white border border-[#E2E8F0] text-slate-800 focus:border-[#2563EB] shadow-sm'"
                  required
                  autocomplete="new-password"
                />
                <p v-if="form.errors.password" class="text-red-400 text-xs mt-1.5 font-semibold">⚠️ {{ form.errors.password }}</p>
              </div>

              <!-- Konfirmasi Password -->
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                  KONFIRMASI KATA SANDI BARU <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="Ketik ulang kata sandi baru"
                  class="w-full px-4 py-3 text-sm rounded-xl outline-none transition-all duration-200"
                  :class="settings?.login_background 
                    ? 'bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:border-blue-400 focus:bg-white/20' 
                    : 'bg-white border border-[#E2E8F0] text-slate-800 focus:border-[#2563EB] shadow-sm'"
                  required
                  autocomplete="new-password"
                />
              </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-3">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-3.5 bg-[#2563EB] hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/20 transition-all cursor-pointer flex items-center justify-center gap-2"
              >
                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Perbarui Kata Sandi</span>
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const page = usePage();
const settings = computed(() => page.props.settings || {});

const { alertSuccess, alertError } = useSwal();

const form = useForm({
  username: '',
  otp: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('password.update-otp'), {
    onSuccess: () => {
      alertSuccess('Sandi Diperbarui', 'Kata sandi Anda berhasil di-reset. Silakan login kembali.');
    },
    onError: (errors) => {
      const firstErr = Object.values(errors)[0] || 'Gagal mereset kata sandi.';
      alertError('Reset Gagal', firstErr);
    }
  });
};
</script>
