<template>
  <AuthenticatedLayout>
    <template #header-title>Pusat Layanan Informasi</template>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 font-sans space-y-5">
      <!-- 1. Banner Header Militer & Status Sesi -->
      <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 rounded-3xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-blue-600/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-widest bg-blue-500/20 border border-blue-400/30 text-blue-300">
                SISTEM INFORMASI PERSONEL KOMCAD
              </span>

              <!-- Status Indikator Sesi / Respon -->
              <span 
                v-if="thread?.status === 'CLOSED'"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-slate-500/30 border border-slate-400/30 text-slate-300"
              >
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                Sesi Ditutup
              </span>
              <span 
                v-else-if="isAwaitingResponse"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-amber-500/20 border border-amber-400/30 text-amber-300 shadow-sm"
              >
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                Menunggu Respon
              </span>
              <span 
                v-else
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 shadow-sm"
              >
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Anda sedang berbicara dengan {{ activeOperatorName }}
              </span>
            </div>

            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
              Pusat Layanan Informasi
            </h1>
            <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
              Saluran komunikasi dinas langsung dengan Pengelola Sisfopers Mabes TNI dan Satuan Pembina. Seluruh berkas yang dilampirkan akan terhapus saat Anda mengakhiri live chat.
            </p>
          </div>

          <!-- Aksi Sesi (Akhiri Sesi / Buka Sesi Baru) -->
          <div class="shrink-0 flex items-center gap-2.5">
            <button
              v-if="thread?.status === 'OPEN'"
              @click="confirmEndSession"
              :disabled="isEndingSession"
              type="button"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-red-600/90 hover:bg-red-600 text-white font-bold text-xs shadow-lg shadow-red-950/30 border border-red-400/30 transition cursor-pointer disabled:opacity-50"
            >
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              </svg>
              <span>{{ isEndingSession ? 'Mengakhiri Sesi...' : 'Akhiri Sesi Percakapan' }}</span>
            </button>

            <button
              v-else
              @click="startNewSession"
              :disabled="isStartingSession"
              type="button"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-950/40 border border-blue-400/30 transition cursor-pointer disabled:opacity-50"
            >
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              <span>{{ isStartingSession ? 'Membuka Chat...' : 'Mulai Chat Baru' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- 2. Kotak Percakapan Standalone -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[calc(100vh-14rem)] min-h-[580px] max-h-[850px]">
        
        <!-- Header Kotak Obrolan -->
        <div class="px-6 py-4 bg-slate-50/90 border-b border-slate-200 flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center font-black text-sm border border-blue-200 select-none">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Pusat Layanan Informasi</h3>
                <span 
                  v-if="thread?.status === 'CLOSED'"
                  class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200"
                >
                  Sesi Ditutup
                </span>
                <span 
                  v-else-if="isAwaitingResponse"
                  class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 animate-pulse"
                >
                  Menunggu Respon
                </span>
                <span 
                  v-else
                  class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200"
                >
                  Terhubung
                </span>
              </div>
              <p class="text-[11px] text-slate-600 font-medium flex items-center gap-2 mt-0.5">
                <span><strong class="text-slate-800">{{ personel?.pangkat }} {{ personel?.name }}</strong> <span class="text-slate-500">(NIKC: {{ personel?.nikc }})</span></span>
                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded-full">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  Online
                </span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <!-- Tombol Mulai Panggilan Video Dinas (Vicon P2P) -->
            <button 
              v-if="thread?.status === 'OPEN'"
              @click="openPersonelVideoCall"
              type="button" 
              class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
              title="Mulai Panggilan Video Dinas (Vicon P2P)"
            >
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <span class="hidden sm:inline">Panggilan Video</span>
            </button>

            <div class="text-right hidden sm:block">
              <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider block">Batas Lampiran</span>
              <span class="text-xs font-bold text-slate-700">Maks. 15 MB per Unggahan</span>
            </div>
          </div>
        </div>

        <!-- Banner Sub-Header: Status Interaktif Percakapan & Respon -->
        <div 
          v-if="thread?.status === 'OPEN'"
          :class="isOperatorTyping ? 'bg-emerald-100/90 border-b border-emerald-300/80 text-emerald-950' : (isAwaitingResponse ? 'bg-amber-50/80 border-b border-amber-200/60 text-amber-900' : 'bg-emerald-50/80 border-b border-emerald-200/60 text-emerald-900')"
          class="px-6 py-2.5 flex items-center justify-between text-xs transition shrink-0"
        >
          <div class="flex items-center gap-2 font-bold">
            <span 
              :class="isOperatorTyping ? 'bg-emerald-600 animate-ping' : (isAwaitingResponse ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500')"
              class="w-2 h-2 rounded-full shrink-0"
            ></span>
            <span v-if="isOperatorTyping" class="text-emerald-800 animate-pulse">
              {{ operatorTypingName }} sedang mengetik...
            </span>
            <span v-else-if="isAwaitingResponse">
              Menunggu Respon dari Petugas Dinas...
            </span>
            <span v-else>
              Anda sedang berbicara dengan {{ activeOperatorName }}
            </span>
          </div>
          <span class="text-[10px] font-semibold text-slate-500 hidden md:inline">
            Seluruh berkas yang dilampirkan akan terhapus saat Anda mengakhiri live chat.
          </span>
        </div>

        <!-- Wadah Daftar Pesan -->
        <div 
          ref="chatScrollContainer"
          class="flex-1 min-h-0 overflow-y-auto p-6 space-y-4 bg-slate-50/40 scroll-smooth"
        >
          <!-- Pesan Sambutan Sistem -->
          <div class="bg-blue-50/70 border border-blue-200/80 rounded-2xl p-4 text-xs text-blue-900 space-y-1.5 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-blue-800">
              <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Instruksi Pusat Layanan Informasi</span>
            </div>
            <p class="text-slate-600 leading-relaxed text-[11px]">
              Gunakan saluran ini untuk berkonsultasi seputar informasi formulir, kelengkapan administrasi SKEP/KTA, pembaruan data profil, atau koordinasi dinas. Seluruh berkas yang dilampirkan akan terhapus saat Anda mengakhiri live chat.
            </p>
          </div>

          <!-- Belum ada riwayat pesan -->
          <div v-if="messagesList.length === 0" class="py-16 text-center space-y-3">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
              </svg>
            </div>
            <p class="text-xs text-slate-500 font-medium">
              Belum ada pesan dalam sesi ini. Silakan ketik pertanyaan Anda pada kolom di bawah.
            </p>
          </div>

          <!-- Gelembung Pesan -->
          <template v-for="msg in messagesList" :key="msg.id">
            <!-- Pesan dari Personel (Kanan) -->
            <div 
              v-if="msg.sender_type === 'PERSONEL'"
              class="flex flex-col items-end space-y-1"
            >
              <div class="flex items-center gap-1.5 text-[10px] text-slate-400 px-1 font-medium">
                <span>Anda</span>
                <span>&bull;</span>
                <span>{{ formatTime(msg.created_at) }}</span>
                <span v-if="msg.is_read" class="text-blue-600 font-bold" title="Telah dibaca oleh petugas">
                  (Dibaca)
                </span>
              </div>

              <div class="max-w-xl bg-blue-600 text-white rounded-2xl rounded-tr-xs px-4 py-3 text-xs shadow-sm space-y-2">
                <p v-if="msg.message" class="whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>

                <!-- Lampiran Berkas Personel -->
                <div v-if="msg.attachments && msg.attachments.length > 0" class="space-y-1.5 pt-1">
                  <div 
                    v-for="(att, aIdx) in msg.attachments" 
                    :key="aIdx"
                    class="rounded-xl overflow-hidden bg-blue-700/60 border border-blue-400/30 p-2"
                  >
                    <!-- Tampilan Berkas Terhapus Permanen -->
                    <div v-if="att.purged || !att.file_path" class="text-[11px] text-blue-200 italic flex items-center gap-1.5">
                      <svg class="w-3.5 h-3.5 text-blue-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                      <span>Berkas telah terhapus dari server saat live chat diakhiri.</span>
                    </div>

                    <!-- Gambar -->
                    <div v-else-if="att.is_image" class="space-y-1">
                      <a :href="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" target="_blank" class="block">
                        <img 
                          :src="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" 
                          :alt="att.original_name"
                          class="max-h-48 rounded-lg object-cover hover:opacity-95 transition"
                        />
                      </a>
                      <div class="flex items-center justify-between text-[10px] text-blue-200">
                        <span class="truncate max-w-[200px]">{{ att.original_name }}</span>
                        <span>{{ formatFileSize(att.size) }}</span>
                      </div>
                    </div>

                    <!-- Dokumen -->
                    <div v-else class="flex items-center justify-between gap-2">
                      <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 text-blue-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs text-white truncate">{{ att.original_name }}</span>
                        <span class="text-[10px] text-blue-200 shrink-0">({{ formatFileSize(att.size) }})</span>
                      </div>
                      <a 
                        :href="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" 
                        target="_blank"
                        class="px-2 py-1 bg-white/20 hover:bg-white/30 rounded-lg text-[10px] font-bold text-white transition shrink-0"
                      >
                        Unduh
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pesan dari Operator / Petugas Dinas (Kiri) -->
            <div 
              v-else
              class="flex flex-col items-start space-y-1"
            >
              <div class="flex items-center gap-1.5 text-[10px] text-slate-500 px-1 font-medium">
                <span class="font-bold text-slate-800">{{ msg.sender_name }}</span>
                <span>&bull;</span>
                <span>{{ formatTime(msg.created_at) }}</span>
              </div>

              <div class="max-w-xl bg-white border border-slate-200 text-slate-800 rounded-2xl rounded-tl-xs px-4 py-3 text-xs shadow-xs space-y-2">
                <p v-if="msg.message" class="whitespace-pre-wrap leading-relaxed text-slate-700">{{ msg.message }}</p>

                <!-- Lampiran Berkas Operator -->
                <div v-if="msg.attachments && msg.attachments.length > 0" class="space-y-1.5 pt-1">
                  <div 
                    v-for="(att, aIdx) in msg.attachments" 
                    :key="aIdx"
                    class="rounded-xl overflow-hidden bg-slate-50 border border-slate-200 p-2"
                  >
                    <!-- Tampilan Berkas Terhapus Permanen -->
                    <div v-if="att.purged || !att.file_path" class="text-[11px] text-slate-400 italic flex items-center gap-1.5">
                      <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                      <span>Berkas telah terhapus dari server saat live chat diakhiri.</span>
                    </div>

                    <!-- Gambar -->
                    <div v-else-if="att.is_image" class="space-y-1">
                      <a :href="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" target="_blank" class="block">
                        <img 
                          :src="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" 
                          :alt="att.original_name"
                          class="max-h-48 rounded-lg object-cover hover:opacity-95 transition"
                        />
                      </a>
                      <div class="flex items-center justify-between text-[10px] text-slate-400">
                        <span class="truncate max-w-[200px]">{{ att.original_name }}</span>
                        <span>{{ formatFileSize(att.size) }}</span>
                      </div>
                    </div>

                    <!-- Dokumen -->
                    <div v-else class="flex items-center justify-between gap-2">
                      <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs text-slate-800 truncate font-medium">{{ att.original_name }}</span>
                        <span class="text-[10px] text-slate-400 shrink-0">({{ formatFileSize(att.size) }})</span>
                      </div>
                      <a 
                        :href="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" 
                        target="_blank"
                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-[10px] font-bold text-slate-700 transition shrink-0"
                      >
                        Unduh
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- Animasi Indikator Operator Sedang Mengetik -->
          <div v-if="isOperatorTyping" class="flex items-end gap-2.5 max-w-[85%] transition-all">
            <div class="w-8 h-8 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center shrink-0 text-xs font-bold shadow-xs">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div class="space-y-1">
              <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-semibold">
                <span>{{ operatorTypingName }}</span>
              </div>
              <div class="bg-white border border-slate-200 text-slate-800 text-xs px-4 py-2.5 rounded-2xl rounded-tl-xs shadow-xs flex items-center gap-2.5">
                <div class="flex items-center gap-1 py-1">
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-bounce [animation-delay:-0.3s]"></span>
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-bounce [animation-delay:-0.15s]"></span>
                  <span class="w-2 h-2 rounded-full bg-emerald-500 animate-bounce"></span>
                </div>
                <span class="text-[11px] font-semibold text-slate-600 italic">sedang mengetik...</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Area Input Pengiriman (Jika Sesi Terbuka) -->
        <div v-if="thread?.status === 'OPEN'" class="p-4 bg-white border-t border-slate-200 space-y-3 shrink-0">
          <!-- Daftar Lampiran Siap Unggah -->
          <div v-if="stagedFiles.length > 0" class="flex flex-wrap items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-2xl">
            <span class="text-[10px] font-extrabold uppercase text-slate-500 px-1">
              Lampiran ({{ stagedFiles.length }} berkas - {{ formatFileSize(totalStagedSize) }}):
            </span>

            <div 
              v-for="(f, fIdx) in stagedFiles" 
              :key="fIdx"
              class="flex items-center gap-1.5 bg-white border border-slate-200 px-2.5 py-1 rounded-xl text-xs font-semibold text-slate-700 shadow-2xs"
            >
              <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
              </svg>
              <span class="truncate max-w-[140px] text-[11px]">{{ f.name }}</span>
              <span class="text-[10px] text-slate-400">({{ formatFileSize(f.size) }})</span>
              <button 
                @click="removeStagedFile(fIdx)" 
                type="button" 
                class="text-slate-400 hover:text-red-600 transition p-0.5 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div v-if="totalStagedSize > maxAllowedBytes" class="w-full text-left text-red-600 text-[11px] font-bold px-1">
              Peringatan: Total berkas lampiran melebihi batas maksimal 15MB. Mohon kurangi berkas.
            </div>
          </div>

          <!-- Form Input Baris -->
          <form @submit.prevent="sendMessage" class="flex items-end gap-2.5">
            <!-- Tombol Lampirkan Berkas Bulk -->
            <input 
              ref="fileInputRef" 
              type="file" 
              multiple 
              accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt" 
              class="hidden" 
              @change="handleFileChange"
            />
            <button
              @click="$refs.fileInputRef.click()"
              type="button"
              title="Unggah lampiran foto atau dokumen bulk (Maks 15MB)"
              class="p-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition cursor-pointer shrink-0 border border-slate-200/80"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
              </svg>
            </button>

            <!-- Textarea Pesan -->
            <div class="flex-1 min-w-0 relative">
              <textarea
                v-model="inputMessage"
                @input="handleTyping"
                @keydown.enter.exact.prevent="sendMessage"
                rows="2"
                placeholder="Tulis pesan dinas atau pertanyaan Anda di sini... (Tekan Enter untuk kirim)"
                class="w-full resize-none px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
              ></textarea>
            </div>

            <!-- Tombol Kirim -->
            <button
              :disabled="isSending || (!inputMessage.trim() && stagedFiles.length === 0) || totalStagedSize > maxAllowedBytes"
              type="submit"
              class="px-5 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition cursor-pointer flex items-center gap-2 shrink-0"
            >
              <span v-if="!isSending">Kirim</span>
              <span v-else>Mengirim...</span>
              <svg v-if="!isSending" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
              </svg>
              <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
            </button>
          </form>
        </div>

        <!-- Banner Jika Sesi Telah Ditutup -->
        <div v-else class="p-5 bg-slate-100 border-t border-slate-200 text-center space-y-3 shrink-0">
          <div class="flex items-center justify-center gap-2 text-xs font-bold text-slate-700">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <span>Sesi percakapan ini telah berakhir. Seluruh berkas yang dilampirkan telah terhapus saat Anda mengakhiri live chat.</span>
          </div>

          <button
            @click="startNewSession"
            :disabled="isStartingSession"
            type="button"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition cursor-pointer disabled:opacity-50"
          >
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ isStartingSession ? 'Membuka Chat...' : 'Mulai Chat Baru' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Komponen Ruang Panggilan Video Dinas (Vicon P2P WebRTC) -->
    <VideoCallModal 
      :show="showPersonelVideoCallModal"
      :thread-uuid="thread?.uuid || activeIncomingCallData?.thread_uuid"
      user-role="PERSONEL"
      :current-user="currentUserInfo"
      :partner-user="callPartnerInfo"
      :incoming-call-data="activeIncomingCallData"
      url-prefix="personel"
      @close="closePersonelVideoCallModal"
      @call-ended="onPersonelCallEnded"
      @call-accepted="onPersonelCallAccepted"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VideoCallModal from '@/Components/Chat/VideoCallModal.vue';
import { playNotificationSound } from '@/Utils/sound';
import Swal from 'sweetalert2';
import axios from 'axios';

const showPersonelVideoCallModal = ref(false);
const activeIncomingCallData = ref(null);

const currentUserInfo = computed(() => ({
  id: props.personel?.id,
  name: props.personel?.name,
  pangkat: props.personel?.pangkat,
  photo: props.personel?.photo_profile,
}));

const callPartnerInfo = computed(() => {
  if (activeIncomingCallData.value?.caller) {
    return {
      id: activeIncomingCallData.value.caller.id,
      name: activeIncomingCallData.value.caller.name,
      pangkat: activeIncomingCallData.value.caller.pangkat,
      photo: activeIncomingCallData.value.caller.photo,
      matra: 'Pengelola',
      nikc: null,
    };
  }
  return {
    id: null,
    name: activeOperatorName.value || 'Petugas Layanan Informasi',
    pangkat: 'Pengelola Dinas',
    photo: null,
    matra: 'KC',
    nikc: null,
  };
});

const openPersonelVideoCall = () => {
  if (!thread.value || thread.value.status !== 'OPEN') return;
  activeIncomingCallData.value = null;
  showPersonelVideoCallModal.value = true;
};

const closePersonelVideoCallModal = () => {
  showPersonelVideoCallModal.value = false;
  activeIncomingCallData.value = null;
};

const onPersonelCallEnded = () => {
  if (thread.value?.uuid) {
    axios.get(`/personel/live-chat/${thread.value.uuid}/messages`).then((res) => {
      if (res.data.messages) {
        messagesList.value = res.data.messages;
        scrollToBottom();
      }
    });
  }
};

const onPersonelCallAccepted = () => {
  // Panggilan diterima
};

const props = defineProps({
  initialThread: Object,
  initialMessages: Array,
  personel: Object,
});

const thread = ref(props.initialThread || null);
const messagesList = ref(props.initialMessages || []);
const inputMessage = ref('');
const stagedFiles = ref([]);
const isSending = ref(false);
const isEndingSession = ref(false);
const isStartingSession = ref(false);

const chatScrollContainer = ref(null);
const fileInputRef = ref(null);

const isOperatorTyping = ref(false);
const operatorTypingName = ref('');
let operatorTypingTimer = null;
let lastTypingSentAt = 0;

const updateOperatorTypingStatus = (typing) => {
  if (typing?.is_typing) {
    const wasTyping = isOperatorTyping.value;
    isOperatorTyping.value = true;
    operatorTypingName.value = typing.name || activeOperatorName.value || 'Petugas Layanan';
    if (!wasTyping) {
      scrollToBottom();
    }
    if (operatorTypingTimer) clearTimeout(operatorTypingTimer);
    operatorTypingTimer = setTimeout(() => {
      isOperatorTyping.value = false;
    }, 4500);
  } else {
    isOperatorTyping.value = false;
    if (operatorTypingTimer) {
      clearTimeout(operatorTypingTimer);
      operatorTypingTimer = null;
    }
  }
};

const handleTyping = () => {
  if (!thread.value || !thread.value.uuid || thread.value.status !== 'OPEN') return;
  const now = Date.now();
  if (now - lastTypingSentAt > 2000) {
    lastTypingSentAt = now;
    axios.post(`/personel/live-chat/${thread.value.uuid}/typing`)
      .then((res) => {
        if (res.data?.typing) {
          updateOperatorTypingStatus(res.data.typing);
        }
      })
      .catch(() => {});
  }
};

let pollingTimer = null;
const maxAllowedBytes = 15 * 1024 * 1024; // 15 Megabytes

// Cari pesan balasan dinas terakhir dari Admin / PJU / Koordinator
const latestDinasMessage = computed(() => {
  return [...messagesList.value].reverse().find((m) => m.sender_type !== 'PERSONEL');
});

const isAwaitingResponse = computed(() => {
  return thread.value?.status === 'OPEN' && !latestDinasMessage.value;
});

const activeOperatorName = computed(() => {
  return latestDinasMessage.value?.sender_name || 'Petugas Layanan';
});

const totalStagedSize = computed(() => {
  return stagedFiles.value.reduce((acc, f) => acc + (f.size || 0), 0);
});

const formatTime = (ts) => {
  if (!ts) return '';
  const d = new Date(ts);
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB';
};

const formatFileSize = (bytes) => {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const scrollToBottom = () => {
  nextTick(() => {
    if (chatScrollContainer.value) {
      chatScrollContainer.value.scrollTop = chatScrollContainer.value.scrollHeight;
    }
  });
};

const handleFileChange = (e) => {
  const files = Array.from(e.target.files || []);
  if (!files.length) return;
  stagedFiles.value.push(...files);
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const removeStagedFile = (idx) => {
  stagedFiles.value.splice(idx, 1);
};

const pollMessages = async () => {
  // Jika belum ada utas atau status saat ini DITUTUP, periksa apakah ada sesi terbuka baru yang diinisiasi oleh pengelola
  if (!thread.value || !thread.value.uuid || thread.value.status === 'CLOSED') {
    try {
      const activeRes = await axios.get('/personel/live-chat/active-session');
      if (activeRes.data?.call && activeRes.data.call.status === 'RINGING' && activeRes.data.call.caller?.type !== 'PERSONEL') {
        if (!showPersonelVideoCallModal.value) {
          activeIncomingCallData.value = activeRes.data.call;
          showPersonelVideoCallModal.value = true;
        }
      }
      if (activeRes.data?.thread && activeRes.data.thread.status === 'OPEN') {
        thread.value = activeRes.data.thread;
        messagesList.value = activeRes.data.messages || [];
        scrollToBottom();
        playNotificationSound();
        return;
      }
    } catch (e) {
      console.debug('Pemeriksaan sesi aktif personel terkendala:', e);
    }
    return;
  }

  const lastMsg = messagesList.value[messagesList.value.length - 1];
  const lastId = lastMsg ? lastMsg.id : 0;

  try {
    const res = await axios.get(`/personel/live-chat/${thread.value.uuid}/messages`, {
      params: { last_id: lastId },
    });

    if (res.data.status) {
      thread.value.status = res.data.status;
    }

    if (res.data.typing) {
      updateOperatorTypingStatus(res.data.typing);
    }

    if (res.data.call && res.data.call.status === 'RINGING' && res.data.call.caller?.type !== 'PERSONEL') {
      if (!showPersonelVideoCallModal.value) {
        activeIncomingCallData.value = res.data.call;
        showPersonelVideoCallModal.value = true;
      }
    }

    if (res.data.messages && res.data.messages.length > 0) {
      const hasDinasMessage = res.data.messages.some((m) => m.sender_type !== 'PERSONEL');
      messagesList.value.push(...res.data.messages);
      scrollToBottom();

      if (hasDinasMessage) {
        playNotificationSound();
      }
    }
  } catch (err) {
    console.debug('Pembaruan pesan dinas terkendala:', err);
  }
};

const startPolling = () => {
  stopPolling();
  pollingTimer = setInterval(() => {
    pollMessages();
  }, 2500); // Polling asinkron teratur setiap 2.5 detik
};

const stopPolling = () => {
  if (pollingTimer) {
    clearInterval(pollingTimer);
    pollingTimer = null;
  }
};

const sendMessage = async () => {
  if (isSending.value || !thread.value) return;
  const text = inputMessage.value.trim();

  if (!text && stagedFiles.value.length === 0) return;

  if (totalStagedSize.value > maxAllowedBytes) {
    Swal.fire({
      icon: 'error',
      title: 'Batas Ukuran Terlampaui',
      text: 'Total akumulasi berkas lampiran tidak boleh melebihi 15 MB.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
    return;
  }

  isSending.value = true;
  const formData = new FormData();
  if (text) {
    formData.append('message', text);
  }

  stagedFiles.value.forEach((f) => {
    formData.append('attachments[]', f);
  });

  try {
    const res = await axios.post(`/personel/live-chat/${thread.value.uuid}/send`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (res.data.message) {
      messagesList.value.push(res.data.message);
      inputMessage.value = '';
      stagedFiles.value = [];
      scrollToBottom();
    }
  } catch (err) {
    const errMsg = err.response?.data?.error || 'Gagal mengirimkan pesan.';
    Swal.fire({
      icon: 'error',
      title: 'Gagal Mengirim',
      text: errMsg,
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  } finally {
    isSending.value = false;
  }
};

const confirmEndSession = () => {
  if (isEndingSession.value) return;

  Swal.fire({
    icon: 'warning',
    title: 'Akhiri Sesi Percakapan?',
    text: 'Apakah Anda yakin ingin mengakhiri sesi percakapan ini? Seluruh berkas yang dilampirkan akan terhapus saat Anda mengakhiri live chat.',
    showCancelButton: true,
    confirmButtonText: 'Ya, Akhiri & Hapus Berkas',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#DC2626',
    cancelButtonColor: '#64748B',
    customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' },
  }).then((result) => {
    if (result.isConfirmed) {
      executeEndSession();
    }
  });
};

const executeEndSession = async () => {
  if (!thread.value) return;
  isEndingSession.value = true;

  try {
    const res = await axios.post(`/personel/live-chat/${thread.value.uuid}/end`);
    if (res.data.success) {
      thread.value.status = 'CLOSED';
      // Tandai lampiran sebagai terhapus di antarmuka
      messagesList.value.forEach((m) => {
        if (m.attachments) {
          m.attachments.forEach((a) => {
            a.purged = true;
            a.file_path = null;
          });
        }
      });

      Swal.fire({
        icon: 'success',
        title: 'Sesi Berhasil Diakhiri',
        text: 'Seluruh berkas yang dilampirkan telah terhapus dari server penyimpanan saat Anda mengakhiri live chat.',
        confirmButtonColor: '#2563EB',
        customClass: { popup: 'rounded-2xl' },
      });
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Hambatan',
      text: 'Gagal mengakhiri sesi obrolan dinas.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  } finally {
    isEndingSession.value = false;
  }
};

const startNewSession = async () => {
  isStartingSession.value = true;
  try {
    const res = await axios.post('/personel/live-chat/new');
    if (res.data.success && res.data.thread) {
      thread.value = res.data.thread;
      messagesList.value = [];
      inputMessage.value = '';
      stagedFiles.value = [];
      scrollToBottom();

      Swal.fire({
        icon: 'success',
        title: 'Chat Baru Dimulai',
        text: 'Anda telah terhubung pada sesi chat baru.',
        timer: 2000,
        showConfirmButton: false,
        customClass: { popup: 'rounded-2xl' },
      });
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal Memulai Chat',
      text: 'Terjadi kendala saat memulai chat baru.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  } finally {
    isStartingSession.value = false;
  }
};

onMounted(() => {
  scrollToBottom();
  startPolling();
});

onUnmounted(() => {
  stopPolling();
  if (operatorTypingTimer) clearTimeout(operatorTypingTimer);
});
</script>
