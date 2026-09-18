<template>
  <AuthenticatedLayout>
    <template #sidebar-menu>
      <Link :href="route('personel.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard Saya</Link>
      <Link :href="route('personel.education.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Riwayat Pendidikan</Link>
      <Link :href="route('personel.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Tugas & Broadcast Kegiatan</Link>
    </template>

    <template #header-title>
      <div class="flex items-center gap-2">
        <span>Riwayat Pendidikan & Diklat</span>
        <span v-if="personel?.full_name" class="text-xs font-normal text-slate-400">— {{ personel.full_name }} ({{ personel.nikc || '-' }})</span>
      </div>
    </template>

    <div class="max-w-6xl space-y-6">
      <section class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Tambah Riwayat Pendidikan</h2>
        <p class="text-xs text-slate-500 mt-1">Pendidikan akademik menjadi sumber gelar tampilan; diklat dan militer tetap tercatat sebagai riwayat pendukung.</p>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5 text-xs">
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Jenis Pendidikan</label>
            <select v-model="form.jenis" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]">
              <option v-for="option in options.jenis" :key="option" :value="option">{{ option }}</option>
            </select>
          </div>
          
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Jenjang</label>
            <select v-model="form.jenjang" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]">
              <option v-for="option in jenjangOptions" :key="option" :value="option">{{ option }}</option>
            </select>
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Tahun Lulus</label>
            <input v-model="form.tahun_lulus" type="number" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: 2020" />
          </div>

          <!-- 1. Kolom Nama Perguruan Tinggi / Kampus (Jika D3 - Profesi) -->
          <div v-if="isPerguruanTinggi" ref="campusContainerRef" class="flex flex-col gap-1.5 relative">
            <div class="flex items-center justify-between">
              <label class="font-bold text-slate-500 uppercase text-[10px]">
                {{ isManualKampus ? 'Nama Kampus (Input Manual)' : 'Nama Perguruan Tinggi / Kampus' }}
              </label>
              <button
                v-if="!isManualKampus"
                type="button"
                @click="enableManualKampus"
                class="text-[9px] text-blue-600 hover:underline font-bold cursor-pointer"
              >
                Input Manual
              </button>
              <button
                v-else
                type="button"
                @click="disableManualKampus"
                class="text-[9px] text-blue-600 hover:underline font-bold cursor-pointer"
              >
                &larr; Cari dari Daftar
              </button>
            </div>

            <!-- Mode A: Pencarian Otomatis Dropdown Kampus -->
            <div v-if="!isManualKampus" class="relative">
              <input
                v-model="form.nama_institusi"
                @focus="onCampusFocus"
                @input="onCampusInput"
                type="text"
                class="w-full rounded-xl border-[#E2E8F0] text-xs pl-8 pr-8 py-2 outline-none focus:border-[#2563EB]"
                placeholder="Cari nama kampus (cth: Brawijaya, UGM, UI)..."
                autocomplete="off"
              />
              <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <svg v-if="isLoadingCampus" class="w-3.5 h-3.5 text-blue-600 absolute right-2.5 top-2.5 animate-spin pointer-events-none" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>

              <!-- Dropdown Floating Panel -->
              <div
                v-if="showCampusDropdown"
                class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-slate-200 rounded-xl shadow-xl max-h-56 overflow-y-auto divide-y divide-slate-100"
              >
                <div
                  v-for="item in campusResults"
                  :key="item.kode || item.nama"
                  @click="selectCampus(item)"
                  class="p-2.5 hover:bg-blue-50/80 cursor-pointer transition text-left flex items-start justify-between gap-2"
                >
                  <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-800 text-xs truncate">{{ item.nama }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ item.bentuk || 'Perguruan Tinggi' }} &bull; {{ item.provinsi || 'Indonesia' }}</p>
                  </div>
                  <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 shrink-0 font-medium">{{ item.bentuk || 'PT' }}</span>
                </div>

                <div v-if="!isLoadingCampus && campusResults.length === 0" class="p-3 text-center text-slate-400 text-[11px]">
                  Kampus tidak ditemukan dalam daftar.
                </div>

                <!-- Opsi Tambah Kampus Manual di paling bawah -->
                <div
                  @click="enableManualKampus"
                  class="p-2.5 bg-slate-50 hover:bg-blue-50 text-blue-600 cursor-pointer transition text-center font-bold text-[11px] flex items-center justify-center gap-1.5 border-t border-slate-100"
                >
                  <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                  <span>Kampus Tidak Terdaftar? Tambah Manual</span>
                </div>
              </div>
            </div>

            <!-- Mode B: Input Teks Manual Bebas -->
            <div v-else class="relative">
              <input
                v-model="form.nama_institusi"
                type="text"
                class="w-full rounded-xl border-blue-200 bg-blue-50/20 text-xs px-4 py-2 outline-none focus:border-[#2563EB]"
                placeholder="Ketikkan nama kampus secara manual..."
              />
            </div>
          </div>

          <!-- 2. Kolom Program Studi (Jika D3 - Profesi) -->
          <div v-if="isPerguruanTinggi" ref="prodiContainerRef" class="flex flex-col gap-1.5 relative">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Program Studi / Jurusan</label>
            <div class="relative">
              <input
                v-model="form.program_studi"
                @focus="onProdiFocus"
                @input="onProdiInput"
                type="text"
                class="w-full rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]"
                placeholder="Pilih atau ketik program studi..."
                autocomplete="off"
              />
              <svg v-if="isLoadingProdi" class="w-3.5 h-3.5 text-blue-600 absolute right-3 top-2.5 animate-spin pointer-events-none" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
            </div>

            <!-- Dropdown Rekomendasi Prodi -->
            <div
              v-if="showProdiDropdown && prodiResults.length > 0"
              class="absolute left-0 right-0 top-full mt-1 z-30 bg-white border border-slate-200 rounded-xl shadow-xl max-h-52 overflow-y-auto divide-y divide-slate-100"
            >
              <div
                v-for="prodi in prodiResults"
                :key="prodi"
                @click="selectProdi(prodi)"
                class="px-3 py-2 hover:bg-blue-50/80 cursor-pointer transition text-left text-xs font-medium text-slate-700 hover:text-blue-700"
              >
                {{ prodi }}
              </div>
            </div>
          </div>

          <!-- Kolom Biasa (Untuk Jenjang Dasar SD/SMP/SMA/LAIN atau DIKLAT/MILITER) -->
          <div v-if="!isPerguruanTinggi" class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Program Studi / Bidang Keahlian</label>
            <input v-model="form.program_studi" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: IPA / IPS / Bidang Keahlian" />
          </div>

          <div v-if="!isPerguruanTinggi" class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Nama Institusi / Penyelenggara</label>
            <input v-model="form.nama_institusi" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: SMAN 1 Jakarta" />
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Nomor Ijazah / Sertifikat</label>
            <input v-model="form.nomor_ijazah" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Masukkan nomor ijazah resmi" />
          </div>

          <div v-if="form.jenis === 'AKADEMIK'" class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Gelar Depan (Akademik)</label>
            <input v-model="form.front_title" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: dr." />
          </div>

          <div v-if="form.jenis === 'AKADEMIK'" class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Gelar Belakang (Akademik)</label>
            <input v-model="form.suffix_gelar" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: S.T., M.Kom." />
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Upload Berkas Ijazah / Sertifikat (Max 4MB)</label>
            <input type="file" @change="form.file_ijazah = $event.target.files[0]" class="text-xs text-slate-500 mt-1 file:py-1 file:px-3 file:border file:border-slate-200 file:rounded-lg file:text-xs file:bg-slate-50 file:cursor-pointer" />
          </div>

          <button type="submit" :disabled="form.processing" class="md:col-span-3 bg-[#2563EB] hover:bg-[#1E40AF] text-white rounded-xl px-4 py-2.5 font-bold text-xs transition disabled:opacity-50 mt-2 cursor-pointer">
            Simpan Riwayat Pendidikan
          </button>
        </form>
      </section>

      <section class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] bg-slate-50/50 flex items-center justify-between">
          <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Daftar Riwayat Pendidikan & Diklat Anda</h3>
          <span class="text-[10px] text-slate-400 font-semibold">Total: {{ items.length }} Riwayat</span>
        </div>
        <div class="divide-y divide-[#E2E8F0]">
          <article v-for="item in items" :key="item.id" class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:bg-slate-50/50 transition">
            <div class="flex items-start gap-3.5">
              <!-- Indikator Lingkaran Status Hijau/Kuning -->
              <div :class="item.verified_at ? 'bg-emerald-500 ring-4 ring-emerald-100' : 'bg-amber-500 ring-4 ring-amber-100'" class="w-3 h-3 rounded-full shrink-0 mt-1.5"></div>
              
              <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                  <!-- Judul Program Studi / Nama Pendidikan -->
                  <h4 
                    @click="openModal(item)" 
                    class="font-bold text-slate-800 text-sm cursor-pointer hover:text-[#2563EB] transition inline-flex items-center gap-1.5 group"
                    title="Klik untuk lihat detail / verifikasi"
                  >
                    <span>{{ item.program_studi || item.nama_institusi || item.jenjang }}</span>
                  </h4>

                  <!-- Badge Jenis, Jenjang, & Verification Status -->
                  <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-blue-50 text-[#2563EB] border border-blue-100 uppercase">{{ item.jenis }}</span>
                  <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">{{ item.jenjang }}</span>
                  <span v-if="item.verified_at" class="text-[9px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Terverifikasi
                  </span>
                  <span v-else class="text-[9px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">
                    Menunggu Verifikasi
                  </span>
                </div>

                <!-- Subtitle Nama Institusi -->
                <p class="text-xs font-semibold text-slate-600">{{ item.nama_institusi || '-' }}</p>

                <!-- Detail Tahun Lulus & Nomor Ijazah -->
                <p class="text-[11px] text-slate-400">
                  Tahun Lulus: {{ item.tahun_lulus || '-' }}
                  <span v-if="item.nomor_ijazah"> | No. Ijazah: {{ item.nomor_ijazah }}</span>
                </p>
                <p v-if="item.front_title || item.suffix_gelar" class="text-xs text-slate-500">
                  Gelar Tersimpan: <span class="font-semibold text-slate-700">{{ [item.front_title, item.suffix_gelar].filter(Boolean).join(' / ') }}</span>
                </p>
              </div>
            </div>
            
            <div class="flex items-center gap-3 text-xs">
              <a v-if="item.file_ijazah_path" :href="documentUrl(item.file_ijazah_path)" target="_blank" class="font-bold text-[#2563EB] hover:underline flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Lihat Berkas</span>
              </a>
              <button 
                v-if="routes?.verify && !item.verified_at" 
                @click="openModal(item)" 
                class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer flex items-center gap-1"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Verifikasi / Tolak</span>
              </button>
              <button @click="remove(item.id)" class="font-bold text-red-500 hover:underline cursor-pointer flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Hapus</span>
              </button>
            </div>
          </article>
          <div v-if="items.length === 0" class="p-12 text-center text-sm text-slate-400 italic">Belum ada riwayat pendidikan yang ditambahkan.</div>
        </div>
      </section>
    </div>

    <!-- MODAL DETAIL & VERIFIKASI / PENOLAKAN PENDIDIKAN (UNTUK ADMIN) -->
    <div v-if="showModal && selectedItem" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 animate-fade-in">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden text-xs">
        
        <!-- Header Modal -->
        <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
          <div>
            <span 
              class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
              :class="selectedItem.verified_at ? 'bg-green-50 text-green-700 border-green-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
            >
              {{ selectedItem.verified_at ? 'STATUS: TERVERIFIKASI' : 'STATUS: MENUNGGU VERIFIKASI' }}
            </span>
            <h3 class="text-sm font-bold text-slate-800 mt-1">Detail Riwayat Pendidikan</h3>
          </div>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Tampilan Detail Informasi (Normal) -->
        <div v-if="!isRejecting" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-[#E2E8F0]">
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">Jenis Pendidikan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.jenis }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">Jenjang</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.jenjang }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Program Studi / Bidang</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.program_studi || '-' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Nama Institusi / Penyelenggara</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.nama_institusi || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">Tahun Lulus</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.tahun_lulus || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">Nomor Ijazah / Sertifikat</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.nomor_ijazah || '-' }}</p>
            </div>
            <div v-if="selectedItem.front_title || selectedItem.suffix_gelar" class="col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Gelar Akademik</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ [selectedItem.front_title, selectedItem.suffix_gelar].filter(Boolean).join(' / ') }}</p>
            </div>
          </div>

          <!-- Pratinjau / Download Berkas -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Berkas Ijazah / Sertifikat</p>
              <a v-if="selectedItem.file_ijazah_path" :href="documentUrl(selectedItem.file_ijazah_path)" target="_blank" class="text-blue-600 hover:underline font-bold text-[10px] flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Buka Tab Baru</span>
              </a>
              <span v-else class="text-slate-400 font-bold text-[10px]">Tidak ada berkas</span>
            </div>
            <div v-if="selectedItem.file_ijazah_path" class="border border-[#E2E8F0] rounded-xl overflow-hidden bg-slate-50">
              <iframe :src="documentUrl(selectedItem.file_ijazah_path)" class="w-full h-56 border-0"></iframe>
            </div>
          </div>
        </div>

        <!-- Tampilan Input Alasan Penolakan -->
        <div v-else class="p-6 space-y-4">
          <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <p class="font-bold text-xs">Form Penolakan Pengajuan Pendidikan</p>
            <p class="text-[11px] mt-0.5 text-red-600">Alasan penolakan akan dikirimkan ke personel agar dapat memperbaiki berkas atau data yang kurang sesuai.</p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase">Alasan Penolakan</label>
            <textarea
              v-model="rejectReason"
              rows="4"
              class="w-full px-4 py-3 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-red-500"
              placeholder="Tulis alasan penolakan... (Contoh: Scan berkas ijazah buram / tidak terbaca, mohon unggah ulang berkas asli yang jelas.)"
              required
            ></textarea>
          </div>
        </div>

        <!-- Footer Modal Aksi Normal -->
        <div v-if="!isRejecting" class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
          <div>
            <button
              v-if="routes?.verify && !selectedItem.verified_at"
              @click="isRejecting = true"
              class="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl font-bold transition cursor-pointer flex items-center gap-1.5"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Tolak Pengajuan</span>
            </button>
          </div>

          <div class="flex items-center gap-3">
            <button @click="closeModal" class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition cursor-pointer">
              Tutup
            </button>

            <button
              v-if="routes?.verify && !selectedItem.verified_at"
              @click="processVerify"
              :disabled="processing"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-md shadow-emerald-500/20 transition cursor-pointer flex items-center gap-1.5"
            >
              <svg v-if="!processing" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ processing ? 'Memproses...' : 'Setujui & Verifikasi' }}</span>
            </button>
          </div>
        </div>

        <!-- Footer Modal saat mengisi Alasan Penolakan -->
        <div v-else class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50 flex items-center justify-end gap-3">
          <button @click="isRejecting = false" class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition cursor-pointer">
            Kembali
          </button>
          <button
            @click="processReject"
            :disabled="processing || !rejectReason.trim()"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-md shadow-red-500/20 transition cursor-pointer"
          >
            {{ processing ? 'Memproses...' : 'Kirim & Tolak' }}
          </button>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useSwal } from '@/Composables/useSwal.js';
import axios from 'axios';

const props = defineProps({
  personel: Object,
  items: Array,
  options: Object,
  routes: Object,
  flash: Object,
});

const { alertSuccess, alertError } = useSwal();

const form = useForm({
  jenis: 'AKADEMIK',
  jenjang: 'S1',
  program_studi: '',
  nama_institusi: '',
  tahun_lulus: '',
  nomor_ijazah: '',
  front_title: '',
  suffix_gelar: '',
  file_ijazah: null,
});

// Dropdown Kampus & Prodi (Jenjang D3 - Profesi)
const campusContainerRef = ref(null);
const prodiContainerRef = ref(null);
const isManualKampus = ref(false);
const campusResults = ref([]);
const isLoadingCampus = ref(false);
const showCampusDropdown = ref(false);

const prodiResults = ref([]);
const isLoadingProdi = ref(false);
const showProdiDropdown = ref(false);

let campusDebounceTimeout = null;
let prodiDebounceTimeout = null;

const isPerguruanTinggi = computed(() => {
  return form.jenis === 'AKADEMIK' && ['D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI'].includes(form.jenjang);
});

const fetchCampuses = async (query = '') => {
  isLoadingCampus.value = true;
  try {
    const res = await axios.get(route('referensi.kampus'), {
      params: { q: query },
    });
    campusResults.value = res.data || [];
  } catch (err) {
    console.error('Gagal memuat referensi kampus:', err);
    campusResults.value = [];
  } finally {
    isLoadingCampus.value = false;
  }
};

const onCampusInput = () => {
  showCampusDropdown.value = true;
  if (campusDebounceTimeout) clearTimeout(campusDebounceTimeout);
  campusDebounceTimeout = setTimeout(() => {
    fetchCampuses(form.nama_institusi);
  }, 250);
};

const onCampusFocus = () => {
  showCampusDropdown.value = true;
  if (campusResults.value.length === 0) {
    fetchCampuses(form.nama_institusi);
  }
};

const selectCampus = (item) => {
  form.nama_institusi = item.nama;
  showCampusDropdown.value = false;
};

const enableManualKampus = () => {
  isManualKampus.value = true;
  showCampusDropdown.value = false;
};

const disableManualKampus = () => {
  isManualKampus.value = false;
  fetchCampuses(form.nama_institusi);
};

const fetchProdi = async (query = '') => {
  isLoadingProdi.value = true;
  try {
    const res = await axios.get(route('referensi.prodi'), {
      params: { q: query },
    });
    prodiResults.value = res.data || [];
  } catch (err) {
    console.error('Gagal memuat referensi prodi:', err);
    prodiResults.value = [];
  } finally {
    isLoadingProdi.value = false;
  }
};

const onProdiInput = () => {
  showProdiDropdown.value = true;
  if (prodiDebounceTimeout) clearTimeout(prodiDebounceTimeout);
  prodiDebounceTimeout = setTimeout(() => {
    fetchProdi(form.program_studi);
  }, 200);
};

const onProdiFocus = () => {
  showProdiDropdown.value = true;
  if (prodiResults.value.length === 0) {
    fetchProdi(form.program_studi);
  }
};

const selectProdi = (prodiName) => {
  form.program_studi = prodiName;
  showProdiDropdown.value = false;
};

const handleClickOutside = (e) => {
  if (campusContainerRef.value && !campusContainerRef.value.contains(e.target)) {
    showCampusDropdown.value = false;
  }
  if (prodiContainerRef.value && !prodiContainerRef.value.contains(e.target)) {
    showProdiDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  if (campusDebounceTimeout) clearTimeout(campusDebounceTimeout);
  if (prodiDebounceTimeout) clearTimeout(prodiDebounceTimeout);
});

// State Modal Detail & Verifikasi
const showModal = ref(false);
const selectedItem = ref(null);
const isRejecting = ref(false);
const rejectReason = ref('');
const processing = ref(false);

const openModal = (item) => {
  selectedItem.value = item;
  showModal.value = true;
  isRejecting.value = false;
  rejectReason.value = '';
};

const closeModal = () => {
  showModal.value = false;
  selectedItem.value = null;
  isRejecting.value = false;
  rejectReason.value = '';
};

const processVerify = () => {
  if (!selectedItem.value) return;
  processing.value = true;

  const targetUrl = route('admin.education.verify', { education: selectedItem.value.id });

  router.post(targetUrl, {}, {
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil', 'Riwayat pendidikan telah disetujui & diverifikasi.');
      closeModal();
    },
    onError: (err) => {
      alertError('Gagal', 'Terjadi kesalahan saat memverifikasi.');
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};

const processReject = () => {
  if (!selectedItem.value || !rejectReason.value.trim()) return;
  processing.value = true;

  const targetUrl = route('admin.education.reject', { education: selectedItem.value.id });

  router.post(targetUrl, {
    reason: rejectReason.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil Ditolak', 'Pengajuan pendidikan telah ditolak dan notifikasi telah dikirim.');
      closeModal();
    },
    onError: (err) => {
      alertError('Gagal', 'Terjadi kesalahan saat menolak pengajuan.');
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};

const jenjangOptions = computed(() => {
  if (form.jenis === 'MILITER') {
    return [
      'Letnan Dua Perwira Komcad',
      'Sersan Dua Bintara Komcad',
      'Prajurit dua Tamtama Komcad',
      'Lain'
    ];
  } else if (form.jenis === 'DIKLAT') {
    return ['Sertifikasi', 'Kursus', 'Pelatihan', 'Penataran', 'Lain'];
  } else {
    return props.options?.jenjang || ['SD', 'SMP', 'SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI', 'LAIN'];
  }
});

watch(() => form.jenis, (newJenis) => {
  showCampusDropdown.value = false;
  showProdiDropdown.value = false;
  if (newJenis === 'MILITER') {
    form.jenjang = 'Letnan Dua Perwira Komcad';
  } else if (newJenis === 'DIKLAT') {
    form.jenjang = 'Sertifikasi';
  } else {
    form.jenjang = 'S1';
  }
});

watch(() => form.jenjang, () => {
  showCampusDropdown.value = false;
  showProdiDropdown.value = false;
});

const submit = () => {
  form.post(route(props.routes.store, props.routes.params || {}), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      isManualKampus.value = false;
      showCampusDropdown.value = false;
      showProdiDropdown.value = false;
    },
  });
};

const remove = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus riwayat pendidikan ini?')) {
    router.delete(route(props.routes.destroy, id), { preserveScroll: true });
  }
};

const documentUrl = (path) => route('personel.document.download', { path });
</script>
