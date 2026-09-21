<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-0 sm:p-4 select-none overscroll-none touch-none">
    
    <!-- 1. KONDISI A: PANGGILAN KELUAR (OUTGOING CALLING) -->
    <div 
      v-if="callStatus === 'OUTGOING'" 
      class="w-full h-full sm:h-auto max-w-md bg-slate-900 border-0 sm:border border-slate-700/80 rounded-none sm:rounded-3xl p-6 sm:p-8 text-center text-white shadow-2xl space-y-6 relative overflow-hidden flex flex-col justify-center"
    >
      <!-- Background Ambient Glow -->
      <div class="absolute -top-24 -left-24 w-48 h-48 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>

      <div class="space-y-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
          <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
          Panggilan Video Dinas
        </span>
        <h3 class="text-base font-bold text-slate-200">Menghubungkan Jalur Satelit...</h3>
      </div>

      <!-- Avatar dengan Animasi Gelombang Berdenyut -->
      <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
        <div class="absolute inset-0 rounded-full bg-blue-500/20 animate-ping"></div>
        <div class="absolute -inset-2 rounded-full border border-blue-500/30 animate-pulse"></div>
        <img 
          :src="partnerUser?.photo ? `/documents/private-stream?path=${encodeURIComponent(partnerUser.photo)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(partnerUser?.name || 'P')}&background=1e293b&color=94a3b8`" 
          class="w-24 h-24 object-cover rounded-full border-2 border-blue-400 shadow-xl relative z-10"
        />
      </div>

      <div class="space-y-1">
        <h4 class="text-lg font-black text-white">
          {{ partnerUser?.pangkat }} {{ partnerUser?.name }}
        </h4>
        <p class="text-xs text-slate-400">
          <span v-if="partnerUser?.nikc">NIKC: {{ partnerUser.nikc }} &bull; </span>
          <span v-if="partnerUser?.matra">Matra {{ partnerUser.matra }}</span>
        </p>
      </div>

      <div v-if="partnerUser?.is_online === false" class="px-3 py-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-[11px] text-amber-300">
        Perhatian: Personel saat ini berstatus offline. Sinyal panggilan tetap dipancarkan dan akan berdering saat personel membuka aplikasi.
      </div>

      <div class="space-y-1">
        <p class="text-xs text-slate-400 animate-pulse">
          Menunggu lawan bicara menerima sambungan...
        </p>
        <p class="text-[11px] font-mono text-slate-500">
          Batas waktu panggil: {{ Math.max(0, 40 - outgoingSeconds) }} detik
        </p>
      </div>

      <!-- Tombol Batalkan Panggilan -->
      <div class="pt-2">
        <button 
          @click="cancelOutgoingCall" 
          type="button" 
          class="w-full py-3 px-6 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-black rounded-2xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span>Batalkan Panggilan</span>
        </button>
      </div>
    </div>

    <!-- 2. KONDISI B: PANGGILAN MASUK (INCOMING CALL DIALOG) -->
    <div 
      v-else-if="callStatus === 'INCOMING'" 
      class="w-full h-full sm:h-auto max-w-md bg-slate-900 border-0 sm:border border-slate-700/80 rounded-none sm:rounded-3xl p-6 sm:p-8 text-center text-white shadow-2xl space-y-6 relative overflow-hidden flex flex-col justify-center"
    >
      <div class="absolute -top-20 -right-20 w-40 h-40 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

      <div class="space-y-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
          Panggilan Video Dinas Masuk
        </span>
        <h3 class="text-base font-bold text-slate-200">Permintaan Sambungan Langsung</h3>
      </div>

      <!-- Avatar Pemanggil -->
      <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
        <div class="absolute inset-0 rounded-full bg-emerald-500/20 animate-ping"></div>
        <div class="absolute -inset-2 rounded-full border border-emerald-500/30 animate-pulse"></div>
        <img 
          :src="activeCaller?.photo ? `/documents/private-stream?path=${encodeURIComponent(activeCaller.photo)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(activeCaller?.name || 'P')}&background=1e293b&color=94a3b8`" 
          class="w-24 h-24 object-cover rounded-full border-2 border-emerald-400 shadow-xl relative z-10"
        />
      </div>

      <div class="space-y-1">
        <h4 class="text-lg font-black text-white">
          {{ activeCaller?.pangkat }} {{ activeCaller?.name }}
        </h4>
        <p class="text-xs text-slate-400">
          Pusat Layanan Informasi SISFOPERS KC
        </p>
      </div>

      <!-- Tombol Aksi Terima & Tolak -->
      <div class="grid grid-cols-2 gap-3 pt-2">
        <button 
          @click="rejectIncomingCall" 
          type="button" 
          class="py-3 px-4 bg-slate-800 hover:bg-red-600/90 text-slate-200 hover:text-white text-xs font-black rounded-2xl border border-slate-700 transition flex items-center justify-center gap-2 cursor-pointer active:scale-95"
        >
          <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span>Tolak</span>
        </button>

        <button 
          @click="acceptIncomingCall" 
          type="button" 
          class="py-3 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-black rounded-2xl shadow-lg transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <span>Terima Video</span>
        </button>
      </div>
    </div>

    <!-- 3. KONDISI C: RUANG VICON AKTIF (CONNECTED / CONNECTING ROOM JITSI SFU) -->
    <div 
      v-else 
      :class="isFullscreen ? 'fixed inset-0 z-50 rounded-none' : 'w-full max-w-5xl h-[100dvh] sm:h-[90vh] sm:max-h-[760px] rounded-none sm:rounded-3xl border-0 sm:border border-slate-800'"
      class="bg-slate-900 shadow-2xl flex flex-col overflow-hidden relative overscroll-none"
    >
      <!-- Bilah Header Atas Ruang Vicon -->
      <div class="h-14 px-3 sm:px-6 bg-slate-950/90 backdrop-blur-md border-b border-slate-800/80 flex items-center justify-between shrink-0 z-20">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
          <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></div>
          <div class="min-w-0">
            <h4 class="text-xs sm:text-sm font-black text-white flex items-center gap-1.5 sm:gap-2 truncate">
              <span class="truncate">{{ partnerUser?.pangkat }} {{ partnerUser?.name }}</span>
              <span class="hidden sm:inline text-[10px] font-bold text-slate-400 bg-slate-800 px-2 py-0.5 rounded-md shrink-0">
                {{ partnerUser?.matra ? `Matra ${partnerUser.matra}` : 'Dinas' }}
              </span>
            </h4>
            <div class="flex items-center gap-1.5 sm:gap-2 text-[10px] text-slate-400">
              <span>Durasi: <strong class="text-slate-200 font-mono">{{ formatDuration(callDuration) }}</strong></span>
              <span>&bull;</span>
              <span class="text-emerald-400 font-semibold truncate">{{ connectionStatusText }}</span>
            </div>
          </div>
        </div>

        <!-- Tombol Aksi Kanan Header -->
        <div class="flex items-center gap-2 shrink-0">
          <!-- Lencana Keamanan Militer -->
          <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-950/60 border border-emerald-800/60 text-[10px] font-bold text-emerald-400">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>Terenkripsi E2E (SFU)</span>
          </div>

          <!-- Tombol Buka di Tab Baru (Fallback Handal untuk Browser HP) -->
          <a 
            :href="jitsiWebUrl" 
            target="_blank" 
            rel="noopener noreferrer"
            class="hidden sm:flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl border border-slate-700 transition"
            title="Buka ruang vicon pada tab mandiri browser jika kamera terblokir"
          >
            <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            <span>Buka Tab Mandiri</span>
          </a>

          <!-- Tombol Fullscreen -->
          <button 
            @click="toggleFullscreen" 
            type="button" 
            class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition cursor-pointer"
            :title="isFullscreen ? 'Kecilkan Layar' : 'Layar Penuh'"
          >
            <svg v-if="!isFullscreen" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Tombol Cepat Akhiri Panggilan -->
          <button 
            @click="hangUpCall" 
            type="button" 
            class="py-1.5 px-3 bg-red-600 hover:bg-red-700 text-white font-black text-xs rounded-xl shadow transition flex items-center gap-1 cursor-pointer active:scale-95"
            title="Akhiri Panggilan Dinas"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.684A1 1 0 008.279 3H5z" />
            </svg>
            <span>Akhiri</span>
          </button>
        </div>
      </div>

      <!-- Area Layar Jitsi Meet (SFU Terintegrasi) -->
      <div class="flex-1 min-h-0 bg-slate-950 relative flex items-center justify-center overflow-hidden">
        
        <!-- Wadah Iframe Jitsi Meet -->
        <div ref="jitsiContainerRef" id="jitsi-container" class="w-full h-full flex-1"></div>

        <!-- Loading Overlay saat Inisialisasi API Jitsi -->
        <div 
          v-if="isJitsiLoading" 
          class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950 text-center space-y-4 p-6 z-10"
        >
          <div class="relative w-20 h-20 flex items-center justify-center">
            <div class="absolute inset-0 rounded-full border-2 border-emerald-500/20 animate-ping"></div>
            <div class="w-16 h-16 rounded-full border-2 border-t-emerald-500 border-r-transparent border-b-blue-500 border-l-transparent animate-spin"></div>
            <img 
              :src="partnerUser?.photo ? `/documents/private-stream?path=${encodeURIComponent(partnerUser.photo)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(partnerUser?.name || 'P')}&background=1e293b&color=94a3b8`" 
              class="w-10 h-10 object-cover rounded-full absolute" 
            />
          </div>

          <div class="space-y-1">
            <h4 class="text-base font-bold text-white">{{ partnerUser?.pangkat }} {{ partnerUser?.name }}</h4>
            <p class="text-xs text-slate-400">
              Membuka bilik vicon militer terenkripsi bebas hambatan CGNAT...
            </p>
          </div>

          <div class="flex items-center gap-2 text-xs text-emerald-400">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            <span>{{ connectionStatusText }}</span>
          </div>

          <!-- Fallback jika memuat lama di browser mobile tertentu -->
          <div class="pt-2">
            <a 
              :href="jitsiWebUrl" 
              target="_blank" 
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600/20 hover:bg-blue-600/40 text-blue-300 text-xs font-bold rounded-xl border border-blue-500/30 transition"
            >
              <span>Klik di sini jika tampilan video tidak kunjung muncul</span>
            </a>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { 
  startIncomingCallRing, 
  stopIncomingCallRing, 
  startOutgoingDialRing, 
  stopOutgoingDialRing, 
  playCallEndedSound 
} from '@/Utils/sound';

const props = defineProps({
  show: Boolean,
  threadUuid: String,
  userRole: { type: String, default: 'OPERATOR' }, // 'OPERATOR' atau 'PERSONEL'
  currentUser: Object,
  partnerUser: Object,
  incomingCallData: Object,
  urlPrefix: { type: String, default: 'admin' },
});

const emit = defineEmits(['close', 'call-ended', 'call-accepted']);

// State Status Panggilan
const callStatus = ref('IDLE'); // 'IDLE' | 'OUTGOING' | 'INCOMING' | 'CONNECTING' | 'CONNECTED' | 'ENDED'
const callDuration = ref(0);
const connectionStatusText = ref('Menghubungkan...');
const activeCaller = ref(null);
const isInitiator = ref(false);
const isFullscreen = ref(false);

// State Penanda Waktu Timeout
const outgoingSeconds = ref(0);
const connectingSeconds = ref(0);
let outgoingTimeoutTimer = null;
let connectingTimeoutTimer = null;

// State Jitsi Meet
const jitsiContainerRef = ref(null);
const isJitsiLoading = ref(false);
let jitsiApiInstance = null;
let signalingTimer = null;
let durationTimer = null;

// Nama Ruang Bersama Unik per Utas Chat
const roomName = computed(() => {
  const cleanId = (props.threadUuid || 'dinas').replace(/[^a-zA-Z0-9]/g, '');
  return `SISFOPERSKC_${cleanId}`;
});

// URL Cadangan untuk Membuka Ruang Mandiri
const jitsiWebUrl = computed(() => {
  return `https://meet.jit.si/${roomName.value}#config.prejoinPageEnabled=false&config.disableDeepLinking=true`;
});

// Nama Tampilan Pengguna
const displayName = computed(() => {
  const rank = props.currentUser?.pangkat ? `${props.currentUser.pangkat} ` : '';
  const name = props.currentUser?.name || (props.userRole === 'OPERATOR' ? 'Operator Pelayanan' : 'Personel Komcad');
  return `${rank}${name}`.trim();
});

// Penentuan peran pemanggil
const isCaller = computed(() => {
  if (props.incomingCallData) {
    return (props.incomingCallData.caller?.type === props.userRole);
  }
  return Boolean(isInitiator.value);
});

const formatDuration = (totalSeconds) => {
  const m = Math.floor(totalSeconds / 60);
  const s = totalSeconds % 60;
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
};

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value;
};

// ==========================================
// 1. PEMUAT SKRIP JITSI MEET EXTERNAL API
// ==========================================
const loadJitsiScript = () => {
  return new Promise((resolve, reject) => {
    if (typeof window !== 'undefined' && window.JitsiMeetExternalAPI) {
      resolve(window.JitsiMeetExternalAPI);
      return;
    }
    const existingScript = document.getElementById('jitsi-meet-external-api');
    if (existingScript) {
      existingScript.addEventListener('load', () => resolve(window.JitsiMeetExternalAPI));
      existingScript.addEventListener('error', (e) => reject(e));
      return;
    }
    const script = document.createElement('script');
    script.id = 'jitsi-meet-external-api';
    script.src = 'https://meet.jit.si/external_api.js';
    script.async = true;
    script.onload = () => resolve(window.JitsiMeetExternalAPI);
    script.onerror = (e) => reject(e);
    document.head.appendChild(script);
  });
};

// ==========================================
// 2. INISIASI RUANG VICON JITSI MEET
// ==========================================
const initJitsiRoom = async () => {
  if (jitsiApiInstance) {
    return;
  }

  isJitsiLoading.value = true;
  connectionStatusText.value = 'Mempersiapkan bilik aman...';

  await nextTick();

  if (!jitsiContainerRef.value) {
    // Beri penundaan sedikit bila elemen DOM sedang dirender
    setTimeout(initJitsiRoom, 300);
    return;
  }

  try {
    const JitsiMeetExternalAPI = await loadJitsiScript();

    // Bersihkan isi kontainer sebelum memasang iframe baru
    jitsiContainerRef.value.innerHTML = '';

    const domain = 'meet.jit.si';
    const options = {
      roomName: roomName.value,
      width: '100%',
      height: '100%',
      parentNode: jitsiContainerRef.value,
      userInfo: {
        displayName: displayName.value,
        email: 'personel@sisfoperskc.my.id',
      },
      configOverwrite: {
        prejoinPageEnabled: false,
        disableDeepLinking: true,
        startWithAudioMuted: false,
        startWithVideoMuted: false,
        enableWelcomePage: false,
        enableClosePage: false,
        hideConferenceSubject: false,
        subject: `SISFOPERS KC - Vicon Dinas (${partnerUserDisplay()})`,
        disableThirdPartyRequests: true,
        toolbarButtons: [
          'microphone',
          'camera',
          'select-background',
          'desktop',
          'hangup',
          'tileview',
          'toggle-camera',
          'chat',
          'fullscreen'
        ],
      },
      interfaceConfigOverwrite: {
        SHOW_JITSI_WATERMARK: false,
        SHOW_WATERMARK_FOR_GUESTS: false,
        DEFAULT_REMOTE_DISPLAY_NAME: 'Peserta Dinas',
        TOOLBAR_ALWAYS_VISIBLE: false,
        MOBILE_APP_PROMO: false,
      },
    };

    jitsiApiInstance = new JitsiMeetExternalAPI(domain, options);

    // Event saat peserta berhasil masuk ke ruang konferensi
    jitsiApiInstance.addEventListener('videoConferenceJoined', () => {
      isJitsiLoading.value = false;
      callStatus.value = 'CONNECTED';
      connectionStatusText.value = 'Tersambung (SFU Satelit)';
      stopConnectingTimer();
      startDurationTimer();

      // Pancarkan sinyal tersambung ke backend Laravel
      axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
        action: 'connected',
      }).catch(() => {});
    });

    // Event saat ada lawan bicara masuk
    jitsiApiInstance.addEventListener('participantJoined', () => {
      connectionStatusText.value = 'Tersambung';
    });

    // Event saat tombol hangup pada kontrol Jitsi ditekan
    jitsiApiInstance.addEventListener('videoConferenceLeft', () => {
      hangUpCall();
    });

    jitsiApiInstance.addEventListener('readyToClose', () => {
      hangUpCall();
    });

    // Timeout pengaman bila iframe Jitsi sudah terbuka namun event lambat terpanggil
    setTimeout(() => {
      if (isJitsiLoading.value) {
        isJitsiLoading.value = false;
        if (callStatus.value !== 'CONNECTED') {
          callStatus.value = 'CONNECTED';
          connectionStatusText.value = 'Tersambung';
          stopConnectingTimer();
          startDurationTimer();
        }
      }
    }, 4000);

  } catch (err) {
    console.error('Gagal menginisialisasi Jitsi Meet:', err);
    isJitsiLoading.value = false;
    connectionStatusText.value = 'Koneksi IFrame terhambat';
  }
};

const partnerUserDisplay = () => {
  return `${props.partnerUser?.pangkat || ''} ${props.partnerUser?.name || 'Lawan Bicara'}`.trim();
};

// ==========================================
// 3. PENANGAN BATAS WAKTU (TIMEOUT HANDLERS)
// ==========================================
const startOutgoingTimeout = () => {
  stopOutgoingTimeout();
  outgoingSeconds.value = 0;
  outgoingTimeoutTimer = setInterval(() => {
    outgoingSeconds.value++;
    if (outgoingSeconds.value >= 40) {
      stopOutgoingTimeout();
      Swal.fire({
        icon: 'info',
        title: 'Panggilan Tidak Terjawab',
        text: 'Lawan bicara tidak menjawab panggilan dinas. Silakan kirim pesan tertulis pada obrolan.',
        confirmButtonColor: '#2563eb',
      });
      cancelOutgoingCall();
    }
  }, 1000);
};

const stopOutgoingTimeout = () => {
  if (outgoingTimeoutTimer) {
    clearInterval(outgoingTimeoutTimer);
    outgoingTimeoutTimer = null;
  }
  outgoingSeconds.value = 0;
};

const startConnectingTimer = () => {
  stopConnectingTimer();
  connectingSeconds.value = 0;
  connectingTimeoutTimer = setInterval(() => {
    connectingSeconds.value++;
    if (connectingSeconds.value >= 35) {
      stopConnectingTimer();
      if (callStatus.value !== 'CONNECTED') {
        hangUpCall();
      }
    }
  }, 1000);
};

const stopConnectingTimer = () => {
  if (connectingTimeoutTimer) {
    clearInterval(connectingTimeoutTimer);
    connectingTimeoutTimer = null;
  }
  connectingSeconds.value = 0;
};

const startDurationTimer = () => {
  if (durationTimer) clearInterval(durationTimer);
  callDuration.value = 0;
  durationTimer = setInterval(() => {
    callDuration.value++;
  }, 1000);
};

// ==========================================
// 4. LOGIKA PANGGILAN KELUAR & MASUK
// ==========================================
const startCall = async () => {
  try {
    isInitiator.value = true;
    callStatus.value = 'OUTGOING';
    startOutgoingDialRing();
    startOutgoingTimeout();

    // Inisiasi panggilan ke server SISFOPERS KC
    const res = await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/initiate`, {
      call_type: 'video',
      offer: { room: roomName.value },
    });

    if (res.data.success) {
      startSignalingPoll();
    } else {
      throw new Error(res.data.message || 'Gagal memulai panggilan dinas');
    }
  } catch (err) {
    console.error('Gagal memulai panggilan video:', err);
    cleanupMedia();
    emit('close');
  }
};

const acceptIncomingCall = async () => {
  isInitiator.value = false;
  stopIncomingCallRing();
  stopOutgoingTimeout();
  callStatus.value = 'CONNECTING';
  startConnectingTimer();

  try {
    // Beri tahu server bahwa panggilan diterima
    await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
      action: 'accept',
      sender: 'callee',
    });

    startSignalingPoll();
    emit('call-accepted');

    // Langsung buka ruang vicon Jitsi Meet
    await initJitsiRoom();
  } catch (err) {
    console.error('Peringatan saat menerima panggilan:', err);
    startSignalingPoll();
    await initJitsiRoom();
  }
};

const rejectIncomingCall = async () => {
  stopIncomingCallRing();
  stopConnectingTimer();
  stopSignalingPoll();
  cleanupMedia();
  try {
    await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/end`, {
      reason: 'rejected',
    });
  } catch (e) {}
  callStatus.value = 'IDLE';
  emit('close');
};

const cancelOutgoingCall = async () => {
  stopOutgoingDialRing();
  stopOutgoingTimeout();
  stopConnectingTimer();
  stopSignalingPoll();
  cleanupMedia();
  try {
    await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/end`, {
      reason: 'canceled',
    });
  } catch (e) {}
  callStatus.value = 'IDLE';
  emit('close');
};

const hangUpCall = async () => {
  stopIncomingCallRing();
  stopOutgoingDialRing();
  stopOutgoingTimeout();
  stopConnectingTimer();
  stopSignalingPoll();
  playCallEndedSound();

  const finalDuration = callDuration.value;
  cleanupMedia();

  try {
    await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/end`, {
      reason: 'ended',
      duration_seconds: finalDuration,
    });
  } catch (e) {}

  callStatus.value = 'IDLE';
  emit('call-ended', finalDuration);
  emit('close');
};

const handleRemoteEnded = (reason = 'ended') => {
  stopIncomingCallRing();
  stopOutgoingDialRing();
  stopOutgoingTimeout();
  stopConnectingTimer();
  stopSignalingPoll();
  playCallEndedSound();

  const finalDuration = callDuration.value;
  cleanupMedia();

  callStatus.value = 'IDLE';
  emit('call-ended', finalDuration);
  emit('close');
};

const resumeActiveCall = async (callData) => {
  if (!callData) return;
  const isCallerUser = (callData.caller?.type === props.userRole);
  isInitiator.value = isCallerUser;
  stopIncomingCallRing();
  stopOutgoingDialRing();
  stopOutgoingTimeout();

  callStatus.value = (callData.status === 'CONNECTED') ? 'CONNECTED' : 'CONNECTING';
  if (callData.status === 'CONNECTED') {
    startDurationTimer();
  } else {
    startConnectingTimer();
  }

  await initJitsiRoom();
  startSignalingPoll();
};

// ==========================================
// 5. SINKRONISASI SINYAL STATUS PANGGILAN
// ==========================================
let missedPollCount = 0;

const startSignalingPoll = () => {
  stopSignalingPoll();
  missedPollCount = 0;

  signalingTimer = setInterval(async () => {
    if (!props.threadUuid) return;

    try {
      const res = await axios.get(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`);
      const call = res.data?.call;

      if (!call) {
        missedPollCount++;
        if (missedPollCount >= 10) {
          handleRemoteEnded('Panggilan terputus');
        }
        return;
      }

      missedPollCount = 0;

      if (call.status === 'ENDED' || call.status === 'REJECTED') {
        handleRemoteEnded(call.status === 'REJECTED' ? 'Panggilan ditolak' : 'Panggilan selesai');
        return;
      }

      const isCallerUser = isCaller.value;

      // 1. Pemanggil mendeteksi bahwa penerima telah menerima panggilan
      if (isCallerUser && (call.status === 'ACCEPTED' || call.status === 'CONNECTED') && (callStatus.value === 'OUTGOING' || callStatus.value === 'CONNECTING')) {
        stopOutgoingDialRing();
        stopOutgoingTimeout();
        callStatus.value = 'CONNECTING';
        if (!jitsiApiInstance) {
          await initJitsiRoom();
        }
      }

      // 2. Jika status di server telah CONNECTED dan di klien belum
      if (call.status === 'CONNECTED' && callStatus.value !== 'CONNECTED') {
        callStatus.value = 'CONNECTED';
        connectionStatusText.value = 'Tersambung (SFU Satelit)';
        stopConnectingTimer();
        startDurationTimer();
      }

    } catch (err) {
      console.debug('Penyelarasan sinyal panggilan:', err);
    }
  }, 1000);
};

const stopSignalingPoll = () => {
  if (signalingTimer) {
    clearInterval(signalingTimer);
    signalingTimer = null;
  }
};

// ==========================================
// 6. PEMBERSIHAN SUMBER DAYA & EVENT SIKLUS
// ==========================================
const cleanupMedia = () => {
  stopSignalingPoll();
  stopOutgoingTimeout();
  stopConnectingTimer();
  isInitiator.value = false;
  isJitsiLoading.value = false;

  if (durationTimer) {
    clearInterval(durationTimer);
    durationTimer = null;
  }

  if (jitsiApiInstance) {
    try {
      jitsiApiInstance.dispose();
    } catch (e) {
      console.debug('Jitsi dispose error:', e);
    }
    jitsiApiInstance = null;
  }

  if (jitsiContainerRef.value) {
    jitsiContainerRef.value.innerHTML = '';
  }

  callDuration.value = 0;
  connectionStatusText.value = 'Menghubungkan...';
};

// Pantau properti show
watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      if (props.incomingCallData) {
        activeCaller.value = props.incomingCallData.caller;
        const status = props.incomingCallData.status;
        const isCallerUser = (props.incomingCallData.caller?.type === props.userRole);
        isInitiator.value = isCallerUser;

        if (status === 'RINGING') {
          if (isCallerUser) {
            callStatus.value = 'OUTGOING';
            startOutgoingDialRing();
            startOutgoingTimeout();
          } else {
            callStatus.value = 'INCOMING';
            startIncomingCallRing();
          }
        } else if (['ACCEPTED', 'CONNECTING', 'CONNECTED'].includes(status)) {
          resumeActiveCall(props.incomingCallData);
        }
      } else {
        startCall();
      }
    } else {
      cleanupMedia();
    }
  },
  { immediate: true }
);

watch(
  () => props.incomingCallData,
  (callData) => {
    if (!callData) return;
    activeCaller.value = callData.caller;
    const isCallerUser = (callData.caller?.type === props.userRole);

    if (callData.status === 'RINGING' && !isCallerUser && callStatus.value !== 'INCOMING' && callStatus.value !== 'CONNECTING' && callStatus.value !== 'CONNECTED') {
      isInitiator.value = false;
      callStatus.value = 'INCOMING';
      startIncomingCallRing();
    } else if (['ACCEPTED', 'CONNECTING', 'CONNECTED'].includes(callData.status) && callStatus.value === 'IDLE' && props.show) {
      resumeActiveCall(callData);
    }
  }
);

onMounted(() => {
  loadJitsiScript().catch(() => {});
});

onUnmounted(() => {
  cleanupMedia();
  stopIncomingCallRing();
  stopOutgoingDialRing();
  stopOutgoingTimeout();
  stopConnectingTimer();
});
</script>
