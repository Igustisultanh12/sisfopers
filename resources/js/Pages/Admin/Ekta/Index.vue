<template>
  <AuthenticatedLayout>
    <template #header-title>Manajemen E-KTA Komponen Cadangan</template>

    <div class="space-y-6 max-w-7xl mx-auto">
      <!-- Header Banner & Tab Navigasi -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
              Dokumen Resmi Negara
            </span>
            <span class="text-xs text-slate-400 font-mono">Format: [No...../KTA KC/Tahun]</span>
          </div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight mt-1">
            Kartu Tanda Anggota Elektronik (E-KTA)
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Sistem penerbitan terpusat, pengesahan kriptografi, dan verifikasi dokumen digital Komcad TNI.
          </p>
        </div>

        <!-- Tab Tombol -->
        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl border border-slate-200">
          <button
            @click="activeTab = 'list'"
            :class="activeTab === 'list' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-4 py-2 text-xs rounded-lg transition"
          >
            Daftar & Penerbitan E-KTA
          </button>
          <button
            @click="activeTab = 'settings'"
            :class="activeTab === 'settings' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-4 py-2 text-xs rounded-lg transition"
          >
            Pengaturan Logo & Pejabat
          </button>
        </div>
      </div>

      <!-- ================= TAB 1: DAFTAR & PENERBITAN ================= -->
      <div v-show="activeTab === 'list'" class="space-y-6">
        <!-- Statistik Ringkasan -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Personel</p>
              <h3 class="text-2xl font-black text-slate-800 mt-1">{{ stats.total_personel }}</h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Seluruh Anggota Komcad</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center font-bold">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
          </div>

          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">E-KTA Diterbitkan</p>
              <h3 class="text-2xl font-black text-emerald-700 mt-1">{{ stats.total_terbit }}</h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Telah Terbit & Aktif</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>

          <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-sm flex items-center justify-between">
            <div>
              <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Belum Diterbitkan</p>
              <h3 class="text-2xl font-black text-amber-700 mt-1">{{ stats.total_belum_terbit }}</h3>
              <p class="text-[11px] text-slate-400 mt-0.5">Menunggu Penerbitan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
          </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <input
              v-model="searchQuery"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Cari Personel: Nama, NIKC, Pangkat, Matra..."
              class="w-full pl-4 pr-10 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB]"
            />
            <button
              @click="handleSearch"
              class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600 font-bold"
            >
              Cari
            </button>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="setFilterStatus('all')"
              :class="filters.status === 'all' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-[#E2E8F0] hover:bg-slate-50'"
              class="px-3.5 py-2 text-xs font-bold rounded-xl transition"
            >
              Semua ({{ stats.total_personel }})
            </button>
            <button
              @click="setFilterStatus('terbit')"
              :class="filters.status === 'terbit' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-[#E2E8F0] hover:bg-slate-50'"
              class="px-3.5 py-2 text-xs font-bold rounded-xl transition"
            >
              Terbit ({{ stats.total_terbit }})
            </button>
            <button
              @click="setFilterStatus('belum_terbit')"
              :class="filters.status === 'belum_terbit' ? 'bg-amber-600 text-white' : 'bg-white text-slate-600 border border-[#E2E8F0] hover:bg-slate-50'"
              class="px-3.5 py-2 text-xs font-bold rounded-xl transition"
            >
              Belum Terbit ({{ stats.total_belum_terbit }})
            </button>
          </div>
        </div>

        <!-- Grid Kartu Personel -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="pers in personels.data"
            :key="pers.id"
            class="bg-white border border-[#E2E8F0] hover:border-blue-400 rounded-2xl p-5 shadow-xs transition duration-200 flex flex-col justify-between space-y-4"
          >
            <div class="flex items-start gap-3.5">
              <!-- Foto Personel -->
              <div class="w-13 h-16 rounded-xl overflow-hidden shrink-0 border border-slate-200 bg-slate-100 flex items-center justify-center">
                <img
                  v-if="pers.photo_profile"
                  :src="getPhotoUrl(pers.photo_profile, pers.full_name)"
                  @error="$event.target.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(pers.full_name || 'PERS') + '&background=e2e8f0&color=334155'"
                  class="w-full h-full object-cover object-top"
                  alt="Foto"
                />
                <div v-else class="text-[9px] font-bold text-slate-400 uppercase text-center p-1">
                  NO FOTO
                </div>
              </div>

              <!-- Info Personel -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    {{ pers.pangkat || 'Prajurit' }}
                  </span>
                  <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ pers.matra || 'AD' }}
                  </span>
                </div>

                <h4 class="text-sm font-extrabold text-slate-800 truncate mt-1">
                  {{ pers.full_name }}
                </h4>

                <p class="text-[11px] font-mono text-slate-500 mt-0.5">
                  <span class="text-slate-400">NIKC:</span> {{ pers.nikc || pers.user?.username || '-' }}
                </p>

                <!-- Status E-KTA Badge -->
                <div class="mt-2">
                  <span
                    v-if="pers.ekta && pers.ekta.status === 'TERBIT'"
                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200"
                  >
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    E-KTA Terbit ({{ pers.ekta.nomor_kta }})
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                  >
                    E-KTA Belum Diterbitkan
                  </span>
                </div>
              </div>
            </div>

            <!-- Tombol Kelola E-KTA -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
              <span class="text-[10px] text-slate-400 font-medium">
                Lulus: {{ pers.angkatan || '2025' }}
              </span>
              <button
                @click="openEktaModal(pers)"
                type="button"
                class="px-4 py-2 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Kelola E-KTA
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="personels.links && personels.links.length > 3" class="flex justify-center gap-1 mt-6">
          <Link
            v-for="(link, idx) in personels.links"
            :key="idx"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              'px-3 py-1.5 rounded-xl text-xs font-bold transition',
              link.active ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-white text-slate-600 border border-[#E2E8F0] hover:bg-slate-50',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
          />
        </div>
      </div>

      <!-- ================= TAB 2: PENGATURAN LOGO & PEJABAT ================= -->
      <div v-show="activeTab === 'settings'" class="bg-white border border-[#E2E8F0] rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
        <div>
          <h2 class="text-base font-extrabold text-slate-800">
            Pengaturan Aset Visual & Pejabat Penandatangan E-KTA
          </h2>
          <p class="text-xs text-slate-500 mt-1">
            Atur dan unggah logo Komcad, tanda tangan digital, dan cap dinas yang akan tercetak otomatis pada setiap kartu digital.
          </p>
        </div>

        <form @submit.prevent="submitSettings" class="space-y-6">
          <!-- Grid Form Unggah Aset Gambar -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- 1. Logo Komcad -->
            <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/50">
              <label class="block text-xs font-bold text-slate-700 uppercase">Logo Komcad (Kotak Kiri & Watermark)</label>
              <div class="w-24 h-24 bg-white rounded-xl border border-slate-200 mx-auto flex items-center justify-center p-2 shadow-xs">
                <img v-if="settingsForm.logoPreview || ektaSettings.logo_komcad" :src="settingsForm.logoPreview || getAssetUrl(ektaSettings.logo_komcad)" class="max-h-full max-w-full object-contain" />
                <KomcadEmblem v-else class="w-16 h-16" />
              </div>
              <input
                type="file"
                accept="image/*"
                @change="handleLogoChange"
                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#2563EB] file:text-white hover:file:bg-blue-700"
              />
              <p class="text-[10px] text-slate-400">Rekomendasi: PNG Transparan atau SVG resolusi tinggi.</p>
            </div>

            <!-- 2. Tanda Tangan Pejabat -->
            <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/50">
              <label class="block text-xs font-bold text-slate-700 uppercase">Tanda Tangan Pejabat (Dirjen Pothan)</label>
              <div class="w-32 h-24 bg-white rounded-xl border border-slate-200 mx-auto flex items-center justify-center p-2 shadow-xs">
                <img v-if="settingsForm.signaturePreview || ektaSettings.signature" :src="settingsForm.signaturePreview || getAssetUrl(ektaSettings.signature)" class="max-h-full max-w-full object-contain" />
                <span v-else class="text-[10px] text-slate-400 italic">Default Tanda Tangan</span>
              </div>
              <input
                type="file"
                accept="image/*"
                @change="handleSignatureChange"
                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#2563EB] file:text-white hover:file:bg-blue-700"
              />
              <p class="text-[10px] text-slate-400">Rekomendasi: PNG Transparan (Tinta Biru / Hitam).</p>
            </div>

            <!-- 3. Stempel Dinas Komcad -->
            <div class="border border-slate-200 rounded-2xl p-5 space-y-3 bg-slate-50/50">
              <label class="block text-xs font-bold text-slate-700 uppercase">Stempel Dinas Kemhan / Komcad</label>
              <div class="w-24 h-24 bg-white rounded-full border border-slate-200 mx-auto flex items-center justify-center p-2 shadow-xs">
                <img v-if="settingsForm.stampPreview || ektaSettings.stamp" :src="settingsForm.stampPreview || getAssetUrl(ektaSettings.stamp)" class="max-h-full max-w-full object-contain" />
                <span v-else class="text-[9px] text-purple-900 font-bold text-center">Cap Resmi Default</span>
              </div>
              <input
                type="file"
                accept="image/*"
                @change="handleStampChange"
                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#2563EB] file:text-white hover:file:bg-blue-700"
              />
              <p class="text-[10px] text-slate-400">Rekomendasi: PNG Bulat Transparan (Tinta Ungu / Merah).</p>
            </div>
          </div>

          <!-- Rincian Identitas Pejabat Penandatangan -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-slate-200">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pejabat</label>
              <input
                v-model="settingsForm.ekta_signer_name"
                type="text"
                placeholder="Contoh: Sri Yanto, S.T."
                class="w-full px-3.5 py-2.5 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Pangkat Militer</label>
              <input
                v-model="settingsForm.ekta_signer_rank"
                type="text"
                placeholder="Contoh: Laksamana Muda TNI"
                class="w-full px-3.5 py-2.5 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan Penandatangan</label>
              <input
                v-model="settingsForm.ekta_signer_title"
                type="text"
                placeholder="Direktur Jenderal Potensi Pertahanan"
                class="w-full px-3.5 py-2.5 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button
              type="submit"
              :disabled="settingsForm.processing"
              class="px-6 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
            >
              Simpan Pengaturan E-KTA
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ================= MODAL KELOLA / PENERBITAN E-KTA ================= -->
    <div v-if="selectedPersonel" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs animate-fade-in overflow-y-auto">
      <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 my-8 space-y-6 animate-scale-up relative">
        
        <!-- Tombol Tutup Modal -->
        <button
          @click="closeEktaModal"
          class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 p-1.5 rounded-full hover:bg-slate-100 transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <!-- KONDISI 1: E-KTA SUDAH TERBIT -->
        <div v-if="selectedPersonel.ekta && selectedPersonel.ekta.status === 'TERBIT'" class="space-y-6">
          <div class="border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                STATUS: TERBIT & AKTIF
              </span>
              <span class="text-xs font-mono text-slate-400">{{ selectedPersonel.ekta.nomor_kta }}</span>
            </div>
            <h3 class="text-lg font-black text-slate-800 mt-1">
              Kartu Tanda Anggota Elektronik (E-KTA)
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
              Pemegang: <strong class="text-slate-800">{{ selectedPersonel.full_name }}</strong> (NIKC: {{ selectedPersonel.nikc }})
            </p>
          </div>

          <!-- Komponen Visualisasi EktaCard Depan & Belakang -->
          <div class="flex justify-center">
            <EktaCard
              :personel="selectedPersonel"
              :ekta="selectedPersonel.ekta"
              :settings="ektaSettings"
            />
          </div>

          <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <a
              :href="`/verifikasi/ekta/${selectedPersonel.ekta.verify_code}`"
              target="_blank"
              class="text-xs font-bold text-[#2563EB] hover:underline flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
              Buka Halaman Verifikasi Publik QR
            </a>

            <button
              @click="startIssuanceFlow"
              type="button"
              class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold rounded-xl transition"
            >
              Terbitkan Ulang / Perbarui Data
            </button>
          </div>
        </div>

        <!-- KONDISI 2: E-KTA BELUM DITERBITKAN (LANGKAH 0: TAMPILAN AWAL BELUM TERBIT) -->
        <div v-else-if="issuanceStep === 0" class="text-center py-6 space-y-6">
          <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 mx-auto flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>

          <div class="space-y-1">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
              Menunggu Otorisasi
            </span>
            <h3 class="text-xl font-black text-slate-800 mt-2">
              E-KTA Belum Diterbitkan
            </h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
              Kartu Tanda Anggota Elektronik untuk personel <strong>{{ selectedPersonel.full_name }}</strong> belum diterbitkan secara sah oleh Pejabat Berwenang Mabes Komcad RI.
            </p>
          </div>

          <!-- Preview Nomor Format Awal: [No...../KTA KC/tahun lulus pendidikan kc] -->
          <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 max-w-sm mx-auto text-center space-y-1">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Format Nomor KTA Awal</p>
            <p class="text-sm font-extrabold font-mono text-slate-800">
              No. ...../KTA KC/{{ selectedPersonel.angkatan || new Date().getFullYear() }}
            </p>
          </div>

          <div class="pt-2">
            <button
              @click="startIssuanceFlow"
              :disabled="loadingOtp"
              class="px-8 py-3 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-blue-500/20 transition cursor-pointer flex items-center gap-2 mx-auto"
            >
              <svg v-if="!loadingOtp" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span v-if="loadingOtp">Mengirimkan Kode OTP Ke Email...</span>
              <span v-else>Terbitkan E-KTA Sekarang</span>
            </button>
          </div>
        </div>

        <!-- LANGKAH 1: INPUT OTP OTORISASI EMAIL -->
        <div v-else-if="issuanceStep === 1" class="text-center py-4 space-y-5">
          <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#2563EB] border border-blue-200 mx-auto flex items-center justify-center">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>

          <div class="space-y-1">
            <h3 class="text-lg font-black text-slate-800">
              Otorisasi Keamanan Penerbitan
            </h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
              Kode OTP 6-digit telah dikirimkan ke email akun Admin Anda. Masukkan kode tersebut untuk melanjutkan otorisasi penerbitan dokumen negara.
            </p>
          </div>

          <!-- Input 6 Digit OTP -->
          <div class="flex justify-center">
            <input
              v-model="otpInput"
              type="text"
              maxlength="6"
              placeholder="123456"
              class="w-48 text-center text-2xl font-black font-mono tracking-widest px-4 py-3 border-2 border-blue-500 rounded-2xl outline-none focus:ring-4 focus:ring-blue-100 bg-blue-50/30 text-slate-900"
            />
          </div>

          <p v-if="otpError" class="text-xs font-bold text-rose-600">
            {{ otpError }}
          </p>

          <div class="flex items-center justify-center gap-3 pt-2">
            <button
              @click="requestOtp"
              type="button"
              :disabled="loadingOtp"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 transition"
            >
              Kirim Ulang OTP
            </button>
            <button
              @click="verifyOtp"
              :disabled="verifyingOtp || otpInput.length !== 6"
              class="px-6 py-2.5 bg-[#2563EB] hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs transition"
            >
              <span v-if="verifyingOtp">Memverifikasi...</span>
              <span v-else>Verifikasi OTP</span>
            </button>
          </div>
        </div>

        <!-- LANGKAH 2: FORMULIR KONFIRMASI DATA LENGKAP E-KTA -->
        <div v-else-if="issuanceStep === 2" class="space-y-5">
          <div class="border-b border-slate-100 pb-3">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
              Tahap Konfirmasi Data
            </span>
            <h3 class="text-base font-black text-slate-800 mt-1">
              Konfirmasi Data Lengkap E-KTA
            </h3>
            <p class="text-xs text-slate-500">
              Periksa kelengkapan data sebelum mengesahkan dan menerbitkan kartu digital resmi.
            </p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs max-h-[380px] overflow-y-auto pr-1">
            <!-- Nomor Urut KTA -->
            <div class="sm:col-span-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
              <label class="block font-bold text-slate-700 mb-1">
                Nomor KTA (Awal: Kosong / Dapat Diisi Nomor Urut)
              </label>
              <div class="flex items-center gap-2">
                <span class="font-mono font-bold text-slate-500 text-sm">No.</span>
                <input
                  v-model="confirmationForm.nomor_urut"
                  type="text"
                  placeholder="....."
                  class="w-28 px-3 py-1.5 border border-[#E2E8F0] bg-white rounded-lg text-xs font-mono font-bold outline-none focus:border-[#2563EB]"
                />
                <span class="font-mono font-bold text-slate-700 text-sm">
                  /KTA KC/{{ confirmationForm.tahun_lulus }}
                </span>
              </div>
              <p class="text-[10px] text-slate-400 mt-1">
                Biarkan nomor urut kosong jika ingin menerbitkan dengan format awal: <code>[No...../KTA KC/{{ confirmationForm.tahun_lulus }}]</code>
              </p>
            </div>

            <!-- Nama Lengkap -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
              <input
                v-model="confirmationForm.full_name"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs bg-slate-50"
                readonly
              />
            </div>

            <!-- NIKC -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">NIKC Personel</label>
              <input
                v-model="confirmationForm.nikc"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs bg-slate-50 font-mono"
                readonly
              />
            </div>

            <!-- Pangkat -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Pangkat</label>
              <input
                v-model="confirmationForm.pangkat"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Jabatan -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Jabatan</label>
              <input
                v-model="confirmationForm.jabatan"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Kesatuan/Matra -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Kesatuan/Matra</label>
              <select
                v-model="confirmationForm.kesatuan_matra"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              >
                <option value="Matra Darat">Matra Darat</option>
                <option value="Matra Laut">Matra Laut</option>
                <option value="Matra Udara">Matra Udara</option>
              </select>
            </div>

            <!-- Berlaku s/d -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Masa Berlaku</label>
              <input
                v-model="confirmationForm.berlaku_sampai"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Sinyalemen: Tinggi/Berat Badan -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tinggi / Berat Badan</label>
              <input
                v-model="confirmationForm.tinggi_berat"
                type="text"
                placeholder="Contoh: 161/45"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Sinyalemen: Rambut -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Bentuk Rambut</label>
              <input
                v-model="confirmationForm.rambut"
                type="text"
                placeholder="Contoh: Bergelombang"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Sinyalemen: Mata -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Warna Mata</label>
              <input
                v-model="confirmationForm.mata"
                type="text"
                placeholder="Contoh: Coklat"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Sinyalemen: Golongan Darah -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Golongan Darah</label>
              <select
                v-model="confirmationForm.golongan_darah"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              >
                <option value="O">O</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="AB">AB</option>
              </select>
            </div>

            <!-- Sinyalemen: Tempat Lahir -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tempat Lahir</label>
              <input
                v-model="confirmationForm.tempat_lahir"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Sinyalemen: Tanggal Lahir -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Lahir</label>
              <input
                v-model="confirmationForm.tanggal_lahir"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Sinyalemen: Agama -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Agama</label>
              <input
                v-model="confirmationForm.agama"
                type="text"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              />
            </div>

            <!-- Sinyalemen: Alamat Rumah -->
            <div class="sm:col-span-2">
              <label class="block font-bold text-slate-700 mb-1">Alamat Rumah Lengkap</label>
              <textarea
                v-model="confirmationForm.alamat"
                rows="2"
                class="w-full px-3 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
              ></textarea>
            </div>
          </div>

          <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <button
              @click="issuanceStep = 0"
              type="button"
              class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800"
            >
              Batal
            </button>
            <button
              @click="triggerGenerateAnimation"
              type="button"
              class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-2"
            >
              <span>Konfirmasi & Terbitkan E-KTA</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

        <!-- LANGKAH 3: ANIMASI PROSES GENERATE E-KTA -->
        <div v-else-if="issuanceStep === 3" class="text-center py-10 space-y-6">
          <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
            <!-- Lingkaran Luar Berputar -->
            <div class="absolute inset-0 rounded-full border-4 border-blue-200 border-t-[#2563EB] animate-spin"></div>
            <!-- Ikon Inti -->
            <div class="w-12 h-12 bg-blue-50 text-[#2563EB] rounded-full flex items-center justify-center font-bold">
              <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
          </div>

          <div class="space-y-1">
            <h3 class="text-base font-black text-slate-800">
              Memproses Penerbitan E-KTA Digital
            </h3>
            <p class="text-xs text-slate-500 font-mono">
              Otorisasi Kriptografi Resmi Mabes TNI Komcad
            </p>
          </div>

          <!-- Progress Bar & Tahapan Militer Berurutan -->
          <div class="max-w-md mx-auto space-y-2.5 text-left text-xs">
            <div class="flex items-center gap-2.5" :class="genProgress >= 25 ? 'text-emerald-600 font-bold' : 'text-slate-400'">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border shrink-0" :class="genProgress >= 25 ? 'bg-emerald-50 border-emerald-300' : 'border-slate-300'">
                <svg v-if="genProgress >= 25" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span v-else>1</span>
              </span>
              <span>1. Memverifikasi keabsahan profil & data sinyalemen personel...</span>
            </div>

            <div class="flex items-center gap-2.5" :class="genProgress >= 50 ? 'text-emerald-600 font-bold' : 'text-slate-400'">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border shrink-0" :class="genProgress >= 50 ? 'bg-emerald-50 border-emerald-300' : 'border-slate-300'">
                <svg v-if="genProgress >= 50" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span v-else>2</span>
              </span>
              <span>2. Menghasilkan tanda tangan digital & QR Code otentikasi publik...</span>
            </div>

            <div class="flex items-center gap-2.5" :class="genProgress >= 75 ? 'text-emerald-600 font-bold' : 'text-slate-400'">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border shrink-0" :class="genProgress >= 75 ? 'bg-emerald-50 border-emerald-300' : 'border-slate-300'">
                <svg v-if="genProgress >= 75" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span v-else>3</span>
              </span>
              <span>3. Menyusun tata letak kriptografi E-KTA tampak depan dan belakang...</span>
            </div>

            <div class="flex items-center gap-2.5" :class="genProgress >= 100 ? 'text-emerald-600 font-bold' : 'text-slate-400'">
              <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] border shrink-0" :class="genProgress >= 100 ? 'bg-emerald-50 border-emerald-300' : 'border-slate-300'">
                <svg v-if="genProgress >= 100" class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span v-else>4</span>
              </span>
              <span>4. Menerbitkan dan mengunci E-KTA secara resmi ke basis data negara...</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EktaCard from '@/Components/EktaCard.vue';
import KomcadEmblem from '@/Components/KomcadEmblem.vue';

const props = defineProps({
  personels: Object,
  filters: Object,
  stats: Object,
  ektaSettings: Object,
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

const activeTab = ref('list');
const searchQuery = ref(props.filters?.search || '');

const handleSearch = () => {
  router.get(route('admin.ekta.index'), {
    search: searchQuery.value,
    status: props.filters?.status || 'all',
  }, { preserveState: true, preserveScroll: true });
};

const setFilterStatus = (st) => {
  router.get(route('admin.ekta.index'), {
    search: searchQuery.value,
    status: st,
  }, { preserveState: true, preserveScroll: true });
};

// Form Pengaturan Logo & Pejabat
const settingsForm = useForm({
  ekta_logo_komcad: null,
  ekta_signature: null,
  ekta_stamp: null,
  ekta_signer_name: props.ektaSettings?.signer_name || 'Sri Yanto, S.T.',
  ekta_signer_rank: props.ektaSettings?.signer_rank || 'Laksamana Muda TNI',
  ekta_signer_title: props.ektaSettings?.signer_title || 'Direktur Jenderal Potensi Pertahanan',
  logoPreview: null,
  signaturePreview: null,
  stampPreview: null,
});

const handleLogoChange = (e) => {
  const f = e.target.files[0];
  if (f) {
    settingsForm.ekta_logo_komcad = f;
    settingsForm.logoPreview = URL.createObjectURL(f);
  }
};

const handleSignatureChange = (e) => {
  const f = e.target.files[0];
  if (f) {
    settingsForm.ekta_signature = f;
    settingsForm.signaturePreview = URL.createObjectURL(f);
  }
};

const handleStampChange = (e) => {
  const f = e.target.files[0];
  if (f) {
    settingsForm.ekta_stamp = f;
    settingsForm.stampPreview = URL.createObjectURL(f);
  }
};

const submitSettings = () => {
  settingsForm.post(route('admin.ekta.settings'), {
    preserveScroll: true,
    onSuccess: () => {
      // Settings berhasil disimpan
    }
  });
};

// Modal Alur Penerbitan E-KTA
const selectedPersonel = ref(null);
const issuanceStep = ref(0); // 0: Awal belum terbit, 1: OTP, 2: Konfirmasi Data, 3: Animasi Generate
const loadingOtp = ref(false);
const verifyingOtp = ref(false);
const otpInput = ref('');
const otpError = ref('');
const genProgress = ref(0);

const confirmationForm = reactive({
  nomor_urut: '',
  tahun_lulus: '',
  full_name: '',
  nikc: '',
  pangkat: '',
  jabatan: '',
  kesatuan_matra: 'Matra Darat',
  berlaku_sampai: 'Selama Menjadi Anggota Komcad',
  tinggi_berat: '161/45',
  rambut: 'Bergelombang',
  mata: 'Coklat',
  golongan_darah: 'O',
  tempat_lahir: '-',
  tanggal_lahir: '-',
  agama: 'Islam',
  alamat: '-',
});

const openEktaModal = (pers) => {
  selectedPersonel.value = pers;
  issuanceStep.value = 0;
  otpInput.value = '';
  otpError.value = '';
  genProgress.value = 0;

  // Siapkan data konfirmasi awal
  const sin = pers.sinyalmen;
  const m = (pers.matra || 'AD').toUpperCase();
  const matraTitle = m === 'AL' ? 'Matra Laut' : (m === 'AU' ? 'Matra Udara' : 'Matra Darat');
  const p = pers.pangkat || 'Prajurit Komcad';
  const isP = p.toUpperCase().includes('LET') || p.toUpperCase().includes('KAP') || p.toUpperCase().includes('MAY');

  confirmationForm.nomor_urut = pers.ekta?.nomor_urut || '';
  confirmationForm.tahun_lulus = pers.ekta?.tahun_lulus || pers.angkatan || new Date().getFullYear();
  confirmationForm.full_name = pers.full_name;
  confirmationForm.nikc = pers.nikc || pers.user?.username || '-';
  confirmationForm.pangkat = p;
  confirmationForm.jabatan = pers.ekta?.jabatan || (isP ? 'Perwira Komcad' : 'Anggota Komcad');
  confirmationForm.kesatuan_matra = pers.ekta?.kesatuan_matra || matraTitle;
  confirmationForm.berlaku_sampai = pers.ekta?.berlaku_sampai || 'Selama Menjadi Anggota Komcad';
  confirmationForm.tinggi_berat = pers.ekta?.tinggi_berat || (sin?.tinggi_badan && sin?.berat_badan ? `${sin.tinggi_badan}/${sin.berat_badan}` : '161/45');
  confirmationForm.rambut = pers.ekta?.rambut || sin?.rambut || 'Bergelombang';
  confirmationForm.mata = pers.ekta?.mata || sin?.mata || 'Coklat';
  confirmationForm.golongan_darah = pers.ekta?.golongan_darah || sin?.golongan_darah || 'O';
  confirmationForm.tempat_lahir = pers.ekta?.tempat_lahir || pers.pob || '-';
  
  let tgl = pers.ekta?.tanggal_lahir || '-';
  if (pers.dob) {
    try {
      tgl = new Date(pers.dob).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    } catch {
      tgl = pers.dob;
    }
  }
  confirmationForm.tanggal_lahir = tgl;
  confirmationForm.agama = pers.ekta?.agama || 'Islam';
  confirmationForm.alamat = pers.ekta?.alamat || pers.address || '-';
};

const closeEktaModal = () => {
  selectedPersonel.value = null;
  issuanceStep.value = 0;
};

// Langkah 1: Minta OTP Email
const startIssuanceFlow = async () => {
  issuanceStep.value = 1;
  await requestOtp();
};

const requestOtp = async () => {
  loadingOtp.value = true;
  otpError.value = '';
  try {
    const res = await fetch(route('admin.ekta.request-otp'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });
    const data = await res.json();
    if (!data.success) {
      otpError.value = data.message || 'Gagal mengirim kode OTP.';
    }
  } catch (err) {
    otpError.value = 'Terjadi kesalahan jaringan saat mengirimkan OTP.';
  } finally {
    loadingOtp.value = false;
  }
};

// Langkah 2: Verifikasi OTP
const verifyOtp = async () => {
  if (otpInput.value.length !== 6) return;
  verifyingOtp.value = true;
  otpError.value = '';

  try {
    const res = await fetch(route('admin.ekta.verify-otp'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ otp: otpInput.value })
    });
    const data = await res.json();
    if (data.success) {
      issuanceStep.value = 2; // Pindah ke form konfirmasi data
    } else {
      otpError.value = data.message || 'Kode OTP tidak cocok.';
    }
  } catch (err) {
    otpError.value = 'Gagal memverifikasi OTP. Coba lagi.';
  } finally {
    verifyingOtp.value = false;
  }
};

// Langkah 3: Animasi Generate E-KTA & Publish
const triggerGenerateAnimation = () => {
  issuanceStep.value = 3;
  genProgress.value = 15;

  const timer1 = setTimeout(() => { genProgress.value = 40; }, 600);
  const timer2 = setTimeout(() => { genProgress.value = 75; }, 1300);
  const timer3 = setTimeout(() => {
    genProgress.value = 100;

    // Submit ke backend
    router.post(route('admin.ekta.publish', selectedPersonel.value.id), {
      nomor_urut: confirmationForm.nomor_urut,
      tahun_lulus: confirmationForm.tahun_lulus,
      pangkat: confirmationForm.pangkat,
      jabatan: confirmationForm.jabatan,
      kesatuan_matra: confirmationForm.kesatuan_matra,
      berlaku_sampai: confirmationForm.berlaku_sampai,
      tinggi_berat: confirmationForm.tinggi_berat,
      rambut: confirmationForm.rambut,
      mata: confirmationForm.mata,
      golongan_darah: confirmationForm.golongan_darah,
      tempat_lahir: confirmationForm.tempat_lahir,
      tanggal_lahir: confirmationForm.tanggal_lahir,
      agama: confirmationForm.agama,
      alamat: confirmationForm.alamat,
    }, {
      preserveScroll: true,
      onSuccess: (page) => {
        // Cari data terbaru dari props
        const updated = page.props.personels.data.find(p => p.id === selectedPersonel.value.id);
        if (updated) {
          selectedPersonel.value = updated;
        }
      }
    });
  }, 2200);
};
</script>
