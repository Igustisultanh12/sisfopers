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
            </div>

            <!-- Group 4: MONITORING & LAPORAN -->
            <div class="space-y-1 pt-3 border-t border-slate-100/60 mt-3">
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Laporan & Auditing</p>
              
              <Link :href="route('admin.monitoring.login')" :class="route().current('admin.monitoring.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold shadow-sm shadow-blue-500/[0.02]' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="group flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80 group-hover:opacity-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                Monitoring Log
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
              <p class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest px-5 pb-1">Verifikasi Jajaran</p>
              
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

              <Link :href="route('admin.report.index')" :class="route().current('admin.report.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Laporan Pelaporan
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
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Riwayat Pekerjaan
              </Link>
              <Link :href="route('personel.broadcast.index')" @click="isSidebarOpen = false" :class="route().current('personel.broadcast.*') ? 'bg-[#2563EB]/5 text-[#2563EB] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium'" class="flex items-center gap-4 px-5 py-3.5 rounded-2xl text-[14px] transition duration-150">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                Tugas & Broadcast Kegiatan
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
</template>
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const dropdownOpen = ref(false);
const isSidebarOpen = ref(false);

let notificationInterval = null;

onMounted(() => {
  notificationInterval = setInterval(() => {
    router.reload({
      only: ['auth'],
      preserveScroll: true,
      preserveState: true,
    });
  }, 15000); // Polling notifikasi setiap 15 detik
});

onUnmounted(() => {
  if (notificationInterval) {
    clearInterval(notificationInterval);
  }
});

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