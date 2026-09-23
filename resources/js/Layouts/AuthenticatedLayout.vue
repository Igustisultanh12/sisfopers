<template>
  <div class="min-h-screen bg-[#F8FAFC] flex text-[#334155] font-sans antialiased">
    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex w-72 bg-white border-r border-[#E2E8F0] flex-col justify-between shadow-sm shrink-0">
      <div>
        <div class="pt-8 pb-6 px-7 flex flex-col select-none">
          <div class="flex items-center gap-3">
            <img :src="settings.logo_tni || 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Tentara_Nasional_Indonesia_insignia.svg'" alt="Logo TNI" class="w-8 h-8 object-contain select-none" />
            <div class="text-2xl font-extrabold tracking-tight text-slate-900">
              SISFOPERS<span class="text-[#2563EB]">.</span>
            </div>
          </div>
          <span class="text-[9px] font-bold tracking-wider text-[#94A3B8] uppercase mt-2">
            Sistem Informasi Personel Komponen Cadangan
          </span>
        </div>
        
        <nav class="px-4 py-2 space-y-2">
          <template v-if="authProps?.user?.role?.name === 'admin'">
            <!-- Group 1: UTAMA -->
            <div class="space-y-1">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pt-1 pb-1">Utama</p>
              <Link :href="route('admin.dashboard')" :class="route().current('admin.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
              </Link>
            </div>

            <!-- Group 2: MANAJEMEN PERSONEL & VERIFIKASI -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Kelola Personel</p>
              
              <Link :href="route('admin.verification.index')" :class="route().current('admin.verification.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Konfirmasi Pendaftaran
              </Link>
              
              <Link :href="route('admin.personel.index')" :class="route().current('admin.personel.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Master Personel
              </Link>
              
              <Link :href="route('admin.skep.index')" :class="route().current('admin.skep.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Manajemen SKEP
              </Link>

              <Link :href="route('admin.pengkinian-data.index')" :class="route().current('admin.pengkinian-data.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Verifikasi Pengkinian Data</span>
              </Link>

              <Link :href="route('admin.education.verif-list')" :class="route().current('admin.education.verif-list') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Verifikasi Pendidikan
              </Link>
            </div>

            <!-- Group 3: OTORISASI & KEGIATAN -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Otorisasi & Broadcast</p>
              
              <Link :href="route('admin.otp.index')" :class="route().current('admin.otp.*') && !route().current('admin.otp.reset-password.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                OTP Manual
              </Link>

              <Link :href="route('admin.otp.reset-password.index')" :class="route().current('admin.otp.reset-password.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                OTP Reset Password
              </Link>
              
              <Link :href="route('admin.broadcast.index')" :class="route().current('admin.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Broadcast Kegiatan
              </Link>

              <Link :href="route('admin.form.index')" :class="route().current('admin.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Formulir & Rekrutmen
              </Link>

              <Link :href="route('admin.chat.index')" :class="route().current('admin.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Pusat Layanan Informasi
              </Link>

              <Link :href="route('admin.vicon.index')" :class="route().current('admin.vicon.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Vicon & Rapat Dinas
              </Link>

              <Link :href="route('admin.tickets.index')" :class="route().current('admin.tickets.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                Pengaduan & Tiket
              </Link>
            </div>

            <!-- Group 4: MONITORING & LAPORAN -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Laporan & Auditing</p>
              
              <Link :href="route('admin.monitoring.activity')" :class="route().current('admin.monitoring.activity') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Aktivitas Sistem (Audit Trail)
              </Link>

              <Link :href="route('admin.monitoring.login')" :class="route().current('admin.monitoring.login') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Log Login & Sesi
              </Link>

              <Link :href="route('admin.report.index')" :class="route().current('admin.report.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Laporan Pelaporan
              </Link>
            </div>

            <!-- Group 5: PENGATURAN SISTEM -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Sistem & Konfigurasi</p>
              
              <Link :href="route('admin.setting.index')" :class="route().current('admin.setting.index') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                Pengaturan Sistem
              </Link>

              <Link :href="route('admin.pju.index')" :class="route().current('admin.pju.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Manajemen Akun PJU
              </Link>

              <Link :href="route('admin.mail.index')" :class="route().current('admin.mail.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Mail Gateway
              </Link>
            </div>

            <!-- Group 6: DATA MANDIRI SAYA -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Data Mandiri Saya</p>
              
              <Link :href="route('personel.pengkinian-data.index')" :class="route().current('personel.pengkinian-data.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Pengkinian Data</span>
              </Link>

              <Link :href="route('personel.education.index')" :class="route().current('personel.education.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Pendidikan Saya
              </Link>

              <Link :href="route('personel.job.index')" :class="route().current('personel.job.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Pekerjaan Saya
              </Link>

              <Link :href="route('personel.broadcast.index')" :class="route().current('personel.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Broadcast & Kegiatan
              </Link>
            </div>
          </template>


          <template v-else-if="['ka_bacadnas','ses_bacadnas','kapus_komcad','pembina_matra','pembina_kodam','pembina_kodaeral','pembina_kodau','pembina_kodim','pembina_lanal','pembina_lanud'].includes(authProps?.user?.role?.name)">
            <Link :href="route('pju.dashboard')" :class="route().current('pju.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
              Dashboard PJU
            </Link>
            <Link :href="route('pju.personel.index')" :class="route().current('pju.personel.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
              Master Personel Jajaran
            </Link>
            <Link :href="route('pju.report.index')" :class="route().current('pju.report.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              Laporan Pelaporan
            </Link>
            <Link :href="route('pju.broadcast.index')" :class="route().current('pju.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
              Broadcast Kegiatan
            </Link>
            <Link :href="route('pju.form.index')" :class="route().current('pju.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              Formulir & Rekrutmen
            </Link>
            <Link :href="route('pju.chat.index')" :class="route().current('pju.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
              Pusat Layanan Informasi
            </Link>
          </template>

          <template v-else-if="authProps?.user?.role?.name === 'komandan'">
            <Link :href="route('komandan.dashboard')" :class="route().current('komandan.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
              Dashboard Utama
            </Link>
            <Link :href="route('komandan.personel.index')" :class="route().current('komandan.personel.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
              Data Kekuatan Personel
            </Link>
            <Link :href="route('komandan.broadcast.index')" :class="route().current('komandan.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
              Monitoring Kehadiran
            </Link>
          </template>

          <template v-else-if="authProps?.user?.role?.name === 'personel'">
            <Link :href="route('personel.dashboard')" :class="route().current('personel.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
              Dashboard Saya
            </Link>
            <Link :href="route('personel.education.index')" :class="route().current('personel.education.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
              Riwayat Pendidikan
            </Link>
            <Link :href="route('personel.job.index')" :class="route().current('personel.job.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              Riwayat Pekerjaan
            </Link>
            <Link :href="route('personel.broadcast.index')" :class="route().current('personel.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
              Tugas & Broadcast Kegiatan
            </Link>
            <Link :href="route('personel.form.index')" :class="route().current('personel.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              Formulir & Rekrutmen
            </Link>
            <Link :href="route('personel.chat.index')" :class="route().current('personel.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
              Pusat Layanan Informasi
            </Link>
            <Link :href="route('personel.vicon.index')" :class="route().current('personel.vicon.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
              Vicon Dinas
            </Link>
          </template>

          <template v-else-if="authProps?.user?.role?.name === 'kordinator_angkatan' || authProps?.user?.role?.name === 'kordinator_matra'">
            <div class="space-y-1">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Utama</p>
              <Link :href="route('kordinator.dashboard')" :class="route().current('kordinator.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard Pembinaan
              </Link>
            </div>

            <!-- Group 2: VERIFIKASI & KELOLA JAJARAN -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Verifikasi & Kelola Jajaran</p>
              
              <Link :href="route('admin.personel.index')" :class="route().current('admin.personel.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Master Personel Jajaran
              </Link>

              <Link :href="route('admin.verification.index')" :class="route().current('admin.verification.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Konfirmasi Pendaftaran
              </Link>

              <Link :href="route('admin.pengkinian-data.index')" :class="route().current('admin.pengkinian-data.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Verifikasi Pengkinian Data
              </Link>

              <Link :href="route('admin.education.verif-list')" :class="route().current('admin.education.verif-list') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                Verifikasi Pendidikan
              </Link>
            </div>

            <!-- Group 3: BROADCAST & LAPORAN -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Broadcast & Pelaporan</p>

              <Link :href="route('kordinator.broadcast.index')" :class="route().current('kordinator.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                {{ authProps?.user?.role?.name === 'kordinator_matra' ? 'Broadcast Matra' : 'Broadcast Angkatan' }}
              </Link>

              <Link :href="route('kordinator.form.index')" :class="route().current('kordinator.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Formulir & Rekrutmen
              </Link>

              <Link :href="route('kordinator.chat.index')" :class="route().current('kordinator.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Pusat Layanan Informasi
              </Link>

              <Link :href="route('admin.report.index')" :class="route().current('admin.report.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Laporan Pelaporan
              </Link>

              <Link :href="route('admin.tickets.index')" :class="route().current('admin.tickets.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                Pengaduan & Tiket Jajaran
              </Link>
            </div>
            
            <!-- Group 4: DATA MANDIRI SAYA -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Data Mandiri Saya</p>
              <Link :href="route('personel.education.index')" :class="route().current('personel.education.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Pendidikan Saya
              </Link>
              <Link :href="route('personel.job.index')" :class="route().current('personel.job.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Pekerjaan Saya
              </Link>
              <Link :href="route('personel.broadcast.index')" :class="route().current('personel.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Kegiatan & Absensi Saya
              </Link>
            </div>
          </template>
        </nav>
      </div>

      <div class="p-5 border-t border-[#E2E8F0] bg-slate-50/50">
        <Link 
          :href="route('profile.edit')" 
          :class="route().current('profile.edit') ? 'bg-[#2563EB]/5 text-[#2563EB]' : 'hover:bg-slate-100/80'"
          class="flex items-center gap-3.5 mb-4 p-2 rounded-xl transition duration-150 group cursor-pointer"
        >
          <div class="w-10 h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center font-bold text-xs text-[#2563EB] uppercase select-none group-hover:bg-[#2563EB] group-hover:text-white transition duration-150">
            {{ authProps?.user?.username ? authProps.user.username.substring(0, 2).toUpperCase() : 'US' }}
          </div>
          <div class="text-xs truncate flex-1" v-if="authProps?.user">
            <p class="font-bold text-slate-800 truncate group-hover:text-[#2563EB] transition duration-150">
              {{ authProps.user.username }}
            </p>
            <p class="text-slate-400 truncate text-[11px] mt-0.5">
              {{ authProps.user.email }}
            </p>
          </div>
        </Link>
        
        <button @click="logout" class="w-full py-2.5 px-3 text-center text-xs text-[#EF4444] hover:bg-red-50 rounded-xl font-bold border border-transparent hover:border-red-100/50 transition duration-150 cursor-pointer">
          Keluar Sistem
        </button>
      </div>
    </aside>

    <!-- Mobile Sidebar Drawer (Slide-out menu) -->
    <div v-if="isSidebarOpen" class="fixed inset-0 z-50 flex lg:hidden">
      <!-- Backdrop overlay -->
      <div @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity duration-300"></div>
      
      <!-- Drawer Content -->
      <aside class="relative w-72 max-w-xs bg-white h-full flex flex-col justify-between shadow-2xl z-50 transition-transform duration-300 ease-out transform translate-x-0">
        <div>
          <div class="pt-6 pb-4 px-6 flex items-center justify-between border-b border-[#E2E8F0]">
            <div class="flex items-center gap-2">
              <img :src="settings.logo_tni || 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Tentara_Nasional_Indonesia_insignia.svg'" alt="Logo TNI" class="w-6 h-6 object-contain select-none" />
              <div class="text-xl font-extrabold tracking-tight text-slate-900">
                SISFOPERS<span class="text-[#2563EB]">.</span>
              </div>
            </div>
            <button @click="isSidebarOpen = false" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600 transition cursor-pointer">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <nav class="px-4 py-4 space-y-2">
            <template v-if="authProps?.user?.role?.name === 'admin'">
              <!-- Group 1: UTAMA -->
              <div class="space-y-1">
                <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pt-1 pb-1">Utama</p>
                <Link :href="route('admin.dashboard')" @click="isSidebarOpen = false" :class="route().current('admin.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                  Dashboard
                </Link>
              </div>

              <!-- Group 2: MANAJEMEN PERSONEL & VERIFIKASI -->
              <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
                <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Kelola Personel</p>
                
                <Link :href="route('admin.verification.index')" @click="isSidebarOpen = false" :class="route().current('admin.verification.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  Konfirmasi Pendaftaran
                </Link>
                
                <Link :href="route('admin.personel.index')" @click="isSidebarOpen = false" :class="route().current('admin.personel.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                  Master Personel
                </Link>
                
                <Link :href="route('admin.skep.index')" @click="isSidebarOpen = false" :class="route().current('admin.skep.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                  Manajemen SKEP
                </Link>

                <Link :href="route('admin.pengkinian-data.index')" @click="isSidebarOpen = false" :class="route().current('admin.pengkinian-data.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span>Verifikasi Pengkinian Data</span>
                </Link>

                <Link :href="route('admin.education.verif-list')" @click="isSidebarOpen = false" :class="route().current('admin.education.verif-list') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                  Verifikasi Pendidikan
                </Link>
              </div>

              <!-- Group 3: OTORISASI & KEGIATAN -->
              <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
                <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Otorisasi & Broadcast</p>
                
                <Link :href="route('admin.otp.index')" @click="isSidebarOpen = false" :class="route().current('admin.otp.*') && !route().current('admin.otp.reset-password.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                  OTP Manual
                </Link>

                <Link :href="route('admin.otp.reset-password.index')" @click="isSidebarOpen = false" :class="route().current('admin.otp.reset-password.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                  OTP Reset Password
                </Link>
                
                <Link :href="route('admin.broadcast.index')" @click="isSidebarOpen = false" :class="route().current('admin.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                  Broadcast Kegiatan
                </Link>

                <Link :href="route('admin.form.index')" @click="isSidebarOpen = false" :class="route().current('admin.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                  Formulir & Rekrutmen
                </Link>

                <Link :href="route('admin.chat.index')" @click="isSidebarOpen = false" :class="route().current('admin.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                  Pusat Layanan Informasi
                </Link>
              </div>

              <!-- Group 4: MONITORING & LAPORAN -->
              <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
                <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Laporan & Auditing</p>
                
                <Link :href="route('admin.monitoring.login')" @click="isSidebarOpen = false" :class="route().current('admin.monitoring.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                  Monitoring Log
                </Link>

                <Link :href="route('admin.report.index')" @click="isSidebarOpen = false" :class="route().current('admin.report.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                  Laporan Pelaporan
                </Link>
              </div>

              <!-- Group 5: PENGATURAN SISTEM -->
              <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
                <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Sistem & Konfigurasi</p>
                
                <Link :href="route('admin.setting.index')" @click="isSidebarOpen = false" :class="route().current('admin.setting.index') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                  Pengaturan Sistem
                </Link>

                <Link :href="route('admin.mail.index')" @click="isSidebarOpen = false" :class="route().current('admin.mail.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  Mail Gateway
                </Link>
              </div>

              <!-- Group 6: DATA MANDIRI SAYA -->
              <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
                <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Data Mandiri Saya</p>
                
                <Link :href="route('personel.pengkinian-data.index')" @click="isSidebarOpen = false" :class="route().current('personel.pengkinian-data.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  <span>Pengkinian Data</span>
                </Link>

                <Link :href="route('personel.education.index')" @click="isSidebarOpen = false" :class="route().current('personel.education.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                  Pendidikan Saya
                </Link>

                <Link :href="route('personel.job.index')" @click="isSidebarOpen = false" :class="route().current('personel.job.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  Pekerjaan Saya
                </Link>

                <Link :href="route('personel.broadcast.index')" @click="isSidebarOpen = false" :class="route().current('personel.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                  Broadcast & Kegiatan
                </Link>
              </div>
            </template>

            <template v-else-if="authProps?.user?.role?.name === 'komandan'">
              <Link :href="route('komandan.dashboard')" @click="isSidebarOpen = false" :class="route().current('komandan.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard Utama
              </Link>
              <Link :href="route('komandan.personel.index')" @click="isSidebarOpen = false" :class="route().current('komandan.personel.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Data Kekuatan Personel
              </Link>
              <Link :href="route('komandan.broadcast.index')" @click="isSidebarOpen = false" :class="route().current('komandan.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                Monitoring Kehadiran
              </Link>
            </template>

            <template v-else-if="authProps?.user?.role?.name === 'personel'">
              <Link :href="route('personel.dashboard')" @click="isSidebarOpen = false" :class="route().current('personel.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard Saya
              </Link>
              <Link :href="route('personel.education.index')" @click="isSidebarOpen = false" :class="route().current('personel.education.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Riwayat Pendidikan
              </Link>
              <Link :href="route('personel.job.index')" @click="isSidebarOpen = false" :class="route().current('personel.job.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Riwayat Pekerjaan
              </Link>
              <Link :href="route('personel.broadcast.index')" @click="isSidebarOpen = false" :class="route().current('personel.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Tugas & Broadcast Kegiatan
              </Link>
              <Link :href="route('personel.form.index')" @click="isSidebarOpen = false" :class="route().current('personel.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Formulir & Rekrutmen
              </Link>
              <Link :href="route('personel.tickets.index')" @click="isSidebarOpen = false" :class="route().current('personel.tickets.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                Open Tiket Pengaduan
              </Link>
              <Link :href="route('personel.chat.index')" @click="isSidebarOpen = false" :class="route().current('personel.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Pusat Layanan Informasi
              </Link>
              <Link :href="route('personel.vicon.index')" @click="isSidebarOpen = false" :class="route().current('personel.vicon.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Vicon Dinas
              </Link>
            </template>

            <template v-else-if="authProps?.user?.role?.name === 'pju'">
              <Link :href="route('pju.broadcast.index')" @click="isSidebarOpen = false" :class="route().current('pju.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Broadcast Kegiatan
              </Link>
              <Link :href="route('pju.form.index')" @click="isSidebarOpen = false" :class="route().current('pju.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Formulir & Rekrutmen
              </Link>
              <Link :href="route('pju.chat.index')" @click="isSidebarOpen = false" :class="route().current('pju.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Pusat Layanan Informasi
              </Link>
            </template>

            <template v-else-if="authProps?.user?.role?.name === 'kordinator_angkatan' || authProps?.user?.role?.name === 'kordinator_matra'">
              <Link :href="route('kordinator.dashboard')" @click="isSidebarOpen = false" :class="route().current('kordinator.dashboard') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard Pembinaan
              </Link>
              <Link v-if="authProps?.user?.role?.name === 'kordinator_matra' || authProps?.user?.role?.name === 'kordinator_angkatan'" @click="isSidebarOpen = false" :href="route('kordinator.broadcast.index')" :class="route().current('kordinator.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                {{ authProps?.user?.role?.name === 'kordinator_matra' ? 'Broadcast Matra' : 'Broadcast Angkatan' }}
              </Link>
              <Link :href="route('kordinator.form.index')" @click="isSidebarOpen = false" :class="route().current('kordinator.form.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Formulir & Rekrutmen
              </Link>
              <Link :href="route('kordinator.chat.index')" @click="isSidebarOpen = false" :class="route().current('kordinator.chat.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                Pusat Layanan Informasi
              </Link>
              
              <!-- Tautan Akun Personel untuk Koordinator Mandiri (Mobile) -->
              <div class="border-t border-slate-100/50 my-2 pt-2">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest px-5 mb-2">Data Mandiri Saya</p>
                <Link :href="route('personel.education.index')" @click="isSidebarOpen = false" :class="route().current('personel.education.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                  Pendidikan Saya
                </Link>
                <Link :href="route('personel.job.index')" @click="isSidebarOpen = false" :class="route().current('personel.job.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  Pekerjaan Saya
                </Link>
                <Link :href="route('personel.broadcast.index')" @click="isSidebarOpen = false" :class="route().current('personel.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                  Kegiatan & Absensi Saya
                </Link>
              </div>
            </template>
          </nav>
        </div>

        <div class="p-5 border-t border-[#E2E8F0] bg-slate-50/50">
          <Link 
            :href="route('profile.edit')" 
            @click="isSidebarOpen = false"
            :class="route().current('profile.edit') ? 'bg-[#2563EB]/5 text-[#2563EB]' : 'hover:bg-slate-100/80'"
            class="flex items-center gap-3.5 mb-4 p-2 rounded-xl transition duration-150 group cursor-pointer"
          >
            <div class="w-10 h-10 rounded-xl bg-[#2563EB]/10 flex items-center justify-center font-bold text-xs text-[#2563EB] uppercase select-none group-hover:bg-[#2563EB] group-hover:text-white transition duration-150">
              {{ authProps?.user?.username ? authProps.user.username.substring(0, 2).toUpperCase() : 'US' }}
            </div>
            <div class="text-xs truncate flex-1" v-if="authProps?.user">
              <p class="font-bold text-slate-800 truncate group-hover:text-[#2563EB] transition duration-150">
                {{ authProps.user.username }}
              </p>
              <p class="text-slate-400 truncate text-[11px] mt-0.5">
                {{ authProps.user.email }}
              </p>
            </div>
          </Link>
          
          <button @click="logout" class="w-full py-2.5 px-3 text-center text-xs text-[#EF4444] hover:bg-red-50 rounded-xl font-bold border border-transparent hover:border-red-100/50 transition duration-150 cursor-pointer">
            Keluar Sistem
          </button>
        </div>
      </aside>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">
      <header class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-4 sm:px-8 shadow-sm">
        <div class="flex items-center gap-3">
          <button @click="isSidebarOpen = true" class="lg:hidden p-2 hover:bg-slate-100 rounded-xl text-slate-500 hover:text-slate-700 transition cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
          <h1 class="text-xs font-bold tracking-wider text-slate-400 uppercase select-none">
            <slot name="header-title"></slot>
          </h1>
        </div>
        
        <div class="flex items-center gap-4">
          <div class="relative">
            <button @click="toggleDropdown" class="relative p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition cursor-pointer outline-none focus:outline-none">
              <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#EF4444] ring-2 ring-white"></span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
            </button>

            <div v-if="dropdownOpen" class="absolute right-0 mt-2 w-80 bg-white border border-[#E2E8F0] rounded-2xl shadow-xl z-50 overflow-hidden text-xs">
              <div class="p-4 border-b border-[#E2E8F0] bg-slate-50/50 flex items-center justify-between font-bold">
                <span class="text-slate-700">Pemberitahuan</span>
                <button @click="markAllAsRead" class="text-[11px] text-[#2563EB] hover:underline cursor-pointer">Tandai Baca</button>
              </div>

              <div class="max-h-64 overflow-y-auto divide-y divide-[#E2E8F0]">
                <template v-if="allNotifications?.length > 0">
                  <a 
                    v-for="notif in allNotifications" 
                    :key="notif.id" 
                    :href="route('notifications.read', notif.id)" 
                    :class="!notif.read_at ? 'bg-blue-50/40 border-l-2 border-[#2563EB]' : ''"
                    class="block p-4 hover:bg-slate-50/60 transition group"
                  >
                    <div class="flex gap-3">
                      <div :class="getNotificationStyles(notif.data?.icon).bg" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 select-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" v-html="getNotificationStyles(notif.data?.icon).svg"></svg>
                      </div>
                      <div class="space-y-0.5 flex-1">
                        <div class="flex justify-between items-center gap-1">
                          <p class="font-bold text-slate-800 group-hover:text-[#2563EB] transition">{{ notif.data?.title || 'Instruksi Baru' }}</p>
                          <span v-if="!notif.read_at" class="w-1.5 h-1.5 bg-[#2563EB] rounded-full shrink-0"></span>
                        </div>
                        <p class="text-slate-500 leading-relaxed text-[11px] mt-0.5">{{ notif.data?.message }}</p>
                      </div>
                    </div>
                  </a>
                </template>
                <div v-else class="p-6 text-center text-slate-400 italic">
                  Tidak ada pemberitahuan saat ini.
                </div>
              </div>

              <Link :href="route('notifications.index')" @click="dropdownOpen = false" class="block text-center font-bold text-[#2563EB] py-3 bg-slate-50 hover:bg-slate-100 transition border-t border-[#E2E8F0]">
                Lihat Semua Pemberitahuan &rarr;
              </Link>
            </div>
          </div>
        </div>
      </header>

      <main class="p-4 sm:p-8 flex-1 overflow-y-auto">
        <slot></slot>
      </main>
    </div>
  </div>

  <!-- GLOBAL MODAL SWEETALERT-STYLE: PEMUTAKHIRAN DATA KEWILAYAHAN (1X PENGISIAN) -->
  <div
    v-if="needKewilayahanUpdate && !showSuccessModal"
    class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto"
  >
    <div class="bg-white border border-slate-200 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl space-y-6 my-auto text-left relative animate-float-card">
      <!-- Header Banner -->
      <div class="text-center space-y-2">
        <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 mx-auto flex items-center justify-center p-2 shadow-sm animate-float-slow">
          <img
            :src="page.props.settings?.logo_tni || 'https://upload.wikimedia.org/wikipedia/commons/b/b5/Tentara_Nasional_Indonesia_insignia.svg'"
            alt="Logo TNI"
            class="w-full h-full object-contain drop-shadow-sm"
          />
        </div>
        <span class="text-[10px] font-extrabold uppercase tracking-widest bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-lg inline-block">
          PEMUTAKHIRAN DATA KEWILAYAHAN (1X PENGISIAN)
        </span>
        <h2 class="text-lg font-black text-slate-800 tracking-tight">
          Komando Utama & Satuan Kewilayahan
        </h2>
        <p class="text-xs text-slate-500 leading-relaxed">
          Yth. <strong class="text-slate-800">{{ authProps?.personel?.full_name || authProps?.user?.username }}</strong>, mohon melengkapi data Komando Utama (Kodam / Kodaeral / Kodau) dan Satuan Kewilayahan (Kodim / Lanal / Lanud) Anda.
        </p>
      </div>

      <form @submit.prevent="submitKewilayahan" class="space-y-4 text-xs">
        <div>
          <label class="block font-bold text-slate-700 mb-1">Matra Dinas</label>
          <input
            type="text"
            :value="'TNI ' + (authProps?.personel?.matra || 'AD')"
            disabled
            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl font-extrabold text-blue-700 text-xs cursor-not-allowed"
          />
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1">
            {{ currentUnitConfig.label_kotama }} <span class="text-red-500">*</span>
          </label>
          <select
            v-model="kewilayahanForm.kotama"
            @change="onKotamaChange"
            required
            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] bg-white font-semibold text-xs"
          >
            <option value="" disabled>-- Pilih {{ currentUnitConfig.label_kotama }} --</option>
            <option v-for="(satuans, kotName) in currentUnitConfig.kotama" :key="kotName" :value="kotName">
              {{ kotName }}
            </option>
          </select>
        </div>

        <div>
          <label class="block font-bold text-slate-700 mb-1">
            {{ currentUnitConfig.label_satuan }} <span class="text-red-500">*</span>
          </label>
          <select
            v-model="kewilayahanForm.satuan_kewilayahan"
            :disabled="!kewilayahanForm.kotama"
            required
            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl outline-none focus:border-[#2563EB] bg-white font-semibold text-xs disabled:bg-slate-100 disabled:cursor-not-allowed"
          >
            <option value="" disabled>-- Pilih {{ currentUnitConfig.label_satuan }} --</option>
            <option v-for="satName in currentSatuanOptions" :key="satName" :value="satName">
              {{ satName }}
            </option>
          </select>
        </div>

        <div class="pt-3 border-t border-slate-100">
          <button
            type="submit"
            :disabled="kewilayahanForm.processing || !kewilayahanForm.satuan_kewilayahan"
            class="w-full py-3 bg-[#2563EB] hover:bg-blue-600 disabled:opacity-60 text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer flex items-center justify-center gap-2"
          >
            <svg v-if="kewilayahanForm.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ kewilayahanForm.processing ? 'Menyimpan Data Kewilayahan...' : 'Simpan Data Kewilayahan' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL SUKSES PENGISIAN DATA KEWILAYAHAN -->
  <div
    v-if="showSuccessModal"
    class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4 overflow-y-auto"
  >
    <div class="bg-white border border-slate-200 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl space-y-6 my-auto text-center relative animate-float-card">
      <div class="w-16 h-16 rounded-full bg-blue-50 text-[#2563EB] border border-blue-100 mx-auto flex items-center justify-center shadow-xs">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
      </div>

      <div class="space-y-2">
        <h3 class="text-lg font-black text-slate-800 tracking-tight">Data Berhasil Disimpan</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
          Yth. <strong class="text-slate-800">{{ authProps?.personel?.full_name || authProps?.user?.username }}</strong>, data Komando Utama ({{ kewilayahanForm.kotama }}) dan Satuan Kewilayahan ({{ kewilayahanForm.satuan_kewilayahan }}) Anda telah berhasil disimpan ke dalam sistem.
        </p>
      </div>

      <button
        type="button"
        @click="closeSuccessModal"
        class="w-full py-3 bg-[#2563EB] hover:bg-blue-600 text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer uppercase tracking-wider"
      >
        OK
      </button>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router, Link, usePage, useForm } from '@inertiajs/vue3';
import { playNotificationSound } from '@/Utils/sound';

const page = usePage();
const dropdownOpen = ref(false);
const isSidebarOpen = ref(false);

let notificationInterval = null;

onMounted(() => {
  notificationInterval = setInterval(() => {
    // Lewati reload asinkron jika sedang berada di rute live-chat atau vicon agar sesi WebRTC tetap utuh tanpa gangguan
    if (typeof window !== 'undefined' && (window.location.pathname.includes('/live-chat') || window.location.pathname.includes('/vicon'))) {
      return;
    }
    router.reload({
      only: ['auth'],
      preserveScroll: true,
      preserveState: true,
    });
  }, 15000);
});

onUnmounted(() => {
  if (notificationInterval) {
    clearInterval(notificationInterval);
  }
});

const militaryUnitsData = {
  AD: {
    label_kotama: 'Kodam (Komando Daerah Militer)',
    label_satuan: 'Kodim (Komando Distrik Militer)',
    kotama: {
      'Kodam I/Bukit Barisan': [
        'Kodam I/Bukit Barisan', 'Kodim 0201/Medan', 'Kodim 0202/Tapanuli Utara', 'Kodim 0203/Langkat', 'Kodim 0204/Deli Serdang',
        'Kodim 0205/Tanah Karo', 'Kodim 0206/Dairi', 'Kodim 0207/Simalungun', 'Kodim 0208/Asahan',
        'Kodim 0209/Labuhanbatu', 'Kodim 0210/Tapanuli Utara', 'Kodim 0211/Tapanuli Tengah', 'Kodim 0212/Tapanuli Selatan',
        'Kodim 0213/Nias', 'Kodim 0304/Agam', 'Kodim 0305/Pasaman', 'Kodim 0306/50 Kota', 'Kodim 0307/Tanah Datar',
        'Kodim 0308/Pariaman', 'Kodim 0309/Solok', 'Kodim 0311/Pesisir Selatan', 'Kodim 0319/Mentawai',
        'Kodim 0301/Pekanbaru', 'Kodim 0302/Inhu', 'Kodim 0303/Bengkalis', 'Kodim 0313/Kampar',
        'Kodim 0314/Inhil', 'Kodim 0320/Dumai', 'Kodim 0321/Rokan Hilir', 'Kodim 0315/Tanjung Pinang',
        'Kodim 0316/Batam', 'Kodim 0317/Tanjung Balai Karimun', 'Kodim 0318/Natuna'
      ],
      'Kodam II/Sriwijaya': [
        'Kodam II/Sriwijaya', 'Kodim 0401/Muba', 'Kodim 0402/OKI', 'Kodim 0403/OKU', 'Kodim 0404/Muara Enim', 'Kodim 0405/Lahat',
        'Kodim 0406/Lubuklinggau', 'Kodim 0418/Palembang', 'Kodim 0430/Banyuasin', 'Kodim 0407/Kota Bengkulu',
        'Kodim 0408/Bengkulu Selatan', 'Kodim 0409/Rejang Lebong', 'Kodim 0423/Bengkulu Utara', 'Kodim 0425/Seluma',
        'Kodim 0428/Mukomuko', 'Kodim 0415/Jambi', 'Kodim 0417/Kerinci', 'Kodim 0419/Tanjab', 'Kodim 0420/Sarko',
        'Kodim 0411/Kota Metro', 'Kodim 0412/Lampung Utara', 'Kodim 0421/Lampung Selatan', 'Kodim 0422/Lampung Barat',
        'Kodim 0424/Tanggamus', 'Kodim 0426/Tulang Bawang', 'Kodim 0427/Way Kanan', 'Kodim 0429/Lampung Timur',
        'Kodim 0413/Bangka', 'Kodim 0414/Belitung', 'Kodim 0431/Bangka Barat'
      ],
      'Kodam III/Siliwangi': [
        'Kodam III/Siliwangi', 'Kodim 0601/Pandeglang', 'Kodim 0602/Serang', 'Kodim 0603/Lebak', 'Kodim 0623/Cilegon',
        'Kodim 0604/Karawang', 'Kodim 0605/Subang', 'Kodim 0606/Kota Bogor', 'Kodim 0607/Kota Sukabumi',
        'Kodim 0608/Cianjur', 'Kodim 0609/Cimahi', 'Kodim 0610/Sumedang', 'Kodim 0611/Garut',
        'Kodim 0612/Tasikmalaya', 'Kodim 0613/Ciamis', 'Kodim 0614/Kota Cirebon', 'Kodim 0615/Kuningan',
        'Kodim 0616/Indramayu', 'Kodim 0617/Majalengka', 'Kodim 0618/Kota Bandung', 'Kodim 0620/Kabupaten Cirebon',
        'Kodim 0621/Kabupaten Bogor', 'Kodim 0622/Kabupaten Sukabumi', 'Kodim 0624/Kabupaten Bandung', 'Kodim 0625/Pangandaran'
      ],
      'Kodam IV/Diponegoro': [
        'Kodam IV/Diponegoro', 'Kodim 0701/Banyumas', 'Kodim 0702/Purbalingga', 'Kodim 0703/Cilacap', 'Kodim 0704/Banjarnegara',
        'Kodim 0705/Magelang', 'Kodim 0706/Temanggung', 'Kodim 0707/Wonosobo', 'Kodim 0708/Purworejo',
        'Kodim 0709/Kebumen', 'Kodim 0710/Pekalongan', 'Kodim 0711/Pemalang', 'Kodim 0712/Tegal',
        'Kodim 0713/Brebes', 'Kodim 0714/Salatiga', 'Kodim 0715/Kendal', 'Kodim 0716/Demak',
        'Kodim 0717/Grobogan', 'Kodim 0718/Pati', 'Kodim 0719/Jepara', 'Kodim 0720/Rembang',
        'Kodim 0721/Blora', 'Kodim 0722/Kudus', 'Kodim 0723/Klaten', 'Kodim 0724/Boyolali',
        'Kodim 0725/Sragen', 'Kodim 0726/Sukoharjo', 'Kodim 0727/Karanganyar', 'Kodim 0728/Wonogiri',
        'Kodim 0733/Kota Semarang', 'Kodim 0734/Kota Yogyakarta', 'Kodim 0730/Gunungkidul', 'Kodim 0731/Kulon Progo', 'Kodim 0732/Sleman'
      ],
      'Kodam V/Brawijaya': [
        'Kodam V/Brawijaya', 'Kodim 0801/Pacitan', 'Kodim 0802/Ponorogo', 'Kodim 0803/Madiun', 'Kodim 0804/Magetan',
        'Kodim 0805/Ngawi', 'Kodim 0806/Trenggalek', 'Kodim 0807/Tulungagung', 'Kodim 0808/Blitar',
        'Kodim 0809/Kediri', 'Kodim 0810/Nganjuk', 'Kodim 0811/Tuban', 'Kodim 0812/Lamongan',
        'Kodim 0813/Bojonegoro', 'Kodim 0814/Jombang', 'Kodim 0815/Mojokerto', 'Kodim 0816/Sidoarjo',
        'Kodim 0817/Gresik', 'Kodim 0818/Kabupaten Malang', 'Kodim 0819/Pasuruan', 'Kodim 0820/Probolinggo',
        'Kodim 0821/Lumajang', 'Kodim 0822/Bondowoso', 'Kodim 0823/Situbondo', 'Kodim 0824/Jember',
        'Kodim 0825/Banyuwangi', 'Kodim 0826/Pamekasan', 'Kodim 0827/Sumenep', 'Kodim 0828/Sampang',
        'Kodim 0829/Bangkalan', 'Kodim 0830/Surabaya Utara', 'Kodim 0831/Surabaya Timur', 'Kodim 0832/Surabaya Selatan', 'Kodim 0833/Kota Malang'
      ],
      'Kodam VI/Mulawarman': [
        'Kodam VI/Mulawarman', 'Kodim 0901/Samarinda', 'Kodim 0902/Berau', 'Kodim 0904/Paser', 'Kodim 0905/Balikpapan',
        'Kodim 0906/Kutai Kartanegara', 'Kodim 0907/Tarakan', 'Kodim 0908/Bontang', 'Kodim 0909/Kutai Timur',
        'Kodim 0911/Nunukan', 'Kodim 0912/Kutai Barat', 'Kodim 0913/Penajam Paser Utara', 'Kodim 0914/Tana Tidung',
        'Kodim 1001/Hulu Sungai Utara', 'Kodim 1002/Hulu Sungai Tengah', 'Kodim 1003/Hulu Sungai Selatan',
        'Kodim 1004/Kotabaru', 'Kodim 1005/Barito Kuala', 'Kodim 1006/Banjar', 'Kodim 1007/Banjarmasin',
        'Kodim 1008/Tabalong', 'Kodim 1009/Tanah Laut', 'Kodim 1010/Tapin', 'Kodim 1022/Tanah Bumbu'
      ],
      'Kodam IX/Udayana': [
        'Kodam IX/Udayana', 'Kodim 1609/Buleleng', 'Kodim 1611/Badung', 'Kodim 1612/Manggarai', 'Kodim 1606/Mataram',
        'Kodim 1607/Sumbawa', 'Kodim 1608/Bima', 'Kodim 1614/Dompu', 'Kodim 1615/Lombok Timur',
        'Kodim 1620/Lombok Tengah', 'Kodim 1601/Sumba Timur', 'Kodim 1602/Ende', 'Kodim 1603/Sikka',
        'Kodim 1604/Kupang', 'Kodim 1605/Belu', 'Kodim 1613/Sumba Barat', 'Kodim 1618/TTU',
        'Kodim 1621/TTS', 'Kodim 1622/Alor', 'Kodim 1624/Flores Timur', 'Kodim 1625/Ngada',
        'Kodim 1627/Rote Ndao', 'Kodim 1629/Sumba Barat Daya'
      ],
      'Kodam XII/Tanjungpura': [
        'Kodam XII/Tanjungpura', 'Kodim 1201/Mempawah', 'Kodim 1202/Singkawang', 'Kodim 1203/Ketapang', 'Kodim 1204/Sanggau',
        'Kodim 1205/Sintang', 'Kodim 1206/Putussibau', 'Kodim 1207/Pontianak', 'Kodim 1208/Sambas',
        'Kodim 1209/Bengkayang', 'Kodim 1011/Kuala Kapuas', 'Kodim 1012/Buntok', 'Kodim 1013/Muara Teweh',
        'Kodim 1014/Pangkalan Bun', 'Kodim 1015/Sampit', 'Kodim 1016/Palangka Raya', 'Kodim 1017/Lamandau', 'Kodim 1019/Katingan'
      ],
      'Kodam XIII/Merdeka': [
        'Kodam XIII/Merdeka', 'Kodim 1301/Sangihe', 'Kodim 1302/Minahasa', 'Kodim 1303/Bolaang Mongondow', 'Kodim 1309/Manado',
        'Kodim 1310/Bitung', 'Kodim 1312/Talaud', 'Kodim 1313/Pohuwato', 'Kodim 1314/Gorontalo Utara',
        'Kodim 1315/Kabupaten Gorontalo', 'Kodim 1304/Gorontalo', 'Kodim 1305/Buol Tolitoli', 'Kodim 1306/Kota Palu',
        'Kodim 1307/Poso', 'Kodim 1308/Luwuk Banggai', 'Kodim 1311/Morowali'
      ],
      'Kodam XIV/Hasanuddin': [
        'Kodam XIV/Hasanuddin', 'Kodim 1401/Majene', 'Kodim 1402/Polman', 'Kodim 1403/Palopo', 'Kodim 1404/Pinrang',
        'Kodim 1405/Parepare', 'Kodim 1406/Wajo', 'Kodim 1407/Bone', 'Kodim 1408/Makassar',
        'Kodim 1409/Gowa', 'Kodim 1410/Bantaeng', 'Kodim 1411/Bulukumba', 'Kodim 1412/Kolaka',
        'Kodim 1413/Buton', 'Kodim 1414/Tana Toraja', 'Kodim 1415/Selayar', 'Kodim 1416/Muna',
        'Kodim 1417/Kendari', 'Kodim 1418/Mamuju', 'Kodim 1419/Enrekang', 'Kodim 1420/Sidrap',
        'Kodim 1421/Pangkep', 'Kodim 1422/Maros', 'Kodim 1423/Soppeng', 'Kodim 1424/Sinjai',
        'Kodim 1425/Jeneponto', 'Kodim 1426/Takalar', 'Kodim 1427/Pasangkayu', 'Kodim 1428/Mamasa',
        'Kodim 1429/Buton Utara', 'Kodim 1430/Konawe Utara', 'Kodim 1431/Bombana'
      ],
      'Kodam XV/Pattimura': [
        'Kodam XV/Pattimura', 'Kodim 1501/Ternate', 'Kodim 1502/Masohi', 'Kodim 1503/Tual', 'Kodim 1504/Ambon',
        'Kodim 1505/Tidore', 'Kodim 1506/Namlea', 'Kodim 1507/Saumlaki', 'Kodim 1508/Tobelo',
        'Kodim 1509/Labuha', 'Kodim 1510/Sula', 'Kodim 1511/Pulau Moa', 'Kodim 1512/Weda',
        'Kodim 1513/Seram Bagian Barat', 'Kodim 1514/Morotai'
      ],
      'Kodam XVII/Cenderawasih': [
        'Kodam XVII/Cenderawasih', 'Kodim 1701/Jayapura', 'Kodim 1702/Jayawijaya', 'Kodim 1703/Deiyai', 'Kodim 1705/Nabire',
        'Kodim 1708/Biak Numfor', 'Kodim 1709/Yapen Waropen', 'Kodim 1710/Mimika', 'Kodim 1711/Boven Digoel',
        'Kodim 1712/Sarmi', 'Kodim 1714/Puncak Jaya', 'Kodim 1715/Yahukimo', 'Kodim 1716/Mamberamo Raya'
      ],
      'Kodam XVIII/Kasuari': [
        'Kodam XVIII/Kasuari', 'Kodim 1801/Manokwari', 'Kodim 1802/Sorong', 'Kodim 1803/Fakfak', 'Kodim 1804/Kaimana',
        'Kodim 1805/Raja Ampat', 'Kodim 1806/Teluk Bintuni', 'Kodim 1807/South Sorong', 'Kodim 1808/Manokwari Selatan',
        'Kodim 1809/Maybrat', 'Kodim 1810/Tambrauw', 'Kodim 1811/Teluk Wondama', 'Kodim 1812/Pegunungan Arfak'
      ],
      'Kodam Jaya': [
        'Kodam Jaya', 'Kodim 0501/Jakarta Pusat', 'Kodim 0502/Jakarta Utara', 'Kodim 0503/Jakarta Barat',
        'Kodim 0504/Jakarta Selatan', 'Kodim 0505/Jakarta Timur', 'Kodim 0506/Tangerang',
        'Kodim 0507/Bekasi', 'Kodim 0508/Depok', 'Kodim 0509/Kabupaten Bekasi', 'Kodim 0510/Tigaraksa'
      ],
      'Kodam Iskandar Muda': [
        'Kodam Iskandar Muda', 'Kodim 0101/Kota Banda Aceh', 'Kodim 0102/Pidie', 'Kodim 0103/Aceh Utara', 'Kodim 0104/Aceh Timur',
        'Kodim 0105/Aceh Barat', 'Kodim 0106/Aceh Tengah', 'Kodim 0107/Aceh Selatan', 'Kodim 0108/Aceh Tenggara',
        'Kodim 0109/Aceh Singkil', 'Kodim 0110/Aceh Barat Daya', 'Kodim 0111/Bireuen', 'Kodim 0112/Sabang',
        'Kodim 0113/Gayo Lues', 'Kodim 0114/Aceh Jaya', 'Kodim 0115/Simeulue', 'Kodim 0116/Nagan Raya',
        'Kodim 0117/Aceh Tamiang', 'Kodim 0118/Subulussalam', 'Kodim 0119/Bener Meriah'
      ]
    }
  },
  AL: {
    label_kotama: 'Kodaeral (Komando Daerah Angkatan Laut)',
    label_satuan: 'Lanal (Pangkalan TNI Angkatan Laut)',
    kotama: {
      'Kodaeral I (Belawan)': ['Kodaeral I (Belawan)', 'Lanal Sabang', 'Lanal Lhokseumawe', 'Lanal Tanjung Balai Asahan', 'Lanal Simeulue', 'Lanal Dumai', 'Lanal Bintan', 'Lanal Brandan'],
      'Kodaeral II (Padang)': ['Kodaeral II (Padang)', 'Lanal Sibolga', 'Lanal Nias', 'Lanal Bengkulu', 'Lanal Mentawai'],
      'Kodaeral III (Jakarta)': ['Kodaeral III (Jakarta)', 'Lanal Lampung', 'Lanal Palembang', 'Lanal Bangka Belitung', 'Lanal Cirebon', 'Lanal Bandung', 'Lanal Banten', 'Lanal Sukabumi', 'Lanal Pangandaran'],
      'Kodaeral IV (Batam)': ['Kodaeral IV (Batam)', 'Lanal Ranai', 'Lanal Tarempa', 'Lanal Dabo Singkep', 'Lanal Tanjung Balai Karimun', 'Lanal Batam'],
      'Kodaeral V (Surabaya)': ['Kodaeral V (Surabaya)', 'Lanal Tegal', 'Lanal Semarang', 'Lanal Yogyakarta', 'Lanal Cilacap', 'Lanal Malang', 'Lanal Banyuwangi', 'Lanal Denpasar', 'Lanal Batuporon', 'Lanal Pacitan'],
      'Kodaeral VI (Makassar)': ['Kodaeral VI (Makassar)', 'Lanal Mamuju', 'Lanal Palu', 'Lanal Kendari', 'Lanal Fajar'],
      'Kodaeral VII (Kupang)': ['Kodaeral VII (Kupang)', 'Lanal Mataram', 'Lanal Maumere', 'Lanal Rote', 'Lanal Waingapu', 'Lanal Labuan Bajo'],
      'Kodaeral VIII (Manado)': ['Kodaeral VIII (Manado)', 'Lanal Gorontalo', 'Lanal Tahuna', 'Lanal Melonguane', 'Lanal Tolitoli'],
      'Kodaeral IX (Ambon)': ['Kodaeral IX (Ambon)', 'Lanal Tual', 'Lanal Saumlaki', 'Lanal Aru', 'Lanal Bandanaira', 'Lanal Morotai', 'Lanal Ternate'],
      'Kodaeral X (Jayapura)': ['Kodaeral X (Jayapura)', 'Lanal Biak', 'Lanal Sarmi', 'Lanal Nabire'],
      'Kodaeral XI (Merauke)': ['Kodaeral XI (Merauke)', 'Lanal Timika', 'Lanal Arafuru', 'Lanal Agats', 'Lanal Merauke'],
      'Kodaeral XII (Pontianak)': ['Kodaeral XII (Pontianak)', 'Lanal Sambas', 'Lanal Ketapang', 'Lanal Kumai'],
      'Kodaeral XIII (Tarakan)': ['Kodaeral XIII (Tarakan)', 'Lanal Nunukan', 'Lanal Sangatta', 'Lanal Balikpapan', 'Lanal Kotabaru', 'Lanal Banjarmasin'],
      'Kodaeral XIV (Sorong)': ['Kodaeral XIV (Sorong)', 'Lanal Kaimana', 'Lanal Fakfak', 'Lanal Sorong']
    }
  },
  AU: {
    label_kotama: 'Kodau (Komando Daerah Angkatan Udara)',
    label_satuan: 'Lanud (Pangkalan TNI Angkatan Udara)',
    kotama: {
      'Kodau I (Koopsud I)': [
        'Kodau I (Koopsud I)', 'Lanud Halim Perdanakusuma (Jakarta)', 'Lanud Atang Sendjaja (Bogor)', 'Lanud Suryadarma (Subang)',
        'Lanud Husein Sastranegara (Bandung)', 'Lanud Roesmin Nurjadin (Pekanbaru)', 'Lanud Soewondo (Medan)',
        'Lanud Sultan Iskandar Muda (Banda Aceh)', 'Lanud Maimun Saleh (Sabang)', 'Lanud Sutan Sjahrir (Padang)',
        'Lanud Sri Mulyono Herlambang (Palembang)', 'Lanud H.AS Hanandjoeddin (Belitung)', 'Lanud Raden Sadjad (Natuna)',
        'Lanud Prince M. Bun Yamin (Lampung)', 'Lanud Sugiri Sukani (Majalengka)', 'Lanud Wiriadinata (Tasikmalaya)', 'Lanud Gading (Gunungkidul)'
      ],
      'Kodau II (Koopsud II)': [
        'Kodau II (Koopsud II)', 'Lanud Sultan Hasanuddin (Makassar)', 'Lanud Iswahjudi (Madiun)', 'Lanud Abdulrachman Saleh (Malang)',
        'Lanud Muljono (Surabaya)', 'Lanud Sam Ratulangi (Manado)', 'Lanud Zainuddin Abdul Madjid / Zam (Lombok)',
        'Lanud Syamsudin Noor (Banjarmasin)', 'Lanud Anang Busra (Tarakan)', 'Lanud I Gusti Ngurah Rai (Bali)',
        'Lanud Hadizuddin (Pontianak)', 'Lanud Iskandar (Pangkalan Bun)'
      ],
      'Kodau III (Koopsud III)': [
        'Kodau III (Koopsud III)', 'Lanud Silas Papare (Jayapura)', 'Lanud Manuhua (Biak)', 'Lanud Johannes Abraham Dimara (Merauke)',
        'Lanud Leo Wattimena (Morotai)', 'Lanud Pattimura (Ambon)', 'Lanud El Tari (Kupang)',
        'Lanud Yohanis Kapiyau (Timika)', 'Lanud Dumatubun (Langgur)', 'Lanud Ignatius Dewanto (Saumlaki)'
      ]
    }
  }
};

const needKewilayahanUpdate = computed(() => page.props.auth?.needKewilayahanUpdate || false);
const userMatra = computed(() => page.props.auth?.personel?.matra || 'AD');

const currentUnitConfig = computed(() => {
  return militaryUnitsData[userMatra.value] || militaryUnitsData['AD'];
});

const currentSatuanOptions = computed(() => {
  if (!kewilayahanForm.kotama) return [];
  return currentUnitConfig.value.kotama[kewilayahanForm.kotama] || [];
});

const showSuccessModal = ref(false);

const kewilayahanForm = useForm({
  kotama: '',
  satuan_kewilayahan: '',
});

function onKotamaChange() {
  kewilayahanForm.satuan_kewilayahan = '';
}

function submitKewilayahan() {
  kewilayahanForm.post(route('personel.kewilayahan.update'), {
    preserveScroll: true,
    onSuccess: () => {
      showSuccessModal.value = true;
    }
  });
}

function closeSuccessModal() {
  showSuccessModal.value = false;
  router.reload({ only: ['auth'] });
}

const getNotificationStyles = (iconKey) => {
  const map = {
    'broadcast': {
      bg: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.685-.196-1.353-.49-1.985-.875L6 16v-4.836A7.49 7.49 0 014.25 6h15.5c-.383 1.937-1.383 3.654-2.75 4.836V16l-2.355-1.035a7.9 7.9 0 01-1.985.875M10.34 15.84A8.001 8.001 0 0012 16c.56 0 1.1-.1 1.66-.27M10.34 15.84v1.41c0 1.105.895 2 2 2s2-.895 2-2v-1.41" />`
    },
    'password': {
      bg: 'bg-amber-50 text-amber-600 border border-amber-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H3.75v-2.25A3.375 3.375 0 007.125 16.5h1.5v-1.5h1.5v-1.5h1.5c.22 0 .44-.089.6-.25l2.777-2.778c.404-.404.527-.9.43-1.563A6 6 0 0121.75 8.25z" />`
    },
    'profile': {
      bg: 'bg-blue-50 text-blue-600 border border-blue-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />`
    },
    'job': {
      bg: 'bg-indigo-50 text-indigo-600 border border-indigo-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.214.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.426.293-.68.374a23.953 23.953 0 01-8.32 0c-.254-.08-.486-.209-.68-.375m14.483-3.13c-.08-.398-.3-.72-.64-.902a24.58 24.58 0 01-13.18 0c-.34.182-.56.504-.64.902M15 6.75V4.5a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 009 4.5v2.25m6 0a48.667 48.667 0 00-6 0m6 0v1.125a2.25 2.25 0 01-2.25 2.25h-1.5a2.25 2.25 0 01-2.25-2.25V6.75" />`
    },
    'mfa': {
      bg: 'bg-red-50 text-red-600 border border-red-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />`
    },
    'success': {
      bg: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
      svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />`
    }
  };
  return map[iconKey] || {
    bg: 'bg-slate-50 text-slate-500 border border-slate-100',
    svg: `<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />`
  };
};

const authProps = computed(() => page.props.auth);
const unreadCount = computed(() => page.props.auth?.unread_notifications_count || 0);
const allNotifications = computed(() => page.props.auth?.all_notifications || []);
const settings = computed(() => page.props.settings || {});

watch(unreadCount, (newVal, oldVal) => {
  if (typeof oldVal !== 'undefined' && newVal > oldVal) {
    playNotificationSound();
  }
});

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const markAllAsRead = () => {
  router.post(route('notifications.read-all'), {}, {
    preserveScroll: true,
    onSuccess: () => {
      dropdownOpen.value = false;
    }
  });
};

const logout = () => {
  router.post(route('logout'));
};
</script>

<style scoped>
.animate-float-slow {
  animation: float-logo 5s ease-in-out infinite;
}

.animate-float-card {
  animation: float-card 6s ease-in-out infinite;
}

@keyframes float-logo {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

@keyframes float-card {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-6px);
  }
}
</style>