# Rencana Kerja
## tentang SINKRONISASI MASTER DATA DAN STATUS PERSONELS

Status: `Selesai (Diarsipkan)`
Tanggal dibuat: 10 Juli 2026
Tanggal arsip: 12 Juli 2026
Identitas dokumen: 2026.07.10 Rencana Kerja tentang Sinkronisasi Master Data dan Status Personels
Arsip: `docs/00 Arsip/[SELESAI] 2026.07.12 Rencana Kerja tentang Sinkronisasi Master Data dan Status Personels.md`
Jenis dokumen: Rencana Kerja
Domain: kctrimatra
Topik: peleburan users ke personels, sinkronisasi runtime auth/profile/admin, status lifecycle, alamat terstruktur, kontak multi-nomor, pendidikan inti, SINYALEMEN, dan penutupan drift field ganda
Keputusan terkait: `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
Rencana kerja induk: -
Mengubah: -
Digantikan oleh: -

## Konvensi Istilah

- backend dan naming kode memprioritaskan `personel`
- display dan teks user-facing boleh memakai `anggota`
- `SINYALEMEN` adalah istilah user-facing baku; nama teknis kode yang masih bertahan tetap memakai `Sinyalemen` sebagai alias transisi sampai refactor khusus dilakukan

## Instruksi Baca Wajib

Sebelum mengubah file apa pun, baca berurutan:

1. `AGENTS.md`
2. `docs/README.md`
3. `docs/CHANGELOG.md`
4. `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
5. `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
6. `docs/01 Profil Sistem/PROJECT_STATUS.md`
7. `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`

## Tujuan

- melebur `users` ke `personels` sebagai sumber kebenaran tunggal profil personel
- istilah `personel` dan `anggota` boleh dipakai bergantian sebagai sinonim domain; display boleh memakai `anggota`, tetapi backend harus tetap konsisten memakai `personel`
- memindahkan field yang overlap agar tidak ada sumber data ganda pada profil personel
- menyelaraskan runtime auth, profile, admin, export, dan seeding ke `personels`
- menjaga `MFA` dan `avatar` tetap `hold` sampai keputusan tersendiri lahir
- mempertahankan `SINYALEMEN` sebagai domain turunan yang membaca data inti yang sama
- menutup drift kode yang masih membaca atau menulis ke `users`
- menyiapkan dokumen turunan agar tidak lagi menyebut `users` sebagai sumber utama data anggota
- menyiapkan revisi keputusan master data agar `TMT Penetapan` menjadi sumber utama untuk menurunkan `Tahun` pengelompokan grup angkatan
- menyelaraskan makna field `angkatan` agar tidak lagi dibaca sebagai input manual mandiri bila arah normatif final menetapkannya sebagai representasi `Tahun` turunan dari `TMT Penetapan`

## Kondisi Stop

Hentikan pekerjaan jika:

- keputusan `2026.Kep.004` tidak ditemukan atau isinya berubah
- ada kebutuhan menjalankan migration destruktif tanpa backup atau tanpa konfirmasi user
- runtime masih bergantung pada `users` untuk jalur yang belum dipetakan, tetapi pemetaan dependensinya belum lengkap
- perubahan yang dibutuhkan menyentuh scope yang masih `hold`, khususnya `MFA` atau `avatar`
- ditemukan drift baru yang belum tercatat di keputusan atau diskusi terkait
- environment eksekusi belum siap untuk verifikasi minimal dan pekerjaan sudah masuk tahap yang membutuhkan pembuktian

## Klasifikasi Operasi

| Jenis | Contoh | Tindakan |
| --- | --- | --- |
| Reversible | ubah controller, request validation, model, view, docs, seeder | kerjakan dan verifikasi lokal |
| Semi-reversible | tambah migration baru yang hanya menambah struktur | kerjakan dengan verifikasi ekstra |
| Irreversible | drop tabel, drop kolom, backfill yang menghapus nilai lama, ubah constraint yang memutus data existing | minta konfirmasi eksplisit user sebelum eksekusi |

## Status Eksekusi

| Fase | Status | Ringkasan |
| --- | --- | --- |
| 1 | Selesai | inventarisasi drift `users`, `SINYALEMEN`, field hold, schema fresh install, dan kontrak grup angkatan berhasil dipetakan |
| 2 | Selesai | schema fresh install diselaraskan ke keputusan tanpa menjalankan database dan tanpa tabel `users` |
| 3 | Selesai | runtime auth/profile/admin/export diarahkan ke `personels` sebagai model auth tunggal |
| 4 | Berjalan | penguncian dokumen, batch artefak SK turunan, dan catatan QA sebelum arsip |

## Temuan Audit Pra-Perbaikan

- migration awal Laravel dan migration domain sama-sama membuat tabel `users`, sehingga fresh install berisiko gagal karena tabel duplikat
- migration final peleburan `users` masih menahan tabel `users`, padahal repo belum diinstall sehingga tabel tersebut aman dicabut dari target fresh install
- `personels` belum memuat seluruh field keputusan untuk `abituren`, `religion`, pendidikan inti, status lifecycle, dan atribut SK
- nomor HP multi-nomor belum punya tabel terpisah dengan status aktif atau nonaktif
- perubahan status lifecycle belum punya tabel riwayat perubahan status
- form register masih memakai gender teknis `L/P`, sementara keputusan menetapkan label profil `Pria/Wanita`
- profile masih membuka jalur `avatar` dan `MFA`, padahal keduanya berstatus `hold`
- runtime sempat menghidupkan `face verification` sebagai gerbang login dan prasyarat sebelum `SINYALEMEN`, padahal keputusan aktif belum pernah memfinalkan tujuan, status data, maupun artefak biometriknya
- beberapa teks UI dan komentar masih memakai nama teknis `Sinyalemen` sebagai alias transisi, padahal istilah baku dokumen dan user-facing adalah `SINYALEMEN`
- file aktif masih memiliki pembaca `users`; bagian ini harus dicabut karena keputusan sudah menetapkan `personels` sebagai sumber tunggal dan belum ada database produksi yang perlu dipreservasi
- keputusan master data aktif masih menempatkan `angkatan` sebagai field inti personel, sementara diskusi struktur organisasi sudah menegaskan bahwa `Tahun` grup diambil dari `TMT Penetapan`
- `TMT Penetapan` di schema sudah tersedia tetapi masih `nullable` dan belum menjadi sumber utama validasi, input UI, atau pembentukan makna `Tahun`
- UI registrasi dan admin masih meminta input manual `Tahun Angkatan`, sehingga istilah dan perilaku aktif belum selaras dengan arah revisi normatif
- fitur aktif seperti filter personel dan target broadcast masih membaca `angkatan` sebagai atribut langsung, belum sebagai representasi `Tahun` yang diturunkan dari `TMT Penetapan`

## Temuan Audit Lanjutan - `TMT Penetapan` dan `Tahun`

- arah revisi yang diminta user adalah memasukkan aturan `TMT Penetapan -> Tahun` ke keputusan master data aktif, bukan menunggu keputusan baru
- substansi diskusi struktur organisasi sudah cukup jelas: `TMT Penetapan` adalah sumber tanggal utama, `Tahun` diambil dari tanggal tersebut, struktur unik grup tetap `Matra-Tahun-Batch`, dan `batch` tetap opsional
- keputusan master data aktif belum mencerminkan arah ini secara cukup tegas karena `TMT Penetapan` masih diposisikan sebagai field opsional, sedangkan `angkatan` masih muncul sebagai field inti tanpa penegasan makna turunannya
- implementasi aktif juga belum selaras karena schema, request validation, controller, dan UI masih meminta atau memproses `angkatan` sebagai input primer
- diskusi lanjutan pada 11 Juli 2026 memperjelas bahwa istilah `TMT Penetapan` dapat muncul berulang pada dokumen yang berbeda, sehingga konteks sumber kebenarannya harus dipisahkan dengan tegas
- `TMT Penetapan` pada keputusan penetapan anggota Komponen Cadangan reguler adalah sumber kebenaran resmi untuk status sah anggota dan menjadi dasar `angkatan` yang berlaku bagi personel
- `TMT Penetapan` pada SK penyesuaian ijazah atau kenaikan pangkat hanya berlaku untuk perubahan pangkat dan tidak boleh mengubah `angkatan` personel yang sudah ditetapkan pada penetapan awal
- contoh normatif yang harus dipertahankan: personel `Matra Laut 2025` dengan penetapan awal 20 November 2025 tetap berada pada grup `Matra Laut 2025`, meskipun pada 2026 memperoleh SK penyesuaian ijazah yang mengubah pangkat dari `Prada KC` menjadi `Serda KC`

## Kebutuhan Revisi Keputusan

- `TMT Penetapan` ditegaskan sebagai `Terhitung Mulai Tanggal Penetapan`
- `TMT Penetapan` disimpan sebagai tanggal lengkap dengan format tanggal sistem
- `Tahun` untuk kebutuhan grouping diambil dari tahun pada `TMT Penetapan`
- `Tahun` tidak lagi diposisikan sebagai input mandiri yang berdiri sendiri dari `TMT Penetapan`
- struktur unik grup angkatan tetap `Matra-Tahun-Batch`
- `batch` tetap bersifat opsional
- perlu dibedakan secara normatif antara `TMT Penetapan` untuk penetapan awal anggota Komponen Cadangan dan `TMT Penetapan` pada SK perubahan pangkat berikutnya
- `TMT Penetapan` penetapan awal adalah sumber kebenaran `angkatan` dan/atau `tahun pelantikan selesai pendidikan` yang tetap melekat pada personel
- `TMT Penetapan` pada penyesuaian ijazah atau kenaikan pangkat hanya mengubah konteks riwayat pangkat dan `TMT` pangkat terbaru, bukan `angkatan`
- keputusan hanya mengunci arah data dan istilah; pilihan implementasi apakah `Tahun` disimpan sebagai field turunan, dihitung dinamis, atau dibentuk lewat mekanisme schema diturunkan ke batch implementasi berikutnya

## Audit Khusus - Agar `Matra-Tahun-Batch` Benar-Benar Hidup

Temuan audit khusus pada 11 Juli 2026:

- dokumen aktif sudah menegaskan struktur `Matra-Tahun-Batch`, tetapi schema aktif baru berhenti pada `matra`, `tmt_penetapan`, dan representasi `angkatan`
- repo belum memiliki entitas formal `grup angkatan` yang menyimpan kombinasi `matra`, `tahun`, dan `batch`
- repo belum memiliki field `batch` pada personel maupun tabel grup, sehingga contoh seperti `Matra Darat 2021 Batch 1` belum bisa direpresentasikan secara native
- repo belum memiliki constraint database yang menegakkan keunikan `Matra-Tahun-Batch`
- relasi koordinator dan wakil koordinator masih hidup pada role, tetapi belum terikat ke entitas grup angkatan formal
- target broadcast aktif masih berbasis `matra` dan `angkatan`, belum berbasis grup angkatan yang dapat memilih kombinasi `matra-tahun-batch`
- UI aktif masih bisa menampilkan `Tahun Angkatan`, tetapi belum bisa menampilkan identitas grup formal seperti `Matra Laut 2023` atau `Matra Darat 2021 Batch 1`

Hal yang perlu lahir agar `Matra-Tahun-Batch` hidup di schema:

- tabel induk grup angkatan, minimal memuat:
  - `matra`
  - `tahun`
  - `batch`
  - `tmt_penetapan_awal`
  - identitas koordinator aktif
  - identitas wakil koordinator aktif
  - metadata status grup bila diperlukan
- constraint unik untuk kombinasi `matra`, `tahun`, dan `batch`
- aturan yang jelas untuk `batch` kosong atau `null` agar keunikan tetap konsisten
- relasi personel ke satu grup angkatan aktif
- tabel riwayat perpindahan grup angkatan bila perpindahan personel perlu diaudit
- tabel riwayat pergantian koordinator dan wakil koordinator per grup angkatan

Hal yang perlu lahir agar `Matra-Tahun-Batch` hidup di UI dan runtime:

- pilihan grup angkatan formal pada create/edit personel
- tampilan identitas grup angkatan yang konsisten di daftar personel, verifikasi, dan profil ringkas
- filter admin berdasarkan grup angkatan, bukan hanya `matra` atau `tahun` terpisah
- target broadcast berdasarkan grup angkatan formal
- teks user-facing yang konsisten:
  - `TMT Penetapan Awal` untuk sumber tanggal
  - `Tahun Angkatan` untuk tahun turunan
  - `Grup Angkatan` untuk identitas kombinasi `Matra-Tahun-Batch`

Keputusan teknis sementara yang dipakai sampai schema grup lahir:

- field database tetap memakai nama `angkatan` sebagai representasi tahun turunan
- secara konseptual, `angkatan` harus dibaca sebagai alias domain `tahun_angkatan`
- istilah `grup angkatan` belum boleh disamakan dengan field tunggal `angkatan`, karena grup membutuhkan `matra`, `tahun`, dan `batch`

## Fase 1 - Inventarisasi Drift dan Pemetaan Target

### Tujuan

- memetakan semua file yang masih membaca atau menulis ke `users`
- memetakan field `users` yang harus dipindah ke `personels`
- memetakan field yang harus tetap `hold`

### Klasifikasi

- reversible

### File yang disentuh

- `app/Models/User.php`
- `app/Models/Personel.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/Profile/ProfileController.php`
- `app/Http/Controllers/Admin/MasterPersonelController.php`
- `app/Http/Controllers/Admin/DashboardAdminController.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/js/Pages/Auth/Register.vue`
- `resources/js/Pages/Profile/Edit.vue`
- `resources/js/Pages/Admin/Personel/*.vue`
- `docs/01 Profil Sistem/PROJECT_STATUS.md`
- `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Struktur Organisasi dan Grup Angkatan.md`

### Verifikasi fase

- pastikan daftar file yang masih menyentuh `users` lengkap
- pastikan field target tunggal untuk `personels` sudah konsisten dengan keputusan
- pastikan `MFA` dan `avatar` tidak masuk scope cutover ini
- pastikan `SINYALEMEN` dibaca sebagai domain turunan, bukan sumber data baru
- pastikan gap antara `angkatan` sebagai input manual dan `TMT Penetapan` sebagai sumber `Tahun` sudah dipetakan jelas sebelum revisi keputusan diturunkan
- pastikan pemetaan konteks `TMT Penetapan` awal versus `TMT Penetapan` perubahan pangkat tidak tercampur dalam keputusan maupun istilah implementasi

### Langkah jika gagal

- hentikan perumusan cutover
- perbarui pemetaan drift terlebih dahulu
- jangan lanjut ke schema bila daftar pembaca `users` belum lengkap

## Fase 2 - Schema Transisi dan Backfill

### Tujuan

- menyiapkan schema target `personels`
- menampung atribut akun yang akan dilebur
- menyiapkan jalur transisi yang additive sebelum `users` dicabut

### Klasifikasi

- semi-reversible

### File yang disentuh

- kolom akun langsung berada pada base migration `personels`
- migration baru untuk relasi atau kolom pendukung nomor HP multi-nomor
- migration baru untuk status lifecycle jika dibutuhkan
- penghapusan migration khusus `users` dari target fresh install
- `database/migrations/*personels*.php`
- migration atau penyesuaian schema yang terkait `tmt_penetapan` dan representasi `angkatan/tahun`

### Verifikasi fase

- pastikan perubahan schema bersifat additive terlebih dahulu
- pastikan tidak ada field inti yang masih terpecah tanpa alasan normatif
- pastikan `email`, `username`, `password`, dan `role` punya target schema yang jelas di `personels`
- pastikan nomor HP multi-nomor tidak menabrak struktur lama
- pastikan keputusan implementasi tentang `Tahun` tidak diambil diam-diam di schema sebelum revisi keputusan master data benar-benar dikunci
- pastikan schema nantinya dapat membedakan tanggal penetapan awal anggota dari tanggal efektif SK perubahan pangkat bila keduanya sama-sama memakai istilah `TMT Penetapan` di dokumen sumber

### Langkah jika gagal

- batalkan migration yang belum aman
- jangan mempertahankan `users` pada fresh install karena runtime sudah dicutover ke `personels`
- jika perlu, pecah migration menjadi batch yang lebih kecil

## Fase 3 - Cutover Runtime

### Tujuan

- mengalihkan baca-tulis utama dari `users` ke `personels`
- menyelaraskan auth, profile, admin, export, dan seeder
- menghapus dependency runtime yang masih menjadikan `users` sebagai sumber utama

### Klasifikasi

- reversible untuk sebagian besar perubahan kode
- semi-reversible untuk perubahan schema yang menyertai cutover

### File yang disentuh

- `app/Actions/ApproveRegistrationAction.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/Profile/ProfileController.php`
- `app/Http/Controllers/Admin/MasterPersonelController.php`
- `app/Http/Controllers/Admin/VerificationController.php`
- `app/Http/Controllers/Admin/DashboardAdminController.php`
- `app/Http/Requests/Auth/RegisterPersonelRequest.php`
- `app/Models/Role.php`
- `app/Export/PersonelExport.php`
- `database/seeders/DatabaseSeeder.php`
- `resources/js/Pages/Auth/Register.vue`
- `resources/js/Pages/Profile/Edit.vue`
- `resources/js/Pages/Admin/Personel/Create.vue`
- `resources/js/Pages/Admin/Personel/Edit.vue`
- `resources/js/Pages/Admin/Verification/Index.vue`
- `app/Http/Controllers/Admin/BroadcastController.php`
- `resources/js/Pages/Admin/Broadcast/Create.vue`

### Verifikasi fase

- pastikan login, register, edit profil, dan approval tidak lagi bergantung pada `users` sebagai sumber utama
- pastikan `personels` menjadi prioritas baca-tulis
- pastikan fallback lama dihapus jika sudah tidak dibutuhkan
- pastikan export dan seeder membaca field yang sama dengan schema target
- pastikan `SINYALEMEN` tetap menarik data inti yang sama tanpa duplikasi
- pastikan UI dan validasi tidak lagi meminta `Tahun Angkatan` sebagai sumber input utama bila keputusan revisi sudah diberlakukan
- pastikan fitur filter, grouping, dan target operasional tidak lagi memperlakukan `angkatan` sebagai data primer yang berdiri sendiri dari `TMT Penetapan`
- pastikan perubahan pangkat berbasis SK baru tidak mengubah grup angkatan awal personel

### Langkah jika gagal

- kembalikan cutover sebagian yang belum stabil
- jangan lanjut ke penghapusan `users` sebelum seluruh jalur runtime aman
- catat file yang masih bergantung pada `users` untuk batch berikutnya

## Fase 4 - Penguncian Dokumen dan Cleanup

### Tujuan

- mengunci perubahan dokumen agar selaras dengan keputusan
- membersihkan referensi lama yang masih menulis `users` sebagai sumber utama
- menutup workplan dengan jejak audit yang jujur

### Klasifikasi

- reversible

### File yang disentuh

- `docs/CHANGELOG.md`
- `docs/01 Profil Sistem/PROJECT_STATUS.md`
- `docs/02 Keputusan/README.md` bila perlu sinkronisasi daftar keputusan aktif
- `docs/04 Diskusi/*` yang masih menyebut `users` sebagai sumber utama bila memang sudah ditetapkan lain
- `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Struktur Organisasi dan Grup Angkatan.md`

### Verifikasi fase

- cek ulang tidak ada lagi dokumentasi aktif yang menyalip keputusan `2026.Kep.004`
- cek ulang tidak ada lagi klaim bahwa `users` adalah sumber data utama profil anggota
- cek ulang link antar dokumen masih valid
- cek ulang ringkasan perubahan sudah dicatat di changelog
- cek ulang keputusan master data tidak lagi menempatkan `TMT Penetapan` sebagai sekadar field opsional bila ia sudah ditetapkan menjadi sumber utama `Tahun`

### Langkah jika gagal

- jangan tutup workplan sebelum dokumentasi dan runtime selaras
- jika ada gap yang tersisa, buka workplan turunan baru atau lanjutkan fase yang gagal

## Verifikasi Final

Verifikasi final minimal yang diharapkan:

- audit manual bahwa semua jalur baca-tulis utama sudah mengarah ke `personels`
- audit manual bahwa `users` sudah dicabut dari target fresh install dan runtime aktif
- audit manual bahwa `MFA` dan `avatar` tetap tidak tersentuh di workplan ini
- audit manual bahwa `personels` memegang field inti, kontak, alamat, pendidikan, dan status lifecycle
- audit manual bahwa dokumen aktif dan arsip sudah menyebut keputusan yang benar

## Hasil Verifikasi Lokal

- `php -l` lulus untuk migration dan file PHP yang diubah pada batch ini, termasuk migration `personels`, nomor HP, riwayat status, controller auth/admin/profile/personel, middleware profil lengkap, action approval, dan model baru pendukung personel
- `npm run build` lulus
- command Laravel seperti `php artisan about` masih terblokir karena environment PHP 8.5.4 belum punya ekstensi `mbstring`, sehingga verifikasi artisan/test belum dapat dijalankan penuh
- verifikasi `php -l` untuk batch 12 Juli tidak bisa dijalankan dari shell ini karena binary `php` tidak tersedia di `PATH`, sehingga pengecekan syntax PHP lanjutan harus dibaca sebagai pending verifikasi manual user
- approval personel sekarang menyalakan `is_active` langsung di `personels`
- ekspor dan PDF personel sekarang membaca data canonical dari `personels` dan `SINYALEMEN` tanpa fallback utama ke `users`
- keputusan master data aktif sudah direvisi untuk membedakan `TMT Penetapan` penetapan awal anggota sebagai sumber kebenaran `angkatan` dari `TMT` pada penyesuaian ijazah atau kenaikan pangkat yang hanya berlaku bagi riwayat pangkat
- keputusan NIKC dan keputusan kepangkatan juga sudah diberi rujukan silang singkat agar `angkatan` tetap dibaca dari penetapan awal anggota dan tidak tertukar dengan `TMT` perubahan pangkat
- model `Personel` kini menurunkan `angkatan` secara otomatis dari tahun pada `tmt_penetapan` ketika data personel disimpan
- registrasi dan jalur admin personel tidak lagi meminta input manual `angkatan`; form aktif kini meminta `TMT Penetapan` awal dan hanya menampilkan `Tahun Angkatan` sebagai hasil turunan
- label tampilan dan ekspor diselaraskan agar memakai istilah `Tahun Angkatan` sebagai representasi tahun dari penetapan awal, bukan `tahun kelulusan` atau input manual bebas
- audit 11 Juli 2026 menemukan bahwa `Matra-Tahun-Batch` belum hidup penuh di runtime karena belum ada entitas `grup angkatan`, belum ada relasi formal personel ke grup, dan target broadcast masih dominan membaca `matra + angkatan` terpisah
- batch implementasi lanjutan ini melahirkan schema `grup_angkatans`, relasi `personels.grup_angkatan_id`, alias domain `tahun_angkatan`, preview `Grup Angkatan` di form aktif, serta target broadcast baru berbasis `GRUP_ANGKATAN`
- istilah teknis filter admin mulai dirapikan: request aktif kini dibedakan ke `tahun_angkatan` dan `grup_angkatan_uuid`, sementara kolom database `angkatan` tetap dipertahankan sebagai representasi tahun turunan
- audit file aktif juga menutup sisa istilah tunggal `angkatan` pada PDF laporan, daftar verifikasi, daftar personel, dan diskusi struktur organisasi agar konteks `Tahun Angkatan` versus `Grup Angkatan` tampil lebih tegas
- atas arahan user untuk target `fresh install`, schema grup angkatan dan histori tidak dipertahankan sebagai migration tambahan terpisah; seluruh kebutuhan dasar dilipat ke migration inti `create_personels_table` dan migration aktif lain yang relevan
- modul admin `Master Grup Angkatan` kini sudah lahir dengan route, controller, sidebar, halaman daftar, tambah, edit, filter admin, dan penghapusan aman bila grup sudah kosong
- tabel riwayat perpindahan grup personel dan riwayat pergantian koordinator grup juga sudah lahir, lalu mulai ditulis saat registrasi, input admin, perpindahan grup, dan perubahan koordinator/wakil
- tampilan broadcast dan laporan personel kini memakai label target dan label grup yang human-readable, bukan lagi nilai mentah yang membingungkan admin
- turunan lanjutan pada 11 Juli 2026 tetap berada dalam keputusan dan workplan yang sama; tidak dibuka diskusi baru karena substansi normatifnya tidak berubah, yang berubah hanya pendalaman implementasi runtime
- form admin personel kini tidak lagi meminta admin menyusun `matra/tahun/batch` secara manual; admin wajib memilih `grup angkatan` langsung dari master dan sistem hanya menampilkan field turunannya secara readonly
- modul grup angkatan kini punya halaman detail yang menampilkan anggota grup, riwayat perpindahan grup, dan riwayat pergantian koordinator/wakil
- validasi bisnis koordinator dan wakil diperketat agar role penugasannya benar dan satu personel tidak dapat terikat sebagai koordinator atau wakil pada grup lain secara bersamaan
- migration diperbaiki untuk kesiapan fresh install tanpa database aktif: tidak ada tabel `users`, schema `personels` memuat field keputusan sekaligus akun autentikasi, nomor HP multi-nomor punya tabel sendiri, dan riwayat status punya tabel sendiri
- scan fixed-string kode aktif untuk `users`, `User::`, `App\Models\User`, `constrained('users')`, dan `DB::table('users')` tidak menemukan sisa referensi
- audit ulang naming log sudah diselaraskan ke `personel_id` pada trait audit, model audit/login, controller login, dashboard admin, dan migration log
- schema fresh install mempertahankan `user_id` pada tabel `sessions` karena Laravel database session handler memang menulis ke kolom itu, meski auth model aktif sudah `personels`
- halaman profil sudah berhenti membawa prop `user` dan sekarang konsisten membaca `personel` sebagai sumber data utama
- route notifikasi dibetulkan menjadi `POST` untuk aksi tandai-baca agar tidak lagi memakai `GET` untuk mutasi state
- migration `broadcasts.matra` dan `broadcast_responses.status` yang tidak dipakai lagi sudah dicabut agar fresh install tidak membawa schema residu
- UI admin personel sekarang membaca `status_attendance`, bukan `status`, agar riwayat balasan broadcast tampil sesuai schema aktif
- respons broadcast personel kini divalidasi terhadap target `broadcast_targets` sebelum bisa disimpan
- `personel` dan `anggota` dicatat sebagai sinonim domain, dengan backend tetap menomorsatukan `personel`
- scaffold lama `routes/auth.php` dan `ProfileController.php` sudah tidak ada lagi, sehingga tidak ada dead route/controller yang masih mengarah ke request starter kit
- jalur `avatar` dan `MFA` ditahan di profile sesuai status `hold`
- gate `face verification` tetap dimatikan dari login dan route operasional, tetapi file controller, model, middleware, view, dan migration dasarnya dikembalikan sebagai modul dormant untuk jalur registrasi yang masih dibahas
- istilah user-facing `SINYALEMEN` diseragamkan; nama teknis lama `Sinyalemen` hanya dipertahankan sebagai alias transisi kode
- validasi dan tampilan gender diselaraskan ke label keputusan `Pria/Wanita`
- relasi monitoring log sudah memakai `personel` dan tampilan admin monitoring tidak lagi membaca `log.user`
- dashboard koordinator sudah berhenti menghitung `personel_aktif` lewat relasi `user` dan kini membaca status aktif langsung dari `personels`
- broadcast admin kini menyaring target role `personel` agar admin, koordinator, atau wakil koordinator yang juga tersimpan di tabel `personels` tidak ikut menerima maklumat lapangan
- pemindaian ulang masih menemukan istilah `Sinyalemen` pada dokumen arsip dan nama teknis model/route/file, tetapi tidak pada teks user-facing aktif yang wajib diseragamkan
- audit 12 Juli 2026 menemukan bahwa sisa drift pada route laporan broadcast dan status `hold` PDF/Excel dipindahkan ke diskusi broadcast operasional, sehingga tidak ditutup diam-diam di workplan master data ini
- audit 12 Juli 2026 juga menemukan bahwa registrasi publik masih efektif hanya pada jalur `Reguler`; gap `ASN` dan `SPPI` dipindahkan ke diskusi akun dan verifikasi agar tidak diputuskan sepihak dari workplan master data

## Catatan Perubahan Detail

- fase 1 memetakan drift dan target `personels`
- fase 2 menyiapkan schema fresh install yang langsung memakai `personels`
- fase 3 memindahkan runtime baca-tulis dan auth utama ke `personels`
- fase 4 mengunci dokumentasi dan menutup workplan
- fresh install belum dijalankan karena repo belum dipasang dan belum ada database aktif; perbaikan dilakukan pada file migration agar siap ketika instalasi dilakukan
- migration dan runtime `users` dicabut karena repo belum diinstall dan belum ada database aktif yang perlu dimigrasikan
- runtime `face verification` tetap nonaktif, sedangkan file dan migration dasarnya dipertahankan sebagai fondasi modul yang belum diaktifkan
- workplan belum diarsipkan karena user meminta laporan QA terlebih dahulu

## Status Commit Lokal

- perubahan batch audit fresh install dan sinkronisasi drift ini belum diarsipkan; status akhir menunggu QA user

## Catatan QA dan Penutupan

- workplan ini harus ditutup hanya setelah runtime benar-benar selaras dengan keputusan
- batch ini sudah menutup drift utama pada approval, verifikasi, tampilan personel, dan ekspor personel
- batch ini juga menutup drift naming log internal yang semula masih memakai istilah lama
- batas akses `admin`, `koordinator`, `wakil koordinator`, dan `personel` sekarang dijaga oleh middleware route; repo ini tidak mendaftarkan policy layer terpisah
- jika QA user menemukan sisa file aktif pembaca `users`, batch lanjutan harus dibuat sebelum arsip
- workplan tidak boleh diarsipkan sebelum user menyatakan hasil QA diterima
- drift yang kini bergantung pada keputusan domain lain tidak dianggap selesai di sini:
  - route laporan broadcast dan status `hold` PDF/Excel diproses di diskusi broadcast operasional
  - aturan registrasi publik lintas `Reguler`, `ASN`, dan `SPPI` diproses di diskusi akun dan verifikasi
  - definisi final `super admin` versus `admin` diproses di diskusi akun dan struktur organisasi
  - **nomor HP**: arah inti (multi-nomor & tabel terpisah) sudah final di Kep.004 klausul 29, dan pencabutan kolom `personels.phone_number` plus rewire ~19 referensi **sudah dieksekusi** (migration `2026_07_12_000001_drop_phone_number_from_personels_table`; akses `$personel->phone_number` kini dilayani accessor dari `personel_phone_numbers`). Yang masih dibahas di diskusi Manajemen Kontak Anggota hanyalah aturan detail `is_primary`/`is_active`/`verified` serta kendala OTP/verifikasi belum siap sehingga nomor sebatas input & dipercaya dulu.
- audit 11 Juli 2026 menambahkan kebutuhan revisi keputusan master data terkait `TMT Penetapan` dan `Tahun`; sebelum batch implementasi lanjutan dibuka, keputusan `2026.Kep.004` perlu diperjelas agar schema, validasi, UI, dan fitur grouping tidak berjalan dengan tafsir ganda
- audit 11 Juli 2026 juga menegaskan bahwa istilah `TMT Penetapan` perlu dibaca menurut konteks dokumen sumber: penetapan awal anggota Komcad menjadi sumber kebenaran `angkatan`, sedangkan penyesuaian ijazah atau kenaikan pangkat hanya menjadi sumber `TMT` untuk riwayat pangkat terbaru
- audit lanjutan 11 Juli 2026 menghasilkan daftar perubahan konkret untuk batch berikutnya yang kini sudah mulai diturunkan ke runtime:
- migration (seluruhnya dilipat ke `2026_07_06_055601_create_personels_table` agar fresh install tidak membawa schema residu; tidak ada file `create_grup_angkatans_table` atau `add_grup_angkatan_id_to_personels_table` terpisah):
  - entitas `grup_angkatans`, `personel_grup_angkatan_histories`, `grup_angkatan_coordinator_histories`, `surat_keputusan_artifacts`, `surat_keputusan_personel_entries` lahir di migration inti `create_personels_table`
  - `grup_angkatan_id` pada `personels` + FK ke `grup_angkatans` juga di migration inti yang sama
  - revisi target type `broadcasts` agar mengenal `TAHUN_ANGKATAN` dan `GRUP_ANGKATAN`
- model:
  - model baru `GrupAngkatan`
  - relasi `Personel -> grupAngkatan`
  - accessor `tahun_angkatan`, `batch_angkatan`, `grup_angkatan_label`, dan `grup_angkatan_uuid`
- controller dan request:
  - register/admin personel menerima `batch_angkatan` opsional lalu me-resolve grup angkatan formal
  - filter admin dipisah antara `tahun_angkatan` dan `grup_angkatan_uuid`
  - broadcast admin menerima target `GRUP_ANGKATAN`
- UI:
  - form register/create/edit personel menampilkan `Batch Angkatan` dan preview `Grup Angkatan`
  - daftar personel dan verifikasi menampilkan identitas `Grup Angkatan`
  - form broadcast dapat menargetkan grup angkatan formal
  - modul admin `Master Grup Angkatan` memberi CRUD dasar dan filter `matra-tahun-status`
- audit lanjutan pada batch yang sama masih menemukan tiga drift implementasi yang harus ditutup agar aturan grup benar-benar hidup:
  - form edit personel belum mewajibkan alasan saat admin memindahkan personel ke grup lain
  - daftar personel belum memberi penanda visual siapa yang sedang menjadi koordinator atau wakil
  - guard backend belum menutup seluruh jalur penambahan anggota ke grup berstatus `arsip`
- drift tersebut kini sudah ditutup di runtime aktif:
  - edit personel mewajibkan `group_transfer_reason` ketika `grup_angkatan_uuid` berubah dan alasan itu ditulis ke histori perpindahan
  - daftar personel menampilkan badge `Koordinator` atau `Wakil` berdasarkan role aktif personel
  - create personel, edit personel, dan transfer anggota antar grup sama-sama menolak target grup berstatus `arsip`
- audit governance pada batch yang sama menemukan bahwa file aktif untuk grup angkatan dan administrasi personel sudah lebih maju daripada dokumen aktif karena kontrak teknisnya belum tertulis eksplisit
- penutupan drift governance dilakukan melalui tiga lapis:
  - workplan ini mencatat gap dan penutupannya
  - keputusan master data direvisi untuk menegaskan status grup `aktif/arsip`, larangan grup `arsip` menerima anggota baru, pemilihan grup dari master, dan alasan wajib saat edit personel mengubah grup
  - referensi teknis baru `docs/11 Referensi Teknis/2026.11.003 tentang Kontrak Teknis Grup Angkatan dan Administrasi Personel.md` dilahirkan untuk mengikat schema, validasi, histori, dan pola UI admin yang sudah hidup di file aktif
- batch 12 Juli menambahkan turunan runtime agar identitas grup benar-benar mengikuti `Matra-Tahun-Abituren-Batch` serta dapat merujuk ke artefak `Surat Keputusan` yang sama dengan penyesuaian pangkat
- audit migration fresh install setelah batch 12 Juli mencabut file transisi/no-op dan melipat final state ke migration dasar agar repo siap diinstall dari nol tanpa jejak schema sementara
- audit governance pada 12 Juli 2026 kemudian diperbarui lagi: verifikasi wajah awal dipertahankan sebagai kandidat bukti registrasi tetapi tetap nonaktif, sedangkan `face recognition` otomatis dipindahkan ke diskusi khusus dan tetap ditahan

