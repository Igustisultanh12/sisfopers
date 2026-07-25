<template>
  <AuthenticatedLayout>
    <template #header-title>Manajemen Pusat Akun</template>

    <!-- KARTU 1: DATA IDENTITAS PERSONAL & FOTO PROFIL -->
    <div class="max-w-4xl bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden mb-8">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40">
        <h3 class="text-base font-bold text-slate-800">Profil Pengguna</h3>
        <p class="text-xs text-slate-400 mt-0.5">Kelola data informasi personal, foto profil instansi, dan kontak operasional.</p>
      </div>

      <div class="p-8 space-y-8">
        <!-- Komponen Upload & Preview Avatar Interaktif ROMEI Style -->
        <div class="flex items-center gap-6 pb-6 border-b border-[#E2E8F0]">
          <div class="relative group">
            <div class="w-20 h-20 rounded-2xl bg-[#2563EB]/10 border border-[#E2E8F0] flex items-center justify-center font-extrabold text-2xl text-[#2563EB] uppercase overflow-hidden shadow-sm">
              <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
              <span v-else>{{ form.username.substring(0, 2) }}</span>
            </div>
            <label class="absolute -bottom-1 -right-1 bg-white border border-[#E2E8F0] p-1.5 rounded-lg shadow-sm cursor-pointer hover:bg-slate-50 transition">
              <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"></path>
                <circle cx="12" cy="13" r="3"></circle>
              </svg>
              <input type="file" @change="handleAvatarChange" class="hidden" accept="image/*" />
            </label>
          </div>
          <div>
            <h4 class="text-base font-bold text-slate-800">{{ user.username }}</h4>
            <p class="text-xs text-[#2563EB] font-mono mt-0.5">ID Pengguna: #000{{ user.id }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Terdaftar Sejak: {{ user.created_at }}</p>
          </div>
        </div>

        <!-- Form Manipulasi Identitas Pokok -->
        <form @submit.prevent="submitProfileUpdate" class="space-y-6 max-w-2xl">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Akun (Username)</label>
              <input type="text" v-model="form.username" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Alamat Surat Elektronik (Email)</label>
              <input type="email" v-model="form.email" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor Telepon / WhatsApp Operasional</label>
              <input type="text" v-model="form.phone_number" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="Masukkan nomor aktif WhatsApp..." />
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
            <button type="submit" :disabled="form.processing" class="py-2 px-5 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-60 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/10 transition cursor-pointer">
              Simpan Perubahan Profil
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- KARTU 2: ENKRIPSI KEAMANAN KATA SANDI BARU -->
    <div class="max-w-4xl bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden mb-8">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40">
        <h3 class="text-base font-bold text-slate-800">Enkripsi Keamanan Sandi</h3>
        <p class="text-xs text-slate-400 mt-0.5">Perbarui kata sandi gerbang masuk Anda secara berkala demi integritas akun data operasional.</p>
      </div>

      <div class="p-8">
        <form @submit.prevent="submitPasswordUpdate" class="space-y-6 max-w-2xl">
          <div class="space-y-5">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kata Sandi Saat Ini</label>
              <input type="password" v-model="passwordForm.current_password" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kata Sandi Baru</label>
              <input type="password" v-model="passwordForm.password" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Konfirmasi Kata Sandi Baru</label>
              <input type="password" v-model="passwordForm.password_confirmation" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
            <button type="submit" :disabled="passwordForm.processing" class="py-2 px-5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-sm transition cursor-pointer">
              Perbarui Kata Sandi
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- KARTU 3: KEAMANAN DUA FAKTOR (M2FA / GOOGLE AUTHENTICATOR) -->
    <div class="max-w-4xl bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden mb-8">
      <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/40">
        <h3 class="text-base font-bold text-slate-800">Keamanan Otentikasi Dua Faktor (M2FA)</h3>
        <p class="text-xs text-slate-400 mt-0.5">Gunakan lapisan perlindungan ekstra kriptografi Token OTP dari Google Authenticator saat masuk sistem.</p>
      </div>

      <div class="p-8 flex items-center justify-between gap-6 max-w-2xl">
        <div class="space-y-1">
          <h4 class="text-sm font-bold text-slate-700">Status Aktivasi Token Google Auth</h4>
          <p class="text-xs" :class="user.google2fa_enabled ? 'text-green-600 font-semibold' : 'text-slate-400'">
            {{ user.google2fa_enabled ? 'Aktif Terlindungi (M2FA Secure)' : 'Saat ini fitur pengamanan M2FA belum diaktifkan.' }}
          </p>
        </div>
        
        <button 
          @click="toggleMfaProtection" 
          :class="user.google2fa_enabled ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100' : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100'"
          class="px-5 py-2.5 rounded-xl text-xs font-bold transition cursor-pointer"
        >
          {{ user.google2fa_enabled ? 'Nonaktifkan M2FA' : 'Tautkan M2FA Sekarang' }}
        </button>
      </div>
    </div>

    <!-- MODAL POPUP CONFIGURATION M2FA (ROMEI MODERN STYLING) -->
    <div v-if="showMfaModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl max-w-md w-full p-6 shadow-xl space-y-5">
        <div class="text-center space-y-1">
          <h3 class="text-base font-bold text-slate-800">Konfigurasi Google Authenticator</h3>
          <p class="text-xs text-slate-400">Pindai kode QR di bawah ini menggunakan aplikasi otentikator gadget Anda.</p>
        </div>

        <!-- Box Tampilan Gambar QR Code -->
        <div class="flex flex-col items-center justify-center p-4 bg-slate-50 border border-[#E2E8F0] rounded-2xl space-y-3">
          <img :src="mfaData.qr_image" class="w-44 h-44 object-contain rounded-lg shadow-xs bg-white p-2" />
          <div class="text-center">
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Atau Masukkan Kode Manual:</span>
            <code class="text-xs font-mono font-bold bg-white px-2.5 py-1 rounded border border-[#E2E8F0] mt-1 inline-block text-slate-700 select-all">{{ mfaData.secret }}</code>
          </div>
        </div>

        <!-- Form Pengisian OTP Token -->
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 text-center">Masukkan 6-Digit Kode Otentikasi</label>
            <input 
              type="text" 
              v-model="mfaVerifyForm.code" 
              maxlength="6" 
              class="w-full tracking-[0.5em] text-center font-mono font-bold text-lg px-4 py-2 border border-[#E2E8F0] rounded-xl outline-none focus:border-[#2563EB]" 
              placeholder="000000" 
              required 
            />
            <span v-if="$page.props.errors.mfa_code" class="text-xs text-red-500 block text-center mt-1 font-semibold">
              {{ $page.props.errors.mfa_code }}
            </span>
          </div>
        </div>

        <!-- Tombol Aksi Kontrol -->
        <div class="flex justify-end gap-2.5 pt-2 border-t border-[#E2E8F0]">
          <button @click="showMfaModal = false" class="px-4 py-2 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">Batal</button>
          <button @click="submitMfaVerification" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer">Verifikasi & Aktifkan</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({ 
  user: Object, 
  personel: Object, 
  mfa_setup: Object // Menerima payload kiriman parameter reaktif murni dari controller
});

const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

const avatarPreview = ref(props.user.avatar);
const showMfaModal = ref(false);
const mfaData = ref({ secret: '', qr_image: '' });

// Form Utama Info Akun Menggunakan POST murni untuk handle pengiriman berkas gambar biner
const form = useForm({
  username: props.user.username,
  email: props.user.email,
  phone_number: props.user.phone_number,
  avatar: null,
});

// Form Modul Ganti Password
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

// Form Modul Verifikasi OTP M2FA
const mfaVerifyForm = useForm({
  secret: '',
  code: '',
});

// Watcher untuk mendeteksi perubahan properti mfa_setup hasil re-render Inertia
watch(() => props.mfa_setup, (newVal) => {
  if (newVal && newVal.secret && newVal.qr_image) {
    mfaData.value = newVal;
    mfaVerifyForm.secret = newVal.secret;
    mfaVerifyForm.code = '';
    showMfaModal.value = true;
  }
}, { immediate: true });

const handleAvatarChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
  }
};

const submitProfileUpdate = () => {
  form.post(route('profile.update'), {
    forceFormData: true,
    onBefore: () => showLoadingProgress('Menyinkronkan Perubahan Akun...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Berhasil Diperbarui', 'Data identitas personal pusat akun Anda telah disesuaikan secara riil.');
    },
    onError: () => {
      closeLoading();
      alertError('Gagal Memperbarui', 'Periksa kembali duplikasi nama akun atau ekstensi file avatar Anda.');
    }
  });
};

const submitPasswordUpdate = () => {
  passwordForm.put(route('profile.password.update'), {
    onBefore: () => showLoadingProgress('Mengamankan Enkripsi Sandi...'),
    onSuccess: () => {
      closeLoading();
      passwordForm.reset();
      alertSuccess('Sandi Diperbarui', 'Kata sandi pelindung akun Anda berhasil diganti.');
    },
    onError: (errors) => {
      closeLoading();
      alertError('Gagal Mengubah', errors.current_password || 'Periksa kembali kecocokan entri kata sandi baru Anda.');
    }
  });
};

const toggleMfaProtection = () => {
  if (props.user.google2fa_enabled) {
    router.post(route('profile.mfa'), {}, {
      onBefore: () => showLoadingProgress('Menonaktifkan Parameter Pengaman M2FA...'),
      onSuccess: () => {
        closeLoading();
        alertSuccess('M2FA Dinonaktifkan', 'Proteksi multi-faktor autentikasi akun berhasil dicabut.');
      }
    });
    return;
  }

  // Melakukan post request tanpa interupsi siklus redirect loop 302
  router.post(route('profile.mfa'), {}, {
    onBefore: () => showLoadingProgress('Menyiapkan Enkripsi QR Code Baru...'),
    onSuccess: () => {
      closeLoading();
    },
    onError: () => {
      closeLoading();
      alertError('Gagal Menyiapkan', 'Terjadi gangguan otorisasi engine pengaman.');
    }
  });
};

const submitMfaVerification = () => {
  mfaVerifyForm.post(route('profile.mfa.verify'), {
    preserveState: true,
    onBefore: () => showLoadingProgress('Validasi Token Kriptografi...'),
    onSuccess: () => {
      closeLoading();
      showMfaModal.value = false;
      alertSuccess('Otentikasi Aktif', 'Akun Anda berhasil ditautkan dan dilindungi oleh M2FA.');
    },
    onError: () => {
      closeLoading();
      alertError('Validasi Gagal', 'Kode OTP salah. Sila periksa penunjuk waktu gadget Anda.');
    }
  });
};
</script>