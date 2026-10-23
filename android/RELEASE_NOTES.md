## SISFOPERS KC — Aplikasi Android

### Perubahan 1.0.3
- **Satu akun satu perangkat:** bila akun sedang login di HP lain, aplikasi menampilkan peringatan untuk logout/melepaskan perangkat tersebut. Bila kehilangan akses HP lama, ketuk "Kehilangan akses…" — tautan dikirim ke email terdaftar untuk melepaskan HP lama (verifikasi NIKC, tanggal lahir, kata sandi, dan OTP email).
- **Sesi web aktif:** saat login aplikasi ketika akun masih aktif di web, muncul pertanyaan "Anda sedang aktif di sesi web. Apakah Anda ingin logout sesi web?" — pilih Iya untuk mengakhiri sesi web dan melanjutkan login.
- HP yang dilepas otomatis keluar dari akun.

### Perubahan 1.0.2
- Kartu E-KTA kini identik dengan versi web: sisi depan & belakang, QR verifikasi keaslian, watermark KOMCAD, lambang Komcad, blok pejabat (stempel & tanda tangan), sinyalemen, tanda kehormatan, dan tanda tangan pemegang.
- Tab Tampak Depan/Belakang, tombol Balik Kartu (animasi), dan Cetak E-KTA.
- Logo, tanda tangan, stempel, pejabat, serta jenis & ukuran font mengikuti Pengaturan E-KTA admin (dengan pratinjau langsung).

### Perubahan 1.0.1
- Ikon aplikasi, layar pembuka, dan logo di dalam aplikasi kini memakai lambang Mabes TNI.
- Lambang Tri Matra (TNI AD, TNI AL, TNI AU) di halaman masuk tampil utuh, tidak lagi terpotong kartu login.

Aplikasi native (Kotlin + Jetpack Compose) dengan tampilan mengikuti versi web dan animasi interaktif.

### Fitur
- Login User & PJU; menu dan fitur menyesuaikan peran: **Admin, Koordinator (Matra/Angkatan), PJU, Personel**
- Pendaftaran akun (cek NIKC SKEP), pengajuan verifikasi SKEP, lupa NIKC, reset kata sandi OTP
- Verifikasi akun OTP (WhatsApp/Email/OTP cetak), sinyalmen, data kewilayahan
- Admin/Koordinator: konfirmasi pendaftaran, master personel, SKEP, pengkinian data, verifikasi pendidikan, E-KTA, OTP manual & reset, broadcast, formulir & rekrutmen, tiket, monitoring, laporan PDF/Excel, pengaturan sistem (termasuk Notifikasi Push FCM), akun PJU, mail gateway
- Personel: dashboard, kegiatan & konfirmasi kehadiran, pendidikan, pekerjaan (OTP), pengkinian data, tiket, formulir, E-KTA
- Pusat Layanan Informasi: chat dengan lampiran + panggilan video (Agora)
- Vicon rapat dinas multi-peserta
- Notifikasi push real-time (Firebase, diatur dari Pengaturan Sistem admin) dengan cadangan sinkronisasi berkala; panggilan masuk layar penuh
- **Pembaruan aplikasi dari dalam aplikasi** (seperti SISINDEN): Admin mengunggah APK di Pengaturan Sistem → semua perangkat mendapat notifikasi, aplikasi mengunduh, memverifikasi SHA-256 & tanda tangan, lalu memasang. Cadangan otomatis dari GitHub Release ini. Pemeriksaan berkala tiap 6 jam, unduh otomatis via Wi-Fi (bisa dimatikan di menu Akun)

### Kebutuhan server
Backend harus memuat perubahan branch `api-mobile` (API `/api/m`, tabel `mobile_devices`, pembaruan APK). Setelah deploy jalankan `php artisan migrate` dan `php artisan optimize:clear`. Untuk unggah APK dari Pengaturan Sistem, naikkan `upload_max_filesize`/`post_max_size` PHP (mis. 100M) dan `client_max_body_size` Nginx.

### Instalasi
Unduh APK di bawah, izinkan "Instal aplikasi tidak dikenal", lalu pasang. Android 7.0 ke atas.

### Panduan
Buku panduan penggunaan (PDF & Word) tersedia bersama paparan aplikasi.
