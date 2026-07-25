<script setup>
import { ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  mailConfig: Object
})

const activeTab = ref('config') // 'config' | 'test' | 'guide'
const showPassword = ref(false)

const form = useForm({
  mail_mailer: props.mailConfig?.mail_mailer || 'smtp',
  mail_host: props.mailConfig?.mail_host || 'smtp.gmail.com',
  mail_port: props.mailConfig?.mail_port || '587',
  mail_username: props.mailConfig?.mail_username || '',
  mail_password: props.mailConfig?.mail_password || '',
  mail_encryption: props.mailConfig?.mail_encryption || 'tls',
  mail_from_address: props.mailConfig?.mail_from_address || 'no-reply@sisfoperskc.my.id',
  mail_from_name: props.mailConfig?.mail_from_name || 'Sisfoperskc',
  enable_email_otp: props.mailConfig?.enable_email_otp || '1',
})

const testForm = useForm({
  recipient_email: ''
})

const presetGmail = () => {
  form.mail_mailer = 'smtp'
  form.mail_host = 'smtp.gmail.com'
  form.mail_port = '587'
  form.mail_encryption = 'tls'
  form.mail_from_name = 'Sisfoperskc'
}

const saveConfig = () => {
  form.post(route('admin.mail.update'), {
    preserveScroll: true,
  })
}

const sendTestEmail = () => {
  testForm.post(route('admin.mail.test'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Mail Gateway — Sisfoperskc" />

  <AuthenticatedLayout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
      
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
              Gateway SMTP
            </span>
            <span class="text-slate-400 text-xs">•</span>
            <span class="text-xs font-medium text-slate-500">Pengaturan Email & OTP</span>
          </div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
            Mail Gateway
          </h1>
          <p class="text-sm text-slate-500 mt-0.5">
            Kelola server SMTP pengiriman Email OTP, notifikasi sistem, dan pengujian koneksi real-time.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <button 
            @click="presetGmail" 
            type="button" 
            class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300/80 transition cursor-pointer flex items-center gap-2"
          >
            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L12 9.545l8.073-6.052C21.69 2.28 24 3.434 24 5.457z"/></svg>
            Preset Gmail SMTP
          </button>
        </div>
      </div>

      <!-- Flash Notification Alerts -->
      <div v-if="$page.props.flash?.success" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2.5">
          <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span>{{ $page.props.flash.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash?.error" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2.5">
          <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span>{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Status Metric Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Driver Mailer</p>
            <p class="text-xl font-extrabold text-slate-900 uppercase mt-1">{{ form.mail_mailer }}</p>
            <p class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Mode Aktif
            </p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Server SMTP Host</p>
            <p class="text-lg font-extrabold text-slate-900 mt-1 truncate max-w-[180px]">{{ form.mail_host }}</p>
            <p class="text-xs text-slate-500 font-medium mt-1">Port {{ form.mail_port }} ({{ form.mail_encryption ? form.mail_encryption.toUpperCase() : 'NO ENCRYPT' }})</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
          </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Status OTP via Email</p>
            <p class="text-lg font-extrabold text-slate-900 mt-1">
              {{ form.enable_email_otp === '1' ? 'Aktif' : 'Non-Aktif' }}
            </p>
            <p class="text-xs font-medium mt-1" :class="form.enable_email_otp === '1' ? 'text-blue-600' : 'text-slate-400'">
              {{ form.enable_email_otp === '1' ? 'Otomatis Kirim ke Mail' : 'Hanya Kirim WA' }}
            </p>
          </div>
          <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="form.enable_email_otp === '1' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-2 border-b border-slate-200">
        <button 
          @click="activeTab = 'config'" 
          type="button" 
          class="px-5 py-3 text-sm font-bold border-b-2 transition cursor-pointer"
          :class="activeTab === 'config' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Konfigurasi Mail Server
        </button>
        <button 
          @click="activeTab = 'test'" 
          type="button" 
          class="px-5 py-3 text-sm font-bold border-b-2 transition cursor-pointer"
          :class="activeTab === 'test' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Uji Coba Kirim Email
        </button>
        <button 
          @click="activeTab = 'guide'" 
          type="button" 
          class="px-5 py-3 text-sm font-bold border-b-2 transition cursor-pointer"
          :class="activeTab === 'guide' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
        >
          Panduan Gmail SMTP
        </button>
      </div>

      <!-- TAB 1: KONFIGURASI FORM -->
      <div v-if="activeTab === 'config'" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <form @submit.prevent="saveConfig" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Mail Driver (Mailer)</label>
              <select v-model="form.mail_mailer" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="smtp">SMTP (Gmail / Webmail / Custom Server)</option>
                <option value="log">Log Only (Simpan ke file log laravel.log)</option>
              </select>
              <p class="text-xs text-slate-400 mt-1">Pilih 'SMTP' untuk pengiriman email nyata.</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Status OTP via Email</label>
              <select v-model="form.enable_email_otp" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="1">Aktifkan Pengiriman OTP via Email</option>
                <option value="0">Nonaktifkan (Hanya WhatsApp)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">SMTP Host</label>
              <input v-model="form.mail_host" type="text" placeholder="smtp.gmail.com" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">SMTP Port & Enkripsi</label>
              <div class="grid grid-cols-2 gap-3">
                <input v-model="form.mail_port" type="text" placeholder="587" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                <select v-model="form.mail_encryption" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                  <option value="tls">TLS (Port 587 - Gmail)</option>
                  <option value="ssl">SSL (Port 465)</option>
                  <option value="none">Tanpa Enkripsi (Port 25)</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">SMTP Username / Email Pengirim</label>
              <input v-model="form.mail_username" type="email" placeholder="email_anda@gmail.com" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">SMTP Password / App Password 16-Digit</label>
              <div class="relative">
                <input 
                  v-model="form.mail_password" 
                  :type="showPassword ? 'text' : 'password'" 
                  placeholder="xxxx xxxx xxxx xxxx" 
                  class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 pr-10" 
                />
                <button 
                  @click="showPassword = !showPassword" 
                  type="button" 
                  class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-semibold cursor-pointer"
                >
                  {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                </button>
              </div>
              <p class="text-xs text-slate-400 mt-1">Gunakan 'App Password' 16 digit jika menggunakan Gmail.</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Alamat Email Pengirim (From Address)</label>
              <input v-model="form.mail_from_address" type="email" placeholder="no-reply@sisfoperskc.my.id" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nama Pengirim (From Name)</label>
              <input v-model="form.mail_from_name" type="text" placeholder="Sisfoperskc" class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

          </div>

          <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-blue-500/20 disabled:opacity-50 cursor-pointer flex items-center gap-2"
            >
              <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
              <span>Simpan Konfigurasi Mail Gateway</span>
            </button>
          </div>
        </form>
      </div>

      <!-- TAB 2: TEST EMAIL SENDER -->
      <div v-if="activeTab === 'test'" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div>
          <h3 class="text-base font-extrabold text-slate-900">Uji Coba Pengiriman Email</h3>
          <p class="text-xs text-slate-500 mt-1">
            Kirim email tes simulasi OTP langsung ke alamat email penerima untuk memverifikasi koneksi SMTP.
          </p>
        </div>

        <form @submit.prevent="sendTestEmail" class="space-y-4 max-w-lg">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Alamat Email Penerima Uji Coba</label>
            <input 
              v-model="testForm.recipient_email" 
              type="email" 
              placeholder="penerima@gmail.com" 
              required
              class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500" 
            />
          </div>

          <button 
            type="submit" 
            :disabled="testForm.processing"
            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-emerald-500/20 disabled:opacity-50 cursor-pointer flex items-center gap-2"
          >
            <svg v-if="testForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <span>Kirim Email Uji Coba Now</span>
          </button>
        </form>
      </div>

      <!-- TAB 3: PANDUAN GMAIL SMTP -->
      <div v-if="activeTab === 'guide'" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4 text-sm text-slate-700">
        <h3 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">Panduan Singkat Konfirmasi Gmail SMTP</h3>
        
        <ol class="list-decimal list-inside space-y-3 font-medium">
          <li>Buka Akun Google Anda di <a href="https://myaccount.google.com" target="_blank" class="text-blue-600 underline font-bold">myaccount.google.com</a>.</li>
          <li>Masuk ke menu <strong>Keamanan (Security)</strong> dan pastikan <strong>Verifikasi 2 Langkah (2-Step Verification)</strong> telah AKTIF.</li>
          <li>Cari menu <strong>Sandi Aplikasi (App Passwords)</strong> di kolom pencarian Akun Google.</li>
          <li>Buat sandi baru dengan nama aplikasi: <code>Sisfoperskc</code>.</li>
          <li>Salin 16-digit kode unik yang diberikan Google dan tempelkan ke kolom <strong>SMTP Password</strong> pada tab Konfigurasi Mail Gateway.</li>
        </ol>
      </div>

    </div>
  </AuthenticatedLayout>
</template>
