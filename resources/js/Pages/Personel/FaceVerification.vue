<template>
  <div 
    class="min-h-screen flex text-[#334155] font-sans bg-cover bg-center relative transition-all duration-300"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>
    
    <!-- Bagian Kiri: Logo Tri Matra & Informasi Aplikasi (Desktop) -->
    <div 
      class="hidden lg:flex lg:w-1/2 flex-col justify-between p-16 transition-all duration-300 z-10"
      :class="settings?.login_background ? 'text-white' : 'bg-gradient-to-b from-[#2563EB]/5 to-transparent'"
    >
      <div></div>
      
      <!-- Live Preview Logo Tri Matra -->
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

    <!-- Bagian Kanan: Card Form Verifikasi OTP -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 z-10">
      <div 
        class="w-full max-w-md p-8 sm:p-10 rounded-2xl shadow-2xl transition-all duration-300 animate-float-card space-y-6"
        :class="settings?.login_background 
          ? 'bg-slate-900/40 backdrop-blur-md border border-white/10 text-white' 
          : 'bg-white border border-[#E2E8F0] shadow-slate-200/50 text-[#334155]'"
      >
        <!-- Header logo di mobile -->
        <div class="lg:hidden flex items-center justify-center gap-3 mb-4">
          <img v-if="settings?.logo_tni" :src="settings.logo_tni" class="h-12 object-contain" />
        </div>

        <div class="space-y-1">
          <div class="w-12 h-12 bg-[#2563EB]/10 text-[#2563EB] flex items-center justify-center rounded-xl font-bold text-xl mb-3">
            🔑
          </div>
          <h3 class="text-xl font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">Verifikasi Akun Personel</h3>
          <p class="text-xs leading-relaxed" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
            Demi keamanan akses sistem SISFOPERSKC, silakan verifikasi kepemilikan akun Anda menggunakan Kode OTP.
          </p>
        </div>

        <!-- Banner jika Request OTP WhatsApp Mandiri Dinonaktifkan Admin -->
        <div 
          v-if="disable_whatsapp_otp" 
          class="p-4 rounded-xl text-xs font-semibold leading-relaxed border"
          :class="settings?.login_background ? 'bg-amber-500/20 border-amber-400/30 text-amber-200' : 'bg-amber-50 border-amber-200 text-amber-800'"
        >
          ⚠️ <strong>Info Administrator:</strong> Permintaan OTP WhatsApp mandiri dinonaktifkan. Silakan masukkan <strong>Kode OTP Manual</strong> yang tertera pada lembar cetak yang diberikan oleh Administrator.
        </div>

        <!-- Area Tampilan Nomor HP dan Tombol Kirim (Hanya jika WA OTP tidak mati) -->
        <div 
          v-if="!disable_whatsapp_otp && !otpSent && !showManualForm" 
          class="p-5 rounded-xl text-center space-y-4 border animate-slide-up"
          :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
        >
          <div class="space-y-1">
            <span class="text-[10px] uppercase font-bold tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">Nomor WhatsApp Terdaftar</span>
            <p class="text-lg font-bold tracking-wide" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">{{ masked_phone }}</p>
          </div>

          <button 
            @click="sendOtp" 
            :disabled="loading" 
            class="w-full py-3 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/20 transition cursor-pointer flex items-center justify-center gap-2"
          >
            <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-else>Kirim Kode OTP via WhatsApp</span>
          </button>
        </div>

        <!-- Form Input OTP WhatsApp (Muncul setelah OTP dikirim) -->
        <form v-else-if="!disable_whatsapp_otp && otpSent && !showManualForm" @submit.prevent="verifyOtp" class="space-y-4 animate-slide-up">
          <div 
            class="p-5 rounded-xl text-center space-y-4 border"
            :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
          >
            <div class="space-y-1">
              <span class="text-[10px] uppercase font-bold tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">Masukkan 6 Digit OTP</span>
              <p class="text-xs" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
                Kode telah dikirimkan ke <span class="font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-700'">{{ masked_phone }}</span>
              </p>
            </div>

            <div class="flex justify-center">
              <input 
                type="text" 
                v-model="form.otp" 
                maxlength="6" 
                class="w-48 px-4 py-2.5 rounded-xl border text-center font-bold tracking-widest text-lg outline-none transition" 
                :class="settings?.login_background ? 'bg-white/10 border-white/20 text-white focus:border-blue-400' : 'bg-white border-[#E2E8F0] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/20 text-slate-800'"
                placeholder="******" 
                required 
              />
            </div>

            <div class="text-xs" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">
              Tidak menerima kode? 
              <button type="button" @click="sendOtp" :disabled="countdown > 0 || loading" class="font-bold hover:underline transition disabled:opacity-50" :class="settings?.login_background ? 'text-blue-400' : 'text-[#2563EB]'">
                {{ countdown > 0 ? `Kirim Ulang (${countdown}s)` : 'Kirim Ulang' }}
              </button>
            </div>
          </div>

          <button type="submit" :disabled="form.processing" class="w-full py-3 bg-[#22C55E] hover:bg-[#16A34A] disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-lg shadow-green-500/20 transition cursor-pointer flex items-center justify-center">
            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Verifikasi & Masuk
          </button>
        </form>

        <!-- Form OTP Manual (Aktif jika sakelar Admin aktif atau dipilih) -->
        <form v-if="showManualForm || disable_whatsapp_otp" @submit.prevent="verifyManualOtp" class="space-y-4 animate-slide-up">
          <div 
            class="p-5 rounded-xl text-center space-y-4 border"
            :class="settings?.login_background ? 'bg-amber-500/10 border-amber-500/30' : 'bg-amber-50/60 border-amber-200/80'"
          >
            <div class="space-y-1">
              <span class="text-[10px] uppercase font-bold tracking-wider text-amber-700">🔑 Kode OTP Manual (dari Admin)</span>
              <p class="text-xs" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
                Masukkan 6 digit kode OTP yang tertera pada lembar cetak yang diberikan oleh administrator Anda.
              </p>
            </div>
            <div class="flex justify-center">
              <input
                type="text"
                v-model="manualForm.otp"
                maxlength="6"
                @input="manualForm.otp = manualForm.otp.replace(/\D/g, '').slice(0, 6)"
                class="w-48 px-4 py-2.5 bg-white border border-amber-300 text-slate-800 text-center font-bold tracking-widest text-lg rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition"
                placeholder="······"
                required
              />
            </div>
            <div v-if="manualForm.errors.otp" class="text-red-500 text-xs font-semibold">⚠️ {{ manualForm.errors.otp }}</div>
          </div>

          <button
            type="submit"
            :disabled="manualForm.processing || manualForm.otp.length !== 6"
            class="w-full py-3 bg-amber-600 hover:bg-amber-700 disabled:opacity-40 text-white text-xs font-bold rounded-xl shadow-lg shadow-amber-500/20 transition cursor-pointer flex items-center justify-center gap-2"
          >
            <svg v-if="manualForm.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Verifikasi Kode OTP Manual
          </button>

          <button
            v-if="!disable_whatsapp_otp"
            type="button"
            @click="showManualForm = false"
            class="w-full text-center text-xs font-semibold hover:underline transition mt-2"
            :class="settings?.login_background ? 'text-slate-400 hover:text-white' : 'text-slate-500 hover:text-slate-700'"
          >
            ← Kembali ke OTP WhatsApp
          </button>
        </form>

        <!-- Toggle ke form OTP Manual jika WA OTP aktif tapi ingin masukan manual -->
        <div v-if="!disable_whatsapp_otp && !showManualForm" class="space-y-3">
          <div class="relative flex items-center gap-3 py-1">
            <div class="flex-1 h-px bg-slate-200"></div>
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider whitespace-nowrap">atau</span>
            <div class="flex-1 h-px bg-slate-200"></div>
          </div>
          <button
            type="button"
            @click="showManualForm = true"
            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition cursor-pointer flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Gunakan Kode OTP Manual (dari Admin)
          </button>
        </div>

        <div class="pt-2 border-t border-[#E2E8F0]/60">
          <Link :href="route('logout')" method="post" as="button" class="w-full text-center text-xs text-red-500 hover:text-red-700 font-semibold transition py-1 block cursor-pointer">
            Keluar dari Sesi Sisfopers
          </Link>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';
import axios from 'axios';

const props = defineProps({
  masked_phone: String,
  disable_whatsapp_otp: Boolean
});

const page = usePage();
const settings = computed(() => page.props.settings || {});
const { alertSuccess, alertError } = useSwal();

const otpSent = ref(false);
const loading = ref(false);
const countdown = ref(0);
const showManualForm = ref(props.disable_whatsapp_otp || false);
let timer = null;

const form = useForm({ otp: '' });
const manualForm = useForm({ otp: '' });

const startTimer = () => {
  countdown.value = 60;
  if (timer) clearInterval(timer);
  timer = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value--;
    } else {
      clearInterval(timer);
    }
  }, 1000);
};

onBeforeUnmount(() => {
  if (timer) clearInterval(timer);
});

const sendOtp = async () => {
  loading.value = true;
  try {
    const res = await axios.post(route('personel.face-verification.request-otp'));
    if (res.data.success) {
      otpSent.value = true;
      startTimer();
      alertSuccess('Berhasil', res.data.message);
    } else {
      alertError('Gagal', res.data.message);
    }
  } catch (err) {
    alertError('Gagal', err.response?.data?.message || 'Terjadi kesalahan sistem saat mengirim OTP.');
  } finally {
    loading.value = false;
  }
};

const verifyOtp = () => {
  form.post(route('personel.face-verification.verify-otp'), {
    onSuccess: () => {
      alertSuccess('Verifikasi Berhasil', 'Akun Anda berhasil diverifikasi. Selamat datang!');
    },
    onError: (errors) => {
      alertError('Verifikasi Gagal', errors.otp || 'Kode OTP tidak cocok atau sudah kadaluarsa.');
    }
  });
};

const verifyManualOtp = () => {
  manualForm.post(route('personel.face-verification.otp-manual'), {
    onSuccess: () => {
      alertSuccess('Verifikasi Berhasil', 'Kode OTP Manual cocok. Akun Anda berhasil diverifikasi!');
    },
    onError: (errors) => {
      alertError('Verifikasi Gagal', errors.otp || 'Kode OTP Manual tidak cocok atau sudah kadaluarsa.');
    }
  });
};
</script>