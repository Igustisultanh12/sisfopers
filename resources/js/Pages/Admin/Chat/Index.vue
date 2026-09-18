<template>
  <AuthenticatedLayout>
    <template #header-title>Pusat Layanan Informasi Personel</template>

    <div class="space-y-5">
      <!-- 1. Statistik Ringkasan Obrolan -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sesi Terbuka (Aktif)</p>
            <h3 class="text-2xl font-black text-slate-900 mt-1">{{ stats?.total_open || 0 }}</h3>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pesan Baru Belum Dibalas</p>
            <h3 class="text-2xl font-black text-blue-600 mt-1">{{ stats?.total_unread || 0 }}</h3>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Utas Terdaftar</p>
            <h3 class="text-2xl font-black text-slate-900 mt-1">{{ threads?.total || 0 }}</h3>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- 2. Antarmuka Split Obrolan (Daftar Kiri & Pesan Kanan) -->
      <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">
        
        <!-- KOLOM KIRI: Daftar Percakapan Personel (lg:col-span-5) -->
        <div class="lg:col-span-5 border-r border-slate-200 flex flex-col h-full bg-slate-50/40">
          
          <!-- Filter & Pencarian -->
          <div class="p-4 border-b border-slate-200 bg-white space-y-3">
            <div class="relative">
              <input 
                v-model="searchQuery" 
                @keyup.enter="applyFilters"
                type="text" 
                placeholder="Cari nama, NIKC, atau nomor telepon..." 
                class="w-full text-xs pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600"
              />
              <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <div class="flex items-center gap-2">
              <button 
                @click="setStatusFilter('all')" 
                type="button" 
                :class="statusFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
              >
                Semua
              </button>
              <button 
                @click="setStatusFilter('OPEN')" 
                type="button" 
                :class="statusFilter === 'OPEN' ? 'bg-emerald-600 text-white font-bold' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
              >
                Terbuka
              </button>
              <button 
                @click="setStatusFilter('CLOSED')" 
                type="button" 
                :class="statusFilter === 'CLOSED' ? 'bg-slate-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                class="px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
              >
                Ditutup
              </button>
            </div>
          </div>

          <!-- Daftar Utas Obrolan -->
          <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            <div v-if="!threads?.data || threads.data.length === 0" class="p-8 text-center text-slate-400 text-xs">
              Tidak ada sesi obrolan yang sesuai kriteria.
            </div>

            <div 
              v-for="th in threads?.data" 
              :key="th.id"
              @click="selectThread(th)"
              :class="selectedThread?.uuid === th.uuid ? 'bg-blue-50/70 border-l-4 border-blue-600' : 'hover:bg-slate-50 border-l-4 border-transparent'"
              class="p-4 cursor-pointer transition flex items-start gap-3 text-left"
            >
              <!-- Avatar Personel -->
              <img 
                :src="th.personel?.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(th.personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(th.personel?.full_name || 'P')}&background=e2e8f0&color=334155`" 
                class="w-11 h-11 object-cover rounded-xl border border-slate-200 shrink-0 mt-0.5" 
              />

              <!-- Info Personel & Pesan Terakhir -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1 mb-0.5">
                  <h4 class="text-xs font-bold text-slate-900 truncate">
                    {{ th.personel?.full_name }}
                  </h4>
                  <span class="text-[10px] text-slate-400 shrink-0 font-medium">
                    {{ formatTimestamp(th.last_message_at) }}
                  </span>
                </div>

                <div class="flex items-center gap-2 text-[10px] text-slate-500 mb-1">
                  <span class="font-bold text-blue-600">{{ th.personel?.pangkat || 'Prajurit KC' }}</span>
                  <span>&bull;</span>
                  <span>Matra {{ th.personel?.matra || 'AD' }}</span>
                  <span v-if="th.status === 'CLOSED'" class="px-1.5 py-0.2 rounded bg-slate-200 text-slate-700 text-[9px] font-bold uppercase">
                    Ditutup
                  </span>
                </div>

                <p class="text-xs text-slate-600 truncate">
                  <span v-if="th.latest_message?.sender_type !== 'PERSONEL'" class="font-semibold text-slate-500">Anda: </span>
                  {{ th.latest_message?.message || (th.latest_message?.attachments?.length ? '[Lampiran Berkas]' : 'Belum ada pesan') }}
                </p>
              </div>

              <!-- Lencana Pesan Baru dari Personel -->
              <span 
                v-if="th.unread_admin > 0" 
                class="w-5 h-5 rounded-full bg-blue-600 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-1 shadow-xs"
              >
                {{ th.unread_admin }}
              </span>
            </div>
          </div>
        </div>

        <!-- KOLOM KANAN: Ruang Obrolan Aktif (lg:col-span-7) -->
        <div class="lg:col-span-7 flex flex-col h-full bg-white">
          
          <template v-if="selectedThread">
            <!-- Header Utas Aktif -->
            <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-white shrink-0">
              <div class="flex items-center gap-3">
                <img 
                  :src="selectedThread.personel?.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(selectedThread.personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedThread.personel?.full_name || 'P')}&background=e2e8f0&color=334155`" 
                  class="w-11 h-11 object-cover rounded-xl border border-slate-200 shrink-0" 
                />
                <div>
                  <h3 class="text-sm font-extrabold text-slate-900">
                    {{ selectedThread.personel?.pangkat }} {{ selectedThread.personel?.full_name }}
                  </h3>
                  <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                    <span>NIKC: <strong>{{ selectedThread.personel?.nikc || selectedThread.personel?.nik }}</strong></span>
                    <span>&bull;</span>
                    <span>No HP: <strong>{{ selectedThread.personel?.phone_number || '-' }}</strong></span>
                    <span>&bull;</span>
                    <span :class="selectedThread.status === 'OPEN' ? 'text-emerald-600 font-bold' : 'text-slate-500 font-bold'">
                      {{ selectedThread.status === 'OPEN' ? 'Sesi Terbuka' : 'Sesi Ditutup' }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex items-center gap-2">
                <button 
                  @click="toggleStatus(selectedThread.uuid)" 
                  type="button" 
                  :class="selectedThread.status === 'OPEN' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border-emerald-200'"
                  class="px-3 py-1.5 rounded-xl border text-xs font-bold transition cursor-pointer"
                >
                  {{ selectedThread.status === 'OPEN' ? 'Tutup Sesi' : 'Buka Sesi Kembali' }}
                </button>
              </div>
            </div>

            <!-- Wadah Pesan Obrolan -->
            <div 
              ref="adminChatContainer" 
              class="flex-1 p-4 overflow-y-auto space-y-4 bg-slate-50/50 scroll-smooth min-h-[380px]"
            >
              <div v-if="activeMessagesList.length === 0" class="flex flex-col items-center justify-center h-48 text-slate-400 text-xs">
                Belum ada pesan dalam sesi obrolan ini.
              </div>

              <template v-for="msg in activeMessagesList" :key="msg.id">
                <!-- Pesan Masuk dari Personel (Kiri) -->
                <div v-if="msg.sender_type === 'PERSONEL'" class="flex gap-3 max-w-[85%]">
                  <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-bold text-slate-800">{{ msg.sender_name }}</span>
                      <span class="text-[10px] text-slate-400">{{ formatTime(msg.created_at) }}</span>
                    </div>
                    <div class="bg-white border border-slate-200 text-slate-800 text-xs p-3.5 rounded-2xl rounded-tl-xs shadow-xs leading-relaxed">
                      <p v-if="msg.message" class="whitespace-pre-wrap">{{ msg.message }}</p>

                      <!-- Lampiran Personel -->
                      <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-2.5 pt-2 border-t border-slate-100 space-y-2">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Lampiran Berkas ({{ msg.attachments.length }}):</p>
                        <div class="grid grid-cols-1 gap-1.5">
                          <div 
                            v-for="(att, idx) in msg.attachments" 
                            :key="idx" 
                            class="flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-50 border border-slate-200 text-[11px]"
                          >
                            <div class="flex items-center gap-2 truncate min-w-0">
                              <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                              </svg>
                              <span class="truncate text-slate-700 font-medium">{{ att.original_name }}</span>
                              <span class="text-[9px] text-slate-400 shrink-0">({{ formatBytes(att.size) }})</span>
                            </div>
                            <span v-if="att.purged || !att.file_path" class="shrink-0 text-slate-400 text-[10px] font-semibold italic">
                              Berkas Terhapus
                            </span>
                            <a 
                              v-else
                              :href="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" 
                              target="_blank" 
                              class="shrink-0 text-blue-600 hover:text-blue-800 font-bold underline text-[10px]"
                            >
                              Unduh
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Pesan Keluar dari Operator / Admin (Kanan) -->
                <div v-else class="flex justify-end">
                  <div class="max-w-[85%] space-y-1 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <span class="text-[10px] text-slate-400">{{ formatTime(msg.created_at) }}</span>
                      <span class="text-xs font-bold text-slate-800">{{ msg.sender_name }}</span>
                    </div>
                    <div class="bg-slate-900 text-white text-xs p-3.5 rounded-2xl rounded-tr-xs shadow-md text-left leading-relaxed">
                      <p v-if="msg.message" class="whitespace-pre-wrap">{{ msg.message }}</p>

                      <!-- Lampiran Petugas -->
                      <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-2.5 pt-2 border-t border-slate-700 space-y-2">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Lampiran Berkas ({{ msg.attachments.length }}):</p>
                        <div class="grid grid-cols-1 gap-1.5">
                          <div 
                            v-for="(att, idx) in msg.attachments" 
                            :key="idx" 
                            class="flex items-center justify-between gap-2 p-2 rounded-xl bg-slate-800 border border-slate-700 text-[11px]"
                          >
                            <div class="flex items-center gap-2 truncate min-w-0">
                              <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                              </svg>
                              <span class="truncate text-white font-medium">{{ att.original_name }}</span>
                              <span class="text-[9px] text-slate-300 shrink-0">({{ formatBytes(att.size) }})</span>
                            </div>
                            <span v-if="att.purged || !att.file_path" class="shrink-0 text-amber-300 text-[10px] font-semibold italic">
                              Berkas Terhapus
                            </span>
                            <a 
                              v-else
                              :href="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" 
                              target="_blank" 
                              class="shrink-0 text-blue-400 hover:text-blue-300 font-bold underline text-[10px]"
                            >
                              Unduh
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- Staged File Previews Admin -->
            <div v-if="adminStagedFiles.length > 0" class="p-3 bg-slate-100 border-t border-slate-200 shrink-0 space-y-2">
              <div class="flex items-center justify-between text-[11px]">
                <span class="font-bold text-slate-700">Lampiran Jawaban ({{ adminStagedFiles.length }})</span>
                <span :class="adminTotalStagedSize > maxAllowedBytes ? 'text-red-600 font-bold' : 'text-slate-500'">
                  Total: {{ formatBytes(adminTotalStagedSize) }} / 15 MB
                </span>
              </div>
              <div class="flex flex-wrap gap-2">
                <div 
                  v-for="(f, i) in adminStagedFiles" 
                  :key="i"
                  class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white border border-slate-300 text-[11px]"
                >
                  <span class="truncate max-w-[140px] text-slate-700">{{ f.name }}</span>
                  <span class="text-[9px] text-slate-400">({{ formatBytes(f.size) }})</span>
                  <button @click="removeAdminStagedFile(i)" type="button" class="text-red-500 hover:text-red-700 ml-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Composer Balasan Pesan Admin / Status Ditutup -->
            <div v-if="selectedThread.status === 'CLOSED'" class="p-4 bg-slate-50 border-t border-slate-200 text-center space-y-2 shrink-0">
              <p class="text-xs text-slate-600 font-medium">Sesi obrolan ini telah ditutup. Seluruh berkas lampiran otomatis dihapus permanen dari server.</p>
              <button 
                @click="toggleStatus(selectedThread.uuid)" 
                type="button" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                <span>Buka Sesi Kembali</span>
              </button>
            </div>

            <form v-else @submit.prevent="submitAdminReply" class="p-4 bg-white border-t border-slate-200 shrink-0">
              <div class="flex items-end gap-2">
                <label 
                  class="p-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-600 hover:text-slate-900 transition cursor-pointer shrink-0 flex items-center justify-center"
                  title="Pilih foto atau dokumen jawaban (Bulk max 15MB)"
                >
                  <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                  </svg>
                  <input 
                    type="file" 
                    multiple 
                    @change="handleAdminFileChange" 
                    accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt" 
                    class="hidden" 
                    ref="adminFileInputRef"
                  />
                </label>

                <div class="flex-1 min-w-0">
                  <textarea 
                    v-model="adminReplyMessage" 
                    @keydown.enter.exact.prevent="submitAdminReply"
                    rows="1" 
                    placeholder="Tulis balasan arahan dinas..." 
                    class="w-full text-xs px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 resize-none max-h-28"
                  ></textarea>
                </div>

                <button 
                  type="submit" 
                  :disabled="isAdminSending || adminTotalStagedSize > maxAllowedBytes || (!adminReplyMessage.trim() && adminStagedFiles.length === 0)"
                  class="px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-blue-600/20 transition cursor-pointer shrink-0"
                >
                  <span v-if="!isAdminSending">Kirim</span>
                  <span v-else>Mengirim...</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                  </svg>
                </button>
              </div>
            </form>
          </template>

          <template v-else>
            <div class="flex flex-col items-center justify-center h-full p-8 text-center text-slate-400">
              <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mb-3 text-slate-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
              </div>
              <h4 class="text-sm font-bold text-slate-700">Pilih Percakapan Personel</h4>
              <p class="text-xs text-slate-500 max-w-sm mt-1">Silakan klik salah satu obrolan personel pada panel kiri untuk meninjau riwayat pertanyaan dan memberikan balasan dinas.</p>
            </div>
          </template>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { playNotificationSound } from '@/Utils/sound';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  threads: Object,
  activeThread: Object,
  activeMessages: Array,
  filters: Object,
  stats: Object,
});

const searchQuery = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const selectedThread = ref(props.activeThread || null);
const activeMessagesList = ref(props.activeMessages || []);

const adminReplyMessage = ref('');
const adminStagedFiles = ref([]);
const adminFileInputRef = ref(null);
const adminChatContainer = ref(null);
const isAdminSending = ref(false);

const maxAllowedBytes = 15 * 1024 * 1024;

const adminTotalStagedSize = computed(() => {
  return adminStagedFiles.value.reduce((sum, f) => sum + (f.size || 0), 0);
});

let adminPollingTimer = null;

const formatBytes = (bytes) => {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const formatTime = (isoString) => {
  if (!isoString) return '';
  const d = new Date(isoString);
  const h = String(d.getHours()).padStart(2, '0');
  const m = String(d.getMinutes()).padStart(2, '0');
  return `${h}:${m} WIB`;
};

const formatTimestamp = (isoString) => {
  if (!isoString) return '-';
  const d = new Date(isoString);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
};

const scrollAdminChatToBottom = async () => {
  await nextTick();
  if (adminChatContainer.value) {
    adminChatContainer.value.scrollTop = adminChatContainer.value.scrollHeight;
  }
};

const applyFilters = () => {
  router.get(
    window.location.pathname,
    {
      search: searchQuery.value,
      status: statusFilter.value,
      thread: selectedThread.value?.uuid,
    },
    { preserveState: true, replace: true }
  );
};

const setStatusFilter = (st) => {
  statusFilter.value = st;
  applyFilters();
};

const selectThread = (th) => {
  selectedThread.value = th;
  th.unread_admin = 0;
  loadThreadMessages(th.uuid);
};

const getPrefix = () => {
  if (typeof window !== 'undefined') {
    if (window.location.pathname.startsWith('/pju')) return 'pju';
    if (window.location.pathname.startsWith('/kordinator')) return 'kordinator';
  }
  return 'admin';
};

// Pengambilan pesan utas secara asinkron
const loadThreadMessages = async (uuid) => {
  try {
    const res = await axios.get(`/${getPrefix()}/live-chat/${uuid}/messages`);
    activeMessagesList.value = res.data.messages || [];
    scrollAdminChatToBottom();
  } catch (err) {
    console.error('Gagal mengambil pesan utas:', err);
  }
};

// Polling asinkron pembaharuan pesan masuk dari Personel
const pollAdminMessages = async () => {
  if (!selectedThread.value || !selectedThread.value.uuid) return;
  const lastMsg = activeMessagesList.value[activeMessagesList.value.length - 1];
  const lastId = lastMsg ? lastMsg.id : 0;

  try {
    const res = await axios.get(`/${getPrefix()}/live-chat/${selectedThread.value.uuid}/messages`, {
      params: { last_id: lastId },
    });

    if (res.data.status) {
      selectedThread.value.status = res.data.status;
    }

    if (res.data.messages && res.data.messages.length > 0) {
      const hasPersonelMsg = res.data.messages.some(m => m.sender_type === 'PERSONEL');
      activeMessagesList.value.push(...res.data.messages);
      scrollAdminChatToBottom();
      if (hasPersonelMsg) {
        playNotificationSound();
      }
    }
  } catch (err) {
    console.error('Pembaruan pesan terhambat:', err);
  }
};

const startAdminPolling = () => {
  stopAdminPolling();
  adminPollingTimer = setInterval(() => {
    pollAdminMessages();
  }, 3500); // Polling asinkron berkala setiap 3.5 detik
};

const stopAdminPolling = () => {
  if (adminPollingTimer) {
    clearInterval(adminPollingTimer);
    adminPollingTimer = null;
  }
};

const handleAdminFileChange = (e) => {
  const selected = Array.from(e.target.files || []);
  if (!selected.length) return;
  adminStagedFiles.value.push(...selected);
  if (adminFileInputRef.value) {
    adminFileInputRef.value.value = '';
  }
};

const removeAdminStagedFile = (idx) => {
  adminStagedFiles.value.splice(idx, 1);
};

const submitAdminReply = async () => {
  if (isAdminSending.value || !selectedThread.value) return;
  const msgText = adminReplyMessage.value.trim();

  if (!msgText && adminStagedFiles.value.length === 0) return;

  if (adminTotalStagedSize.value > maxAllowedBytes) {
    Swal.fire({
      icon: 'error',
      title: 'Ukuran Terlalu Besar',
      text: 'Total seluruh lampiran berkas tidak boleh melebihi 15 MB.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
    return;
  }

  isAdminSending.value = true;
  const formData = new FormData();
  if (msgText) {
    formData.append('message', msgText);
  }

  adminStagedFiles.value.forEach((file) => {
    formData.append('attachments[]', file);
  });

  try {
    const res = await axios.post(`/${getPrefix()}/live-chat/${selectedThread.value.uuid}/send`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (res.data.message) {
      activeMessagesList.value.push(res.data.message);
      adminReplyMessage.value = '';
      adminStagedFiles.value = [];
      scrollAdminChatToBottom();
    }
  } catch (err) {
    const errMsg = err.response?.data?.error || 'Gagal mengirim pesan balasan dinas.';
    Swal.fire({
      icon: 'error',
      title: 'Pesan Gagal Terkirim',
      text: errMsg,
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  } finally {
    isAdminSending.value = false;
  }
};

const toggleStatus = (uuid) => {
  const isClosing = selectedThread.value?.status === 'OPEN';
  if (isClosing) {
    Swal.fire({
      icon: 'warning',
      title: 'Tutup Sesi Obrolan?',
      text: 'Apakah Anda yakin ingin menutup sesi obrolan ini? Seluruh berkas lampiran yang ada dalam obrolan akan otomatis dihapus permanen dari server.',
      showCancelButton: true,
      confirmButtonText: 'Ya, Tutup Sesi & Hapus Berkas',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#DC2626',
      cancelButtonColor: '#64748B',
      customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' },
    }).then((result) => {
      if (result.isConfirmed) {
        executeToggleStatus(uuid);
      }
    });
  } else {
    executeToggleStatus(uuid);
  }
};

const executeToggleStatus = async (uuid) => {
  try {
    const res = await axios.post(`/${getPrefix()}/live-chat/${uuid}/status`);
    if (res.data.success) {
      if (selectedThread.value) {
        selectedThread.value.status = res.data.status;
        if (res.data.status === 'CLOSED') {
          activeMessagesList.value.forEach((msg) => {
            if (msg.attachments) {
              msg.attachments.forEach((att) => {
                att.purged = true;
                att.file_path = null;
              });
            }
          });
        }
      }

      // Sinkronisasi status pada item daftar utas di sisi kiri
      if (props.threads?.data) {
        const targetThread = props.threads.data.find((t) => t.uuid === uuid);
        if (targetThread) {
          targetThread.status = res.data.status;
        }
      }

      Swal.fire({
        icon: 'success',
        title: 'Status Diperbarui',
        text: res.data.message,
        confirmButtonColor: '#2563EB',
        customClass: { popup: 'rounded-2xl' },
      });
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Hambatan',
      text: err.response?.data?.error || 'Gagal mengubah status sesi percakapan.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  }
};

onMounted(() => {
  scrollAdminChatToBottom();
  startAdminPolling();
});

onUnmounted(() => {
  stopAdminPolling();
});
</script>
