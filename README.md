# SISFOPERSKC - Sistem Informasi Personel Komponen Cadangan

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
  <span style="font-size: 2.5rem; font-weight: bold; margin: 0 15px; position: relative; top: -10px;">+</span>
  <img src="https://vuejs.org/images/logo.png" width="80" alt="Vue Logo" style="position: relative; top: 12px;">
</p>

SISFOPERSKC adalah Sistem Informasi Manajemen Personel Komponen Cadangan Nasional yang dirancang dengan arsitektur modern untuk mencatat, memvalidasi, memantau, dan memobilisasi personel Komponen Cadangan (Komcad) secara terpusat dan aman.

Aplikasi ini dibangun menggunakan framework **Laravel 13** di sisi Backend dan **Vue 3** (Composition API) berbasis **Inertia.js** di sisi Frontend, serta menggunakan **Tailwind CSS** untuk antarmuka pengguna yang premium dan responsif.

---

## 🚀 Fitur Utama Sistem

### 🔒 1. Manajemen Hak Akses & Multi-Role
Sistem mendukung tiga level otorisasi akses (roles):
- **Administrator Sistem (Admin)**: Kontrol penuh atas seluruh database personel, validasi berkas pendaftaran, manajemen database SKEP, manajemen broadcast kegiatan, pemantauan log keamanan, dan konfigurasi gateway sistem.
- **Komandan Satuan (Komandan)**: Hak akses baca-saja (read-only) untuk memantau data kekuatan pasukan, riwayat kehadiran, dan status respon mobilisasi.
- **Anggota Komponen Cadangan (Personel)**: Hak akses personal untuk verifikasi wajah, pengisian sinyalmen fisik, menerima perintah broadcast, dan konfirmasi presensi kehadiran.

### 📋 2. Manajemen SKEP (Pra-Verifikasi & Unggah PDF)
Pendaftaran anggota Komponen Cadangan kini disinkronkan secara ketat dengan Surat Keputusan (SKEP) resmi:
- **Impor Database Excel**: Admin dapat mengunggah file Excel berisi daftar nama pra-verifikasi yang memuat: *Nama Lengkap, NIKC, Angkatan,* dan *Matra*.
- **Pengecekan NIKC Real-Time**: Pada halaman pendaftaran, pendaftar wajib memasukkan NIKC. 
  - Jika NIKC terdaftar, data *Nama Lengkap, Matra,* dan *Angkatan* akan dimuat otomatis dan dikunci (read-only) demi keamanan data.
  - Jika NIKC tidak terdaftar, pendaftar akan diarahkan ke form pengajuan verifikasi SKEP untuk mengunggah berkas SKEP asli mereka dalam format **PDF** (Maks. 2MB).
- **Verifikasi Berkas PDF oleh Admin**: Admin memiliki panel khusus untuk mempratinjau berkas PDF SKEP yang diunggah pendaftar. Admin dapat menyetujui (APPROVED) atau menolak (REJECTED) disertai catatan penolakan.
- **Notifikasi WhatsApp Otomatis**: Setelah admin memproses keputusan berkas SKEP, pendaftar akan menerima notifikasi status pengajuan secara real-time via WhatsApp.

### 🛡️ 3. Autentikasi Akun via OTP WhatsApp & Proteksi Salah 5x (*Fitur Baru*)
Pemindaian wajah biometrik (kamera) dinonaktifkan sepenuhnya demi fleksibilitas pengguna dan digantikan dengan alur verifikasi OTP WhatsApp yang sangat aman:
- **Verifikasi OTP Akun Baru**: Pengguna baru wajib melakukan verifikasi OTP 6 digit yang dikirimkan langsung ke nomor WhatsApp mereka sebelum dapat masuk ke halaman pengisian Sinyalmen.
- **Proteksi Salah 5x & Blokir 5 Menit**: Input OTP memiliki keamanan limitasi percobaan salah maksimal 5 kali. Jika pengguna salah memasukkan kode sebanyak 5 kali, akses permintaan OTP dan verifikasi akun akan dikunci (diblokir) selama **5 menit**.
- **Hitung Mundur Digital di Layar**: Saat terblokir, tombol verifikasi akan dinonaktifkan secara dinamis dan memunculkan penghitung waktu mundur digital (MM:SS) secara real-time langsung di layar browser pengguna.

### 💼 4. Integrasi Kepegawaian ASN & Riwayat Pekerjaan Terverifikasi OTP WA
- **Sinkronisasi ASN Otomatis**: Formulir pendaftaran dilengkapi opsi status ASN. Jika terpilih, pendaftar mengisi data kepegawaian (NIP, Jenis ASN, TMT Pengangkatan, dan Unggah SK ASN). Setelah disetujui, data ini otomatis tersinkronisasi sebagai pekerjaan aktif pertama personel di tabel Riwayat Pekerjaan.
- **Tampilan Penuh Data ASN**: Personel ASN kini dapat melihat detail data pekerjaan aktif mereka secara lengkap di menu Riwayat Pekerjaan, dan dapat mengajukan pembaruan data secara dinamis via OTP WhatsApp dengan form yang sudah terisi otomatis (pre-populated) berdasarkan data pendaftaran awal.
- **Verifikasi OTP WhatsApp**: Untuk personel Non-ASN, pengisian awal data pekerjaan dan setiap pembaruan karir (pindah lokasi, update detail, atau pelaporan PHK) dilindungi oleh pengiriman OTP 6 digit ke nomor WhatsApp.
- **Integrasi API Wilayah Indonesia**: Penentuan wilayah di halaman pendaftaran (`Register.vue`) dan lokasi kerja di halaman pekerjaan personel menggunakan select dropdown dinamis yang terhubung ke API statis `emsifa/api-wilayah-indonesia` untuk memuat data Provinsi, Kabupaten/Kota, dan Kecamatan secara berjenjang.
- **Transisi Laporan PHK**: Personel yang mengalami PHK dapat melaporkan status PHK dengan memasukkan TMT PHK dan Alasan PHK (terverifikasi OTP WhatsApp), yang otomatis memindahkan status pekerjaan aktif mereka menjadi "Tidak Bekerja" serta melampirkan alasan tersebut pada linimasa karir.

### 🔔 5. Pusat Notifikasi Terpadu & Visual Ikon SVG Premium (*Fitur Baru*)
Sistem notifikasi (lonceng) pada header dasbor dan halaman utama notifikasi kini dikelola secara dinamis menggunakan database notifications:
- **Notifikasi Multi-Peristiwa**: Notifikasi terkirim otomatis pada peristiwa penting: Broadcast Komando Baru, Sukses Konfirmasi Presensi Kegiatan, Sukses Perbarui Sandi Masuk, Sukses Perbarui Profil Akun, Sukses Mengubah Pengaturan Google MFA (2FA), Sukses Mengisi/Memperbarui Data Kerja, dan Sukses Verifikasi Akun Baru.
- **Visualisasi Ikon SVG Bebas Emoji**: Seluruh ikon emoticon/emoji sistem dibersihkan secara total dan diganti dengan representasi badge warna lembut (light background) dengan ikon SVG vektor modern yang rapi dan profesional untuk setiap tipe notifikasi.

### 🎨 6. Dasbor TNI Terintegrasi & Logo Dinamis (*Fitur Baru*)
- **Logo TNI Dinamis**: Logo TNI di samping tulisan SISFOPERS. pada sidebar (desktop maupun mobile) kini dimuat secara dinamis langsung dari menu **Pengaturan Sistem** (kolom `logo_tni` di database).
- **Pembaruan Subtitle & Nama Dasbor**: Teks deskripsi di bawah logo sidebar diganti menjadi **"Sistem Informasi Personil KC"**. Selain itu, dasbor personel kini memunculkan nama pangkat secara rapi sebelum nama lengkap, contoh: `Selamat Datang Kembali, Letda I Gusti Sultan`.

### 📊 7. Ekspor Laporan & Cetak Berkas
- **Ekspor Excel**: Mengunduh seluruh daftar kekuatan personel Komcad dalam format `.xlsx` secara instan menggunakan `maatwebsite/excel`.
- **Cetak Laporan PDF**: Mencetak laporan resmi instansial dalam format `.pdf` berukuran A4 landscape menggunakan engine rendering `barryvdh/laravel-dompdf`.

---

### 🛡️ 8. Penghapusan Akun Personel Secara Permanen (Force & Cascade Delete) (*Fitur Baru*)
- **Pembersihan Berkas Fisik**: Saat admin menghapus akun personel melalui dashboard admin web, sistem secara otomatis menghapus berkas foto pasfoto dan dokumen PDF scan KTP dari penyimpanan disk server.
- **Cascade & Force Delete Database**: Menghapus seluruh riwayat data terkait di tabel pendaftaran (`registrations`), verifikasi wajah (`face_verifications`), sinyalmen, respon kegiatan dinas, riwayat pendidikan, riwayat pekerjaan, dan kode verifikasi OTP.
- **Pelepasan Data Autentikasi**: Menghapus baris personel secara permanen (*Force Delete*) dan menghapus akun `User` terkait agar **NIK, NIKC, dan Email** terbebas sepenuhnya sehingga dapat digunakan untuk mendaftar ulang secara instan tanpa hambatan constraint unik.

## 🛠️ Persyaratan Sistem

- PHP >= 8.3
- Node.js >= 20.x & npm
- Composer >= 2.x
- Driver database SQLite atau MySQL.

---

## 🔧 Panduan Instalasi dan Penyiapan

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi di lingkungan lokal Anda:

### 1. Kloning Repositori
```bash
git clone https://github.com/Igustisultanh12/sisfopers1.git sisfopers1
cd sisfopers1
```

### 2. Konfigurasi Environment
Salin berkas contoh `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi koneksi database Anda di dalam file `.env`. Secara default, aplikasi dikonfigurasi menggunakan SQLite (`DB_CONNECTION=sqlite`). Jika menggunakan SQLite, buat file database kosong di direktori `database/`:
```bash
touch database/database.sqlite
```

### 3. Instal Dependensi PHP (Composer)
```bash
composer install
```

### 4. Buat Application Key
```bash
php artisan key:generate
```

### 5. Jalankan Migrasi & Seeder Database
Eksekusi migrasi tabel baru beserta data awal (roles, default users, dan default settings):
```bash
php artisan migrate --seed
```

### 6. Instal & Build Aset Frontend (npm)
Instal paket javascript pendukung Vue 3 dan jalankan build aset menggunakan Vite:
```bash
npm install
npm run build
```

### 7. Jalankan Server Pengembangan
Jalankan server lokal Laravel dan server kompilasi Vite secara bersamaan:
```bash
npm run dev
```
Aplikasi Anda akan siap diakses melalui peramban di alamat [http://localhost:8000](http://localhost:8000).

### 8. Jalankan Antrean Queue Worker (Penting)
Untuk memproses pengiriman notifikasi pesan WhatsApp secara latar belakang (background job), jalankan queue worker Laravel:
```bash
php artisan queue:work
```

---

## 🔑 Akun Bawaan Sistem (Default Credentials)

Akun bawaan dibuat oleh seeder. **Kata sandinya TIDAK dipublikasikan di sini** demi
keamanan. Tetapkan kata sandi saat seeding melalui variabel environment, lalu segera
ubah setelah login pertama:

```env
SEED_ADMIN_PASSWORD=...
SEED_KOMANDAN_PASSWORD=...
SEED_KORD_ANGKATAN_PASSWORD=...
SEED_KORD_MATRA_PASSWORD=...
```

Jika variabel tidak diisi di lingkungan **produksi**, seeder akan membangkitkan kata
sandi acak dan menampilkannya sekali di konsol — jangan gunakan kata sandi yang pernah
dipublikasikan/di-commit.

| Role | Username / NIK | Kegunaan |
| :--- | :--- | :--- |
| **Administrator** | `admin.sisfopers` | Mengakses panel kontrol admin utama |
| **Komandan Satuan** | `komandan.kc` | Memantau statistik & data pasukan (Read-Only) |
| **Koordinator Angkatan** | `koordinator.angkatan` | Mengelola & membina personel Angkatan 2026 |
| **Koordinator Matra** | `koordinator.matra` | Mengelola personel Matra & membuat Broadcast Matra |

> ⚠️ Jika deployment produksi Anda pernah di-seed dengan kata sandi default lama
> (`P@sswordEnterprise2026`, dll. yang sebelumnya tercantum di README ini), segera
> ganti/nonaktifkan keempat akun tersebut: kata sandi itu ada di riwayat git publik.

---

## 📂 Struktur Direktori Utama

- `app/Http/Controllers/Admin/SkepController.php`: Pengelolaan panel data SKEP Excel dan verifikasi berkas PDF.
- `app/Http/Controllers/SkepPublicController.php`: Endpoint publik cek NIKC dan ajukan unggah berkas.
- `app/Http/Controllers/Personel/JobHistoryController.php`: Pengelolaan OTP WhatsApp, verifikasi token, dan amandemen karir/PHK personel.
- `app/Notifications/SystemNotification.php`: Notifikasi sistem generik penampil status/aktivitas pengguna.
- `app/Exports/PersonelExport.php`: Ekspor data Excel kekuatan personel.
- `resources/js/Pages/Admin/Skep/Index.vue`: Antarmuka manajemen SKEP admin.
- `resources/js/Pages/Auth/Register.vue`: Formulir pendaftaran personel dengan sistem cek NIKC, alur unggah berkas, dan pilihan wilayah.
- `resources/js/Pages/Personel/JobHistory/Index.vue`: Halaman portal karir personel terintegrasi OTP WA dan API wilayah.

---

## 🌐 Panduan Deployment di aaPanel

aaPanel adalah panel kontrol server yang sangat efisien untuk menghosting aplikasi Laravel + Vue (Inertia). Berikut adalah langkah-langkah deployment terperinci:

### 1. Persiapan Server di aaPanel
1. Masuk ke panel kontrol aaPanel Anda.
2. Melalui **App Store**, pastikan Anda telah memasang:
   - **Nginx** (Versi terbaru)
   - **MySQL** / **MariaDB**
   - **PHP** (Rekomendasi PHP >= 8.3)
3. Pada tab PHP yang terpasang, pastikan ekstensi berikut aktif: `fileinfo`, `opcache`, `redis` (jika menggunakan redis queue).

### 2. Kompilasi & Unggah Kode Aplikasi
1. Lakukan kompilasi aset frontend secara lokal (di komputer Anda) sebelum diunggah agar menghemat penggunaan RAM dan CPU server:
   ```bash
   npm run build
   ```
2. Kompres seluruh folder proyek Anda menjadi file `.zip` (abaikan folder `node_modules` dan `vendor`). Pastikan folder `public/build` ikut terkompres.
3. Di aaPanel, buka **Files** -> Masuk ke direktori `/www/wwwroot/`.
4. Buat folder baru untuk domain Anda, klik **Upload**, pilih file `.zip`, lalu ekstrak file tersebut di dalam folder tersebut.
5. Setel izin folder (Permissions) pada folder `storage` dan `bootstrap/cache` ke `775` dengan pemilik (Owner) diatur ke `www` agar server web dapat menulis berkas sesi dan unggahan PDF/Foto.

### 3. Tambahkan Situs Baru di aaPanel
1. Buka menu **Website** -> Klik **Add site**.
2. Masukkan **Domain** Anda, pilih **MySQL** untuk otomatis membuat database baru, dan klik **Submit**.
3. Klik nama website yang baru dibuat untuk membuka pengaturan situs:
   - **Site directory**: Setel folder root tempat Anda mengekstrak file tadi (misalnya `/www/wwwroot/sisfopers`).
   - **Running directory**: Ubah dari `/` menjadi `/public`. Klik **Save**.
   - **URL rewrite**: Pilih opsi template **laravel**, lalu klik **Save** (ini penting untuk routing SPA Inertia agar tidak menghasilkan error 404).

### 4. Konfigurasi Environment & Dependensi PHP
1. Buka terminal server (melalui SSH atau menu **Terminal** di aaPanel) dan masuk ke folder proyek Anda.
2. Salin berkas `.env`:
   ```bash
   cp .env.example .env
   ```
3. Edit file `.env` di aaPanel File Manager. Konfigurasikan detail database MySQL yang baru dibuat, `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` sesuai domain Anda.
4. Jalankan perintah instalasi dependensi PHP:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
5. Buat Application Key baru:
   ```bash
   php artisan key:generate
   ```
6. Hubungkan folder penyimpanan publik:
   ```bash
   php artisan storage:link
   ```
7. Jalankan migrasi dan isi database seeder:
   ```bash
   php artisan migrate --force --seed
   ```

### 5. Konfigurasi Supervisor (Untuk Notifikasi WA / Queue)
Karena WhatsApp notifikasi menggunakan Laravel Queue asinkronus, Anda perlu menjalankan queue worker secara terus menerus di latar belakang:
1. Di aaPanel App Store, cari dan pasang **Supervisor**.
2. Buka pengaturan Supervisor -> Klik **Add Daemon**.
3. Isi parameter berikut:
   - **Name**: `sisfopers-queue`
   - **Run User**: `www`
   - **Run Directory**: `/www/wwwroot/folder-situs-anda`
   - **Start Command**: `/www/server/php/83/bin/php artisan queue:work --sleep=3 --tries=3` (sesuaikan path PHP versi Anda di aaPanel, contoh path di atas adalah untuk PHP 8.3).
   - **Processes**: `1`
4. Klik **Confirm** dan pastikan status daemon menunjukkan **Running**.

---

## 📱 Aplikasi Mobile (Android & iOS) - Flutter

Proyek ini kini dilengkapi dengan aplikasi mobile cross-platform (Android & iOS) yang dibangun menggunakan **Flutter (v3.19.6)**. Aplikasi ini dirancang dengan antarmuka yang sangat premium dan terintegrasi penuh dengan backend API cloud.

### 🎨 Fitur Tampilan & Integrasi Premium
- **Desain Premium Ala CoreTax/DJP**: Mengadopsi estetika dasbor CoreTax resmi (warna navy gelap `#0B132B` / `#1C2541`, oranye emas `#F97316`, sudut bulat lebar, dan kartu transparan *translucent card*).
- **Branding & Aset Cloud Dinamis**: Halaman masuk (Login) memuat gambar latar belakang kapal perang, nama aplikasi, logo utama TNI Pentagonal, hingga logo ketiga Matra (TNI AD, AL, AU) secara real-time langsung dari database Pengaturan Admin Web melalui API Publik `/api/settings/public`.
- **Cascading Regional Dropdown**: Pengisian lokasi kantor (Provinsi, Kota/Kabupaten, Kecamatan) di halaman Pekerjaan aplikasi HP menggunakan dropdown berjenjang dinamis terintegrasi API Wilayah Indonesia.
- **Format Tanggal TMT**: Menampilkan format tanggal TMT mulai kerja secara bersih (`YYYY-MM-DD`).
- **Pusat Notifikasi Bersuara**: Dilengkapi suara notifikasi bawaan sistem (*default system sound*) dan banner visual melayang (*heads-up alert*) baik di Android maupun iOS (iPhone) saat menerima broadcast komando baru.
- **WhatsApp OTP Dialog Reaktif**: Dilengkapi tampilan input OTP 6-digit WhatsApp yang bersih dan proteksi blokir countdown digital jika salah input 5x.

### 🛠️ Alur Kompilasi Cloud Otomatis (GitHub Actions)
Aplikasi mobile dirakit secara otomatis di cloud menggunakan berkas alur kerja GitHub Actions `.github/workflows/flutter-build.yml` pada setiap push ke branch `main`:
1. **Android APK (`app-release.apk`)**: Kompilasi otomatis dengan injeksi izin `<uses-permission android:name="android.permission.INTERNET" />` pada manifest rilis untuk menjamin konektivitas internet penuh.
2. **iOS IPA (`sisfoperskc.ipa`)**: Dirakit tanpa penandatanganan pengembang berbayar (*Unsigned iOS Build*) menggunakan bypass Xcode (`CODE_SIGNING_ALLOWED=NO CODE_SIGNING_REQUIRED=NO PROVISIONING_PROFILE_REQUIRED=NO` via env) sehingga menghasilkan file `.ipa` bersih yang siap dipasang secara sideloading via **AltStore** atau **Sideloadly**.

### 📥 Cara Mengunduh Hasil Build APK & IPA
1. Buka repositori GitHub Anda di browser, masuk ke menu **Actions**.
2. Pilih run build terbaru yang berhasil (bercentang hijau).
3. Scroll ke bagian paling bawah ke bagian **Artifacts**:
   - Unduh **`sisfopers-release-apk`** untuk file `app-release.apk` (Android).
   - Unduh **`sisfopers-release-ipa`** untuk file `sisfoperskc.ipa` (iOS).
