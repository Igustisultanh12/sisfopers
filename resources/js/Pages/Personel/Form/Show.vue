<template>
  <AuthenticatedLayout>
    <template #header-title>Pengisian Formulir & Seleksi</template>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 font-sans space-y-6">
      <!-- Back Navigation & Live Chat Action -->
      <div class="flex items-center justify-between">
        <Link 
          :href="route('personel.form.index')" 
          class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
          Kembali ke Daftar Formulir
        </Link>

        <button 
          @click="showChatModal = true" 
          type="button" 
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition cursor-pointer shadow-sm shadow-blue-600/20"
        >
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <span>Hubungi Live chat</span>
        </button>
      </div>

      <!-- HEADER FORMULIR & DESKRIPSI -->
      <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
        <div class="flex flex-wrap items-center gap-2">
          <span class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-blue-50 text-blue-700 border border-blue-200">
            {{ form.category }}
          </span>

          <span 
            v-if="existingResponse" 
            class="px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase"
            :class="{
              'bg-blue-50 text-blue-700 border border-blue-200': existingResponse.status === 'SUBMITTED',
              'bg-indigo-50 text-indigo-700 border border-indigo-200': existingResponse.status === 'VERIFIED',
              'bg-emerald-50 text-emerald-700 border border-emerald-200': existingResponse.status === 'ACCEPTED',
              'bg-rose-50 text-rose-700 border border-rose-200': existingResponse.status === 'REJECTED',
            }"
          >
            STATUS: {{ getStatusLabel(existingResponse.status) }}
          </span>

          <span v-else-if="isClosed" class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500">
            PENDAFTARAN DITUTUP
          </span>
        </div>

        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900">{{ form.title }}</h1>

        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 text-xs text-slate-600 leading-relaxed space-y-2">
          <p class="font-bold text-slate-700 uppercase tracking-wide text-[10px]">Petunjuk & Ketentuan:</p>
          <p class="whitespace-pre-line">{{ form.description || 'Silakan lengkapi data persyaratan berkas dan jawab pertanyaan seleksi di bawah ini dengan sungguh-sungguh.' }}</p>
          <div v-if="form.deadline" class="pt-1 text-[11px] font-semibold text-slate-500 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Batas Waktu Pengisian: <strong class="text-slate-800">{{ formatDate(form.deadline) }}</strong>
          </div>
        </div>

        <!-- Catatan Verifikator Jika Ada -->
        <div v-if="existingResponse && existingResponse.verification_notes" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
          <p class="font-bold text-[11px] uppercase tracking-wider">Catatan Hasil Seleksi / Verifikator:</p>
          <p>{{ existingResponse.verification_notes }}</p>
        </div>
      </div>

      <!-- TAMPILAN JIKA SUDAH PERNAH MENGIRIM (READ-ONLY REVIEW) -->
      <div v-if="existingResponse" class="space-y-6">
        <!-- Review Data Diri -->
        <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
          <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
            Data Diri yang Diserahkan
          </h2>
          <div class="flex flex-col sm:flex-row gap-5 items-center sm:items-start bg-slate-50 p-4 rounded-2xl border border-slate-200">
            <img 
              :src="personel.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel.full_name)}&background=e2e8f0&color=334155`" 
              class="w-20 h-26 object-cover rounded-xl border border-slate-200 bg-white shrink-0" 
            />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs flex-1 w-full">
              <div><span class="text-slate-400 font-bold block text-[10px]">Nama Lengkap</span><strong class="text-slate-800">{{ personel.full_name }}</strong></div>
              <div><span class="text-slate-400 font-bold block text-[10px]">Pangkat & Matra</span><strong class="text-slate-800">{{ formatPangkat(personel.pangkat) }} (TNI {{ personel.matra }})</strong></div>
              <div><span class="text-slate-400 font-bold block text-[10px]">NIKC / NRP</span><span class="font-mono font-bold text-slate-800">{{ personel.nikc || personel.nrp }}</span></div>
              <div><span class="text-slate-400 font-bold block text-[10px]">NIK KTP</span><span class="font-mono text-slate-800">{{ personel.nik }}</span></div>
              <div><span class="text-slate-400 font-bold block text-[10px]">Email</span><span class="text-slate-700">{{ personel.user?.email }}</span></div>
              <div><span class="text-slate-400 font-bold block text-[10px]">Nomor WhatsApp</span><span class="text-slate-700">{{ personel.phone_number }}</span></div>
              <div class="sm:col-span-2"><span class="text-slate-400 font-bold block text-[10px]">Alamat Domisili</span><span class="text-slate-700">{{ personel.address }}</span></div>
            </div>
          </div>
        </div>

        <!-- Review Berkas yang Diunggah -->
        <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
          <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
            Berkas Persyaratan yang Telah Diunggah
          </h2>
          <div v-if="existingResponse.uploaded_files && Object.keys(existingResponse.uploaded_files).length > 0" class="space-y-2">
            <div 
              v-for="(fMeta, k) in existingResponse.uploaded_files" 
              :key="k"
              class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-3 text-xs"
            >
              <div>
                <p class="font-bold text-slate-800">{{ fMeta.name }}</p>
                <p class="text-[10px] text-slate-400 font-mono">{{ fMeta.original_name }}</p>
              </div>
              <a 
                :href="`/documents/private-stream?path=${encodeURIComponent(fMeta.file_path)}`" 
                target="_blank"
                class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 font-bold text-xs shadow-2xs transition"
              >
                Lihat Berkas
              </a>
            </div>
          </div>
          <div v-else class="text-xs text-slate-400 italic">Tidak ada berkas yang diunggah.</div>
        </div>

        <!-- Review Jawaban Kuesioner -->
        <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
          <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
            Jawaban Kuesioner yang Telah Diserahkan
          </h2>
          <div v-if="existingResponse.answers && Object.keys(existingResponse.answers).length > 0" class="space-y-3">
            <div 
              v-for="(ans, qId) in existingResponse.answers" 
              :key="qId"
              class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-1.5 text-xs"
            >
              <p class="font-bold text-slate-800">{{ ans.question }}</p>
              <div class="bg-white p-3 rounded-xl border border-slate-100 text-slate-700 font-medium">
                {{ ans.answer || '(Tidak dijawab)' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FORM PENDAFTARAN AKTIF (STEP 1: DATA DIRI & BERKAS, STEP 2: PERTANYAAN) -->
      <div v-else-if="!isClosed" class="space-y-6">
        <!-- STEP INDICATOR -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 shadow-xs flex items-center justify-center gap-4 text-xs font-bold select-none">
          <div 
            class="flex items-center gap-2 cursor-pointer"
            :class="currentStep === 1 ? 'text-[#2563EB]' : 'text-slate-400'"
            @click="currentStep = 1"
          >
            <span 
              class="w-6 h-6 rounded-full flex items-center justify-center text-xs"
              :class="currentStep === 1 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'"
            >1</span>
            <span>Data Diri & Persyaratan Berkas</span>
          </div>

          <div class="w-12 h-0.5 bg-slate-200"></div>

          <div 
            class="flex items-center gap-2 cursor-pointer"
            :class="currentStep === 2 ? 'text-[#2563EB]' : 'text-slate-400'"
            @click="validateStep1AndProceed"
          >
            <span 
              class="w-6 h-6 rounded-full flex items-center justify-center text-xs"
              :class="currentStep === 2 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'"
            >2</span>
            <span>Kuesioner Pertanyaan Seleksi</span>
          </div>
        </div>

        <form @submit.prevent="submitFullForm" class="space-y-6">
          <!-- TAHAP 1: DATA DIRI TERISI OTOMATIS & UNGGAH BERKAS -->
          <div v-show="currentStep === 1" class="space-y-6">
            <!-- 1. DATA DIRI TERVERIFIKASI SISTEM (AUTO-FILL) -->
            <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
              <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                  TERISI OTOMATIS DARI DATABASE
                </span>
                <h2 class="text-base font-extrabold text-slate-900 mt-1">Data Diri Personel Terverifikasi</h2>
                <p class="text-xs text-slate-500">
                  Data berikut diambil secara otomatis dari akun Anda sehingga Anda tidak perlu menginput ulang data pokok.
                </p>
              </div>

              <!-- Profil Box -->
              <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col sm:flex-row gap-5 items-center sm:items-start">
                <img 
                  :src="personel.photo_profile ? `/documents/private-stream?path=${encodeURIComponent(personel.photo_profile)}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(personel.full_name)}&background=e2e8f0&color=334155`" 
                  class="w-20 h-26 object-cover rounded-xl border border-slate-200 bg-white shadow-xs shrink-0" 
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs flex-1 w-full">
                  <div>
                    <span class="text-slate-400 font-bold block text-[10px]">Nama Lengkap</span>
                    <strong class="text-slate-800 text-sm">{{ personel.full_name }}</strong>
                  </div>
                  <div>
                    <span class="text-slate-400 font-bold block text-[10px]">Pangkat Resmi & Matra</span>
                    <strong class="text-slate-800">{{ formatPangkat(personel.pangkat) }} (TNI {{ personel.matra }})</strong>
                  </div>
                  <div>
                    <span class="text-slate-400 font-bold block text-[10px]">NIKC / NRP</span>
                    <span class="font-mono font-bold text-slate-800">{{ personel.nikc || personel.nrp }}</span>
                  </div>
                  <div>
                    <span class="text-slate-400 font-bold block text-[10px]">NIK KTP (16 Digit)</span>
                    <span class="font-mono text-slate-800">{{ personel.nik }}</span>
                  </div>
                  <div>
                    <span class="text-slate-400 font-bold block text-[10px]">Alamat Email</span>
                    <span class="text-slate-700">{{ personel.user?.email }}</span>
                  </div>
                  <div>
                    <span class="text-slate-400 font-bold block text-[10px]">Nomor WhatsApp</span>
                    <span class="text-slate-700 font-semibold">{{ personel.phone_number }}</span>
                  </div>
                  <div class="sm:col-span-2">
                    <span class="text-slate-400 font-bold block text-[10px]">Alamat Domisili Terdata</span>
                    <span class="text-slate-700">{{ personel.address }}</span>
                  </div>
                </div>
              </div>

              <!-- Riwayat Pendidikan Singkat -->
              <div v-if="personel.riwayat_pendidikan && personel.riwayat_pendidikan.length > 0" class="pt-2">
                <p class="text-xs font-bold text-slate-700 mb-2">Riwayat Pendidikan Terdaftar:</p>
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                  <table class="w-full text-left text-[11px]">
                    <thead class="bg-slate-100 text-slate-600 font-bold">
                      <tr>
                        <th class="px-3 py-1.5">Jenjang</th>
                        <th class="px-3 py-1.5">Institusi</th>
                        <th class="px-3 py-1.5">Jurusan</th>
                        <th class="px-3 py-1.5">Tahun</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                      <tr v-for="edu in personel.riwayat_pendidikan" :key="edu.id">
                        <td class="px-3 py-1.5 font-bold text-slate-700">{{ edu.jenjang }}</td>
                        <td class="px-3 py-1.5 text-slate-700">{{ edu.nama_institusi }}</td>
                        <td class="px-3 py-1.5 text-slate-600">{{ edu.jurusan || '-' }}</td>
                        <td class="px-3 py-1.5 text-slate-600">{{ edu.tahun_lulus || '-' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- 2. UNGGAH PERSYARATAN BERKAS -->
            <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
              <div>
                <h2 class="text-base font-extrabold text-slate-900">Persyaratan Dokumen & Berkas</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                  Unggah dokumen persyaratan yang diminta berikut. Berkas akan disimpan pada penyimpanan privat terenkripsi.
                </p>
              </div>

              <div v-if="form.requirements && form.requirements.length > 0" class="space-y-4 pt-2">
                <div 
                  v-for="req in form.requirements" 
                  :key="req.id"
                  class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-xs font-bold text-slate-800">
                        {{ req.name }}
                        <span v-if="req.required" class="text-red-500">* (Wajib)</span>
                        <span v-else class="text-slate-400 font-normal">(Opsional)</span>
                      </p>
                      <p v-if="req.description" class="text-[11px] text-slate-500 mt-0.5">{{ req.description }}</p>
                    </div>
                  </div>

                  <!-- File Input Selector -->
                  <div class="flex items-center gap-3">
                    <input 
                      type="file" 
                      :id="'file_' + req.id"
                      :required="req.required"
                      accept=".pdf,.jpg,.jpeg,.png"
                      @change="handleFileUpload($event, req.id)"
                      class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer"
                    />
                  </div>

                  <p v-if="uploadErrors[req.id]" class="text-[11px] text-red-500 font-medium">
                    {{ uploadErrors[req.id] }}
                  </p>
                </div>
              </div>

              <div v-else class="text-xs text-slate-400 italic p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                Tidak ada dokumen khusus yang dipersyaratkan untuk diunggah pada formulir ini.
              </div>

              <!-- Button to proceed to Step 2 -->
              <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button 
                  type="button" 
                  @click="proceedToStep2"
                  class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition cursor-pointer"
                >
                  <span>Lanjut ke Kuesioner Pertanyaan</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- TAHAP 2: KUESIONER PERTANYAAN SELEKSI -->
          <div v-show="currentStep === 2" class="space-y-6">
            <div class="bg-white border border-[#E2E8F0] rounded-3xl p-6 sm:p-8 shadow-xs space-y-6">
              <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                  LANGKAH TERAKHIR
                </span>
                <h2 class="text-base font-extrabold text-slate-900 mt-1">Kuesioner Pertanyaan Seleksi</h2>
                <p class="text-xs text-slate-500">
                  Silakan jawab pertanyaan di bawah ini secara jujur dan akurat. Seluruh jawaban Anda dienkripsi aman pada basis data.
                </p>
              </div>

              <!-- List of Questions -->
              <div v-if="form.questions && form.questions.length > 0" class="space-y-5">
                <div 
                  v-for="(q, qIdx) in form.questions" 
                  :key="q.id"
                  class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-3 text-xs"
                >
                  <p class="font-extrabold text-slate-900 text-sm">
                    {{ qIdx + 1 }}. {{ q.question }}
                    <span v-if="q.required" class="text-red-500">*</span>
                  </p>

                  <!-- Multiple Choice (Radio Buttons) -->
                  <div v-if="q.type === 'multiple_choice'" class="space-y-2 pt-1">
                    <label 
                      v-for="(opt, optIdx) in q.options" 
                      :key="optIdx"
                      class="flex items-center gap-3 p-3 rounded-xl border bg-white cursor-pointer transition select-none"
                      :class="answersData[q.id] === opt ? 'border-blue-500 bg-blue-50/30 text-blue-900 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                    >
                      <input 
                        type="radio" 
                        :name="'q_' + q.id" 
                        :value="opt" 
                        v-model="answersData[q.id]" 
                        :required="q.required"
                        class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500"
                      />
                      <span class="text-xs">{{ opt }}</span>
                    </label>
                  </div>

                  <!-- Short Text Input -->
                  <div v-else-if="q.type === 'text'">
                    <input 
                      type="text" 
                      v-model="answersData[q.id]"
                      :required="q.required"
                      placeholder="Ketikkan jawaban Anda di sini..."
                      class="w-full text-xs bg-white border border-slate-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                  </div>

                  <!-- Textarea Input -->
                  <div v-else-if="q.type === 'textarea'">
                    <textarea 
                      v-model="answersData[q.id]"
                      :required="q.required"
                      rows="3"
                      placeholder="Tuliskan uraian jawaban Anda secara lengkap..."
                      class="w-full text-xs bg-white border border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    ></textarea>
                  </div>

                  <p v-if="questionErrors[q.id]" class="text-[11px] text-red-500 font-medium">
                    {{ questionErrors[q.id] }}
                  </p>
                </div>
              </div>

              <div v-else class="text-xs text-slate-400 italic p-4 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                Tidak ada pertanyaan kuesioner pada formulir ini. Anda dapat langsung mengirimkan formulir.
              </div>

              <!-- Integrity Checkbox -->
              <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 text-xs">
                <label class="flex items-start gap-3 cursor-pointer select-none">
                  <input type="checkbox" v-model="integrityAgreed" required class="w-4 h-4 text-blue-600 rounded mt-0.5" />
                  <span class="text-slate-700 leading-relaxed font-medium">
                    Saya menyatakan bahwa seluruh berkas persyaratan dan jawaban yang saya berikan adalah benar, sah, dan dapat dipertanggungjawabkan sesuai hukum serta tata tertib kedinasan Komponen Cadangan.
                  </span>
                </label>
              </div>

              <!-- Navigation & Submit -->
              <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                <button 
                  type="button" 
                  @click="currentStep = 1"
                  class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition cursor-pointer"
                >
                  Kembali ke Data Diri
                </button>

                <button 
                  type="submit" 
                  :disabled="isSubmitting || !integrityAgreed"
                  class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition cursor-pointer disabled:opacity-50"
                >
                  {{ isSubmitting ? 'Mengirim Formulir...' : 'Kirim Respon Formulir Sekarang' }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- TAMPILAN JIKA DITUTUP DAN BELUM PERNAH MENGISI -->
      <div v-else class="bg-white border border-[#E2E8F0] rounded-3xl p-12 text-center space-y-3">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
        </div>
        <h3 class="text-base font-bold text-slate-800">Pendaftaran / Formulir Ini Telah Ditutup</h3>
        <p class="text-xs text-slate-500 max-w-md mx-auto">
          Masa pengisian formulir ini telah berakhir atau dinonaktifkan oleh Komando. Anda tidak dapat lagi mengirimkan respon baru.
        </p>
      </div>

      <!-- Komponen Modal Obrolan Langsung -->
      <LiveChatModal v-model="showChatModal" />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import LiveChatModal from '@/Components/LiveChatModal.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  form: Object,
  personel: Object,
  existingResponse: Object,
  isClosed: Boolean,
  isDeadlinePassed: Boolean,
  hasCompletedEducation: Boolean,
});

const showChatModal = ref(false);

onMounted(() => {
  // Pengecekan kelengkapan riwayat pendidikan
  if (props.hasCompletedEducation === false) {
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

const currentStep = ref(1);
const integrityAgreed = ref(false);
const isSubmitting = ref(false);

const fileData = reactive({});
const uploadErrors = reactive({});
const answersData = reactive({});
const questionErrors = reactive({});

// Handle file input change
const handleFileUpload = (event, reqId) => {
  const file = event.target.files[0];
  if (file) {
    fileData[reqId] = file;
    uploadErrors[reqId] = null;
  }
};

const proceedToStep2 = () => {
  // Validate required uploads in step 1
  let hasError = false;
  const requirements = props.form.requirements || [];

  for (const req of requirements) {
    if (req.required && !fileData[req.id]) {
      uploadErrors[req.id] = `Dokumen ${req.name} wajib diunggah.`;
      hasError = true;
    } else {
      uploadErrors[req.id] = null;
    }
  }

  if (!hasError) {
    currentStep.value = 2;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const validateStep1AndProceed = () => {
  proceedToStep2();
};

const submitFullForm = () => {
  // Validate questions in step 2
  let hasError = false;
  const questions = props.form.questions || [];

  for (const q of questions) {
    const ans = answersData[q.id];
    if (q.required && (!ans || String(ans).trim() === '')) {
      questionErrors[q.id] = 'Pertanyaan ini wajib dijawab.';
      hasError = true;
    } else {
      questionErrors[q.id] = null;
    }
  }

  if (hasError) return;

  isSubmitting.value = true;

  // Build FormData for multipart upload
  const formData = new FormData();

  // Attach files
  for (const reqId in fileData) {
    if (fileData[reqId]) {
      formData.append(`req_${reqId}`, fileData[reqId]);
    }
  }

  // Attach answers
  for (const qId in answersData) {
    formData.append(`q_${qId}`, answersData[qId] || '');
  }

  router.post(route('personel.form.submit', props.form.uuid), formData, {
    forceFormData: true,
    onSuccess: () => {
      isSubmitting.value = false;
      Swal.fire({
        icon: 'success',
        title: 'Jawaban Berhasil Disimpan',
        text: 'Jawaban dan berkas persyaratan Anda telah berhasil disimpan dan saat ini sedang menunggu proses verifikasi oleh panitia / pembina.',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#2563EB',
        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl' },
      });
    },
    onError: (errors) => {
      isSubmitting.value = false;
      console.error(errors);
    }
  });
};

const getStatusLabel = (status) => {
  switch (status) {
    case 'SUBMITTED': return 'TERKIRIM (MENUNGGU VERIFIKASI)';
    case 'VERIFIED': return 'TELAH DIVERIFIKASI';
    case 'ACCEPTED': return 'LOLOS SELEKSI';
    case 'REJECTED': return 'DITOLAK';
    default: return status || '-';
  }
};

const formatPangkat = (pangkat) => {
  if (!pangkat) return '-';
  return pangkat.endsWith(' KC') ? pangkat : `${pangkat} KC`;
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
