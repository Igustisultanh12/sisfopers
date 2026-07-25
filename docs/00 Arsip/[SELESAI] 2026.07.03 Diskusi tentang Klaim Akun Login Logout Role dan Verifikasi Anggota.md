# Diskusi
## tentang KLAIM AKUN LOGIN LOGOUT ROLE DAN VERIFIKASI ANGGOTA

Status: `Siap Difinalkan`
> Status Arsip: `Selesai`
> Selesai pada: 2026-07-13 10:32:57 WIB
> Rujukan keputusan: `docs/02 Keputusan/2026.Kep.009 tentang Akun Login Logout Role dan Verifikasi Anggota.md`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang Klaim Akun Login Logout Role dan Verifikasi Anggota
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: login, logout, klaim akun, role pengguna, verifikasi personel, registrasi mandiri, verifikasi wajah awal, dan batas akses dasar website
Keputusan terkait: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`, `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`, `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`, `docs/02 Keputusan/2026.Kep.006 tentang Struktur Organisasi dan Grup Angkatan.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `docs/02 Keputusan/2026.Kep.009 tentang Akun Login Logout Role dan Verifikasi Anggota.md`

## Pemicu

Selain kebutuhan manajemen personel dasar, user juga menegaskan bahwa website perlu memiliki kemampuan login dan personel harus bisa dikelola. Agar pembahasan otentikasi tidak bercampur terlalu jauh dengan master data personel, perlu diskusi terpisah yang khusus membahas akun, sesi masuk, role, dan proses verifikasi personel.

## Temuan Awal

Audit awal terhadap dokumen aktif yang beririsan menghasilkan temuan berikut.

### Dokumen yang diaudit

- `AGENTS.md`
- `docs/README.md`
- `docs/01 Profil Sistem/PROJECT_STATUS.md`
- `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
- `docs/04 Diskusi/README.md`
- `docs/00 Arsip/[DITETAPKAN] 2026.07.03 Diskusi tentang Master Data dan Status Anggota.md`

### Kondisi repo yang terverifikasi

- repo masih berstatus `bootstrap` (catatan awal 3 Juli; per 2026-07-13 `Kep.006` sudah final & role aktif di kode — lihat T6, snapshot ini bukan penghalang finalisasi)
- belum ada implementasi autentikasi aktif (pada 3 Juli; per 13 Juli route/login/role sudah jalan, lihat Kondisi Kode Aktual)
- belum ada keputusan final tentang role pengguna (PADA 3 Juli; `Kep.006` sudah menguncinya per 13 Juli)
- belum ada aturan final tentang apakah akun personel lahir dari input admin, klaim mandiri, atau keduanya (PADA 3 Juli; model hibrida sudah di T1-T3 per 13 Juli)

### Temuan dari dokumen aktif yang beririsan

- `PROJECT_STATUS.md` menyatakan bootstrap Laravel 13 sebaiknya didahului keputusan dasar tentang modul awal
- `docs/04 Diskusi/README.md` mewajibkan hasil analisis awal diringkas di dokumen diskusi
- `2026.Kep.001` melarang lahirnya referensi teknis atau implementasi formal ketika topik masih berupa diskusi
- diskusi personel dasar sudah dibuka untuk membahas data inti dan CRUD personel, sehingga diskusi ini perlu menjaga boundary agar tidak terjadi tumpang tindih

### Kondisi kode aktual

- route login, register, dan logout sudah ada di `routes/web.php` dan `routes/auth.php`
- otorisasi berbasis role sudah dipasang lewat middleware `role`
- role yang tampak di kode aktual adalah `admin`, `koordinator`, `wakil koordinator`, dan `personel`
- gerbang verifikasi personel sudah dipakai di middleware dan controller personel
- halaman pengaturan akun sudah memuat pembaruan profil, kata sandi, dan MFA Google Authenticator
- ada ketidaksinkronan field MFA antara controller profil dan schema user, sehingga keputusan atas standar penyimpanan secret perlu dipertegas
- runtime registrasi publik saat ini masih menganggap pencocokan grup aktif berbasis `abituren = Reguler`, sehingga jalur `ASN` dan `SPPI` belum benar-benar hidup di form registrasi mandiri
- istilah `super admin` sudah muncul di keputusan, workplan, dan beberapa teks UI, tetapi role database aktif yang benar-benar ada saat ini masih `admin`, `koordinator`, `wakil koordinator`, dan `personel`
- route permanen untuk dashboard pengawasan sudah disinkronkan ke `koordinator`, dan `wakil koordinator` diarahkan ke dashboard yang sama
- user sudah menetapkan MFA sebagai `hold`, sehingga pembahasan MFA hanya dicatat sebagai penundaan, bukan keputusan implementasi

### Catatan pelanggaran lifecycle

Pada domain akun dan akses juga terjadi pelanggaran lifecycle. Walaupun topik ini masih berstatus `04 Diskusi`, sinkronisasi implementasi role permanen sempat langsung dilakukan pada route, controller, page, store, seeder, dan role database. Karena itu, kondisi kode saat ini tidak boleh dibaca sebagai legitimasi keputusan; ia hanya dicatat sebagai drift implementasi yang terjadi sebelum lahir keputusan dan rencana kerja formal.

### Temuan substantif dari arahan user

- website perlu memiliki login
- personel perlu dapat dikelola
- user menyorot `super admin`, `role`, `login`, dan `logout` sebagai bagian yang perlu ditempatkan secara benar dalam lifecycle
- verifikasi oleh `koordinator` dibatasi hanya untuk klaim awal, bukan update data inti berkala
- fondasi `Grup Angkatan`, `TMT Penetapan`, `Tahun Angkatan`, dan perpindahan grup sudah memiliki norma di `2026.Kep.004`, sehingga diskusi ini tidak perlu mengulang domain tersebut

## Batas Setelah Keputusan Terkait

Hal berikut sudah diputuskan di dokumen lain dan tidak perlu didiskusikan ulang di sini:

- standar identitas dokumen formal di `2026.Kep.001`
- standar `NIKC` di `2026.Kep.003`
- `TMT Penetapan`, `Tahun Angkatan`, `Grup Angkatan`, perpindahan grup, dan status dasar personel di `2026.Kep.004`

Dokumen ini hanya membahas kebijakan akun, login, logout, role akses, dan verifikasi klaim akun.

## Opsi yang Dipertimbangkan

- **Opsi A: Login dan verifikasi personel dipisah dari master data personel**
  - Kelebihan:
    - menjaga domain tetap rapi
    - memudahkan finalisasi per tahap
    - cocok untuk mendefinisikan role dan boundary akses secara lebih fokus
  - Kekurangan:
    - perlu sinkronisasi ketat dengan diskusi personel dasar

- **Opsi B: Login, role, dan CRUD personel digabung seluruhnya ke diskusi personel**
  - Kelebihan:
    - lebih sedikit dokumen
    - terasa sederhana di awal
  - Kekurangan:
    - scope mudah melebar
    - risiko campur antara data personel dan policy akses

- **Opsi C: Hanya membahas login/logout, sedangkan role dan verifikasi ditunda**
  - Kelebihan:
    - paling ringan
    - cukup untuk bootstrap autentikasi dasar
  - Kekurangan:
    - tidak cukup menjawab kebutuhan pengelolaan personel
    - role dan verifikasi akan menjadi asumsi liar saat implementasi

## Poin Diskusi

### 1. Perlu dibedakan antara aktor pengelola dan aktor personel

Website yang memiliki fitur manajemen personel dasar biasanya memiliki dua rumpun pengguna:

- pengelola internal seperti `super admin` atau `admin`
- personel yang mengakses akun miliknya sendiri

Diskusi ini perlu menegaskan sejak awal apakah keduanya memakai satu mekanisme login yang sama atau berbeda hanya pada otorisasi setelah login.

### 2. Role dasar cukup didefinisikan sebagai boundary akses awal

Pada fase ini belum perlu membuat permission matrix rinci lintas semua modul. Yang perlu dibahas lebih dulu adalah boundary dasar:

- `super admin`
- `admin`
- `koordinator` jika nanti dipakai untuk verifikasi
- `personel`

Masing-masing role perlu punya batas kewenangan minimum yang jelas agar implementasi autentikasi awal tidak liar.

### 3. Login dan logout tampak sederhana, tetapi implikasinya normatif

Login dan logout tidak sekadar fitur teknis. Keduanya menyentuh:

- siapa yang berhak memiliki akun
- apakah akun personel dibuat admin atau diklaim sendiri
- kapan akun dianggap aktif
- apa yang terjadi jika pengajuan belum diverifikasi

Karena itu, diskusi ini perlu menempatkan login/logout sebagai bagian dari kebijakan akses, bukan sekadar form.

### 4. Klaim akun personel perlu diputuskan apakah termasuk scope awal

Ada dua pola yang mungkin:

- admin membuat akun personel secara penuh sejak awal
- admin hanya membuat data personel, lalu personel mengklaim akun sendiri

Pilihan ini akan mengubah desain alur verifikasi, kebutuhan dokumen pendukung, dan siapa yang pertama kali memicu aktivasi akun.

### 5. Verifikasi personel bisa menjadi gerbang aktivasi akun

Jika repo memilih pola klaim akun, verifikasi kemungkinan menjadi tahap normatif yang memisahkan:

- data personel yang sudah tercatat
- akun personel yang sah untuk login

Karena itu, diskusi ini perlu menentukan apakah verifikasi dilakukan oleh admin, koordinator, atau aktor lain.

### 5.a. Klaim akun berbasis kecocokan NIKC

Jika NIKC sudah ada di sistem, mekanismenya cenderung masuk ke jalur klaim akun biasa. Jika NIKC belum ada datanya di sistem, alurnya perlu diputuskan apakah tetap boleh melanjutkan klaim dengan auto-fill data dari NIKC lalu meminta nomor keputusan dan unggahan PDF keputusan.

Pertanyaan yang relevan untuk diskusi ini:

- apakah keberadaan NIKC di sistem menjadi syarat utama agar akun masuk jalur klaim
- apakah NIKC yang belum ada di sistem tetap boleh mendaftar dengan status baru atau pending
- apakah form klaim perlu menampilkan field tambahan untuk nomor keputusan jika NIKC belum ditemukan
- apakah sistem wajib meminta unggah PDF keputusan sebagai lampiran verifikasi
- apakah pending verifikasi diproses oleh admin, koordinator, atau keduanya
- jika grup angkatan yang sah belum ada di master, apakah pendaftar tetap boleh lanjut dengan unggah Surat Keputusan untuk diverifikasi admin sebagai dasar pembentukan atau penautan grup
- apakah verifikasi wajah awal dipakai sebagai bukti bahwa registrasi diajukan oleh personel yang bersangkutan, tanpa otomatis mengaktifkan face recognition

Keputusan yang sudah mengerucut:

- verifikasi oleh `koordinator` hanya dipakai untuk klaim awal
- perubahan data personel setelah aktif tidak dibawa ke jalur verifikasi klaim akun ini

Catatan scope terbaru:

- keputusan final NIKC sudah lahir di `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`
- keputusan final `TMT Penetapan`, `Tahun Angkatan`, dan `Grup Angkatan` sudah lahir di `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
- jika NIKC belum ada di sistem, alurnya tidak lagi dibahas di diskusi NIKC
- alur itu masuk ke scope modul atau fitur lain yang mengatur registrasi baru, klaim, atau verifikasi manual
- diskusi ini hanya memegang logika klaim akun, verifikasi, dan hubungan akun dengan data personel yang sudah ada
- arahan user per 12 Juli 2026 menambahkan satu jalur sementara: jika personel mendaftar mandiri dan grup angkatan belum ada di master, pendaftaran tetap boleh masuk sebagai PENDING, tetapi pendaftar wajib mengunggah Surat Keputusan agar admin dapat memverifikasi dasar pembentukan atau penautan grup
- arahan user yang sama juga memisahkan face verification awal sebagai bukti registrasi dari face recognition; yang pertama dibahas di diskusi ini, yang kedua dipindah ke diskusi tersendiri

### 6. Super admin perlu didefinisikan sebagai peran, bukan sekadar user paling kuat

Karena user menyebut `super admin`, diskusi ini perlu menegaskan:

- apakah `super admin` berbeda nyata dari `admin`
- kewenangan tambahan apa yang hanya dimiliki `super admin`
- apakah `super admin` boleh mengelola role user lain
- apakah `super admin` boleh melihat dan mengubah semua data tanpa batas kelompok

### 7. MFA akun dan batas penyimpanan secret perlu diputuskan

Repo sudah mengarah ke MFA Google Authenticator di halaman pengaturan akun. Namun implementasi aktual masih memerlukan keputusan normatif yang tegas:

- apakah MFA wajib untuk fase awal atau opsional
- kolom mana yang menjadi sumber kebenaran secret MFA
- apakah MFA hanya aktif untuk admin atau semua role

Posisi governance saat ini:

- dasar normatif status `hold` untuk `MFA` hidup di `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
- rumah diskusi untuk membuka kembali `hold`, memutuskan aktor, alur aktivasi, dan sumber kebenaran secret tetap berada di dokumen akun dan akses ini
- selama belum ada keputusan turunan baru, route atau controller MFA yang masih ada hanya boleh diperlakukan sebagai stub nonaktif

### 8. Role `komandan` sudah dilepas dari runtime aktif

Sinkronisasi implementasi sudah memindahkan jalur menu, route, controller, page, dan seeder dari `komandan` ke `koordinator`. Role `wakil koordinator` disimpan sebagai role terpisah dan memakai dashboard `koordinator` yang sama. Karena repo masih diarahkan untuk fresh install, perubahan ini dilakukan langsung tanpa alias route lama dan tanpa migration transisi data role lama. Namun perubahan tersebut tetap tercatat sebagai implementasi prematur selama diskusi belum difinalkan.

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Website memerlukan login | Sudah jelas dari arahan user | Clear |
| 2 | Logout sebagai bagian autentikasi dasar | Relevan dan perlu dimasukkan | Clear |
| 3 | Definisi role dasar | **Dijawab:** diatur `Kep.006` (4 role operasional: personel, koordinator, wakil koordinator, admin; + Super Admin sebagai role non-Komcad terpisah). Lihat T6 | Clear (rujuk Kep.006) |
| 4 | Posisi `super admin` | Sudah dibatasi: klaim awal oleh koordinator. Klarifikasi user per 2026-07-12: `Super Admin` adalah role, tetapi dalam sebutan harian disebut `admin`; dengan demikian `admin` berarti `Super Admin`. Jika kelak `admin` berbeda dari `Super Admin`, akan terbit keputusan terpisah. | Clear (arah) |
| 5 | Pola klaim akun vs akun dibuat admin | Sudah mengerucut ke model hibrida: admin tetap bisa membuat akun, tetapi registrasi mandiri tetap boleh berjalan sebagai `PENDING` | Sebagian clear |
| 6 | Otoritas verifikasi personel | Sudah dibatasi: klaim awal oleh koordinator, sedangkan verifikasi SK pembentukan/penautan grup pada jalur registrasi mandiri berada di admin/super admin; nomenklatur aktor finalnya belum bersih di runtime | Sebagian clear |
| 7 | MFA akun dan penyimpanan secret | Ditahan oleh user | Tidak dibahas lanjut dulu |
| 8 | Status normatif role `komandan` | Sudah diganti ke `koordinator` pada runtime permanen | Clear |
| 9 | Domain grup angkatan | Sudah dirujukkan ke 2026.Kep.004 dan diskusi struktur organisasi | Clear |
| 10 | Verifikasi wajah awal sebagai bukti registrasi | Penting, tetapi belum aktif; file modul boleh ada sebagai dormant sampai diskusi ini selesai | Clear |
| 11 | Face recognition otomatis | Dipisahkan ke diskusi tersendiri dan ditunda | Clear |

## Checklist Pra-Finalisasi

| # | Yang Wajib Dicatat | Sudah Dibahas? | Catatan |
| --- | --- | --- | --- |
| 1 | Scope (`termasuk` dan `tidak termasuk`) | Ya | Fokus pada akun dan akses, bukan schema teknis sesi |
| 2 | Non-goals | Ya | Tidak membahas implementasi middleware, guard, atau package Laravel |
| 3 | Exception (`jika relevan`) | Ya | Jika scope awal terlalu sempit, klaim akun bisa ditunda dan akun dibuat admin |
| 4 | Dampak ke fitur lain | Ya | Akan memengaruhi modul personel, notifikasi, dan dokumen pendukung |
| 5 | Dampak ke Profil Sistem (`jika relevan`) | Ya | `PROJECT_STATUS.md` perlu diperbarui setelah keputusan final lahir |
| 6 | Data / Schema impact (`jika relevan`) | Ya | Ada dampak schema user dan relasinya, tetapi belum difinalkan |
| 7 | Hal yang tidak berubah | Ya | Lifecycle docs dan status bootstrap repo tetap berlaku |
| 8 | Acceptance criteria | Ya | Dirangkum di bawah sebagai syarat keputusan lanjutan |

Acceptance criteria yang diharapkan bila topik ini nanti difinalkan:

- [x] ada keputusan resmi tentang role dasar website -> `Kep.006` (admin/koordinator/wakil koordinator/personel); `super admin` = role `admin` (klarifikasi 2026-07-12)
- [x] ada keputusan resmi tentang posisi `super admin` -> `Kep.006` klausul: `admin` = daily term Super Admin; pemisahan keduanya hanya bila lahir keputusan terpisah
- [x] ada keputusan resmi tentang login dan logout dasar -> lihat Temuan Analisis T5 (login identifier NIKC/username; MFA hold `Kep.004`)
- [x] ada keputusan resmi tentang pola aktivasi atau klaim akun personel -> lihat T1-T3 (registrasi mandiri NIKC-first; admin pre-load SK = nicety)
- [x] ada keputusan resmi tentang siapa yang memverifikasi personel jika verifikasi memang dipakai -> koordinator untuk dokumen hilang (KTA/Ijazah), admin untuk sisanya; gating `is_active`

## Kondisi Kode Aktual (snapshot 2026-07-13, verifikasi statis)

> Tujuan blok ini: merekam keadaan file/schema SAAT INI agar saat lahir keputusan/revisi/dokumen
> turunan, pekerjaan implementasi & migration mudah dipetakan. Verifikasi STATIS (baca file);
> runtime tidak diuji (Laragon/MySQL mati, `php` tak di PATH).

### Routing (`routes/web.php`)
- Login: `GET/POST /login` -> `AuthController` (satu halaman, guest).
- Register mandiri: `GET/POST /register` -> `AuthController` (guest).
- Logout: `POST /logout`.
- MFA: `POST /account/settings/mfa` (`profile.mfa`) + `.../mfa/verify` (`profile.mfa.verify`) **MASIH TERDAFTAR** (baris 66-67) walau status HOLD -> **dipertahankan sebagai yatim/dormant** (T5/T7 + Rencana Perubahan File): field & route tetap ada, tak direachable dari flow aktif, tak dipakai hingga keputusan baru. Tidak dihapus.
- Verifikasi pendaftaran (admin): `GET /admin/verifikasi`, `GET/POST /admin/verifikasi/{uuid}` -> `VerificationController`.
- Role group: `role:admin` (prefix admin), `role:koordinator,wakil koordinator` (prefix koordinator, read-only), `role:personel` (prefix personel).
- Personel di balik middleware `profile_complete`; alur pengisian via `sinyalemen.*`.

### Middleware (`bootstrap/app.php` + `app/Http/Middleware`)
- Alias terdaftar HANYA: `role` (`RoleMiddleware`), `profile_complete` (`EnsureProfileComplete`).
- **`EnsureFaceVerified` = DEAD CODE**: tidak di-alias, tidak dipasang di route mana pun, dan redirect ke `route('personel.face-verification')` yang **TIDAK ADA** di `web.php`. -> **KYC Pengajuan (Swafoto Pengajuan) BELUM aktif**; baru ada file middleware + tabel. Ini pekerjaan implementasi, bukan fitur berjalan.

### Schema personels (`create_personels_table`, migration 2026_07_06_055601)
- Identitas: `username`(50, unique, nullable), `email`, `password`(nullable), `is_active`(default false), `nik`(16 unique), `nikc`(17 unique), `full_name`(150).
- Data diri: `pob`, `dob`, `gender`(enum Pria/Wanita), `matra`, `angkatan`, `abituren`(default Reguler), `pangkat`(50), kontak & alamat, `photo_profile`, `ktp_document`.
- Pendidikan: `file_ijazah_path`, `education_level`, `study_program`, `education_verified_at`, `education_verified_by`.
- SK inline: `sk_number`, `sk_date`, `sk_title`, `tmt_penetapan`.
- Status: `status_keaktifan`(enum), `status_profile`(BELUM_LENGKAP/LENGKAP), `role_id`, `grup_angkatan_id`.
- **BELUM ADA** (padahal disyaratkan keputusan): `is_komcad`, `nrp` (`Kep.006` klausul 16-19; baru rencana di `2026.11.004`). `two_factor_*` (lihat drift schema).

### Tabel terkait registrasi/verifikasi
- `registrations` (migration 055620): HANYA `personel_id`(unique), `status_verification`(PENDING/APPROVED/REJECTED), `admin_notes`, `verified_by`, `verified_at`. **BELUM ADA** kolom untuk membedakan sub-status MATCH / MISMATCH / NO_SK (kalau alur T1/T3 mau ditegakkan, perlu kolom tambahan).
- `face_verifications` (migration 055639): ada tabel (`verification_image`, dst) tapi middleware-nya dead (lihat atas). -> **rencana: rename -> `kyc_submissions`** (`swafoto_path`, `kyc_verified_at`) saat implementasi KYC (lihat Rencana Perubahan File A/C).
- `surat_keputusan_personel_entries`: `nikc`, `full_name`, `target_pangkat`, `entry_status`(pending/matched/processed/unmatched). **BELUM ADA** `pob`/`dob`/`pendidikan` -> cross-check identitas dari SK belum bisa (selaras T2).
- `surat_keputusan_artifacts`: `nomor_surat`+`tanggal_surat` unique (mendukung B3 cocokan nomor/tanggal SK).

### Drift schema nyata (WAJIB dicatat untuk keputusan/revisi)
1. **`two_factor_*` (MFA) = yatim/dormant:** `Personel.php` fillable+casts (baris 31-33, 74, 80-81) & `AuthController:164`/`MasterPersonelController:157,317` memakai `two_factor_enabled/secret/recovery_codes`, TAPI migration `personels` **tidak punya kolom itu** (drift schema). -> Karena MFA **fitur penting yang dipertahankan** namun **HOLD**, arah = **yatim/dormant** (field & route tetap ada, tak direachable dari flow aktif, tak dipakai hingga ada keputusan baru). Saat fresh-install skema dibangun, kolom `two_factor_*` **diduplikasi ke migration `personels`** agar tak ada error, lalu dibiarkan dormant (lihat Rencana Perubahan File A/B).
2. **Login identifier:** pesan approval (`ApproveRegistrationAction:58`, `VerificationController:129`) mengarahkan "login menggunakan **NIK**", sedangkan keputusan user = **NIKC atau username** -> perlu penyelarasan implementasi + kolom sudah tersedia (`nik`, `nikc`, `username` semua ada).
3. **KYC Pengajuan (Swafoto Pengajuan) belum aktif** (dead middleware `EnsureFaceVerified`) -> implementasi: rename ke istilah KYC, alias + pasang di grup `role:personel` + route `personel.kyc` + view Swafoto Pengajuan (lihat Rencana Perubahan File).
4. **`is_komcad`/`nrp` belum ada** -> blok registrasi Super Admin non-Komcad (T6) belum bisa ditegakkan sampai migration lahir (fresh install: tulis ulang bersih di migration `personels`).

## Catatan untuk AI Agent

- jangan lahirkan referensi teknis autentikasi, endpoint, flow UI, atau package rekomendasi saat topik ini masih diskusi
- jika ada perubahan kode yang terlanjur dilakukan saat status masih diskusi, catat sebagai drift implementasi dan jangan perlakukan sebagai keputusan sah
- jaga boundary dengan diskusi personel dasar; data inti personel hidup di sana, sedangkan policy akun hidup di sini
- jangan ulangi pembahasan `TMT Penetapan`, `Tahun Angkatan`, `Grup Angkatan`, atau perpindahan grup karena domain itu sudah punya keputusan sendiri
- jika user memilih akun dibuat penuh oleh admin tanpa klaim mandiri, perbarui ruang lingkup verifikasi secara jujur
- bila notifikasi internal nanti dibutuhkan untuk klaim atau persetujuan akun, lahirkan diskusi notifikasi terpisah setelah domain akun cukup stabil
- MFA sedang di-hold, jadi jangan ubah lagi alur atau schema MFA sampai user membuka hold itu

## Pertanyaan Terbuka

- apakah role minimum yang wajib ada pada versi awal website -> **Dijawab:** `admin` (=Super Admin), `koordinator`, `wakil koordinator`, `personel` (`Kep.006`). Cukup 4.
- apa pembeda normatif antara `super admin` dan `admin` -> **Dijawab:** `admin` adalah daily-term untuk Super Admin (`Kep.006`); keduanya dipisah hanya bila lahir keputusan terpisah (klarifikasi 2026-07-12).
- apakah personel membuat akun sendiri lewat klaim atau akun dibuat oleh admin -> **Dijawab:** keduanya. Registrasi mandiri NIKC-first (T1); admin bisa pre-load entry lewat SK (T3, nicety).
- apakah klaim akun berbasis NIKC yang sudah ada di sistem harus dibedakan dari pendaftaran baru berbasis NIKC yang belum ada -> **Dijawab:** TIDAK dibedakan. NIKC diasumsikan sudah di `entries` (admin input SK); kalau tidak ketemu -> cabang upload-SK (T3).
- apakah semua user memakai halaman login yang sama -> **Dijawab:** ya, satu halaman. Identifier: NIKC atau username (T5).
- apakah verifikasi personel wajib ada pada fase awal atau bisa ditunda -> **Dijawab:** wajib (gating `is_active` lewat admin/koordinator).
- kapan verifikasi wajah awal boleh diaktifkan pada jalur registrasi mandiri -> **Dijawab:** yang dipakai = **KYC Pengajuan** dengan artefak **Swafoto Pengajuan** (diambil saat daftar) untuk mengetahui siapa yang mengajukan; **WAJIB secara keputusan**, tetapi **BELUM aktif di kode** (`EnsureFaceVerified` masih dead code -> lihat Kondisi Kode Aktual). **Face Recognition / Pencocokan Wajah Otomatis** DITUNDA/HOLD (`Kep.004`). Ini BUKAN verifikasi wajah. Lihat T5.
- apakah verifikasi wajah awal wajib terjadi saat registrasi, sebelum approval admin, atau setelah approval admin namun sebelum akses penuh -> **Dijawab:** Swafoto Pengajuan (KYC) saat ajukan registrasi; approval admin/koordinator mengaktifkan `is_active` (T5). Catatan: `EnsureFaceVerified` masih dead code -> belum aktif (lihat Kondisi Kode Aktual).
- siapa yang berwenang menonaktifkan akses login seorang personel -> **Dijawab:** admin (dan koordinator per batas otoritasnya per `Kep.006`).
- apakah MFA Google Authenticator wajib di fase awal -> **Dijawab:** TIDAK. MFA = 2FA, status HOLD (`Kep.004`); fitur **PENTING yang dipertahankan** tetapi **yatim/dormant** (field & route tetap ada, tak direachable, tak dipakai hingga keputusan baru). Tidak dihapus. Lihat T5/T7 + Rencana Perubahan File.
- apakah role minimum versi awal cukup `admin`, `koordinator`, `wakil koordinator`, dan `personel` -> **Dijawab:** ya, cukup 4 (`Kep.006`).
- apakah registrasi mandiri publik memang hanya dibuka untuk jalur `Reguler`, atau sejak awal harus mendukung `ASN` dan `SPPI` juga -> **Dijawab:** SEMUA abituren boleh daftar (Reguler/ASN/SPPI); `GrupAngkatan::abiturenOptions()` + field `abituren` di form (T5). Skenario SPPI: NIKC tetap 3 walau pangkat naik Prada->Letda (`Kep.003` + arsip Identitas Grup Angkatan).

## Rencana Tindak Lanjut

- [x] menurunkan arahan user tentang registrasi mandiri tanpa grup ke rumusan final diskusi (lihat T1-T3)
- [x] menurunkan temuan audit bahwa registrasi publik saat ini masih hardcode ke jalur `Reguler`, sehingga dukungan `ASN` dan `SPPI` belum final (lihat T5 + Kondisi Kode Aktual)
- [x] menunggu arahan user tentang role minimum dan pola aktivasi akun (role: `Kep.006`; pola: T1-T3/T5)
- [x] menyelaraskan hasilnya dengan diskusi master data personel (`Kep.004`) dan struktur (`Kep.006`)
- [ ] finalisasi: substansi diskusi sudah lengkap & akurat vs kode, tetapi ada gap implementasi (KYC Pengajuan/Swafoto Pengajuan dead, drift `two_factor_*`, `is_komcad`/`nrp` absent) yang perlu diselesaikan lewat Rencana Kerja SETELAH keputusan lahir -- bukan blocker keputusan, tetapi harus tercatat di keputusan sebagai pekerjaan turunan

## Temuan Analisis (2026-07-13, fase bertahap menuju tujuan 1)

Catatan ini menyintesis hasil baca file repo + koreksi user, bertujuan memetakan scope finalisasi
tanpa mengubah status diskusi. Semua masih level `04 Diskusi`.

### T1. Logika NIKC-first sudah dikoreksi (bukan hard-fail digit-1)
- NIKC digit-1 = **kode kelompok pangkat AWAL/klaim**, bukan pangkat saat ini.
- Skenario SPPI: Prada KC A (NIKC awali 3, Matra Darat 2021) -> Latsarmil SPPI 2025
  ditetapkan Letda KC A (Matra Laut), **NIKC TIDAK berubah, tetap awali 3**.
- Jadi NIKC kode 3 BOLEH milik personel Letda (lewat SPPI) selama NIKC tetap 3.
- Alur register yang BENAR:
  1. NIKC di-input -> sistem **prefill pangkat terendah kelompok** (kode 3 -> `Prada KC`) = default penetapan awal/klaim awal.
  2. Personel BOLEH ubah ke Letda (masih kode 3, valid SPPI) -> ini BUKAN mismatch NIKC.
  3. Yang memicu notif + **field verifikasi baru** bukan "NIKC beda", tapi **personel meng-OVERRIDE prefill**.
     Karena transisi (bukan klaim awal), maka wajib **pengajuan (upload SK)** -> selaras `Kep.005` (mekanisme pengajuan/penyesuaian pangkat berbasis SK).
  4. Mismatch NIKC BARU hanya terjadi kalau pilih pangkat dari **kode kelompok lain** (mis. NIKC 3 tapi pilih Kapten kode 1).
- Tindakan: `NikcFormatRule::validatePangkat` dilunakkan dari *tolak* menjadi *flag "pangkat di-override -> wajib pengajuan"*.

### T2. Surat Keputusan membawa nama lengkap + tempat & tanggal lahir (sudah dibahas)
- Diskusi Master Data (diarsipkan `[DITETAPKAN]` -> lahir `2026.Kep.004`) membahas ekstraksi:
  nama, lahir, pangkat, NIKC, pendidikan, TMT penetapan (CHANGELOG baris 106-107).
- Spesifikasi ekstraksi PDF (CHANGELOG 107): field utama = nomor SK, tanggal, judul,
  **nama, lahir (tempat+tanggal), pangkat, NIKC, pendidikan, TMT penetapan**.
- GAP implementasi: `SuratKeputusanController::parseEntryLines` hari ini hanya parse
  `nikc|full_name|target_pangkat`. **Tempat/tanggal lahir + pendidikan BELUM diekstrak.**
  Agar cross-check pob/dob bisa jalan, `surat_keputusan_personel_entries` perlu kolom `pob`/`dob`
  + `parseEntryLines` diperluas. Ini keputusan finalisasi, bukan blocker teknis.

### T3. Prasyarat admin-input SK dilunakkan (bukan wajib) + jalur Ijazah sebagai opsi ringan
- Admin pre-load entries = **nicety**, bukan syarat wajib.
- Tanpa itu, pendaftar jatuh ke cabang **upload-SK** (lebih berat, tapi jalan): admin verifikasi dasar pembentukan grup.
- **Arah user (2026-07-13):** cabang upload-SK dianggap **terlalu berat** untuk calon yang cuma mau mendaftar.
  Maka disediakan **jalur lebih ringan & opsional**: kalau calon tak punya/tak sanggup upload SK,
  cukup **unggah Ijazah Latsarmil / Pendidikan Militer** sebagai bukti status keanggotaan Komcad
  -> tetap boleh lanjut ke status PENDING (tidak langsung ditolak). Ijazah Latsarmil menjadi
  **jalur mandiri yang sah** (bukti selesai pendidikan dasar militer), bukan sekadar pengganti SK.
- **PEMISAHAN ISTILAH (penting):** "Ijazah" di sini = **Ijazah Latsarmil / Pendidikan Militer**
  (bukti keanggotaan Komcad), **BUKAN** Ijazah Pendidikan Umum/Akademik (S1/S2/S3/D4, dll).
  Ijazah akademik adalah dokumen **pendidikan** yang menjadi sumber **suffix gelar** (#5 T1/T2, #9),
  dan tidak dipakai sebagai bukti keanggotaan di cabang registrasi ini. Jangan campur kedua jenis ijazah.
- Jadi **Ijazah Latsarmil = dokumen wajib** di jalur no-match / override / no-SK, DAN **opsi ringan**
  ketika upload-SK terlalu berat. Aturan "ijazah kondisional akademik" (#5 T2) tetap berlaku terpisah:
  SK sudah ada gelar -> tak perlu ijazah akademik; edit profil mau nyantumkan gelar -> perlu ijazah akademik.
- Implikasi UI: form registrasi memberi **dua opsi bukti keanggotaan** (SK penetapan ATAU Ijazah Latsarmil),
  bukan hanya satu jalur kaku. Ijazah akademik diisi di bagian riwayat pendidikan (#9), terpisah.

### T4. Prioritas finalisasi untuk tujuan 1 (login + verifikasi + manajemen dasar)
- MATANG & SIAP FINALISASI (urutan aman #12 -> #9 -> #5 -> #2 -> #8): #2 (akun/login/role/verifikasi), #5 (format nama), #9 (riwayat pendidikan), #12 (repositori dokumen).
- **#8 (OCR/Import): keputusan DOMAIN sudah siap, tapi eksekusi SK import = fase lanjut via queue** (lihat #8 T4: OCR KTP/KTA/Ijazah MVP, SK import fase lanjut). Jangan masukkan #8 ke "segera deploy" sebelum #9/#12 matang.
  OCR boleh hold), #9 (upload Ijazah wajib minimalis), #12 (repositori dokumen evidence).
- **CATATAN URUTAN:** #8 (OCR/Import) = domain matang tapi **eksekusi SK import fase lanjut**; jadi urutan finalisasi aman = **#12 -> #9 -> #5 -> #2 -> #8** (selaras analisis user 2026-07-13), BUKAN "semua segera".
- DEFER (bukan blocker tujuan 1): #1 Broadcast/Presensi/Monitoring/Laporan, #3 Face Recognition (sudah HOLD Kep.004),
  #4 Role Lapangan & Struktur (sudah Kep.006), #6 Riwayat Pekerjaan, #7 Soft Skill, #10 Notifikasi (prinsip di Kep.006),
  #11 Cetak Ulang KTA, #13 Riwayat Jabatan, #14 Riwayat Penugasan, #15 GitHub/Gitea/Deploy.
- Dari 15 diskusi, cukup **5 yang difinalkan** (#2,#5,#8,#9,#12) untuk capai tujuan 1.
  Kep.003 (NIKC), Kep.004 (master data), Kep.006 (struktur) sudah final jadi landasan.

### T5. Keputusan #2 yang diminta user (identitas, biometrik, MFA)
- Login identifier (keputusan user): **NIKC atau username** (dua-duanya diterima pada satu halaman login). **NIK (16 digit) BUKAN identifier login** — NIK hanya untuk profil internal personel (pencocokan data, OCR KTP -> `personels.nik`), tidak dipakai untuk masuk akun.
  - Catatan lintas-kelas: `Super Admin` non-Komcad tidak punya NIKC -> login memakai **username** (lihat T6).
- **KYC Pengajuan & Swafoto Pengajuan (istilah seragam, keputusan user):**
  - **KYC Pengajuan** = proses payung untuk mengetahui/membuktikan **siapa yang mengajukan** (identitas pengaju) saat registrasi. Ini BUKAN "verifikasi wajah".
  - **Swafoto Pengajuan** = artefak/aksi: pengaju mengambil swafoto saat mendaftar sebagai bukti dialah personel sah yang mengajukan. Ini interaksi utama yang dilihat user di flow.
  - **WAJIB secara keputusan**, tetapi **BELUM aktif di kode** (`EnsureFaceVerified` masih dead code -> lihat "Kondisi Kode Aktual"). Pengaktifan = pekerjaan implementasi.
  - **Face Recognition / Pencocokan Wajah Otomatis (matching AI)** = **DITUNDA/HOLD** (`Kep.004`). KYC Pengajuan TIDAK melakukan pencocokan biometrik; hanya merekam bukti identitas pengaju.
  - Istilah lama yang DIPENSIUNKAN agar seragam: "verifikasi wajah", "Selfie Check", "Biometric Capture/Enrollment" -> semua diganti **KYC Pengajuan** (proses) + **Swafoto Pengajuan** (artefak).
- **MFA (2FA):** status **HOLD**; fitur **PENTING** yang **dijaga tetap ada** (file/route/field tidak dihapus) tetapi **dijadikan yatim/dormant** — tidak ditaut dari UI/menu, tidak reachable dari flow aktif, tidak digunakan hingga ada keputusan baru yang mengaktifkannya. Tidak ada perubahan skema MFA (`two_factor_*` tetap ada di model/controller/route; hanya tak diakses).
- Verifikasi gating `is_active` (sudah di RoleMiddleware + VerificationController).
- Otoritas **koordinator** memverifikasi dokumen hilang (KTA/Ijazah), bukan hanya admin.
- Foto KYC (Swafoto Pengajuan, live capture getUserMedia) terpisah dari foto profil (upload).
- KTP/KTA opsional; Ijazah kondisional wajib (no-match/override/no-SK).
- Abituren semua boleh daftar (Reguler/ASN/SPPI) -> `GrupAngkatan::abiturenOptions()` + field `abituren` di form.

### T6. Kelas identifier Super Admin non-Komcad (rujuk keputusan final, menutup gap)
- Registrasi mandiri **hanya untuk personel Komcad** ber-NIKC (`2026.Kep.006` klausul 20).
- `Super Admin` non-Komcad **tidak self-register**; didaftarkan oleh `Super Admin` lain lewat dashboard (`2026.Kep.006` klausul 20).
- Identifier `Super Admin` non-Komcad: **NRP** (eks Komponen Utama) atau **NIK** (bukan Komponen Utama), sesuai `2026.Kep.006` klausul 16-19 + amandemen `2026.Kep.003` klausul 16. `nikc` nullable bagi mereka.
- Konsekuensi login: "NIKC atau username" berlaku untuk Komcad; Super Admin non-Komcad login via **username**.
- Turunan operasional visibilitas data koordinator/hak multi-grup ada di `docs/11 Referensi Teknis/2026.11.004 tentang Turunan Operasional Hak Multi-Grup Angkatan.md` (turunan `Kep.006`) -- relevan saat #2 menyentuh batas akses dashboard.

### T7. Drift implementasi yang HARUS diselaraskan saat implementasi (jujur, bukan "beres")
- **Login identifier:** keputusan = NIKC/username, tetapi runtime saat ini masih mengarahkan **NIK** (pesan approval `ApproveRegistrationAction.php:58` & `VerificationController.php:129`: "login menggunakan NIK"). -> perlu diselaraskan ke NIKC/username saat implementasi.
- **MFA route hidup:** `routes/web.php:66-67` masih mendaftarkan `profile.mfa*` walau status HOLD. -> jadikan yatim (T5), catat sebagai drift terbuka, bukan sudah tuntas.
- **FORMAT_FINAL nama+gelar:** lihat #5 -- belum ada `display_name`/`formatName` di kode; `full_name` masih plain string(150). Diskusi-only, belum diimplementasi.

## Rencana Perubahan File (Fresh Install DB, dieksekusi SETELAH keputusan lahir)

> Target = **fresh install database**: deploy dari nol (`migrate:fresh`), jadi **migration harus self-contained & utuh**
> — setiap migration `create_*` ditulis ulang agar **state akhirnya sudah benar langsung**, TANPA perlu
> migration `alter`/`add_*`/`drop_*` tambahan saat di-deploy. Kolom yang dibutuhkan (mis. `two_factor_*`,
> `is_komcad`, `nrp`, sub-status) **langsung ada di migration `create_*` aslinya**.
> Blok ini HANYA rencana; tidak ada kode disentuh selama status masih `04 Diskusi` (`Kep.001` klausul 24).

### A. Migration (tulis ulang utuh, self-contained — tidak ada alter/add/drop terpisah)
| File | Aksi |
| --- | --- |
| `database/migrations/2026_07_06_055601_create_personels_table.php` | Tulis ulang agar **sudah berisi**: (1) `is_komcad` (boolean, default true) + `nrp` (string, nullable) per `Kep.006` klausul 16-19; (2) `nikc` **nullable** (Super Admin non-Komcad tak punya NIKC); (3) `two_factor_enabled` (boolean) + `two_factor_secret` + `two_factor_recovery_codes` **langsung ada** (MFA dipertahankan, dormant — bukan dihapus, agar tak error & siap aktif tanpa alter). |
| `database/migrations/2026_07_06_055620_create_registrations_table.php` | Tulis ulang agar sudah berisi kolom sub-status alur KYC/SK: `jalur` (enum match/mismatch/no_sk), `requires_manual_check` (boolean), `verified_by_role` (string) — agar alur T1/T3 tegak tanpa migration tambahan. |
| `database/migrations/2026_07_06_055639_create_face_verifications_table.php` | RENAME konsep -> **KYC Pengajuan**. Tulis ulang: tabel `kyc_submissions`, kolom `swafoto_path` (pengganti `verification_image`), `kyc_verified_at` (pengganti `verified_at`). Full rename langsung di migration (aman karena fresh install). |

### B. Model & Controller (selaraskan dengan keputusan; MFA TETAP dipertahankan)
| File | Aksi |
| --- | --- |
| `app/Models/Personel.php` | TIDAK hapus `two_factor_*`. TAMBAH `is_komcad`, `nrp` ke fillable; sesuaikan relasi KYC (`face_verifications` -> `kyc_submissions`) bila tabel di-rename. |
| `app/Http/Controllers/Auth/AuthController.php` | (1) **Biarkan** `two_factor_enabled` (MFA dormant, bukan dihapus); (2) login: terima **NIKC atau username** (bukan NIK); (3) pesan pasca-approve: ganti "login menggunakan NIK" -> identifier final (NIKC/username). |
| `app/Actions/ApproveRegistrationAction.php` (baris 58) | Ganti teks "login menggunakan NIK" -> identifier final (NIKC/username). (Tidak hapus MFA.) |
| `app/Http/Controllers/Admin/VerificationController.php` (baris 129) | Idem pesan approve. |
| `app/Http/Controllers/Admin/MasterPersonelController.php` (baris 157, 317) | **Biarkan** `two_factor_enabled` (tampil di form admin saja, tidak dipakai flow aktif). |
| `app/Http/Controllers/Profile/ProfileController.php` | `toggleMfa`/`verifyMfa` **TETAP ADA** (MFA dipertahankan) tetapi **tidak ditaut** dari UI/menu apa pun -> dormant. (Tidak dihapus.) |

### C. Routing & Middleware (MFA dormant, KYC diaktifkan)
| File | Aksi |
| --- | --- |
| `routes/web.php` (baris 66-67) | Route `profile.mfa` + `profile.mfa.verify` **TETAP ADA** (MFA dipertahankan) namun **tidak direach** dari UI/menu -> dormant. (Tidak dihapus.) |
| `bootstrap/app.php` | TAMBAH alias middleware KYC (rename dari `EnsureFaceVerified`) bila KYC diaktifkan. |
| `app/Http/Middleware/EnsureFaceVerified.php` | RENAME -> `EnsureKycSubmitted` (atau serupa); perbaiki agar redirect ke route KYC yang benar; **pasang** di grup `role:personel`. |
| `routes/web.php` (grup personel) | TAMBAH route `personel.kyc` (pengganti `personel.face-verification` yang belum ada) + controller/view Swafoto Pengajuan. |

### D. Penamaan/istilah (seluruh kode & view)
- Ganti semua sebutan `face_verification` / `EnsureFaceVerified` / "verifikasi wajah" -> **KYC Pengajuan** / **Swafoto Pengajuan** (`kyc` / `swafoto`), agar seragam dengan keputusan. Sebutan `two_factor_*`/MFA **dipertahankan** (fitur penting, dormant).

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-13 | KOREKSI terminologi Ijazah (#2 T3): "Ijazah" di cabang registrasi = **Ijazah Latsarmil / Pendidikan Militer** (bukti keanggotaan Komcad), BUKAN ijazah akademik. Tambah pemisahan eksplisit dari ijazah akademik (#5 T2/#9, sumber gelar). Form registrasi: 2 opsi bukti keanggotaan (SK ATAU Ijazah Latsarmil). Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-13 | Perluas T3: cabang "NIKC belum ada" -> upload-SK dianggap terlalu berat, maka ijazah jadi **jalur ringan & opsional** (bukti identitas dasar, tetap PENDING, tak langsung ditolak), bukan sekadar pengganti SK. Form registrasi beri 2 opsi bukti (SK penetapan ATAU Ijazah). Selaras #5 T2 (ijazah kondisional). Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-13 | Audit konsistensi pasca revisi MFA: perbaiki 3 sisa istilah lama di #2 agar selaras arah "MFA dipertahankan/dormant" (bukan hapus). Pertanyaan Terbuka MFA (baris 311) dikoreksi dari "dihapus bersih" -> "yatim/dormant, tidak dihapus"; snapshot routing (261) & tabel `face_verifications` (280) diselaraskan + sebut rename `kyc_submissions`. Cross-doc ke Kep.004/005/006 + relasi #2<->#5 terverifikasi SELARAS (tanpa drift). Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-13 | FINALISASI: diskusi dinyatakan final oleh user; status -> `Siap Difinalkan`; diarsipkan `[SELESAI]` & melahirkan `2026.Kep.009 tentang Akun, Login, Logout, Role, dan Verifikasi Anggota`. Tanpa sentuh kode |
| 2026-07-13 | KONSISTENSI INTERNAL: baris 34-37 `Kondisi repo yang terverifikasi` ditandai snapshot 3 Juli (bukan stale) — Kep.006 & auth sudah ada per 13 Juli (lihat T6). Tanpa sentuh kode |
| 2026-07-13 | KOREKSI user: login = NIKC atau username; **NIK (16 digit) BUKAN identifier login**, hanya profil internal personel (#12 T7). KTP berlaku seumur hidup (tidak kadaluarsa). Tanpa sentuh kode |
| 2026-07-13 | AUDIT FIX B.1-B.7: B1 #8 T5 basi "belum ada" -> sudah ada; B4 #2 T4 urutan finalisasi dikoreksi jadi #12->#9->#5->#2->#8 (bukan "semua segera"); B7 #9 field `«redacted»`->`sk_penetapan_id`. Tanpa sentuh kode |
| 2026-07-13 | Bersihkan metadata & kontradiksi pra-finalisasi: tambah `Kep.006` ke `Keputusan terkait`; baris 220 "Definisi role dasar: Belum diputuskan" -> diatur Kep.006 (4 role + Super Admin). Selaras analisis user. Tanpa sentuh kode |
| 2026-07-13 | KOREKSI arah MFA (revisi keputusan user): MFA = fitur PENTING yang **dipertahankan**, cukup **yatim/dormant** (tidak dihapus, tidak reachable, tak dipakai hingga keputusan baru). Blok "Rencana Perubahan File" diperjelas = fresh-install **database** -> migration `create_*` **self-contained utuh** (state akhir benar langsung, tak perlu alter/add/drop). `two_factor_*` **diduplikasi ke migration `personels`** agar tak error. Membatalkan arah "hapus bersih MFA" di catatan sebelumnya. Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-13 | Seragamkan istilah (keputusan user): "verifikasi wajah/Selfie Check/Biometric Capture" -> **KYC Pengajuan** (proses) + **Swafoto Pengajuan** (artefak); Face Recognition/Pencocokan Wajah Otomatis tetap HOLD & bukan bagian KYC. Target fresh install ditetapkan -> MFA `two_factor_*` + route `profile.mfa*` dihapus bersih (arah ini dibatalkan di revisi berikutnya). Tambah blok "Rencana Perubahan File (Fresh Install)": daftar migration/model/controller/route + aksi (tambah `is_komcad`/`nrp`, `nikc` nullable, sub-status `registrations`, rename `face_verifications`->`kyc_submissions`). Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-13 | Rapikan konsistensi teks temuan (tanpa sentuh kode): koreksi Pertanyaan Terbuka verifikasi wajah dari "sudah AKTIF" -> "WAJIB secara keputusan, BELUM aktif di kode" (selaras snapshot dead-code); perbaiki typo (`keduaunya`->keduanya, `tIDAK`->tidak, `membatwa`->membawa, `[DISETAPKAN]`->`[DITETAPKAN]`); mutakhirkan Rencana Tindak Lanjut menandai poin terselesaikan + catat gap implementasi sebagai pekerjaan turunan pasca-keputusan. Tetap `04 Diskusi` |
| 2026-07-13 | Tambah blok "Kondisi Kode Aktual" (snapshot routing/middleware/schema) + 4 drift schema untuk memudahkan implementasi saat lahir keputusan. KOREKSI PENTING: `EnsureFaceVerified` ternyata DEAD CODE (tak di-alias/tak dipasang/route target tak ada) -> Selfie Check BELUM aktif; `two_factor_*` dipakai model+controller TAPI kolomnya tak ada di migration `personels`; `is_komcad`/`nrp` belum ada. Klaim T5 sebelumnya ("Selfie Check aktif") dikoreksi. Tetap `04 Diskusi` |
| 2026-07-13 | Koreksi pasca re-audit vs kode aktual: (T5) login = NIKC/username (keputusan user); terminologi biometrik dipertegas (Biometric Capture/Enrollment + Selfie Verification/Check WAJIB & aktif via `EnsureFaceVerified`, Face Recognition matching DITUNDA); MFA hold->dinonaktifkan->yatim (bukan dihapus/dibersihkan). (T6) tambah kelas identifier Super Admin non-Komcad (NRP/NIK) rujuk `Kep.006` klausul 16-20 + amandemen `Kep.003` klausul 16; rujuk `2026.11.004`. (T7) catat drift: runtime masih arahkan NIK, route `profile.mfa*` masih hidup, FORMAT_FINAL belum diimplementasi. Tetap `04 Diskusi` |
| 2026-07-13 | Menandai 5 Pertanyaan Pending + Acceptance Criteria sebagai Dijawab: role dasar (`Kep.006`), `super admin`=`admin` (klarifikasi 12 Jul), login/logout + MFA hold (`Kep.004`), pola klaim/aktivasi (T1-T3), verifikator (koordinator untuk dokumen hilang, admin sisanya). Permatangan #5 (format nama/gelar) sebagai rujukan display. Tetap level `04 Diskusi`, belum final |
| 2026-07-12 | Menambahkan temuan audit bahwa registrasi publik masih efektif hanya untuk jalur `Reguler`, menjelaskan posisi governance `MFA` sebagai `hold` di `2026.Kep.004` dengan rumah diskusi tetap di domain akun, serta mencatat drift istilah `super admin` versus role runtime aktif |
| 2026-07-12 | Klarifikasi user: `Super Admin` adalah role namun disebut `admin` dalam sebutan harian; pemisahan keduanya kelak hanya terjadi bila lahir keputusan terpisah |
| 2026-07-12 | Ditambahkan jalur registrasi mandiri saat grup angkatan belum ada di master melalui unggah Surat Keputusan, serta dipisahkan antara verifikasi wajah awal dan face recognition otomatis |
| 2026-07-12 | Menyelaraskan scope diskusi dengan 2026.Kep.003 dan 2026.Kep.004, sehingga pembahasan akun tidak lagi mengulang domain NIKC, TMT Penetapan, atau Grup Angkatan |
| 2026-07-11 | Mencatat bahwa sinkronisasi implementasi role permanen terjadi terlalu dini saat topik masih diskusi, sehingga statusnya adalah drift implementasi dan bukan keputusan lifecycle yang sah |
| 2026-07-11 | Menambahkan hasil sinkronisasi implementasi role permanen: `komandan` diganti ke `koordinator`, `wakil koordinator` memakai dashboard yang sama, dan fresh install tidak memakai alias transisi |
| 2026-07-03 | Dokumen dibuat untuk membahas login, logout, role, super admin, dan verifikasi personel sebelum keputusan final dilahirkan |



