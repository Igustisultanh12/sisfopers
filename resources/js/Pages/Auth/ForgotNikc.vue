<template>
  <div 
    class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 font-sans bg-cover bg-center relative transition-all duration-300 flex items-center justify-center"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay saat background gambar aktif -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>

    <div 
      class="max-w-3xl w-full mx-auto rounded-2xl shadow-2xl transition-all duration-300 relative z-10 overflow-hidden"
      :class="settings?.login_background 
        ? 'bg-slate-900/60 backdrop-blur-md border border-white/10 text-white' 
        : 'bg-white border border-[#E2E8F0] shadow-slate-200/50 text-[#334155]'"
    >
      <!-- Header Layanan Lupa NIKC -->
      <div 
        class="p-6 sm:p-8 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-300"
        :class="settings?.login_background 
          ? 'border-white/10 bg-white/5' 
          : 'border-[#E2E8F0] bg-gradient-to-r from-[#2563EB]/5 to-transparent'"
      >
        <div class="flex items-center gap-4">
          <img v-if="settings?.logo_tni" :src="settings.logo_tni" class="h-12 object-contain drop-shadow" alt="Logo TNI" />
          <div v-else-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center gap-2">
            <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-9 object-contain" alt="AD" />
            <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-9 object-contain" alt="AL" />
            <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-9 object-contain" alt="AU" />
          </div>
          <div>
            <h1 class="text-lg sm:text-xl font-bold uppercase tracking-wide" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
              Layanan Bantuan NIKC
            </h1>
            <p class="text-xs mt-0.5" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
              Pengecekan Nomor Induk Komponen Cadangan (NIKC) Terdaftar
            </p>
          </div>
        </div>

        <Link 
          :href="route('login')" 
          class="inline-flex items-center gap-1.5 text-xs font-semibold hover:underline transition self-start sm:self-center"
          :class="settings?.login_background ? 'text-orange-400 hover:text-orange-300' : 'text-[#2563EB] hover:text-blue-800'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Kembali ke Login
        </Link>
      </div>

      <!-- Konten Utama -->
      <div class="p-6 sm:p-8 space-y-6">

        <!-- KONDISI 1: Belum Melakukan Pencarian atau Sedang Mode Cari Ulang -->
        <div v-if="!activeResult || activeResult.status === 'NOT_FOUND'">
          <!-- Banner Pengumuman -->
          <div 
            class="p-4 sm:p-5 rounded-xl mb-6 text-xs leading-relaxed border transition-all"
            :class="settings?.login_background 
              ? 'bg-blue-950/40 border-blue-500/30 text-blue-200' 
              : 'bg-blue-50 border-blue-200 text-blue-900'"
          >
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 flex-shrink-0 text-[#2563EB]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="font-semibold mb-1">Informasi Layanan Bantuan NIKC:</p>
                <p>
                  Halaman ini diperuntukkan bagi personel Komponen Cadangan yang lupa NIKC. Masukkan Nama Lengkap, Tanggal Lahir, dan NIK 16 digit sesuai identitas resmi Anda. Data lengkap dan barcode e-KTA hanya dapat ditampilkan jika Anda telah memiliki akun terdaftar di sistem.
                </p>
              </div>
            </div>
          </div>

          <!-- Pesan Kesalahan / Not Found jika ada -->
          <div 
            v-if="activeResult?.status === 'NOT_FOUND'" 
            class="p-4 rounded-xl mb-6 text-xs font-medium border flex items-start gap-3 bg-red-500/15 border-red-500/30 text-red-300"
          >
            <svg class="w-5 h-5 flex-shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
              <p class="font-bold text-red-200">Data Tidak Cocok / Tidak Ditemukan</p>
              <p class="mt-1 text-red-300/90 leading-relaxed">{{ activeResult.message }}</p>
            </div>
          </div>

          <!-- Form Pencarian NIKC -->
          <form @submit.prevent="submitSearch" class="space-y-5">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                Nama Lengkap (Sesuai KTP / SKEP) <span class="text-red-400">*</span>
              </label>
              <input 
                type="text" 
                v-model="form.full_name" 
                class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2" 
                :class="settings?.login_background 
                  ? 'bg-white/10 border-white/15 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                  : 'bg-white border-[#E2E8F0] text-slate-800 focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
                placeholder="Contoh: I Gusti Sultan"
                required 
              />
              <p v-if="form.errors.full_name" class="text-xs text-red-400 mt-1 font-medium">{{ form.errors.full_name }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                  Tanggal Lahir <span class="text-red-400">*</span>
                </label>
                <input 
                  type="date" 
                  v-model="form.dob" 
                  class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2" 
                  :class="settings?.login_background 
                    ? 'bg-white/10 border-white/15 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                    : 'bg-white border-[#E2E8F0] text-slate-800 focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
                  required 
                />
                <p v-if="form.errors.dob" class="text-xs text-red-400 mt-1 font-medium">{{ form.errors.dob }}</p>
              </div>

              <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-2" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                  Nomor Induk Kependudukan (NIK) <span class="text-red-400">*</span>
                </label>
                <input 
                  type="text" 
                  v-model="form.nik" 
                  maxlength="16"
                  inputmode="numeric"
                  class="w-full px-4 py-2.5 rounded-lg border text-sm outline-none transition duration-150 focus:ring-2" 
                  :class="settings?.login_background 
                    ? 'bg-white/10 border-white/15 text-white placeholder-slate-400 focus:ring-white/20 focus:border-white' 
                    : 'bg-white border-[#E2E8F0] text-slate-800 focus:ring-[#2563EB]/20 focus:border-[#2563EB]'"
                  placeholder="16 Digit Angka NIK"
                  required 
                />
                <p v-if="form.errors.nik" class="text-xs text-red-400 mt-1 font-medium">{{ form.errors.nik }}</p>
              </div>
            </div>

            <div class="pt-2">
              <button 
                type="submit" 
                :disabled="form.processing" 
                class="w-full py-3 px-4 text-white text-sm font-semibold rounded-lg shadow-lg transition duration-150 disabled:opacity-50 cursor-pointer flex justify-center items-center gap-2"
                :class="settings?.login_background 
                  ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' 
                  : 'bg-[#2563EB] hover:bg-[#1E40AF] shadow-blue-500/10'"
              >
                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>{{ form.processing ? 'Memproses Pengecekan...' : 'Cek Status & Cari NIKC' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- KONDISI 2: Data Personel Ditemukan Namun BELUM MEMILIKI AKUN SISTEM -->
        <div v-else-if="activeResult?.status === 'FOUND_WITHOUT_ACCOUNT'" class="space-y-6">
          <div 
            class="p-6 rounded-2xl border text-center space-y-5 transition-all duration-300"
            :class="settings?.login_background ? 'bg-amber-500/10 border-amber-500/30' : 'bg-amber-50 border-amber-200'"
          >
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-500/20 text-amber-500 mx-auto">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>

            <div>
              <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 mb-2">
                Identitas Ditemukan - Akun Belum Aktif
              </span>
              <h2 class="text-lg font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
                Hasil Verifikasi Data Personel
              </h2>
            </div>

            <!-- Tampilan Data Disensor Sesuai Ketentuan -->
            <div 
              class="max-w-md mx-auto p-4 rounded-xl text-left space-y-3 border"
              :class="settings?.login_background ? 'bg-slate-900/70 border-white/10' : 'bg-white border-slate-200'"
            >
              <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Personel (Disensor)</label>
                <p class="font-mono text-base font-bold tracking-wide mt-0.5" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
                  {{ activeResult.masked_name }}
                </p>
              </div>
              <div class="border-t pt-2" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">NIKC (Disensor)</label>
                <p class="font-mono text-base font-bold tracking-wider mt-0.5 text-amber-400">
                  NIKC : {{ activeResult.masked_nikc }}
                </p>
              </div>
            </div>

            <!-- Peringatan Wajib Dari Sistem -->
            <div class="max-w-lg mx-auto p-4 rounded-xl bg-red-500/15 border border-red-500/30 text-red-200 text-xs font-semibold leading-relaxed">
              <p>Mohon maaf permintaan tidak dapat diproses dikarenakan anda masih belum memiliki akun.</p>
            </div>

            <p class="text-xs max-w-md mx-auto leading-relaxed" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
              Data Anda telah terdaftar dalam basis data Komponen Cadangan, namun Anda belum membuat akun pengguna internal. Silakan lakukan pendaftaran akun terlebih dahulu agar dapat mengelola data personel dan mencetak e-KTA.
            </p>

            <!-- Tombol Aksi -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
              <Link 
                :href="route('register')" 
                class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-lg text-white shadow transition flex items-center justify-center gap-2"
                :class="settings?.login_background ? 'bg-orange-500 hover:bg-orange-600' : 'bg-[#2563EB] hover:bg-[#1E40AF]'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Daftar Akun Baru Sekarang
              </Link>

              <button 
                type="button" 
                @click="resetSearch" 
                class="w-full sm:w-auto px-5 py-2.5 text-xs font-semibold rounded-lg border transition hover:bg-white/10"
                :class="settings?.login_background ? 'border-white/20 text-slate-200' : 'border-slate-300 text-slate-700 hover:bg-slate-50'"
              >
                Cari Kembali
              </button>
            </div>
          </div>
        </div>

        <!-- KONDISI 3: Data Personel Ditemukan & SUDAH MEMILIKI AKUN SISTEM (Tampilkan Lengkap) -->
        <div v-else-if="activeResult?.status === 'FOUND_WITH_ACCOUNT'" class="space-y-6">
          <div 
            class="p-6 sm:p-8 rounded-2xl border transition-all duration-300 space-y-6"
            :class="settings?.login_background ? 'bg-white/5 border-emerald-500/30' : 'bg-white border-emerald-200 shadow-md'"
          >
            <!-- Badge Status Sukses -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b gap-3" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
              <div class="flex items-center gap-2">
                <span class="flex h-3 w-3 relative">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">
                  Identitas Terverifikasi & Akun Aktif
                </span>
              </div>
              <span class="text-[11px] font-medium" :class="settings?.login_background ? 'text-slate-400' : 'text-slate-500'">
                Matra {{ activeResult.matra || 'AD' }} - Angkatan {{ activeResult.angkatan }}
              </span>
            </div>

            <!-- Bagian Profil & NIKC -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
              <!-- Foto Personel -->
              <div class="flex flex-col items-center text-center">
                <div class="relative w-32 h-40 rounded-xl overflow-hidden border-2 shadow-lg bg-slate-800" :class="settings?.login_background ? 'border-white/20' : 'border-blue-500/40'">
                  <img 
                    :src="activeResult.photo_url || defaultAvatar" 
                    alt="Foto Profil" 
                    class="w-full h-full object-cover"
                    @error="handleImageError"
                  />
                  <div class="absolute bottom-0 inset-x-0 bg-slate-950/80 backdrop-blur py-1 px-2 text-[10px] font-bold text-center text-white">
                    {{ activeResult.pangkat || 'Prajurit Komcad' }}
                  </div>
                </div>
              </div>

              <!-- Data Teks Personel Lengkap -->
              <div class="md:col-span-2 space-y-4">
                <div>
                  <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama Lengkap</label>
                  <p class="text-xl font-extrabold tracking-wide uppercase mt-0.5" :class="settings?.login_background ? 'text-white' : 'text-slate-900'">
                    {{ activeResult.full_name }}
                  </p>
                </div>

                <!-- Box Khusus NIKC dengan tombol Salin -->
                <div 
                  class="p-3.5 rounded-xl border flex items-center justify-between gap-3"
                  :class="settings?.login_background ? 'bg-slate-950/60 border-orange-500/40' : 'bg-blue-50/70 border-blue-200'"
                >
                  <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-orange-400">
                      NOMOR INDUK KOMPONEN CADANGAN (NIKC)
                    </label>
                    <p class="font-mono text-lg font-extrabold tracking-wider mt-0.5" :class="settings?.login_background ? 'text-white' : 'text-[#2563EB]'">
                      {{ activeResult.nikc }}
                    </p>
                  </div>
                  <button 
                    type="button" 
                    @click="copyNikc" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition shadow"
                    :class="copied ? 'bg-emerald-600 text-white' : (settings?.login_background ? 'bg-white/15 hover:bg-white/25 text-white' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300')"
                  >
                    <svg v-if="copied" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    <span>{{ copied ? 'Tersalin' : 'Salin NIKC' }}</span>
                  </button>
                </div>

                <!-- Alamat & Angkatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                  <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Angkatan</label>
                    <p class="font-semibold mt-0.5" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                      {{ activeResult.angkatan || '-' }}
                    </p>
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor KTA</label>
                    <p class="font-semibold mt-0.5 font-mono" :class="settings?.login_background ? 'text-slate-200' : 'text-slate-700'">
                      {{ activeResult.nomor_kta || '[Belum Diterbitkan]' }}
                    </p>
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat Lengkap</label>
                    <p class="font-medium mt-0.5 leading-relaxed" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-600'">
                      {{ activeResult.address || '-' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Barcode / Kode QR e-KTA -->
            <div 
              class="p-4 sm:p-5 rounded-xl border flex flex-col sm:flex-row items-center gap-5 transition-all"
              :class="settings?.login_background ? 'bg-slate-950/40 border-white/10' : 'bg-slate-50 border-slate-200'"
            >
              <div class="p-2 bg-white rounded-xl shadow-md border border-slate-200 flex-shrink-0">
                <img 
                  :src="activeResult.qr_code_url" 
                  alt="Barcode e-KTA" 
                  class="w-28 h-28 object-contain"
                />
              </div>

              <div class="space-y-2 text-center sm:text-left flex-1">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wide bg-blue-500/20 text-blue-400">
                  <span>Barcode e-KTA Resmi</span>
                </div>
                <h4 class="text-sm font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
                  Kode Otentikasi: <span class="font-mono text-orange-400">{{ activeResult.verify_code }}</span>
                </h4>
                <p class="text-xs leading-relaxed" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-500'">
                  Barcode / QR Code ini digunakan untuk memvalidasi keabsahan data e-KTA Anda secara langsung pada sistem verifikasi dinas SISFOPERS KC.
                </p>
                <div class="pt-1">
                  <a 
                    :href="activeResult.verification_url" 
                    target="_blank" 
                    class="inline-flex items-center gap-1 text-xs font-semibold text-blue-400 hover:text-blue-300 hover:underline"
                  >
                    Buka Halaman Verifikasi e-KTA
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>
              </div>
            </div>

            <!-- Tombol Navigasi Lanjut -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t" :class="settings?.login_background ? 'border-white/10' : 'border-slate-100'">
              <button 
                type="button" 
                @click="resetSearch" 
                class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded-lg border transition hover:bg-white/10 text-center"
                :class="settings?.login_background ? 'border-white/20 text-slate-300' : 'border-slate-300 text-slate-700 hover:bg-slate-50'"
              >
                Cari Data Lain
              </button>

              <Link 
                :href="`/login?nikc=${encodeURIComponent(activeResult.nikc)}`" 
                class="w-full sm:w-auto px-6 py-2.5 text-xs font-bold rounded-lg text-white shadow-lg transition flex items-center justify-center gap-2"
                :class="settings?.login_background ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' : 'bg-[#2563EB] hover:bg-[#1E40AF] shadow-blue-500/10'"
              >
                <span>Lanjut Masuk ke Akun</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
              </Link>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  settings: Object,
  result: Object,
  input: Object,
});

const page = usePage();
const settings = computed(() => props.settings || page.props.settings || {});

const form = useForm({
  full_name: props.input?.full_name || '',
  dob: props.input?.dob || '',
  nik: props.input?.nik || '',
});

// State aktif hasil pencarian
const activeResult = ref(props.result || null);
const copied = ref(false);

watch(
  () => props.result,
  (newResult) => {
    activeResult.value = newResult || null;
  },
  { immediate: true }
);

watch(
  () => props.input,
  (newInput) => {
    if (newInput) {
      if (newInput.full_name !== undefined) form.full_name = newInput.full_name;
      if (newInput.dob !== undefined) form.dob = newInput.dob;
      if (newInput.nik !== undefined) form.nik = newInput.nik;
    }
  }
);

const defaultAvatar = computed(() => {
  const name = activeResult.value?.full_name || 'Personel';
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=1E293B&color=F8FAFC&size=200`;
});

const handleImageError = (event) => {
  event.target.src = defaultAvatar.value;
};

const submitSearch = () => {
  form.post(route('nikc.check'), {
    preserveScroll: true,
    onSuccess: (pageObj) => {
      activeResult.value = pageObj.props.result || null;
    },
  });
};

const resetSearch = () => {
  activeResult.value = null;
  form.reset();
  form.clearErrors();
};

const copyNikc = () => {
  if (!activeResult.value?.nikc) return;
  navigator.clipboard.writeText(activeResult.value.nikc).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2500);
  });
};
</script>
