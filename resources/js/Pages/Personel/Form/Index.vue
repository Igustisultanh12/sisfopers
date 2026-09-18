<template>
  <AuthenticatedLayout>
    <template #header-title>Formulir & Rekrutmen Terbuka</template>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 font-sans space-y-6">
      <!-- Banner Header -->
      <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-blue-600/20 blur-2xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="space-y-2">
            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest bg-blue-500/20 border border-blue-400/30 text-blue-300">
              PORTAL PENDAFTARAN & SELEKSI
            </span>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">Formulir & Rekrutmen Personel</h1>
            <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
              Daftar pengumuman seleksi, penugasan operasi, dan pendataan khusus yang ditujukan untuk kualifikasi kepangkatan dan matra Anda.
            </p>
          </div>
          <div class="shrink-0">
            <Link 
              :href="route('personel.chat.index')" 
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-lg shadow-blue-950/40 border border-blue-400/30 transition cursor-pointer"
            >
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              <span>Hubungi Live chat</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Forms List -->
      <div v-if="forms && forms.length > 0" class="space-y-4">
        <div 
          v-for="f in forms" 
          :key="f.id"
          class="bg-white border border-[#E2E8F0] rounded-3xl p-6 shadow-xs hover:border-blue-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-5"
        >
          <div class="space-y-2 flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span 
                class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider"
                :class="{
                  'bg-blue-50 text-blue-700 border border-blue-200': f.category === 'REKRUTMEN',
                  'bg-emerald-50 text-emerald-700 border border-emerald-200': f.category === 'SELEKSI',
                  'bg-amber-50 text-amber-700 border border-amber-200': f.category === 'PENDATAAN',
                  'bg-purple-50 text-purple-700 border border-purple-200': f.category === 'PENUGASAN',
                  'bg-slate-50 text-slate-700 border border-slate-200': f.category === 'LAINNYA',
                }"
              >
                {{ f.category }}
              </span>

              <!-- Status Submission Personel -->
              <span 
                v-if="f.is_submitted" 
                class="px-2.5 py-0.5 rounded-md text-[10px] font-bold"
                :class="{
                  'bg-blue-50 text-blue-700 border border-blue-200': f.submission_status === 'SUBMITTED',
                  'bg-emerald-50 text-emerald-700 border border-emerald-200': f.submission_status === 'ACCEPTED',
                  'bg-rose-50 text-rose-700 border border-rose-200': f.submission_status === 'REJECTED',
                  'bg-indigo-50 text-indigo-700 border border-indigo-200': f.submission_status === 'VERIFIED',
                }"
              >
                {{ getStatusBadge(f.submission_status) }}
              </span>

              <span 
                v-else-if="f.is_deadline_passed || !f.is_active" 
                class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500"
              >
                DITUTUP
              </span>

              <span 
                v-else 
                class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse"
              >
                BELUM MENGISI
              </span>
            </div>

            <h3 class="text-base font-extrabold text-slate-900 leading-snug">{{ f.title }}</h3>
            <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">{{ f.description || 'Tidak ada keterangan tambahan.' }}</p>

            <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-500 pt-1">
              <span class="flex items-center gap-1 font-medium">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Tenggat: <strong class="text-slate-700">{{ f.deadline || 'Tidak dibatasi' }}</strong>
              </span>

              <span v-if="f.is_submitted" class="flex items-center gap-1 text-blue-600 font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Diserahkan pada {{ f.submission_date }}
              </span>
            </div>
          </div>

          <!-- Action Button -->
          <div class="shrink-0 flex items-center">
            <Link 
              :href="route('personel.form.show', f.uuid)"
              class="w-full sm:w-auto text-center px-5 py-2.5 rounded-2xl text-xs font-bold transition shadow-xs cursor-pointer"
              :class="f.is_submitted ? 'bg-slate-100 text-slate-700 hover:bg-slate-200' : 'bg-[#2563EB] hover:bg-blue-700 text-white shadow-blue-600/20'"
            >
              {{ f.is_submitted ? 'Lihat Respon Saya' : 'Isi Formulir' }}
            </Link>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white border border-[#E2E8F0] rounded-3xl p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        </div>
        <h3 class="text-base font-bold text-slate-800">Tidak Ada Formulir Terbuka Saat Ini</h3>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
          Saat ada kegiatan seleksi, rekrutmen, atau pendataan khusus yang ditujukan untuk Anda, informasi akan muncul pada halaman ini.
        </p>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  forms: Array,
  personel: Object,
  hasCompletedEducation: Boolean,
  ineligibleError: String,
});

onMounted(() => {
  // Pengecekan pesan penolakan akses kelayakan sasaran strata formulir
  if (props.ineligibleError) {
    Swal.fire({
      icon: 'warning',
      title: 'Akses Tidak Memenuhi Kriteria',
      text: props.ineligibleError,
      confirmButtonText: 'Mengerti',
      confirmButtonColor: '#2563EB',
      customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl' },
    });
  } else if (props.hasCompletedEducation === false) {
    Swal.fire({
      icon: 'warning',
      title: 'Perhatian',
      text: 'Anda belum melengkapi riwayat pendidikan, Silahkan Lengwapi di Profil anda',
      confirmButtonText: 'Lengkapi Sekarang',
      showCancelButton: true,
      cancelButtonText: 'Nanti',
      confirmButtonColor: '#2563EB',
      cancelButtonColor: '#64748B',
      customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' },
    }).then((result) => {
      if (result.isConfirmed) {
        router.visit(route('personel.education.index'));
      }
    });
  }
});

const getStatusBadge = (status) => {
  switch (status) {
    case 'SUBMITTED': return 'SUDAH TERKIRIM';
    case 'VERIFIED': return 'DIVERIFIKASI';
    case 'ACCEPTED': return 'LOLOS SELEKSI';
    case 'REJECTED': return 'DITOLAK';
    default: return 'SUDAH MENGISI';
  }
};
</script>
