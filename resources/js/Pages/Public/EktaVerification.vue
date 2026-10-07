<template>
  <div 
    class="min-h-screen flex text-[#334155] font-sans bg-cover bg-center relative transition-all duration-300"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>
    
    <!-- Bagian Kiri: Logo Tri Matra & Informasi Aplikasi (Desktop) -->
    <div 
      class="hidden lg:flex lg:w-1/2 flex-col justify-between p-16 transition-all duration-300 z-10"
      :class="settings?.login_background ? 'text-white' : 'bg-gradient-to-b from-[#2563EB]/5 to-transparent'"
    >
      <div></div>
      
      <!-- Live Preview Logo Tri Matra & Lambang TNI (Persis Sesuai Login.vue) -->
      <div class="my-auto max-w-lg space-y-8 flex flex-col items-center text-center mx-auto">
        <!-- Logo TNI Utama -->
        <div v-if="settings?.logo_tni" class="flex justify-center animate-float-slow">
          <img :src="settings.logo_tni" class="h-32 lg:h-40 object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.6)]" alt="Logo Mabes TNI" />
        </div>
        
        <!-- Logo Tiga Matra Sejajar -->
        <div v-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center justify-center gap-6 flex-wrap animate-float-slow delay-200">
          <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-16 object-contain drop-shadow-md transition hover:scale-110" alt="Logo TNI AD" />
          <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-16 object-contain drop-shadow-md transition hover:scale-110" alt="Logo TNI AL" />
          <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-16 object-contain drop-shadow-md transition hover:scale-110" alt="Logo TNI AU" />
        </div>

        <!-- Fallback jika belum diatur logo TNI/Matra -->
        <div v-if="!settings?.logo_tni && !settings?.logo_ad && !settings?.logo_al && !settings?.logo_au" class="flex justify-center animate-float-slow">
          <img v-if="settings?.ekta_logo_komcad" :src="settings.ekta_logo_komcad" class="h-32 lg:h-40 object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.6)]" alt="Logo Komcad" />
          <KomcadEmblem v-else class="h-32 w-32 drop-shadow-[0_10px_25px_rgba(0,0,0,0.6)]" />
        </div>
        
        <div>
          <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-widest bg-blue-600/20 text-blue-300 border border-blue-400/30">
            PORTAL VERIFIKASI RESMI
          </span>
          <h2 class="text-3xl font-black tracking-tight uppercase mt-3" :class="settings?.login_background ? 'text-white drop-shadow-md' : 'text-slate-800'">
            {{ settings?.app_name || 'SISFOPERS KC' }} INTEGRASI TNI
          </h2>
          <p class="text-sm mt-3 leading-relaxed max-w-md mx-auto" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-500'">
            Pusat Verifikasi Otentikasi Kartu Tanda Anggota Elektronik (E-KTA) Komponen Cadangan Kementerian Pertahanan Republik Indonesia.
          </p>
        </div>
      </div>

      <p class="text-xs" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-400'">
        © 2026 {{ settings?.app_name || 'SISFOPERS KC' }}. Hak Cipta Dilindungi Undang-Undang.
      </p>
    </div>

    <!-- Bagian Kanan: Card Form Verifikasi Dokumen Publik -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 z-10">
      <div 
        class="w-full max-w-lg p-6 sm:p-8 rounded-3xl shadow-2xl transition-all duration-300 space-y-6"
        :class="settings?.login_background 
          ? 'bg-slate-900/80 backdrop-blur-md border border-white/10 text-white' 
          : 'bg-white border border-[#E2E8F0] shadow-slate-200/50 text-[#334155]'"
      >
        <!-- Header logo di mobile (Persis Sesuai Login.vue) -->
        <div class="lg:hidden flex flex-col items-center justify-center gap-2 mb-4">
          <div v-if="settings?.logo_tni" class="flex justify-center">
            <img :src="settings.logo_tni" class="h-14 object-contain drop-shadow-md" alt="Logo TNI" />
          </div>
          <div v-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center justify-center gap-3">
            <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-8 object-contain drop-shadow-xs" alt="Logo TNI AD" />
            <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-8 object-contain drop-shadow-xs" alt="Logo TNI AL" />
            <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-8 object-contain drop-shadow-xs" alt="Logo TNI AU" />
          </div>
          <div v-if="!settings?.logo_tni && !settings?.logo_ad && !settings?.logo_al && !settings?.logo_au" class="flex justify-center">
            <img v-if="settings?.ekta_logo_komcad" :src="settings.ekta_logo_komcad" class="h-12 object-contain" alt="Logo Komcad" />
            <KomcadEmblem v-else class="w-12 h-12" />
          </div>
        </div>

        <!-- KONDISI 1: E-KTA VALID & TERDAFTAR SAH -->
        <div v-if="is_valid && doc" class="space-y-6">
          
          <!-- BANNER STATUS SAH -->
          <div 
            class="p-5 rounded-2xl text-center space-y-2 border shadow-xs"
            :class="settings?.login_background ? 'bg-emerald-500/20 border-emerald-400/40 text-emerald-200' : 'bg-emerald-50 border-emerald-200 text-emerald-900'"
          >
            <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center mx-auto shadow-md shadow-emerald-600/30">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-base font-black tracking-tight uppercase">E-KTA RESMI TERVERIFIKASI & SAH</h3>
            <p class="text-xs leading-relaxed opacity-90">
              Keabsahan dan integritas Kartu Tanda Anggota Elektronik (E-KTA) Komponen Cadangan ini terdaftar secara sah pada basis data <strong>SISFOPERS KC</strong> Kementerian Pertahanan RI.
            </p>
          </div>

          <!-- DETAIL HASIL VERIFIKASI -->
          <div class="space-y-3.5">
            <div class="flex items-center justify-between border-b pb-3" :class="settings?.login_background ? 'border-white/10' : 'border-[#E2E8F0]'">
              <span class="text-xs font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                Kode Otentikasi
              </span>
              <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded border" :class="settings?.login_background ? 'bg-white/10 border-white/20 text-emerald-300' : 'bg-emerald-50 border-emerald-200 text-emerald-700'">
                {{ doc.verify_code }}
              </span>
            </div>

            <div class="space-y-2.5 text-xs">
              <!-- Nomor KTA -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">Nomor KTA</span>
                <span class="font-mono font-black text-right">{{ doc.nomor_kta }}</span>
              </div>

              <!-- Nama Personel (Disamarkan) -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">Nama Personel</span>
                <span class="font-black text-right text-emerald-600 text-sm tracking-wide">{{ doc.nama_personel }}</span>
              </div>

              <!-- NRP / NIKC (Disamarkan) -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">NRP / NIKC</span>
                <span class="font-mono font-bold text-right">{{ doc.nikc }}</span>
              </div>

              <!-- Pangkat -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">Pangkat Militer</span>
                <span class="font-bold text-right">{{ doc.pangkat }}</span>
              </div>

              <!-- Jabatan -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">Jabatan</span>
                <span class="font-bold text-right">{{ doc.jabatan }}</span>
              </div>

              <!-- Kesatuan / Matra -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">Kesatuan / Matra</span>
                <span class="font-bold text-right">{{ doc.kesatuan_matra }}</span>
              </div>

              <!-- Masa Berlaku -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">Masa Berlaku</span>
                <span class="font-bold text-right">{{ doc.berlaku_sampai }}</span>
              </div>

              <!-- Tanggal Pengesahan -->
              <div class="flex justify-between items-baseline py-1 border-b border-dashed" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <span class="opacity-70 font-medium">Tanggal Diterbitkan</span>
                <span class="font-bold text-right">{{ doc.tanggal_terbit }}</span>
              </div>

              <!-- Pejabat Penandatangan -->
              <div class="flex justify-between items-baseline py-1">
                <span class="opacity-70 font-medium">Pejabat Pengesah</span>
                <span class="font-bold text-right">{{ doc.signer_title }} - {{ doc.signer_name }} ({{ doc.signer_rank }})</span>
              </div>
            </div>

            <!-- Catatan Perlindungan Data Pribadi -->
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[10px] text-slate-500 leading-relaxed text-center" :class="settings?.login_background ? 'bg-white/5 border-white/10 text-slate-300' : ''">
              Sesuai dengan Undang-Undang Perlindungan Data Pribadi (UU PDP), sebagian data identitas personel disamarkan demi keamanan informasi pertahanan negara.
            </div>
          </div>
        </div>

        <!-- KONDISI 2: DOKUMEN TIDAK DITEMUKAN / TIDAK VALID -->
        <div v-else class="text-center py-8 space-y-4">
          <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto border border-rose-200">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
          </div>
          <h3 class="text-lg font-black text-rose-600 uppercase">DOKUMEN TIDAK VALID ATAU BELUM TERDAFTAR</h3>
          <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
            {{ message || 'Data Kartu Tanda Anggota Elektronik (E-KTA) tidak ditemukan dalam pangkalan data resmi SISFOPERS KC. Mohon pastikan kode QR yang dipindai adalah kode otentikasi resmi.' }}
          </p>
          <div class="pt-4">
            <a href="/" class="px-5 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition inline-block">
              Kembali ke Beranda
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import KomcadEmblem from '@/Components/KomcadEmblem.vue';

defineProps({
  is_valid: Boolean,
  doc: Object,
  verify_code: String,
  message: String,
  settings: Object,
});
</script>

<style scoped>
.animate-float-slow {
  animation: float-logo 5s ease-in-out infinite;
}

.animate-float-card {
  animation: float-card 6s ease-in-out infinite;
}

@keyframes float-logo {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

@keyframes float-card {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}
</style>
