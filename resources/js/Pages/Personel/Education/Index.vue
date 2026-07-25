<template>
  <AuthenticatedLayout>
    <template #sidebar-menu>
      <Link :href="route('personel.dashboard')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Dashboard Saya</Link>
      <Link :href="route('personel.education.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#2563EB]/5 text-[#2563EB]">Riwayat Pendidikan</Link>
      <Link :href="route('personel.broadcast.index')" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50">Tugas & Broadcast Kegiatan</Link>
    </template>

    <template #header-title>Riwayat Pendidikan & Diklat</template>

    <div class="max-w-6xl space-y-6">
      <section class="bg-white border border-[#E2E8F0] rounded-2xl p-6 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Tambah Riwayat Pendidikan</h2>
        <p class="text-xs text-slate-505 mt-1">Pendidikan akademik menjadi sumber gelar tampilan; diklat dan militer tetap tercatat sebagai riwayat pendukung.</p>

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

          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Program Studi / Bidang Keahlian</label>
            <input v-model="form.program_studi" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: Teknik Informatika" />
          </div>

          <div class="flex flex-col gap-1.5">
            <label class="font-bold text-slate-500 uppercase text-[10px]">Nama Institusi / Penyelenggara</label>
            <input v-model="form.nama_institusi" class="rounded-xl border-[#E2E8F0] text-xs px-4 py-2 outline-none focus:border-[#2563EB]" placeholder="Contoh: Universitas Indonesia" />
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
            <input type="file" @input="form.file_ijazah = $event.target.files[0]" class="text-xs text-slate-500 mt-1 file:py-1 file:px-3 file:border file:border-slate-200 file:rounded-lg file:text-xs file:bg-slate-50 file:cursor-pointer" />
          </div>

          <button type="submit" :disabled="form.processing" class="md:col-span-3 bg-[#2563EB] hover:bg-[#1E40AF] text-white rounded-xl px-4 py-2.5 font-bold text-xs transition disabled:opacity-50 mt-2 cursor-pointer">
            Simpan Riwayat Pendidikan
          </button>
        </form>
      </section>

      <section class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-[#E2E8F0] bg-slate-50/50">
          <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Daftar Riwayat Pendidikan & Diklat Anda</h3>
        </div>
        <div class="divide-y divide-[#E2E8F0]">
          <article v-for="item in items" :key="item.id" class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-blue-50 text-[#2563EB] border border-blue-100">{{ item.jenis }}</span>
                <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">{{ item.jenjang }}</span>
                <span v-if="item.verified_at" class="text-[9px] font-bold px-2 py-0.5 rounded bg-green-50 text-green-700 border border-green-200">TERVERIFIKASI</span>
                <span v-else class="text-[9px] font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">MENUNGGU VERIFIKASI</span>
              </div>
              <h4 class="font-bold text-slate-800 text-sm mt-2">{{ item.program_studi || '-' }}</h4>
              <p class="text-xs text-slate-500">{{ item.nama_institusi || '-' }} · Lulus Tahun {{ item.tahun_lulus || '-' }}</p>
              <p v-if="item.front_title || item.suffix_gelar" class="text-xs text-slate-500">
                Gelar Tersimpan: <span class="font-semibold text-slate-700">{{ [item.front_title, item.suffix_gelar].filter(Boolean).join(' / ') }}</span>
              </p>
              <p v-if="item.nomor_ijazah" class="text-xs text-slate-400">No. Ijazah/Sertifikat: {{ item.nomor_ijazah }}</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
              <a v-if="item.file_ijazah_path" :href="documentUrl(item.file_ijazah_path)" target="_blank" class="font-bold text-[#2563EB] hover:underline flex items-center gap-1">
                📂 Lihat Berkas
              </a>
              <button v-if="routes.verify && !item.verified_at" @click="verify(item.id)" class="font-bold text-green-600 hover:underline cursor-pointer">
                ✓ Verifikasi
              </button>
              <button @click="remove(item.id)" class="font-bold text-red-500 hover:underline cursor-pointer">
                🗑️ Hapus
              </button>
            </div>
          </article>
          <div v-if="items.length === 0" class="p-12 text-center text-sm text-slate-400 italic">Belum ada riwayat pendidikan yang ditambahkan.</div>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
  personel: Object,
  items: Array,
  options: Object,
  routes: Object,
  flash: Object,
});

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
    return props.options.jenjang || ['SD', 'SMP', 'SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI', 'LAIN'];
  }
});

watch(() => form.jenis, (newJenis) => {
  if (newJenis === 'MILITER') {
    form.jenjang = 'Letnan Dua Perwira Komcad';
  } else if (newJenis === 'DIKLAT') {
    form.jenjang = 'Sertifikasi';
  } else {
    form.jenjang = 'S1';
  }
});

const submit = () => {
  form.post(route(props.routes.store, props.routes.params || {}), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
  });
};

const remove = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus riwayat pendidikan ini?')) {
    router.delete(route(props.routes.destroy, id), { preserveScroll: true });
  }
};

const verify = (id) => {
  router.post(route(props.routes.verify, id), {}, { preserveScroll: true });
};

const documentUrl = (path) => route('personel.document.download', { path });
</script>
