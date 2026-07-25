<template>
  <div 
    class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 font-sans bg-cover bg-center relative transition-all duration-300"
    :style="settings?.login_background ? { backgroundImage: `url(${settings.login_background})` } : { backgroundColor: '#F8FAFC' }"
  >
    <!-- Dark overlay when background image is present -->
    <div v-if="settings?.login_background" class="absolute inset-0 bg-slate-950/70 z-0"></div>

    <div 
      class="max-w-4xl mx-auto rounded-2xl shadow-xl transition-all duration-300 relative z-10 overflow-hidden"
      :class="settings?.login_background 
        ? 'bg-slate-900/60 backdrop-blur-md border border-white/10 text-white' 
        : 'bg-white border border-[#E2E8F0] shadow-slate-100/60 text-[#334155]'"
    >
      
      <div 
        class="p-8 border-b flex items-center justify-between transition-all duration-300"
        :class="settings?.login_background 
          ? 'border-white/10 bg-white/5' 
          : 'border-[#E2E8F0] bg-gradient-to-r from-[#2563EB]/5 to-transparent'"
      >
        <div class="flex items-center gap-4">
          <img v-if="settings?.logo_tni" :src="settings.logo_tni" class="h-12 object-contain drop-shadow" />
          <div v-else-if="settings?.logo_ad || settings?.logo_al || settings?.logo_au" class="flex items-center gap-1.5">
            <img v-if="settings?.logo_ad" :src="settings.logo_ad" class="h-8 object-contain" />
            <img v-if="settings?.logo_al" :src="settings.logo_al" class="h-8 object-contain" />
            <img v-if="settings?.logo_au" :src="settings.logo_au" class="h-8 object-contain" />
          </div>
          <div>
            <h2 class="text-xl font-bold" :class="settings?.login_background ? 'text-white' : 'text-slate-800'">
              Formulir Pendaftaran Komponen Cadangan
            </h2>
            <p class="text-xs mt-1" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
              Lengkapi data autentikasi dan data diri Anda secara valid.
            </p>
          </div>
        </div>
        <Link 
          :href="route('login')" 
          class="text-xs font-semibold hover:underline"
          :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'"
        >
          Kembali ke Login
        </Link>
      </div>

      <div class="p-8 space-y-6">
        <!-- LANGKAH 1: VERIFIKASI NIKC -->
        <div 
          class="p-6 rounded-2xl space-y-4 transition-all duration-300"
          :class="settings?.login_background ? 'bg-white/5 border border-white/10' : 'bg-blue-50/40 border border-blue-100/70'"
        >
          <div>
            <label 
              class="block text-xs font-bold uppercase tracking-wider mb-2"
              :class="settings?.login_background ? 'text-orange-400' : 'text-[#2563EB]'"
            >
              01. Verifikasi NIKC Komcad Anda
            </label>
            <p class="text-xs mb-3" :class="settings?.login_background ? 'text-slate-300' : 'text-slate-400'">
              Masukkan NIKC Anda untuk memverifikasi keanggotaan pra-pendaftaran.
            </p>
          </div>
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
              <input 
                type="text" 
                v-model="form.nikc"
                @input="form.nikc = form.nikc.replace(/\D/g, '').slice(0, 17)"
                :disabled="nikcVerified"
                :class="[
                  form.errors.nikc ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]',
                  form.nikc.length === 17 && !nikcVerified ? 'border-emerald-400 focus:border-emerald-500' : ''
                ]" 
                class="w-full px-4 py-2.5 border bg-white rounded-xl text-sm outline-none" 
                placeholder="Masukkan NIKC Anda (17 digit)"
                maxlength="17"
              />
              <span
                class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold"
                :class="form.nikc.length === 17 ? 'text-emerald-500' : form.nikc.length > 0 ? 'text-amber-500' : 'text-slate-300'"
              >{{ form.nikc.length }}/17</span>
            </div>
            <div class="sm:w-48">
              <input
                type="date"
                v-model="form.dob"
                :disabled="nikcVerified"
                class="w-full px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-sm outline-none focus:border-[#2563EB]"
                required
              />
            </div>
            <button 
              type="button" 
              v-if="!nikcVerified"
              @click="checkNikc" 
              class="px-6 py-2.5 bg-[#2563EB] hover:bg-[#1E40AF] text-white font-bold text-xs rounded-xl transition shadow-md shadow-blue-500/10 cursor-pointer"
            >
              Verifikasi NIKC
            </button>
            <button 
              type="button" 
              v-else 
              @click="resetNikcCheck" 
              class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer"
            >
              Ubah NIKC
            </button>
          </div>
          <div v-if="form.errors.nikc" class="text-red-500 text-[11px] font-semibold mt-1">⚠️ {{ form.errors.nikc }}</div>
          
          <div v-if="nikcVerified" class="text-xs font-bold flex items-center gap-2 p-3.5 rounded-xl border transition-all duration-300" :class="settings?.login_background ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-emerald-50 text-emerald-700 border-emerald-200'">
            <span>✓</span> NIKC Terverifikasi! Biodata utama Anda dimuat otomatis dari SKEP. Silakan lengkapi data registrasi di bawah.
          </div>
        </div>

        <!-- FORMULIR PENGAJUAN VERIFIKASI SKEP (JIKA NIKC TIDAK DITEMUKAN) -->
        <div v-if="showRequestForm" class="space-y-6 border border-amber-200/80 bg-amber-50/20 p-6 rounded-2xl">
          <div>
            <h3 class="text-sm font-bold text-amber-800 flex items-center gap-1.5">
              NIKC Belum Ada di Database
            </h3>
            <p class="text-xs text-slate-400 mt-1">
              Sistem tidak membuat NIKC. Jika NIKC sudah tercantum di SKEP tetapi belum ada di database, lengkapi formulir ini dan unggah SKEP sebagai dasar admin menambahkan atau memverifikasi NIKC tersebut.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Nama Lengkap (Sesuai KTP/SKEP)</label>
              <input type="text" v-model="requestForm.nama_lengkap" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Nomor WhatsApp Aktif</label>
              <input type="text" v-model="requestForm.phone_number" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" placeholder="Contoh: 08123456789" required />
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Nomor Induk Kependudukan (NIK)</label>
              <input type="text" v-model="requestForm.nik" maxlength="16" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">NIKC</label>
              <input type="text" v-model="requestForm.nikc" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm bg-slate-50 text-slate-500 outline-none" disabled />
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Tanggal Lahir</label>
              <input type="date" v-model="requestForm.dob" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Matra</label>
              <select v-model="requestForm.matra" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm bg-white outline-none focus:border-[#2563EB]" required>
                <option value="AD">Darat (AD)</option>
                <option value="AL">Laut (AL)</option>
                <option value="AU">Udara (AU)</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Pangkat</label>
              <select v-model="requestForm.pangkat" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm bg-white outline-none focus:border-[#2563EB]" required>
                <option v-for="pangkat in pangkatOptions" :key="pangkat.nama" :value="pangkat.nama">{{ pangkat.nama }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Tahun Angkatan Kelulusan</label>
              <input type="text" v-model="requestForm.angkatan" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" required />
            </div>
            <div class="md:col-span-2">
              <label class="block font-semibold text-slate-600 mb-2 uppercase">Upload Dokumen Berkas SKEP (Format PDF, Max 2MB)</label>
              <input type="file" @change="handleRequestFileChange" accept=".pdf" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold" :class="settings?.login_background ? 'text-white file:bg-amber-500/10 file:text-amber-300' : 'text-slate-500 file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100'" required />
            </div>
          </div>

          <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
            <button 
              type="button" 
              @click="submitSkepRequest" 
              class="py-2.5 px-6 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg shadow-lg shadow-amber-500/10 transition cursor-pointer"
            >
              Ajukan Verifikasi SKEP
            </button>
          </div>
        </div>

        <!-- FORMULIR UTAMA PENDAFTARAN (MUNCUL JIKA NIKC VALID) -->
        <form v-if="nikcVerified" @submit.prevent="submit" class="space-y-8 animate-slide-up">
          
          <div>
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">02. Kredensial & Matra</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Nama Lengkap</label>
                <input type="text" v-model="form.full_name" class="w-full px-4 py-2 border border-[#E2E8F0] bg-slate-50 text-slate-500 rounded-lg text-sm outline-none cursor-not-allowed" disabled />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Email Aktif</label>
                <input type="email" v-model="form.email" :class="form.errors.email ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-lg text-sm outline-none" required />
                <div v-if="form.errors.email" class="text-red-500 text-[11px] mt-1 font-semibold">⚠️ {{ form.errors.email }}</div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Nomor WhatsApp</label>
                <input type="text" v-model="form.phone_number" :class="form.errors.phone_number ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-lg text-sm outline-none" placeholder="08xxxxx" required />
                <div v-if="form.errors.phone_number" class="text-red-500 text-[11px] mt-1 font-semibold">⚠️ {{ form.errors.phone_number }}</div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Nomor Induk Kependudukan (NIK)</label>
                <input type="text" v-model="form.nik" maxlength="16" :class="form.errors.nik ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-lg text-sm outline-none" required />
                <div v-if="form.errors.nik" class="text-red-500 text-[11px] mt-1 font-semibold">⚠️ {{ form.errors.nik }}</div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Matra</label>
                <select v-model="form.matra" class="w-full px-4 py-2 border border-[#E2E8F0] bg-slate-50 text-slate-500 rounded-lg text-sm outline-none cursor-not-allowed" disabled>
                  <option value="AD">Darat (AD)</option>
                  <option value="AL">Laut (AL)</option>
                  <option value="AU">Udara (AU)</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Tahun Angkatan Kelulusan</label>
                <input type="text" v-model="form.angkatan" class="w-full px-4 py-2 border border-[#E2E8F0] bg-slate-50 text-slate-500 rounded-lg text-sm outline-none cursor-not-allowed" disabled />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Pangkat</label>
                <select v-model="form.pangkat" class="w-full px-4 py-2 border border-[#E2E8F0] bg-slate-50 text-slate-500 rounded-lg text-sm outline-none cursor-not-allowed" disabled>
                  <option v-for="pangkat in pangkatOptions" :key="pangkat.nama" :value="pangkat.nama">{{ pangkat.nama }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Sumber Rekrutmen</label>
                <select v-model="form.sumber_rekrutmen" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm bg-white outline-none focus:border-[#2563EB]" required>
                  <option value="Reguler">Reguler</option>
                  <option value="SPPI">SPPI</option>
                  <option value="PNS">PNS</option>
                </select>
              </div>
              
              <!-- Checkbox Apakah ASN -->
              <div class="md:col-span-3 flex items-center gap-2.5 py-2">
                <input type="checkbox" v-model="form.is_asn" id="is_asn" class="w-4 h-4 text-[#2563EB] border-[#E2E8F0] rounded focus:ring-[#2563EB]" />
                <label for="is_asn" class="text-xs font-extrabold text-slate-700 uppercase cursor-pointer select-none">Saya Seorang Aparatur Sipil Negara (ASN)</label>
              </div>

              <!-- Form Input Khusus ASN (v-if) -->
              <template v-if="form.is_asn">
                <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50 p-4 border border-[#E2E8F0] rounded-2xl animate-slide-up">
                  <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Nama Instansi / Satuan Kerja Pemerintah</label>
                    <input type="text" v-model="form.asn_nama_instansi" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB] bg-white" placeholder="Contoh: Kementerian Pertahanan RI" required />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Nomor Induk Pegawai (NIP)</label>
                    <input type="text" v-model="form.asn_nip" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB] bg-white" placeholder="Masukkan NIP Anda" required />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Jenis ASN</label>
                    <select v-model="form.asn_jenis" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm bg-white outline-none focus:border-[#2563EB]" required>
                      <option value="PNS">PNS</option>
                      <option value="CPNS">CPNS</option>
                      <option value="P3K">P3K (PPPK)</option>
                      <option value="P3K Paruh Waktu">P3K Paruh Waktu</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">TMT Pengangkatan</label>
                    <input type="date" v-model="form.asn_tmt" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB] bg-white" required />
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Upload Berkas SK ASN (Opsional, Max 2MB)</label>
                    <input type="file" @input="form.asn_sk = $event.target.files[0]" class="w-full text-xs file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold" :class="settings?.login_background ? 'text-white file:bg-blue-500/10 file:text-blue-300' : 'text-slate-500 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100'" />
                  </div>
                </div>
              </template>
            </div>
          </div>

          <div class="border-t border-[#E2E8F0] pt-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">03. Kelahiran & Alamat Domisili</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Tempat Lahir</label>
                <input type="text" v-model="form.pob" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" required />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Tanggal Lahir</label>
                <input type="date" v-model="form.dob" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" required />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Jenis Kelamin</label>
                <select v-model="form.gender" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm bg-white outline-none focus:border-[#2563EB]" required>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>
              <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Alamat Lengkap Rumah</label>
                <textarea v-model="form.address" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" rows="2" required></textarea>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Provinsi</label>
                <select v-model="form.province" @change="handleProvinceChange" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB] bg-white" required>
                  <option value="">Pilih Provinsi</option>
                  <option v-for="prov in provinces" :key="prov.code" :value="prov.name">{{ prov.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kabupaten / Kota</label>
                <select v-model="form.city" @change="handleRegencyChange" :disabled="!form.province || loadingRegencies" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB] bg-white disabled:opacity-50">
                  <option value="">{{ loadingRegencies ? 'Memuat...' : 'Pilih Kabupaten / Kota' }}</option>
                  <option v-for="reg in regencies" :key="reg.code" :value="reg.name">{{ reg.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kecamatan</label>
                <select v-model="form.district" @change="handleDistrictChange" :disabled="!form.city || loadingDistricts" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB] bg-white disabled:opacity-50">
                  <option value="">{{ loadingDistricts ? 'Memuat...' : 'Pilih Kecamatan' }}</option>
                  <option v-for="dist in districts" :key="dist.code" :value="dist.name">{{ dist.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kelurahan</label>
                <select v-if="villages.length > 0" v-model="form.village" :disabled="loadingVillages" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB] bg-white disabled:opacity-50">
                  <option value="">{{ loadingVillages ? 'Memuat...' : 'Pilih Kelurahan / Desa' }}</option>
                  <option v-for="village in villages" :key="village.code" :value="village.name">{{ village.name }}</option>
                </select>
                <input v-else type="text" v-model="form.village" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" placeholder="Opsional bila data wilayah tidak tersedia" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kode Pos</label>
                <input type="text" v-model="form.postal_code" class="w-full px-4 py-2 border border-[#E2E8F0] rounded-lg text-sm outline-none focus:border-[#2563EB]" placeholder="Opsional" />
              </div>
            </div>
          </div>

          <div class="border-t border-[#E2E8F0] pt-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">04. Berkas Pendukung & Password</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Upload Foto Profil (Format Gambar, Max 2MB)</label>
                <input type="file" @input="form.photo_profile = $event.target.files[0]" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold" :class="[form.errors.photo_profile ? 'text-red-500' : (settings?.login_background ? 'text-white file:bg-blue-500/10 file:text-blue-300' : 'text-slate-500 file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100')]" required />
                <div v-if="form.errors.photo_profile" class="text-red-500 text-[11px] mt-1 font-semibold">⚠️ {{ form.errors.photo_profile }}</div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Upload Berkas KTP (Format Gambar, Max 2MB)</label>
                <input type="file" @input="form.ktp_document = $event.target.files[0]" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold" :class="[form.errors.ktp_document ? 'text-red-500' : (settings?.login_background ? 'text-white file:bg-blue-500/10 file:text-blue-300' : 'text-slate-500 file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100')]" required />
                <div v-if="form.errors.ktp_document" class="text-red-500 text-[11px] mt-1 font-semibold">⚠️ {{ form.errors.ktp_document }}</div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Kata Sandi Akun</label>
                <input type="password" v-model="form.password" :class="form.errors.password ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-lg text-sm outline-none" required />
                <div v-if="form.errors.password" class="text-red-500 text-[11px] mt-1 font-semibold">⚠️ {{ form.errors.password }}</div>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase">Konfirmasi Kata Sandi</label>
                <input type="password" v-model="form.password_confirmation" :class="form.errors.password_confirmation ? 'border-red-400 focus:border-red-500' : 'border-[#E2E8F0] focus:border-[#2563EB]'" class="w-full px-4 py-2 border rounded-lg text-sm outline-none" required />
                <div v-if="form.errors.password_confirmation" class="text-red-500 text-[11px] mt-1 font-semibold">⚠️ {{ form.errors.password_confirmation }}</div>
              </div>
            </div>
          </div>

          <div class="pt-4 flex items-start gap-3">
            <input type="checkbox" v-model="form.agreement" id="agree" class="w-4 h-4 text-[#2563EB] border-[#E2E8F0] rounded focus:ring-[#2563EB] mt-0.5" required />
            <label for="agree" class="text-xs text-slate-500 leading-relaxed select-none cursor-pointer">
              Saya menyatakan dengan ini bahwa seluruh data yang saya masukkan adalah benar, valid, dan saya bersedia mematuhi segala ketentuan hukum komponen cadangan nasional.
            </label>
          </div>

          <div class="flex justify-end pt-4 border-t border-[#E2E8F0]">
            <button type="submit" :disabled="form.processing" class="py-2.5 px-6 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-sm font-semibold rounded-lg shadow-lg shadow-blue-500/10 transition cursor-pointer">
              Kirim Pendaftaran
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useSwal } from '@/Composables/useSwal';

const page = usePage();
const settings = computed(() => page.props.settings || {});
const props = defineProps({
  pangkatOptions: {
    type: Array,
    default: () => []
  }
});
const pangkatOptions = computed(() => props.pangkatOptions || []);

const { showLoadingProgress, closeLoading, alertSuccess, alertError, alertErrorHtml } = useSwal();

// State Verifikasi NIKC
const nikcVerified = ref(false);
const showRequestForm = ref(false);

// State data wilayah.id
const provinces = ref([]);
const regencies = ref([]);
const districts = ref([]);
const villages = ref([]);
const loadingRegencies = ref(false);
const loadingDistricts = ref(false);
const loadingVillages = ref(false);

onMounted(() => {
  fetch('/api/wilayah/provinces')
    .then(res => res.json())
    .then(data => {
      provinces.value = data;
    })
    .catch(err => console.error('Gagal mengambil data provinsi:', err));
});

const handleProvinceChange = () => {
  form.city = '';
  form.district = '';
  form.village = '';
  regencies.value = [];
  districts.value = [];
  villages.value = [];
  
  if (!form.province) return;
  
  const selectedProvince = provinces.value.find(p => p.name === form.province);
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
  form.district = '';
  form.village = '';
  districts.value = [];
  villages.value = [];
  
  if (!form.city) return;
  
  const selectedRegency = regencies.value.find(r => r.name === form.city);
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

const handleDistrictChange = () => {
  form.village = '';
  villages.value = [];

  if (!form.district) return;

  const selectedDistrict = districts.value.find(d => d.name === form.district);
  if (!selectedDistrict) return;

  loadingVillages.value = true;
  fetch(`/api/wilayah/villages/${selectedDistrict.code}`)
    .then(res => res.ok ? res.json() : [])
    .then(data => {
      villages.value = data;
      loadingVillages.value = false;
    })
    .catch(err => {
      console.error('Gagal mengambil data kelurahan/desa:', err);
      loadingVillages.value = false;
    });
};

const form = useForm({
  full_name: '',
  email: '',
  phone_number: '',
  nik: '',
  matra: 'AD',
  angkatan: '',
  pangkat: 'Prada KC',
  nikc: '',         
  sumber_rekrutmen: 'Reguler',
  pob: '',
  dob: '',
  gender: 'L',
  address: '',
  province: '',
  city: '',
  district: '',
  village: '',
  postal_code: '',
  photo_profile: null,
  ktp_document: null,
  password: '',
  password_confirmation: '',
  agreement: false,
  is_asn: false,
  asn_nip: '',
  asn_jenis: 'PNS',
  asn_tmt: '',
  asn_sk: null,
  asn_nama_instansi: '',
});

// Form khusus pengajuan verifikasi berkas SKEP baru
const requestForm = useForm({
  nama_lengkap: '',
  pangkat: 'Prada KC',
  phone_number: '',
  nik: '',
  nikc: '',
  dob: '',
  matra: 'AD',
  angkatan: new Date().getFullYear().toString(),
  skep_file: null
});

// Cek NIKC Ke Database
const checkNikc = () => {
  if (!form.nikc) {
    alertError('Form Kosong', 'Silakan masukkan nomor NIKC Anda terlebih dahulu.');
    return;
  }
  if (!form.dob) {
    alertError('Tanggal Lahir Wajib Diisi', 'Silakan masukkan tanggal lahir sesuai SKEP untuk memverifikasi NIKC.');
    return;
  }

  const cleanNikc = form.nikc.replace(/\D/g, '');
  if (cleanNikc.length !== 17) {
    alertErrorHtml(
      'Format NIKC Tidak Valid',
      `NIKC wajib terdiri dari tepat <strong>17 digit angka</strong>.<br>Saat ini Anda memasukkan <strong>${cleanNikc.length} digit</strong>.<br><br>Silakan periksa kembali nomor NIKC Anda pada kartu SKEP.`,
    );
    return;
  }
  form.nikc = cleanNikc;

  showLoadingProgress('Mengecek NIKC di database...');
  
  fetch(route('skep.check', { nikc: form.nikc, dob: form.dob }))
    .then(async (res) => {
      const payload = await res.json();
      if (!res.ok) {
        throw new Error(payload.message || 'Gagal memproses pengecekan NIKC.');
      }
      return payload;
    })
    .then(data => {
      closeLoading();
      if (data.exists) {
        if (data.registered) {
          alertError('NIKC Sudah Terdaftar', 'Nomor NIKC ini sudah terdaftar di sistem. Silakan login menggunakan NIKC Anda atau hubungi administrator.');
          return;
        }
        nikcVerified.value = true;
        showRequestForm.value = false;
        
        // Isi data otomatis dari SKEP
        form.full_name = data.data.nama_lengkap;
        form.pangkat = data.data.pangkat;
        form.matra = data.data.matra;
        form.angkatan = data.data.angkatan;
      } else {
        nikcVerified.value = false;
        showRequestForm.value = true;
        
        // Samakan NIKC yang salah ke form pengajuan verifikasi
        requestForm.nikc = form.nikc;
        requestForm.dob = form.dob;
        requestForm.nama_lengkap = '';
        requestForm.pangkat = 'Prada KC';
        requestForm.phone_number = '';
        requestForm.nik = '';
        requestForm.matra = 'AD';
        requestForm.angkatan = new Date().getFullYear().toString();
        
        alertError('NIKC Belum Ada di Database', 'Sistem tidak membuat NIKC. Jika NIKC sudah tercantum di SKEP tetapi belum ada di database, unggah SKEP sebagai dasar admin menambahkan atau memverifikasi NIKC tersebut.');
      }
    })
    .catch((error) => {
      closeLoading();
      alertError('Kesalahan Pengecekan', error.message || 'Gagal memproses pengecekan NIKC.');
    });
};

const resetNikcCheck = () => {
  nikcVerified.value = false;
  showRequestForm.value = false;
  form.nikc = '';
  form.full_name = '';
  form.matra = 'AD';
  form.angkatan = '';
  form.pangkat = 'Prada KC';
};

// Request file change handler
const handleRequestFileChange = (e) => {
  requestForm.skep_file = e.target.files[0];
};

// Kirim Pengajuan Verifikasi Berkas
const submitSkepRequest = () => {
  if (!requestForm.nama_lengkap || !requestForm.dob || !requestForm.pangkat || !requestForm.phone_number || !requestForm.nik || !requestForm.skep_file) {
    alertError('Form Belum Lengkap', 'Silakan isi seluruh kolom pengajuan verifikasi berkas.');
    return;
  }

  requestForm.post(route('skep.request'), {
    forceFormData: true,
    preserveScroll: true,
    onBefore: () => showLoadingProgress('Mengirim pengajuan verifikasi SKEP...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Pengajuan Dikirim', 'Berkas pengajuan SKEP Anda telah berhasil terkirim. Admin akan meninjau dan mengirim notifikasi WhatsApp kepada Anda.');
      resetNikcCheck();
    },
    onError: (errors) => {
      closeLoading();
      const firstError = Object.values(errors)[0] || 'Gagal mengirim berkas pengajuan.';
      alertError('Gagal Mengirim', firstError);
    }
  });
};

// Kirim pendaftaran utama
const submit = () => {
  form.post(route('register'), {
    forceFormData: true,
    preserveScroll: true,
    onBefore: () => showLoadingProgress('Memproses berkas pendaftaran Komponen Cadangan...'),
    onSuccess: () => {
      closeLoading();
      alertSuccess('Pendaftaran Berhasil', 'Data pendaftaran Anda telah direkam. Silakan tunggu verifikasi admin untuk mengaktifkan akun Anda.');
    },
    onError: (errors) => {
      closeLoading();
      const firstErrorKey = Object.keys(errors)[0];
      const errorMessage = errors[firstErrorKey] || 'Mohon periksa kembali isian berkas formulir Anda.';
      alertError('Gagal Mendaftar', errorMessage);
    }
  });
};
</script>

<style scoped>
.animate-slide-up {
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Force dark text for readability on all white inputs */
input:not([type="checkbox"]):not([type="radio"]):not([type="file"]), select, textarea {
  color: #1e293b !important;
}

/* Improve contrast of labels and descriptions when glassmorphism (dark mode) is active */
.bg-slate-900\/60 label {
  color: #cbd5e1 !important; /* slate-300 */
}
.bg-slate-900\/60 p:not(.text-green-600) {
  color: #94a3b8 !important; /* slate-400 */
}
</style>
