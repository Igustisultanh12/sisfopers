<template>
  <AuthenticatedLayout>
    <div class="p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Generate OTP Manual</h1>
          <p class="text-xs text-slate-400 mt-1">Kelola kode OTP untuk personel yang belum melakukan verifikasi akun via WhatsApp.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <!-- Tombol Generate Semua OTP -->
          <button
            @click="confirmGenerateAll"
            class="flex items-center gap-2 px-4 py-2.5 bg-[#2563EB] hover:bg-[#1E40AF] text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-sm shadow-blue-500/20"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Generate Semua OTP
          </button>
          <!-- Tombol Cetak Semua PDF -->
          <a
            :href="route('admin.otp.bulk-pdf')"
            target="_blank"
            class="flex items-center gap-2 px-4 py-2.5 bg-slate-700 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition cursor-pointer shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Cetak Semua PDF
          </a>
        </div>
      </div>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ $page.props.flash.success }}</span>
      </div>
      <div v-if="$page.props.flash?.error" class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ $page.props.flash.error }}</span>
      </div>

      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="searchQuery"
          @input="debounceSearch"
          type="text"
          placeholder="Cari nama, NIK, atau NIKC..."
          class="flex-1 px-4 py-2.5 border border-[#E2E8F0] rounded-xl text-sm outline-none focus:border-[#2563EB] bg-white"
        />
        <select
          v-model="filterMatra"
          @change="applyFilter"
          class="px-4 py-2.5 border border-[#E2E8F0] rounded-xl text-sm bg-white outline-none focus:border-[#2563EB]"
        >
          <option value="">Semua Matra</option>
          <option value="AD">TNI AD</option>
          <option value="AL">TNI AL</option>
          <option value="AU">TNI AU</option>
        </select>
      </div>

      <!-- Info stats -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 text-center shadow-xs">
          <p class="text-2xl font-extrabold text-amber-600">{{ personels.total }}</p>
          <p class="text-xs text-slate-400 mt-1 font-medium">Total Belum Verifikasi</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 text-center shadow-xs">
          <p class="text-2xl font-extrabold text-blue-600">{{ countHasOtp }}</p>
          <p class="text-xs text-slate-400 mt-1 font-medium">Sudah Punya OTP</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 text-center shadow-xs">
          <p class="text-2xl font-extrabold text-slate-500">{{ countNoOtp }}</p>
          <p class="text-xs text-slate-400 mt-1 font-medium">Belum Generate OTP</p>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead class="bg-slate-50 border-b border-[#E2E8F0]">
              <tr>
                <th class="text-left px-5 py-3.5 font-bold text-slate-500 uppercase tracking-wider">Personel</th>
                <th class="text-left px-5 py-3.5 font-bold text-slate-500 uppercase tracking-wider">Pangkat / Matra</th>
                <th class="text-left px-5 py-3.5 font-bold text-slate-500 uppercase tracking-wider">NIKC</th>
                <th class="text-left px-5 py-3.5 font-bold text-slate-500 uppercase tracking-wider">No. HP</th>
                <th class="text-center px-5 py-3.5 font-bold text-slate-500 uppercase tracking-wider">Status OTP</th>
                <th class="text-center px-5 py-3.5 font-bold text-slate-500 uppercase tracking-wider w-52">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#F1F5F9]">
              <tr v-for="p in personels.data" :key="p.id" class="hover:bg-slate-50/60 transition">
                <td class="px-5 py-4">
                  <div class="flex items-center gap-3">
                    <img
                      :src="p.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(p.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(p.full_name)}&background=e2e8f0&color=334155&size=80`"
                      class="w-9 h-9 rounded-xl object-cover bg-slate-100 border border-[#E2E8F0] shrink-0"
                    />
                    <div>
                      <p class="font-bold text-slate-800">{{ p.full_name }}</p>
                      <p class="text-slate-400 text-[11px]">NIK: {{ p.nik }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4">
                  <p class="font-semibold text-slate-700">{{ p.pangkat }}</p>
                  <p class="text-slate-400">TNI {{ p.matra }} — Ang. {{ p.angkatan }}</p>
                </td>
                <td class="px-5 py-4 font-mono text-slate-600 text-[11px]">{{ p.nikc || '-' }}</td>
                <td class="px-5 py-4 text-slate-600">{{ p.phone_number }}</td>
                <td class="px-5 py-4 text-center">
                  <!-- Sudah Dicetak -->
                  <span
                    v-if="p.manual_otp && p.manual_otp_printed_at"
                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg font-bold text-[10px]"
                  >
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Sudah Dicetak
                  </span>
                  <!-- Punya OTP, belum dicetak -->
                  <span
                    v-else-if="p.manual_otp && !p.manual_otp_printed_at"
                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg font-bold text-[10px]"
                  >
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    OTP: {{ p.manual_otp }}
                  </span>
                  <!-- Belum ada OTP -->
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-50 text-slate-500 border border-slate-200 rounded-lg font-bold text-[10px]"
                  >
                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                    Belum Generate
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <!-- Tombol Generate OTP -->
                    <button
                      @click="confirmGenerate(p)"
                      :class="p.manual_otp
                        ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
                        : 'bg-[#2563EB]/5 text-[#2563EB] border-blue-200 hover:bg-[#2563EB]/10'"
                      class="flex items-center gap-1 px-3 py-1.5 border rounded-lg text-[11px] font-bold transition cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                      </svg>
                      {{ p.manual_otp ? 'Regen' : 'Generate' }}
                    </button>

                    <!-- Tombol Cetak PDF (hanya jika sudah ada OTP) -->
                    <a
                      v-if="p.manual_otp"
                      :href="route('admin.otp.pdf', p.id)"
                      target="_blank"
                      class="flex items-center gap-1 px-3 py-1.5 bg-slate-700 hover:bg-slate-900 text-white border border-slate-700 rounded-lg text-[11px] font-bold transition cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                      </svg>
                      PDF
                    </a>
                  </div>
                </td>
              </tr>
              <tr v-if="personels.data.length === 0">
                <td colspan="6" class="px-5 py-14 text-center text-slate-400 text-xs font-medium">
                  <svg class="w-10 h-10 mx-auto mb-3 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Semua personel sudah melakukan verifikasi OTP.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="personels.last_page > 1" class="px-5 py-4 border-t border-[#F1F5F9] flex flex-wrap items-center justify-between gap-3">
          <p class="text-xs text-slate-400">Menampilkan {{ personels.from }}–{{ personels.to }} dari {{ personels.total }} personel</p>
          <div class="flex gap-1.5 flex-wrap">
            <Link
              v-for="link in personels.links"
              :key="link.label"
              :href="link.url || '#'"
              v-html="link.label"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-semibold border transition',
                link.active ? 'bg-[#2563EB] text-white border-[#2563EB]' : 'bg-white text-slate-500 border-[#E2E8F0] hover:bg-slate-50',
                !link.url ? 'opacity-30 pointer-events-none' : 'cursor-pointer'
              ]"
            />
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useSwal } from '@/Composables/useSwal';

const props = defineProps({
  personels: Object,
  filters: Object,
});

const { confirmAction, alertSuccess, alertError, showLoadingProgress, closeLoading } = useSwal();

const searchQuery = ref(props.filters?.search || '');
const filterMatra  = ref(props.filters?.matra  || '');

let debounceTimer = null;
const debounceSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(applyFilter, 450);
};

const applyFilter = () => {
  router.get(route('admin.otp.index'), {
    search: searchQuery.value,
    matra:  filterMatra.value,
  }, { preserveState: true, replace: true });
};

const countHasOtp = computed(() => props.personels.data.filter(p => p.manual_otp).length);
const countNoOtp  = computed(() => props.personels.data.filter(p => !p.manual_otp).length);

// Generate OTP untuk satu personel
const confirmGenerate = async (personel) => {
  const isRegen = !!personel.manual_otp;
  const confirmed = await confirmAction(
    isRegen ? 'Regenerate OTP?' : 'Generate OTP Manual?',
    isRegen
      ? `OTP lama untuk ${personel.full_name} akan diganti yang baru. Lembar cetak lama tidak berlaku lagi.`
      : `Buat kode OTP 6 digit untuk ${personel.full_name}? OTP berlaku 72 jam dan dapat dicetak ke PDF.`,
    isRegen ? 'Ya, Regenerate!' : 'Ya, Generate!'
  );

  if (confirmed) {
    showLoadingProgress('Sedang membuat kode OTP...');
    router.post(route('admin.otp.generate', personel.id), {}, {
      onSuccess: () => closeLoading(),
      onError: () => {
        closeLoading();
        alertError('Gagal', 'Terjadi kesalahan saat generate OTP.');
      },
    });
  }
};

// Generate OTP untuk SEMUA personel yang belum punya OTP
const confirmGenerateAll = async () => {
  const jumlah = props.personels.total;
  const confirmed = await confirmAction(
    'Generate Semua OTP?',
    `Sistem akan membuat kode OTP untuk semua ${jumlah} personel yang belum verifikasi. Personel yang sudah punya OTP akan di-regenerate.`,
    'Ya, Generate Semua!'
  );

  if (confirmed) {
    showLoadingProgress('Sedang generate OTP untuk semua personel...');
    router.post(route('admin.otp.generate-all'), {}, {
      onSuccess: () => closeLoading(),
      onError: () => {
        closeLoading();
        alertError('Gagal', 'Terjadi kesalahan saat generate OTP massal.');
      },
    });
  }
};
</script>
