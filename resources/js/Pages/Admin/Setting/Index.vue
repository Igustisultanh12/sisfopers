<template>
  <AuthenticatedLayout>
    <template #header-title>Konfigurasi Parameter Sistem</template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Sisi Kiri: Form Pengaturan -->
      <div class="lg:col-span-2 bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#E2E8F0] bg-gradient-to-r from-[#2563EB]/5 to-transparent">
          <h3 class="text-base font-bold text-slate-800">Sistem Integrasi Gateway & Keamanan</h3>
          <p class="text-xs text-slate-400 mt-0.5">Kelola konfigurasi fungsional backend dan konektivitas modul eksternal secara terpusat.</p>
        </div>

        <form @submit.prevent="saveSettings" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Instansi / Website</label>
              <input type="text" v-model="form.app_name" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">IP Local WA Gateway</label>
              <input type="text" v-model="form.wa_host" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm font-mono outline-none focus:border-[#2563EB]" required />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Port Gateway (Default: 3100)</label>
              <input type="number" v-model="form.wa_port" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm font-mono outline-none focus:border-[#2563EB]" required />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Sesi Node.js Session</label>
              <input type="text" v-model="form.wa_session" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>

             <div>
              <label class="block text-xs font-bold text-slate-500 uppercase mb-2">API Token Kunci Rahasia</label>
              <input type="password" v-model="form.wa_api_key" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="••••••••••••••••" />
            </div>

            <div class="md:col-span-2 bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between">
              <div class="space-y-0.5">
                <label class="block text-xs font-bold text-slate-800 uppercase">Matikan Request OTP WhatsApp Mandiri</label>
                <p class="text-[11px] text-slate-400">Jika aktif, personel tidak bisa meminta OTP WhatsApp secara mandiri (tombol request OTP mati). Personel wajib meminta kode OTP manual dari lembar cetak Admin.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" v-model="form.disable_whatsapp_otp" true-value="1" false-value="0" class="sr-only peer" />
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </div>

          <!-- Section Pengaturan Tampilan Visual (Background, Logo, Favicon) -->
          <div class="border-t border-[#E2E8F0] pt-6 space-y-6">
            <div>
              <h4 class="text-sm font-bold text-slate-800">Branding & Tampilan Visual</h4>
              <p class="text-xs text-slate-400 mt-0.5">Unggah aset gambar untuk memodifikasi latar belakang halaman masuk, logo tri matra, dan favicon tab browser.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Favicon -->
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Favicon Browser (.ico, .png, Max 2MB)</label>
                <input type="file" @change="e => handleFileChange(e, 'favicon')" accept=".ico,.png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>

              <!-- Logo TNI Utama -->
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Logo Lambang TNI Utama (.png, Max 5MB)</label>
                <input type="file" @change="e => handleFileChange(e, 'logo_tni')" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>

              <!-- Logo AD -->
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Logo Angkatan Darat (AD) (.png, Max 5MB)</label>
                <input type="file" @change="e => handleFileChange(e, 'logo_ad')" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>

              <!-- Logo AL -->
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Logo Angkatan Laut (AL) (.png, Max 5MB)</label>
                <input type="file" @change="e => handleFileChange(e, 'logo_al')" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>

              <!-- Logo AU -->
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Logo Angkatan Udara (AU) (.png, Max 5MB)</label>
                <input type="file" @change="e => handleFileChange(e, 'logo_au')" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>

              <!-- Background Login -->
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Latar Belakang Halaman Login / Register (.png, .jpg, Max 5MB)</label>
                <input type="file" @change="e => handleFileChange(e, 'login_background')" accept=".png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between pt-6 border-t border-[#E2E8F0] flex-wrap gap-4">
            <div class="flex items-center gap-3">
              <button type="button" @click="testConnection" class="py-2 px-4 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center gap-2 cursor-pointer">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Uji Koneksi Port 3100
              </button>
              
              <button type="button" @click="promptTestSend" class="py-2 px-4 border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center gap-2 cursor-pointer">
                <span>💬</span>
                Test Kirim WA
              </button>
            </div>
            
            <button type="submit" class="py-2 px-5 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl shadow-md shadow-blue-500/10 transition cursor-pointer">
              Simpan Konfigurasi
            </button>
          </div>
        </form>
      </div>

      <!-- Sisi Kanan: Pratinjau Visual (Live Preview) -->
      <div class="space-y-6">
        <!-- Live Preview Favicon & Logo -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm space-y-4">
          <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aset Logo & Favicon</h3>
          
          <div class="space-y-4">
            <div class="flex items-center gap-6">
              <div class="text-center space-y-1.5">
                <span class="block text-[10px] text-slate-400 font-bold uppercase">Favicon</span>
                <div class="w-12 h-12 bg-slate-50 border border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden">
                  <img v-if="faviconPreview" :src="faviconPreview" class="w-8 h-8 object-contain" />
                  <span v-else class="text-xs text-slate-300">N/A</span>
                </div>
              </div>

              <div class="flex-1 space-y-1.5 text-center">
                <span class="block text-[10px] text-slate-400 font-bold uppercase">Logo Lambang TNI</span>
                <div class="h-16 bg-slate-50 border border-dashed border-slate-200 rounded-xl flex items-center justify-center p-2 overflow-hidden">
                  <img v-if="logoTniPreview" :src="logoTniPreview" class="h-full object-contain" />
                  <span v-else class="text-xs text-slate-300">Belum diunggah</span>
                </div>
              </div>
            </div>

            <!-- Matra Tri Logos Row -->
            <div class="space-y-1.5">
              <span class="block text-[10px] text-slate-400 font-bold uppercase text-center">Logo Matra (AD, AL, AU)</span>
              <div class="grid grid-cols-3 gap-2">
                <div class="h-16 bg-slate-50 border border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center p-1 overflow-hidden">
                  <img v-if="logoAdPreview" :src="logoAdPreview" class="h-10 object-contain" />
                  <span v-else class="text-[9px] text-slate-300">AD N/A</span>
                </div>
                <div class="h-16 bg-slate-50 border border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center p-1 overflow-hidden">
                  <img v-if="logoAlPreview" :src="logoAlPreview" class="h-10 object-contain" />
                  <span v-else class="text-[9px] text-slate-300">AL N/A</span>
                </div>
                <div class="h-16 bg-slate-50 border border-dashed border-slate-200 rounded-xl flex flex-col items-center justify-center p-1 overflow-hidden">
                  <img v-if="logoAuPreview" :src="logoAuPreview" class="h-10 object-contain" />
                  <span v-else class="text-[9px] text-slate-300">AU N/A</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Live Preview Background -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm space-y-4">
          <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pratinjau Background Login</h3>
          <div class="relative w-full h-44 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 flex items-center justify-center bg-cover bg-center" :style="loginBgPreview ? { backgroundImage: `url(${loginBgPreview})` } : {}">
            <div v-if="!loginBgPreview" class="text-xs text-slate-400 text-center px-4">Belum ada latar belakang khusus</div>
            <!-- Glassmorphism Login Box Mockup -->
            <div v-else class="absolute right-4 w-24 h-28 bg-white/30 backdrop-blur-md border border-white/20 rounded-lg p-2 shadow-lg flex flex-col justify-between">
              <div class="w-4 h-1 bg-white/80 rounded"></div>
              <div class="space-y-1">
                <div class="w-full h-1.5 bg-white/50 rounded"></div>
                <div class="w-full h-1.5 bg-white/50 rounded"></div>
              </div>
              <div class="w-full h-2.5 bg-orange-500 rounded"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useSwal } from '@/Composables/useSwal';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({ settings: Object });
const { alertSuccess, alertError, showLoadingProgress, closeLoading } = useSwal();

// Reactive State Previews
const loginBgPreview = ref(props.settings.login_background || '');
const logoTniPreview = ref(props.settings.logo_tni || '');
const logoAdPreview = ref(props.settings.logo_ad || '');
const logoAlPreview = ref(props.settings.logo_al || '');
const logoAuPreview = ref(props.settings.logo_au || '');
const faviconPreview = ref(props.settings.favicon || '');

const form = useForm({
  app_name: props.settings.app_name || 'SISFOPERSKC',
  wa_host: props.settings.wa_host || '127.0.0.1',
  wa_port: props.settings.wa_port || '3100',
  wa_session: props.settings.wa_session || 'sisfopers_session',
  wa_api_key: '',
  disable_whatsapp_otp: props.settings.disable_whatsapp_otp || '0',
  login_background: null,
  logo_tni: null,
  logo_ad: null,
  logo_al: null,
  logo_au: null,
  favicon: null
});

const handleFileChange = (e, key) => {
  const file = e.target.files[0];
  if (!file) return;
  
  form[key] = file;
  
  const reader = new FileReader();
  reader.onload = (event) => {
    if (key === 'login_background') loginBgPreview.value = event.target.result;
    if (key === 'logo_tni') logoTniPreview.value = event.target.result;
    if (key === 'logo_ad') logoAdPreview.value = event.target.result;
    if (key === 'logo_al') logoAlPreview.value = event.target.result;
    if (key === 'logo_au') logoAuPreview.value = event.target.result;
    if (key === 'favicon') faviconPreview.value = event.target.result;
  };
  reader.readAsDataURL(file);
};

const saveSettings = () => {
  form.post(route('admin.setting.update'), {
    forceFormData: true, // Paksa FormData agar Laravel dapat mengurai berkas biner
    onBefore: () => showLoadingProgress('Menyinkronkan Basis Data...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Penyimpanan Berhasil', 'Konfigurasi global telah terenkripsi dan diterapkan ke sistem.');
    },
    onError: () => {
      closeLoading();
      alertError('Gagal Menyimpan', 'Periksa kembali parameter inputan Anda.');
    }
  });
};

const testConnection = async () => {
  showLoadingProgress('Melakukan Ping ke Port ' + form.wa_port + '...');
  try {
    const res = await axios.post(route('admin.setting.wa-test'));
    closeLoading();
    if (res.data.status) {
      alertSuccess('Koneksi Terhubung!', res.data.message);
    } else {
      alertError('Koneksi Gagal', res.data.message);
    }
  } catch (err) {
    closeLoading();
    alertError('Koneksi Gagal', 'Layanan Local WA Gateway mati atau tidak merespon.');
  }
};

const promptTestSend = async () => {
  const { value: phone } = await Swal.fire({
    title: 'Test Kirim WA',
    input: 'text',
    inputLabel: 'Masukkan nomor WhatsApp tujuan',
    inputPlaceholder: 'Contoh: 628123456789',
    showCancelButton: true,
    confirmButtonColor: '#2563EB',
    cancelButtonColor: '#1E293B',
    confirmButtonText: 'Kirim',
    cancelButtonText: 'Batal',
    customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg', cancelButton: 'rounded-lg' },
    inputValidator: (value) => {
      if (!value) {
        return 'Nomor WhatsApp tujuan wajib diisi!';
      }
      if (!/^\d+$/.test(value)) {
        return 'Nomor WhatsApp hanya boleh berupa angka!';
      }
    }
  });

  if (phone) {
    showLoadingProgress('Mengirim pesan uji coba ke ' + phone + '...');
    try {
      const res = await axios.post(route('admin.setting.wa-test-send'), { phone });
      closeLoading();
      if (res.data.status) {
        alertSuccess('Berhasil Terkirim!', res.data.message);
      } else {
        alertError('Gagal Mengirim', res.data.message);
      }
    } catch (err) {
      closeLoading();
      alertError('Koneksi Gagal', 'Gagal memproses pengiriman pesan uji coba.');
    }
  }
};
</script>