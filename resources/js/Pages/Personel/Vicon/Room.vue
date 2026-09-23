<template>
  <div 
    class="fixed inset-0 z-50 text-white flex flex-col select-none overflow-hidden overscroll-none touch-none bg-cover bg-center"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#090E1A' }"
  >
    <!-- Dark Glassmorphic Military Overlay -->
    <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-md z-0 pointer-events-none"></div>

    <!-- 1. TOP BAR DINAS -->
    <header class="h-16 bg-slate-900/80 border-b border-slate-800/80 px-4 sm:px-6 flex items-center justify-between shrink-0 z-20 backdrop-blur-xl relative">
      <div class="flex items-center gap-3 min-w-0">
        <!-- Logo TNI / Tri Matra / Emblem -->
        <img v-if="settings?.logo_tni" :src="settings.logo_tni" class="h-10 object-contain drop-shadow shrink-0" />
        <div v-else-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center gap-1 shrink-0">
          <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-7 object-contain" />
          <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-7 object-contain" />
          <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-7 object-contain" />
        </div>
        <div v-else class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
        </div>

        <div class="truncate">
          <div class="flex items-center gap-2">
            <span class="text-xs font-black tracking-wider text-white truncate max-w-[120px] sm:max-w-none uppercase">
              {{ settings?.app_name || 'SISFOPERS KC' }}
            </span>
            <span class="text-slate-500 hidden sm:inline">&bull;</span>
            <h1 class="text-xs sm:text-sm font-bold text-slate-200 truncate max-w-xs sm:max-w-md">
              {{ room.title }}
            </h1>
            <span class="hidden md:inline-block px-2 py-0.5 rounded-full text-[9px] font-mono font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
              {{ room.room_code }}
            </span>
          </div>
          <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium mt-0.5">
            <span class="flex items-center gap-1.5 text-emerald-400">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              {{ totalActiveCount }} Peserta Terhubung
            </span>
            <span>&bull;</span>
            <span class="font-mono text-slate-300 font-bold tracking-wider">{{ meetingDurationFormatted }}</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2.5">
        <button 
          @click="copyMeetingLink" 
          type="button" 
          class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 transition cursor-pointer"
          title="Salin Tautan Rapat"
        >
          <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
          <span>Salin Tautan</span>
        </button>

        <button 
          @click="toggleFullscreen" 
          type="button" 
          class="p-2 bg-slate-800/80 hover:bg-slate-700 rounded-xl text-slate-400 hover:text-white border border-slate-700/80 transition cursor-pointer"
          title="Layar Penuh"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
          </svg>
        </button>
      </div>
    </header>

    <!-- 2. MAIN BODY: VIDEO GRID & CHAT -->
    <div class="flex-1 relative flex overflow-hidden z-10">
      <!-- AREA VIDEO GRID -->
      <main class="flex-1 p-3 sm:p-5 overflow-y-auto flex items-center justify-center">
        <div 
          class="w-full h-full grid gap-3 sm:gap-4 items-center justify-center transition-all duration-300"
          :class="gridClass"
        >
          <!-- UBIN VIDEO LOKAL (PERSONEL) -->
          <div 
            class="relative w-full h-full min-h-[180px] sm:min-h-[240px] bg-slate-900/80 rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-700/60 shadow-2xl flex items-center justify-center group backdrop-blur-xs"
          >
            <div ref="localVideoContainerRef" class="w-full h-full object-cover"></div>

            <div 
              v-if="isCameraOff || isScreenSharing" 
              class="absolute inset-0 bg-gradient-to-b from-slate-900/90 to-slate-950 flex flex-col items-center justify-center space-y-3 z-10"
            >
              <div class="relative">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 p-1 shadow-xl shadow-blue-900/30 flex items-center justify-center">
                  <div class="w-full h-full rounded-full bg-slate-900 flex items-center justify-center text-2xl sm:text-3xl font-black text-blue-400 tracking-wider">
                    {{ getInitial(currentParticipant?.display_name || 'Personel') }}
                  </div>
                </div>
                <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-slate-900 border-2 border-slate-800 flex items-center justify-center">
                  <span class="w-2.5 h-2.5 rounded-full" :class="isMuted ? 'bg-red-500' : 'bg-emerald-400'"></span>
                </span>
              </div>
              <div class="text-center px-4">
                <p class="text-sm font-bold text-white tracking-wide truncate max-w-xs">
                  {{ currentParticipant?.display_name || 'Anda' }}
                </p>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800/80 text-slate-400 border border-slate-700">
                  {{ isScreenSharing ? 'Sedang Berbagi Layar' : 'Kamera Dinonaktifkan' }}
                </span>
              </div>
            </div>

            <!-- Lencana Identitas Lokal -->
            <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between z-10 pointer-events-none">
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/80 backdrop-blur-md border border-slate-800 text-[11px] font-bold text-white shadow-md">
                <span class="w-2 h-2 rounded-full" :class="isMuted ? 'bg-red-500' : 'bg-emerald-400 animate-pulse'"></span>
                <span class="truncate max-w-[140px] sm:max-w-[200px]">{{ currentParticipant?.display_name || 'Anda' }} (Anda)</span>
                <span class="px-1.5 py-0.2 rounded-md bg-blue-600/20 text-blue-300 text-[9px] font-bold border border-blue-500/30">PERSONEL</span>
              </div>

              <div class="flex items-center gap-1">
                <span v-if="isMuted" class="p-1 rounded-lg bg-red-600/90 text-white">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 19L5 5m14 0L5 19M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z" />
                  </svg>
                </span>
              </div>
            </div>
          </div>

          <!-- UBIN VIDEO REMOTE (HOST, PERSONEL LAIN, TAMU) -->
          <div 
            v-for="remoteUser in remoteUsers" 
            :key="remoteUser.uid"
            class="relative w-full h-full min-h-[180px] sm:min-h-[240px] bg-slate-900/80 rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-700/60 shadow-2xl flex items-center justify-center group backdrop-blur-xs"
          >
            <div :id="'remote-video-' + remoteUser.uid" class="w-full h-full object-cover"></div>

            <div 
              v-if="!remoteUser.hasVideo" 
              class="absolute inset-0 bg-gradient-to-b from-slate-900/90 to-slate-950 flex flex-col items-center justify-center space-y-3 z-10"
            >
              <div class="relative">
                <div 
                  class="w-20 h-20 sm:w-24 sm:h-24 rounded-full p-1 shadow-xl bg-gradient-to-tr flex items-center justify-center"
                  :class="getRoleColor(getParticipantByUid(remoteUser.uid)?.role).borderGradient"
                >
                  <div 
                    class="w-full h-full rounded-full bg-slate-900 flex items-center justify-center text-2xl sm:text-3xl font-black tracking-wider"
                    :class="getRoleColor(getParticipantByUid(remoteUser.uid)?.role).textColor"
                  >
                    {{ getInitial(getParticipantByUid(remoteUser.uid)?.display_name || 'P') }}
                  </div>
                </div>
                <span class="absolute bottom-0 right-0 w-5 h-5 rounded-full bg-slate-900 border-2 border-slate-800 flex items-center justify-center">
                  <span class="w-2.5 h-2.5 rounded-full" :class="remoteUser.hasAudio ? 'bg-emerald-400' : 'bg-red-500'"></span>
                </span>
              </div>
              <div class="text-center px-4">
                <p class="text-sm font-bold text-white tracking-wide truncate max-w-xs">
                  {{ getParticipantByUid(remoteUser.uid)?.display_name || ('Peserta #' + remoteUser.uid) }}
                </p>
                <span 
                  class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border"
                  :class="getRoleColor(getParticipantByUid(remoteUser.uid)?.role).badgeClass"
                >
                  {{ getParticipantByUid(remoteUser.uid)?.role || 'PESERTA' }}
                </span>
              </div>
            </div>

            <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between z-10 pointer-events-none">
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/80 backdrop-blur-md border border-slate-800 text-[11px] font-bold text-white shadow-md">
                <span class="w-2 h-2 rounded-full" :class="remoteUser.hasAudio ? 'bg-emerald-400 animate-pulse' : 'bg-red-500'"></span>
                <span class="truncate max-w-[140px] sm:max-w-[200px]">
                  {{ getParticipantByUid(remoteUser.uid)?.display_name || ('Peserta #' + remoteUser.uid) }}
                </span>
                <span 
                  class="px-1.5 py-0.2 rounded-md text-[9px] font-bold border"
                  :class="getRoleColor(getParticipantByUid(remoteUser.uid)?.role).badgeClass"
                >
                  {{ getParticipantByUid(remoteUser.uid)?.role || 'TAMU' }}
                </span>
              </div>

              <div class="flex items-center gap-1">
                <span v-if="!remoteUser.hasAudio" class="p-1 rounded-lg bg-red-600/90 text-white">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 19L5 5m14 0L5 19M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z" />
                  </svg>
                </span>
              </div>
            </div>
          </div>
        </div>
      </main>

      <!-- 3. LACI OBROLAN RUANG RAPAT -->
      <aside 
        v-if="showChatDrawer"
        class="w-80 sm:w-96 bg-slate-900/95 border-l border-slate-800 flex flex-col shrink-0 z-30 shadow-2xl transition-all duration-300 backdrop-blur-xl"
      >
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200">Obrolan Ruang Rapat</h3>
            <p class="text-[10px] text-slate-400">Pesan teks dan koordinasi</p>
          </div>
          <button @click="showChatDrawer = false" class="p-1 text-slate-400 hover:text-white cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div ref="chatContainerRef" class="flex-1 overflow-y-auto p-4 space-y-3">
          <div v-if="messagesList.length === 0" class="h-full flex items-center justify-center text-center text-xs text-slate-500 italic">
            Belum ada pesan dalam sesi rapat ini.
          </div>

          <div 
            v-for="msg in messagesList" 
            :key="msg.id"
            class="space-y-1"
          >
            <div class="flex items-center justify-between text-[10px] text-slate-400">
              <span class="font-bold text-slate-300">{{ msg.sender_name }}</span>
              <span>{{ formatTime(msg.created_at) }}</span>
            </div>
            <div class="bg-slate-800/80 border border-slate-700/60 rounded-xl px-3 py-2 text-xs text-slate-200 whitespace-pre-wrap leading-relaxed">
              {{ msg.message }}
            </div>
          </div>
        </div>

        <form @submit.prevent="submitChatMessage" class="p-3 border-t border-slate-800 bg-slate-900/90 flex gap-2">
          <input 
            v-model="inputChatMessage" 
            type="text" 
            placeholder="Tulis pesan rapat..." 
            class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"
          />
          <button 
            type="submit" 
            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Kirim
          </button>
        </form>
      </aside>
    </div>

    <!-- 4. BILAH KONTROL BAWAH -->
    <footer class="h-20 bg-slate-900/80 border-t border-slate-800/80 px-4 sm:px-8 flex items-center justify-between shrink-0 z-20 backdrop-blur-xl relative">
      <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400">
        <span class="font-mono px-2.5 py-1 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-300 font-bold tracking-wider">
          {{ room.room_code }}
        </span>
      </div>

      <div class="flex items-center gap-2 sm:gap-3 mx-auto">
        <!-- Mikrofon -->
        <button 
          @click="toggleAudio"
          type="button" 
          :class="isMuted ? 'bg-red-600 hover:bg-red-700 text-white shadow-red-600/30' : 'bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700/80'"
          class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex flex-col items-center justify-center transition active:scale-95 cursor-pointer shadow-lg"
          :title="isMuted ? 'Aktifkan Mikrofon' : 'Bisukan Mikrofon'"
        >
          <svg v-if="!isMuted" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
          </svg>
        </button>

        <!-- Kamera -->
        <button 
          @click="toggleVideo"
          type="button" 
          :class="isCameraOff ? 'bg-red-600 hover:bg-red-700 text-white shadow-red-600/30' : 'bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700/80'"
          class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex flex-col items-center justify-center transition active:scale-95 cursor-pointer shadow-lg"
          :title="isCameraOff ? 'Nyalakan Kamera' : 'Matikan Kamera'"
        >
          <svg v-if="!isCameraOff" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
          </svg>
        </button>

        <!-- Berbagi Layar -->
        <button 
          @click="toggleScreenShare"
          type="button" 
          :class="isScreenSharing ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-600/30' : 'bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700/80'"
          class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex flex-col items-center justify-center transition active:scale-95 cursor-pointer shadow-lg"
          :title="isScreenSharing ? 'Hentikan Berbagi Layar' : 'Berbagi Layar / Dokumen'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </button>

        <!-- Laci Obrolan -->
        <button 
          @click="showChatDrawer = !showChatDrawer"
          type="button" 
          :class="showChatDrawer ? 'bg-blue-600 text-white shadow-blue-600/30' : 'bg-slate-800/90 hover:bg-slate-700 text-slate-200 border border-slate-700/80'"
          class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl flex flex-col items-center justify-center transition active:scale-95 cursor-pointer shadow-lg relative"
          title="Obrolan Rapat"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        </button>

        <!-- Tinggalkan Rapat -->
        <button 
          @click="leaveMeeting"
          type="button" 
          class="px-4 sm:px-5 h-11 sm:h-12 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-xs font-black rounded-2xl shadow-lg shadow-red-600/30 transition flex items-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span class="hidden sm:inline">Tinggalkan Rapat</span>
        </button>
      </div>

      <div class="hidden sm:flex items-center gap-2 text-[11px] text-slate-400">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="font-semibold text-slate-300">Enkripsi Satelit Aktif</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import axios from 'axios';

const page = usePage();
const settings = computed(() => page.props.settings || {});

const props = defineProps({
  room: { type: Object, required: true },
  currentParticipant: { type: Object, required: true },
  agoraAppId: { type: String, default: '19daeb63b0ec46f2b02197c9fbbe81d6' },
});

const isMuted = ref(false);
const isCameraOff = ref(false);
const isScreenSharing = ref(false);
const isFullscreen = ref(false);

const showChatDrawer = ref(false);
const inputChatMessage = ref('');
const messagesList = ref(props.room.messages || []);
const participantsList = ref(props.room.participants || []);
const chatContainerRef = ref(null);

const localVideoContainerRef = ref(null);
const remoteUsers = ref([]);
let agoraClient = null;
let localAudioTrack = null;
let localVideoTrack = null;
let localScreenTrack = null;

let meetingTimer = null;
const meetingSeconds = ref(0);
let syncTimer = null;

const totalActiveCount = computed(() => 1 + remoteUsers.value.length);

const meetingDurationFormatted = computed(() => {
  const m = Math.floor(meetingSeconds.value / 60);
  const s = meetingSeconds.value % 60;
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

const gridClass = computed(() => {
  const count = totalActiveCount.value;
  if (count <= 1) return 'grid-cols-1 max-w-4xl max-h-[85vh]';
  if (count === 2) return 'grid-cols-1 sm:grid-cols-2 max-w-5xl';
  if (count <= 4) return 'grid-cols-2 max-w-5xl';
  if (count <= 6) return 'grid-cols-2 sm:grid-cols-3 max-w-6xl';
  return 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4';
});

const getInitial = (name) => {
  if (!name) return 'P';
  const parts = name.trim().split(' ');
  return parts[0].charAt(0).toUpperCase();
};

const getRoleColor = (role) => {
  if (role === 'HOST') {
    return {
      avatarClass: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
      badgeClass: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
      borderGradient: 'from-amber-500 to-yellow-300',
      textColor: 'text-amber-400',
    };
  }
  if (role === 'PERSONEL') {
    return {
      avatarClass: 'bg-blue-600/20 text-blue-300 border-blue-500/30',
      badgeClass: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
      borderGradient: 'from-blue-600 to-cyan-400',
      textColor: 'text-blue-400',
    };
  }
  return {
    avatarClass: 'bg-emerald-600/20 text-emerald-300 border-emerald-500/30',
    badgeClass: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
    borderGradient: 'from-emerald-600 to-teal-400',
    textColor: 'text-emerald-400',
  };
};

const copyMeetingLink = () => {
  const url = `${window.location.origin}/vicon/join/${props.room.room_code}`;
  navigator.clipboard.writeText(url);
  Swal.fire({
    icon: 'success',
    title: 'Tautan Berhasil Disalin',
    text: `Tautan ruang rapat (${props.room.room_code}) telah disalin ke papan klip.`,
    timer: 2000,
    showConfirmButton: false,
    customClass: { popup: 'rounded-2xl' },
  });
};

const getParticipantByUid = (uid) => {
  return participantsList.value.find(p => Number(p.agora_uid) === Number(uid));
};

const loadAgoraScript = () => {
  return new Promise((resolve, reject) => {
    if (typeof window !== 'undefined' && window.AgoraRTC) {
      resolve(window.AgoraRTC);
      return;
    }
    const existing = document.getElementById('agora-rtc-sdk');
    if (existing) {
      existing.addEventListener('load', () => resolve(window.AgoraRTC));
      existing.addEventListener('error', e => reject(e));
      return;
    }
    const s = document.createElement('script');
    s.id = 'agora-rtc-sdk';
    s.src = 'https://download.agora.io/sdk/release/AgoraRTC_N-4.20.2.js';
    s.async = true;
    s.onload = () => resolve(window.AgoraRTC);
    s.onerror = e => reject(e);
    document.head.appendChild(s);
  });
};

const initAgora = async () => {
  try {
    const AgoraRTC = await loadAgoraScript();
    agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

    const tokenRes = await axios.get(`/vicon/${props.room.uuid}/token`, {
      params: { uid: props.currentParticipant.agora_uid },
    });

    const { appId, channel, token, uid } = tokenRes.data;

    agoraClient.on('user-published', async (user, mediaType) => {
      await agoraClient.subscribe(user, mediaType);

      let targetUser = remoteUsers.value.find(u => u.uid === user.uid);
      if (!targetUser) {
        targetUser = {
          uid: user.uid,
          hasVideo: false,
          hasAudio: false,
        };
        remoteUsers.value.push(targetUser);
      }

      if (mediaType === 'video') {
        targetUser.hasVideo = true;
        await nextTick();
        user.videoTrack.play('remote-video-' + user.uid);
      }

      if (mediaType === 'audio') {
        targetUser.hasAudio = true;
        user.audioTrack.play();
      }
    });

    agoraClient.on('user-unpublished', (user, mediaType) => {
      const targetUser = remoteUsers.value.find(u => u.uid === user.uid);
      if (targetUser) {
        if (mediaType === 'video') targetUser.hasVideo = false;
        if (mediaType === 'audio') targetUser.hasAudio = false;
      }
    });

    agoraClient.on('user-left', (user) => {
      remoteUsers.value = remoteUsers.value.filter(u => u.uid !== user.uid);
    });

    await agoraClient.join(appId, channel, token, uid);

    const [audioTrack, videoTrack] = await AgoraRTC.createMicrophoneAndCameraTracks();
    localAudioTrack = audioTrack;
    localVideoTrack = videoTrack;

    await nextTick();
    if (localVideoContainerRef.value) {
      localVideoTrack.play(localVideoContainerRef.value);
    }

    await agoraClient.publish([localAudioTrack, localVideoTrack]);

    startMeetingTimer();
  } catch (err) {
    console.error('Koneksi Agora RTC Gagal:', err);
    Swal.fire({
      icon: 'error',
      title: 'Koneksi Terkendala',
      text: 'Gagal menghubungkan ke saluran Agora RTC. Pastikan izin kamera dan mikrofon telah diizinkan.',
      confirmButtonColor: '#2563EB',
    });
  }
};

const toggleAudio = async () => {
  if (!localAudioTrack) return;
  if (isMuted.value) {
    await localAudioTrack.setEnabled(true);
    isMuted.value = false;
  } else {
    await localAudioTrack.setEnabled(false);
    isMuted.value = true;
  }
};

const toggleVideo = async () => {
  if (!localVideoTrack) return;
  if (isCameraOff.value) {
    await localVideoTrack.setEnabled(true);
    isCameraOff.value = false;
  } else {
    await localVideoTrack.setEnabled(false);
    isCameraOff.value = true;
  }
};

const toggleScreenShare = async () => {
  if (!agoraClient) return;

  if (isScreenSharing.value) {
    if (localScreenTrack) {
      await agoraClient.unpublish(localScreenTrack);
      localScreenTrack.close();
      localScreenTrack = null;
    }
    if (localVideoTrack) {
      await agoraClient.publish(localVideoTrack);
      await nextTick();
      if (localVideoContainerRef.value) {
        localVideoTrack.play(localVideoContainerRef.value);
      }
    }
    isScreenSharing.value = false;
  } else {
    try {
      const AgoraRTC = await loadAgoraScript();
      localScreenTrack = await AgoraRTC.createScreenVideoTrack({}, 'disable');
      
      if (localVideoTrack) {
        await agoraClient.unpublish(localVideoTrack);
      }
      await agoraClient.publish(localScreenTrack);

      await nextTick();
      if (localVideoContainerRef.value) {
        localScreenTrack.play(localVideoContainerRef.value);
      }

      localScreenTrack.on('track-ended', () => {
        toggleScreenShare();
      });

      isScreenSharing.value = true;
    } catch (e) {
      console.warn('Screen share dibatalkan:', e);
    }
  }
};

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {});
    isFullscreen.value = true;
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen().catch(() => {});
    }
    isFullscreen.value = false;
  }
};

const startMeetingTimer = () => {
  meetingSeconds.value = 0;
  meetingTimer = setInterval(() => {
    meetingSeconds.value++;
  }, 1000);
};

const startRoomSync = () => {
  syncTimer = setInterval(async () => {
    try {
      const res = await axios.get(`/vicon/${props.room.uuid}/sync`, {
        params: { last_msg_id: messagesList.value[messagesList.value.length - 1]?.id || 0 }
      });

      if (res.data.status === 'ENDED') {
        clearInterval(syncTimer);
        cleanMedia();
        Swal.fire({
          icon: 'info',
          title: 'Rapat Telah Berakhir',
          text: 'Host telah mengakhiri sesi rapat dinas ini.',
          confirmButtonColor: '#2563EB',
        }).then(() => {
          router.visit(route('personel.vicon.index'));
        });
        return;
      }

      if (res.data.participants) {
        participantsList.value = res.data.participants;
      }

      if (res.data.messages && res.data.messages.length > 0) {
        messagesList.value.push(...res.data.messages);
        await nextTick();
        if (chatContainerRef.value) {
          chatContainerRef.value.scrollTop = chatContainerRef.value.scrollHeight;
        }
      }
    } catch (e) {
      console.debug('Sync room terkendala:', e);
    }
  }, 2000);
};

const submitChatMessage = async () => {
  const text = inputChatMessage.value.trim();
  if (!text) return;

  inputChatMessage.value = '';

  const tempMsg = {
    id: 'temp_' + Date.now(),
    sender_name: props.currentParticipant.display_name,
    message: text,
    created_at: new Date().toISOString(),
  };
  messagesList.value.push(tempMsg);
  await nextTick();
  if (chatContainerRef.value) {
    chatContainerRef.value.scrollTop = chatContainerRef.value.scrollHeight;
  }

  try {
    const res = await axios.post(`/vicon/${props.room.uuid}/chat`, {
      message: text,
      sender_name: props.currentParticipant.display_name,
    });
    if (res.data?.message) {
      const idx = messagesList.value.findIndex(m => m.id === tempMsg.id);
      if (idx !== -1) {
        messagesList.value[idx] = res.data.message;
      }
    }
  } catch (err) {
    console.error('Kirim pesan chat gagal:', err);
  }
};

const leaveMeeting = () => {
  Swal.fire({
    title: 'Tinggalkan Rapat?',
    text: 'Anda akan keluar dari sesi rapat konferensi dinas ini.',
    showCancelButton: true,
    confirmButtonText: 'Tinggalkan',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#DC2626',
    customClass: { popup: 'rounded-2xl' },
  }).then((res) => {
    if (res.isConfirmed) {
      cleanMedia();
      router.visit(route('personel.vicon.index'));
    }
  });
};

const cleanMedia = () => {
  if (localAudioTrack) {
    localAudioTrack.close();
    localAudioTrack = null;
  }
  if (localVideoTrack) {
    localVideoTrack.close();
    localVideoTrack = null;
  }
  if (localScreenTrack) {
    localScreenTrack.close();
    localScreenTrack = null;
  }
  if (agoraClient) {
    agoraClient.leave();
    agoraClient = null;
  }
  if (meetingTimer) clearInterval(meetingTimer);
  if (syncTimer) clearInterval(syncTimer);
};

const formatTime = (iso) => {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
  initAgora();
  startRoomSync();
});

onUnmounted(() => {
  cleanMedia();
});
</script>
