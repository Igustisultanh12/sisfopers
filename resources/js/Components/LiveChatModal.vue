<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-end sm:justify-center p-0 sm:p-4 bg-slate-950/60 backdrop-blur-xs">
    <!-- Container Modal / Drawer Obrolan -->
    <div 
      class="bg-white w-full sm:max-w-xl h-[88vh] sm:h-[650px] rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-200 transition-all duration-300"
    >
      <!-- 1. Header Obrolan Dinas -->
      <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 p-4 sm:p-5 text-white flex items-center justify-between shrink-0 shadow-md">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-blue-600/30 border border-blue-400/40 flex items-center justify-center text-white shrink-0">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-sm font-extrabold tracking-wide text-white uppercase">Live Chat Layanan Personel</h3>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Terhubung
              </span>
            </div>
            <p class="text-[11px] text-slate-300 mt-0.5">Pusat Konsultasi, Pertanyaan & Pembinaan Personel Komcad</p>
          </div>
        </div>

        <button 
          @click="close" 
          type="button" 
          class="p-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition cursor-pointer"
          title="Tutup Jendela"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- 2. Status Banner Utas -->
      <div v-if="thread && thread.status === 'CLOSED'" class="bg-amber-500/10 border-b border-amber-500/20 px-4 py-2 flex items-center justify-between text-xs text-amber-800">
        <span class="font-medium">Sesi obrolan ini telah ditutup oleh operator dinas.</span>
        <span class="text-[10px] font-bold uppercase bg-amber-200 text-amber-900 px-2 py-0.5 rounded">Tutup</span>
      </div>

      <!-- 3. Wadah Aliran Pesan Obrolan (Message Stream) -->
      <div 
        ref="messageContainer" 
        class="flex-1 p-4 overflow-y-auto space-y-4 bg-slate-50/50 scroll-smooth"
      >
        <!-- Indikator Memuat Data Awal -->
        <div v-if="isLoading" class="flex flex-col items-center justify-center h-48 text-slate-400 text-xs gap-2">
          <svg class="w-6 h-6 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          <span>Memuat riwayat obrolan...</span>
        </div>

        <!-- Sambutan Awal dari Dinas SISFOPERSKC -->
        <div v-else class="space-y-4">
          <div class="flex gap-3 max-w-[85%]">
            <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-xs">
              <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800">Operator Pelayanan Dinas</span>
                <span class="text-[10px] text-slate-400">Sistem</span>
              </div>
              <div class="bg-white border border-slate-200 text-slate-800 text-xs p-3.5 rounded-2xl rounded-tl-xs shadow-xs leading-relaxed">
                Siap. Selamat datang di saluran komunikasi dinas SISFOPERSKC. Silakan ajukan pertanyaan seputar pengisian formulir, persyaratan berkas, atau informasi rekrutmen. Anda dapat melampirkan berkas foto atau dokumen resmi hingga total 15MB.
              </div>
            </div>
          </div>

          <!-- Daftar Pesan Riil -->
          <template v-for="msg in messages" :key="msg.id">
            <!-- Pesan Dari Personel (Kanan) -->
            <div v-if="msg.sender_type === 'PERSONEL'" class="flex justify-end">
              <div class="max-w-[85%] space-y-1 text-right">
                <div class="flex items-center justify-end gap-2">
                  <span class="text-[10px] text-slate-400">{{ formatTime(msg.created_at) }}</span>
                  <span class="text-xs font-bold text-blue-700">Anda</span>
                </div>
                <div class="bg-blue-600 text-white text-xs p-3.5 rounded-2xl rounded-tr-xs shadow-md text-left leading-relaxed">
                  <p v-if="msg.message" class="whitespace-pre-wrap">{{ msg.message }}</p>

                  <!-- Lampiran File Personel -->
                  <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-2.5 pt-2 border-t border-blue-500/50 space-y-2">
                    <p class="text-[10px] font-bold text-blue-100 uppercase tracking-wider">Lampiran Berkas ({{ msg.attachments.length }}):</p>
                    <div class="grid grid-cols-1 gap-1.5">
                      <div 
                        v-for="(att, idx) in msg.attachments" 
                        :key="idx" 
                        class="flex items-center justify-between gap-2 p-2 rounded-xl bg-blue-700/60 border border-blue-400/30 text-[11px]"
                      >
                        <div class="flex items-center gap-2 truncate min-w-0">
                          <svg class="w-4 h-4 text-blue-200 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                          </svg>
                          <span class="truncate text-white font-medium">{{ att.original_name }}</span>
                          <span class="text-[9px] text-blue-200 shrink-0">({{ formatBytes(att.size) }})</span>
                        </div>
                        <a 
                          :href="`/documents/private-stream?path=${encodeURIComponent(att.file_path)}`" 
                          target="_blank" 
                          class="shrink-0 text-white hover:text-blue-100 font-bold underline text-[10px]"
                        >
                          Unduh
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pesan Dari Admin / Petugas (Kiri) -->
            <div v-else class="flex gap-3 max-w-[85%]">
              <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-xs">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

                  <!-- Lampiran File Petugas -->
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
                        <a 
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
          </template>
        </div>
      </div>

      <!-- 4. Area Pratinjau Berkas Unggahan Bulk Sebelum Kirim -->
      <div v-if="stagedFiles.length > 0" class="p-3 bg-slate-100 border-t border-slate-200 shrink-0 max-h-36 overflow-y-auto space-y-2">
        <div class="flex items-center justify-between text-[11px]">
          <span class="font-bold text-slate-700">Lampiran Dipilih ({{ stagedFiles.length }})</span>
          <span :class="totalStagedSize > maxAllowedBytes ? 'text-red-600 font-bold' : 'text-slate-500'">
            Total: {{ formatBytes(totalStagedSize) }} / 15 MB
          </span>
        </div>

        <p v-if="totalStagedSize > maxAllowedBytes" class="text-[11px] text-red-600 font-bold">
          Peringatan: Total ukuran berkas melebihi batas maksimal 15 MB. Silakan kurangi sebagian berkas.
        </p>

        <div class="flex flex-wrap gap-2">
          <div 
            v-for="(f, i) in stagedFiles" 
            :key="i"
            class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white border border-slate-300 text-[11px] shadow-2xs"
          >
            <span class="truncate max-w-[140px] text-slate-700">{{ f.name }}</span>
            <span class="text-[9px] text-slate-400">({{ formatBytes(f.size) }})</span>
            <button 
              @click="removeStagedFile(i)" 
              type="button" 
              class="text-red-500 hover:text-red-700 ml-1 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- 5. Bagian Input Teks & Pengiriman -->
      <form @submit.prevent="submitMessage" class="p-3 sm:p-4 bg-white border-t border-slate-200 shrink-0">
        <div class="flex items-end gap-2">
          <!-- Tombol Pemilihan Berkas Bulk -->
          <label 
            class="p-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-600 hover:text-slate-900 transition cursor-pointer shrink-0 flex items-center justify-center"
            title="Pilih foto atau dokumen (Bulk max 15MB)"
          >
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
            <input 
              type="file" 
              multiple 
              @change="handleFileChange" 
              accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt" 
              class="hidden" 
              ref="fileInputRef"
            />
          </label>

          <!-- Input Pesan Teks -->
          <div class="flex-1 min-w-0 relative">
            <textarea 
              v-model="inputMessage" 
              @keydown.enter.exact.prevent="submitMessage"
              rows="1" 
              placeholder="Ketik pertanyaan atau informasi..." 
              class="w-full text-xs sm:text-sm px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-2xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent resize-none max-h-28"
            ></textarea>
          </div>

          <!-- Tombol Kirim Pesan -->
          <button 
            type="submit" 
            :disabled="isSending || totalStagedSize > maxAllowedBytes || (!inputMessage.trim() && stagedFiles.length === 0)"
            class="px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-blue-600/20 transition cursor-pointer shrink-0"
          >
            <span v-if="!isSending">Kirim</span>
            <span v-else>Mengirim...</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});

const thread = ref(null);
const messages = ref([]);
const isLoading = ref(false);
const isSending = ref(false);
const inputMessage = ref('');
const stagedFiles = ref([]);
const fileInputRef = ref(null);
const messageContainer = ref(null);

const maxAllowedBytes = 15 * 1024 * 1024; // Maksimal 15 MB

const totalStagedSize = computed(() => {
  return stagedFiles.value.reduce((sum, file) => sum + (file.size || 0), 0);
});

let pollingTimer = null;

// Format ukuran berkas (KB / MB)
const formatBytes = (bytes) => {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

// Format jam pesan (HH:mm WIB)
const formatTime = (isoString) => {
  if (!isoString) return '';
  const d = new Date(isoString);
  const h = String(d.getHours()).padStart(2, '0');
  const m = String(d.getMinutes()).padStart(2, '0');
  return `${h}:${m} WIB`;
};

const scrollToBottom = async () => {
  await nextTick();
  if (messageContainer.value) {
    messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
  }
};

// Inisialisasi atau ambil utas obrolan aktif personel
const fetchThread = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(route('personel.chat.thread'));
    thread.value = res.data.thread;
    messages.value = res.data.messages || [];
    scrollToBottom();
  } catch (err) {
    console.error('Gagal memuat utas obrolan:', err);
  } finally {
    isLoading.value = false;
  }
};

// Pengambilan pesan baru secara asinkron (Polling tanpa penyegaran laman)
const pollMessages = async () => {
  if (!thread.value || !thread.value.uuid) return;
  const lastMsg = messages.value[messages.value.length - 1];
  const lastId = lastMsg ? lastMsg.id : 0;

  try {
    const res = await axios.get(route('personel.chat.messages', thread.value.uuid), {
      params: { last_id: lastId },
    });

    if (res.data.status) {
      thread.value.status = res.data.status;
    }

    if (res.data.messages && res.data.messages.length > 0) {
      messages.value.push(...res.data.messages);
      scrollToBottom();
    }
  } catch (err) {
    console.error('Pembaruan pesan terhambat:', err);
  }
};

const startPolling = () => {
  stopPolling();
  pollingTimer = setInterval(() => {
    pollMessages();
  }, 3500); // Polling asinkron teratur setiap 3.5 detik
};

const stopPolling = () => {
  if (pollingTimer) {
    clearInterval(pollingTimer);
    pollingTimer = null;
  }
};

// Pengelolaan berkas lampiran bulk
const handleFileChange = (e) => {
  const selected = Array.from(e.target.files || []);
  if (!selected.length) return;

  const currentCount = stagedFiles.value.length;
  stagedFiles.value.push(...selected);

  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const removeStagedFile = (index) => {
  stagedFiles.value.splice(index, 1);
};

// Pengiriman pesan dan berkas
const submitMessage = async () => {
  if (isSending.value) return;
  const msgText = inputMessage.value.trim();

  if (!msgText && stagedFiles.value.length === 0) return;

  if (totalStagedSize.value > maxAllowedBytes) {
    Swal.fire({
      icon: 'error',
      title: 'Ukuran Terlalu Besar',
      text: 'Total seluruh lampiran berkas tidak boleh melebihi 15 MB.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
    return;
  }

  if (!thread.value || !thread.value.uuid) {
    await fetchThread();
  }

  isSending.value = true;
  const formData = new FormData();
  if (msgText) {
    formData.append('message', msgText);
  }

  stagedFiles.value.forEach((file) => {
    formData.append('attachments[]', file);
  });

  try {
    const res = await axios.post(route('personel.chat.send', thread.value.uuid), formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (res.data.message) {
      messages.value.push(res.data.message);
      inputMessage.value = '';
      stagedFiles.value = [];
      scrollToBottom();
    }
  } catch (err) {
    const errorMsg = err.response?.data?.error || 'Gagal mengirimkan pesan. Silakan periksa koneksi Anda.';
    Swal.fire({
      icon: 'error',
      title: 'Pesan Gagal Terkirim',
      text: errorMsg,
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  } finally {
    isSending.value = false;
  }
};

const close = () => {
  isOpen.value = false;
};

watch(isOpen, (newVal) => {
  if (newVal) {
    fetchThread().then(() => {
      startPolling();
    });
  } else {
    stopPolling();
  }
});

onMounted(() => {
  if (isOpen.value) {
    fetchThread().then(() => {
      startPolling();
    });
  }
});

onUnmounted(() => {
  stopPolling();
});
</script>
