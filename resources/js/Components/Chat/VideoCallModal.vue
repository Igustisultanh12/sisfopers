<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-2 sm:p-4 select-none">
    
    <!-- 1. KONDISI A: PANGGILAN KELUAR (OUTGOING CALLING) -->
    <div 
      v-if="callStatus === 'OUTGOING'" 
      class="w-full max-w-md bg-slate-900 border border-slate-700/80 rounded-3xl p-6 sm:p-8 text-center text-white shadow-2xl space-y-6 relative overflow-hidden"
    >
      <!-- Background Ambient Glow -->
      <div class="absolute -top-24 -left-24 w-48 h-48 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>

      <div class="space-y-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
          <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
          Panggilan Video Dinas
        </span>
        <h3 class="text-base font-bold text-slate-200">Menghubungkan Sinyal...</h3>
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
      class="w-full max-w-md bg-slate-900 border border-slate-700/80 rounded-3xl p-6 sm:p-8 text-center text-white shadow-2xl space-y-6 relative overflow-hidden"
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

    <!-- 3. KONDISI C: RUANG VICON AKTIF (CONNECTED / CONNECTING ROOM) -->
    <div 
      v-else 
      :class="isFullscreen ? 'fixed inset-0 z-50 rounded-none' : 'w-full max-w-5xl h-[90vh] max-h-[720px] rounded-3xl'"
      class="bg-slate-900 border border-slate-800 shadow-2xl flex flex-col overflow-hidden relative"
    >
      <!-- Bilah Header Atas Ruang Vicon -->
      <div class="h-14 px-4 sm:px-6 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80 flex items-center justify-between shrink-0 z-20">
        <div class="flex items-center gap-3">
          <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
          <div>
            <h4 class="text-xs sm:text-sm font-black text-white flex items-center gap-2">
              <span>{{ partnerUser?.pangkat }} {{ partnerUser?.name }}</span>
              <span class="hidden sm:inline text-[10px] font-bold text-slate-400 bg-slate-800 px-2 py-0.5 rounded-md">
                {{ partnerUser?.matra ? `Matra ${partnerUser.matra}` : 'Dinas' }}
              </span>
            </h4>
            <div class="flex items-center gap-2 text-[10px] text-slate-400">
              <span>Durasi: <strong class="text-slate-200 font-mono">{{ formatDuration(callDuration) }}</strong></span>
              <span>&bull;</span>
              <span class="text-emerald-400 font-semibold">{{ connectionStatusText }}</span>
            </div>
          </div>
        </div>

        <!-- Lencana Keamanan Militer -->
        <div class="flex items-center gap-2">
          <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-950/60 border border-emerald-800/60 text-[10px] font-bold text-emerald-400">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>Terenkripsi Ujung-ke-Ujung (DTLS-SRTP)</span>
          </div>

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
        </div>
      </div>

      <!-- Area Layar Video Utama (Remote Video & Overlay) -->
      <div class="flex-1 min-h-0 bg-slate-950 relative flex items-center justify-center overflow-hidden">
        
        <!-- Video Peserta Lawan (Remote Video) -->
        <video 
          ref="remoteVideoRef" 
          autoplay 
          playsinline 
          class="w-full h-full object-contain"
        ></video>

        <!-- Placeholder jika Remote Video Belum Terhubung atau Kamera Lawan Nonaktif -->
        <div 
          v-if="callStatus === 'CONNECTING' || isRemoteVideoOff" 
          class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/80 text-center space-y-3 p-6 z-10"
        >
          <img 
            :src="partnerUser?.photo ? `/documents/private-stream?path=${encodeURIComponent(partnerUser.photo)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(partnerUser?.name || 'P')}&background=1e293b&color=94a3b8`" 
            class="w-24 h-24 object-cover rounded-2xl border border-slate-700 shadow-xl" 
          />
          <div>
            <h4 class="text-base font-bold text-white">{{ partnerUser?.pangkat }} {{ partnerUser?.name }}</h4>
            <p class="text-xs text-slate-400 mt-0.5">
              {{ callStatus === 'CONNECTING' ? 'Mempersiapkan jalur komunikasi terenkripsi...' : 'Kamera lawan bicara sedang dinonaktifkan' }}
            </p>
          </div>
          <div v-if="callStatus === 'CONNECTING'" class="flex flex-col items-center gap-2 text-xs text-blue-400">
            <div class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
              <span>{{ connectionStatusText }}</span>
            </div>
            <div v-if="connectingSeconds > 7" class="pt-1">
              <button 
                @click="retryIceNegotiation" 
                type="button" 
                class="px-3 py-1.5 bg-blue-600/80 hover:bg-blue-600 text-white text-[11px] font-bold rounded-xl border border-blue-400/30 transition flex items-center gap-1.5 cursor-pointer shadow active:scale-95"
              >
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Segarkan Penyelarasan Sinyal</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Video Lokal Pengguna (Miniatur PiP di Sudut Kanan Bawah) -->
        <div 
          class="absolute bottom-4 right-4 w-36 sm:w-48 aspect-video bg-slate-900 border-2 border-slate-700/90 rounded-2xl shadow-2xl overflow-hidden z-20 group transition-all"
        >
          <video 
            ref="localVideoRef" 
            autoplay 
            playsinline 
            muted 
            :class="isMirrorCamera ? '-scale-x-100' : ''"
            class="w-full h-full object-cover"
          ></video>

          <!-- Placeholder saat Kamera Lokal Dimatikan -->
          <div 
            v-if="isCameraOff" 
            class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center text-slate-400"
          >
            <svg class="w-6 h-6 text-slate-500 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            <span class="text-[10px] font-bold">Kamera Mati</span>
          </div>

          <!-- Lencana Nama & Status Lokal -->
          <div class="absolute bottom-1.5 left-2 right-2 flex items-center justify-between pointer-events-none text-[9px] font-bold text-white/90">
            <span class="truncate bg-slate-950/70 px-1.5 py-0.5 rounded">Anda</span>
            <span v-if="isMuted" class="bg-red-600/90 px-1.5 py-0.5 rounded">Mute</span>
          </div>
        </div>

        <!-- Canvas Tersembunyi untuk Pemrosesan Virtual Background & Blur -->
        <canvas ref="bgCanvasRef" class="hidden" width="640" height="480"></canvas>
      </div>

      <!-- Bilah Menu Pilihan Virtual Background (Bila Dibuka) -->
      <div 
        v-if="showBgMenu" 
        class="absolute bottom-20 left-1/2 -translate-x-1/2 w-full max-w-md bg-slate-950/95 border border-slate-800 rounded-2xl p-4 shadow-2xl z-30 space-y-3 backdrop-blur-xl"
      >
        <div class="flex items-center justify-between pb-2 border-b border-slate-800">
          <h5 class="text-xs font-bold text-white flex items-center gap-1.5">
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Pilihan Latar Belakang (Virtual Background)</span>
          </h5>
          <button @click="showBgMenu = false" type="button" class="text-slate-400 hover:text-white text-xs cursor-pointer">&times;</button>
        </div>

        <div class="grid grid-cols-3 gap-2 text-center text-[10px] font-bold">
          <!-- Tanpa Efek -->
          <button 
            @click="applyBackground('none')"
            :class="currentBackground === 'none' ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-slate-800 bg-slate-900 text-slate-300 hover:bg-slate-800'"
            class="p-2.5 rounded-xl border flex flex-col items-center gap-1.5 transition cursor-pointer"
          >
            <div class="w-7 h-7 rounded-lg bg-slate-800 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
            </div>
            <span>Normal</span>
          </button>

          <!-- Blur Ringan -->
          <button 
            @click="applyBackground('blur-light')"
            :class="currentBackground === 'blur-light' ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-slate-800 bg-slate-900 text-slate-300 hover:bg-slate-800'"
            class="p-2.5 rounded-xl border flex flex-col items-center gap-1.5 transition cursor-pointer"
          >
            <div class="w-7 h-7 rounded-lg bg-slate-800 flex items-center justify-center backdrop-blur-xs">
              <span class="text-xs">BL</span>
            </div>
            <span>Blur Ringan</span>
          </button>

          <!-- Blur Pekat -->
          <button 
            @click="applyBackground('blur-heavy')"
            :class="currentBackground === 'blur-heavy' ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-slate-800 bg-slate-900 text-slate-300 hover:bg-slate-800'"
            class="p-2.5 rounded-xl border flex flex-col items-center gap-1.5 transition cursor-pointer"
          >
            <div class="w-7 h-7 rounded-lg bg-slate-800 flex items-center justify-center backdrop-blur-md">
              <span class="text-xs">BP</span>
            </div>
            <span>Blur Pekat</span>
          </button>

          <!-- Latar Markas Komcad -->
          <button 
            @click="applyBackground('command')"
            :class="currentBackground === 'command' ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-slate-800 bg-slate-900 text-slate-300 hover:bg-slate-800'"
            class="p-2.5 rounded-xl border flex flex-col items-center gap-1.5 transition cursor-pointer"
          >
            <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-slate-950 to-blue-900 flex items-center justify-center">
              <span class="text-[9px] text-blue-300">KC</span>
            </div>
            <span>Markas Dinas</span>
          </button>

          <!-- Latar Ruang Sidang Mabes -->
          <button 
            @click="applyBackground('office')"
            :class="currentBackground === 'office' ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-slate-800 bg-slate-900 text-slate-300 hover:bg-slate-800'"
            class="p-2.5 rounded-xl border flex flex-col items-center gap-1.5 transition cursor-pointer"
          >
            <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-slate-900 to-emerald-950 flex items-center justify-center">
              <span class="text-[9px] text-emerald-300">MBS</span>
            </div>
            <span>Ruang Rapat</span>
          </button>
        </div>
      </div>

      <!-- Bilah Kontrol Bawah (Toolbar Melayang Gaya Zoom / Meet / WhatsApp) -->
      <div class="h-20 px-4 bg-slate-950/90 backdrop-blur-md border-t border-slate-800 flex items-center justify-center gap-2 sm:gap-4 shrink-0 z-20">
        
        <!-- 1. Tombol Mikrofon (Mute/Unmute) -->
        <button 
          @click="toggleMute" 
          type="button" 
          :class="isMuted ? 'bg-red-600 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-200'"
          class="p-3 sm:p-3.5 rounded-2xl shadow-md transition cursor-pointer flex flex-col items-center gap-1 active:scale-95"
          :title="isMuted ? 'Nyalakan Mikrofon' : 'Matikan Mikrofon'"
        >
          <svg v-if="!isMuted" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
          </svg>
        </button>

        <!-- 2. Tombol Kamera (On/Off) -->
        <button 
          @click="toggleCamera" 
          type="button" 
          :class="isCameraOff ? 'bg-red-600 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-200'"
          class="p-3 sm:p-3.5 rounded-2xl shadow-md transition cursor-pointer flex flex-col items-center gap-1 active:scale-95"
          :title="isCameraOff ? 'Nyalakan Kamera' : 'Matikan Kamera'"
        >
          <svg v-if="!isCameraOff" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
          </svg>
        </button>

        <!-- 3. Tombol Ubah Background (Virtual Background) -->
        <button 
          @click="showBgMenu = !showBgMenu" 
          type="button" 
          :class="showBgMenu ? 'bg-blue-600 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-200'"
          class="p-3 sm:p-3.5 rounded-2xl shadow-md transition cursor-pointer flex flex-col items-center gap-1 active:scale-95"
          title="Ubah Latar Belakang (Virtual Background)"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </button>

        <!-- 4. Tombol Bagi Layar (Screen Share) -->
        <button 
          @click="toggleScreenShare" 
          type="button" 
          :class="isScreenSharing ? 'bg-emerald-600 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-200'"
          class="p-3 sm:p-3.5 rounded-2xl shadow-md transition cursor-pointer flex flex-col items-center gap-1 active:scale-95"
          :title="isScreenSharing ? 'Berhenti Berbagi Layar' : 'Bagi Layar Paparan (Screen Share)'"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </button>

        <!-- 5. Tombol Flip / Ganti Kamera -->
        <button 
          @click="flipCamera" 
          type="button" 
          class="p-3 sm:p-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 shadow-md transition cursor-pointer flex flex-col items-center gap-1 active:scale-95"
          title="Ganti Sudut Kamera"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>

        <!-- 6. Tombol Akhiri Panggilan (Hang Up) -->
        <button 
          @click="hangUpCall" 
          type="button" 
          class="py-3 px-5 sm:px-6 bg-red-600 hover:bg-red-700 active:scale-95 text-white font-black text-xs sm:text-sm rounded-2xl shadow-xl transition flex items-center gap-2 cursor-pointer ml-1 sm:ml-2"
          title="Akhiri Panggilan Dinas"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.684A1 1 0 008.279 3H5z" />
          </svg>
          <span class="hidden sm:inline">Akhiri</span>
        </button>
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

// State Penanda Waktu Timeout
const outgoingSeconds = ref(0);
const connectingSeconds = ref(0);
let outgoingTimeoutTimer = null;
let connectingTimeoutTimer = null;

// State Kontrol Media
const isMuted = ref(false);
const isCameraOff = ref(false);
const isScreenSharing = ref(false);
const isMirrorCamera = ref(true);
const isFullscreen = ref(false);
const isRemoteVideoOff = ref(false);

// State Virtual Background
const showBgMenu = ref(false);
const currentBackground = ref('none'); // 'none' | 'blur-light' | 'blur-heavy' | 'command' | 'office'

// Elemen DOM Referensi
const localVideoRef = ref(null);
const remoteVideoRef = ref(null);
const bgCanvasRef = ref(null);

// Stream & WebRTC Variables
let localStream = null;
let remoteStream = null;
let rawVideoTrack = null;
let rawAudioTrack = null;
let screenStream = null;
let peerConnection = null;
let signalingTimer = null;
let durationTimer = null;
let canvasAnimId = null;

// Antrean & Deduplikasi Kandidat ICE
const addedCandidateKeys = new Set();
const pendingCandidates = [];

// Konfigurasi STUN & TURN Resmi Komprehensif (Google, Cloudflare, dan Public OpenRelay Metered)
const rtcConfig = {
  iceServers: [
    { urls: 'stun:stun.l.google.com:19302' },
    { urls: 'stun:stun1.l.google.com:19302' },
    { urls: 'stun:stun2.l.google.com:19302' },
    { urls: 'stun:stun.cloudflare.com:3478' },
    { urls: 'stun:relay.metered.ca:80' },
    {
      urls: 'turn:relay.metered.ca:80',
      username: 'openrelayproject',
      credential: 'openrelayproject',
    },
    {
      urls: 'turn:relay.metered.ca:443',
      username: 'openrelayproject',
      credential: 'openrelayproject',
    },
    {
      urls: 'turn:relay.metered.ca:443?transport=tcp',
      username: 'openrelayproject',
      credential: 'openrelayproject',
    },
  ],
  iceCandidatePoolSize: 10,
};

// Penentuan peran pemanggil secara absolut
const isCaller = computed(() => {
  if (isInitiator.value) return true;
  if (props.incomingCallData) return false;
  return props.userRole === 'OPERATOR';
});

const formatDuration = (totalSeconds) => {
  const m = Math.floor(totalSeconds / 60);
  const s = totalSeconds % 60;
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
};

// ==========================================
// 1. INISIASI MEDIA & WEBRTC PEER CONNECTION
// ==========================================
const initLocalMedia = async () => {
  try {
    localStream = await navigator.mediaDevices.getUserMedia({
      video: {
        width: { ideal: 1280 },
        height: { ideal: 720 },
        facingMode: 'user',
      },
      audio: {
        echoCancellation: true,
        noiseSuppression: true,
        autoGainControl: true,
      },
    });

    rawVideoTrack = localStream.getVideoTracks()[0];
    rawAudioTrack = localStream.getAudioTracks()[0];

    await nextTick();
    if (localVideoRef.value) {
      localVideoRef.value.srcObject = localStream;
    }
  } catch (err) {
    console.error('Akses kamera atau mikrofon ditolak:', err);
    throw err;
  }
};

const waitForIceGathering = (pc, maxWaitMs = 600) => {
  return new Promise((resolve) => {
    if (pc.iceGatheringState === 'complete') {
      resolve();
      return;
    }
    let timer = null;
    const checkState = () => {
      if (pc.iceGatheringState === 'complete') {
        pc.removeEventListener('icegatheringstatechange', checkState);
        if (timer) clearTimeout(timer);
        resolve();
      }
    };
    pc.addEventListener('icegatheringstatechange', checkState);
    timer = setTimeout(() => {
      pc.removeEventListener('icegatheringstatechange', checkState);
      resolve();
    }, maxWaitMs);
  });
};

const addCandidateSafely = async (cand) => {
  if (!cand || !cand.candidate) return;
  const candKey = `${cand.sdpMid || ''}_${cand.sdpMLineIndex || 0}_${cand.candidate}`;
  if (addedCandidateKeys.has(candKey)) return;

  if (!peerConnection || !peerConnection.currentRemoteDescription) {
    pendingCandidates.push(cand);
    return;
  }

  try {
    await peerConnection.addIceCandidate(new RTCIceCandidate(cand));
    addedCandidateKeys.add(candKey);
  } catch (err) {
    console.debug('Kandidat ICE dilewati atau telah usang:', err);
  }
};

const flushPendingCandidates = async () => {
  if (!peerConnection || !peerConnection.currentRemoteDescription) return;
  while (pendingCandidates.length > 0) {
    const cand = pendingCandidates.shift();
    const candKey = `${cand.sdpMid || ''}_${cand.sdpMLineIndex || 0}_${cand.candidate}`;
    if (!addedCandidateKeys.has(candKey)) {
      try {
        await peerConnection.addIceCandidate(new RTCIceCandidate(cand));
        addedCandidateKeys.add(candKey);
      } catch (err) {
        console.debug('Gagal menerapkan kandidat tertunda:', err);
      }
    }
  }
};

const createPeerConnection = () => {
  if (peerConnection) {
    peerConnection.close();
    peerConnection = null;
  }

  peerConnection = new RTCPeerConnection(rtcConfig);

  // Pasang media lokal ke Peer Connection
  if (localStream) {
    localStream.getTracks().forEach((track) => {
      peerConnection.addTrack(track, localStream);
    });
  }

  // Tangkap media lawan bicara dan pasang ke objek stream
  peerConnection.ontrack = (event) => {
    if (!remoteStream) {
      remoteStream = new MediaStream();
    }
    if (event.streams && event.streams[0]) {
      remoteStream = event.streams[0];
    } else if (event.track) {
      remoteStream.addTrack(event.track);
    }

    if (remoteVideoRef.value) {
      remoteVideoRef.value.srcObject = remoteStream;
      remoteVideoRef.value.play().catch(() => {});
    }
    isRemoteVideoOff.value = false;
  };

  // Tangkap kandidat ICE lokal dan pancarkan ke server
  peerConnection.onicecandidate = (event) => {
    if (event.candidate && props.threadUuid) {
      const sender = isCaller.value ? 'caller' : 'callee';
      axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
        action: 'candidate',
        data: event.candidate.toJSON ? event.candidate.toJSON() : event.candidate,
        sender: sender,
      }).catch(() => {});
    }
  };

  // Penanganan saat jalur P2P berhasil terhubung
  const handleConnected = () => {
    if (callStatus.value !== 'CONNECTED') {
      callStatus.value = 'CONNECTED';
      connectionStatusText.value = 'Tersambung (P2P)';
      stopOutgoingDialRing();
      stopIncomingCallRing();
      stopOutgoingTimeout();
      stopConnectingTimer();
      startDurationTimer();

      nextTick(() => {
        if (remoteVideoRef.value && remoteStream) {
          remoteVideoRef.value.srcObject = remoteStream;
          remoteVideoRef.value.play().catch(() => {});
        }
        if (localVideoRef.value && localStream) {
          localVideoRef.value.srcObject = localStream;
        }
      });

      axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
        action: 'connected',
      }).catch(() => {});
    }
  };

  peerConnection.onconnectionstatechange = () => {
    if (!peerConnection) return;
    const state = peerConnection.connectionState;
    console.debug('WebRTC connectionState:', state);
    if (state === 'connected') {
      handleConnected();
    } else if (state === 'failed') {
      console.warn('WebRTC connection failed, menyegarkan ICE...');
      retryIceNegotiation();
    } else if (state === 'disconnected') {
      connectionStatusText.value = 'Terputus';
    }
  };

  peerConnection.oniceconnectionstatechange = () => {
    if (!peerConnection) return;
    const iceState = peerConnection.iceConnectionState;
    console.debug('WebRTC iceConnectionState:', iceState);
    if (iceState === 'connected' || iceState === 'completed') {
      handleConnected();
    } else if (iceState === 'failed') {
      console.warn('ICE connection failed, menyegarkan ICE...');
      retryIceNegotiation();
    }
  };
};

// Penyegaran jalur ICE bila terjadi hambatan NAT / Firewall
const retryIceNegotiation = async () => {
  if (!peerConnection) return;
  try {
    connectionStatusText.value = 'Menyelaraskan rute cadangan...';
    if (peerConnection.restartIce) {
      peerConnection.restartIce();
    }
    if (isCaller.value) {
      const offer = await peerConnection.createOffer({ iceRestart: true });
      await peerConnection.setLocalDescription(offer);
      await waitForIceGathering(peerConnection, 500);
      await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
        action: 'offer',
        data: peerConnection.localDescription,
        sender: 'caller',
      });
    }
  } catch (err) {
    console.debug('Gagal restart ICE:', err);
  }
};

// ==========================================
// 2. PENANGAN BATAS WAKTU (TIMEOUT HANDLERS)
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
    if (connectingSeconds.value === 10) {
      retryIceNegotiation();
    } else if (connectingSeconds.value >= 25) {
      stopConnectingTimer();
      Swal.fire({
        icon: 'warning',
        title: 'Koneksi P2P Terkendala',
        text: 'Koneksi terhambat oleh pembatas jaringan lawan bicara. Silakan gunakan saluran pesan tertulis.',
        confirmButtonColor: '#2563eb',
      });
      hangUpCall();
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

// ==========================================
// 3. LOGIKA PANGGILAN KELUAR & MASUK
// ==========================================
const startCall = async () => {
  try {
    isInitiator.value = true;
    callStatus.value = 'OUTGOING';
    startOutgoingDialRing();
    startOutgoingTimeout();

    await initLocalMedia();
    createPeerConnection();

    // Buat Offer seketika dan kumpulkan kandidat lokal dalam SDP
    const offer = await peerConnection.createOffer();
    await peerConnection.setLocalDescription(offer);
    await waitForIceGathering(peerConnection, 600);

    // Inisiasi panggilan ke server dengan data penawaran awal
    const res = await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/initiate`, {
      call_type: 'video',
      offer: peerConnection.localDescription,
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
    await initLocalMedia();
    createPeerConnection();

    // Pastikan status penerimaan dikirim ke server
    await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
      action: 'accept',
      sender: 'callee',
    });

    // Ambil sinyal panggilan terbaru untuk memperoleh data penawaran
    const resSignal = await axios.get(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`);
    const call = resSignal.data?.call;

    if (call?.offer && !peerConnection.currentRemoteDescription) {
      await peerConnection.setRemoteDescription(new RTCSessionDescription(call.offer));
      await flushPendingCandidates();

      const answer = await peerConnection.createAnswer();
      await peerConnection.setLocalDescription(answer);
      await waitForIceGathering(peerConnection, 500);

      await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
        action: 'answer',
        data: peerConnection.localDescription,
        sender: 'callee',
      });
    }

    startSignalingPoll();
    emit('call-accepted');
  } catch (err) {
    console.error('Gagal menerima panggilan:', err);
    hangUpCall();
  }
};

const rejectIncomingCall = async () => {
  stopIncomingCallRing();
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

// ==========================================
// 4. SINKRONISASI SINYAL WEBRTC TERARAH
// ==========================================
const startSignalingPoll = () => {
  stopSignalingPoll();
  signalingTimer = setInterval(async () => {
    if (!props.threadUuid) return;

    try {
      const res = await axios.get(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`);
      const call = res.data?.call;

      if (!call || call.status === 'ENDED' || call.status === 'REJECTED') {
        stopSignalingPoll();
        hangUpCall();
        return;
      }

      const isCallerUser = isCaller.value;

      // 1. Pemanggil mendeteksi bahwa penerima telah menerima sambungan
      if (isCallerUser && call.status === 'ACCEPTED' && callStatus.value === 'OUTGOING') {
        callStatus.value = 'CONNECTING';
        stopOutgoingDialRing();
        stopOutgoingTimeout();
        startConnectingTimer();
      }

      // 2. Pemanggil mengirimkan Offer jika belum sempat terkirim
      if (isCallerUser && call.status === 'ACCEPTED' && !call.offer && peerConnection) {
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        await waitForIceGathering(peerConnection, 500);

        await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
          action: 'offer',
          data: peerConnection.localDescription,
          sender: 'caller',
        });
      }

      // 3. Penerima menerima Offer dari Pemanggil dan membuat Answer
      if (!isCallerUser && call.offer && !peerConnection?.currentRemoteDescription && peerConnection) {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(call.offer));
        await flushPendingCandidates();

        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);
        await waitForIceGathering(peerConnection, 500);

        await axios.post(`/${props.urlPrefix}/live-chat/${props.threadUuid}/call/signal`, {
          action: 'answer',
          data: peerConnection.localDescription,
          sender: 'callee',
        });
      }

      // 4. Pemanggil menerima Answer dari Penerima
      if (isCallerUser && call.answer && peerConnection && !peerConnection.currentRemoteDescription) {
        await peerConnection.setRemoteDescription(new RTCSessionDescription(call.answer));
        await flushPendingCandidates();
      }

      // 5. Pertukaran Kandidat ICE tambahan secara aman tanpa redundansi
      const incomingCandidates = isCallerUser ? (call.candidates_callee || []) : (call.candidates_caller || []);
      for (const cand of incomingCandidates) {
        await addCandidateSafely(cand);
      }

    } catch (err) {
      console.debug('Pertukaran sinyal dinas terkendala:', err);
    }
  }, 1000);
};

const stopSignalingPoll = () => {
  if (signalingTimer) {
    clearInterval(signalingTimer);
    signalingTimer = null;
  }
};

const startDurationTimer = () => {
  if (durationTimer) clearInterval(durationTimer);
  callDuration.value = 0;
  durationTimer = setInterval(() => {
    callDuration.value++;
  }, 1000);
};

// ==========================================
// 4. KONTROL MIKROFON, KAMERA & BAGI LAYAR
// ==========================================
const toggleMute = () => {
  if (rawAudioTrack) {
    rawAudioTrack.enabled = !rawAudioTrack.enabled;
    isMuted.value = !rawAudioTrack.enabled;
  }
};

const toggleCamera = () => {
  if (rawVideoTrack) {
    rawVideoTrack.enabled = !rawVideoTrack.enabled;
    isCameraOff.value = !rawVideoTrack.enabled;
  }
};

const flipCamera = () => {
  isMirrorCamera.value = !isMirrorCamera.value;
};

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value;
};

const toggleScreenShare = async () => {
  if (isScreenSharing.value) {
    stopScreenShare();
  } else {
    try {
      screenStream = await navigator.mediaDevices.getDisplayMedia({
        video: { cursor: 'always' },
        audio: false,
      });

      const screenTrack = screenStream.getVideoTracks()[0];
      const sender = peerConnection?.getSenders()?.find((s) => s.track && s.track.kind === 'video');

      if (sender) {
        await sender.replaceTrack(screenTrack);
      }

      if (localVideoRef.value) {
        localVideoRef.value.srcObject = screenStream;
      }

      screenTrack.onended = () => {
        stopScreenShare();
      };

      isScreenSharing.value = true;
    } catch (err) {
      console.debug('Batal berbagi layar paparan:', err);
    }
  }
};

const stopScreenShare = async () => {
  if (screenStream) {
    screenStream.getTracks().forEach((t) => t.stop());
    screenStream = null;
  }

  const sender = peerConnection?.getSenders()?.find((s) => s.track && s.track.kind === 'video');
  if (sender && rawVideoTrack) {
    await sender.replaceTrack(rawVideoTrack);
  }

  if (localVideoRef.value && localStream) {
    localVideoRef.value.srcObject = localStream;
  }

  isScreenSharing.value = false;
};

// ==========================================
// 5. ENGINE VIRTUAL BACKGROUND & BLUR (CANVAS)
// ==========================================
const applyBackground = async (type) => {
  currentBackground.value = type;
  showBgMenu.value = false;

  const sender = peerConnection?.getSenders()?.find((s) => s.track && s.track.kind === 'video');

  if (type === 'none') {
    if (canvasAnimId) {
      cancelAnimationFrame(canvasAnimId);
      canvasAnimId = null;
    }
    if (sender && rawVideoTrack) {
      await sender.replaceTrack(rawVideoTrack);
    }
    if (localVideoRef.value && localStream) {
      localVideoRef.value.srcObject = localStream;
    }
    return;
  }

  // Mulai render loop Canvas untuk efek blur atau latar dinas
  startCanvasEffect(type, sender);
};

const startCanvasEffect = (type, sender) => {
  const canvas = bgCanvasRef.value;
  if (!canvas || !localVideoRef.value) return;

  const ctx = canvas.getContext('2d');

  const renderLoop = () => {
    if (currentBackground.value === 'none') return;

    ctx.save();
    if (isMirrorCamera.value) {
      ctx.translate(canvas.width, 0);
      ctx.scale(-1, 1);
    }

    if (type === 'blur-light') {
      ctx.filter = 'blur(6px)';
      ctx.drawImage(localVideoRef.value, 0, 0, canvas.width, canvas.height);
      ctx.filter = 'none';
      // Gambar pusat video tanpa blur
      ctx.drawImage(localVideoRef.value, canvas.width * 0.15, canvas.height * 0.1, canvas.width * 0.7, canvas.height * 0.8, canvas.width * 0.15, canvas.height * 0.1, canvas.width * 0.7, canvas.height * 0.8);
    } else if (type === 'blur-heavy') {
      ctx.filter = 'blur(14px)';
      ctx.drawImage(localVideoRef.value, 0, 0, canvas.width, canvas.height);
      ctx.filter = 'none';
      ctx.drawImage(localVideoRef.value, canvas.width * 0.2, canvas.height * 0.15, canvas.width * 0.6, canvas.height * 0.7, canvas.width * 0.2, canvas.height * 0.15, canvas.width * 0.6, canvas.height * 0.7);
    } else if (type === 'command') {
      // Background Latar Komando Dinas Militer
      const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
      grad.addColorStop(0, '#020617');
      grad.addColorStop(1, '#0f172a');
      ctx.fillStyle = grad;
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      // Gambar grid taktis dinas
      ctx.strokeStyle = 'rgba(59, 130, 246, 0.15)';
      ctx.lineWidth = 1;
      for (let x = 0; x < canvas.width; x += 40) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
      }

      ctx.drawImage(localVideoRef.value, canvas.width * 0.15, canvas.height * 0.1, canvas.width * 0.7, canvas.height * 0.85);
    } else if (type === 'office') {
      // Background Ruang Sidang Mabes
      const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
      grad.addColorStop(0, '#042f2e');
      grad.addColorStop(1, '#022c22');
      ctx.fillStyle = grad;
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      ctx.drawImage(localVideoRef.value, canvas.width * 0.15, canvas.height * 0.1, canvas.width * 0.7, canvas.height * 0.85);
    }

    ctx.restore();
    canvasAnimId = requestAnimationFrame(renderLoop);
  };

  renderLoop();

  try {
    const canvasStream = canvas.captureStream(30);
    const canvasTrack = canvasStream.getVideoTracks()[0];
    if (sender && canvasTrack) {
      sender.replaceTrack(canvasTrack);
    }
  } catch (e) {
    console.debug('Canvas capture stream tidak didukung:', e);
  }
};

// ==========================================
// 6. PEMBERSIHAN SUMBER DAYA & EVENT LIFECYCLE
// ==========================================
const cleanupMedia = () => {
  stopSignalingPoll();
  stopOutgoingTimeout();
  stopConnectingTimer();
  addedCandidateKeys.clear();
  pendingCandidates.length = 0;
  isInitiator.value = false;

  if (durationTimer) {
    clearInterval(durationTimer);
    durationTimer = null;
  }
  if (canvasAnimId) {
    cancelAnimationFrame(canvasAnimId);
    canvasAnimId = null;
  }
  if (localStream) {
    localStream.getTracks().forEach((track) => track.stop());
    localStream = null;
  }
  if (remoteStream) {
    remoteStream.getTracks().forEach((track) => track.stop());
    remoteStream = null;
  }
  if (screenStream) {
    screenStream.getTracks().forEach((track) => track.stop());
    screenStream = null;
  }
  if (peerConnection) {
    peerConnection.close();
    peerConnection = null;
  }
  if (localVideoRef.value) {
    localVideoRef.value.srcObject = null;
  }
  if (remoteVideoRef.value) {
    remoteVideoRef.value.srcObject = null;
  }
  callDuration.value = 0;
  isScreenSharing.value = false;
  isMuted.value = false;
  isCameraOff.value = false;
  connectionStatusText.value = 'Menghubungkan...';
};

// Pantau perubahan status panggilan untuk memastikan elemen video menerima aliran media
watch(
  () => callStatus.value,
  async (newStatus) => {
    if (newStatus === 'CONNECTING' || newStatus === 'CONNECTED') {
      await nextTick();
      if (localVideoRef.value && localStream) {
        localVideoRef.value.srcObject = localStream;
      }
      if (remoteVideoRef.value && remoteStream) {
        remoteVideoRef.value.srcObject = remoteStream;
        remoteVideoRef.value.play().catch(() => {});
      }
    }
  }
);

// Pantau perubahan properti tampil / sembunyi modal
watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      if (props.incomingCallData) {
        isInitiator.value = false;
        callStatus.value = 'INCOMING';
        activeCaller.value = props.incomingCallData.caller;
        startIncomingCallRing();
      } else {
        startCall();
      }
    } else {
      cleanupMedia();
    }
  }
);

watch(
  () => props.incomingCallData,
  (callData) => {
    if (callData && callData.status === 'RINGING') {
      isInitiator.value = false;
      callStatus.value = 'INCOMING';
      activeCaller.value = callData.caller;
      startIncomingCallRing();
    }
  }
);

onUnmounted(() => {
  cleanupMedia();
  stopIncomingCallRing();
  stopOutgoingDialRing();
  stopOutgoingTimeout();
  stopConnectingTimer();
});
</script>
