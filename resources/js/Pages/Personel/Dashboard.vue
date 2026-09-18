<template>
  <AuthenticatedLayout>
    <!-- Pemanggilan Slot Sidebar Khusus Personel Komcad -->
    <template #sidebar-menu>
      <Link :href="route('personel.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Dashboard Saya</Link>
      <Link :href="route('personel.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Kegiatan & Mobilisasi</Link>
      <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Pengaturan Akun</Link>
    </template>

    <template #header-title>Dashboard Kontrol Personel</template>

    <div class="space-y-6 max-w-5xl">
      
      <!-- PEMBERITAHUAN FORMULIR & REKRUTMEN TERBUKA UNTUK PERSONEL INI -->
      <div v-if="activeForms && activeForms.length > 0" class="space-y-3">
        <div 
          v-for="af in activeForms" 
          :key="af.id"
          class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 rounded-3xl p-5 text-white shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 border border-blue-400/30"
        >
          <div class="flex items-center gap-3.5 text-left w-full sm:w-auto">
            <div class="w-11 h-11 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/20">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <div class="space-y-0.5 min-w-0 flex-1">
              <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase bg-white/20 tracking-wider">
                {{ af.category }} TERBUKA
              </span>
              <h4 class="text-xs sm:text-sm font-extrabold text-white truncate">{{ af.title }}</h4>
              <p class="text-[10px] text-blue-100 font-medium">
                Tenggat: <strong class="text-white">{{ af.deadline ? formatDate(af.deadline) : 'Tidak dibatasi' }}</strong>
              </p>
            </div>
          </div>

          <Link 
            :href="route('personel.form.show', af.uuid)"
            class="shrink-0 w-full sm:w-auto text-center px-4 py-2 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-xs font-extrabold shadow-sm transition"
          >
            Isi Formulir
          </Link>
        </div>
      </div>

      <!-- 1. TAMPILAN KHUSUS HP / SELULER (lg:hidden) -->
      <div class="lg:hidden space-y-5">
        
        <!-- Header Profil Aplikasi Seluler -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-5 text-white shadow-xl flex items-center gap-4 relative overflow-hidden">
          <div class="absolute -right-10 -top-10 w-28 h-28 rounded-full bg-blue-600/20 blur-2xl"></div>
          <div class="absolute -left-10 -bottom-10 w-28 h-28 rounded-full bg-indigo-500/20 blur-2xl"></div>

          <img :src="personel.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" class="w-14 h-14 object-cover rounded-2xl border-2 border-white/20 shadow-md shrink-0 z-10" />
          <div class="space-y-0.5 z-10 flex-1 min-w-0">
            <p class="text-blue-400 font-extrabold tracking-widest text-[8px] uppercase">KOMPONEN CADANGAN</p>
            <h2 class="text-xs font-medium text-slate-300">Selamat Datang,</h2>
            <h3 class="text-sm font-extrabold text-white truncate">{{ formatPangkat(personel.pangkat) }} {{ personel.full_name }}</h3>
            <p class="text-[9px] text-slate-400 font-semibold mt-1">
              {{ formatPangkat(personel.pangkat) }} | TNI {{ personel.matra }}
            </p>
          </div>
        </div>

        <!-- Grid Menu Pintasan 6 Kolom ala Aplikasi Seluler Militer -->
        <div class="grid grid-cols-6 gap-1 bg-white p-3 rounded-3xl border border-[#E2E8F0] shadow-xs">
          <!-- Pintasan 1: Dashboard (Portal Saya) -->
          <Link :href="route('personel.dashboard')" class="flex flex-col items-center justify-center p-1 rounded-2xl hover:bg-slate-50 transition text-center group cursor-pointer">
            <div class="w-9 h-9 rounded-2xl bg-blue-50 text-[#2563EB] flex items-center justify-center shadow-xs border border-blue-100/30 group-hover:scale-105 transition duration-200 select-none">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
              </svg>
            </div>
            <span class="text-[8px] font-bold text-slate-600 mt-1.5 tracking-tight leading-tight">Portal</span>
          </Link>

          <!-- Pintasan 2: Riwayat Pekerjaan -->
          <Link :href="route('personel.job.index')" class="flex flex-col items-center justify-center p-1 rounded-2xl hover:bg-slate-50 transition text-center group cursor-pointer">
            <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs border border-emerald-100/30 group-hover:scale-105 transition duration-200 select-none">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 .621-.504 1.125-1.125 1.125H4.875c-.621 0-1.125-.504-1.125-1.125v-4.25m16.5 0a2.25 2.25 0 00-2.25-2.25H5.625a2.25 2.25 0 00-2.25 2.25m16.5 0v-1.5A3.375 3.375 0 0017.25 9h-2.625M3.75 14.15v-1.5A3.375 3.375 0 016.75 9h2.625m.002-2.25a3.375 3.375 0 013.373-3.375h1.5a3.375 3.375 0 013.375 3.375v2.25m-8.25 0h8.25" />
              </svg>
            </div>
            <span class="text-[8px] font-bold text-slate-600 mt-1.5 tracking-tight leading-tight">Pekerjaan</span>
          </Link>

          <!-- Pintasan 3: Broadcast Kegiatan -->
          <Link :href="route('personel.broadcast.index')" class="flex flex-col items-center justify-center p-1 rounded-2xl hover:bg-slate-50 transition text-center group cursor-pointer">
            <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-xs border border-amber-100/30 group-hover:scale-105 transition duration-200 select-none">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
              </svg>
            </div>
            <span class="text-[8px] font-bold text-slate-600 mt-1.5 tracking-tight leading-tight">Kegiatan</span>
          </Link>

          <!-- Pintasan 4: Riwayat Pendidikan -->
          <Link :href="route('personel.education.index')" class="flex flex-col items-center justify-center p-1 rounded-2xl hover:bg-slate-50 transition text-center group cursor-pointer">
            <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-xs border border-purple-100/30 group-hover:scale-105 transition duration-200 select-none">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A5.905 5.905 0 018 3.097V5.25m15.482 4.897a50.57 50.57 0 012.658-.813c.852-.162 1.48-.901 1.48-1.767V5.25m0 0a5.905 5.905 0 00-5.88-5.25h-1.5a5.905 5.905 0 00-5.88 5.25m13.26 0v2.25" />
              </svg>
            </div>
            <span class="text-[8px] font-bold text-slate-600 mt-1.5 tracking-tight leading-tight">Pendidikan</span>
          </Link>

          <!-- Pintasan 5: Formulir & Rekrutmen -->
          <Link :href="route('personel.form.index')" class="flex flex-col items-center justify-center p-1 rounded-2xl hover:bg-slate-50 transition text-center group cursor-pointer">
            <div class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-xs border border-indigo-100/30 group-hover:scale-105 transition duration-200 select-none">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <span class="text-[8px] font-bold text-slate-600 mt-1.5 tracking-tight leading-tight">Formulir</span>
          </Link>

          <!-- Pintasan 6: Pusat Layanan Informasi -->
          <Link :href="route('personel.chat.index')" class="flex flex-col items-center justify-center p-1 rounded-2xl hover:bg-slate-50 transition text-center group cursor-pointer">
            <div class="w-9 h-9 rounded-2xl bg-sky-50 text-[#2563EB] flex items-center justify-center shadow-xs border border-sky-100/30 group-hover:scale-105 transition duration-200 select-none">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <span class="text-[8px] font-bold text-slate-600 mt-1.5 tracking-tight leading-tight">Layanan Info</span>
          </Link>
        </div>

        <!-- Agregasi Statistik Ringkas di HP -->
        <div class="grid grid-cols-3 gap-3">
          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3 text-center shadow-xs">
            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Kegiatan</p>
            <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ stats.total_kegiatan }}</p>
          </div>
          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3 text-center shadow-xs">
            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Siap Hadir</p>
            <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ stats.total_hadir }}</p>
          </div>
          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-3 text-center shadow-xs">
            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Izin Dinas</p>
            <p class="text-sm font-extrabold text-slate-800 mt-0.5">{{ stats.total_izin }}</p>
          </div>
        </div>

        <!-- Pengumuman Singkat Kegiatan Terbaru di HP -->
        <div class="bg-white border border-[#E2E8F0] rounded-3xl overflow-hidden shadow-xs">
          <div class="px-5 py-4 border-b border-[#E2E8F0] bg-slate-50/50 flex items-center justify-between">
            <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
              </svg>
              <span>Pengumuman Terbaru</span>
            </h4>
            <Link :href="route('personel.broadcast.index')" class="text-[10px] font-bold text-[#2563EB] hover:underline">Lihat Semua</Link>
          </div>

          <div class="divide-y divide-[#E2E8F0] text-xs">
            <template v-if="latestBroadcasts && latestBroadcasts.length > 0">
              <Link 
                v-for="item in latestBroadcasts" 
                :key="item.id" 
                :href="route('personel.broadcast.show', item.uuid)"
                class="block p-4 hover:bg-slate-50/40 transition group cursor-pointer"
              >
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-100/20">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <div class="space-y-1 flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                      <span class="px-2 py-0.5 rounded text-[8px] font-extrabold uppercase bg-slate-100 text-slate-600 tracking-wider">
                        {{ item.category }}
                      </span>
                      <span class="text-[9px] text-slate-400 font-medium">{{ formatDate(item.event_date) }}</span>
                    </div>
                    <h5 class="font-bold text-slate-800 group-hover:text-[#2563EB] transition truncate">{{ item.title }}</h5>
                    <p class="text-slate-500 line-clamp-2 leading-relaxed mt-0.5">{{ item.description }}</p>
                  </div>
                </div>
              </Link>
            </template>
            <div v-else class="p-8 text-center text-slate-400 italic">
              Belum ada pengumuman kegiatan baru saat ini.
            </div>
          </div>
        </div>

        <!-- Banner Promosi/Petunjuk di HP (Marquee) -->
        <div class="bg-blue-900 border border-blue-800 rounded-2xl p-3 shadow-xs overflow-hidden flex items-center gap-2 text-white">
          <span class="text-[10px] select-none shrink-0 font-extrabold text-blue-200 uppercase tracking-wider">Warta Dinas:</span>
          <marquee class="text-[10px] font-semibold text-blue-100 flex-1" scrollamount="3">
            Pusat Informasi & Layanan SISFOPERSKC Komponen Cadangan RI. Selalu verifikasi data diri dan kehadiran latihan Anda secara berkala.
          </marquee>
        </div>

      </div>

      <!-- 2. TAMPILAN KHUSUS LAPTOP / DESKTOP (hidden lg:block) -->
      <div class="hidden lg:block space-y-6">
        
        <!-- CARD BANNER: SALAM KOMANDO & STATUS UTAMA -->
        <div class="bg-gradient-to-r from-[#2563EB] to-[#1D4ED8] rounded-2xl p-6 text-white shadow-md shadow-blue-500/10 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="space-y-1 text-center sm:text-left">
            <h2 class="text-lg font-bold tracking-wide">Selamat Datang Kembali, {{ formatPangkat(personel.pangkat) }} {{ personel.full_name }}</h2>
            <p class="text-xs text-blue-100 font-medium">
              Pangkat: <span class="uppercase font-bold">{{ formatPangkat(personel.pangkat) }}</span> |
              Matra: TNI {{ personel.matra }} | 
              Status Profil: <span class="bg-green-500 text-white px-1.5 py-0.5 rounded text-[10px] font-bold ml-1">AKTIF</span>
            </p>
          </div>
          <div class="shrink-0 select-none">
            <span class="bg-white/10 backdrop-blur-md px-3 py-1.5 rounded-xl border border-white/10 text-xs font-mono font-bold uppercase tracking-wider">
              NIKC: {{ personel.nikc || '-' }}
            </span>
          </div>
        </div>

        <!-- KARTU GRID STATISTIK PRESENSI KEGIATAN OPERASIONAL -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
          <!-- Boks 1: Total Perintah Mobilisasi -->
          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 flex items-center gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center select-none">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kegiatan</p>
              <p class="text-xl font-extrabold text-slate-800 mt-0.5">{{ stats.total_kegiatan }}</p>
            </div>
          </div>

          <!-- Boks 2: Siap Hadir Laksana -->
          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 flex items-center gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center select-none">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siap Hadir</p>
              <p class="text-xl font-extrabold text-slate-800 mt-0.5">{{ stats.total_hadir }}</p>
            </div>
          </div>

          <!-- Boks 3: Izin Dinas -->
          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 flex items-center gap-4 shadow-xs">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center select-none">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Izin Dinas</p>
              <p class="text-xl font-extrabold text-slate-800 mt-0.5">{{ stats.total_izin }}</p>
            </div>
          </div>
        </div>

        <!-- LAYOUT UTAMA: SPILIT INFORMASI 360° DATA DIRI & SINYALMEN -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- PANEL SEBELAH KIRI: BIODATA ADMINISTRASI LENGKAP -->
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs overflow-hidden">
              <div class="p-5 border-b border-[#E2E8F0] bg-slate-50/60 flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Lembar Administrasi Domisili & Identitas</h4>
              </div>
              
              <div class="p-6 space-y-5 text-xs">
                <div class="flex flex-col sm:flex-row gap-5 items-center sm:items-start bg-slate-50/50 p-4 border border-[#E2E8F0] rounded-xl">
                  <img :src="personel.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" class="w-20 h-26 object-cover rounded-xl border border-[#E2E8F0] bg-white shadow-xs" />
                  <div class="space-y-1.5 text-center sm:text-left">
                    <h5 class="text-sm font-bold text-slate-800">{{ personel.full_name }}</h5>
                    <p class="text-slate-400 font-medium">Nomor Induk Kependudukan: <span class="text-slate-700 font-bold">{{ personel.nik }}</span></p>
                    <p class="text-slate-400 font-medium">WhatsApp Aktif: <span class="text-slate-700 font-bold">{{ personel.phone_number }}</span></p>
                    <p class="text-slate-400 font-medium">Otoritas Komparasi Wajah: <span class="text-green-600 font-bold">Terverifikasi (Face Verified)</span></p>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                  <div class="p-3 bg-white border border-[#E2E8F0] rounded-xl">
                    <p class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Komando Utama (Kotama)</p>
                    <p class="font-bold text-blue-700 mt-1">{{ personel.kotama || '-' }}</p>
                  </div>
                  <div class="p-3 bg-white border border-[#E2E8F0] rounded-xl">
                    <p class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Satuan Kewilayahan</p>
                    <p class="font-bold text-blue-700 mt-1">{{ personel.satuan_kewilayahan || '-' }}</p>
                  </div>
                  <div class="p-3 bg-white border border-[#E2E8F0] rounded-xl">
                    <p class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Tempat, Tanggal Lahir</p>
                    <p class="font-semibold text-slate-800 mt-1">{{ personel.pob }}, {{ formatDate(personel.dob) }}</p>
                  </div>
                  <div class="p-3 bg-white border border-[#E2E8F0] rounded-xl">
                    <p class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Jenis Kelamin / Gender</p>
                    <p class="font-semibold text-slate-800 mt-1">{{ personel.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                  </div>
                  <div class="p-3 bg-white border border-[#E2E8F0] rounded-xl sm:col-span-2">
                    <p class="text-slate-400 font-bold uppercase tracking-wide text-[10px]">Alamat Lengkap Sesuai Berkas Sipil</p>
                    <p class="font-semibold text-slate-800 mt-1 leading-relaxed uppercase">
                      {{ personel.address }}, KEL. {{ personel.village }}, KEC. {{ personel.district }}, {{ personel.city }}, PROV. {{ personel.province }} (KODE POS: {{ personel.postal_code || '-' }})
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PANEL SEBELAH KANAN: PARAMETER DATA FISIK (SINYALMEN) -->
          <div class="space-y-6">
            <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs overflow-hidden">
              <div class="p-5 border-b border-[#E2E8F0] bg-slate-50/60">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Kriptografi Ciri Fisik (Sinyalmen)</h4>
              </div>

              <div class="p-5 text-xs space-y-4">
                <div v-if="personel.sinyalmen" class="space-y-3.5">
                  <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 border border-[#E2E8F0] rounded-xl">
                      <p class="text-slate-400 text-[10px] font-bold uppercase">Tinggi</p>
                      <p class="font-extrabold text-slate-800 text-sm mt-0.5">{{ personel.sinyalmen.tinggi_badan }} cm</p>
                    </div>
                    <div class="p-3 bg-slate-50 border border-[#E2E8F0] rounded-xl">
                      <p class="text-slate-400 text-[10px] font-bold uppercase">Berat</p>
                      <p class="font-extrabold text-slate-800 text-sm mt-0.5">{{ personel.sinyalmen.berat_badan }} kg</p>
                    </div>
                  </div>

                  <div class="p-3 bg-slate-50 border border-[#E2E8F0] rounded-xl flex justify-between items-center">
                    <p class="text-slate-400 text-[10px] font-bold uppercase">Golongan Darah</p>
                    <span class="px-2 py-0.5 rounded-md font-extrabold bg-red-50 text-red-600 border border-red-100 text-xs">{{ personel.sinyalmen.golongan_darah || '-' }}</span>
                  </div>

                  <div class="p-3 bg-slate-50 border border-[#E2E8F0] rounded-xl">
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-1">Kondisi Rambut & Mata</p>
                    <p class="font-medium text-slate-700">Rambut: <span class="font-bold text-slate-800">{{ personel.sinyalmen.rambut || '-' }}</span></p>
                    <p class="font-medium text-slate-700 mt-0.5">Retina Mata: <span class="font-bold text-slate-800">{{ personel.sinyalmen.mata || '-' }}</span></p>
                  </div>

                  <div class="p-3 bg-slate-50 border border-[#E2E8F0] rounded-xl">
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-0.5">Ciri Khas Khusus Paten</p>
                    <p class="font-semibold text-slate-800 leading-relaxed">{{ personel.sinyalmen.ciri_khas || '-' }}</p>
                  </div>

                  <div class="p-3 bg-slate-50 border border-[#E2E8F0] rounded-xl">
                    <p class="text-slate-400 text-[10px] font-bold uppercase mb-0.5">Catatan Anatomis Cacat Tubuh</p>
                    <p :class="personel.sinyalmen.cacat_tubuh && personel.sinyalmen.cacat_tubuh !== 'Tidak Ada' ? 'text-amber-600' : 'text-slate-800'" class="font-semibold leading-relaxed">
                      {{ personel.sinyalmen.cacat_tubuh || 'Tidak Ada / Sehat Walafiat' }}
                    </p>
                  </div>
                </div>

                <!-- Bukti Safety Catcher jika relasi model bermasalah -->
                <div v-else class="text-center p-8 text-slate-400 italic font-medium">
                  Lembar riwayat sinyalmen fisik belum ter-record.
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- PAPAN PETUNJUK OPERASIONAL -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs space-y-4">
          <div class="border-b border-[#E2E8F0] pb-3">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Papan Petunjuk Operasional</h3>
            <p class="text-xs text-slate-400 mt-0.5">Panduan penugasan berkala anggota Komponen Cadangan Sisfopers.</p>
          </div>

          <div class="text-xs text-slate-600 space-y-3 leading-relaxed">
            <div class="flex items-start gap-2.5">
              <svg class="w-3.5 h-3.5 text-blue-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="4" />
              </svg>
              <p><strong>Respons Instruksi Pimpinan:</strong> Pastikan Anda selalu memantau menu <Link :href="route('personel.broadcast.index')" class="text-[#2563EB] font-bold hover:underline">Kegiatan & Mobilisasi</Link> untuk melakukan konfirmasi presensi setiap ada perintah apel atau latihan baru dari pusat komando.</p>
            </div>
            <div class="flex items-start gap-2.5">
              <svg class="w-3.5 h-3.5 text-blue-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="4" />
              </svg>
              <p><strong>Integritas Pusat Akun:</strong> Jaga keamanan sandi masuk Anda secara berkala, dan tautkan pengaman kriptografi **Google Authenticator (M2FA)** melalui sub-menu pengaturan akun demi kerahasiaan data operasional instansi.</p>
            </div>
          </div>
        </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

// Menerima data reaktif komplit dari DashboardPersonelController
defineProps({
  personel: Object,
  stats: Object,
  latestBroadcasts: Array,
  activeForms: Array,
});

const formatDate = (dateString) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const formatPangkat = (pangkatStr) => {
  if (!pangkatStr) return '-';
  if (pangkatStr.toUpperCase().includes('KC')) {
    return pangkatStr;
  }
  return `${pangkatStr} KC`;
};
</script>
