<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-between select-none relative overflow-hidden">
    <!-- Ambient Background Lighting -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Instansi -->
    <header class="py-6 px-6 sm:px-12 flex items-center justify-between border-b border-slate-800/80 bg-slate-900/40 backdrop-blur-md relative z-10">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center">
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-sm sm:text-base font-black tracking-wider text-white">
            SISFOPERS KC
          </h1>
          <p class="text-[10px] text-slate-400 font-semibold tracking-widest uppercase">
            Portal Konferensi Video & Rapat Dinas
          </p>
        </div>
      </div>

      <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/80 text-[11px] text-slate-300">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span>Jalur Satelit Terenkripsi</span>
      </div>
    </header>

    <!-- Main Form & Device Preview -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-8 relative z-10 my-auto">
      <!-- JIKA ROOM DITEMUKAN: PRATINJAU KAMERA & FORM IDENTITAS TAMU -->
      <div v-if="room" class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-900/80 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
        
        <!-- Kolom Kiri: Pratinjau Kamera & Status Ruang -->
        <div class="lg:col-span-6 flex flex-col justify-between space-y-4">
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                Sesi Terbuka
              </span>
              <span class="text-[11px] font-mono text-slate-400">Kode: {{ room.room_code }}</span>
            </div>
            <h2 class="text-lg sm:text-xl font-black text-white leading-snug">
              {{ room.title }}
            </h2>
            <p v-if="room.description" class="text-xs text-slate-400 line-clamp-3">
              {{ room.description }}
            </p>
          </div>

          <!-- Video Pratinjau Kamera Lokal -->
          <div class="relative w-full aspect-video bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center shadow-inner">
            <video 
              ref="previewVideoRef" 
              autoplay 
              playsinline 
              muted 
              class="w-full h-full object-cover transform -scale-x-100"
              :class="isPreviewCamOn ? 'block' : 'hidden'"
            ></video>

            <div v-if="!isPreviewCamOn" class="flex flex-col items-center justify-center space-y-2 text-slate-500">
              <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <span class="text-xs font-semibold">Pratinjau Kamera Non-aktif</span>
            </div>

            <!-- Kontrol Pratinjau Cepat -->
            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between pointer-events-auto">
              <span class="px-2 py-0.5 rounded-lg bg-slate-900/80 text-[10px] text-slate-300 font-medium backdrop-blur-xs border border-slate-800">
                Uji Perangkat
              </span>
              <button 
                @click="togglePreviewCam" 
                type="button" 
                class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-[10px] font-bold text-slate-200 border border-slate-700 transition cursor-pointer"
              >
                {{ isPreviewCamOn ? 'Matikan Kamera' : 'Nyalakan Kamera' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Form Data Diri Tamu -->
        <div class="lg:col-span-6 flex flex-col justify-center space-y-5 border-t lg:border-t-0 lg:border-l border-slate-800 pt-6 lg:pt-0 lg:pl-6">
          <div class="space-y-1">
            <h3 class="text-base font-bold text-white">Identitas Tamu Undangan</h3>
            <p class="text-xs text-slate-400">
              Silakan masukkan nama dan instansi Anda untuk bergabung ke ruang rapat.
            </p>
          </div>

          <form @submit.prevent="submitJoin" class="space-y-4">
            <!-- Nama Lengkap -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-300 uppercase">
                Nama Lengkap & Gelar <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="form.name" 
                type="text" 
                required
                placeholder="Contoh: Kolonel Inf. Bambang / Bpk. Hendro" 
                class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
              />
            </div>

            <!-- Instansi / Lembaga -->
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-300 uppercase">
                Instansi / Lembaga / Jabatan (Opsional)
              </label>
              <input 
                v-model="form.institution" 
                type="text" 
                placeholder="Contoh: Kementerian Pertahanan RI / Kodam Jaya" 
                class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
              />
            </div>

            <!-- Kata Sandi Ruang (Jika Ada) -->
            <div v-if="room.has_passcode" class="space-y-1">
              <label class="block text-xs font-bold text-amber-400 uppercase">
                Kata Sandi Ruang Rapat <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="form.passcode" 
                type="password" 
                required
                placeholder="Masukkan kata sandi yang diberikan host..." 
                class="w-full px-4 py-3 bg-slate-950 border border-amber-500/50 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition"
              />
            </div>

            <div class="pt-2">
              <button 
                type="submit" 
                :disabled="isJoining"
                class="w-full py-3.5 px-6 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-black rounded-2xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <svg v-if="isJoining" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>{{ isJoining ? 'Memverifikasi Akses...' : 'Masuk Ruang Rapat Dinas' }}</span>
              </button>
            </div>
          </form>

          <p class="text-[10px] text-slate-500 text-center">
            Dengan masuk ke ruang rapat, Anda menyetujui penggunaan kamera dan mikrofon selama konferensi berlangsung.
          </p>
        </div>
      </div>

      <!-- JIKA ROOM KOSONG / BELUM DIMASUKKAN: INPUT KODE RUANG RAPAT -->
      <div v-else class="w-full max-w-md bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl space-y-6">
        <div class="text-center space-y-2">
          <div class="w-14 h-14 rounded-2xl bg-blue-600/20 border border-blue-500/30 text-blue-400 flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
          <h2 class="text-xl font-black text-white tracking-tight">Gabung Rapat Dinas</h2>
          <p class="text-xs text-slate-400">
            Masukkan kode ruang rapat atau tautan yang Anda terima dari penyelenggara dinas.
          </p>
        </div>

        <div v-if="errorMessage" class="p-3.5 bg-red-500/10 border border-red-500/30 rounded-2xl text-xs text-red-400 flex items-start gap-2.5">
          <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div>{{ errorMessage }}</div>
        </div>

        <form @submit.prevent="submitRoomCode" class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-300 uppercase">Kode Ruang Rapat</label>
            <input 
              v-model="inputCode" 
              type="text" 
              required
              placeholder="Contoh: VICON-KC-XXXXXX"
              class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white uppercase font-mono tracking-wider placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
            />
          </div>

          <button 
            type="submit" 
            class="w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-black rounded-2xl shadow-lg shadow-blue-600/20 transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <span>Lanjutkan ke Ruang Rapat</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </button>
        </form>

        <div class="pt-4 border-t border-slate-800/80 text-center">
          <a href="/login" class="text-xs text-slate-400 hover:text-white transition font-medium">
            Masuk dengan Akun SISFOPERS KC &rarr;
          </a>
        </div>
      </div>
    </main>

    <!-- Footer Ringkas -->
    <footer class="py-4 px-6 text-center text-[10px] text-slate-500 border-t border-slate-900 relative z-10">
      &copy; 2026 Komponen Cadangan Republik Indonesia &bull; SISFOPERS KC
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

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
