<template>
  <AuthenticatedLayout title="Gabung Vicon Dinas">
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
      <!-- 1. HEADER UTAMA DINAS -->
      <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-200">
              Jalur Satelit Terenkripsi
            </span>
            <span class="text-xs text-slate-400 font-medium">&bull; Agora RTC Enterprise</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
            Gabung Ruang Rapat Dinas (Vicon)
          </h1>
          <p class="text-xs text-slate-500">
            Portal resmi Personel Komponen Cadangan untuk memasuki ruang konferensi video dinas.
          </p>
        </div>

        <!-- Ringkasan Identitas Personel -->
        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200/80 shrink-0">
          <div class="w-10 h-10 rounded-xl bg-blue-600/10 border border-blue-500/20 text-blue-600 flex items-center justify-center font-black text-sm">
            {{ currentPersonel?.matra?.slice(0, 2) || 'KC' }}
          </div>
          <div class="text-left text-xs">
            <p class="font-bold text-slate-800 leading-tight">{{ currentPersonel?.display_name }}</p>
            <p class="text-[10px] text-slate-400 font-mono mt-0.5">NRP: {{ currentPersonel?.nrp || '-' }} &bull; {{ currentPersonel?.matra || 'KOMCAD' }}</p>
          </div>
        </div>
      </div>

      <!-- 2. UNDANGAN RAPAT AKTIF SAAT INI (BILA ADA) -->
      <div v-if="activeRooms.length > 0" class="space-y-3">
        <div class="flex items-center justify-between">
          <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Undangan Rapat Dinas Aktif ({{ activeRooms.length }})
          </h2>
          <span class="text-[11px] text-slate-400">Anda dapat langsung bergabung tanpa kode</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div 
            v-for="room in activeRooms" 
            :key="room.id"
            class="bg-gradient-to-br from-emerald-50/60 to-white rounded-3xl border-2 border-emerald-500/40 p-5 shadow-sm flex flex-col justify-between space-y-4"
          >
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100/80 text-emerald-800 border border-emerald-300">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                  SEDANG BERLANGSUNG
                </span>
                <span class="text-[10px] font-mono font-bold text-slate-500">
                  {{ room.room_code }}
                </span>
              </div>

              <div>
                <h3 class="text-sm font-bold text-slate-900 line-clamp-1">
                  {{ room.title }}
                </h3>
                <p v-if="room.description" class="text-xs text-slate-500 line-clamp-2 mt-0.5">
                  {{ room.description }}
                </p>
              </div>

              <div class="pt-2 border-t border-emerald-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Penyelenggara: <strong class="text-slate-700">{{ room.host?.name || 'Admin Dinas' }}</strong></span>
                <span>Peserta: <strong class="text-slate-700">{{ room.participants?.length || 1 }} orang</strong></span>
              </div>
            </div>

            <Link 
              :href="route('personel.vicon.room', room.uuid)"
              class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <span>Langsung Masuk ke Ruang Rapat</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- 3. FORM UTAMA: GABUNG DENGAN KODE / TAUTAN RAPAT -->
      <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 sm:p-8 space-y-6">
          <div class="border-b border-slate-100 pb-5">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Masuk Sesi Rapat Dinas
            </h2>
            <p class="text-xs text-slate-500 mt-1">
              Masukkan kode ruang rapat dinas atau tautan undangan yang Anda terima dari satuan komando.
            </p>
          </div>

          <form @submit.prevent="submitJoin" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Kolom Kiri: Input Kode & Sandi -->
              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Kode Ruang Rapat Dinas <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="form.room_code"
                    type="text" 
                    required
                    placeholder="Contoh: VICON-KC-XXXXXX"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-sm font-mono font-bold tracking-wider text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:bg-white transition uppercase"
                  />
                  <p class="text-[11px] text-slate-400 mt-1.5">
                    Dapat berupa kode 6 digit ruang rapat atau tautan lengkap yang ditempelkan.
                  </p>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Kata Sandi Ruang Rapat
                  </label>
                  <div class="relative">
                    <input 
                      v-model="form.passcode"
                      :type="showPasscode ? 'text' : 'password'"
                      placeholder="Masukkan sandi jika ruang dikunci"
                      class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:bg-white transition pr-11"
                    />
                    <button 
                      type="button" 
                      @click="showPasscode = !showPasscode"
                      class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                    >
                      <svg v-if="!showPasscode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                      </svg>
                    </button>
                  </div>
                  <p class="text-[11px] text-slate-400 mt-1.5">
                    Kosongkan apabila ruang rapat tidak diproteksi kata sandi oleh host.
                  </p>
                </div>

                <!-- Info Identitas Terdaftar -->
                <div class="p-3.5 bg-blue-50/70 rounded-2xl border border-blue-200/80 flex items-start gap-3">
                  <svg class="w-4 h-4 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  <div class="text-[11px] text-blue-900 leading-relaxed">
                    <p class="font-bold">Identitas Resmi Personel Terverifikasi</p>
                    <p class="text-blue-700 mt-0.5">
                      Anda akan masuk dengan nama dinas: <strong>{{ currentPersonel?.display_name }}</strong>.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Kolom Kanan: Uji Coba Perangkat (Pre-flight Check) -->
              <div class="space-y-3 flex flex-col">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                  Pratinjau Kamera & Perangkat
                </label>
                
                <div class="relative flex-1 min-h-[220px] bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center">
                  <!-- Video Track -->
                  <video 
                    ref="previewVideoRef" 
                    autoplay 
                    playsinline 
                    muted 
                    class="w-full h-full object-cover"
                    :class="{ hidden: !cameraActive }"
                  ></video>

                  <!-- Placeholder Kamera Mati -->
                  <div v-if="!cameraActive" class="text-center space-y-2 p-4">
                    <div class="w-14 h-14 rounded-2xl bg-slate-800 text-slate-500 border border-slate-700 flex items-center justify-center mx-auto">
                      <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-300">Pratinjau Kamera Non-aktif</p>
                    <p class="text-[10px] text-slate-400">Klik tombol di bawah untuk memeriksa kesiapan video</p>
                  </div>

                  <!-- Kontrol Terapung Pratinjau -->
                  <div class="absolute bottom-3 inset-x-0 flex items-center justify-center gap-2 z-10">
                    <button 
                      type="button" 
                      @click="togglePreviewCamera"
                      :class="cameraActive ? 'bg-blue-600 text-white' : 'bg-slate-800/90 text-slate-300 hover:bg-slate-700'"
                      class="px-3 py-1.5 rounded-xl border border-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-md cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      <span>{{ cameraActive ? 'Matikan Kamera' : 'Uji Kamera' }}</span>
                    </button>

                    <button 
                      type="button" 
                      @click="togglePreviewMic"
                      :class="micActive ? 'bg-emerald-600 text-white' : 'bg-slate-800/90 text-slate-300 hover:bg-slate-700'"
                      class="px-3 py-1.5 rounded-xl border border-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-md cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                      </svg>
                      <span>{{ micActive ? 'Mikrofon Siap' : 'Uji Mikrofon' }}</span>
                    </button>
                  </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-400 px-1">
                  <span>Status Kamera: <strong :class="cameraActive ? 'text-emerald-600' : 'text-slate-500'">{{ cameraActive ? 'Aktif' : 'Non-aktif' }}</strong></span>
                  <span>Status Mikrofon: <strong :class="micActive ? 'text-emerald-600' : 'text-slate-500'">{{ micActive ? 'Tersambung' : 'Mati' }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Tombol Aksi Submit -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
              <span class="text-xs text-slate-400">
                Pastikan Anda telah menerima instruksi kode dari komandan/host sebelum bergabung.
              </span>

              <button 
                type="submit" 
                :disabled="isSubmitting || !form.room_code"
                class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-black uppercase tracking-wider rounded-2xl shadow-lg shadow-blue-600/20 transition flex items-center justify-center gap-2.5 cursor-pointer"
              >
                <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>{{ isSubmitting ? 'Memvalidasi Akses...' : 'Masuk Ruang Rapat Dinas' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- 4. PANDUAN TATA TERTIB VICON DINAS -->
      <div class="bg-slate-50 p-6 rounded-3xl border border-slate-200/80 space-y-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Tata Tertib & Ketentuan Koordinasi Virtual Dinas
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600">
          <div class="p-3 bg-white rounded-2xl border border-slate-200/60 space-y-1">
            <p class="font-bold text-slate-800">1. Kerapian Personel</p>
            <p class="text-[11px] text-slate-500 leading-relaxed">
              Kenakan seragam atau pakaian dinas rapi sesuai arahan satuan komando yang berlaku.
            </p>
          </div>
          <div class="p-3 bg-white rounded-2xl border border-slate-200/60 space-y-1">
            <p class="font-bold text-slate-800">2. Disiplin Audio</p>
            <p class="text-[11px] text-slate-500 leading-relaxed">
              Bisukan mikrofon saat pihak lain sedang berbicara dan aktifkan kembali saat diberikan kesempatan.
            </p>
          </div>
          <div class="p-3 bg-white rounded-2xl border border-slate-200/60 space-y-1">
            <p class="font-bold text-slate-800">3. Keamanan Informasi</p>
            <p class="text-[11px] text-slate-500 leading-relaxed">
              Seluruh rekaman dan materi paparan dinas bersifat tertutup dan terenkripsi satelit aman.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  activeRooms: { type: Array, default: () => [] },
  currentPersonel: { type: Object, default: () => ({}) },
});

const form = ref({
  room_code: '',
  passcode: '',
});

const showPasscode = ref(false);
const isSubmitting = ref(false);

const previewVideoRef = ref(null);
const cameraActive = ref(false);
const micActive = ref(false);
let localStream = null;

const togglePreviewCamera = async () => {
  if (cameraActive.value) {
    if (localStream) {
      localStream.getVideoTracks().forEach(t => t.stop());
    }
    cameraActive.value = false;
    return;
  }

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: micActive.value });
    localStream = stream;
    if (previewVideoRef.value) {
      previewVideoRef.value.srcObject = stream;
    }
    cameraActive.value = true;
  } catch (err) {
    Swal.fire({
      icon: 'warning',
      title: 'Kamera Tidak Terdeteksi',
      text: 'Mohon izinkan akses peramban ke kamera untuk menggunakan fitur pratinjau.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  }
};

const togglePreviewMic = async () => {
  if (micActive.value) {
    if (localStream) {
      localStream.getAudioTracks().forEach(t => t.stop());
    }
    micActive.value = false;
    return;
  }

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: cameraActive.value });
    localStream = stream;
    if (cameraActive.value && previewVideoRef.value) {
      previewVideoRef.value.srcObject = stream;
    }
    micActive.value = true;
  } catch (err) {
    Swal.fire({
      icon: 'warning',
      title: 'Mikrofon Tidak Terdeteksi',
      text: 'Mohon izinkan akses peramban ke mikrofon.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  }
};

const submitJoin = () => {
  if (!form.value.room_code) return;

  isSubmitting.value = true;

  // Hentikan pratinjau kamera sebelum navigasi ke ruang vicon utama
  if (localStream) {
    localStream.getTracks().forEach(t => t.stop());
    localStream = null;
  }

  router.post(route('personel.vicon.join'), form.value, {
    onError: (errors) => {
      isSubmitting.value = false;
      const msg = errors.error || Object.values(errors)[0] || 'Gagal memvalidasi ruang rapat dinas.';
      Swal.fire({
        icon: 'error',
        title: 'Gagal Bergabung',
        text: msg,
        confirmButtonColor: '#2563EB',
        customClass: { popup: 'rounded-2xl' },
      });
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
};

onUnmounted(() => {
  if (localStream) {
    localStream.getTracks().forEach(t => t.stop());
    localStream = null;
  }
});
</script>
