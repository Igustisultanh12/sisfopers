<template>
  <AuthenticatedLayout>
    <template #header-title>Verifikasi & Kelola Pengkinian Data Personel</template>

    <div class="space-y-6 max-w-6xl">
      <!-- Summary / Filter section & Add Button -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Daftar Pengajuan Pengkinian Data</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Admin dapat menyetujui pengajuan personel atau menambahkan pengkinian data personel secara langsung.</p>
          </div>
          
          <div class="flex flex-wrap items-center gap-3">
            <button
              @click="openAddModal"
              class="px-4 py-2 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer"
            >
              <span>+ Tambah Pengkinian Data</span>
            </button>
            <input
              v-model="search"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Cari nama / NIKC..."
              class="px-4 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]"
            />
            <select v-model="statusFilter" @change="handleSearch" class="px-4 py-2 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-[#2563EB]">
              <option value="">Semua Status</option>
              <option value="PENDING">Menunggu Verifikasi</option>
              <option value="APPROVED">Disetujui</option>
              <option value="REJECTED">Ditolak</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tabel Pengajuan -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden text-xs">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-[#E2E8F0] text-slate-500 font-bold uppercase text-[10px]">
                <th class="p-4">No.</th>
                <th class="p-4">Personel</th>
                <th class="p-4">Kategori</th>
                <th class="p-4">Detail Data (NRP/Satuan)</th>
                <th class="p-4">Tanggal Pengajuan</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E2E8F0]">
              <tr v-for="(item, idx) in items.data" :key="item.id" class="hover:bg-slate-50/50 transition">
                <td class="p-4 text-slate-400 font-bold">{{ (items.current_page - 1) * items.per_page + idx + 1 }}.</td>
                <td class="p-4">
                  <p class="font-bold text-slate-800">{{ item.personel?.full_name || '-' }}</p>
                  <p class="text-[10px] text-slate-400">NIKC: {{ item.personel?.nikc || '-' }}</p>
                </td>
                <td class="p-4">
                  <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                    {{ item.jenis_pengkinian }}
                  </span>
                </td>
                <td class="p-4">
                  <p v-if="item.nrp" class="font-bold text-slate-700">NRP: {{ item.nrp }}</p>
                  <p v-if="item.satuan" class="text-slate-500">{{ item.satuan }} ({{ item.jabatan || '-' }})</p>
                  <p v-if="!item.nrp && !item.satuan" class="text-slate-400 italic">Surat Kematian</p>
                </td>
                <td class="p-4 text-slate-500">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</td>
                <td class="p-4 text-center">
                  <span
                    class="px-2.5 py-1 rounded-lg text-[10px] font-bold border"
                    :class="{
                      'bg-amber-50 text-amber-700 border-amber-200': item.status === 'PENDING',
                      'bg-green-50 text-green-700 border-green-200': item.status === 'APPROVED',
                      'bg-red-50 text-red-700 border-red-200': item.status === 'REJECTED'
                    }"
                  >
                    {{ item.status === 'PENDING' ? 'PENDING' : (item.status === 'APPROVED' ? 'APPROVED' : 'REJECTED') }}
                  </span>
                </td>
                <td class="p-4 text-center">
                  <button
                    @click="openModal(item)"
                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-[10px] font-bold rounded-lg transition cursor-pointer"
                  >
                    Detail / Verifikasi
                  </button>
                </td>
              </tr>
              <tr v-if="items.data.length === 0">
                <td colspan="7" class="p-12 text-center text-slate-400 italic">Belum ada data pengajuan pengkinian data.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL ADD PENGKINIAN DATA (ADMIN INPUT) -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 animate-fade-in">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden text-xs">
        <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
          <h3 class="text-sm font-bold text-slate-800">Tambah Pengkinian Data Personel</h3>
          <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer">✕</button>
        </div>

        <form @submit.prevent="submitAddForm" class="p-6 space-y-4 max-h-[85vh] overflow-y-auto">
          <!-- Autocomplete Search Personel / NIKC -->
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-600 uppercase text-[10px]">Cari NIKC / NIK / Nama Personel (Master DB & SKEP)</label>
            <div class="relative">
              <input
                v-model="personelSearchQuery"
                @input="onSearchPersonelInput"
                type="text"
                class="w-full rounded-xl border-[#E2E8F0] text-xs px-4 py-2.5 outline-none focus:border-[#2563EB]"
                placeholder="Ketik minimal 2 karakter (NIKC / NIK / Nama)..."
                required
              />
              <span v-if="isSearching" class="absolute right-3 top-2.5 text-[10px] text-slate-400 font-bold animate-pulse">Memuat...</span>
            </div>

            <!-- Inline Hasil Pencarian Personel -->
            <div v-if="searchResults.length > 0" class="mt-1 bg-white border border-blue-200 rounded-xl shadow-lg max-h-56 overflow-y-auto divide-y divide-slate-100">
              <button
                v-for="p in searchResults"
                :key="p.nikc"
                type="button"
                @click="selectPersonel(p)"
                class="w-full text-left p-3 hover:bg-blue-50/80 transition flex items-center justify-between cursor-pointer"
              >
                <div>
                  <p class="font-bold text-slate-800">{{ p.full_name }}</p>
                  <p class="text-[10px] text-slate-500">NIKC: <span class="font-bold text-slate-700">{{ p.nikc || p.nik }}</span> | {{ p.pangkat || '-' }} ({{ p.matra || '-' }})</p>
                </div>
                <div class="text-right">
                  <span class="text-[9px] font-bold px-2 py-0.5 rounded"
                    :class="p.source === 'MASTER_PERSONEL' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'">
                    {{ p.source === 'MASTER_PERSONEL' ? 'MASTER DB' : 'DATA SKEP' }}
                  </span>
                </div>
              </button>
            </div>
            <div v-else-if="personelSearchQuery.trim().length >= 2 && !isSearching && hasSearched" class="mt-1 p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-700 font-semibold">
              ⚠️ Data tidak ditemukan di Master Personel maupun SKEP. Coba ketik sebagian Nama atau NIKC.
            </div>
          </div>

          <!-- Card Info & Detail Personel Terpilih -->
          <div v-if="selectedPersonel" class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
              <span class="text-[10px] font-bold uppercase text-slate-400">Informasi Profil Personel</span>
              <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800">
                🔒 Data yang sudah ada dikunci (Tampil saja)
              </span>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase">Nama Lengkap</p>
                <p class="font-bold text-slate-800 mt-0.5 text-xs">{{ selectedPersonel.full_name }}</p>
              </div>

              <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase">NIKC / NIK</p>
                <p class="font-bold text-blue-700 mt-0.5 text-xs">{{ selectedPersonel.nikc || selectedPersonel.nik }}</p>
              </div>

              <!-- Pangkat -->
              <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase">Pangkat</p>
                <p v-if="selectedPersonel.pangkat" class="font-bold text-slate-800 mt-0.5 text-xs">{{ selectedPersonel.pangkat }}</p>
                <div v-else class="mt-1">
                  <input
                    v-model="addForm.pangkat"
                    type="text"
                    placeholder="Isi Pangkat..."
                    class="w-full rounded-lg border-[#E2E8F0] text-xs px-2.5 py-1 outline-none focus:border-[#2563EB]"
                    required
                  />
                  <span class="text-[9px] text-amber-600 font-semibold">*Kosong, silakan lengkapi</span>
                </div>
              </div>

              <!-- Matra -->
              <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase">Matra</p>
                <p v-if="selectedPersonel.matra" class="font-bold text-slate-800 mt-0.5 text-xs">{{ selectedPersonel.matra }}</p>
                <div v-else class="mt-1">
                  <select
                    v-model="addForm.matra"
                    class="w-full rounded-lg border-[#E2E8F0] text-xs px-2.5 py-1 outline-none focus:border-[#2563EB]"
                    required
                  >
                    <option value="">Pilih Matra...</option>
                    <option value="AD">TNI AD</option>
                    <option value="AL">TNI AL</option>
                    <option value="AU">TNI AU</option>
                  </select>
                  <span class="text-[9px] text-amber-600 font-semibold">*Kosong, silakan lengkapi</span>
                </div>
              </div>

              <!-- Nomor WhatsApp / HP -->
              <div class="col-span-2 md:col-span-1">
                <p class="text-[9px] font-bold text-slate-400 uppercase">No. WhatsApp / Telepon</p>
                <p v-if="selectedPersonel.phone_number" class="font-bold text-emerald-700 mt-0.5 text-xs">{{ selectedPersonel.phone_number }}</p>
                <div v-else class="mt-1">
                  <input
                    v-model="addForm.phone_number"
                    type="text"
                    placeholder="Contoh: 08123456789"
                    class="w-full rounded-lg border-[#E2E8F0] text-xs px-2.5 py-1 outline-none focus:border-[#2563EB]"
                  />
                  <span class="text-[9px] text-amber-600 font-semibold">*Kosong, silakan lengkapi</span>
                </div>
              </div>

              <!-- Alamat Domisili -->
              <div class="col-span-2 md:col-span-1">
                <p class="text-[9px] font-bold text-slate-400 uppercase">Alamat Domisili</p>
                <p v-if="selectedPersonel.province || selectedPersonel.city" class="font-bold text-slate-800 mt-0.5 text-xs">
                  {{ [selectedPersonel.subdistrict, selectedPersonel.city, selectedPersonel.province].filter(Boolean).join(', ') }}
                </p>
                <div v-else class="mt-1 space-y-1">
                  <input v-model="addForm.province" type="text" placeholder="Provinsi..." class="w-full rounded-lg border-[#E2E8F0] text-xs px-2 py-1 outline-none focus:border-[#2563EB]" />
                  <input v-model="addForm.city" type="text" placeholder="Kota / Kabupaten..." class="w-full rounded-lg border-[#E2E8F0] text-xs px-2 py-1 outline-none focus:border-[#2563EB]" />
                  <input v-model="addForm.subdistrict" type="text" placeholder="Kecamatan..." class="w-full rounded-lg border-[#E2E8F0] text-xs px-2 py-1 outline-none focus:border-[#2563EB]" />
                  <span class="text-[9px] text-amber-600 font-semibold">*Kosong, silakan lengkapi</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Kategori Pengkinian -->
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-600 uppercase text-[10px]">Kategori Pengkinian Data</label>
            <select v-model="addForm.jenis_pengkinian" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2.5 outline-none focus:border-[#2563EB]">
              <option value="MENINGGAL">Telah Meninggal Dunia</option>
              <option value="TNI_AD">Menjadi Anggota TNI AD</option>
              <option value="TNI_AL">Menjadi Anggota TNI AL</option>
              <option value="TNI_AU">Menjadi Anggota TNI AU</option>
              <option value="POLRI">Menjadi Anggota POLRI</option>
            </select>
          </div>

          <!-- Form Tambahan untuk TNI / POLRI -->
          <div v-if="addForm.jenis_pengkinian !== 'MENINGGAL'" class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
            <div class="flex flex-col gap-1">
              <label class="font-bold text-slate-600 uppercase text-[9px]">NRP</label>
              <input v-model="addForm.nrp" class="rounded-xl border-[#E2E8F0] text-xs px-3 py-1.5 outline-none focus:border-[#2563EB]" placeholder="Nomor NRP" required />
            </div>

            <div class="flex flex-col gap-1">
              <label class="font-bold text-slate-600 uppercase text-[9px]">Nama Satuan</label>
              <input v-model="addForm.satuan" class="rounded-xl border-[#E2E8F0] text-xs px-3 py-1.5 outline-none focus:border-[#2563EB]" placeholder="Nama Satuan" required />
            </div>

            <div class="flex flex-col gap-1">
              <label class="font-bold text-slate-600 uppercase text-[9px]">TMT Pengangkatan</label>
              <input v-model="addForm.tmt_pengangkatan" type="date" class="rounded-xl border-[#E2E8F0] text-xs px-3 py-1.5 outline-none focus:border-[#2563EB]" required />
            </div>

            <div class="flex flex-col gap-1">
              <label class="font-bold text-slate-600 uppercase text-[9px]">TMT Masuk Satuan</label>
              <input v-model="addForm.tmt_masuk_satuan" type="date" class="rounded-xl border-[#E2E8F0] text-xs px-3 py-1.5 outline-none focus:border-[#2563EB]" required />
            </div>

            <div class="flex flex-col gap-1 md:col-span-2">
              <label class="font-bold text-slate-600 uppercase text-[9px]">Jabatan</label>
              <input v-model="addForm.jabatan" class="rounded-xl border-[#E2E8F0] text-xs px-3 py-1.5 outline-none focus:border-[#2563EB]" placeholder="Jabatan" required />
            </div>
          </div>

          <!-- Upload Berkas -->
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-600 uppercase text-[10px]">Upload Berkas Pendukung (PDF/JPG/PNG Opsional)</label>
            <input type="file" @change="addForm.document = $event.target.files[0]" class="text-xs text-slate-500 file:py-1.5 file:px-3 file:border file:border-slate-200 file:rounded-xl file:text-xs file:bg-slate-50 file:cursor-pointer" />
          </div>

          <!-- Catatan -->
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-600 uppercase text-[10px]">Catatan Tambahan (Opsional)</label>
            <textarea v-model="addForm.catatan" rows="2" class="rounded-xl border-[#E2E8F0] text-xs p-2.5 outline-none focus:border-[#2563EB]" placeholder="Keterangan tambahan..."></textarea>
          </div>

          <div class="pt-2 flex items-center justify-end gap-3 border-t border-[#E2E8F0]">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition cursor-pointer">Batal</button>
            <button type="submit" :disabled="addForm.processing || !selectedPersonel" class="px-5 py-2 bg-[#2563EB] hover:bg-[#1E40AF] disabled:opacity-50 text-white font-bold rounded-xl shadow-md transition cursor-pointer">
              {{ addForm.processing ? 'Menyimpan...' : 'Simpan & Setujui' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL DETAIL & VERIFIKASI PENGKINIAN DATA -->
    <div v-if="showModal && selectedItem" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 animate-fade-in">
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden text-xs">
        <div class="px-6 py-4 border-b border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
          <div>
            <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border"
              :class="selectedItem.status === 'APPROVED' ? 'bg-green-50 text-green-700 border-green-200' : (selectedItem.status === 'REJECTED' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200')">
              STATUS: {{ selectedItem.status }}
            </span>
            <h3 class="text-sm font-bold text-slate-800 mt-1">Detail Pengkinian Data Personel</h3>
          </div>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 p-1 font-bold text-base cursor-pointer">✕</button>
        </div>

        <div v-if="!isRejecting" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-[#E2E8F0]">
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">Nama Personel</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.personel?.full_name }}</p>
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">NIKC</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.personel?.nikc || '-' }}</p>
            </div>
            <div class="col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Kategori Pengkinian</p>
              <p class="font-bold text-blue-600 mt-0.5">{{ selectedItem.jenis_pengkinian }}</p>
            </div>
            <div v-if="selectedItem.nrp">
              <p class="text-[10px] font-bold text-slate-400 uppercase">NRP</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.nrp }}</p>
            </div>
            <div v-if="selectedItem.satuan">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Satuan & Jabatan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.satuan }} - {{ selectedItem.jabatan || '-' }}</p>
            </div>
            <div v-if="selectedItem.tmt_pengangkatan">
              <p class="text-[10px] font-bold text-slate-400 uppercase">TMT Pengangkatan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.tmt_pengangkatan }}</p>
            </div>
            <div v-if="selectedItem.tmt_masuk_satuan">
              <p class="text-[10px] font-bold text-slate-400 uppercase">TMT Masuk Satuan</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ selectedItem.tmt_masuk_satuan }}</p>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <p class="text-[10px] font-bold text-slate-400 uppercase">Berkas Lampiran Pengkinian</p>
              <a :href="documentUrl(selectedItem.document_path)" target="_blank" class="text-blue-600 hover:underline font-bold text-[10px]">
                📂 Buka Tab Baru
              </a>
            </div>
            <div class="border border-[#E2E8F0] rounded-xl overflow-hidden bg-slate-50">
              <iframe :src="documentUrl(selectedItem.document_path)" class="w-full h-56 border-0"></iframe>
            </div>
          </div>
        </div>

        <div v-else class="p-6 space-y-4">
          <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <p class="font-bold text-xs">Form Penolakan Pengkinian Data</p>
            <p class="text-[11px] mt-0.5 text-red-600">Berikan alasan penolakan agar personel dapat memperbaiki berkas atau data yang diajukan.</p>
          </div>
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase">Alasan Penolakan</label>
            <textarea
              v-model="rejectReason"
              rows="4"
              class="w-full px-4 py-3 border border-[#E2E8F0] rounded-xl text-xs outline-none focus:border-red-500"
              placeholder="Tulis alasan penolakan..."
              required
            ></textarea>
          </div>
        </div>

        <div v-if="!isRejecting" class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50 flex items-center justify-between">
          <button
            v-if="selectedItem.status === 'PENDING'"
            @click="isRejecting = true"
            class="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl font-bold transition cursor-pointer"
          >
            ✕ Tolak Pengajuan
          </button>
          <div v-else></div>

          <div class="flex items-center gap-3">
            <button @click="closeModal" class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition cursor-pointer">
              Tutup
            </button>
            <button
              v-if="selectedItem.status === 'PENDING'"
              @click="processVerify"
              :disabled="processing"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-md transition cursor-pointer"
            >
              {{ processing ? 'Memproses...' : '✓ Setujui & Verifikasi' }}
            </button>
          </div>
        </div>

        <div v-else class="px-6 py-4 border-t border-[#E2E8F0] bg-slate-50 flex items-center justify-end gap-3">
          <button @click="isRejecting = false" class="px-4 py-2 border border-[#E2E8F0] rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition cursor-pointer">
            Kembali
          </button>
          <button
            @click="processReject"
            :disabled="processing || !rejectReason.trim()"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-bold rounded-xl shadow-md transition cursor-pointer"
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
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({
  items: Object,
  filters: Object,
});

const { alertSuccess, alertError } = useSwal();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const showModal = ref(false);
const selectedItem = ref(null);
const isRejecting = ref(false);
const rejectReason = ref('');
const processing = ref(false);

const showAddModal = ref(false);
const personelSearchQuery = ref('');
const searchResults = ref([]);
const selectedPersonel = ref(null);
const isSearching = ref(false);
const hasSearched = ref(false);

const addForm = useForm({
  personel_id: '',
  nikc: '',
  full_name: '',
  pangkat: '',
  matra: '',
  angkatan: '',
  phone_number: '',
  province: '',
  city: '',
  subdistrict: '',
  jenis_pengkinian: 'MENINGGAL',
  document: null,
  nrp: '',
  tmt_pengangkatan: '',
  tmt_masuk_satuan: '',
  satuan: '',
  jabatan: '',
  catatan: '',
});

const openAddModal = () => {
  showAddModal.value = true;
  personelSearchQuery.value = '';
  searchResults.value = [];
  selectedPersonel.value = null;
  hasSearched.value = false;
  addForm.reset();
};

let searchTimeout = null;
const onSearchPersonelInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);

  if (personelSearchQuery.value.trim().length < 2) {
    searchResults.value = [];
    isSearching.value = false;
    hasSearched.value = false;
    return;
  }

  isSearching.value = true;
  searchTimeout = setTimeout(async () => {
    try {
      const response = await fetch('/admin/verifikasi-pengkinian/search-personel?query=' + encodeURIComponent(personelSearchQuery.value));
      if (response.ok) {
        const data = await response.json();
        searchResults.value = data;
      }
    } catch (err) {
      console.error('Search failed:', err);
    } finally {
      isSearching.value = false;
      hasSearched.value = true;
    }
  }, 300);
};

const selectPersonel = (p) => {
  selectedPersonel.value = p;
  addForm.personel_id = p.id || '';
  addForm.nikc = p.nikc || p.nik;
  addForm.full_name = p.full_name;
  addForm.pangkat = p.pangkat || '';
  addForm.matra = p.matra || '';
  addForm.angkatan = p.angkatan || '';
  addForm.phone_number = p.phone_number || '';
  addForm.province = p.province || '';
  addForm.city = p.city || '';
  addForm.subdistrict = p.subdistrict || '';
  personelSearchQuery.value = p.full_name + ' (' + (p.nikc || p.nik) + ')';
  searchResults.value = [];
};

const submitAddForm = () => {
  if (!selectedPersonel.value) return;
  addForm.post(route('admin.pengkinian-data.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil', 'Data pengkinian personel telah ditambahkan.');
      showAddModal.value = false;
      addForm.reset();
      selectedPersonel.value = null;
    },
    onError: () => {
      alertError('Gagal', 'Terjadi kesalahan saat menyimpan data.');
    }
  });
};

const handleSearch = () => {
  router.get(route('admin.pengkinian-data.index'), {
    search: search.value,
    status: statusFilter.value,
  }, { preserveState: true, replace: true });
};

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

  router.post(route('admin.pengkinian-data.verify', selectedItem.value.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil', 'Pengkinian data telah disetujui.');
      closeModal();
    },
    onError: () => {
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

  router.post(route('admin.pengkinian-data.reject', selectedItem.value.id), {
    reason: rejectReason.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil Ditolak', 'Pengajuan pengkinian data telah ditolak.');
      closeModal();
    },
    onError: () => {
      alertError('Gagal', 'Terjadi kesalahan saat menolak pengajuan.');
    },
    onFinish: () => {
      processing.value = false;
    }
  });
};

const documentUrl = (path) => route('personel.document.download', { path });
</script>
