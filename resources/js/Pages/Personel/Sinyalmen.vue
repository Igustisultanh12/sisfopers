<template>
  <div 
    class="min-h-screen flex text-[#334155] font-sans bg-cover bg-center relative transition-all duration-300 overflow-y-auto"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>
    
    <!-- Bagian Kiri: Logo Tri Matra & Informasi Aplikasi (Desktop) -->
    <div 
      class="hidden lg:flex lg:w-5/12 xl:w-1/2 flex-col justify-between p-12 xl:p-16 transition-all duration-300 z-10 sticky top-0 h-screen"
      :class="settings?.login_background ? 'text-white' : 'bg-gradient-to-b from-[#2563EB]/5 to-transparent'"
    >
      <div></div>
      
      <!-- Live Preview Logo Tri Matra -->
      <div class="my-auto max-w-lg space-y-8 flex flex-col items-center text-center mx-auto">
        <!-- Logo TNI Utama -->
        <div v-if="settings?.logo_tni" class="flex justify-center animate-float-slow">
          <img :src="settings.logo_tni" class="h-32 lg:h-40 object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.6)]" />
        </div>
        
        <!-- Logo Tiga Matra Sejajar -->
        <div v-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center justify-center gap-6 flex-wrap animate-float-slow delay-200">
          <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-16 object-contain drop-shadow-md transition hover:scale-110" />
          <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-16 object-contain drop-shadow-md transition hover:scale-110" />
          <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-16 object-contain drop-shadow-md transition hover:scale-110" />
        </div>
        
        <div v-if="!settings?.logo_tni && !settings?.logo_ad && !settings?.logo_al && !settings?.logo_au" class="relative w-72 h-72 bg-white/60 backdrop-blur-md rounded-3xl border border-white p-6 shadow-xl shadow-blue-500/5 flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="w-12 h-3 bg-[#2563EB]/20 rounded-full"></span>
            <span class="w-3 h-3 rounded-full bg-[#22C55E]"></span>
          </div>
          <div class="space-y-2">
            <div class="h-4 bg-[#1E293B]/10 rounded-md w-full"></div>
            <div class="h-4 bg-[#1E293B]/10 rounded-md w-5/6"></div>
          </div>
          <div class="h-24 bg-gradient-to-t from-[#2563EB]/10 to-[#2563EB]/0 rounded-xl border border-dashed border-[#2563EB]/20 flex items-center justify-center text-xs text-[#2563EB] font-medium">
            SaaS Enterprise Dashboard System Active
          </div>
        </div>

        <div>
          <h2 class="text-3xl font-extrabold tracking-tight uppercase" :class="settings?.login_background ? 'text-white drop-shadow-md' : 'text-slate-800'">
            {{ settings?.app_name || 'SISFOPERSKC' }} INTEGRASI TNI
          </h2>
          <p class="text-sm mt-3 leading-relaxed max-w-md mx-auto" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-500'">
            Sistem informasi personel yang terintegrasi, valid, dan akuntabel untuk pengelolaan administrasi personel komponen cadangan.
          </p>
        </div>
      </div>

      <p class="text-xs" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-400'">
        © 2026 {{ settings?.app_name || 'SISFOPERSKC' }}. All Rights Reserved.
      </p>
    </div>

    <!-- Bagian Kanan: Card Form Sinyalmen -->
    <div class="w-full lg:w-7/12 xl:w-1/2 flex items-center justify-center p-6 sm:p-10 z-10 my-auto">
      <div 
        class="w-full max-w-2xl p-8 sm:p-10 rounded-2xl shadow-2xl transition-all duration-300 animate-float-card space-y-6"
        :class="settings?.login_background 
          ? 'bg-slate-900/40 backdrop-blur-md border border-white/10 text-white' 
          : 'bg-white border border-[#E2E8F0] shadow-slate-200/50 text-[#334155]'"
      >
        <!-- Header logo di mobile -->
        <div class="lg:hidden flex items-center justify-center gap-3 mb-2">
          <img v-if="settings?.logo_tni" :src="settings.logo_tni" class="h-12 object-contain" />
        </div>

        <!-- Header Ruang Administrasi -->
        <div class="space-y-1.5 pb-4 border-b" :class="settings?.login_background ? 'border-white/10' : 'border-[#E2E8F0]'">
          <span class="text-[10px] bg-[#2563EB]/10 text-[#2563EB] px-2.5 py-1 rounded font-bold uppercase tracking-wider inline-block">
            Gerbang Validasi Akhir
          </span>
          <h3 class="text-xl font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
            Kelengkapan Data Ciri Fisik (Sinyalmen)
          </h3>
          <p class="text-xs leading-relaxed" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
            Sila lengkapi data antropometri dan kondisi fisik riil Anda demi validitas database kekuatan pasukan.
          </p>
        </div>

        <!-- Form Inputs Sinyalmen -->
        <form @submit.prevent="submitSinyalmen" class="space-y-5">
          
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">Tinggi Badan (cm)</label>
              <input 
                type="number" 
                v-model="form.tinggi_badan" 
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition border"
                :class="settings?.login_background 
                  ? 'bg-white/10 border-white/20 text-white placeholder-slate-400 focus:border-blue-400' 
                  : 'bg-white border-[#E2E8F0] text-slate-800 focus:border-[#2563EB]'" 
                placeholder="Contoh: 173" 
                required 
              />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">Berat Badan (kg)</label>
              <input 
                type="number" 
                v-model="form.berat_badan" 
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition border"
                :class="settings?.login_background 
                  ? 'bg-white/10 border-white/20 text-white placeholder-slate-400 focus:border-blue-400' 
                  : 'bg-white border-[#E2E8F0] text-slate-800 focus:border-[#2563EB]'" 
                placeholder="Contoh: 68" 
                required 
              />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">Golongan Darah</label>
              <select 
                v-model="form.golongan_darah" 
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition font-medium border"
                :class="settings?.login_background 
                  ? 'bg-slate-800 border-white/20 text-white focus:border-blue-400' 
                  : 'bg-white border-[#E2E8F0] text-slate-700 focus:border-[#2563EB]'" 
                required
              >
                <option value="" :class="settings?.login_background ? 'bg-slate-800 text-slate-300' : ''">Pilih--</option>
                <option value="A" :class="settings?.login_background ? 'bg-slate-800 text-white' : ''">A</option>
                <option value="B" :class="settings?.login_background ? 'bg-slate-800 text-white' : ''">B</option>
                <option value="AB" :class="settings?.login_background ? 'bg-slate-800 text-white' : ''">AB</option>
                <option value="O" :class="settings?.login_background ? 'bg-slate-800 text-white' : ''">O</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">Kondisi / Sifat Rambut</label>
              <input 
                type="text" 
                v-model="form.rambut" 
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition border"
                :class="settings?.login_background 
                  ? 'bg-white/10 border-white/20 text-white placeholder-slate-400 focus:border-blue-400' 
                  : 'bg-white border-[#E2E8F0] text-slate-800 focus:border-[#2563EB]'" 
                placeholder="Contoh: Hitam Lurus, Cepak, Ikal" 
                required 
              />
            </div>
            <div>
              <label class="block text-xs font-bold uppercase mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">Warna / Kondisi Bola Mata</label>
              <input 
                type="text" 
                v-model="form.mata" 
                class="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition border"
                :class="settings?.login_background 
                  ? 'bg-white/10 border-white/20 text-white placeholder-slate-400 focus:border-blue-400' 
                  : 'bg-white border-[#E2E8F0] text-slate-800 focus:border-[#2563EB]'" 
                placeholder="Contoh: Hitam, Cokelat Tua" 
                required 
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">Ciri Khas Khusus Paten (Jika Ada)</label>
            <input 
              type="text" 
              v-model="form.ciri_khas" 
              class="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition border"
              :class="settings?.login_background 
                ? 'bg-white/10 border-white/20 text-white placeholder-slate-400 focus:border-blue-400' 
                : 'bg-white border-[#E2E8F0] text-slate-800 focus:border-[#2563EB]'" 
              placeholder="Contoh: Tahi lalat di pipi kanan, tato matra laut di lengan (Isi '-' jika tidak ada)" 
              required 
            />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase mb-2" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">Riwayat Kehilangan/Cacat Anatomi Tubuh</label>
            <textarea 
              v-model="form.cacat_tubuh" 
              rows="3" 
              class="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition border"
              :class="settings?.login_background 
                ? 'bg-white/10 border-white/20 text-white placeholder-slate-400 focus:border-blue-400' 
                : 'bg-white border-[#E2E8F0] text-slate-800 focus:border-[#2563EB]'" 
              placeholder="Contoh: Bekas luka jahitan operasi di lutut kiri. (Isi 'Tidak Ada' jika kondisi fisik sehat walafiat)" 
              required
            ></textarea>
          </div>

          <!-- Tombol Aksi Kontrol -->
          <div class="flex items-center justify-between pt-4 border-t" :class="settings?.login_background ? 'border-white/10' : 'border-[#E2E8F0]'">
            <Link 
              :href="route('logout')" 
              method="post" 
              as="button" 
              class="text-xs font-bold transition cursor-pointer"
              :class="settings?.login_background ? 'text-slate-400 hover:text-red-400' : 'text-slate-400 hover:text-red-500'"
            >
              Keluar Sistem
            </Link>
            <button 
              type="submit" 
              :disabled="form.processing" 
              class="py-3 px-6 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-60 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/20 transition cursor-pointer flex items-center gap-2"
            >
              <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Simpan & Masuk Dashboard</span>
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const page = usePage();
const settings = computed(() => page.props.settings || {});
const { showLoadingProgress, closeLoading, alertSuccess, alertError } = useSwal();

const form = useForm({
  tinggi_badan: '',
  berat_badan: '',
  golongan_darah: '',
  rambut: '',
  mata: '',
  ciri_khas: '',
  cacat_tubuh: 'Tidak Ada',
});

const submitSinyalmen = () => {
  form.post(route('personel.sinyalmen.store'), {
    onBefore: () => showLoadingProgress('Menyimpan Data Antropometri...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Data Lengkap', 'Seluruh berkas persyaratan masuk sistem telah terpenuhi sepenuhnya.');
    },
    onError: () => {
      closeLoading();
      alertError('Gagal Menyimpan', 'Harap periksa kembali format angka pada tinggi atau berat badan Anda.');
    }
  });
};
</script>