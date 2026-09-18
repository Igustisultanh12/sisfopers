<template>
  <AuthenticatedLayout>
    <template #header-title>Pembuatan Formulir & Rekrutmen</template>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 font-sans space-y-6">
      <!-- Top Navigation & Header -->
      <div class="flex items-center justify-between">
        <Link 
          :href="getIndexRoute()" 
          class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
          Kembali ke Daftar Formulir
        </Link>
      </div>

      <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs">
        <div class="border-b border-slate-100 pb-5 mb-6">
          <span class="text-[10px] font-extrabold uppercase tracking-widest text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
            BUILDER FORMULIR SECURE
          </span>
          <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">Buat Formulir / Rekrutmen Baru</h2>
          <p class="text-xs text-slate-500 mt-1">
            Tentukan kriteria target penerima, persyaratan berkas yang harus diunggah, dan daftar pertanyaan kuesioner.
          </p>
        </div>

        <form @submit.prevent="submitForm" class="space-y-8">
          <!-- BAGIAN 1: INFORMASI UMUM -->
          <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
              <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">1</span>
              Informasi Pokok Formulir
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
              <!-- Judul -->
              <div class="md:col-span-2 space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Judul Formulir / Rekrutmen <span class="text-red-500">*</span></label>
                <input 
                  v-model="form.title" 
                  type="text" 
                  required
                  placeholder="Contoh: Seleksi Penugasan Operasi Wilayah Perbatasan 2026" 
                  class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                />
                <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
              </div>

              <!-- Kategori -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Kategori Formulir <span class="text-red-500">*</span></label>
                <select 
                  v-model="form.category" 
                  required
                  class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="REKRUTMEN">REKRUTMEN</option>
                  <option value="SELEKSI">SELEKSI</option>
                  <option value="PENDATAAN">PENDATAAN</option>
                  <option value="PENUGASAN">PENUGASAN</option>
                  <option value="LAINNYA">LAINNYA</option>
                </select>
              </div>

              <!-- Tenggat Waktu / Deadline -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Batas Waktu Pengisian (Deadline)</label>
                <input 
                  v-model="form.deadline" 
                  type="datetime-local" 
                  class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <p class="text-[11px] text-slate-400">Kosongkan jika tidak ada batas waktu penutupan.</p>
              </div>

              <!-- Deskripsi -->
              <div class="md:col-span-2 space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Deskripsi & Petunjuk Pengisian</label>
                <textarea 
                  v-model="form.description" 
                  rows="3"
                  placeholder="Jelaskan maksud dan tujuan kegiatan, ketentuan penugasan, serta hal-hal yang perlu disiapkan oleh personel..."
                  class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl p-4 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                ></textarea>
              </div>
            </div>
          </div>

          <hr class="border-slate-100" />

          <!-- BAGIAN 2: KRITERIA TARGET SASARAN -->
          <div class="space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
              <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">2</span>
              Kriteria Target Sasaran Personel
            </h3>
            <p class="text-xs text-slate-500">
              Hanya personel yang sesuai dengan kriteria berikut yang akan menerima notifikasi dan dapat mengisi formulir ini.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
              <!-- Target Kepangkatan -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Strata Kepangkatan <span class="text-red-500">*</span></label>
                <select 
                  v-model="form.target_rank_category" 
                  required
                  class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="ALL">Semua Strata Pangkat</option>
                  <option value="PERWIRA">Khusus Perwira (Letda s/d Kolonel)</option>
                  <option value="BINTARA">Khusus Bintara (Serda s/d Peltu)</option>
                  <option value="TAMTAMA">Khusus Tamtama (Prada s/d Kopka)</option>
                </select>
              </div>

              <!-- Target Matra -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Matra Personel <span class="text-red-500">*</span></label>
                <select 
                  v-model="form.target_matra" 
                  required
                  class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="ALL">Semua Matra (AD / AL / AU)</option>
                  <option value="AD">Khusus TNI AD</option>
                  <option value="AL">Khusus TNI AL</option>
                  <option value="AU">Khusus TNI AU</option>
                </select>
              </div>

              <!-- Target Angkatan -->
              <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700">Tahun Angkatan <span class="text-red-500">*</span></label>
                <select 
                  v-model="form.target_angkatan" 
                  required
                  class="w-full text-sm bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="ALL">Semua Tahun Angkatan</option>
                  <option v-for="ang in angkatanList" :key="ang" :value="ang">{{ ang }}</option>
                </select>
              </div>
            </div>
          </div>

          <hr class="border-slate-100" />

          <!-- BAGIAN 3: PERSYARATAN BERKAS YANG DIUNGGAH -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">3</span>
                  Persyaratan Dokumen & Berkas (Upload)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                  Tentukan berkas yang wajib atau opsional diunggah oleh calon personel (disimpan aman di storage privat).
                </p>
              </div>

              <button 
                type="button" 
                @click="addRequirement"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Berkas
              </button>
            </div>

            <!-- List of Requirements -->
            <div v-if="form.requirements.length > 0" class="space-y-3 pt-2">
              <div 
                v-for="(req, idx) in form.requirements" 
                :key="req.id"
                class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3 relative"
              >
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-bold text-slate-700">Berkas #{{ idx + 1 }}</span>
                  <button 
                    type="button" 
                    @click="removeRequirement(idx)"
                    class="text-xs text-red-500 hover:text-red-700 font-bold cursor-pointer"
                  >
                    Hapus
                  </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600">Nama Dokumen Persyaratan <span class="text-red-500">*</span></label>
                    <input 
                      v-model="req.name" 
                      type="text" 
                      required
                      placeholder="Contoh: Sertifikat Diksar / SK Jabatan Terakhir"
                      class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                  </div>

                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600">Petunjuk Khusus Berkas</label>
                    <input 
                      v-model="req.description" 
                      type="text" 
                      placeholder="Contoh: Format PDF/JPG, maksimal ukuran 5MB"
                      class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                  </div>
                </div>

                <div class="flex items-center gap-4 text-xs">
                  <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" v-model="req.required" class="w-4 h-4 text-blue-600 rounded" />
                    <span class="font-semibold text-slate-700">Wajib Diunggah (Mandatory)</span>
                  </label>
                </div>
              </div>
            </div>

            <div v-else class="border border-dashed border-slate-200 rounded-2xl p-6 text-center text-xs text-slate-400">
              Belum ada berkas persyaratan yang ditambahkan. Klik tombol "Tambah Berkas" di atas jika ada dokumen yang perlu diunggah personel.
            </div>
          </div>

          <hr class="border-slate-100" />

          <!-- BAGIAN 4: KUESIONER PERTANYAAN DINAMIS -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                  <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">4</span>
                  Kuesioner / Pertanyaan Seleksi (Enkripsi Aman)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                  Berikan pertanyaan dengan model Pilihan Ganda atau Isian Bebas. Jawaban personel akan dienkripsi pada basis data.
                </p>
              </div>

              <button 
                type="button" 
                @click="addQuestion"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Pertanyaan
              </button>
            </div>

            <!-- List of Questions -->
            <div v-if="form.questions.length > 0" class="space-y-4 pt-2">
              <div 
                v-for="(q, qIdx) in form.questions" 
                :key="q.id"
                class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3"
              >
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs font-extrabold text-slate-700">Pertanyaan #{{ qIdx + 1 }}</span>
                  <button 
                    type="button" 
                    @click="removeQuestion(qIdx)"
                    class="text-xs text-red-500 hover:text-red-700 font-bold cursor-pointer"
                  >
                    Hapus
                  </button>
                </div>

                <div class="space-y-1">
                  <label class="text-[11px] font-bold text-slate-600">Teks Pertanyaan <span class="text-red-500">*</span></label>
                  <textarea 
                    v-model="q.question" 
                    required
                    rows="2"
                    placeholder="Contoh: Apakah Anda bersedia ditugaskan di wilayah perbatasan selama minimal 6 bulan?"
                    class="w-full text-xs bg-white border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                  ></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- Tipe Pertanyaan -->
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-600">Model Jawaban</label>
                    <select 
                      v-model="q.type" 
                      class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                      <option value="multiple_choice">Pilihan Ganda (Multiple Choice)</option>
                      <option value="text">Isian Singkat (Text)</option>
                      <option value="textarea">Isian Paragraf / Uraian (Textarea)</option>
                    </select>
                  </div>

                  <!-- Required Checkbox -->
                  <div class="flex items-center pt-5">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs">
                      <input type="checkbox" v-model="q.required" class="w-4 h-4 text-blue-600 rounded" />
                      <span class="font-semibold text-slate-700">Wajib Dijawab oleh Personel</span>
                    </label>
                  </div>
                </div>

                <!-- Opsi Jawaban untuk Pilihan Ganda -->
                <div v-if="q.type === 'multiple_choice'" class="space-y-2 pt-2 border-t border-slate-200/60">
                  <div class="flex items-center justify-between">
                    <label class="text-[11px] font-bold text-slate-600">Daftar Pilihan Jawaban</label>
                    <button 
                      type="button" 
                      @click="addOption(q)"
                      class="text-[11px] text-blue-600 hover:text-blue-800 font-bold cursor-pointer"
                    >
                      + Tambah Pilihan
                    </button>
                  </div>

                  <div class="space-y-1.5">
                    <div 
                      v-for="(opt, optIdx) in q.options" 
                      :key="optIdx" 
                      class="flex items-center gap-2"
                    >
                      <span class="text-[11px] font-bold text-slate-400 w-4">{{ String.fromCharCode(65 + optIdx) }}.</span>
                      <input 
                        v-model="q.options[optIdx]" 
                        type="text" 
                        required
                        placeholder="Tuliskan teks opsi pilihan..."
                        class="flex-1 text-xs bg-white border border-slate-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                      />
                      <button 
                        v-if="q.options.length > 2"
                        type="button" 
                        @click="removeOption(q, optIdx)"
                        class="text-slate-400 hover:text-red-500 p-1"
                      >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="border border-dashed border-slate-200 rounded-2xl p-6 text-center text-xs text-slate-400">
              Belum ada pertanyaan kuesioner. Klik tombol "Tambah Pertanyaan" di atas untuk menyusun pertanyaan tes/seleksi.
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
            <Link 
              :href="getIndexRoute()"
              class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
            >
              Batal
            </Link>

            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition cursor-pointer disabled:opacity-50"
            >
              {{ form.processing ? 'Menyimpan & Mengirim Notifikasi...' : 'Terbitkan Formulir Sekarang' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  angkatanList: Array,
  userRole: String,
});

const getPrefix = () => {
  if (['ka_bacadnas','ses_bacadnas','kapus_komcad','pembina_matra','pembina_kodam','pembina_kodaeral','pembina_kodau','pembina_kodim','pembina_lanal','pembina_lanud'].includes(props.userRole)) {
    return 'pju';
  }
  if (props.userRole === 'kordinator_matra' || props.userRole === 'kordinator_angkatan') {
    return 'kordinator';
  }
  return 'admin';
};

const getIndexRoute = () => {
  return route(`${getPrefix()}.form.index`);
};

const form = useForm({
  title: '',
  category: 'REKRUTMEN',
  description: '',
  deadline: '',
  target_rank_category: 'ALL',
  target_matra: 'ALL',
  target_angkatan: 'ALL',
  requirements: [
    {
      id: 'req_' + Date.now(),
      name: 'Surat Permohonan / Pernyataan Diri',
      description: 'Format PDF atau scan dokumen bertanda tangan',
      required: true,
      file_types: ['pdf', 'jpg', 'jpeg', 'png'],
    }
  ],
  questions: [
    {
      id: 'q_' + Date.now(),
      question: 'Apakah Anda bersedia mengikuti rangkaian seleksi dan mematuhi seluruh ketentuan dinas?',
      type: 'multiple_choice',
      options: ['Sangat Bersedia & Siap', 'Bersedia', 'Tidak Bersedia'],
      required: true,
    }
  ],
});

const addRequirement = () => {
  form.requirements.push({
    id: 'req_' + Date.now() + '_' + Math.random().toString(36).substring(2, 5),
    name: '',
    description: '',
    required: true,
    file_types: ['pdf', 'jpg', 'jpeg', 'png'],
  });
};

const removeRequirement = (idx) => {
  form.requirements.splice(idx, 1);
};

const addQuestion = () => {
  form.questions.push({
    id: 'q_' + Date.now() + '_' + Math.random().toString(36).substring(2, 5),
    question: '',
    type: 'multiple_choice',
    options: ['Ya / Siap', 'Tidak'],
    required: true,
  });
};

const removeQuestion = (idx) => {
  form.questions.splice(idx, 1);
};

const addOption = (question) => {
  if (!question.options) question.options = [];
  question.options.push('');
};

const removeOption = (question, optIdx) => {
  question.options.splice(optIdx, 1);
};

const submitForm = () => {
  form.post(route(`${getPrefix()}.form.store`));
};
</script>
