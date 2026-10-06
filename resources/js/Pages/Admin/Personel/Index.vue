<template>
  <AuthenticatedLayout>
    <template #sidebar-menu>
      <Link :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard</Link>
      <Link :href="route('admin.personel.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Master Personel</Link>
      <Link :href="route('admin.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Broadcast Kegiatan</Link>
      <Link :href="route('admin.setting.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Pengaturan Sistem</Link>
    </template>

    <template #header-title>Manajemen Database Personel Terpusat</template>

    <!-- Kontrol Pencarian & Ekspor Responsif HP -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
        <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none w-full sm:w-64 focus:border-[#2563EB]" placeholder="Cari nama, NIKC, atau NIK..." />
        <select v-model="filterMatra" @change="handleSearch" class="px-4 py-2 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB] w-full sm:w-auto">
          <option value="">Semua Matra</option>
          <option value="AD">TNI AD</option>
          <option value="AL">TNI AL</option>
          <option value="AU">TNI AU</option>
        </select>
      </div>

      <div class="flex items-center gap-2.5 w-full sm:w-auto flex-wrap sm:flex-nowrap">
        <a :href="route('admin.report.personel.excel')" class="flex-1 sm:flex-none text-center px-4 py-2 bg-white border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl transition shadow-xs">Ekspor Excel</a>
        <a :href="route('admin.report.personel.pdf')" class="flex-1 sm:flex-none text-center px-4 py-2 bg-white border border-[#E2E8F0] hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl transition shadow-xs">Cetak PDF</a>
        <Link :href="route('admin.personel.create')" class="w-full sm:w-auto text-center px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white font-semibold text-xs rounded-xl transition shadow-md shadow-blue-500/10">Tambah Personel</Link>
      </div>
    </div>

    <!-- TAMPILAN 1: TABEL DESKTOP (hidden pada HP, tampil di md) -->
    <div class="hidden md:block bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none whitespace-nowrap">
            <th class="p-4">Pasfoto</th>
            <th class="p-4">Identitas Resmi</th>
            <th class="p-4">Matra</th>
            <th class="p-4">Angkatan</th>
            <th class="p-4">OTP Verification</th>
            <th class="p-4 text-right">Opsi Operasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
          <tr v-for="personel in personels.data" :key="personel.id" class="hover:bg-slate-50/30 transition">
            <td class="p-4 whitespace-nowrap">
              <img 
                :src="personel.photo_profile ? getDocumentUrl(personel.photo_profile) : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" 
                class="w-9 h-12 object-cover rounded-lg bg-slate-100 shadow-sm border border-[#E2E8F0] cursor-pointer hover:opacity-80 transition" 
                @click="view360Profil(personel)"
                title="Buka Profil Lengkap"
              />
            </td>
            <td class="p-4 whitespace-nowrap">
              <div class="flex items-center gap-2">
                <button @click="view360Profil(personel)" class="font-bold text-slate-800 hover:text-[#2563EB] hover:underline transition text-left cursor-pointer">
                  {{ personel.full_name }}
                </button>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-wide select-none shrink-0">
                  {{ personel.pangkat || '-' }}
                </span>
              </div>
              <div class="text-xs text-slate-400 mt-1 flex items-center gap-1.5 whitespace-nowrap">
                <span>NIK: <span class="font-medium text-slate-600">{{ personel.nik }}</span></span>
                <span class="text-slate-300">|</span>
                <span>
                  NIKC: 
                  <span v-if="personel.nikc" class="font-mono font-bold text-[#2563EB] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100/60 text-[11px]">
                    {{ personel.nikc }}
                  </span>
                  <span v-else class="font-bold text-slate-300 px-1">-</span>
                </span>
              </div>
            </td>
            <td class="p-4 whitespace-nowrap">
              <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-100/40 inline-block whitespace-nowrap">
                TNI {{ personel.matra }}
              </span>
            </td>
            <td class="p-4 font-semibold text-slate-700 whitespace-nowrap">{{ personel.angkatan }}</td>
            <td class="p-4 whitespace-nowrap">
              <span :class="personel.face_verified ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100'" class="px-2.5 py-0.5 rounded text-[11px] font-semibold inline-block whitespace-nowrap">
                {{ personel.face_verified ? 'OTP Verified' : 'Belum Verifikasi OTP' }}
              </span>
            </td>
            <td class="p-4 text-right whitespace-nowrap space-x-2.5">
              <button @click="view360Profil(personel)" class="text-xs font-semibold text-blue-600 hover:underline cursor-pointer">Profil</button>
              <button @click="handlePrintPdf(personel)" class="text-xs font-semibold text-amber-600 hover:underline cursor-pointer">Cetak PDF</button>
              <Link :href="route('admin.personel.education.index', personel.uuid)" class="text-xs font-semibold text-emerald-600 hover:underline">Pendidikan</Link>
              <Link :href="route('admin.personel.edit', personel.uuid)" class="text-xs font-semibold text-[#2563EB] hover:underline">Edit</Link>
              <button @click="deletePersonel(personel.uuid)" class="text-xs font-semibold text-[#EF4444] hover:underline cursor-pointer">Hapus</button>
            </td>
          </tr>
          <tr v-if="personels.data.length === 0">
            <td colspan="6" class="p-12 text-center text-slate-400 text-sm">Tidak ada record data personel komponen cadangan.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TAMPILAN 2: DAFTAR KARTU HP (tampil di HP, hidden di md) -->
    <div class="md:hidden space-y-4">
      <div v-for="personel in personels.data" :key="personel.id" class="bg-white border border-[#E2E8F0] rounded-3xl p-4 shadow-xs space-y-3.5">
        <div class="flex items-start gap-3.5">
          <img 
            :src="personel.photo_profile ? getDocumentUrl(personel.photo_profile) : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel?.full_name || 'PERS')}&background=e2e8f0&color=334155`" 
            class="w-12 h-16 object-cover rounded-xl bg-slate-100 shadow-sm border border-[#E2E8F0] shrink-0 cursor-pointer hover:opacity-80 transition" 
            @click="view360Profil(personel)"
            title="Buka Profil Lengkap"
          />
          <div class="space-y-1 flex-1 min-w-0">
            <div class="flex items-center gap-1.5 flex-wrap">
              <button @click="view360Profil(personel)" class="font-extrabold text-slate-800 hover:text-[#2563EB] hover:underline transition text-left cursor-pointer truncate max-w-[170px]">
                {{ personel.full_name }}
              </button>
              <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200/60 uppercase tracking-wider select-none shrink-0">
                {{ personel.pangkat || '-' }}
              </span>
            </div>
            
            <p class="text-[11px] text-slate-400">NIK: <span class="font-medium text-slate-600">{{ personel.nik }}</span></p>
            
            <p class="text-[11px] text-slate-400 flex items-center gap-1">
              NIKC: 
              <span v-if="personel.nikc" class="font-mono font-bold text-[#2563EB] bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100/60 text-[10px]">
                {{ personel.nikc }}
              </span>
              <span v-else class="font-bold text-slate-300">-</span>
            </p>
          </div>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-[11px]">
          <div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-[#2563EB] border border-blue-100/40 mr-1.5">
              TNI {{ personel.matra }}
            </span>
            <span class="font-semibold text-slate-500">Angkatan {{ personel.angkatan }}</span>
          </div>
          
          <span :class="personel.face_verified ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100'" class="px-2 py-0.5 rounded text-[10px] font-bold border">
            {{ personel.face_verified ? 'OTP Verified' : 'Belum OTP' }}
          </span>
        </div>

        <div class="flex items-center justify-end gap-3 flex-wrap border-t border-slate-100 pt-3">
          <button @click="view360Profil(personel)" class="text-xs font-extrabold text-blue-600 hover:underline cursor-pointer">Lihat Profil</button>
          <button @click="handlePrintPdf(personel)" class="text-xs font-extrabold text-amber-600 hover:underline cursor-pointer">Cetak PDF</button>
          <Link :href="route('admin.personel.education.index', personel.uuid)" class="text-xs font-extrabold text-emerald-600 hover:underline cursor-pointer">Pendidikan</Link>
          <Link :href="route('admin.personel.edit', personel.uuid)" class="text-xs font-extrabold text-[#2563EB] hover:underline cursor-pointer">Edit</Link>
          <button @click="deletePersonel(personel.uuid)" class="text-xs font-extrabold text-[#EF4444] hover:underline cursor-pointer">Hapus</button>
        </div>
      </div>

      <div v-if="personels.data.length === 0" class="text-center p-12 bg-white border border-[#E2E8F0] rounded-3xl text-slate-400 text-xs italic">
        Tidak ada record data personel komponen cadangan.
      </div>
    </div>

    <!-- Paginator Database -->
    <div v-if="personels.links.length > 3" class="flex justify-center gap-1 mt-6 select-none">
      <button 
        v-for="(link, i) in personels.links" 
        :key="i"
        @click="link.url ? router.get(link.url, { search: searchQuery, matra: filterMatra }, { preserveState: true }) : null"
        :disabled="!link.url || link.active"
        :class="[
          link.active ? 'bg-[#2563EB] text-white font-bold' : 'bg-white hover:bg-slate-50 text-slate-700',
          !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
        ]"
        class="px-3 py-1.5 border border-[#E2E8F0] rounded-lg text-xs transition"
        v-html="link.label"
      />
    </div>

    <!-- Modal Dialog Terpusat Profil Personel 360° -->
    <div v-if="slideOpen && selectedPersonel" class="fixed inset-0 z-50 overflow-y-auto">
      <!-- Backdrop Gelap dengan Efek Blur -->
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="slideOpen = false"></div>

      <!-- Container Dialog Terpusat (Centered Modal) -->
      <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
        <div class="relative w-full max-w-5xl lg:max-w-6xl bg-white rounded-3xl shadow-2xl border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[92vh] text-left my-auto transform transition-all animate-in fade-in zoom-in-95 duration-200">
          
          <!-- Header Modal -->
          <div class="px-6 py-5 border-b border-[#E2E8F0] bg-slate-50/80 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-4 min-w-0">
              <div class="relative shrink-0">
                <img 
                  :src="selectedPersonel.photo_profile ? getDocumentUrl(selectedPersonel.photo_profile) : `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedPersonel.full_name || 'PERS')}&background=e2e8f0&color=334155`" 
                  class="w-12 h-14 object-cover rounded-xl bg-slate-100 border border-[#E2E8F0] shadow-xs cursor-pointer hover:opacity-90 transition"
                  @click="previewImage(selectedPersonel.photo_profile, 'Pasfoto Resmi ' + selectedPersonel.full_name)"
                  alt="Pasfoto"
                  title="Klik untuk perbesar pasfoto"
                />
                <span v-if="selectedPersonel.face_verified" class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full" title="Terverifikasi"></span>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="text-base font-bold text-slate-900 truncate">{{ selectedPersonel.full_name }}</h3>
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-blue-50 text-[#2563EB] border border-blue-200 uppercase tracking-wide shrink-0">
                    {{ selectedPersonel.pangkat || '-' }}
                  </span>
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                    TNI {{ selectedPersonel.matra }}
                  </span>
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">
                    Angkatan {{ selectedPersonel.angkatan }}
                  </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                  <span>NIKC: <strong class="font-mono text-[#2563EB]">{{ selectedPersonel.nikc || '-' }}</strong></span>
                  <span class="text-slate-300">|</span>
                  <span>NIK: <strong class="font-mono text-slate-700">{{ selectedPersonel.nik || '-' }}</strong></span>
                  <span class="text-slate-300">|</span>
                  <span :class="selectedPersonel.face_verified ? 'text-emerald-600 font-semibold' : 'text-amber-600 font-semibold'">
                    {{ selectedPersonel.face_verified ? 'Status: Terverifikasi' : 'Status: Belum Verifikasi OTP' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
              <Link 
                :href="route('admin.personel.edit', selectedPersonel.uuid)"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-xl transition"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                Edit Data
              </Link>
              <button 
                @click="slideOpen = false" 
                class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition cursor-pointer"
                title="Tutup Modal"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
          </div>

          <!-- Tab Bar Navigasi Profil -->
          <div class="px-6 border-b border-[#E2E8F0] flex gap-6 text-xs font-bold select-none bg-white overflow-x-auto shrink-0 scrollbar-none">
            <button 
              @click="currentTab = 'profile'" 
              :class="currentTab === 'profile' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-3.5 shrink-0' : 'text-slate-400 py-3.5 hover:text-slate-700 shrink-0 transition cursor-pointer'"
            >
              IDENTITAS & DOKUMEN FISIK
            </button>
            <button 
              @click="currentTab = 'sinyalmen'" 
              :class="currentTab === 'sinyalmen' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-3.5 shrink-0' : 'text-slate-400 py-3.5 hover:text-slate-700 shrink-0 transition cursor-pointer'"
            >
              SINYALMEN FISIK
            </button>
            <button 
              @click="currentTab = 'education'" 
              :class="currentTab === 'education' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-3.5 shrink-0' : 'text-slate-400 py-3.5 hover:text-slate-700 shrink-0 transition cursor-pointer'"
            >
              RIWAYAT PENDIDIKAN ({{ selectedPersonel.riwayat_pendidikan ? selectedPersonel.riwayat_pendidikan.length : 0 }})
            </button>
            <button 
              @click="currentTab = 'jobs'" 
              :class="currentTab === 'jobs' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-3.5 shrink-0' : 'text-slate-400 py-3.5 hover:text-slate-700 shrink-0 transition cursor-pointer'"
            >
              RIWAYAT PEKERJAAN ({{ selectedPersonel.job_histories ? selectedPersonel.job_histories.length : 0 }})
            </button>
            <button 
              @click="currentTab = 'activities'" 
              :class="currentTab === 'activities' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-3.5 shrink-0' : 'text-slate-400 py-3.5 hover:text-slate-700 shrink-0 transition cursor-pointer'"
            >
              RIWAYAT KEGIATAN ({{ selectedItemResponses.length }})
            </button>
          </div>

          <!-- Isi Tab (Scrollable Area) -->
          <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 space-y-6">
            
            <!-- TAB 1: IDENTITAS & DOKUMEN FISIK (KTP, PASFOTO, SKEP, DOMISILI) -->
            <div v-if="currentTab === 'profile'" class="space-y-6">
              
              <!-- Baris Berkas Dokumen Fisik: KTP & Pasfoto Resmi -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                
                <!-- KARTU 1: FOTO DOKUMEN KTP FISIK -->
                <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-3">
                      <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Dokumen Fisik KTP</h4>
                      </div>
                      <span class="text-[10px] font-mono font-bold px-2 py-0.5 bg-blue-50 text-blue-700 rounded border border-blue-100">
                        NIK: {{ selectedPersonel.nik || '-' }}
                      </span>
                    </div>

                    <!-- Area Pratinjau KTP -->
                    <div class="relative group bg-slate-100 rounded-xl border border-slate-200 overflow-hidden flex items-center justify-center min-h-[200px]">
                      <template v-if="selectedPersonel.ktp_document">
                        <img 
                          :src="getDocumentUrl(selectedPersonel.ktp_document)" 
                          class="w-full h-48 object-contain bg-slate-900/5 cursor-pointer hover:scale-[1.02] transition duration-200" 
                          @click="previewImage(selectedPersonel.ktp_document, 'Foto KTP Fisik - ' + selectedPersonel.full_name)"
                          alt="Foto KTP Personel"
                          title="Klik untuk perbesar KTP"
                        />
                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2.5 p-3">
                          <button 
                            type="button" 
                            @click="previewImage(selectedPersonel.ktp_document, 'Foto KTP Fisik - ' + selectedPersonel.full_name)" 
                            class="px-3 py-1.5 bg-white/95 hover:bg-white text-slate-900 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer"
                          >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" /></svg>
                            Perbesar
                          </button>
                          <a 
                            :href="getDocumentUrl(selectedPersonel.ktp_document)" 
                            target="_blank" 
                            class="px-3 py-1.5 bg-blue-600/90 hover:bg-blue-600 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5"
                          >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka di Tab Baru
                          </a>
                        </div>
                      </template>
                      <div v-else class="text-center p-6 text-slate-400 space-y-1.5">
                        <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.364a4.125 4.125 0 00-6.338 0" /></svg>
                        <p class="text-xs font-bold text-slate-500">Berkas KTP Belum Diunggah</p>
                        <p class="text-[11px] text-slate-400">Personel belum melampirkan berkas foto KTP fisik ke dalam pangkalan data.</p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Nama Sesuai KTP: <strong class="text-slate-800">{{ selectedPersonel.full_name }}</strong></span>
                    <span class="text-[11px] font-semibold text-slate-400">Status KTP: {{ selectedPersonel.nik ? 'Tervalidasi' : 'Belum Ada NIK' }}</span>
                  </div>
                </div>

                <!-- KARTU 2: PASFOTO RESMI & BERKAS MILITER -->
                <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-xs flex flex-col justify-between">
                  <div>
                    <div class="flex items-center justify-between mb-3">
                      <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pasfoto & Kelengkapan Berkas</h4>
                      </div>
                      <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 text-slate-700 rounded border border-slate-200">
                        TNI {{ selectedPersonel.matra }} (Angkatan {{ selectedPersonel.angkatan }})
                      </span>
                    </div>

                    <div class="flex items-start gap-4">
                      <!-- Pasfoto Resmi -->
                      <div class="relative group bg-slate-100 rounded-xl border border-slate-200 overflow-hidden shrink-0 w-28 h-36 flex items-center justify-center shadow-xs">
                        <img 
                          :src="selectedPersonel.photo_profile ? getDocumentUrl(selectedPersonel.photo_profile) : `https://ui-avatars.com/api/?name=${encodeURIComponent(selectedPersonel.full_name || 'PERS')}&background=e2e8f0&color=334155`" 
                          class="w-full h-full object-cover cursor-pointer hover:scale-105 transition"
                          @click="previewImage(selectedPersonel.photo_profile, 'Pasfoto Resmi - ' + selectedPersonel.full_name)"
                          alt="Pasfoto Resmi"
                          title="Klik untuk perbesar pasfoto"
                        />
                        <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                          <button 
                            type="button" 
                            @click="previewImage(selectedPersonel.photo_profile, 'Pasfoto Resmi - ' + selectedPersonel.full_name)"
                            class="p-1.5 bg-white text-slate-900 rounded-lg text-xs font-bold shadow-xs cursor-pointer"
                            title="Perbesar Pasfoto"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" /></svg>
                          </button>
                        </div>
                      </div>

                      <!-- Berkas SKEP & Kelengkapan Militer -->
                      <div class="flex-1 space-y-2.5 text-xs">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                          <p class="text-[10px] font-bold uppercase text-slate-400">Surat Keputusan Pengangkatan (SKEP)</p>
                          <div v-if="selectedPersonel.skep_file" class="flex items-center justify-between">
                            <span class="font-semibold text-slate-700 truncate max-w-[140px]">Berkas SKEP Terlampir</span>
                            <a :href="getDocumentUrl(selectedPersonel.skep_file)" target="_blank" class="text-blue-600 hover:underline font-bold text-[11px] inline-flex items-center gap-1">
                              Buka Dokumen
                              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                          </div>
                          <p v-else class="text-slate-400 italic text-[11px]">Belum ada berkas SKEP yang terunggah.</p>
                        </div>

                        <div v-if="selectedPersonel.is_asn" class="p-3 bg-blue-50/70 border border-blue-200/60 rounded-xl space-y-1">
                          <p class="text-[10px] font-bold uppercase text-blue-700">Surat Keputusan ASN (NIP: {{ selectedPersonel.asn_nip || '-' }})</p>
                          <div v-if="selectedPersonel.asn_sk" class="flex items-center justify-between">
                            <span class="font-semibold text-slate-700 text-[11px] truncate max-w-[140px]">SK {{ selectedPersonel.asn_jenis || 'ASN' }}</span>
                            <a :href="route('personel.document.download', { path: selectedPersonel.asn_sk })" target="_blank" class="text-blue-700 hover:underline font-bold text-[11px] inline-flex items-center gap-1">
                              Unduh SK
                              <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            </a>
                          </div>
                          <p v-else class="text-slate-400 italic text-[11px]">Berkas SK ASN belum diunggah.</p>
                        </div>

                        <div class="text-[11px] text-slate-500 space-y-1">
                          <p>Sumber Rekrutmen: <strong class="text-slate-700">{{ selectedPersonel.sumber_rekrutmen || 'Reguler' }}</strong></p>
                          <p>Status Keaktifan: <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ selectedPersonel.status_keaktifan || 'AKTIF' }}</span></p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-3.5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Format Pangkat Resmi: <strong class="text-slate-800">{{ selectedPersonel.pangkat || '-' }} (KC)</strong></span>
                    <span class="text-[11px] text-slate-400">Profil: {{ selectedPersonel.status_profile || 'Lengkap' }}</span>
                  </div>
                </div>

              </div>

              <!-- LEMBAR ADMINISTRASI LENGKAP -->
              <div class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                  <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    Lembar Administrasi Domisili & Wilayah Penugasan
                  </h4>
                  <span class="text-xs font-semibold text-slate-400">Data Pokok Personel</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-4 gap-x-6 text-xs">
                  <div>
                    <p class="text-slate-400 font-medium">Nomor Induk Komcad (NIKC)</p>
                    <p class="font-mono font-bold text-[#2563EB] text-sm mt-0.5">{{ selectedPersonel.nikc || '-' }}</p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Nomor Induk Kependudukan (NIK)</p>
                    <p class="font-mono font-bold text-slate-800 text-sm mt-0.5">{{ selectedPersonel.nik || '-' }}</p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Tempat, Tanggal Lahir</p>
                    <p class="font-semibold text-slate-800 mt-0.5">
                      {{ selectedPersonel.pob || '-' }}, {{ selectedPersonel.dob || '-' }}
                      <span v-if="selectedPersonel.dob" class="text-slate-400 font-normal">({{ calculateAge(selectedPersonel.dob) }})</span>
                    </p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Jenis Kelamin</p>
                    <p class="font-semibold text-slate-800 mt-0.5">
                      {{ selectedPersonel.gender === 'L' ? 'Laki-laki (Pria)' : (selectedPersonel.gender === 'P' ? 'Perempuan (Wanita)' : '-') }}
                    </p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Komando Utama (Kotama)</p>
                    <p class="font-bold text-blue-700 mt-0.5">{{ selectedPersonel.kotama || '-' }}</p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Satuan Kewilayahan</p>
                    <p class="font-bold text-blue-700 mt-0.5">{{ selectedPersonel.satuan_kewilayahan || '-' }}</p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Nomor WhatsApp Aktif</p>
                    <p class="mt-0.5">
                      <a v-if="selectedPersonel.phone_number" :href="`https://wa.me/${selectedPersonel.phone_number.replace(/[^0-9]/g, '')}`" target="_blank" class="font-semibold text-emerald-600 hover:underline">
                        {{ selectedPersonel.phone_number }}
                      </a>
                      <span v-else class="text-slate-400">-</span>
                    </p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Email Akun Sistem</p>
                    <p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.user?.email || '-' }}</p>
                  </div>

                  <div>
                    <p class="text-slate-400 font-medium">Kode Pos</p>
                    <p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.postal_code || selectedPersonel.zip_code || '-' }}</p>
                  </div>

                  <div class="sm:col-span-2 md:col-span-3 lg:col-span-3">
                    <p class="text-slate-400 font-medium">Alamat Rumah Tinggal Lengkap</p>
                    <p class="font-semibold text-slate-800 mt-0.5 leading-relaxed">
                      {{ selectedPersonel.address || '-' }}
                      <span v-if="selectedPersonel.village">, Kel. {{ selectedPersonel.village }}</span>
                      <span v-if="selectedPersonel.district">, Kec. {{ selectedPersonel.district }}</span>
                      <span v-if="selectedPersonel.city">, {{ selectedPersonel.city }}</span>
                      <span v-if="selectedPersonel.province">, Prov. {{ selectedPersonel.province }}</span>
                    </p>
                  </div>

                  <div v-if="selectedPersonel.catatan_pembinaan" class="col-span-full p-3 bg-amber-50/70 border border-amber-200/70 rounded-xl">
                    <p class="text-amber-800 font-bold text-[11px]">Catatan Pembinaan Personel:</p>
                    <p class="text-slate-700 text-xs italic mt-0.5 leading-relaxed">{{ selectedPersonel.catatan_pembinaan }}</p>
                  </div>
                </div>
              </div>

            </div>

            <!-- TAB 2: SINYALMEN FISIK -->
            <div v-if="currentTab === 'sinyalmen'">
              <div v-if="selectedPersonel.sinyalmen" class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                  <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    Parameter Fisik & Sinyalmen Anggota
                  </h4>
                  <span class="text-xs font-semibold text-slate-400">Pemeriksaan Fisik</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 text-xs">
                  <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Tinggi Badan</p>
                    <p class="font-bold text-slate-800 text-base mt-0.5">{{ selectedPersonel.sinyalmen.tinggi_badan }} <span class="text-xs font-normal text-slate-500">cm</span></p>
                  </div>

                  <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Berat Badan</p>
                    <p class="font-bold text-slate-800 text-base mt-0.5">{{ selectedPersonel.sinyalmen.berat_badan }} <span class="text-xs font-normal text-slate-500">kg</span></p>
                  </div>

                  <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Indeks Massa Tubuh (IMT)</p>
                    <p class="font-bold text-slate-800 text-base mt-0.5">{{ calculateBmi(selectedPersonel.sinyalmen.tinggi_badan, selectedPersonel.sinyalmen.berat_badan) }}</p>
                  </div>

                  <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Golongan Darah</p>
                    <p class="font-bold text-red-600 text-base mt-0.5">{{ selectedPersonel.sinyalmen.golongan_darah || '-' }}</p>
                  </div>

                  <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Bentuk Rambut</p>
                    <p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.rambut || '-' }}</p>
                  </div>

                  <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Warna / Bola Mata</p>
                    <p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.mata || '-' }}</p>
                  </div>

                  <div class="col-span-2 p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Ciri Khas Khusus</p>
                    <p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.ciri_khas || '-' }}</p>
                  </div>

                  <div class="col-span-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-slate-400 font-medium">Kondisi Cacat Tubuh</p>
                    <p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.cacat_tubuh || 'Tidak Ada / Sehat Walafiat' }}</p>
                  </div>
                </div>
              </div>

              <div v-else class="text-center p-12 bg-white border border-[#E2E8F0] rounded-2xl text-slate-400 text-xs font-medium">
                Personel bersangkutan belum melengkapi data isian sinyalmen fisik.
              </div>
            </div>

            <!-- TAB 3: RIWAYAT PENDIDIKAN -->
            <div v-if="currentTab === 'education'" class="space-y-4">
              <div v-if="selectedPersonel.riwayat_pendidikan && selectedPersonel.riwayat_pendidikan.length > 0" class="bg-white border border-[#E2E8F0] rounded-2xl p-6 space-y-4 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                  <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Daftar Riwayat Pendidikan Formal & Militer
                  </h4>
                  <Link :href="route('admin.personel.education.index', selectedPersonel.uuid)" class="text-xs font-bold text-blue-600 hover:underline">
                    Kelola Berkas Pendidikan
                  </Link>
                </div>

                <div class="relative pl-6 border-l border-slate-200 space-y-6">
                  <div v-for="edu in selectedPersonel.riwayat_pendidikan" :key="edu.id" class="relative text-xs">
                    <span :class="[
                      edu.verified_at ? 'bg-emerald-500 ring-4 ring-emerald-50' : 'bg-amber-400 ring-4 ring-amber-50',
                      'absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full border border-white'
                    ]"></span>
                    <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 space-y-1.5">
                      <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                          <span class="font-bold text-slate-900 text-sm">{{ edu.program_studi || '-' }}</span>
                          <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold border border-blue-100">{{ edu.jenis }}</span>
                          <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] font-bold border border-slate-200">{{ edu.jenjang }}</span>
                        </div>
                        <span v-if="edu.verified_at" class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-bold border border-emerald-200">Terverifikasi</span>
                        <span v-else class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-[10px] font-bold border border-amber-200">Belum Terverifikasi</span>
                      </div>

                      <p class="text-slate-700 font-semibold text-xs">{{ edu.nama_institusi || '-' }}</p>
                      <p class="text-slate-500 text-[11px]">Tahun Lulus: {{ edu.tahun_lulus || '-' }} <span v-if="edu.nomor_ijazah"> | No. Ijazah: {{ edu.nomor_ijazah }}</span></p>
                      <p v-if="edu.front_title || edu.suffix_gelar" class="text-slate-500 text-[11px]">Gelar: <strong class="text-slate-800">{{ [edu.front_title, edu.suffix_gelar].filter(Boolean).join(' / ') }}</strong></p>
                      
                      <div v-if="edu.file_ijazah_path" class="pt-2 flex items-center gap-3">
                        <a :href="getDocumentUrl(edu.file_ijazah_path)" target="_blank" class="text-blue-600 hover:underline font-bold inline-flex items-center gap-1 text-[11px]">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                          Buka Berkas Ijazah
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center p-12 bg-white border border-[#E2E8F0] rounded-2xl text-slate-400 text-xs font-medium">
                Belum ada riwayat pendidikan atau diklat militer yang terdaftar.
              </div>
            </div>

            <!-- TAB 4: RIWAYAT PEKERJAAN -->
            <div v-if="currentTab === 'jobs'" class="space-y-4">
              <!-- Data Pokok ASN jika berstatus ASN -->
              <div v-if="selectedPersonel.is_asn" class="bg-blue-50/80 border border-blue-200 rounded-2xl p-5 text-xs space-y-3 shadow-xs">
                <div class="flex items-center justify-between border-b border-blue-200/60 pb-2">
                  <h5 class="font-extrabold text-blue-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    Aparatur Sipil Negara (ASN)
                  </h5>
                  <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-100 text-blue-800 rounded">Status ASN Aktif</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-slate-600">
                  <div><p class="text-slate-400 font-medium">NIP ASN</p><p class="font-mono font-bold text-slate-800 mt-0.5">{{ selectedPersonel.asn_nip || '-' }}</p></div>
                  <div><p class="text-slate-400 font-medium">Jenis ASN</p><p class="font-semibold text-slate-800 mt-0.5">{{ selectedPersonel.asn_jenis || '-' }}</p></div>
                  <div><p class="text-slate-400 font-medium">TMT Pengangkatan</p><p class="font-semibold text-slate-800 mt-0.5">{{ selectedPersonel.asn_tmt || '-' }}</p></div>
                  <div v-if="selectedPersonel.asn_sk">
                    <p class="text-slate-400 font-medium">SK Pengangkatan</p>
                    <a :href="route('personel.document.download', { path: selectedPersonel.asn_sk })" target="_blank" class="text-blue-700 hover:underline font-bold inline-flex items-center gap-1 mt-0.5">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                      Unduh SK ASN
                    </a>
                  </div>
                </div>
              </div>

              <!-- Daftar Riwayat Timeline Karir -->
              <div v-if="selectedPersonel.job_histories && selectedPersonel.job_histories.length > 0" class="bg-white border border-[#E2E8F0] rounded-2xl p-6 space-y-4 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                  <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-600"></span>
                    Riwayat Pekerjaan & Karir Profesi
                  </h4>
                  <span class="text-xs font-semibold text-slate-400">{{ selectedPersonel.job_histories.length }} Catatan</span>
                </div>

                <div class="relative pl-6 border-l border-slate-200 space-y-6">
                  <div v-for="job in selectedPersonel.job_histories" :key="job.id" class="relative text-xs">
                    <span :class="[
                      job.is_current ? 'bg-blue-600 ring-4 ring-blue-50' : 'bg-slate-300 ring-4 ring-slate-50',
                      'absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full border border-white'
                    ]"></span>
                    <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 space-y-1.5">
                      <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-2">
                          <span class="font-bold text-slate-900 text-sm">{{ job.nama_perusahaan }}</span>
                          <span v-if="job.is_current" class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold border border-blue-100">Pekerjaan Aktif</span>
                          <span v-if="job.is_phk" class="px-2 py-0.5 bg-red-50 text-red-700 rounded text-[10px] font-bold border border-red-100">PHK</span>
                        </div>
                        <span class="text-slate-400 text-[11px]">TMT: {{ job.tmt_mulai }} <span v-if="job.is_phk"> s/d {{ job.tmt_phk }}</span></span>
                      </div>
                      <p v-if="job.jabatan" class="text-slate-700 font-semibold text-xs">Jabatan / Peran: {{ job.jabatan }}</p>
                      <p class="text-slate-500 text-[11px]">{{ [job.kecamatan, job.kabupaten, job.provinsi].filter(Boolean).join(', ') }}</p>
                      <p v-if="job.alamat_lengkap" class="text-slate-500 text-[11px] leading-relaxed">{{ job.alamat_lengkap }} <span v-if="job.kode_pos">(Kode Pos: {{ job.kode_pos }})</span></p>
                      
                      <div v-if="job.is_phk" class="bg-red-50/70 border border-red-200 rounded-xl p-3 mt-2">
                        <p class="font-bold text-red-800 text-[11px]">Catatan / Alasan PHK:</p>
                        <p class="text-slate-700 italic text-[11px] mt-0.5 leading-relaxed">{{ job.alasan_phk }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center p-12 bg-white border border-[#E2E8F0] rounded-2xl text-slate-400 text-xs font-medium">
                Belum ada riwayat pekerjaan swasta/Non-ASN yang terdaftar.
              </div>
            </div>

            <!-- TAB 5: RIWAYAT KEGIATAN -->
            <div v-if="currentTab === 'activities'" class="space-y-3">
              <div v-for="response in selectedItemResponses" :key="response.id" class="p-4 bg-white border border-[#E2E8F0] rounded-2xl flex items-center justify-between gap-4 shadow-xs">
                <div class="text-xs space-y-1 max-w-xl">
                  <p class="font-bold text-slate-800 text-sm">{{ response.broadcast?.title || 'Mobilisasi Komcad' }}</p>
                  <p class="text-slate-400 text-[11px]">Dikonfirmasi pada: {{ formatFullDate(response.created_at) }}</p>
                  <p v-if="response.notes" class="text-slate-600 italic mt-1 bg-slate-50 p-2.5 rounded-xl border border-dashed border-[#E2E8F0]">"{{ response.notes }}"</p>
                </div>
                <span :class="response.status === 'HADIR' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : response.status === 'IZIN' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200'" class="px-3 py-1 rounded-xl font-bold border text-xs select-none shrink-0">
                  {{ response.status }}
                </span>
              </div>

              <div v-if="selectedItemResponses.length === 0" class="text-center p-12 bg-white border border-[#E2E8F0] rounded-2xl text-slate-400 text-xs font-medium">
                Belum ada rekam jejak partisipasi atau balasan kegiatan dari personel ini.
              </div>
            </div>

          </div>

          <!-- Footer Modal -->
          <div class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50/80 flex items-center justify-between shrink-0 flex-wrap gap-3">
            <div class="text-xs text-slate-500">
              ID Anggota: <span class="font-mono font-semibold">{{ selectedPersonel.uuid }}</span>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <button 
                type="button" 
                @click="handlePrintPdf(selectedPersonel)" 
                class="px-4 py-2 bg-white border border-[#E2E8F0] hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl transition shadow-xs cursor-pointer"
              >
                Cetak PDF Akun
              </button>
              <Link 
                :href="route('admin.personel.education.index', selectedPersonel.uuid)" 
                class="px-4 py-2 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs rounded-xl transition cursor-pointer"
              >
                Kelola Pendidikan
              </Link>
              <Link 
                :href="route('admin.personel.edit', selectedPersonel.uuid)" 
                class="px-4 py-2 bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs rounded-xl transition shadow-sm cursor-pointer"
              >
                Sunting Data
              </Link>
              <button 
                type="button" 
                @click="slideOpen = false" 
                class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer"
              >
                Tutup
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Modal Lightbox Pratinjau Gambar Resolusi Penuh -->
    <div v-if="lightboxOpen" class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md" @click.self="lightboxOpen = false">
      <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center">
        <div class="w-full flex items-center justify-between text-white pb-3">
          <span class="text-xs font-bold tracking-wide">{{ lightboxTitle || 'Pratinjau Dokumen' }}</span>
          <button @click="lightboxOpen = false" class="p-1 rounded-xl hover:bg-white/20 text-white transition cursor-pointer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <img :src="lightboxSrc" class="max-w-full max-h-[75vh] object-contain rounded-2xl shadow-2xl border border-white/20 bg-slate-900" />
        <div class="mt-4 flex items-center gap-3">
          <a :href="lightboxSrc" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition shadow-md">
            Buka File Ukuran Penuh
          </a>
          <button @click="lightboxOpen = false" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-bold rounded-xl transition cursor-pointer">
            Tutup Pratinjau
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useSwal } from '@/Composables/useSwal';
import Swal from 'sweetalert2';

const props = defineProps({ personels: Object, filters: Object });
const page = usePage();
const { confirmAction, alertSuccess, showLoadingProgress, closeLoading } = useSwal();

const searchQuery = ref(props.filters.search || '');
const filterMatra = ref(props.filters.matra || '');

// State Modal Terpusat Profil 360°
const slideOpen = ref(false);
const currentTab = ref('profile');
const selectedPersonel = ref(null);
const selectedItemResponses = ref([]);

// State Modal Lightbox Pratinjau Gambar (KTP / Pasfoto)
const lightboxOpen = ref(false);
const lightboxSrc = ref('');
const lightboxTitle = ref('');

const previewImage = (path, title) => {
  if (!path) return;
  lightboxSrc.value = getDocumentUrl(path);
  lightboxTitle.value = title;
  lightboxOpen.value = true;
};

const getDocumentUrl = (path) => {
  if (!path) return '';
  const cleanPath = path.replace(/^(app\/private\/|app\/public\/|app\/|private\/|storage\/|public\/)+/, '');
  return `/documents/private-stream?path=${encodeURIComponent(cleanPath)}`;
};

const calculateAge = (dob) => {
  if (!dob) return '';
  const birthDate = new Date(dob);
  if (isNaN(birthDate.getTime())) return '';
  const ageDifMs = Date.now() - birthDate.getTime();
  const ageDate = new Date(ageDifMs);
  return Math.abs(ageDate.getUTCFullYear() - 1970) + ' Tahun';
};

const calculateBmi = (tb, bb) => {
  if (!tb || !bb) return '-';
  const tmMeter = tb / 100;
  const bmi = (bb / (tmMeter * tmMeter)).toFixed(1);
  let status = 'Normal';
  if (bmi < 18.5) status = 'Kurang';
  else if (bmi >= 25 && bmi < 30) status = 'Kelebihan';
  else if (bmi >= 30) status = 'Obesitas';
  return `${bmi} (${status})`;
};

onMounted(() => {
  const creds = page.props.flash?.created_credentials;
  if (creds) {
    Swal.fire({
      title: 'Akun Personel Berhasil Dibuat',
      html: `
        <div style="text-align: left; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 13px; margin-top: 10px; line-height: 1.6;">
          <p style="margin: 4px 0;"><strong>Nama Lengkap:</strong> ${creds.full_name}</p>
          <p style="margin: 4px 0;"><strong>Pangkat:</strong> ${creds.pangkat || 'Prajurit'}</p>
          <p style="margin: 4px 0;"><strong>Matra:</strong> TNI ${creds.matra}</p>
          <p style="margin: 4px 0;"><strong>NIKC / Username:</strong> <span style="font-family: monospace; font-weight: bold; color: #2563eb;">${creds.nikc}</span></p>
          <p style="margin: 4px 0;"><strong>Password Akun:</strong> <span style="font-family: monospace; font-weight: bold; color: #dc2626;">${creds.password}</span></p>
        </div>
        <p style="font-size: 11px; color: #64748b; margin-top: 12px;">Klik tombol di bawah untuk mencetak atau mengunduh Lembar Informasi Kredensial Akun (PDF).</p>
      `,
      icon: 'success',
      showCancelButton: true,
      confirmButtonColor: '#2563EB',
      cancelButtonColor: '#64748B',
      confirmButtonText: 'Cetak PDF Informasi Akun',
      cancelButtonText: 'Selesai',
    }).then((res) => {
      if (res.isConfirmed && creds.pdf_url) {
        window.open(creds.pdf_url, '_blank');
      }
    });
  }
});

const handlePrintPdf = async (personel) => {
  const { value: inputPwd } = await Swal.fire({
    title: 'Cetak PDF Informasi Akun',
    text: `Masukkan kata sandi yang akan dicantumkan pada dokumen PDF untuk ${personel.full_name}:`,
    input: 'text',
    inputValue: personel.nik,
    inputLabel: 'Kata Sandi Akun (Password Tampil Terbuka)',
    showCancelButton: true,
    confirmButtonColor: '#2563EB',
    cancelButtonColor: '#64748B',
    confirmButtonText: 'Buka & Cetak PDF',
    cancelButtonText: 'Batal',
    inputValidator: (val) => {
      if (!val) return 'Kata sandi tidak boleh kosong!';
    }
  });

  if (inputPwd) {
    const url = route('admin.personel.print-account', { uuid: personel.uuid, pwd: inputPwd });
    window.open(url, '_blank');
  }
};

const handleSearch = () => {
  router.get(route('admin.personel.index'), { search: searchQuery.value, matra: filterMatra.value }, { preserveState: true });
};

const view360Profil = (personel) => {
  selectedPersonel.value = personel;
  selectedItemResponses.value = personel.broadcast_responses || [];
  currentTab.value = 'profile';
  slideOpen.value = true;
};

const deletePersonel = async (uuid) => {
  const isConfirmed = await confirmAction('Hapus Data Personel?', 'Tindakan ini bersifat permanen dan menghapus akun akses login anggota terkait.', 'Ya, Hapus Data');
  if (isConfirmed) {
    router.delete(route('admin.personel.destroy', uuid), {
      onBefore: () => showLoadingProgress('Menghapus Data dari Server...'),
      onSuccess: () => {
        closeLoading();
        alertSuccess('Berhasil Dihapus', 'Data personel telah dikeluarkan dari database instansi.');
      },
      onError: (errs) => {
        closeLoading();
        Swal.fire('Gagal Menghapus', errs?.error || 'Terjadi kesalahan saat menghapus data.', 'error');
      },
      onFinish: () => {
        closeLoading();
      }
    });
  }
};

const formatFullDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
</script>
