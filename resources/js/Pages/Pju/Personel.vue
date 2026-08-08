<template>
  <AuthenticatedLayout>
    <template #header-title>Master Personel Jajaran (Monitoring PJU)</template>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Master Data Personel Komcad</h1>
          <p class="text-xs text-slate-400 mt-1">Pemantauan data personel Komponen Cadangan terintegrasi (Read-Only PJU).</p>
        </div>
      </div>

      <!-- Filters & Search Bar -->
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Cari kata kunci: Nama Lengkap, NIK, NIKC..."
            class="w-full pl-4 pr-10 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB]"
          />
          <button
            @click="handleSearch"
            class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600 font-bold"
          >
            Cari
          </button>
        </div>

        <select
          v-model="filterMatra"
          @change="handleSearch"
          class="px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB] text-slate-700 font-medium"
        >
          <option value="">Semua Matra</option>
          <option value="AD">TNI AD</option>
          <option value="AL">TNI AL</option>
          <option value="AU">TNI AU</option>
        </select>

        <input
          v-model="filterAngkatan"
          @keyup.enter="handleSearch"
          type="text"
          placeholder="Tahun Angkatan (cth: 2026)"
          class="px-4 py-2.5 border border-[#E2E8F0] bg-white rounded-xl text-xs outline-none focus:border-[#2563EB] text-slate-700 font-medium w-40"
        />
      </div>

      <!-- Tabel Master Personel Read-Only -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-sm overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold text-[11px] border-b border-[#E2E8F0] uppercase tracking-wider select-none whitespace-nowrap">
              <th class="p-4">Identitas Personel</th>
              <th class="p-4">NIKC & Pangkat</th>
              <th class="p-4">Matra & Angkatan</th>
              <th class="p-4">Status Akun</th>
              <th class="p-4">Alamat Wilayah</th>
              <th class="p-4 text-right">Rincian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#E2E8F0] text-xs text-slate-600">
            <tr v-for="p in personels.data" :key="p.id" class="hover:bg-slate-50/50 transition">
              <td class="p-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <img
                    :src="p.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(p.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(p.full_name || 'PERS')}&background=e2e8f0&color=334155`"
                    class="w-10 h-10 rounded-xl object-cover border border-slate-200 shrink-0"
                    alt="Foto Profil"
                  />
                  <div>
                    <p class="font-extrabold text-slate-800">{{ p.full_name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">NIK: {{ p.nik }}</p>
                  </div>
                </div>
              </td>
              <td class="p-4 whitespace-nowrap">
                <p class="font-mono font-bold text-blue-600">{{ p.nikc || '-' }}</p>
                <p class="text-[10px] text-slate-500 font-semibold">{{ p.pangkat || 'KOMCAD' }}</p>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold border bg-blue-50 text-blue-700 border-blue-200 uppercase inline-block">
                  TNI {{ p.matra }}
                </span>
                <span class="ml-1 text-[11px] font-semibold text-slate-600">/ {{ p.angkatan }}</span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span :class="p.status_profile === 'LENGKAP' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'" class="px-2.5 py-1 rounded-xl text-[10px] font-bold border inline-block">
                  {{ p.status_profile || 'DRAFT' }}
                </span>
              </td>
              <td class="p-4 whitespace-nowrap text-slate-600">
                <p class="font-semibold">{{ p.city || '-' }}</p>
                <p class="text-[10px] text-slate-400">{{ p.province || '-' }}</p>
              </td>
              <td class="p-4 text-right whitespace-nowrap">
                <button
                  @click="openDetail(p)"
                  class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-[#2563EB] text-[11px] font-bold rounded-lg border border-blue-200 transition cursor-pointer"
                >
                  Lihat Detail
                </button>
              </td>
            </tr>
            <tr v-if="personels.data.length === 0">
              <td colspan="6" class="p-12 text-center text-slate-400 italic">
                Belum ada data personel yang sesuai dengan pencarian.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginasi -->
        <div class="p-4 bg-slate-50 border-t border-[#E2E8F0] flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500">
          <span>Menampilkan {{ personels.from || 0 }} sampai {{ personels.to || 0 }} dari {{ personels.total }} personel</span>
          <div class="flex gap-2">
            <Link
              v-for="(link, i) in personels.links"
              :key="i"
              :href="link.url || '#'"
              v-html="link.label"
              class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition"
              :class="link.active ? 'bg-[#2563EB] text-white border-[#2563EB]' : (link.url ? 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' : 'bg-slate-100 text-slate-300 border-slate-100 cursor-not-allowed')"
            />
          </div>
        </div>
      </div>

    </div>

    <!-- MODAL DETAIL PERSONEL (READ ONLY) -->
    <div v-if="selectedPersonel" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white border border-slate-200 rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-5 my-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="text-base font-extrabold text-slate-800">{{ selectedPersonel.full_name }}</h3>
            <p class="font-mono text-xs text-blue-600 font-bold mt-0.5">NIKC: {{ selectedPersonel.nikc || '-' }} | Matra TNI {{ selectedPersonel.matra }}</p>
          </div>
          <button @click="selectedPersonel = null" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1 cursor-pointer">✕</button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">NIK KTP</span>
            <span class="font-bold text-slate-800">{{ selectedPersonel.nik }}</span>
          </div>
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Nomor WhatsApp / HP</span>
            <span class="font-bold text-slate-800">{{ selectedPersonel.phone_number }}</span>
          </div>
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Tempat & Tanggal Lahir</span>
            <span class="font-semibold text-slate-700">{{ selectedPersonel.pob || '-' }}, {{ selectedPersonel.dob || '-' }}</span>
          </div>
          <div>
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Pangkat Komcad</span>
            <span class="font-semibold text-slate-700">{{ selectedPersonel.pangkat || 'KOMCAD' }}</span>
          </div>
          <div class="col-span-2 border-t border-slate-200/60 pt-2">
            <span class="text-slate-400 font-bold uppercase text-[10px] block">Alamat Domisili Lengkap</span>
            <span class="font-medium text-slate-700">{{ selectedPersonel.address || '-' }}, {{ selectedPersonel.village || '-' }}, {{ selectedPersonel.district || '-' }}, {{ selectedPersonel.city || '-' }}, {{ selectedPersonel.province || '-' }}</span>
          </div>
        </div>

        <div class="flex justify-end pt-3 border-t border-slate-100">
          <button
            @click="selectedPersonel = null"
            class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  personels: Object,
  filters: Object,
  userRole: String,
});

const searchQuery = ref(props.filters?.search || '');
const filterMatra = ref(props.filters?.matra || '');
const filterAngkatan = ref(props.filters?.angkatan || '');
const selectedPersonel = ref(null);

function handleSearch() {
  router.get(
    route('pju.personel.index'),
    {
      search: searchQuery.value,
      matra: filterMatra.value,
      angkatan: filterAngkatan.value,
    },
    { preserveState: true, replace: true }
  );
}

function openDetail(p) {
  selectedPersonel.value = p;
}
</script>
