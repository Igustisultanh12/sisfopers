<template>
  <AuthenticatedLayout>
    <template #header-title>Manajemen Formulir & Rekrutmen</template>

    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 font-sans">
      <!-- Header Banner & Action -->
      <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100 mb-2">
            MODUL REKRUTMEN & PENUGASAN
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Formulir & Seleksi Personel</h1>
          <p class="text-sm text-slate-500 mt-1 max-w-2xl">
            Kelola formulir rekrutmen, persyaratan berkas dinamis, kuesioner pertanyaan, dan target kriteria kepangkatan/matra jajaran Komponen Cadangan.
          </p>
        </div>

        <div class="shrink-0 flex items-center gap-3">
          <Link 
            :href="getCreateRoute()" 
            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#2563EB] hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 transition cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Buat Formulir Baru
          </Link>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
        <div class="relative w-full md:w-80">
          <input 
            v-model="search" 
            @input="handleSearch"
            type="text" 
            placeholder="Cari judul formulir atau kategori..." 
            class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
          <!-- Filter Status -->
          <select 
            v-model="statusFilter" 
            @change="handleFilterChange"
            class="text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Status</option>
            <option value="active">Aktif / Terbuka</option>
            <option value="inactive">Ditutup / Nonaktif</option>
          </select>

          <!-- Filter Kategori -->
          <select 
            v-model="categoryFilter" 
            @change="handleFilterChange"
            class="text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Semua Kategori</option>
            <option value="REKRUTMEN">REKRUTMEN</option>
            <option value="SELEKSI">SELEKSI</option>
            <option value="PENDATAAN">PENDATAAN</option>
            <option value="PENUGASAN">PENUGASAN</option>
            <option value="LAINNYA">LAINNYA</option>
          </select>
        </div>
      </div>

      <!-- Form Cards Grid -->
      <div v-if="forms.data && forms.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div 
          v-for="form in forms.data" 
          :key="form.id" 
          class="bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-xs hover:shadow-md transition flex flex-col justify-between"
        >
          <div>
            <!-- Header Card Status & Category -->
            <div class="flex items-center justify-between gap-2 mb-3">
              <span 
                class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider"
                :class="{
                  'bg-blue-50 text-blue-700 border border-blue-200': form.category === 'REKRUTMEN',
                  'bg-emerald-50 text-emerald-700 border border-emerald-200': form.category === 'SELEKSI',
                  'bg-amber-50 text-amber-700 border border-amber-200': form.category === 'PENDATAAN',
                  'bg-purple-50 text-purple-700 border border-purple-200': form.category === 'PENUGASAN',
                  'bg-slate-50 text-slate-700 border border-slate-200': form.category === 'LAINNYA',
                }"
              >
                {{ form.category }}
              </span>

              <span 
                class="px-2.5 py-1 rounded-lg text-[10px] font-bold"
                :class="form.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
              >
                {{ form.is_active ? 'TERBUKA' : 'DITUTUP' }}
              </span>
            </div>

            <!-- Title & Description -->
            <h3 class="text-base font-bold text-slate-900 line-clamp-2 leading-snug mb-2">{{ form.title }}</h3>
            <p class="text-xs text-slate-500 line-clamp-3 mb-4">{{ form.description || 'Tidak ada deskripsi tambahan.' }}</p>

            <!-- Metadata Badges -->
            <div class="space-y-2 bg-slate-50 p-3 rounded-2xl border border-slate-100 mb-4 text-xs">
              <div class="flex items-center justify-between text-slate-600">
                <span class="font-medium text-[11px]">Sasaran Pangkat:</span>
                <span class="font-bold text-slate-800 text-[11px] uppercase">
                  {{ form.target_rank_category === 'ALL' ? 'Semua Strata' : form.target_rank_category }}
                </span>
              </div>
              <div class="flex items-center justify-between text-slate-600">
                <span class="font-medium text-[11px]">Sasaran Matra:</span>
                <span class="font-bold text-slate-800 text-[11px]">
                  {{ form.target_matra === 'ALL' ? 'Semua Matra (AD/AL/AU)' : `TNI ${form.target_matra}` }}
                </span>
              </div>
              <div class="flex items-center justify-between text-slate-600">
                <span class="font-medium text-[11px]">Tenggat Waktu:</span>
                <span class="font-semibold text-slate-700 text-[11px]">
                  {{ form.deadline ? formatDate(form.deadline) : 'Tidak Dibatasi' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Footer Actions & Responses Count -->
          <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
            <!-- Responses Link Button -->
            <Link 
              :href="getResponsesRoute(form.uuid)"
              class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-blue-50 text-[#2563EB] hover:bg-blue-100 transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
              <span>{{ form.responses_count || 0 }} Respon</span>
            </Link>

            <!-- Action Dropdown / Buttons -->
            <div class="flex items-center gap-1">
              <!-- Tombol Salin Tautan Langsung (Direct Link) -->
              <button 
                @click="copyDirectLink(form)" 
                title="Salin Direct Link untuk Disebarkan"
                class="p-2 rounded-xl text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                </svg>
              </button>

              <button 
                @click="toggleFormStatus(form)" 
                :title="form.is_active ? 'Tutup Formulir' : 'Buka Formulir'"
                class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition cursor-pointer"
              >
                <svg v-if="form.is_active" class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                <svg v-else class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
              </button>

              <Link 
                :href="getEditRoute(form.uuid)"
                title="Edit Formulir"
                class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
              </Link>

              <button 
                @click="confirmDelete(form)"
                title="Hapus Formulir"
                class="p-2 rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-600 transition cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white border border-[#E2E8F0] rounded-3xl p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
        </div>
        <h3 class="text-base font-bold text-slate-800">Belum Ada Formulir yang Diterbitkan</h3>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
          Mulai buat formulir rekrutmen, penugasan, atau pendataan untuk jajaran personel Komcad.
        </p>
        <Link 
          :href="getCreateRoute()" 
          class="inline-flex items-center gap-2 mt-5 px-4 py-2.5 rounded-xl bg-[#2563EB] text-white text-xs font-bold shadow-sm hover:bg-blue-700 transition"
        >
          Buat Formulir Baru
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="forms.links && forms.links.length > 3" class="flex justify-center mt-6">
        <div class="flex flex-wrap gap-1 bg-white p-2 rounded-2xl border border-slate-200 shadow-xs">
          <template v-for="(link, key) in forms.links" :key="key">
            <Link
              v-if="link.url"
              :href="link.url"
              v-html="link.label"
              class="px-3 py-1.5 text-xs rounded-xl font-medium transition"
              :class="link.active ? 'bg-[#2563EB] text-white font-bold' : 'text-slate-600 hover:bg-slate-100'"
            />
            <span
              v-else
              v-html="link.label"
              class="px-3 py-1.5 text-xs text-slate-300 rounded-xl"
            />
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  forms: Object,
  filters: Object,
  userRole: String,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const categoryFilter = ref(props.filters?.category || '');

let searchTimeout = null;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 400);
};

const handleFilterChange = () => {
  applyFilters();
};

const applyFilters = () => {
  router.get(
    window.location.pathname,
    {
      search: search.value,
      status: statusFilter.value,
      category: categoryFilter.value,
    },
    { preserveState: true, replace: true }
  );
};

const getPrefix = () => {
  if (['ka_bacadnas','ses_bacadnas','kapus_komcad','pembina_matra','pembina_kodam','pembina_kodaeral','pembina_kodau','pembina_kodim','pembina_lanal','pembina_lanud'].includes(props.userRole)) {
    return 'pju';
  }
  if (props.userRole === 'kordinator_matra' || props.userRole === 'kordinator_angkatan') {
    return 'kordinator';
  }
  return 'admin';
};

const getCreateRoute = () => {
  return route(`${getPrefix()}.form.create`);
};

const getEditRoute = (uuid) => {
  return route(`${getPrefix()}.form.edit`, uuid);
};

const getResponsesRoute = (uuid) => {
  return route(`${getPrefix()}.form.responses`, uuid);
};

const toggleFormStatus = (form) => {
  router.patch(route(`${getPrefix()}.form.toggle-status`, form.uuid), {}, {
    preserveScroll: true,
  });
};

const confirmDelete = (form) => {
  if (confirm(`Apakah Anda yakin ingin menghapus formulir "${form.title}"? Seluruh respon yang telah masuk akan diarsipkan.`)) {
    router.delete(route(`${getPrefix()}.form.destroy`, form.uuid));
  }
};

const copyDirectLink = (form) => {
  const directUrl = `${window.location.origin}/f/${form.uuid}`;
  navigator.clipboard.writeText(directUrl).then(() => {
    Swal.fire({
      icon: 'success',
      title: 'Tautan Berhasil Disalin',
      text: `Direct link untuk '${form.title}' telah disalin ke clipboard:\n${directUrl}`,
      confirmButtonText: 'Tutup',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl' },
    });
  }).catch(() => {
    Swal.fire({
      title: 'Tautan Formulir',
      text: directUrl,
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl' },
    });
  });
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }) + ' WIB';
};
</script>
