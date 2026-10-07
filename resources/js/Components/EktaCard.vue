<template>
  <div class="flex flex-col items-center">
    <!-- Kontrol Sisi Kartu (Depan / Belakang) & Cetak -->
    <div class="flex items-center justify-between w-full max-w-[560px] mb-3 print:hidden">
      <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl border border-slate-200">
        <button
          @click="currentSide = 'front'"
          type="button"
          :class="currentSide === 'front' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
          class="px-3.5 py-1.5 text-xs rounded-lg transition"
        >
          Tampak Depan
        </button>
        <button
          @click="currentSide = 'back'"
          type="button"
          :class="currentSide === 'back' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
          class="px-3.5 py-1.5 text-xs rounded-lg transition"
        >
          Tampak Belakang
        </button>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="flipCard"
          type="button"
          class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition shadow-xs flex items-center gap-1.5"
          title="Balik Kartu"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Balik Kartu
        </button>
        <button
          @click="printCard"
          type="button"
          class="px-3 py-1.5 text-xs font-bold text-white bg-[#2563EB] hover:bg-blue-700 rounded-xl transition shadow-xs flex items-center gap-1.5"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Cetak E-KTA
        </button>
      </div>
    </div>

    <!-- Container Utama Kartu Digital (Standar CR80 Aspect Ratio 85.6 x 54) -->
    <div id="printable-ekta" class="w-full max-w-[560px] aspect-[85.6/54] relative rounded-2xl shadow-xl overflow-hidden border border-emerald-900/30 select-none font-sans text-slate-900 bg-[#94BA74]">
      
      <!-- Watermark Background Pola Teks "KOMCAD" Miring & Berulang -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20 flex flex-wrap content-start justify-center gap-x-3 gap-y-2 select-none -rotate-6 scale-110">
        <span v-for="n in 260" :key="n" class="text-[9px] font-black tracking-widest text-[#244616] uppercase">
          KOMCAD
        </span>
      </div>

      <!-- Watermark Lambang Komcad Besar di Latar Belakang Tengah -->
      <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-30 scale-125 z-0">
        <KomcadEmblem v-if="!settings?.logo_komcad" class="w-64 h-64" />
        <img v-else :src="getAssetUrl(settings.logo_komcad)" class="w-64 h-64 object-contain" alt="Watermark" />
      </div>

      <!-- ================= SISI DEPAN (FRONT SIDE) ================= -->
      <div v-show="currentSide === 'front'" class="relative z-10 w-full h-full flex p-3.5 sm:p-4 gap-3">
        
        <!-- Kolom Kiri: Logo Komcad & Pasfoto Personel -->
        <div class="w-[25%] flex flex-col justify-between shrink-0 h-full">
          <!-- Kotak Logo Komcad Atas -->
          <div class="w-full aspect-square bg-white rounded-lg p-1.5 shadow-xs border border-emerald-900/30 flex items-center justify-center">
            <img v-if="settings?.logo_komcad" :src="getAssetUrl(settings.logo_komcad)" class="w-full h-full object-contain" alt="Logo Komcad" />
            <KomcadEmblem v-else class="w-full h-full" />
          </div>

          <!-- Kotak Pasfoto Personel Bawah Berlatar Matra -->
          <div
            class="w-full aspect-[3/4] rounded-lg overflow-hidden shadow-xs border border-emerald-900/30 relative flex items-center justify-center"
            :class="matraBgClass"
          >
            <img
              v-if="personel?.photo_profile"
              :src="getPhotoUrl(personel.photo_profile, personel?.full_name)"
              @error="$event.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(personel?.full_name || 'PERS') + '&background=e2e8f0&color=334155'"
              class="w-full h-full object-cover object-top"
              alt="Foto Personel"
            />
            <div v-else class="flex flex-col items-center justify-center text-white/80 p-1 text-center">
              <svg class="w-8 h-8 opacity-70" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
              <span class="text-[8px] font-bold mt-0.5 uppercase tracking-tighter">FOTO PERS</span>
            </div>
          </div>
        </div>

        <!-- Garis Pemisah Vertikal Kiri -->
        <div class="w-[1.5px] bg-[#244616]/40 h-full shrink-0"></div>

        <!-- Kolom Kanan: Rincian KTA, Data, Pejabat & QR Code -->
        <div class="w-[73%] flex flex-col justify-between h-full pl-0.5">
          <!-- Header Judul & Nomor KTA -->
          <div class="text-center">
            <h2 class="text-[13px] sm:text-[14.5px] font-extrabold tracking-wide uppercase text-[#142C0A] border-b-[1.5px] border-[#142C0A] pb-0.5 inline-block">
              KARTU TANDA ANGGOTA KOMCAD
            </h2>
            <p class="text-[10px] sm:text-[11px] font-extrabold font-mono tracking-tight text-[#142C0A] mt-0.5">
              {{ displayNomorKta }}
            </p>
          </div>

          <!-- Rincian Data Personel (Dengan Titik-Titik Panduan Khas KTA Militer) -->
          <div class="space-y-[3px] text-[10px] sm:text-[11px] font-bold text-[#142C0A] my-auto">
            <!-- Nama -->
            <div class="flex items-baseline">
              <span class="w-24 shrink-0 font-extrabold">Nama</span>
              <span class="mr-1.5">:</span>
              <span class="truncate font-black text-slate-950">{{ personel?.full_name || '-' }}</span>
              <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
            </div>

            <!-- Pangkat -->
            <div class="flex items-baseline">
              <span class="w-24 shrink-0 font-extrabold">Pangkat</span>
              <span class="mr-1.5">:</span>
              <span>{{ displayPangkat }}</span>
              <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
            </div>

            <!-- NIKC -->
            <div class="flex items-baseline">
              <span class="w-24 shrink-0 font-extrabold">NIKC</span>
              <span class="mr-1.5">:</span>
              <span class="font-mono font-black tracking-tight">{{ personel?.nikc || personel?.user?.username || '-' }}</span>
              <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
            </div>

            <!-- Jabatan -->
            <div class="flex items-baseline">
              <span class="w-24 shrink-0 font-extrabold">Jabatan</span>
              <span class="mr-1.5">:</span>
              <span>{{ displayJabatan }}</span>
              <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
            </div>

            <!-- Kesatuan/Matra -->
            <div class="flex items-baseline">
              <span class="w-24 shrink-0 font-extrabold">Kesatuan/Matra</span>
              <span class="mr-1.5">:</span>
              <span>{{ displayMatra }}</span>
              <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
            </div>

            <!-- Berlaku s/d -->
            <div class="flex items-baseline">
              <span class="w-24 shrink-0 font-extrabold">Berlaku s/d</span>
              <span class="mr-1.5">:</span>
              <span class="text-[9.5px] sm:text-[10px]">{{ displayBerlaku }}</span>
              <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
            </div>
          </div>

          <!-- Bagian Bawah: Penandatangan Pejabat & QR Code -->
          <div class="flex items-end justify-between pt-1">
            <!-- Sisi Pejabat Pengesah (Dirjen Pothan) -->
            <div class="text-center relative min-w-[150px] leading-tight text-[#142C0A]">
              <p class="text-[8.5px] sm:text-[9.5px] font-extrabold uppercase">{{ signerTitleLine1 }}</p>
              <p class="text-[8.5px] sm:text-[9.5px] font-extrabold uppercase">{{ signerTitleLine2 }}</p>

              <!-- Ruang Tanda Tangan & Cap Stempel Basah -->
              <div class="h-9 sm:h-10 relative flex items-center justify-center my-0.5">
                <!-- Stempel Dinas Komcad / Kemhan Bulat Ungu/Merah -->
                <div class="absolute -left-2 top-0 w-11 h-11 sm:w-12 sm:h-12 pointer-events-none opacity-85 rotate-[-12deg]">
                  <img v-if="settings?.stamp" :src="getAssetUrl(settings.stamp)" class="w-full h-full object-contain" alt="Stempel Dinas" />
                  <div v-else class="w-full h-full rounded-full border-2 border-purple-800/80 p-0.5 flex items-center justify-center text-center">
                    <div class="w-full h-full rounded-full border border-purple-800/60 flex flex-col items-center justify-center text-[5px] font-extrabold text-purple-900 leading-[6px]">
                      <span>KEMHAN RI</span>
                      <span class="text-[4px]">* KOMCAD *</span>
                      <span>DITJEN</span>
                    </div>
                  </div>
                </div>

                <!-- Tanda Tangan Pejabat -->
                <div class="relative z-10 h-full flex items-center justify-center">
                  <img v-if="settings?.signature" :src="getAssetUrl(settings.signature)" class="h-8 sm:h-9 object-contain" alt="Tanda Tangan" />
                  <!-- Fallback signature visual SVG -->
                  <svg v-else class="h-8 w-24 text-blue-900/90" viewBox="0 0 100 40" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M10 25 C20 10, 30 35, 40 15 C50 5, 55 25, 70 20 C80 18, 85 28, 95 22" stroke-linecap="round"/>
                    <path d="M35 22 L65 24" stroke-width="1.2"/>
                  </svg>
                </div>
              </div>

              <!-- Nama & Pangkat Pejabat -->
              <p class="text-[9.5px] sm:text-[10px] font-black underline uppercase text-[#142C0A]">{{ signerName }}</p>
              <p class="text-[8px] sm:text-[8.5px] font-bold text-[#142C0A]">{{ signerRank }}</p>
            </div>

            <!-- Sisi Pojok Kanan: QR Code Verifikasi Publik -->
            <div class="flex flex-col items-center">
              <a
                :href="verificationUrl"
                target="_blank"
                class="bg-white p-1 rounded-md border border-emerald-900/40 shadow-xs block hover:opacity-90 transition"
                title="Pindai / Buka Verifikasi Keaslian E-KTA"
              >
                <img :src="qrCodeUrl" class="w-13 h-13 sm:w-14 sm:h-14 object-contain" alt="QR Verifikasi" />
              </a>
            </div>
          </div>

        </div>
      </div>

      <!-- ================= SISI BELAKANG (BACK SIDE) ================= -->
      <div v-show="currentSide === 'back'" class="relative z-10 w-full h-full flex p-3.5 sm:p-4 gap-3">
        
        <!-- Sisi Kiri: SINYALEMEN PEMEGANG -->
        <div class="w-[58%] flex flex-col justify-between h-full pr-1">
          <div>
            <h3 class="text-[12px] sm:text-[13px] font-black tracking-wide uppercase text-[#142C0A] border-b-[1.5px] border-[#142C0A] pb-0.5 inline-block mb-1.5">
              SINYALEMEN
            </h3>

            <!-- Tabel Data Sinyalemen Fisik -->
            <div class="space-y-[3.5px] text-[9.5px] sm:text-[10.5px] font-bold text-[#142C0A]">
              <div class="flex items-baseline">
                <span class="w-28 shrink-0 font-extrabold">Tinggi/berat Badan</span>
                <span class="mr-1.5">:</span>
                <span>{{ sinyalmenData.tinggi_berat }}</span>
                <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
              </div>

              <div class="flex items-baseline">
                <span class="w-28 shrink-0 font-extrabold">Rambut</span>
                <span class="mr-1.5">:</span>
                <span>{{ sinyalmenData.rambut }}</span>
                <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
              </div>

              <div class="flex items-baseline">
                <span class="w-28 shrink-0 font-extrabold">Mata</span>
                <span class="mr-1.5">:</span>
                <span>{{ sinyalmenData.mata }}</span>
                <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
              </div>

              <div class="flex items-baseline">
                <span class="w-28 shrink-0 font-extrabold">Darah</span>
                <span class="mr-1.5">:</span>
                <span>{{ sinyalmenData.golongan_darah }}</span>
                <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
              </div>

              <div class="flex items-baseline">
                <span class="w-28 shrink-0 font-extrabold">Tempat Lahir</span>
                <span class="mr-1.5">:</span>
                <span>{{ sinyalmenData.tempat_lahir }}</span>
                <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
              </div>

              <div class="flex items-baseline">
                <span class="w-28 shrink-0 font-extrabold">Tgl Lahir</span>
                <span class="mr-1.5">:</span>
                <span>{{ sinyalmenData.tanggal_lahir }}</span>
                <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
              </div>

              <div class="flex items-baseline">
                <span class="w-28 shrink-0 font-extrabold">Agama</span>
                <span class="mr-1.5">:</span>
                <span>{{ sinyalmenData.agama }}</span>
                <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
              </div>

              <div class="flex flex-col mt-0.5">
                <div class="flex items-baseline">
                  <span class="w-28 shrink-0 font-extrabold">Alamat Rumah</span>
                  <span class="mr-1.5">:</span>
                  <span class="truncate">{{ sinyalmenData.alamat_line1 }}</span>
                  <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
                </div>
                <div v-if="sinyalmenData.alamat_line2" class="flex items-baseline pl-[118px]">
                  <span class="truncate">{{ sinyalmenData.alamat_line2 }}</span>
                  <span class="flex-1 border-b border-dotted border-[#244616]/60 ml-1 mb-0.5"></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Garis Pemisah Vertikal Hitam / Hijau Tua Tengah -->
        <div class="w-[1.5px] bg-[#244616]/50 h-full shrink-0"></div>

        <!-- Sisi Kanan: TANDA KEHORMATAN & TANDA TANGAN PEMEGANG -->
        <div class="w-[40%] flex flex-col justify-between h-full pl-1">
          <!-- Bagian Atas: Tanda Kehormatan -->
          <div class="h-[45%]">
            <h3 class="text-[11px] sm:text-[12px] font-black tracking-wide uppercase text-[#142C0A] border-b-[1.5px] border-[#142C0A] pb-0.5 inline-block mb-1.5">
              TANDA KEHORMATAN
            </h3>
            <div class="space-y-1 text-[9px] font-bold text-[#142C0A]">
              <p v-if="ekta?.tanda_kehormatan" class="leading-tight">{{ ekta.tanda_kehormatan }}</p>
              <template v-else>
                <div class="border-b border-dotted border-[#244616]/60 w-full h-3"></div>
                <div class="border-b border-dotted border-[#244616]/60 w-full h-3"></div>
                <div class="border-b border-dotted border-[#244616]/60 w-full h-3"></div>
              </template>
            </div>
          </div>

          <!-- Garis Pemisah Horizontal -->
          <div class="w-full h-[1.5px] bg-[#244616]/50 my-1"></div>

          <!-- Bagian Bawah: Tanda Tangan Pemegang -->
          <div class="h-[50%] flex flex-col justify-between">
            <h3 class="text-[11px] sm:text-[12px] font-black tracking-wide uppercase text-[#142C0A] border-b-[1.5px] border-[#142C0A] pb-0.5 inline-block">
              TANDA TANGAN PEMEGANG
            </h3>

            <!-- Ruang Tanda Tangan Pemegang -->
            <div class="flex-1 flex items-center justify-center p-1">
              <svg class="h-9 w-28 text-slate-900" viewBox="0 0 100 40" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M15 28 C25 8, 30 38, 45 18 C55 8, 60 22, 75 14 C85 10, 88 30, 92 24" stroke-linecap="round"/>
                <path d="M40 26 L80 28" stroke-width="1.2"/>
              </svg>
            </div>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import KomcadEmblem from '@/Components/KomcadEmblem.vue';

const props = defineProps({
  personel: Object,
  ekta: Object,
  settings: Object,
});

// Helper URL Stream Privat Berkas & Pasfoto (Aman dari 404 Nginx aaPanel)
const getPhotoUrl = (path, name = 'PERS') => {
  if (!path) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name || 'PERS')}&background=e2e8f0&color=334155`;
  }
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:') || path.startsWith('blob:')) {
    return path;
  }
  const clean = path.replace(/^(app\/private\/|app\/public\/|app\/|private\/|storage\/|public\/|\/storage\/|\/admin\/|admin\/)+/, '');
  return `/documents/private-stream?path=${encodeURIComponent(clean)}`;
};

const getAssetUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:') || path.startsWith('blob:')) {
    return path;
  }
  const clean = path.replace(/^(app\/private\/|app\/public\/|app\/|private\/|storage\/|public\/|\/storage\/|\/admin\/|admin\/)+/, '');
  return `/documents/private-stream?path=${encodeURIComponent(clean)}`;
};

const currentSide = ref('front');

const flipCard = () => {
  currentSide.value = currentSide.value === 'front' ? 'back' : 'front';
};

const printCard = () => {
  window.print();
};

// Format Nomor KTA: Jika belum terbit, tampilkan No. ...../KTA KC/[tahun]
const displayNomorKta = computed(() => {
  if (props.ekta?.nomor_kta) {
    return props.ekta.nomor_kta;
  }
  const tahunLulus = props.personel?.angkatan || new Date().getFullYear();
  return `No. ...../KTA KC/${tahunLulus}`;
});

// Pangkat
const displayPangkat = computed(() => {
  if (props.ekta?.pangkat) return props.ekta.pangkat;
  if (props.personel?.pangkat) return props.personel.pangkat;
  return 'Prajurit Komcad';
});

// Jabatan
const displayJabatan = computed(() => {
  if (props.ekta?.jabatan) return props.ekta.jabatan;
  const p = (displayPangkat.value || '').toUpperCase();
  if (p.includes('LET') || p.includes('KAP') || p.includes('MAY') || p.includes('KOL')) {
    return 'Perwira Komcad';
  }
  return 'Anggota Komcad';
});

// Kesatuan / Matra
const displayMatra = computed(() => {
  if (props.ekta?.kesatuan_matra) return props.ekta.kesatuan_matra;
  const m = (props.personel?.matra || 'AD').toUpperCase();
  if (m === 'AL') return 'Matra Laut';
  if (m === 'AU') return 'Matra Udara';
  return 'Matra Darat';
});

// Latar Belakang Matra untuk Pasfoto
const matraBgClass = computed(() => {
  const m = (props.personel?.matra || 'AD').toUpperCase();
  if (m === 'AL') return 'bg-[#1E3A8A]'; // Biru Tua AL
  if (m === 'AU') return 'bg-[#0284C7]'; // Biru Muda AU
  return 'bg-[#991B1B]'; // Merah AD
});

// Berlaku s/d
const displayBerlaku = computed(() => {
  return props.ekta?.berlaku_sampai || 'Selama Menjadi Anggota Komcad';
});

// Data Pejabat Penandatangan
const signerName = computed(() => props.settings?.signer_name || 'Sri Yanto, S.T.');
const signerRank = computed(() => props.settings?.signer_rank || 'Laksamana Muda TNI');
const signerTitle = computed(() => props.settings?.signer_title || 'Direktur Jenderal Potensi Pertahanan');

const signerTitleLine1 = computed(() => {
  const title = signerTitle.value;
  if (title.toLowerCase().includes('potensi')) {
    return 'Direktur Jenderal';
  }
  return title;
});

const signerTitleLine2 = computed(() => {
  const title = signerTitle.value;
  if (title.toLowerCase().includes('potensi')) {
    return 'Potensi Pertahanan';
  }
  return '';
});

// URL Verifikasi Publik dan QR Code
const verifyCode = computed(() => {
  return props.ekta?.verify_code || `KTA-${props.personel?.angkatan || 2025}-${props.personel?.nikc?.slice(-6) || 'VERIF'}`;
});

const verificationUrl = computed(() => {
  if (typeof window !== 'undefined') {
    return `${window.location.origin}/verifikasi/ekta/${verifyCode.value}`;
  }
  return `/verifikasi/ekta/${verifyCode.value}`;
});

const qrCodeUrl = computed(() => {
  return `https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=0&data=${encodeURIComponent(verificationUrl.value)}`;
});

// Data Sinyalemen Belakang
const sinyalmenData = computed(() => {
  const sin = props.personel?.sinyalmen;
  const ek = props.ekta;

  // Alamat dipecah jadi 2 baris jika panjang
  const fullAddress = ek?.alamat || props.personel?.address || 'Jl. Mabes Komcad RI';
  let addr1 = fullAddress;
  let addr2 = '';
  if (fullAddress.length > 32) {
    const splitIndex = fullAddress.lastIndexOf(' ', 32);
    if (splitIndex !== -1) {
      addr1 = fullAddress.substring(0, splitIndex);
      addr2 = fullAddress.substring(splitIndex + 1);
    }
  }

  // Tanggal Lahir
  let tglLahir = ek?.tanggal_lahir || '-';
  if (!ek?.tanggal_lahir && props.personel?.dob) {
    try {
      tglLahir = new Date(props.personel.dob).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    } catch {
      tglLahir = props.personel.dob;
    }
  }

  return {
    tinggi_berat: ek?.tinggi_berat || (sin?.tinggi_badan && sin?.berat_badan ? `${sin.tinggi_badan}/${sin.berat_badan}` : '165/60'),
    rambut: ek?.rambut || sin?.rambut || 'Bergelombang',
    mata: ek?.mata || sin?.mata || 'Coklat',
    golongan_darah: ek?.golongan_darah || sin?.golongan_darah || 'O',
    tempat_lahir: ek?.tempat_lahir || props.personel?.pob || '-',
    tanggal_lahir: tglLahir,
    agama: ek?.agama || 'Islam',
    alamat_line1: addr1,
    alamat_line2: addr2,
  };
});
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #printable-ekta, #printable-ekta * {
    visibility: visible;
  }
  #printable-ekta {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%) scale(1.1);
    box-shadow: none !important;
  }
}
</style>
