<template>
  <AuthenticatedLayout>
    <template #header-title>Registrasi Personel Baru Terpusat</template>

    <div class="max-w-4xl bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40 flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-slate-800">Tambah Anggota Komcad Baru</h3>
          <p class="text-xs text-slate-400 mt-0.5">Daftarkan personel baru secara langsung beserta penetapan kredensial dan peran akun.</p>
        </div>
        <Link :href="route('admin.personel.index')" class="text-xs font-bold text-[#2563EB] hover:underline transition">
          Kembali ke Daftar
        </Link>
      </div>

      <div class="p-8">
        <form @submit.prevent="submitCreate" class="space-y-6 max-w-3xl">
          
          <!-- KARTU UTAMA: LOOKUP NIKC SKEP OTOMATIS -->
          <div class="bg-gradient-to-r from-blue-50/80 via-slate-50 to-indigo-50/80 border border-blue-200/80 rounded-2xl p-6 space-y-4 shadow-xs">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-2">
                  <span>🔍</span> Tarik Data SKEP Otomatis via NIKC
                </h4>
                <p class="text-[11px] text-slate-500 mt-0.5">
                  Masukkan NIKC (Nomor Induk Komcad) untuk mengisi Nama, Tanggal Lahir, Matra, Pangkat, dan Angkatan secara otomatis dari SKEP.
                </p>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
              <div class="flex-1 relative">
                <input
                  type="text"
                  v-model="form.nikc"
                  @blur="handleNikcLookup"
                  @keyup.enter.prevent="handleNikcLookup"
                  class="w-full px-4 py-2.5 bg-white border border-blue-300 rounded-xl text-sm font-mono font-bold tracking-wider text-slate-800 outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 transition"
                  placeholder="Masukkan 17-Digit NIKC (Opsional)..."
                />
              </div>
              <button
                type="button"
                @click="handleNikcLookup"
                :disabled="searchingSkep || !form.nikc"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 cursor-pointer shrink-0"
              >
                <svg v-if="searchingSkep" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ searchingSkep ? 'Mencari...' : 'Cari Data SKEP' }}</span>
              </button>
            </div>

            <!-- STATUS BANNER LOOKUP -->
            <div
              v-if="skepAlert"
              :class="[
                skepAlert.type === 'success' ? 'bg-emerald-50 border-emerald-300 text-emerald-800' :
                skepAlert.type === 'warning' ? 'bg-amber-50 border-amber-300 text-amber-800' :
                'bg-blue-50 border-blue-300 text-blue-800',
                'p-3.5 rounded-xl border text-xs font-semibold flex items-center justify-between transition animate-slide-up'
              ]"
            >
              <span>{{ skepAlert.message }}</span>
              <button type="button" @click="skepAlert = null" class="text-xs font-bold opacity-60 hover:opacity-100 ml-2">✕</button>
            </div>
          </div>

          <!-- ISIAN DATA UTAMA -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Lengkap Personel</label>
              <input type="text" v-model="form.full_name" :class="form.errors.full_name ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.full_name" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.full_name }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor Induk Kependudukan (NIK)</label>
              <input type="text" v-model="form.nik" maxlength="16" :class="form.errors.nik ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.nik" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.nik }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Alamat Email Sistem</label>
              <input type="email" v-model="form.email" :class="form.errors.email ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.email" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.email }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor WhatsApp Aktif</label>
              <input type="text" v-model="form.phone_number" :class="form.errors.phone_number ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.phone_number" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.phone_number }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kata Sandi Akun</label>
              <input type="password" v-model="form.password" :class="form.errors.password ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.password" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.password }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Peran Akun</label>
              <select v-model="form.role" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium" required>
                <option value="personel">Anggota Komcad</option>
                <option value="kordinator_angkatan">Koordinator Angkatan</option>
                <option value="kordinator_matra">Koordinator Matra</option>
                <option value="admin">Administrator Sistem</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pangkat</label>
              <select v-model="form.pangkat" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium" required>
                <option v-for="pangkat in pangkatOptions" :key="pangkat.nama" :value="pangkat.nama">{{ pangkat.nama }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Matra</label>
              <select v-model="form.matra" class="w-full px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] text-slate-700 font-medium" required>
                <option value="AD">TNI AD</option>
                <option value="AL">TNI AL</option>
                <option value="AU">TNI AU</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tahun Angkatan</label>
              <input type="text" v-model="form.angkatan" maxlength="4" :class="form.errors.angkatan ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.angkatan" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.angkatan }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tanggal Lahir</label>
              <input type="date" v-model="form.dob" :class="form.errors.dob ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition" required />
              <div v-if="form.errors.dob" class="text-red-500 text-[11px] mt-1.5 font-semibold">Error: {{ form.errors.dob }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Provinsi</label>
              <select v-model="form.province" :class="form.errors.province ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border bg-white rounded-xl text-sm outline-none text-slate-700 font-medium" required>
                <option value="">Pilih Provinsi</option>
                <option v-for="province in provinceOptions" :key="province.kode_latsarmil" :value="province.nama">{{ province.nama }}</option>
              </select>
              <div v-if="form.errors.province" class="text-red-500 text-[11px] mt-1.5 font-semibold">Error: {{ form.errors.province }}</div>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor Induk Komcad (NIKC)</label>
              <input type="text" v-model="form.nikc" :class="form.errors.nikc ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-xl text-sm outline-none transition font-mono font-semibold" placeholder="Belum Terbit (-)" />
              <div v-if="form.errors.nikc" class="text-red-500 text-[11px] mt-1.5 font-semibold">⚠️ {{ form.errors.nikc }}</div>
            </div>
          </div>

          <div class="pt-4 border-t border-[#E2E8F0]">
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Upload Pasfoto Profil (Wajib)</label>
            <div class="flex items-center gap-4">
              <div class="w-12 h-16 bg-slate-100 border border-[#E2E8F0] rounded-xl overflow-hidden shrink-0 shadow-xs">
                <img :src="photoPreview || `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" class="w-full h-full object-cover" alt="Pasfoto Personel" />
              </div>
              <input type="file" @change="handlePhotoChange" :class="form.errors.photo_profile ? 'text-red-500' : ''" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 cursor-pointer" accept="image/*" required />
            </div>
            <div v-if="form.errors.photo_profile" class="text-red-500 text-[11px] mt-2 font-semibold">⚠️ {{ form.errors.photo_profile }}</div>
          </div>

          <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
            <button type="submit" :disabled="form.processing" class="py-2.5 px-6 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-60 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/10 transition cursor-pointer">
              Daftarkan Anggota Baru
            </button>
          </div>

        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useSwal } from '@/Composables/useSwal';
import axios from 'axios';

const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();
const photoPreview = ref(null);
const searchingSkep = ref(false);
const skepAlert = ref(null);

const props = defineProps({
  pangkatOptions: {
    type: Array,
    default: () => []
  },
  provinceOptions: {
    type: Array,
    default: () => []
  }
});
const pangkatOptions = props.pangkatOptions || [];
const provinceOptions = props.provinceOptions || [];

const form = useForm({
  full_name: '',
  nik: '',
  email: '',
  phone_number: '',
  password: '',
  role: 'personel',
  matra: 'AD',
  angkatan: new Date().getFullYear().toString(),
  pangkat: 'Prada KC',
  dob: '',
  province: '',
  nikc: '',
  photo_profile: null,
});

const handleNikcLookup = async () => {
  if (!form.nikc || form.nikc.trim().length < 10) return;
  searchingSkep.value = true;
  skepAlert.value = null;

  try {
    const res = await axios.get(route('admin.personel.lookup-skep'), {
      params: { nikc: form.nikc.trim() }
    });

    if (res.data.found) {
      const data = res.data.data;
      if (data.nama_lengkap) form.full_name = data.nama_lengkap;
      if (data.dob) form.dob = data.dob;
      if (data.matra) form.matra = data.matra;
      if (data.angkatan) form.angkatan = data.angkatan;
      if (data.pangkat) {
        const matched = pangkatOptions.find(p => p.nama.toLowerCase() === data.pangkat.toLowerCase() || p.nama.toLowerCase().includes(data.pangkat.toLowerCase()));
        if (matched) {
          form.pangkat = matched.nama;
        } else {
          form.pangkat = data.pangkat;
        }
      }
      skepAlert.value = {
        type: res.data.already_has_account ? 'warning' : 'success',
        message: res.data.message
      };
    } else {
      skepAlert.value = {
        type: 'info',
        message: res.data.message
      };
    }
  } catch (err) {
    skepAlert.value = {
      type: 'info',
      message: 'ℹ️ NIKC belum terdaftar di SKEP. Silakan isi data personel secara manual.'
    };
  } finally {
    searchingSkep.value = false;
  }
};

const handlePhotoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.photo_profile = file;
    photoPreview.value = URL.createObjectURL(file);
  }
};

const submitCreate = () => {
  form.post(route('admin.personel.store'), {
    forceFormData: true,
    preserveScroll: true,
    onBefore: () => showLoadingProgress('Mendaftarkan Anggota Baru...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Pendaftaran Berhasil', 'Data Personel Baru Telah Tercatat.');
    },
    onError: (errors) => {
      closeLoading();
      const firstErrorKey = Object.keys(errors)[0];
      const errorMessage = errors[firstErrorKey] || 'Harap periksa kelengkapan field parameter inputan.';
      alertError('Gagal Menyimpan', errorMessage);
    }
  });
};
</script>
