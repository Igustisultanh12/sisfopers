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

            <div class="md:col-span-2 bg-red-50/50 border border-red-200 rounded-2xl p-4 flex items-center justify-between">
              <div class="space-y-0.5">
                <label class="block text-xs font-bold text-red-800 uppercase">Aktifkan Mode Perbaikan (Under Maintenance)</label>
                <p class="text-[11px] text-red-500/85">Jika aktif, seluruh akun dengan role "Personel" yang mencoba masuk atau mengakses halaman akan dihadapkan ke halaman perbaikan. Admin tetap dapat mengakses sistem.</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox" v-model="form.under_maintenance" true-value="1" false-value="0" class="sr-only peer" />
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
              </label>
            </div>

            <!-- Cloudflare Turnstile CAPTCHA Protection -->
            <div class="md:col-span-2 bg-gradient-to-r from-orange-50/70 to-slate-50 border border-orange-200/80 rounded-2xl p-5 space-y-4">
              <div class="flex items-center justify-between">
                <div class="space-y-0.5">
                  <div class="flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wide">
                      Verifikasi Keamanan Cloudflare Turnstile (Anti-Bot)
                    </label>
                  </div>
                  <p class="text-[11px] text-slate-500 leading-relaxed">
                    Jika aktif, setiap pengguna yang melakukan login wajib menyelesaikan verifikasi interaktif Cloudflare Turnstile di bawah kata sandi sebelum dapat masuk ke sistem.
                  </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer select-none shrink-0 ml-4">
                  <input type="checkbox" v-model="form.turnstile_enabled" true-value="1" false-value="0" class="sr-only peer" />
                  <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                </label>
              </div>

              <!-- Input Keys Turnstile -->
              <div v-if="form.turnstile_enabled === '1' || form.turnstile_enabled === 1" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-orange-200/60">
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    Cloudflare Turnstile Site Key <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="text" 
                    v-model="form.turnstile_site_key" 
                    placeholder="Contoh: 0x4AAAAAAAx..." 
                    class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500" 
                  />
                  <p class="text-[10px] text-slate-400 mt-1">Kunci publik Turnstile dari dashboard Cloudflare.</p>
                </div>

                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    Cloudflare Turnstile Secret Key <span class="text-red-500">*</span>
                  </label>
                  <input 
                    type="password" 
                    v-model="form.turnstile_secret_key" 
                    placeholder="Contoh: 0x4AAAAAAAx..." 
                    class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500" 
                  />
                  <p class="text-[10px] text-slate-400 mt-1">Kunci rahasia untuk validasi backend ke Cloudflare.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Section Pengaturan Video Conference & Relay TURN -->
          <div class="border-t border-[#E2E8F0] pt-6 space-y-6">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div>
                <h4 class="text-sm font-bold text-slate-800">Relay Panggilan Video Dinas (WebRTC & TURN Server)</h4>
                <p class="text-xs text-slate-400 mt-0.5">Konfigurasikan server relay Coturn atau Metered agar panggilan video dapat menembus firewall dan jaringan seluler (CGNAT).</p>
              </div>
              <button 
                type="button" 
                @click="autoDetectIp" 
                :disabled="isDetectingIp"
                class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-200 transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
              >
                <svg v-if="isDetectingIp" class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>{{ isDetectingIp ? 'Mendeteksi...' : 'Deteksi IP Publik Peladen' }}</span>
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 border border-slate-200 rounded-2xl p-5">
              <div class="md:col-span-3">
                <span class="text-xs font-bold text-blue-700 uppercase tracking-wider block mb-1">Opsi 1: Server Mandiri Coturn (Peladen Lokal / VPS)</span>
                <p class="text-[11px] text-slate-500 mb-3">Gunakan IP publik peladen Komandan jika telah memasang paket coturn di Linux (apt install coturn).</p>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">IP Publik / Host Coturn</label>
                <input 
                  type="text" 
                  v-model="form.coturn_host" 
                  placeholder="Contoh: 182.8.66.12" 
                  class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-[#2563EB]" 
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Port Coturn (Default: 3478)</label>
                <input 
                  type="number" 
                  v-model="form.coturn_port" 
                  placeholder="3478" 
                  class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-[#2563EB]" 
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Kunci Rahasia (Secret)</label>
                <input 
                  type="text" 
                  v-model="form.coturn_secret" 
                  placeholder="sisfoperskc2026secret" 
                  class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-[#2563EB]" 
                />
              </div>

              <div class="md:col-span-3 border-t border-slate-200/80 pt-3 mt-1">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Opsi 2: Layanan Relay Metered (Alternatif Awan)</span>
                <p class="text-[11px] text-slate-500 mb-3">Jika tidak memasang Coturn, Komandan dapat mengisi App Name dan API Key dari Metered.ca.</p>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Metered App Name</label>
                <input 
                  type="text" 
                  v-model="form.metered_app_name" 
                  placeholder="Contoh: sisfopers" 
                  class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-[#2563EB]" 
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Metered API Key</label>
                <input 
                  type="text" 
                  v-model="form.metered_api_key" 
                  placeholder="Kunci API dari dashboard Metered" 
                  class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-[#2563EB]" 
                />
              </div>

              <div class="md:col-span-3 border-t border-slate-200/80 pt-3 mt-1">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block mb-1">Opsi 3: Layanan Agora RTC (Alternatif 2 - Rekomendasi Bebas Hambatan)</span>
                <p class="text-[11px] text-slate-500 mb-3">10.000 menit gratis/bulan, tanpa popup login pihak ketiga, menyatu langsung dengan antarmuka SISFOPERS KC.</p>
              </div>

              <div class="md:col-span-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Agora App ID</label>
                <input 
                  type="text" 
                  v-model="form.agora_app_id" 
                  placeholder="Contoh: 19daeb63b0ec46f2b02197c9fbbe81d6" 
                  class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-[#2563EB]" 
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Agora App Certificate</label>
                <input 
                  type="text" 
                  v-model="form.agora_app_certificate" 
                  placeholder="Contoh: f20e73efeed44842b2d861723549f8ea" 
                  class="w-full px-3.5 py-2 bg-white border border-[#E2E8F0] rounded-xl text-xs font-mono outline-none focus:border-[#2563EB]" 
                />
              </div>
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
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
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

        <!-- Notifikasi Push Aplikasi Android (Firebase Cloud Messaging) -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm space-y-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Notifikasi Push Aplikasi</h3>
              <p class="text-[11px] text-slate-500 mt-1">Firebase Cloud Messaging untuk aplikasi Android SISFOPERSKC.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
              <input type="checkbox" class="sr-only peer" v-model="fcm.enabled" />
              <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-[#2563EB] transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition peer-checked:after:translate-x-5"></div>
            </label>
          </div>

          <div class="grid grid-cols-2 gap-2 text-[11px]">
            <div class="rounded-xl border p-2.5" :class="fcmStatus.server_ready ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-500'">
              <span class="block font-bold uppercase text-[9px] tracking-wider">Server (pengirim)</span>
              <span class="font-semibold">{{ fcmStatus.server_ready ? 'Siap' : 'Belum siap' }}</span>
            </div>
            <div class="rounded-xl border p-2.5" :class="fcmStatus.client_ready ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-500'">
              <span class="block font-bold uppercase text-[9px] tracking-wider">Aplikasi (penerima)</span>
              <span class="font-semibold">{{ fcmStatus.client_ready ? 'Siap' : 'Belum siap' }}</span>
            </div>
          </div>
          <div v-if="fcmStatus.project_id || fcmStatus.client_email" class="text-[11px] text-slate-500 space-y-0.5">
            <div v-if="fcmStatus.project_id">Project: <span class="font-semibold text-slate-700">{{ fcmStatus.project_id }}</span></div>
            <div v-if="fcmStatus.client_email" class="truncate">Service account: <span class="font-semibold text-slate-700">{{ fcmStatus.client_email }}</span></div>
            <div>Perangkat terdaftar: <span class="font-semibold text-slate-700">{{ fcmStatus.devices ?? 0 }}</span></div>
          </div>

          <div class="space-y-3">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Service Account (JSON)</label>
              <input type="file" accept=".json,application/json" @change="e => fcm.service_account = e.target.files[0] || null" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100" />
              <p class="text-[10px] text-slate-400 mt-1">Firebase Console › Project settings › Service accounts › Generate new private key.</p>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">google-services.json</label>
              <input type="file" accept=".json,application/json" @change="e => fcm.google_services = e.target.files[0] || null" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100" />
              <p class="text-[10px] text-slate-400 mt-1">Daftarkan aplikasi Android dengan package <span class="font-mono">id.my.sisfoperskc.app</span>, lalu unduh berkasnya.</p>
            </div>
          </div>

          <div class="flex flex-wrap gap-2 pt-1">
            <button type="button" @click="saveFcm" class="py-2 px-4 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-semibold rounded-xl transition cursor-pointer">Simpan FCM</button>
            <button type="button" @click="testFcm" class="py-2 px-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition cursor-pointer">Kirim Uji</button>
            <button v-if="fcmStatus.server_ready || fcmStatus.client_ready" type="button" @click="deleteFcm" class="py-2 px-4 bg-white border border-red-200 hover:bg-red-50 text-red-600 text-xs font-semibold rounded-xl transition cursor-pointer">Hapus</button>
          </div>
        </div>

        <!-- Pembaruan Aplikasi Android (unggah APK) -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm space-y-4">
          <div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pembaruan Aplikasi Android</h3>
            <p class="text-[11px] text-slate-500 mt-1">Unggah APK versi baru. Aplikasi di HP pengguna akan mendeteksi, mengunduh, memverifikasi tanda tangan, lalu memasang pembaruan.</p>
          </div>
          <div v-if="appUpdate" class="rounded-xl border bg-emerald-50 border-emerald-200 p-3 text-[11px] text-emerald-800 space-y-0.5">
            <div class="font-bold text-emerald-900">Versi aktif di server: v{{ appUpdate.version }} <span class="font-semibold">(kode {{ appUpdate.version_code }}, {{ fmtSize(appUpdate.size) }})</span></div>
            <div>Diunggah {{ fmtDate(appUpdate.uploaded_at) }} oleh {{ appUpdate.uploaded_by }}<span v-if="appUpdate.notified"> · pemberitahuan ke {{ appUpdate.notified }} pengguna</span></div>
            <div v-if="appUpdate.notes" class="text-slate-600 whitespace-pre-line">{{ appUpdate.notes }}</div>
            <div class="font-mono text-[9px] text-slate-400 break-all">SHA-256 {{ appUpdate.sha256 }}</div>
          </div>
          <div v-else class="rounded-xl border bg-slate-50 border-slate-200 p-3 text-[11px] text-slate-500">Belum ada APK pembaruan yang diunggah.</div>
          <div class="space-y-3">
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Berkas APK (SISFOPERSKC-x.y.z.apk)</label>
              <input ref="apkInput" type="file" accept=".apk,application/vnd.android.package-archive" @change="pickApk" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100" />
              <p v-if="uploadLimit" class="text-[10px] text-slate-400 mt-1">Batas unggah server: {{ fmtSize(uploadLimit) }}.</p>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Catatan Pembaruan</label>
              <textarea v-model="apk.notes" rows="3" maxlength="2000" class="w-full border border-[#E2E8F0] rounded-xl px-3 py-2 text-xs focus:ring-[#2563EB] focus:border-[#2563EB]" placeholder="Contoh: Perbaikan notifikasi dan fitur baru."></textarea>
            </div>
            <label class="flex items-center gap-2 text-[11px] text-slate-600 font-semibold">
              <input type="checkbox" v-model="apk.notify" class="rounded border-slate-300 text-[#2563EB]" /> Kirim pemberitahuan pembaruan ke HP pengguna
            </label>
            <div v-if="apk.progress > 0 && apk.progress < 100" class="h-2 rounded-full bg-slate-100 overflow-hidden">
              <div class="h-full bg-[#2563EB] transition-all" :style="{ width: apk.progress + '%' }"></div>
            </div>
            <p v-if="apk.error" class="text-[11px] text-red-600 font-semibold">{{ apk.error }}</p>
          </div>
          <div class="flex flex-wrap gap-2 pt-1">
            <button type="button" @click="uploadApk" :disabled="apk.uploading" class="py-2 px-4 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-60 text-white text-xs font-semibold rounded-xl transition cursor-pointer">{{ apk.uploading ? 'Mengunggah ' + apk.progress + '%' : 'Unggah APK' }}</button>
            <button v-if="appUpdate" type="button" @click="deleteApk" class="py-2 px-4 bg-white border border-red-200 hover:bg-red-50 text-red-600 text-xs font-semibold rounded-xl transition cursor-pointer">Hapus APK</button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';
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
  under_maintenance: props.settings.under_maintenance || '0',
  turnstile_enabled: props.settings.turnstile_enabled || '0',
  turnstile_site_key: props.settings.turnstile_site_key || '',
  turnstile_secret_key: props.settings.turnstile_secret_key || '',
  coturn_host: props.settings.coturn_host || '',
  coturn_port: props.settings.coturn_port || '3478',
  coturn_secret: props.settings.coturn_secret || 'sisfoperskc2026secret',
  metered_app_name: props.settings.metered_app_name || '',
  metered_api_key: props.settings.metered_api_key || '',
  agora_app_id: props.settings.agora_app_id || '19daeb63b0ec46f2b02197c9fbbe81d6',
  agora_app_certificate: props.settings.agora_app_certificate || 'f20e73efeed44842b2d861723549f8ea',
  login_background: null,
  logo_tni: null,
  logo_ad: null,
  logo_al: null,
  logo_au: null,
  favicon: null
});

const isDetectingIp = ref(false);

const autoDetectIp = async () => {
  isDetectingIp.value = true;
  try {
    const res = await axios.get(route('admin.setting.detect-ip'));
    if (res.data?.success && res.data.ip) {
      form.coturn_host = res.data.ip;
      Swal.fire({
        icon: 'success',
        title: 'IP Publik Terdeteksi',
        text: `Alamat IP publik peladen ${res.data.ip} berhasil dimuat ke formulir.`,
        confirmButtonColor: '#2563eb',
      });
    } else {
      throw new Error(res.data?.message || 'Gagal mendeteksi IP');
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'Deteksi IP Terkendala',
      text: err.response?.data?.message || err.message || 'Gagal menghubungi peladen pendeteksi IP.',
      confirmButtonColor: '#2563eb',
    });
  } finally {
    isDetectingIp.value = false;
  }
};

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

// =================== Notifikasi Push Aplikasi (FCM) ===================
const fcm = reactive({
  enabled: props.settings.fcm_enabled === '1',
  service_account: null,
  google_services: null,
});
const fcmStatus = ref({});

const loadFcmStatus = async () => {
  try {
    const res = await axios.get(route('admin.setting.fcm-status'));
    fcmStatus.value = res.data.status || {};
  } catch (e) {
    fcmStatus.value = {};
  }
};
onMounted(loadFcmStatus);

const saveFcm = async () => {
  const data = new FormData();
  data.append('fcm_enabled', fcm.enabled ? '1' : '0');
  if (fcm.service_account) data.append('service_account', fcm.service_account);
  if (fcm.google_services) data.append('google_services', fcm.google_services);
  showLoadingProgress('Menyimpan konfigurasi FCM...');
  try {
    const res = await axios.post(route('admin.setting.fcm'), data);
    closeLoading();
    fcmStatus.value = res.data.status || fcmStatus.value;
    alertSuccess('Tersimpan', res.data.message);
  } catch (err) {
    closeLoading();
    alertError('Gagal Menyimpan', err?.response?.data?.message || 'Periksa kembali berkas yang diunggah.');
  }
};

const testFcm = async () => {
  showLoadingProgress('Mengirim notifikasi uji...');
  try {
    const res = await axios.post(route('admin.setting.fcm-test'));
    closeLoading();
    alertSuccess('Terkirim', res.data.message);
  } catch (err) {
    closeLoading();
    alertError('Gagal', err?.response?.data?.message || 'Notifikasi uji gagal dikirim.');
  }
};

const deleteFcm = async () => {
  const confirm = await Swal.fire({
    title: 'Hapus konfigurasi FCM?',
    text: 'Notifikasi push aplikasi Android akan berhenti sampai berkas diunggah kembali.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#DC2626',
    cancelButtonColor: '#1E293B',
    confirmButtonText: 'Hapus',
    cancelButtonText: 'Batal',
  });
  if (!confirm.isConfirmed) return;
  try {
    const res = await axios.delete(route('admin.setting.fcm-delete'));
    fcmStatus.value = res.data.status || {};
    fcm.enabled = false;
    alertSuccess('Dihapus', res.data.message);
  } catch (err) {
    alertError('Gagal', 'Konfigurasi FCM gagal dihapus.');
  }
};

// =================== Pembaruan Aplikasi Android ===================
const appUpdate = ref(null);
const uploadLimit = ref(0);
const apkInput = ref(null);
const apk = reactive({ file: null, notes: '', notify: true, progress: 0, uploading: false, error: '' });
const fmtSize = (b) => {
  if (!b) return '0 B';
  if (b >= 1073741824) return (b / 1073741824).toFixed(1) + ' GB';
  if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
  return Math.round(b / 1024) + ' KB';
};
const fmtDate = (v) => { try { return new Date(v).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }); } catch (e) { return v; } };
const loadAppUpdate = async () => {
  try {
    const res = await axios.get(route('admin.setting.app-update-status'));
    appUpdate.value = res.data.app_update || null;
    uploadLimit.value = res.data.upload_limit || 0;
  } catch (e) { /* abaikan */ }
};
onMounted(loadAppUpdate);
const pickApk = (e) => {
  apk.error = '';
  const f = e.target.files[0] || null;
  if (f && !f.name.toLowerCase().endsWith('.apk')) { apk.error = 'Pilih berkas berekstensi .apk.'; apk.file = null; e.target.value = ''; return; }
  if (f && uploadLimit.value && f.size > uploadLimit.value) { apk.error = `Ukuran APK ${fmtSize(f.size)} melebihi batas unggah server (${fmtSize(uploadLimit.value)}). Naikkan upload_max_filesize & post_max_size PHP serta client_max_body_size Nginx.`; apk.file = null; e.target.value = ''; return; }
  apk.file = f;
};
const uploadApk = async () => {
  if (!apk.file) { apk.error = 'Pilih berkas APK terlebih dahulu.'; return; }
  const data = new FormData();
  data.append('apk', apk.file);
  data.append('notes', apk.notes || '');
  data.append('notify', apk.notify ? '1' : '0');
  apk.uploading = true; apk.progress = 0; apk.error = '';
  try {
    const res = await axios.post(route('admin.setting.app-update'), data, {
      onUploadProgress: (ev) => { if (ev.total) apk.progress = Math.round(ev.loaded * 100 / ev.total); },
    });
    appUpdate.value = res.data.app_update || null;
    apk.file = null; apk.notes = ''; if (apkInput.value) apkInput.value.value = '';
    alertSuccess('Berhasil', res.data.message);
  } catch (err) {
    apk.error = err?.response?.data?.message || 'Unggah APK gagal.';
  } finally {
    apk.uploading = false;
  }
};
const deleteApk = async () => {
  const confirm = await Swal.fire({
    title: 'Hapus APK pembaruan?', text: 'Aplikasi berhenti menawarkan pembaruan sampai APK baru diunggah.', icon: 'warning',
    showCancelButton: true, confirmButtonColor: '#DC2626', cancelButtonColor: '#1E293B', confirmButtonText: 'Hapus', cancelButtonText: 'Batal',
  });
  if (!confirm.isConfirmed) return;
  try {
    const res = await axios.delete(route('admin.setting.app-update-delete'));
    appUpdate.value = null;
    alertSuccess('Dihapus', res.data.message);
  } catch (e) { alertError('Gagal', 'APK gagal dihapus.'); }
};
</script>
