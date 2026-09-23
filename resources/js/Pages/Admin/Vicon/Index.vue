<template>
  <AuthenticatedLayout title="Pusat Rapat Dinas & Vicon Terpadu">
    <div class="space-y-6 pb-12">
      <!-- HEADER UTAMA -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-200">
              Agora RTC Enterprise
            </span>
            <span class="text-xs text-slate-400 font-medium">&bull; Jalur Satelit Terenkripsi</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
            Pusat Vicon & Rapat Dinas
          </h1>
          <p class="text-xs text-slate-500">
            Fasilitas konferensi video multi-partisipan untuk koordinasi internal Personel Komcad dan undangan Tamu Luar.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <button 
            @click="openCreateModal"
            type="button" 
            class="inline-flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-2xl shadow-md shadow-blue-500/20 transition cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Inisiasi Rapat Baru</span>
          </button>
        </div>
      </div>

      <!-- 1. RAPAT AKTIF SAAT INI (SEDANG BERLANGSUNG) -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Sesi Rapat Sedang Berlangsung ({{ activeRooms.length }})
          </h2>
        </div>

        <div v-if="activeRooms.length === 0" class="bg-white rounded-3xl border border-slate-200/80 p-8 text-center space-y-2 shadow-xs">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
          <p class="text-xs font-bold text-slate-700">Tidak Ada Sesi Rapat Aktif</p>
          <p class="text-[11px] text-slate-400 max-w-sm mx-auto">
            Seluruh jalur konferensi sedang hening. Silakan klik tombol di atas untuk memulai sesi rapat baru.
          </p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div 
            v-for="room in activeRooms" 
            :key="room.id"
            class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4 hover:border-blue-400 transition group"
          >
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                  AKTIF BERLANGSUNG
                </span>
                <span class="text-[10px] font-mono text-slate-400 font-bold tracking-wider">
                  {{ room.room_code }}
                </span>
              </div>

              <div>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition line-clamp-1">
                  {{ room.title }}
                </h3>
                <p v-if="room.description" class="text-xs text-slate-500 line-clamp-2 mt-1">
                  {{ room.description }}
                </p>
              </div>

              <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                <div>
                  <span class="text-slate-400">Host:</span>
                  <span class="font-bold text-slate-700 ml-1">{{ room.host?.name || 'Pengelola Dinas' }}</span>
                </div>
                <div>
                  <span class="text-slate-400">Peserta:</span>
                  <span class="font-bold text-slate-700 ml-1">{{ room.participants?.length || 1 }} orang</span>
                </div>
              </div>
            </div>

            <div class="space-y-2 pt-2">
              <Link 
                :href="route('admin.vicon.room', room.uuid)"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>Masuk Ruang Rapat</span>
              </Link>

              <div class="grid grid-cols-2 gap-2">
                <button 
                  @click="copyGuestLink(room.room_code)"
                  type="button" 
                  class="py-2 px-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-semibold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
                  title="Salin Tautan untuk Tamu / Orang Luar (Tanpa Login)"
                >
                  <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                  </svg>
                  <span>Link Tamu Luar</span>
                </button>

                <button 
                  @click="copyPersonelLink(room.uuid)"
                  type="button" 
                  class="py-2 px-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-semibold rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer"
                  title="Salin Tautan untuk Personel Komcad"
                >
                  <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>Link Personel</span>
                </button>
              </div>

              <button 
                @click="confirmEndRoom(room)"
                type="button" 
                class="w-full py-2 px-3 text-red-600 hover:bg-red-50 text-[11px] font-semibold rounded-xl transition flex items-center justify-center gap-1 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Akhiri Sesi untuk Semua</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. RAPAT TERJADWAL -->
      <div v-if="scheduledRooms.length > 0" class="space-y-4 pt-4">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
          Rapat Terjadwal ({{ scheduledRooms.length }})
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div 
            v-for="room in scheduledRooms" 
            :key="room.id"
            class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4"
          >
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                  TERJADWAL
                </span>
                <span class="text-[10px] font-mono text-slate-400 font-bold">
                  {{ room.room_code }}
                </span>
              </div>

              <div>
                <h3 class="text-sm font-bold text-slate-900">
                  {{ room.title }}
                </h3>
                <p class="text-xs text-slate-500 mt-1">
                  Waktu: {{ formatDateTime(room.scheduled_at) }}
                </p>
              </div>

              <div class="text-[11px] text-slate-500">
                <span>Undangan: {{ room.participants?.length || 0 }} personel</span>
              </div>
            </div>

            <div class="space-y-2 pt-2">
              <Link 
                :href="route('admin.vicon.room', room.uuid)"
                class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2"
              >
                <span>Mulai Rapat Sekarang</span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. RIWAYAT RAPAT SELESAI -->
      <div class="space-y-4 pt-4">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Riwayat Konferensi Selesai
        </h2>

        <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-xs">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
              <thead class="bg-slate-50/80 border-b border-slate-200/80 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                <tr>
                  <th class="px-5 py-3.5">Judul Rapat</th>
                  <th class="px-5 py-3.5">Kode Ruang</th>
                  <th class="px-5 py-3.5">Host Pelaksana</th>
                  <th class="px-5 py-3.5">Waktu Mulai</th>
                  <th class="px-5 py-3.5">Waktu Selesai</th>
                  <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="pastRooms.data.length === 0">
                  <td colspan="6" class="px-5 py-8 text-center text-slate-400 italic">
                    Belum ada riwayat rapat dinas yang tersimpan.
                  </td>
                </tr>
                <tr v-for="past in pastRooms.data" :key="past.id" class="hover:bg-slate-50/50 transition">
                  <td class="px-5 py-3.5 font-bold text-slate-900">
                    {{ past.title }}
                  </td>
                  <td class="px-5 py-3.5 font-mono text-slate-500">
                    {{ past.room_code }}
                  </td>
                  <td class="px-5 py-3.5">
                    {{ past.host?.name || 'Admin' }}
                  </td>
                  <td class="px-5 py-3.5 text-slate-500">
                    {{ formatDateTime(past.started_at) }}
                  </td>
                  <td class="px-5 py-3.5 text-slate-500">
                    {{ formatDateTime(past.ended_at) }}
                  </td>
                  <td class="px-5 py-3.5 text-right">
                    <button 
                      @click="deletePastRoom(past)" 
                      class="text-red-500 hover:text-red-700 font-bold text-[11px] cursor-pointer"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL PEMBUATAN RAPAT DINAS BARU -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs p-4 overflow-y-auto">
      <div class="w-full max-w-xl bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5 my-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div class="space-y-0.5">
            <h3 class="text-base font-black text-slate-900">Inisiasi Rapat Dinas Baru</h3>
            <p class="text-xs text-slate-500">Atur parameter konferensi video, akses tamu, dan daftar personel yang diundang.</p>
          </div>
          <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitCreateRoom" class="space-y-4">
          <!-- Judul Rapat -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 uppercase">Judul Rapat Dinas <span class="text-red-500">*</span></label>
            <input 
              v-model="createForm.title" 
              type="text" 
              required
              placeholder="Contoh: Rapat Koordinasi Kesiapsiagaan Triwulan III" 
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
            />
          </div>

          <!-- Agenda / Keterangan -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700 uppercase">Agenda / Catatan Rapat (Opsional)</label>
            <textarea 
              v-model="createForm.description" 
              rows="2"
              placeholder="Tuliskan pokok bahasan atau agenda rapat..."
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition"
            ></textarea>
          </div>

          <!-- Opsi Jadwal -->
          <div class="grid grid-cols-2 gap-3 pt-1">
            <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition" :class="!createForm.is_scheduled ? 'border-blue-600 bg-blue-50/40 text-blue-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
              <input type="radio" :value="false" v-model="createForm.is_scheduled" class="text-blue-600" />
              <div class="text-xs">
                <div>Mulai Sekarang</div>
                <div class="text-[10px] text-slate-400 font-normal">Sesi langsung aktif seketika</div>
              </div>
            </label>

            <label class="flex items-center gap-3 p-3 rounded-2xl border cursor-pointer transition" :class="createForm.is_scheduled ? 'border-blue-600 bg-blue-50/40 text-blue-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
              <input type="radio" :value="true" v-model="createForm.is_scheduled" class="text-blue-600" />
              <div class="text-xs">
                <div>Jadwalkan Rapat</div>
                <div class="text-[10px] text-slate-400 font-normal">Tentukan waktu pelaksanaannya</div>
              </div>
            </label>
          </div>

          <div v-if="createForm.is_scheduled" class="space-y-1 pt-1">
            <label class="block text-xs font-bold text-slate-700 uppercase">Waktu Mulai Rapat <span class="text-red-500">*</span></label>
            <input 
              v-model="createForm.scheduled_at" 
              type="datetime-local" 
              required
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-600"
            />
          </div>

          <!-- Pengaturan Tamu Luar (Orang Luar) -->
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-bold text-slate-800">Izinkan Tamu Luar (Orang Luar)</p>
                <p class="text-[11px] text-slate-500">Tamu luar dapat bergabung melalui tautan tanpa akun SISFOPERS KC.</p>
              </div>
              <input 
                type="checkbox" 
                v-model="createForm.allow_guest" 
                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 cursor-pointer"
              />
            </div>

            <div v-if="createForm.allow_guest" class="pt-2 border-t border-slate-200/80 space-y-1">
              <label class="block text-[11px] font-semibold text-slate-600">Kata Sandi Ruang (Opsional)</label>
              <input 
                v-model="createForm.guest_passcode" 
                type="text" 
                placeholder="Kosongkan jika bebas tanpa sandi"
                class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-mono"
              />
            </div>
          </div>

          <!-- Pemilihan Undangan Personel Komcad -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 uppercase">
                Undang Personel Komcad ({{ selectedPersonelIds.length }} Terpilih)
              </label>
              <span class="text-[10px] text-slate-400">Pilih dari database</span>
            </div>

            <input 
              v-model="personelSearchQuery" 
              type="text" 
              placeholder="Cari nama personel, pangkat, atau NIKC..." 
              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"
            />

            <!-- Daftar Personel Scrollable -->
            <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-2xl divide-y divide-slate-100 bg-white p-1">
              <div 
                v-for="pers in filteredPersonels" 
                :key="pers.id"
                @click="toggleSelectPersonel(pers.id)"
                class="p-2 flex items-center justify-between hover:bg-blue-50/50 rounded-xl cursor-pointer transition text-xs"
              >
                <div class="flex items-center gap-2.5">
                  <input 
                    type="checkbox" 
                    :checked="selectedPersonelIds.includes(pers.id)" 
                    class="rounded text-blue-600 pointer-events-none"
                  />
                  <div>
                    <span class="font-bold text-slate-800">{{ pers.pangkat }} {{ pers.full_name }}</span>
                    <span class="text-[10px] text-slate-400 ml-1.5">&bull; NIKC: {{ pers.nikc || '-' }} &bull; {{ pers.matra || '' }}</span>
                  </div>
                </div>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 font-medium text-slate-500">
                  {{ pers.label || pers.kompi || 'Komcad' }}
                </span>
              </div>
              <div v-if="filteredPersonels.length === 0" class="p-4 text-center text-xs text-slate-400">
                Personel tidak ditemukan dengan kata kunci tersebut.
              </div>
            </div>
          </div>

          <!-- Tombol Aksi -->
          <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <button 
              @click="showCreateModal = false" 
              type="button" 
              class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
              <span>{{ createForm.is_scheduled ? 'Simpan Jadwal Rapat' : 'Mulai Rapat Langsung' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
  activeRooms: { type: Array, default: () => [] },
  scheduledRooms: { type: Array, default: () => [] },
  pastRooms: { type: Object, default: () => ({ data: [] }) },
  personels: { type: Array, default: () => [] },
  currentRole: { type: String, default: 'admin' },
});

const showCreateModal = ref(false);
const isSubmitting = ref(false);
const personelSearchQuery = ref('');
const selectedPersonelIds = ref([]);

const createForm = ref({
  title: '',
  description: '',
  is_scheduled: false,
  scheduled_at: '',
  allow_guest: true,
  guest_passcode: '',
});

const openCreateModal = () => {
  createForm.value = {
    title: '',
    description: '',
    is_scheduled: false,
    scheduled_at: '',
    allow_guest: true,
    guest_passcode: '',
  };
  selectedPersonelIds.value = [];
  personelSearchQuery.value = '';
  showCreateModal.value = true;
};

const filteredPersonels = computed(() => {
  const q = personelSearchQuery.value.toLowerCase().trim();
  if (!q) return props.personels.slice(0, 50);
  return props.personels.filter(p => 
    (p.full_name && p.full_name.toLowerCase().includes(q)) ||
    (p.nikc && p.nikc.toLowerCase().includes(q)) ||
    (p.matra && p.matra.toLowerCase().includes(q))
  ).slice(0, 50);
});

const toggleSelectPersonel = (id) => {
  const idx = selectedPersonelIds.value.indexOf(id);
  if (idx === -1) {
    selectedPersonelIds.value.push(id);
  } else {
    selectedPersonelIds.value.splice(idx, 1);
  }
};

const submitCreateRoom = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const payload = {
      ...createForm.value,
      invited_personel_ids: selectedPersonelIds.value,
    };

    const res = await axios.post('/admin/vicon', payload);
    if (res.data?.redirect) {
      window.location.href = res.data.redirect;
    } else {
      showCreateModal.value = false;
      router.reload();
    }
  } catch (err) {
    const msg = err.response?.data?.error || err.response?.data?.message || 'Gagal menginisiasi ruang rapat dinas.';
    Swal.fire({
      icon: 'error',
      title: 'Inisiasi Gagal',
      text: msg,
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  } finally {
    isSubmitting.value = false;
  }
};

const copyGuestLink = (roomCode) => {
  const url = `${window.location.origin}/vicon/join/${roomCode}`;
  navigator.clipboard.writeText(url);
  Swal.fire({
    icon: 'success',
    title: 'Tautan Tamu Luar Disalin',
    text: 'Tautan dapat langsung dibagikan kepada pihak eksternal/tamu tanpa perlu memiliki akun SISFOPERS KC.',
    confirmButtonColor: '#2563EB',
    timer: 3000,
    customClass: { popup: 'rounded-2xl' },
  });
};

const copyPersonelLink = (uuid) => {
  const url = `${window.location.origin}/personel/vicon/${uuid}`;
  navigator.clipboard.writeText(url);
  Swal.fire({
    icon: 'success',
    title: 'Tautan Personel Disalin',
    text: 'Tautan ruang rapat khusus Personel Komcad berhasil disalin.',
    confirmButtonColor: '#2563EB',
    timer: 2500,
    customClass: { popup: 'rounded-2xl' },
  });
};

const confirmEndRoom = (room) => {
  Swal.fire({
    icon: 'warning',
    title: 'Akhiri Sesi Rapat?',
    text: `Apakah Anda yakin ingin mengakhiri sesi "${room.title}" untuk seluruh partisipan? Seluruh koneksi video akan ditutup.`,
    showCancelButton: true,
    confirmButtonText: 'Ya, Akhiri Rapat',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#DC2626',
    customClass: { popup: 'rounded-2xl' },
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await axios.post(`/admin/vicon/${room.uuid}/end`);
        Swal.fire({
          icon: 'success',
          title: 'Rapat Telah Berakhir',
          text: 'Sesi rapat berhasil ditutup secara resmi.',
          confirmButtonColor: '#2563EB',
          timer: 2000,
          customClass: { popup: 'rounded-2xl' },
        });
        router.reload();
      } catch (err) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal Mengakhiri',
          text: err.response?.data?.error || 'Gagal mengakhiri sesi rapat.',
          confirmButtonColor: '#2563EB',
        });
      }
    }
  });
};

const deletePastRoom = (room) => {
  Swal.fire({
    icon: 'warning',
    title: 'Hapus Catatan Rapat?',
    text: `Hapus arsip rapat "${room.title}" secara permanen?`,
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    confirmButtonColor: '#DC2626',
    customClass: { popup: 'rounded-2xl' },
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/admin/vicon/${room.uuid}`);
    }
  });
};

const formatDateTime = (dt) => {
  if (!dt) return '-';
  const d = new Date(dt);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
