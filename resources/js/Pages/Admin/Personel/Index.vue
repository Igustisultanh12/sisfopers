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
    <div class="hidden md:block bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none">
            <th class="p-4">Pasfoto</th>
            <th class="p-4">Identitas Resmi</th>
            <th class="p-4">Matra</th>
            <th class="p-4">Angkatan</th>
            <th class="p-4">Biometrik Wajah</th>
            <th class="p-4 text-right">Opsi Operasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#E2E8F0] text-sm text-slate-600">
          <tr v-for="personel in personels.data" :key="personel.id" class="hover:bg-slate-50/30 transition">
            <td class="p-4">
              <img :src="personel.photo_profile ? `/documents/private/${personel.photo_profile}` : '/storage/default.png'" class="w-9 h-12 object-cover rounded-lg bg-slate-100 shadow-sm border border-[#E2E8F0]" />
            </td>
            <td class="p-4">
              <div class="flex items-center gap-2">
                <button @click="view360Profil(personel)" class="font-bold text-slate-800 hover:text-[#2563EB] hover:underline transition text-left cursor-pointer">
                  {{ personel.full_name }}
                </button>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-wide select-none">
                  {{ personel.pangkat || '-' }}
                </span>
              </div>
              <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-1.5">
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
            <td class="p-4">
              <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-100/40">
                TNI {{ personel.matra }}
              </span>
            </td>
            <td class="p-4 font-semibold text-slate-700">{{ personel.angkatan }}</td>
            <td class="p-4">
              <span :class="personel.face_verified ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-amber-50 text-amber-700 border border-amber-100'" class="px-2.5 py-0.5 rounded text-[11px] font-semibold">
                {{ personel.face_verified ? 'Face Verified' : 'Belum Pemindaian' }}
              </span>
            </td>
            <td class="p-4 text-right space-x-2.5">
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
          <img :src="personel.photo_profile ? `/documents/private/${personel.photo_profile}` : '/storage/default.png'" class="w-12 h-16 object-cover rounded-xl bg-slate-100 shadow-sm border border-[#E2E8F0] shrink-0" />
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
          
          <span :class="personel.face_verified ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-amber-50 text-amber-700 border border-amber-100'" class="px-2 py-0.5 rounded text-[10px] font-bold border">
            {{ personel.face_verified ? 'Verified' : 'Belum Scan' }}
          </span>
        </div>

        <div class="flex items-center justify-end gap-3 flex-wrap border-t border-slate-100 pt-3">
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

    <!-- 360° Profile Slide-over -->
    <div v-if="slideOpen" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-xs transition-opacity" @click="slideOpen = false"></div>
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
          <div class="pointer-events-auto w-screen max-w-xl border-l border-[#E2E8F0] bg-white shadow-2xl flex flex-col">
            
            <div class="p-6 border-b border-[#E2E8F0] bg-slate-50/50 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <img 
                  :src="selectedPersonel.photo_profile ? `/documents/private/${selectedPersonel.photo_profile}` : '/storage/default.png'" 
                  class="w-10 h-10 object-cover rounded-xl bg-slate-100 border border-[#E2E8F0] shadow-sm shrink-0" 
                />
                <div>
                  <h3 class="text-sm font-bold text-slate-800">Profil Lengkap {{ selectedPersonel.full_name }}</h3>
                  <p class="text-xs text-slate-400 mt-0.5">NIKC: {{ selectedPersonel.nikc || '-' }}</p>
                </div>
              </div>
              <button @click="slideOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>

            <div class="px-6 border-b border-[#E2E8F0] flex gap-5 text-xs font-bold select-none bg-white overflow-x-auto scrollbar-none">
              <button @click="currentTab = 'profile'" :class="currentTab === 'profile' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5 shrink-0' : 'text-slate-400 py-4.5 hover:text-slate-600 shrink-0'">IDENTITAS UTAMA</button>
              <button @click="currentTab = 'sinyalmen'" :class="currentTab === 'sinyalmen' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5 shrink-0' : 'text-slate-400 py-4.5 hover:text-slate-600 shrink-0'">SINYALMEN FISIK</button>
              <button @click="currentTab = 'education'" :class="currentTab === 'education' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5 shrink-0' : 'text-slate-400 py-4.5 hover:text-slate-600 shrink-0'">RIWAYAT PENDIDIKAN ({{ selectedPersonel.riwayat_pendidikan ? selectedPersonel.riwayat_pendidikan.length : 0 }})</button>
              <button @click="currentTab = 'activities'" :class="currentTab === 'activities' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5 shrink-0' : 'text-slate-400 py-4.5 hover:text-slate-600 shrink-0'">RIWAYAT KEGIATAN ({{ selectedItemResponses.length }})</button>
              <button @click="currentTab = 'jobs'" :class="currentTab === 'jobs' ? 'text-[#2563EB] border-b-2 border-[#2563EB] py-4.5 shrink-0' : 'text-slate-400 py-4.5 hover:text-slate-600 shrink-0'">RIWAYAT PEKERJAAN ({{ selectedPersonel.job_histories ? selectedPersonel.job_histories.length : 0 }})</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/40 space-y-5">
              
              <div v-if="currentTab === 'profile'" class="space-y-4">
                <div class="flex gap-4 items-start bg-white p-4 border border-[#E2E8F0] rounded-xl shadow-xs">
                  <img :src="selectedPersonel.photo_profile ? `/documents/private/${selectedPersonel.photo_profile}` : '/storage/default.png'" class="w-16 h-20 object-cover rounded-lg border border-[#E2E8F0] bg-slate-50" />
                  <div class="space-y-1 text-xs">
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                      {{ selectedPersonel.full_name }}
                      <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-blue-50 text-[#2563EB] border border-blue-100/60">{{ selectedPersonel.pangkat || '-' }}</span>
                    </h4>
                    <p class="text-slate-400">Komponen Cadangan Matra {{ selectedPersonel.matra }} (Angkatan {{ selectedPersonel.angkatan }})</p>
                    <p class="text-slate-400">Email Akun: {{ selectedPersonel.user?.email || '-' }} | WA: {{ selectedPersonel.phone_number }}</p>
                  </div>
                </div>

                <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 text-xs space-y-3">
                  <h4 class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Lembar Administrasi Domisili</h4>
                  <div class="grid grid-cols-2 gap-3.5">
                    <div><p class="text-slate-400">NIK Pokok</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.nik }}</p></div>
                    <div><p class="text-slate-400">Tempat, Tanggal Lahir</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.pob }}, {{ selectedPersonel.dob }}</p></div>
                    <div><p class="text-slate-400">Jenis Kelamin</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p></div>
                    <div><p class="text-slate-400">Kode Pos</p><p class="font-semibold text-slate-700 mt-0.5">{{ selectedPersonel.postal_code }}</p></div>
                    <div class="col-span-2"><p class="text-slate-400">Alamat Rumah Tinggal</p><p class="font-semibold text-slate-700 mt-0.5 leading-relaxed">{{ selectedPersonel.address }}, KEL. {{ selectedPersonel.village }}, KEC. {{ selectedPersonel.district }}, {{ selectedPersonel.city }}, {{ selectedPersonel.province }}</p></div>
                  </div>
                </div>
              </div>

              <div v-if="currentTab === 'sinyalmen'">
                <div v-if="selectedPersonel.sinyalmen" class="bg-white border border-[#E2E8F0] rounded-xl p-5 text-xs grid grid-cols-2 gap-4">
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Tinggi Badan</p><p class="font-bold text-slate-800 text-sm mt-0.5">{{ selectedPersonel.sinyalmen.tinggi_badan }} cm</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Berat Badan</p><p class="font-bold text-slate-800 text-sm mt-0.5">{{ selectedPersonel.sinyalmen.berat_badan }} kg</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Golongan Darah</p><p class="font-bold text-slate-800 text-sm mt-0.5">{{ selectedPersonel.sinyalmen.golongan_darah || '-' }}</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Bentuk Rambut</p><p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.rambut || '-' }}</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Bola Mata</p><p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.mata || '-' }}</p></div>
                  <div class="p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Ciri Khas Khusus</p><p class="font-bold text-slate-800 mt-0.5">{{ selectedPersonel.sinyalmen.ciri_khas || '-' }}</p></div>
                  <div class="col-span-2 p-3 bg-slate-50/70 border border-[#E2E8F0] rounded-lg"><p class="text-slate-400">Kondisi Cacat Tubuh</p><p class="font-bold text-red-600 mt-0.5">{{ selectedPersonel.sinyalmen.cacat_tubuh || 'Tidak Ada / Sehat Walafiat' }}</p></div>
                </div>
                <div v-else class="text-center p-8 bg-white border border-[#E2E8F0] rounded-xl text-slate-400 text-xs font-medium">
                  Personel bersangkutan belum melengkapi data isian sinyalmen fisik.
                </div>
              </div>

              <div v-if="currentTab === 'education'" class="space-y-4">
                <div v-if="selectedPersonel.riwayat_pendidikan && selectedPersonel.riwayat_pendidikan.length > 0" class="bg-white border border-[#E2E8F0] rounded-xl p-5 space-y-4">
                  <div class="relative pl-6 border-l border-slate-100 space-y-6">
                    <div v-for="edu in selectedPersonel.riwayat_pendidikan" :key="edu.id" class="relative text-xs">
                      <span :class="[
                        edu.verified_at ? 'bg-emerald-500 ring-4 ring-emerald-50' : 'bg-amber-400 ring-4 ring-amber-50',
                        'absolute -left-[29px] top-0.5 w-3.5 h-3.5 rounded-full border border-white'
                      ]"></span>
                      <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                          <span class="font-bold text-slate-800">{{ edu.program_studi || '-' }}</span>
                          <span class="px-1.5 py-0.2 bg-blue-50 text-blue-700 rounded text-[9px] font-bold">{{ edu.jenis }}</span>
                          <span class="px-1.5 py-0.2 bg-slate-50 text-slate-600 rounded text-[9px] font-bold border border-slate-100">{{ edu.jenjang }}</span>
                          <span v-if="edu.verified_at" class="px-1.5 py-0.2 bg-emerald-50 text-emerald-700 rounded text-[9px] font-bold">Terverifikasi</span>
                          <span v-else class="px-1.5 py-0.2 bg-amber-50 text-amber-700 rounded text-[9px] font-bold">Pending</span>
                        </div>
                        <p class="text-slate-600 font-semibold text-[11px]">{{ edu.nama_institusi || '-' }}</p>
                        <p class="text-slate-400 text-[10px]">Tahun Lulus: {{ edu.tahun_lulus || '-' }} <span v-if="edu.nomor_ijazah"> | No. Ijazah: {{ edu.nomor_ijazah }}</span></p>
                        <p v-if="edu.front_title || edu.suffix_gelar" class="text-slate-500 text-[10px]">Gelar: <span class="font-semibold text-slate-700">{{ [edu.front_title, edu.suffix_gelar].filter(Boolean).join(' / ') }}</span></p>
                        
                        <div v-if="edu.file_ijazah_path" class="pt-1">
                          <a :href="route('personel.document.download', { path: edu.file_ijazah_path })" target="_blank" class="text-blue-600 hover:underline font-bold flex items-center gap-1 text-[10px]">
                            📂 Lihat Berkas Ijazah
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center p-8 bg-white border border-[#E2E8F0] rounded-xl text-slate-400 text-xs font-medium">
                  Belum ada riwayat pendidikan atau diklat yang terdaftar.
                </div>
              </div>

              <div v-if="currentTab === 'activities'" class="space-y-3">
                <div v-for="response in selectedItemResponses" :key="response.id" class="p-4 bg-white border border-[#E2E8F0] rounded-xl flex items-center justify-between gap-4 shadow-2xs">
                  <div class="text-xs space-y-1 max-w-sm">
                    <p class="font-bold text-slate-800 truncate">{{ response.broadcast?.title || 'Mobilisasi Komcad' }}</p>
                    <p class="text-slate-400 text-[11px]">Dikonfirmasi pada: {{ formatFullDate(response.created_at) }}</p>
                    <p v-if="response.notes" class="text-slate-500 italic mt-1 bg-slate-50 p-2 rounded border border-dashed border-[#E2E8F0]">"{{ response.notes }}"</p>
                  </div>
                  <span :class="response.status === 'HADIR' ? 'bg-green-50 text-green-700 border-green-200' : response.status === 'IZIN' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200'" class="px-2.5 py-0.5 rounded-lg font-bold border text-[10px] select-none shrink-0">
                    {{ response.status }}
                  </span>
                </div>

                <div v-if="selectedItemResponses.length === 0" class="text-center p-12 bg-white border border-[#E2E8F0] rounded-xl text-slate-400 text-xs font-medium">
                  Belum ada rekam jejak partisipasi atau balasan kegiatan dari personel ini.
                </div>
              </div>

              <div v-if="currentTab === 'jobs'" class="space-y-4">
                <!-- Data Pokok ASN jika berstatus ASN -->
                <div v-if="selectedPersonel.is_asn" class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs space-y-2">
                  <h5 class="font-extrabold text-blue-800 uppercase tracking-wider">Aparatur Sipil Negara (ASN)</h5>
                  <div class="grid grid-cols-2 gap-2 text-slate-600">
                    <div><p class="text-slate-400">NIP</p><p class="font-semibold text-slate-700">{{ selectedPersonel.asn_nip || '-' }}</p></div>
                    <div><p class="text-slate-400">Jenis ASN</p><p class="font-semibold text-slate-700">{{ selectedPersonel.asn_jenis || '-' }}</p></div>
                    <div><p class="text-slate-400">TMT Pengangkatan</p><p class="font-semibold text-slate-700">{{ selectedPersonel.asn_tmt || '-' }}</p></div>
                    <div v-if="selectedPersonel.asn_sk">
                      <p class="text-slate-400">SK Pengangkatan</p>
                      <a :href="route('personel.document.download', { path: selectedPersonel.asn_sk })" target="_blank" class="text-blue-600 hover:underline font-bold">Download SK</a>
                    </div>
                  </div>
                </div>

                <!-- Daftar Riwayat Timeline Karir -->
                <div v-if="selectedPersonel.job_histories && selectedPersonel.job_histories.length > 0" class="bg-white border border-[#E2E8F0] rounded-xl p-5 space-y-4">
                  <div class="relative pl-6 border-l border-slate-100 space-y-6">
                    <div v-for="job in selectedPersonel.job_histories" :key="job.id" class="relative text-xs">
                      <span :class="[
                        job.is_current ? 'bg-blue-500 ring-4 ring-blue-50' : 'bg-slate-300 ring-4 ring-slate-50',
                        'absolute -left-[29px] top-0.5 w-3.5 h-3.5 rounded-full border border-white'
                      ]"></span>
                      <div class="space-y-1">
                        <div class="flex items-center gap-2">
                          <span class="font-bold text-slate-800">{{ job.nama_perusahaan }}</span>
                          <span v-if="job.is_current" class="px-1.5 py-0.2 bg-blue-50 text-blue-700 rounded text-[9px] font-bold">Aktif</span>
                          <span v-if="job.is_phk" class="px-1.5 py-0.2 bg-red-50 text-red-700 rounded text-[9px] font-bold">PHK</span>
                        </div>
                        <p v-if="job.jabatan" class="text-slate-600 font-semibold text-[11px]">Jabatan/Peran: {{ job.jabatan }}</p>
                        <p class="text-slate-400 text-[10px]">TMT: {{ job.tmt_mulai }} <span v-if="job.is_phk"> s/d {{ job.tmt_phk }}</span></p>
                        <p class="text-slate-500">{{ job.kecamatan }}, {{ job.kabupaten }}, {{ job.provinsi }}</p>
                        <p class="text-slate-500 leading-relaxed">{{ job.alamat_lengkap }} (Kode Pos: {{ job.kode_pos || '-' }})</p>
                        <div v-if="job.is_phk" class="bg-red-50/50 border border-red-100 rounded-lg p-2.5 mt-1.5">
                          <p class="font-bold text-red-700">Alasan PHK:</p>
                          <p class="text-slate-600 italic">"{{ job.alasan_phk }}"</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center p-8 bg-white border border-[#E2E8F0] rounded-xl text-slate-400 text-xs font-medium">
                  Belum ada riwayat pekerjaan swasta/Non-ASN yang terdaftar.
                </div>
              </div>

            </div>
          </div>
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

// State Manifes Slide-Over 360°
const slideOpen = ref(false);
const currentTab = ref('profile');
const selectedPersonel = ref(null);
const selectedItemResponses = ref([]);

onMounted(() => {
  const creds = page.props.flash?.created_credentials;
  if (creds) {
    Swal.fire({
      title: '🎉 Akun Personel Berhasil Dibuat!',
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
      confirmButtonText: '🖨️ Cetak PDF Informasi Akun',
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
    title: '🖨️ Cetak PDF Informasi Akun',
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
      }
    });
  }
};

const formatFullDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB';
</script>
