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
            Portal Verifikasi Otentikasi Dokumen Resmi Komponen Cadangan Markas Besar TNI.
          </p>
        </div>
      </div>

      <p class="text-xs" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-400'">
        © 2026 {{ settings?.app_name || 'SISFOPERSKC' }}. All Rights Reserved.
      </p>
    </div>

    <!-- Bagian Kanan: Card Form Verifikasi Dokumen Publik -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 z-10">
      <div 
        class="w-full max-w-lg p-8 sm:p-10 rounded-2xl shadow-2xl transition-all duration-300 animate-float-card space-y-6"
        :class="settings?.login_background 
          ? 'bg-slate-900/40 backdrop-blur-md border border-white/10 text-white' 
          : 'bg-white border border-[#E2E8F0] shadow-slate-200/50 text-[#334155]'"
      >
        <!-- Header logo di mobile -->
        <div class="lg:hidden flex items-center justify-center gap-3 mb-4">
          <img v-if="settings?.logo_tni" :src="settings.logo_tni" class="h-12 object-contain" />
        </div>

        <!-- KONDISI 1: DOKUMEN VALID & TERDAFTAR -->
        <div v-if="doc && doc.is_valid" class="space-y-6">
          
          <!-- BANNER STATUS SAH -->
          <div 
            class="p-5 rounded-2xl text-center space-y-2 border shadow-xs"
            :class="settings?.login_background ? 'bg-emerald-500/20 border-emerald-400/40 text-emerald-200' : 'bg-emerald-50 border-emerald-200 text-emerald-900'"
          >
            <div class="w-12 h-12 bg-emerald-500 text-white rounded-full flex items-center justify-center mx-auto text-xl font-bold shadow-md shadow-emerald-500/30">
              ✓
            </div>
            <h3 class="text-lg font-extrabold tracking-tight uppercase">DOKUMEN RESMI TERVERIFIKASI & SAH</h3>
            <p class="text-xs leading-relaxed opacity-90">
              Keabsahan dan integritas dokumen ini terdaftar secara sah pada basis data <strong>SISFOPERSKC</strong>.
            </p>
          </div>

          <!-- DETAIL HASIL VERIFIKASI -->
          <div class="space-y-4">
            <div class="flex items-center justify-between border-b pb-3" :class="settings?.login_background ? 'border-white/10' : 'border-[#E2E8F0]'">
              <span class="text-xs font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                Manifes Otentikasi
              </span>
              <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded border" :class="settings?.login_background ? 'bg-white/10 border-white/20 text-orange-300' : 'bg-blue-50 border-blue-100 text-[#2563EB]'">
                {{ doc.verify_code }}
              </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
              <div 
                class="sm:col-span-2 p-3.5 rounded-xl border space-y-0.5"
                :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
              >
                <span class="text-[10px] font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">Judul Dokumen Resmi</span>
                <p class="text-xs font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">{{ doc.doc_title }}</p>
              </div>

              <div 
                v-if="doc.nomor_surat"
                class="sm:col-span-2 p-3.5 rounded-xl border space-y-0.5"
                :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
              >
                <span class="text-[10px] font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">Nomor Surat Resmi</span>
                <p class="text-xs font-mono font-bold" :class="settings?.login_background ? 'text-blue-300' : 'text-[#2563EB]'">{{ doc.nomor_surat }}</p>
              </div>

              <div 
                class="p-3.5 rounded-xl border space-y-0.5"
                :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
              >
                <span class="text-[10px] font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">Nama Personel (Subjek)</span>
                <p class="text-xs font-bold" :class="settings?.login_background ? 'text-emerald-300' : 'text-emerald-700'">{{ doc.subject_name }}</p>
              </div>

              <div 
                class="p-3.5 rounded-xl border space-y-0.5"
                :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
              >
                <span class="text-[10px] font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">NIKC / NIK Identitas</span>
                <p class="text-xs font-mono font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">{{ doc.subject_identifier || '-' }}</p>
              </div>

              <div 
                class="p-3.5 rounded-xl border space-y-0.5"
                :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
              >
                <span class="text-[10px] font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">Tanggal & Waktu Penerbitan</span>
                <p class="text-xs font-semibold" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">{{ doc.printed_at }}</p>
              </div>

              <div 
                class="p-3.5 rounded-xl border space-y-0.5"
                :class="settings?.login_background ? 'bg-white/5 border-white/10' : 'bg-slate-50 border-[#E2E8F0]'"
              >
                <span class="text-[10px] font-bold uppercase tracking-wider" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">Yang bertanda tangan</span>
                <p class="text-xs font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">{{ doc.signer_name }}</p>
                <p class="text-[10px]" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">{{ doc.signer_title }}</p>
              </div>
            </div>

            <!-- CATATAN JAMINAN KEASLIAN -->
            <div 
              class="p-4 rounded-xl text-xs leading-relaxed border space-y-1"
              :class="settings?.login_background ? 'bg-blue-500/10 border-blue-400/30 text-blue-200' : 'bg-blue-50 border-blue-100 text-blue-900'"
            >
              <strong class="block font-bold">🔒 Catatan Jaminan Keaslian:</strong>
              <p class="text-[11px] opacity-90">
                Jika data pada dokumen fisik/lembar cetak berbeda dengan data di atas, maka dokumen tersebut dinyatakan <strong>TIDAK SAH / PALSU</strong>.
              </p>
            </div>
          </div>

        </div>

        <!-- KONDISI 2: KODE DOKUMEN TIDAK DITEMUKAN / TIDAK VALID -->
        <div v-else class="space-y-4">
          <div 
            class="p-6 rounded-2xl text-center space-y-3 border"
            :class="settings?.login_background ? 'bg-red-500/20 border-red-400/40 text-red-200' : 'bg-red-50 border-red-200 text-red-900'"
          >
            <div class="w-12 h-12 bg-red-500 text-white rounded-full flex items-center justify-center mx-auto text-xl font-bold shadow-md shadow-red-500/30">
              ✕
            </div>
            <h3 class="text-lg font-extrabold tracking-tight uppercase">DOKUMEN TIDAK TERDAFTAR</h3>
            <p class="text-xs leading-relaxed">
              Kode verifikasi <span class="font-mono font-bold px-2 py-0.5 rounded bg-black/20">{{ verify_code }}</span> tidak ditemukan pada basis data resmi SISFOPERSKC.
            </p>
          </div>
        </div>

        <div class="pt-2 border-t border-[#E2E8F0]/60">
          <Link :href="route('login')" class="w-full text-center text-xs font-bold text-[#2563EB] hover:underline block cursor-pointer">
            ← Kembali ke Portal Login
          </Link>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
  verify_code: String,
  doc: Object
});

const page = usePage();
const settings = computed(() => page.props.settings || {});
</script>
