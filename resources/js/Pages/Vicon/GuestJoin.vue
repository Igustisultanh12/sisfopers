<template>
  <div 
    class="min-h-screen flex text-[#334155] font-sans bg-cover bg-center relative transition-all duration-300"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>

    <!-- KONDISI 1: JIKA ROOM BELUM DITENTUKAN (AKSES LANGSUNG KE /vicon/join) -->
    <!-- TAMPILAN PERSIS SEPERTI HALAMAN LOGIN (SPLIT 2 KOLOM) -->
    <div v-if="!room" class="w-full min-h-screen flex flex-col lg:flex-row relative z-10">
      <!-- Bagian Kiri: Logo Tri Matra & Informasi Aplikasi -->
      <div 
        class="hidden lg:flex lg:w-1/2 flex-col justify-between p-16 transition-all duration-300 z-10"
        :class="settings?.login_background ? 'text-white' : 'bg-gradient-to-b from-[#2563EB]/5 to-transparent'"
      >
        <div></div>
        
        <!-- Preview Logo Tri Matra -->
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

          <div>
            <h2 class="text-3xl font-extrabold tracking-tight uppercase" :class="settings?.login_background ? 'text-white drop-shadow-md' : 'text-slate-800'">
              {{ settings?.app_name || 'SISFOPERSKC' }} INTEGRASI TNI
            </h2>
            <p class="text-sm mt-3 leading-relaxed max-w-md mx-auto" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-500'">
              Fasilitas konferensi video dan rapat dinas terintegrasi Komponen Cadangan untuk koordinasi dinas, perwira, dan tamu eksternal.
            </p>
          </div>
        </div>

        <p class="text-xs" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-400'">
          © 2026 {{ settings?.app_name || 'SISFOPERSKC' }}. All Rights Reserved.
        </p>
      </div>

      <!-- Bagian Kanan: Card Form Gabung Rapat -->
      <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-8 z-10 my-auto">
        <div 
          class="w-full max-w-md p-8 sm:p-10 rounded-2xl shadow-2xl transition-all duration-300 animate-float-card"
          :class="settings?.login_background 
            ? 'bg-slate-900/60 backdrop-blur-md border border-white/10 text-white' 
            : 'bg-white border border-[#E2E8F0] shadow-slate-200/50 text-[#334155]'"
        >
          <div class="mb-8">
            <div class="flex items-center gap-2 mb-2">
              <span 
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide"
                :class="settings?.login_background ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30' : 'bg-blue-50 text-blue-600 border border-blue-200'"
              >
                KONFERENSI VIDEO DINAS
              </span>
            </div>
            <h3 class="text-xl font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
              Gabung Rapat Dinas
            </h3>
            <p class="text-xs mt-1" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
              Masukkan kode ruang rapat atau tautan yang Anda terima dari penyelenggara.
            </p>
          </div>

          <!-- Pesan Kesalahan jika ada -->
          <div v-if="errorMessage" class="mb-6 p-4 rounded-xl text-xs flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-500 font-medium">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>{{ errorMessage }}</div>
          </div>

          <!-- Form Input Kode -->
          <form @submit.prevent="submitRoomCode" class="space-y-5">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                Kode Ruang Rapat
              </label>
              <input 
                v-model="inputCode" 
                type="text" 
                required
                placeholder="Contoh: VICON-KC-XXXXXX"
                class="w-full px-4 py-2.5 rounded-lg border text-sm font-mono uppercase tracking-wider outline-none transition duration-150 focus:ring-2"
                :class="settings?.login_background 
                  ? 'bg-white/10 border-white/10 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                  : 'bg-white border-[#E2E8F0] focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
              />
            </div>

            <button 
              type="submit" 
              class="w-full py-3 px-4 text-white text-sm font-semibold rounded-lg shadow-lg transition duration-150 disabled:opacity-50 cursor-pointer flex justify-center items-center gap-2"
              :class="settings?.login_background 
                ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' 
                : 'bg-[#2563EB] hover:bg-[#1E40AF] shadow-blue-500/10'"
            >
              <span>Lanjutkan ke Ruang Rapat</span>
              <span v-if="settings?.login_background">➜</span>
            </button>
          </form>

          <!-- Footer Tautan -->
          <div class="mt-8 text-center border-t pt-6" :class="settings?.login_background ? 'border-white/10' : 'border-[#E2E8F0]'">
            <p class="text-xs" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
              Personel terdaftar? 
              <Link 
                :href="route('login')" 
                class="font-semibold hover:underline"
                :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'"
              >
                Masuk dengan Akun SISFOPERS KC
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- KONDISI 2: JIKA ROOM DITEMUKAN / DIBUKA DENGAN KODE -->
    <!-- TAMPILAN PERSIS SEPERTI FORMULIR REGISTER DENGAN CARD KONTEN TERSTRUKTUR -->
    <div v-else class="w-full min-h-screen py-10 px-4 sm:px-6 lg:px-8 relative z-10 flex items-center justify-center">
      <div 
        class="w-full max-w-4xl mx-auto rounded-2xl shadow-xl transition-all duration-300 overflow-hidden"
        :class="settings?.login_background 
          ? 'bg-slate-900/60 backdrop-blur-md border border-white/10 text-white' 
          : 'bg-white border border-[#E2E8F0] shadow-slate-100/60 text-[#334155]'"
      >
        <!-- Header Rapat -->
        <div 
          class="p-6 sm:p-8 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-300"
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
                Lobby Konferensi Rapat Dinas
              </h2>
              <p class="text-xs mt-1" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
                Verifikasi kesiapan perangkat audio-video dan lengkapi identitas tamu Anda.
              </p>
            </div>
          </div>

          <Link 
            :href="route('vicon.guest.join')" 
            class="text-xs font-semibold hover:underline"
            :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'"
          >
            Ganti Kode Ruang
          </Link>
        </div>

        <!-- Body: 2 Kolom (Kiri: Info Ruang & Kamera, Kanan: Form Tamu) -->
        <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
          <!-- Kolom Kiri: Parameter Rapat & Pratinjau Kamera -->
          <div class="lg:col-span-6 space-y-5">
            <div 
              class="p-4 rounded-xl border space-y-2"
              :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-slate-200'"
            >
              <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  Sesi Rapat Terbuka
                </span>
                <span class="text-xs font-mono font-bold" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                  {{ room.room_code }}
                </span>
              </div>
              <h3 class="text-base font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-900'">
                {{ room.title }}
              </h3>
              <p v-if="room.description" class="text-xs leading-relaxed" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
                {{ room.description }}
              </p>
            </div>

            <!-- Pratinjau Kamera -->
            <div class="space-y-2">
              <label class="block text-xs font-semibold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                Pratinjau Perangkat Kamera
              </label>
              
              <div class="relative w-full aspect-video bg-slate-900 rounded-xl overflow-hidden border border-slate-700/60 shadow-inner flex items-center justify-center">
                <video 
                  ref="previewVideoRef" 
                  autoplay 
                  playsinline 
                  muted 
                  class="w-full h-full object-cover transform -scale-x-100"
                  :class="isPreviewCamOn ? 'block' : 'hidden'"
                ></video>

                <div v-if="!isPreviewCamOn" class="flex flex-col items-center justify-center space-y-2 text-slate-400 p-4 text-center">
                  <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <span class="text-xs font-medium">Kamera Pratinjau Non-aktif</span>
                </div>

                <div class="absolute bottom-3 right-3">
                  <button 
                    @click="togglePreviewCam" 
                    type="button" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-md cursor-pointer flex items-center gap-1.5"
                    :class="isPreviewCamOn ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-slate-800 hover:bg-slate-700 text-white border border-slate-600'"
                  >
                    {{ isPreviewCamOn ? 'Matikan Kamera' : 'Nyalakan Kamera' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Kolom Kanan: Form Tamu -->
          <div class="lg:col-span-6 space-y-5 flex flex-col justify-between">
            <div class="space-y-4">
              <div>
                <h4 class="text-sm font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
                  Identitas Peserta Tamu
                </h4>
                <p class="text-xs mt-0.5" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
                  Nama dan instansi ini akan ditampilkan pada konferensi video dinas.
                </p>
              </div>

              <form @submit.prevent="submitJoin" class="space-y-4">
                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                    Nama Lengkap & Gelar <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="form.name" 
                    type="text" 
                    required
                    placeholder="Contoh: Kolonel Inf. Bambang / Bpk. Hendro" 
                    class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2"
                    :class="settings?.login_background 
                      ? 'bg-white/10 border-white/10 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                      : 'bg-white border-[#E2E8F0] focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
                  />
                </div>

                <div>
                  <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                    Instansi / Satuan / Jabatan
                  </label>
                  <input 
                    v-model="form.institution" 
                    type="text" 
                    placeholder="Contoh: Kementerian Pertahanan RI / Kodam Jaya" 
                    class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2"
                    :class="settings?.login_background 
                      ? 'bg-white/10 border-white/10 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                      : 'bg-white border-[#E2E8F0] focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
                  />
                </div>

                <div v-if="room.has_passcode">
                  <label class="block text-xs font-semibold uppercase tracking-wider mb-1.5 text-amber-500">
                    Kata Sandi Ruang Rapat <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="form.passcode" 
                    type="password" 
                    required
                    placeholder="Masukkan kata sandi dari Host..." 
                    class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2"
                    :class="settings?.login_background 
                      ? 'bg-white/10 border-amber-500/50 text-white placeholder-slate-400 focus:ring-amber-400' 
                      : 'bg-white border-amber-300 focus:ring-amber-500/20 focus:border-amber-500'"
                  />
                </div>

                <div class="pt-2">
                  <button 
                    type="submit" 
                    :disabled="isJoining"
                    class="w-full py-3 px-4 text-white text-sm font-semibold rounded-lg shadow-lg transition duration-150 disabled:opacity-50 cursor-pointer flex justify-center items-center gap-2"
                    :class="settings?.login_background 
                      ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' 
                      : 'bg-[#2563EB] hover:bg-[#1E40AF] shadow-blue-500/10'"
                  >
                    <svg v-if="isJoining" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>{{ isJoining ? 'Memverifikasi Akses...' : 'Masuk Ruang Rapat Dinas' }}</span>
                    <span v-if="!isJoining && settings?.login_background">➜</span>
                  </button>
                </div>
              </form>
            </div>

            <p class="text-[11px] leading-relaxed text-center" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">
              Dengan bergabung, Anda menyetujui transmisi mikrofon dan kamera untuk keperluan rapat dinas kedinasan.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import axios from 'axios';

const page = usePage();
const settings = computed(() => page.props.settings || {});

const props = defineProps({
  room: { type: Object, default: null },
  errorMessage: { type: String, default: null },
});

const inputCode = ref('');

const submitRoomCode = () => {
  let code = inputCode.value.trim();
  if (!code) return;
  if (code.includes('/join/')) {
    code = code.split('/join/')[1].split('?')[0].split('#')[0];
  } else if (code.includes('/room/')) {
    code = code.split('/room/')[1].split('?')[0].split('#')[0];
  }
  code = code.replace(/[^a-zA-Z0-9_-]/g, '');
  if (code) {
    window.location.href = `/vicon/join/${code}`;
  }
};

const form = ref({
  name: '',
  institution: '',
  passcode: '',
});

const isJoining = ref(false);
const previewVideoRef = ref(null);
const isPreviewCamOn = ref(false);
let localStream = null;

const togglePreviewCam = async () => {
  if (isPreviewCamOn.value) {
    if (localStream) {
      localStream.getTracks().forEach(t => t.stop());
      localStream = null;
    }
    isPreviewCamOn.value = false;
  } else {
    try {
      localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
      if (previewVideoRef.value) {
        previewVideoRef.value.srcObject = localStream;
      }
      isPreviewCamOn.value = true;
    } catch (e) {
      console.warn('Gagal mengakses kamera pratinjau:', e);
      Swal.fire({
        icon: 'warning',
        title: 'Izin Kamera Diperlukan',
        text: 'Silakan berikan izin akses kamera pada peramban Anda untuk melakukan pratinjau.',
        confirmButtonColor: '#2563EB',
      });
    }
  }
};

const submitJoin = async () => {
  if (isJoining.value || !props.room) return;
  isJoining.value = true;

  try {
    const res = await axios.post(`/vicon/join/${props.room.room_code}`, form.value);
    if (res.data?.redirect) {
      if (localStream) {
        localStream.getTracks().forEach(t => t.stop());
      }
      window.location.href = res.data.redirect;
    }
  } catch (err) {
    const msg = err.response?.data?.error || 'Gagal masuk ke ruang rapat dinas.';
    Swal.fire({
      icon: 'error',
      title: 'Tidak Dapat Bergabung',
      text: msg,
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  } finally {
    isJoining.value = false;
  }
};

onMounted(() => {
  if (props.room) {
    togglePreviewCam();
  }
});

onUnmounted(() => {
  if (localStream) {
    localStream.getTracks().forEach(t => t.stop());
  }
});
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
