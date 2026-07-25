<template>
  <AuthenticatedLayout>
    <!-- Sidebar Menu Slot -->
    <template #sidebar-menu>
      <Link :href="route('personel.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard Saya</Link>
      <Link :href="route('personel.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Kegiatan & Mobilisasi</Link>
      <Link :href="route('personel.job.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Riwayat Pekerjaan</Link>
      <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Pengaturan Akun</Link>
    </template>

    <template #header-title>Riwayat & Data Pekerjaan</template>

    <div class="space-y-6 max-w-4xl">

      <!-- ALERT UTAMA UNTUK AKUN ASN -->
      <div v-if="personel.is_asn" class="bg-gradient-to-r from-blue-500/10 to-indigo-500/10 border border-blue-200 rounded-3xl p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="text-lg">👔</span>
            <h3 class="text-sm font-extrabold text-blue-800 uppercase tracking-wider">Pegawai Negeri Sipil / ASN Terdaftar</h3>
          </div>
          <p class="text-xs text-slate-600">Akun Anda berstatus ASN terverifikasi. Data riwayat pekerjaan disinkronkan langsung dari profil pendaftaran Anda dan dapat diperbarui melalui tombol aksi di bawah.</p>
        </div>
        <div v-if="personel.asn_sk" class="shrink-0">
          <a :href="route('personel.document.download', { path: personel.asn_sk })" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <span>📄</span> Lihat SK Pengangkatan
          </a>
        </div>
      </div>

      <!-- KARTU UTAMA PEKERJAAN AKTIF (ASN & NON-ASN) -->
      <div v-if="currentJob" class="bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-xs space-y-6">
        <div class="flex items-center justify-between border-b border-[#E2E8F0] pb-4">
          <div class="space-y-1">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pekerjaan Aktif Saat Ini</p>
            <h2 class="text-lg font-extrabold text-slate-800">{{ currentJob.nama_perusahaan }}</h2>
          </div>
          <span :class="[
            currentJob.tipe_pekerjaan === 'TIDAK_BEKERJA' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100',
            'px-3 py-1 rounded-full text-xs font-bold border'
          ]">
            {{ currentJob.tipe_pekerjaan === 'TIDAK_BEKERJA' ? 'Tidak Bekerja' : (currentJob.tipe_pekerjaan === 'ASN' ? 'ASN' : 'Bekerja') }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
          <div class="space-y-4">
            <div v-if="currentJob.tipe_pekerjaan === 'ASN' || currentJob.nip">
              <span class="text-xs text-slate-400 block">Nomor Induk Pegawai (NIP)</span>
              <span class="font-semibold text-slate-700">{{ currentJob.nip || personel.asn_nip || '-' }}</span>
            </div>
            <div v-else-if="currentJob.nomor_karyawan">
              <span class="text-xs text-slate-400 block">
                {{ currentJob.tipe_pekerjaan === 'PELAJAR' ? 'NIM / Nomor Induk Siswa' : 'Nomor Karyawan' }}
              </span>
              <span class="font-semibold text-slate-700">{{ currentJob.nomor_karyawan }}</span>
            </div>
            
            <div v-if="currentJob.jabatan">
              <span class="text-xs text-slate-400 block">
                {{ currentJob.tipe_pekerjaan === 'ASN' ? 'Jabatan di Instansi' : (currentJob.tipe_pekerjaan === 'PELAJAR' ? 'Kelas / Jurusan' : 'Jabatan / Posisi Kerja') }}
              </span>
              <span class="font-semibold text-slate-700">{{ currentJob.jabatan }}</span>
            </div>

            <div v-if="currentJob.tipe_pekerjaan === 'ASN' && personel.asn_jenis">
              <span class="text-xs text-slate-400 block">Jenis Kepegawaian</span>
              <span class="font-semibold text-slate-700">{{ personel.asn_jenis }}</span>
            </div>

            <div>
              <span class="text-xs text-slate-400 block">
                {{ currentJob.tipe_pekerjaan === 'ASN' ? 'TMT Pengangkatan' : (currentJob.tipe_pekerjaan === 'PELAJAR' ? 'TMT Mulai Pendidikan' : 'TMT Mulai Bekerja / Usaha') }}
              </span>
              <span class="font-semibold text-slate-700">{{ formatDate(currentJob.tmt_mulai) }}</span>
            </div>
            <div>
              <span class="text-xs text-slate-400 block">
                {{ currentJob.tipe_pekerjaan === 'ASN' ? 'Lokasi Kantor / Instansi' : (currentJob.tipe_pekerjaan === 'PELAJAR' ? 'Lokasi Pendidikan' : 'Lokasi Kerja') }}
              </span>
              <span class="font-semibold text-slate-700">{{ currentJob.kecamatan }}, {{ currentJob.kabupaten }}, {{ currentJob.provinsi }}</span>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <span class="text-xs text-slate-400 block">
                {{ currentJob.tipe_pekerjaan === 'ASN' ? 'Alamat Lengkap Instansi' : (currentJob.tipe_pekerjaan === 'SWASTA' ? 'Alamat Lengkap Perusahaan' : (currentJob.tipe_pekerjaan === 'WIRASWASTA' ? 'Alamat Lengkap Tempat Usaha' : 'Alamat Lengkap Instansi Pendidikan')) }}
              </span>
              <span class="font-semibold text-slate-700 block leading-relaxed">{{ currentJob.alamat_lengkap }}</span>
            </div>
            <div v-if="currentJob.kode_pos">
              <span class="text-xs text-slate-400 block">Kode Pos</span>
              <span class="font-semibold text-slate-700">{{ currentJob.kode_pos }}</span>
            </div>
          </div>
        </div>

        <!-- Tombol Aksi Update & PHK -->
        <div class="flex items-center gap-3 pt-4 border-t border-[#E2E8F0]">
          <button @click="startUpdateJobFlow('UPDATE_JOB')" :disabled="lockCountdown > 0" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3 3 3m-3-3v12"></path></svg>
            {{ lockCountdown > 0 ? `Terkunci (${formatCountdown(lockCountdown)})` : 'Perbarui Pekerjaan Baru' }}
          </button>
          <button v-if="currentJob.tipe_pekerjaan !== 'TIDAK_BEKERJA'" @click="startUpdateJobFlow('PHK')" :disabled="lockCountdown > 0" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-bold rounded-xl border border-red-200 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            {{ lockCountdown > 0 ? `Terkunci (${formatCountdown(lockCountdown)})` : 'Laporkan PHK' }}
          </button>
        </div>
      </div>

      <!-- FORM PENGISIAN / AMANDEMEN PEKERJAAN (V-IF) -->
      <div v-if="showJobForm" class="bg-white border border-blue-200 rounded-3xl p-6 shadow-md space-y-6 animate-slide-up">
        <div class="border-b border-[#E2E8F0] pb-4 flex items-center justify-between">
          <h3 class="text-base font-extrabold text-slate-800">
            Formulir Pembaruan Data Kepegawaian & Pekerjaan
          </h3>
          <button @click="cancelForm" class="text-xs text-slate-400 hover:text-slate-600 font-semibold">Batal</button>
        </div>

        <form @submit.prevent="submitJob" class="space-y-5">
          <!-- Dropdown Status Pekerjaan Baru -->
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Status Pekerjaan Baru</label>
            <select v-model="jobForm.tipe_pekerjaan" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] bg-white font-semibold text-slate-700" required>
              <option value="SWASTA">Swasta (Karyawan Swasta)</option>
              <option value="ASN">Aparatur Sipil Negara (ASN)</option>
              <option value="WIRASWASTA">Wiraswasta / Pemilik Usaha</option>
              <option value="PELAJAR">Pelajar / Mahasiswa</option>
              <option value="TIDAK_BEKERJA">Tidak Bekerja / Sedang Mencari Kerja</option>
              <option value="PHK">Terkena PHK</option>
            </select>
          </div>

          <!-- CASE 1: INPUT KHUSUS TERKENA PHK -->
          <div v-if="jobForm.tipe_pekerjaan === 'PHK'" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-slide-up">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">TMT Berhenti / PHK / Resign</label>
              <input type="date" v-model="jobForm.tmt_phk" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-red-500" required />
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Alasan Pemutusan Kerja / PHK / Resign</label>
              <textarea v-model="jobForm.alasan_phk" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-red-500" rows="3" placeholder="Contoh: Pengurangan karyawan, Kontrak kerja selesai, dll." required></textarea>
            </div>
          </div>

          <!-- CASE 2: TIDAK BEKERJA / MENCARI KERJA (TIDAK BUTUH INPUT APAPUN) -->
          <div v-else-if="jobForm.tipe_pekerjaan === 'TIDAK_BEKERJA'" class="p-5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-500 text-xs italic text-center animate-slide-up">
            Anda akan mendaftarkan status sebagai "Tidak Bekerja / Sedang Mencari Kerja". Klik tombol Simpan di bawah untuk memperbarui status Anda.
          </div>

          <!-- CASE 3: INPUT AKTIF (ASN, SWASTA, WIRASWASTA, PELAJAR) -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-slide-up">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">
                {{ jobForm.tipe_pekerjaan === 'ASN' ? 'Nama Satuan Kerja / Instansi' : (jobForm.tipe_pekerjaan === 'SWASTA' ? 'Nama Perusahaan atau PT' : (jobForm.tipe_pekerjaan === 'WIRASWASTA' ? 'Nama Usaha / Bidang Wiraswasta' : 'Nama Sekolah / Universitas')) }}
              </label>
              <input type="text" v-model="jobForm.nama_perusahaan" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" :placeholder="jobForm.tipe_pekerjaan === 'ASN' ? 'Contoh: Kementerian Pertahanan' : (jobForm.tipe_pekerjaan === 'SWASTA' ? 'Masukkan nama perusahaan' : (jobForm.tipe_pekerjaan === 'WIRASWASTA' ? 'Contoh: Toko Sembako Sultan' : 'Masukkan nama instansi pendidikan'))" required />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">
                {{ jobForm.tipe_pekerjaan === 'ASN' ? 'Jabatan di Instansi' : (jobForm.tipe_pekerjaan === 'PELAJAR' ? 'Kelas / Jurusan' : 'Jabatan / Posisi Kerja') }}
              </label>
              <input type="text" v-model="jobForm.jabatan" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" :placeholder="jobForm.tipe_pekerjaan === 'ASN' ? 'Contoh: Analis Kebijakan' : (jobForm.tipe_pekerjaan === 'PELAJAR' ? 'Contoh: Mahasiswa Teknik Informatika' : 'Contoh: Software Engineer')" required />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">
                {{ jobForm.tipe_pekerjaan === 'ASN' ? 'Nomor Induk Pegawai (NIP)' : (jobForm.tipe_pekerjaan === 'SWASTA' ? 'Nomor Karyawan / ID Pegawai (Opsional)' : (jobForm.tipe_pekerjaan === 'WIRASWASTA' ? 'Nomor NPWP / Izin Usaha (Opsional)' : 'NIM / Nomor Induk Siswa (Opsional)')) }}
              </label>
              <input type="text" v-model="jobForm.nip" v-if="jobForm.tipe_pekerjaan === 'ASN'" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="Masukkan NIP Resmi" required />
              <input type="text" v-model="jobForm.nomor_karyawan" v-else class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" :placeholder="jobForm.tipe_pekerjaan === 'SWASTA' ? 'Contoh: K-99120' : (jobForm.tipe_pekerjaan === 'WIRASWASTA' ? 'Masukkan nomor izin jika ada' : 'Contoh: 1200001801')" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">
                {{ jobForm.tipe_pekerjaan === 'ASN' ? 'TMT Pengangkatan' : (jobForm.tipe_pekerjaan === 'PELAJAR' ? 'TMT Mulai Pendidikan' : 'TMT Mulai Bekerja / Usaha') }}
              </label>
              <input type="date" v-model="jobForm.asn_tmt" v-if="jobForm.tipe_pekerjaan === 'ASN'" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
              <input type="date" v-model="jobForm.tmt_mulai" v-else class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" required />
            </div>

            <!-- BIDANG TAMBAHAN KHUSUS ASN -->
            <div v-if="jobForm.tipe_pekerjaan === 'ASN'" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Jenis Kepegawaian ASN</label>
                <select v-model="jobForm.asn_jenis" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] bg-white" required>
                  <option value="">Pilih Jenis ASN</option>
                  <option value="CPNS">CPNS</option>
                  <option value="PNS">PNS</option>
                  <option value="P3K">P3K</option>
                  <option value="P3K Paruh Waktu">P3K Paruh Waktu</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Upload Berkas SK ASN (PDF, Max 2MB, Opsional)</label>
                <input type="file" @change="jobForm.asn_sk = $event.target.files[0]" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              </div>
            </div>

            <!-- LOKASI / ALAMAT (SAMA UNTUK ASN, SWASTA, WIRASWASTA, PELAJAR) -->
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Provinsi Tempat Kerja / Instansi</label>
                <select v-model="jobForm.provinsi" @change="handleProvinceChange" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] bg-white" required>
                  <option value="">Pilih Provinsi</option>
                  <option v-for="prov in provinces" :key="prov.code" :value="prov.name">{{ prov.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kabupaten / Kota</label>
                <select v-model="jobForm.kabupaten" @change="handleRegencyChange" :disabled="!jobForm.provinsi || loadingRegencies" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] bg-white disabled:opacity-50" required>
                  <option value="">{{ loadingRegencies ? 'Memuat...' : 'Pilih Kabupaten' }}</option>
                  <option v-for="reg in regencies" :key="reg.code" :value="reg.name">{{ reg.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kecamatan</label>
                <select v-model="jobForm.kecamatan" :disabled="!jobForm.kabupaten || loadingDistricts" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] bg-white disabled:opacity-50" required>
                  <option value="">{{ loadingDistricts ? 'Memuat...' : 'Pilih Kecamatan' }}</option>
                  <option v-for="dist in districts" :key="dist.code" :value="dist.name">{{ dist.name }}</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kode Pos</label>
              <input type="text" v-model="jobForm.postal_code" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" placeholder="Masukkan kode pos" required />
            </div>

            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">
                {{ jobForm.tipe_pekerjaan === 'ASN' ? 'Alamat Lengkap Instansi' : (jobForm.tipe_pekerjaan === 'SWASTA' ? 'Alamat Lengkap Perusahaan' : (jobForm.tipe_pekerjaan === 'WIRASWASTA' ? 'Alamat Lengkap Tempat Usaha' : 'Alamat Lengkap Instansi Pendidikan')) }}
              </label>
              <textarea v-model="jobForm.alamat_lengkap" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB]" rows="2" placeholder="Tuliskan nama jalan, gedung, nomor kantor/RT/RW, Kelurahan." required></textarea>
            </div>
          </div>

          <!-- Tombol Aksi Simpan / Batal -->
          <div class="flex justify-end pt-4 border-t border-[#E2E8F0] gap-3">
            <button type="button" @click="cancelForm" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition cursor-pointer">
              Batal
            </button>
            <button type="submit" :disabled="jobForm.processing" class="px-6 py-2.5 bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition cursor-pointer">
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>

      <!-- INITIAL FILL AREA (DENGAN DATA KOSONG) -->
      <div v-if="!currentJob && !showJobForm" class="bg-white border border-blue-200 rounded-3xl p-8 text-center space-y-6 shadow-xs">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto shadow-xs border border-blue-100 shrink-0">
          <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
        <div class="space-y-2 max-w-md mx-auto">
          <h3 class="text-base font-extrabold text-slate-800 uppercase tracking-wide">Data Pekerjaan Belum Dilengkapi</h3>
          <p class="text-xs text-slate-500 leading-relaxed">
            Sebagai personel Komponen Cadangan Non-ASN, Anda diwajibkan untuk melengkapi riwayat pekerjaan aktif Anda demi pemutakhiran data mobilisasi.
          </p>
        </div>
        <div class="pt-2">
          <button @click="startUpdateJobFlow('INITIAL_FILL')" :disabled="lockCountdown > 0" class="inline-flex items-center gap-2 px-6 py-3 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/10 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            {{ lockCountdown > 0 ? `Verifikasi Terkunci (Tunggu ${formatCountdown(lockCountdown)})` : 'Verifikasi OTP WhatsApp & Isi Data' }}
          </button>
        </div>
      </div>

      <!-- TIMELINE / RIWAYAT PEKERJAAN LAMPAU -->
      <div v-if="histories && histories.length > 0" class="bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-xs space-y-4">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Riwayat Transisi Karir & Pekerjaan</h4>
        <div class="relative pl-6 border-l-2 border-slate-100 space-y-6">
          <div v-for="item in histories" :key="item.id" class="relative">
            <!-- Icon Marker Timeline -->
            <span :class="[
              item.is_current ? 'bg-blue-600 ring-4 ring-blue-100' : 'bg-slate-300 ring-4 ring-slate-100',
              'absolute -left-[31px] top-0.5 w-4 h-4 rounded-full border border-white'
            ]"></span>

            <div class="space-y-1">
              <div class="flex items-center gap-2 flex-wrap">
                <h5 class="text-sm font-extrabold text-slate-700 leading-tight">{{ item.nama_perusahaan }}</h5>
                <span v-if="item.is_current" class="px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 text-[9px] font-bold rounded-md">Pekerjaan Aktif</span>
                <span v-if="item.is_phk" class="px-2 py-0.5 bg-red-50 text-red-700 border border-red-100 text-[9px] font-bold rounded-md">PHK</span>
              </div>
              <p class="text-[10px] text-slate-400 font-bold">
                TMT: {{ formatDate(item.tmt_mulai) }}
                <span v-if="item.is_phk"> s/d {{ formatDate(item.tmt_phk) }}</span>
              </p>
              <p class="text-xs text-slate-500">{{ item.kecamatan }}, {{ item.kabupaten }}, {{ item.provinsi }}</p>
              <div v-if="item.is_phk" class="bg-red-50/50 border border-red-100/50 rounded-xl p-3 mt-2 text-xs">
                <span class="font-bold text-red-700 block mb-0.5">Alasan PHK:</span>
                <p class="text-slate-600 italic">"{{ item.alasan_phk }}"</p>
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
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useSwal } from '@/Composables/useSwal';
import Swal from 'sweetalert2';

const props = defineProps({
  personel: Object,
  histories: Array
});

const { alertSuccess, alertError, confirmAction } = useSwal();

const showJobForm = ref(false);
const isPhkFlow = ref(false);

const provinces = ref([]);
const regencies = ref([]);
const districts = ref([]);
const loadingRegencies = ref(false);
const loadingDistricts = ref(false);

// Cari pekerjaan saat ini
const currentJob = computed(() => {
  return props.histories.find(h => h.is_current);
});

// Load data provinsi dari wilayah.id saat form dibuka
const loadProvinces = () => {
  if (provinces.value.length > 0) return;
  fetch('/api/wilayah/provinces')
    .then(res => res.json())
    .then(data => {
      provinces.value = data;
    })
    .catch(err => console.error('Gagal mengambil data provinsi:', err));
};

const handleProvinceChange = () => {
  jobForm.kabupaten = '';
  jobForm.kecamatan = '';
  regencies.value = [];
  districts.value = [];
  
  if (!jobForm.provinsi) return;
  
  const selectedProvince = provinces.value.find(p => p.name === jobForm.provinsi);
  if (!selectedProvince) return;
  
  loadingRegencies.value = true;
  fetch(`/api/wilayah/regencies/${selectedProvince.code}`)
    .then(res => res.json())
    .then(data => {
      regencies.value = data;
      loadingRegencies.value = false;
    })
    .catch(err => {
      console.error('Gagal mengambil data kabupaten:', err);
      loadingRegencies.value = false;
    });
};

const handleRegencyChange = () => {
  jobForm.kecamatan = '';
  districts.value = [];
  
  if (!jobForm.kabupaten) return;
  
  const selectedRegency = regencies.value.find(r => r.name === jobForm.kabupaten);
  if (!selectedRegency) return;
  
  loadingDistricts.value = true;
  fetch(`/api/wilayah/districts/${selectedRegency.code}`)
    .then(res => res.json())
    .then(data => {
      districts.value = data;
      loadingDistricts.value = false;
    })
    .catch(err => {
      console.error('Gagal mengambil data kecamatan:', err);
      loadingDistricts.value = false;
    });
};

// Form Inertia untuk input pekerjaan baru
const jobForm = useForm({
  tipe_pekerjaan: 'SWASTA',
  nama_perusahaan: '',
  jabatan: '',
  nomor_karyawan: '',
  nip: '',
  asn_jenis: '',
  asn_tmt: '',
  asn_sk: null,
  tmt_mulai: '',
  provinsi: '',
  kabupaten: '',
  kecamatan: '',
  alamat_lengkap: '',
  postal_code: '',
  tmt_phk: '',
  alasan_phk: '',
});

// Jalankan popup peringatan jika Non-ASN dan belum melengkapi data pekerjaan
onMounted(() => {
  if (!props.personel.is_asn && props.histories.length === 0) {
    Swal.fire({
      icon: 'info',
      title: 'Lengkapi Data Pekerjaan',
      text: 'Anda belum mengisi riwayat pekerjaan. Silakan lakukan verifikasi untuk melengkapi berkas data pekerjaan Anda.',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' }
    });
  }
});

// Konfigurasi hitung mundur blokir OTP
const lockCountdown = ref(0);
let lockTimer = null;

const formatCountdown = (secs) => {
  const m = Math.floor(secs / 60).toString().padStart(2, '0');
  const s = (secs % 60).toString().padStart(2, '0');
  return `${m}:${s}`;
};

const startLockCountdown = (seconds) => {
  lockCountdown.value = seconds;
  if (lockTimer) clearInterval(lockTimer);
  lockTimer = setInterval(() => {
    if (lockCountdown.value > 0) {
      lockCountdown.value--;
    } else {
      clearInterval(lockTimer);
    }
  }, 1000);
};

onBeforeUnmount(() => {
  if (lockTimer) clearInterval(lockTimer);
});

// Mulai Alur Verifikasi OTP & Amandemen Data Pekerjaan / PHK
const startUpdateJobFlow = async (actionType) => {
  if (lockCountdown.value > 0) {
    alertError('Akses Terkunci', `Batas percobaan salah terlampaui. Silakan tunggu ${formatCountdown(lockCountdown.value)} sebelum mencoba kembali.`);
    return;
  }

  let isConfirmed = true;

  if (actionType === 'UPDATE_JOB') {
    isConfirmed = await confirmAction(
      'Apakah anda yakin ingin update data pekerjaan?',
      'Sistem akan mengirimkan kode verifikasi OTP baru ke nomor WhatsApp Anda untuk membuka formulir pembaruan.'
    );
  } else if (actionType === 'PHK') {
    isConfirmed = await confirmAction(
      'Apakah anda yakin ingin melaporkan PHK?',
      'Kami memerlukan verifikasi OTP WhatsApp Anda sebelum mencatat pemberhentian hubungan kerja Anda.'
    );
  }

  if (!isConfirmed) return;

  // Request Kirim OTP ke Backend
  Swal.fire({
    title: 'Mengirim OTP...',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  try {
    const res = await fetch(route('personel.job.otp-request'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({ action_type: actionType })
    });

    const data = await res.json();
    Swal.close();

    if (!res.ok) {
      if (res.status === 429 && data.locked) {
        startLockCountdown(data.seconds_left);
      }
      alertError('Gagal Kirim OTP', data.message || 'Terjadi kesalahan sistem.');
      return;
    }

    // Tampilkan Prompt Input OTP
    const isVerified = await showOtpPrompt(
      actionType,
      data.message,
      data.disable_wa_otp ? 'Verifikasi Kode OTP Email' : 'Verifikasi OTP WhatsApp & Email'
    );
    if (!isVerified) return;

    // Jika verifikasi lolos, buka formulir
    alertSuccess('Verifikasi Berhasil', 'Formulir pengisian data kini telah dibuka.');
    showJobForm.value = true;
    loadProvinces();

    // Reset seluruh isian form agar bersih
    jobForm.nama_perusahaan = '';
    jobForm.jabatan = '';
    jobForm.nomor_karyawan = '';
    jobForm.nip = '';
    jobForm.asn_jenis = '';
    jobForm.asn_tmt = '';
    jobForm.asn_sk = null;
    jobForm.tmt_mulai = '';
    jobForm.provinsi = '';
    jobForm.kabupaten = '';
    jobForm.kecamatan = '';
    jobForm.alamat_lengkap = '';
    jobForm.postal_code = '';
    jobForm.tmt_phk = '';
    jobForm.alasan_phk = '';

    if (actionType === 'PHK') {
      jobForm.tipe_pekerjaan = 'TIDAK_BEKERJA';
    } else if (props.personel.is_asn) {
      jobForm.tipe_pekerjaan = 'ASN';
      jobForm.nip = props.personel.asn_nip || '';
      jobForm.asn_jenis = props.personel.asn_jenis || '';
      jobForm.asn_tmt = props.personel.asn_tmt || '';
      jobForm.tmt_mulai = props.personel.asn_tmt || '';
      jobForm.alamat_lengkap = props.personel.address || '';
      jobForm.provinsi = props.personel.province || '';
      jobForm.kabupaten = props.personel.city || '';
      jobForm.kecamatan = props.personel.district || '';
      jobForm.postal_code = props.personel.postal_code || '';
    } else {
      jobForm.tipe_pekerjaan = 'SWASTA';
    }

  } catch (err) {
    Swal.close();
    alertError('Kesalahan Jaringan', 'Gagal memproses permintaan OTP.');
  }
};

// Dialog Swal untuk Input OTP
const showOtpPrompt = async (actionType, customMessage, modalTitle) => {
  const actionText = actionType === 'INITIAL_FILL' ? 'pengisian data' : (actionType === 'PHK' ? 'pelaporan PHK' : 'pembaruan data');

  const title = modalTitle || 'Verifikasi Kode OTP';
  const text = customMessage || `Kode verifikasi OTP 6 digit telah dikirimkan untuk ${actionText}.`;

  const result = await Swal.fire({
    title: title,
    text: text,
    input: 'text',
    inputPlaceholder: 'Masukkan 6 Digit OTP',
    showCancelButton: true,
    confirmButtonColor: '#2563EB',
    cancelButtonColor: '#1E293B',
    confirmButtonText: 'Verifikasi',
    cancelButtonText: 'Batal',
    showLoaderOnConfirm: true,
    customClass: { popup: 'rounded-2xl', input: 'rounded-lg text-center font-bold tracking-widest text-lg' },
    inputValidator: (value) => {
      if (!value || value.length !== 6 || isNaN(value)) {
        return 'Harap masukkan 6 digit angka kode OTP!';
      }
    },
    preConfirm: async (otpCode) => {
      try {
        const res = await fetch(route('personel.job.otp-verify'), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({ otp_code: otpCode, action_type: actionType })
        });

        const data = await res.json();

        if (!res.ok) {
          if (res.status === 423 && data.locked) {
            startLockCountdown(data.seconds_left);
            Swal.showValidationMessage(data.message);
            setTimeout(() => Swal.close(), 3000);
            return false;
          }
          Swal.showValidationMessage(data.message || 'Kode OTP tidak cocok.');
          return false;
        }

        return true;
      } catch (err) {
        Swal.showValidationMessage('Kesalahan Jaringan. Gagal menghubungi server.');
        return false;
      }
    },
    allowOutsideClick: () => !Swal.isLoading()
  });

  return result.isConfirmed;
};

const cancelForm = () => {
  showJobForm.value = false;
  isPhkFlow.value = false;
};

// Submit Data Pekerjaan Baru/ASN/Wiraswasta/Pelajar/Tidak Bekerja ke backend
const submitJob = () => {
  jobForm.post(route('personel.job.store'), {
    onSuccess: () => {
      showJobForm.value = false;
      alertSuccess('Pembaruan Berhasil', 'Data kepegawaian dan pekerjaan Anda berhasil diperbarui.');
    },
    onError: (errors) => {
      const msg = Object.values(errors)[0] || 'Gagal menyimpan data pekerjaan.';
      alertError('Gagal Menyimpan', msg);
    }
  });
};

// Formatting date helper
const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};
</script>

<style scoped>
.animate-slide-up {
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

select, input, textarea {
  color: #1e293b !important;
}
</style>
