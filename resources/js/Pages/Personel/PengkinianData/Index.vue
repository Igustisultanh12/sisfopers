<template>
  <AuthenticatedLayout>
    <template #header-title>
      <div class="flex items-center gap-2">
        <span>Pengkinian Data Saya</span>
        <span v-if="personel?.full_name" class="text-xs font-normal text-slate-400">— {{ personel.full_name }} ({{ personel.nikc || '-' }})</span>
      </div>
    </template>

    <div class="max-w-6xl space-y-6">
      <section class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Form Pengkinian Data Personel</h2>
        <p class="text-xs text-slate-500 mt-1">Lakukan pembaruan status keaktifan apabila Anda telah menjadi Anggota TNI, POLRI, atau melaporkan status personel yang telah meninggal dunia.</p>

        <!-- Ringkasan Data Diri Terdaftar (Otomatis dari DB) -->
        <div class="mt-4 p-4 bg-slate-50 border border-[#E2E8F0] rounded-xl grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase">Nama Lengkap</p>
            <p class="font-bold text-slate-800 mt-0.5">{{ personel.full_name }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase">NIK / NIKC</p>
            <p class="font-bold text-slate-800 mt-0.5">{{ personel.nikc || personel.nik }}</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase">Pangkat & Matra</p>
            <p class="font-bold text-slate-800 mt-0.5">{{ personel.pangkat || '-' }} ({{ personel.matra }})</p>
          </div>
          <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase">Status Keaktifan Saat Ini</p>
            <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700">
              {{ personel.status_keaktifan || 'AKTIF' }}
            </span>
          </div>
        </div>

        <form @submit.prevent="submit" class="mt-5 space-y-4 text-xs">
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-600 uppercase text-[10px]">Kategori Pengkinian Data</label>
            <select v-model="form.jenis_pengkinian" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2.5 outline-none focus:border-[#2563EB]">
              <option value="MENINGGAL">Telah Meninggal Dunia</option>
              <option value="TNI_AD">Menjadi Anggota TNI AD</option>
              <option value="TNI_AL">Menjadi Anggota TNI AL</option>
              <option value="TNI_AU">Menjadi Anggota TNI AU</option>
              <option value="POLRI">Menjadi Anggota POLRI</option>
            </select>
          </div>

          <!-- Form Tambahan untuk TNI / POLRI -->
          <div v-if="form.jenis_pengkinian !== 'MENINGGAL'" class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-blue-50/50 border border-blue-100 rounded-xl">
            <div class="flex flex-col gap-1.5">
              <label class="font-bold text-slate-600 uppercase text-[10px]">NRP (Nomor Registrasi Prajurit/Anggota)</label>
              <input v-model="form.nrp" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Masukkan NRP resmi" required />
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-bold text-slate-600 uppercase text-[10px]">TMT Pengangkatan</label>
              <input v-model="form.tmt_pengangkatan" type="date" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" required />
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-bold text-slate-600 uppercase text-[10px]">TMT Masuk Satuan</label>
              <input v-model="form.tmt_masuk_satuan" type="date" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" required />
            </div>

            <div class="flex flex-col gap-1.5">
              <label class="font-bold text-slate-600 uppercase text-[10px]">Nama Satuan</label>
              <input v-model="form.satuan" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: Kodam V/Brawijaya" required />
            </div>

            <div class="flex flex-col gap-1.5 md:col-span-2">
              <label class="font-bold text-slate-600 uppercase text-[10px]">Jabatan</label>
              <input v-model="form.jabatan" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: Bintara Operasi / Danru" required />
            </div>
          </div>

          <!-- Upload Berkas Pendukung -->
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-600 uppercase text-[10px]">
              {{ form.jenis_pengkinian === 'MENINGGAL' ? 'Upload Surat Kematian Resmi (PDF/JPG/PNG Max 4MB)' : 'Upload Berkas Ijazah / SKEP Pengangkatan (PDF/JPG/PNG Max 4MB)' }}
            </label>
            <input type="file" @change="form.document = $event.target.files[0]" class="text-xs text-slate-500 file:py-2 file:px-4 file:border file:border-slate-200 file:rounded-xl file:text-xs file:bg-slate-50 file:cursor-pointer" required />
          </div>

          <!-- Catatan Tambahan -->
          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-600 uppercase text-[10px]">Catatan Tambahan (Opsional)</label>
            <textarea v-model="form.catatan" rows="3" class="rounded-xl border-[#E2E8F0] text-xs p-3 outline-none focus:border-[#2563EB]" placeholder="Tuliskan keterangan pendukung jika diperlukan..."></textarea>
          </div>

          <button type="submit" :disabled="form.processing" class="w-full md:w-auto bg-[#2563EB] hover:bg-[#1E40AF] text-white rounded-xl px-6 py-3 font-bold text-xs transition disabled:opacity-50 cursor-pointer">
            {{ form.processing ? 'Mengirim Pengajuan...' : 'Kirim Pengajuan Pengkinian Data' }}
          </button>
        </form>
      </section>

      <!-- Daftar Riwayat Pengajuan Pengkinian Data -->
      <section class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] bg-slate-50/50 flex items-center justify-between">
          <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Riwayat Pengajuan Pengkinian Data Anda</h3>
          <span class="text-[10px] text-slate-400 font-semibold">Total: {{ items.length }} Pengajuan</span>
        </div>
        <div class="divide-y divide-[#E2E8F0]">
          <article v-for="item in items" :key="item.id" class="p-5 space-y-3 hover:bg-slate-50/50 transition">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg border"
                :class="{
                  'bg-amber-50 text-amber-700 border-amber-200': item.status === 'PENDING',
                  'bg-green-50 text-green-700 border-green-200': item.status === 'APPROVED',
                  'bg-red-50 text-red-700 border-red-200': item.status === 'REJECTED'
                }">
                STATUS: {{ item.status === 'PENDING' ? 'MENUNGGU VERIFIKASI' : (item.status === 'APPROVED' ? 'DISETUJUI' : 'DITOLAK') }}
              </span>
              <span class="text-[10px] text-slate-400">{{ new Date(item.created_at).toLocaleDateString('id-ID') }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200/60">
              <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase">Kategori</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ item.jenis_pengkinian }}</p>
              </div>
              <div v-if="item.nrp">
                <p class="text-[9px] font-bold text-slate-400 uppercase">NRP</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ item.nrp }}</p>
              </div>
              <div v-if="item.satuan">
                <p class="text-[9px] font-bold text-slate-400 uppercase">Satuan & Jabatan</p>
                <p class="font-bold text-slate-800 mt-0.5">{{ item.satuan }} ({{ item.jabatan || '-' }})</p>
              </div>
              <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase">Berkas Pendukung</p>
                <a :href="documentUrl(item.document_path)" target="_blank" class="text-blue-600 hover:underline font-bold mt-0.5 inline-block">
                  📂 Lihat Berkas
                </a>
              </div>
            </div>

            <div v-if="item.status === 'REJECTED' && item.rejection_reason" class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs">
              <p class="font-bold">Alasan Penolakan:</p>
              <p class="mt-0.5 text-red-600">"{{ item.rejection_reason }}"</p>
            </div>
          </article>
          <div v-if="items.length === 0" class="p-12 text-center text-sm text-slate-400 italic">Belum ada riwayat pengajuan pengkinian data.</div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({
  personel: Object,
  items: Array,
});

const { alertSuccess, alertError } = useSwal();

const form = useForm({
  jenis_pengkinian: 'MENINGGAL',
  document: null,
  nrp: '',
  tmt_pengangkatan: '',
  tmt_masuk_satuan: '',
  satuan: '',
  jabatan: '',
  catatan: '',
});

const submit = () => {
  form.post(route('personel.pengkinian-data.store'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      alertSuccess('Berhasil', 'Pengajuan pengkinian data berhasil dikirim.');
      form.reset('document', 'nrp', 'tmt_pengangkatan', 'tmt_masuk_satuan', 'satuan', 'jabatan', 'catatan');
    },
    onError: (err) => {
      alertError('Gagal', 'Terjadi kesalahan saat mengunggah berkas.');
    }
  });
};

const documentUrl = (path) => route('personel.document.download', { path });
</script>
