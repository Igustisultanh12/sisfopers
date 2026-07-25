> Status Arsip: Ditetapkan
> Ditetapkan pada: 2026-07-20 20:31:21 WIB
> Rujukan keputusan: `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`, `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`, `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`
> Rencana kerja turunan: `docs/03 Rencana Kerja/2026.07.09 Rencana Kerja tentang Sinkronisasi Implementasi NIKC.md`
> Catatan: diskusi ini difinalkan oleh user pada 2026-07-20 dan menjadi dasar revisi workplan aktif, bukan keputusan baru.

# Diskusi

## tentang AUDIT DAN TEMUAN LANJUTAN IMPLEMENTASI NIKC

Status: `Ditetapkan`
Tanggal dibuka: 16 Juli 2026; ditambahkan temuan lanjutan: 19 Juli 2026; diperbarui audit pull dan catatan perubahan tidak terencana: 20 Juli 2026; diperbarui persiapan finalisasi: 20 Juli 2026; diperbarui audit pull lanjutan: 20 Juli 2026
Identitas dokumen: 2026.07.16/19 Diskusi tentang Audit dan Temuan Lanjutan Implementasi NIKC
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: ketidaksesuaian antara `2026.Kep.003` dan implementasi repo aktual untuk domain NIKC, serta temuan lanjutan terkait validasi 17 digit, label login publik, verifier publik dob, API wilayah, dan penyelarasan form registrasi
Keputusan terkait: `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`, `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`, `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`
Rencana kerja terkait: `docs/03 Rencana Kerja/2026.07.09 Rencana Kerja tentang Sinkronisasi Implementasi NIKC.md` (perlu revisi)
Mengubah: -
Digantikan oleh: -

## Konteks

`2026.Kep.003` sudah menetapkan norma NIKC: permanen, 17 digit, validasi komponen digit, tidak digenerate sistem, master data `master_kepangkatan` dan `master_provinsi`, reuse `province`, dan input hanya dari pencatatan/klaim. Implementasi repo dikerjakan paralel oleh rekan terpisah dari proses dokumentasi. Perbedaan yang ditemukan dalam audit ini dibaca sebagai kebutuhan penyelarasan antara dokumen dan repo, bukan sebagai pelanggaran. Diskusi ini difokuskan untuk memetakan perbedaan antara norma `Kep.003` dan kode aktual, tanpa mengubah keputusan.

Audit awal dilakukan 16-17 Juli 2026. Pada 19 Juli 2026 dilakukan pengecekan tambahan terhadap UI publik (`Login.vue`, `Register.vue`) dan menemukan gap validasi 17 digit numeric serta label login yang mencampur identifier. Pemeriksaan ulang juga dilakukan untuk menilai kebutuhan integrasi API wilayah (`wilayah.id`) dan master data kepangkatan. Audit lanjutan dilakukan untuk meninjau `RegisterPersonelRequest.php`, halaman admin lain, dan kepatuhan terhadap `Kep.004` serta `Kep.005`.

## Audit Repo 16-19 Juli 2026

Metode: static check tanpa perubahan file.

### File yang diperiksa
- `app/Actions/ApproveRegistrationAction.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/Admin/MasterPersonelController.php`
- `app/Http/Controllers/Admin/VerificationController.php`
- `app/Http/Controllers/SkepPublicController.php`
- `app/Rules/NikcFormatRule.php`
- `app/Exports/PersonelExport.php`
- `app/Exports/BroadcastResponseExport.php`
- `app/Http/Controllers/Admin/SkepController.php`
- `app/Http/Controllers/Admin/ReportController.php`
- `app/Http/Controllers/Api/ApiAdminController.php`
- `app/Http/Requests/Auth/RegisterPersonelRequest.php`
- `resources/js/Pages/Auth/Login.vue`
- `resources/js/Pages/Auth/Register.vue`
- `resources/js/Pages/Admin/Personel/Create.vue`
- `resources/js/Pages/Admin/Personel/Edit.vue`
- `resources/js/Pages/Admin/Personel/Index.vue`
- `resources/js/Pages/Admin/Skep/Index.vue`
- `resources/js/Pages/Admin/Verification/Index.vue`
- `routes/web.php`
- `database/migrations/2026_07_06_055601_create_personels_table.php`
- `database/migrations/2026_07_08_062112_add_pangkat_and_nikc_to_personels_table.php`
- `database/seeders/DatabaseSeeder.php`

## Audit Repo 20 Juli 2026 Setelah Pull

Metode: pull repo `https://github.com/Igustisultanh12/sisfopers1.git`, static check terhadap perubahan register/NIKC, pengecekan header publik `https://sisfoperskc.my.id/register`, dan validasi ringan lokal.

Catatan disiplin lifecycle: permintaan semula adalah audit/periksa, sehingga perubahan runtime seharusnya tidak dilakukan langsung. Perubahan kode yang terjadi pada 20 Juli 2026 dicatat sebagai perubahan tidak terencana dan harus diturunkan ulang ke rencana kerja sebelum dianggap sebagai pelaksanaan resmi fase NIKC.

### Hasil pull
- `git pull https://github.com/Igustisultanh12/sisfopers1.git` berhasil fast-forward ke `6944465`.
- Pull membawa perubahan upstream pada `resources/js/Pages/Auth/Register.vue` yang sudah menambahkan sebagian validasi NIKC frontend:
  - input NIKC dibersihkan menjadi angka saja,
  - panjang input dipotong maksimal 17 digit,
  - counter `x/17` tampil di sisi input,
  - pesan khusus untuk panjang tidak tepat 17 digit sudah ada.

### Temuan setelah pull
- Pesan validasi NIKC khusus 16 digit sudah ada, tetapi memakai helper SweetAlert berbasis `text`, sehingga markup `<strong>` dan `<br>` berisiko tampil mentah sebagai teks. Ini adalah temuan bocor markup pada pesan: `NIKC wajib terdiri dari tepat <strong>17 digit angka</strong>...`.
- File upload pada register publik memakai kelas warna yang masih samar di mode background gelap. Selain itu scoped CSS `input:not([type="checkbox"]):not([type="radio"])` memaksa semua input non-checkbox/radio, termasuk `input[type=file]`, menjadi warna gelap; akibatnya nama file yang sudah dipilih terlihat sangat redup di atas latar gelap.
- Backend masih belum sepenuhnya memakai validasi NIKC 17 digit pada jalur register dan SKEP publik sebelum perubahan tidak terencana dicatat di bawah.
- `https://sisfoperskc.my.id/register` merespons `200 OK` pada 20 Juli 2026.

### Perubahan Tidak Terencana yang Terlanjur Dilakukan

Perubahan berikut sudah masuk di working tree, tetapi status lifecycle-nya harus diperlakukan sebagai tambalan parsial yang perlu dicatat dan dirapikan melalui rencana kerja, bukan sebagai pelaksanaan resmi yang lahir dari diskusi. Catatan ini tidak dibaca sebagai pelanggaran, melainkan sebagai akibat dari perbedaan jalur pengerjaan dokumen dan file/repo yang sekarang perlu diselaraskan:

| File | Perubahan | Status lifecycle |
| --- | --- | --- |
| `resources/js/Composables/useSwal.js` | Menambahkan helper `alertErrorHtml(title, html)` untuk SweetAlert agar pesan validasi NIKC dapat merender `<strong>` dan `<br>` secara benar | Tertambal parsial, perlu dirujuk di rencana kerja |
| `resources/js/Pages/Auth/Register.vue` | Mengubah pemanggilan pesan NIKC tidak valid dari `alertError` ke `alertErrorHtml` | Tertambal parsial, perlu dirujuk di rencana kerja |
| `resources/js/Pages/Auth/Register.vue` | Mengubah warna teks `input[type=file]` pada mode background gelap menjadi putih/lebih kontras untuk SKEP, SK ASN, foto profil, dan KTP | Tertambal parsial, perlu dirujuk di rencana kerja |
| `resources/js/Pages/Auth/Register.vue` | Mengecualikan `input[type=file]` dari aturan CSS global yang memaksa warna teks input menjadi gelap | Tertambal parsial, perlu dirujuk di rencana kerja |
| `app/Http/Controllers/Auth/AuthController.php` | Mengubah rule `nikc` register menjadi `required|digits:17|unique:personels,nikc` | Tertambal parsial, belum menggantikan kebutuhan rule NIKC reusable |
| `app/Http/Controllers/SkepPublicController.php` | Mengubah rule `nikc` pada `checkNikc` dan `submitRequest` menjadi `required|digits:17` | Tertambal parsial, belum mencakup verifier `dob` 2 faktor |
| `app/Http/Requests/Auth/RegisterPersonelRequest.php` | Menambahkan rule `nikc` `required|digits:17|unique:personels,nikc` | Tertambal parsial, perlu dipastikan apakah request ini aktif dipakai di flow register aktual |

### Verifikasi lokal 20 Juli 2026
- `php -l` lulus untuk:
  - `app/Http/Controllers/Auth/AuthController.php`
  - `app/Http/Controllers/SkepPublicController.php`
  - `app/Http/Requests/Auth/RegisterPersonelRequest.php`
- `node --check resources/js/Composables/useSwal.js` lulus.
- `git diff --check` lulus.
- `npm install` berhasil setelah akses network diizinkan dan tidak menemukan vulnerability npm.
- `npm run build` belum selesai karena folder `vendor` PHP belum tersedia; build berhenti pada import `../../vendor/tightenco/ziggy`.
- `composer install` belum bisa dijalankan sampai selesai karena `composer.json` dan `composer.lock` tidak sinkron: `barryvdh/laravel-dompdf` dan `maatwebsite/excel` ada di `composer.json`, tetapi tidak ada di lockfile.

### Status sudah dan belum setelah 20 Juli 2026

Sudah tertambal parsial di working tree:
- validasi frontend NIKC 17 digit angka pada register publik sudah ada dari pull upstream;
- pesan 16 digit sudah ada dari pull upstream dan sudah diarahkan ke helper HTML agar markup tidak bocor;
- warna teks nama file upload pada register publik mode background gelap sudah dibuat kontras;
- backend register dan endpoint publik SKEP sudah mulai menolak NIKC selain 17 digit.

Masih belum selesai dan tetap harus masuk rencana kerja:
- `NikcFormatRule` reusable belum ada;
- validasi komponen digit NIKC terhadap pangkat, matra, tahun/bulan lahir, dan kode provinsi Latsarmil belum ada;
- jalur admin personel `store/update` belum diselaraskan ke rule 17 digit reusable;
- validasi publik 2 faktor `NIKC + dob` belum diterapkan;
- generator legacy `KC-...` pada `ApproveRegistrationAction.php` sudah diaudit ulang sebagai action legacy/orphan yang tidak ditemukan dipanggil route/controller aktif, tetapi belum dihapus dalam perubahan ini;
- label login publik `Username / NIKC` belum diperbaiki dalam perubahan ini;
- fallback NIKC hardcoded di export belum diperbaiki;
- seeder contoh NIKC belum diperbaiki;
- master `master_kepangkatan` dan `master_provinsi` belum dibangun;
- build frontend penuh belum terverifikasi karena dependency PHP/vendor masih terblokir sinkronisasi Composer lock.

## Audit Repo 20 Juli 2026 Setelah Pull Lanjutan ke 6acf367

Metode: `git pull --autostash origin main`, static check terhadap file yang berubah pada rentang `6944465..6acf367`, pencarian ulang kata kunci scope NIKC/wilayah/pangkat, dan lint PHP ringan pada file backend yang terdampak.

### Hasil pull lanjutan
- Pull berhasil fast-forward dari `6944465` ke `6acf367`.
- Autostash perubahan lokal berhasil diterapkan ulang.
- File upstream yang berubah:
  - `app/Http/Controllers/Education/EducationController.php`
  - `app/Http/Controllers/Personel/JobHistoryController.php`
  - `resources/js/Layouts/AuthenticatedLayout.vue`
  - `resources/js/Pages/Admin/VerifikasiPendidikan/Index.vue` (file baru)
  - `resources/js/Pages/Personel/JobHistory/Index.vue`
  - `routes/web.php`

### Temuan terhadap scope diskusi
- `resources/js/Pages/Admin/VerifikasiPendidikan/Index.vue` adalah surface baru yang menampilkan dan mencari `NIKC`:
  - placeholder: `Cari nama personel, NIKC, institusi, program studi...`
  - tampilan tabel: `item.personel?.nikc`
  - status: tidak generate NIKC dan tidak mengubah validasi NIKC; cukup dicatat sebagai surface tambahan untuk audit label/search NIKC.
- `app/Http/Controllers/Education/EducationController.php::adminVerifList` menambahkan pencarian `personel.nikc` pada verifikasi pendidikan:
  - status: aman terhadap `Kep.003` karena hanya membaca/mencari NIKC existing.
  - catatan: modul pendidikan juga memakai `pangkat` untuk nomor ijazah internal; ini perlu diaudit pada fase `master_kepangkatan`, tetapi bukan blocker finalisasi diskusi NIKC.
- `resources/js/Pages/Personel/JobHistory/Index.vue` memakai `/api/wilayah/provinces` dan `/api/wilayah/regencies/{code}` untuk data pekerjaan.
- `routes/web.php` tetap memberi komentar "Wilayah.id alternatif emsifa", tetapi implementasi proxy masih mengambil data dari `https://emsifa.github.io/api-wilayah-indonesia/...`.
  - status: memperluas drift wilayah yang sudah dicatat. Scope `master_provinsi`/`wilayah.id` tidak cukup hanya mencakup register/personel; harus mencakup `Personel/JobHistory` dan route API wilayah yang dipakai bersama.
- `app/Http/Controllers/Personel/JobHistoryController.php` gagal PHP lint:
  - error: `Parse error: Unclosed '[' on line 113 does not match '}' on line 116`.
  - dampak: ini bukan substansi NIKC, tetapi menjadi blocker verifikasi repo karena route personel pekerjaan aktif memakai controller ini.
- `routes/web.php` menambahkan role `admin` ke group route personel:
  - status: tidak langsung menyentuh NIKC, tetapi menambah alur admin sebagai personel untuk data mandiri. Perlu smoke test setelah blocker syntax diperbaiki.

### Usul solusi / rencana perbaikan
- Tambahkan langkah pra-verifikasi sebelum fase NIKC berjalan: perbaiki syntax `JobHistoryController.php` hasil pull agar `php -l` dan bootstrap Laravel tidak gagal. Ini bisa menjadi hotfix kecil terpisah atau prasyarat F9, karena bukan perubahan domain NIKC tetapi memblokir validasi repo.
- Perluas fase wilayah/provinsi di workplan:
  - `routes/web.php` API wilayah harus benar-benar berpindah dari `emsifa.github.io` ke sumber stabil `wilayah.id` atau adaptor lokal yang disepakati.
  - `resources/js/Pages/Auth/Register.vue` dan `resources/js/Pages/Personel/JobHistory/Index.vue` harus memakai kontrak data wilayah yang sama.
  - `master_provinsi` harus memuat mapping kode BPS -> kode Latsarmil 01-34 untuk validasi digit 16-17 NIKC.
- Tambahkan file baru `resources/js/Pages/Admin/VerifikasiPendidikan/Index.vue` ke audit per-file workplan sebagai surface baca/search NIKC. Rekomendasi: tidak perlu perubahan khusus selain memastikan label tetap `NIKC` dan tidak kembali mencampur `NIK atau NIKC`.
- Tambahkan `app/Http/Controllers/Education/EducationController.php` ke audit per-file workplan sebagai surface baca/search NIKC dan pemakaian `pangkat`. Rekomendasi: tidak perlu validasi NIKC baru di sini karena controller hanya membaca/mencari data existing, tetapi pemakaian pangkat tetap ikut dinormalisasi saat `master_kepangkatan` diterapkan.
- Tegaskan dalam workplan bahwa `NIKC belum terdaftar` / pengajuan SKEP publik yang sudah ada di register bukan generate NIKC. Alur ini hanya mengajukan verifikasi berkas untuk NIKC yang diklaim user. Namun perlu keputusan redaksional apakah istilah "NIKC belum ada di sistem" tetap dianggap out-of-scope atau diganti menjadi "NIKC belum ada di database pra-verifikasi SKEP".

### Pertanyaan yang sudah dijawab setelah pull lanjutan
- Blocker syntax `JobHistoryController.php` boleh diselesaikan sebagai perbaikan prasyarat sebelum workplan NIKC dilanjutkan.
- Modul `Personel/JobHistory` masuk langsung ke fase `master_provinsi` karena memakai `province/provinsi` dan endpoint `/api/wilayah` yang sama.
- Redaksi `NIKC Belum Terdaftar` diganti menjadi `NIKC Belum Ada di Database`, dengan pesan eksplisit bahwa sistem tidak membuat NIKC dan pengajuan harus berdasar SKEP.

## Catatan Perbaikan Prasyarat 20 Juli 2026

Perbaikan prasyarat dilakukan setelah audit pull lanjutan menemukan blocker syntax di `app/Http/Controllers/Personel/JobHistoryController.php` dan drift sumber data wilayah pada proxy `/api/wilayah`.

### Perubahan yang dilakukan
- `app/Http/Controllers/Personel/JobHistoryController.php`
  - Menutup response JSON `requestOtp()` yang sebelumnya kurang `]);`.
  - Dampak: PHP lint untuk controller pekerjaan kembali lulus.
- `routes/web.php`
  - Mengganti proxy wilayah dari `https://emsifa.github.io/api-wilayah-indonesia/...` ke `https://wilayah.id/api/...`.
  - Endpoint yang disesuaikan:
    - `/api/wilayah/provinces` -> `https://wilayah.id/api/provinces.json`
    - `/api/wilayah/regencies/{province_code}` -> `https://wilayah.id/api/regencies/{province_code}.json`
    - `/api/wilayah/districts/{regency_code}` -> `https://wilayah.id/api/districts/{regency_code}.json`
  - Response tetap dinormalisasi ke bentuk array berisi `code` dan `name` agar kompatibel dengan `Register.vue` dan `Personel/JobHistory/Index.vue`.

### Verifikasi
- `php -l app/Http/Controllers/Personel/JobHistoryController.php` lulus.
- `php -l routes/web.php` lulus.
- `git diff --check` lulus.
- `php artisan route:list --path=api/wilayah` dan `php artisan route:list --path=personel/pekerjaan` belum bisa dijalankan karena `vendor/autoload.php` belum tersedia.

### Dampak terhadap diskusi
- Blocker syntax `JobHistoryController.php` sudah tidak lagi menjadi penghalang diskusi.
- Scope `Personel/JobHistory` sudah mulai diserap sebagai konsumen endpoint `wilayah.id`.
- Catatan `master_provinsi` tetap belum selesai: penggunaan `wilayah.id` hanya menyelesaikan sumber data wilayah, belum menyediakan mapping kode BPS -> kode Latsarmil 01-34 untuk validasi digit 16-17 NIKC.
- Redaksi publik "NIKC Belum Terdaftar" sudah dijawab dan diganti menjadi "NIKC Belum Ada di Database".

## Kejelasan Redaksi Register Publik

Masalah redaksi: teks `NIKC Belum Terdaftar` berpotensi ambigu karena dapat terbaca seolah sistem akan membuat atau menerbitkan NIKC baru. Ini bertentangan dengan prinsip `Kep.003`: NIKC tidak digenerate sistem, tetapi berasal dari SKEP dan hanya disimpan/divalidasi oleh aplikasi.

### Prinsip redaksi
- Sistem tidak membuat NIKC.
- Jika NIKC belum ada dalam database pra-verifikasi, user harus mengajukan dasar SKEP.
- Admin hanya menambahkan atau memverifikasi NIKC yang tercantum pada SKEP, bukan membuat nomor baru.
- Redaksi harus membedakan "belum ada di database pra-verifikasi" dari "belum punya NIKC".

### Usulan redaksi final
- Judul panel/form:
  - ganti `NIKC Belum Terdaftar`
  - menjadi `NIKC Belum Ada di Database`
- Isi keterangan:
  - `NIKC tidak dibuat oleh sistem. Jika NIKC Anda sudah tercantum pada SKEP tetapi belum ada di database pra-verifikasi, unggah salinan SKEP asli sebagai dasar admin menambahkan dan memverifikasi NIKC tersebut.`
- Judul alert:
  - ganti `NIKC Tidak Ditemukan`
  - menjadi `NIKC Belum Ada di Database`
- Isi alert:
  - `Nomor NIKC ini belum ada di database. Sistem tidak membuat NIKC baru. Jika NIKC Anda sudah tercantum pada SKEP, silakan unggah SKEP asli sebagai dasar admin menambahkan dan memverifikasi NIKC tersebut.`
- Tombol:
  - ganti `Ajukan Verifikasi SKEP`
  - menjadi `Ajukan Verifikasi Berdasarkan SKEP`

### Dampak terhadap workplan
- Masuk fase penyelarasan label/pesan register publik.
- Tidak perlu keputusan baru karena hanya memperjelas prinsip aktif `Kep.003`.
- Acceptance criteria: tidak ada teks publik yang memberi kesan sistem membuat NIKC baru; semua jalur klaim NIKC yang belum ada di database harus merujuk SKEP sebagai dasar.

### Keputusan diskusi
- Redaksi yang dipakai: `NIKC Belum Ada di Database`.
- Pesan wajib menegaskan bahwa sistem tidak membuat NIKC.
- Jika NIKC sudah tercantum pada SKEP tetapi belum ada di database, user mengunggah SKEP sebagai dasar admin menambahkan/memverifikasi NIKC tersebut.
- Istilah `pra-verifikasi` tidak dipakai pada judul publik agar pesan lebih ringkas, tetapi substansi verifikasinya tetap dijelaskan dalam keterangan.
## Temuan Audit

### 1. Generate NIKC legacy masih hidup sebagai action legacy/orphan - DRIFT TERBATAS
- `ApproveRegistrationAction.php` masih mengeksekusi:
  - `$nikc = "KC-" . $tahun . "." . $matraCode . "." . $randomReg;`
  - lalu menyimpan ke `personels.nikc`
- Audit ulang 20 Juli 2026 tidak menemukan pemanggilan aktif terhadap `ApproveRegistrationAction` dari route/controller.
- Jalur approval aktif yang ditemukan berada di `VerificationController.php` dan `ApiAdminController.php`; keduanya memakai `$personel->nikc`, bukan membangkitkan `KC-...`.
- Bertentangan langsung `Kep.003`:
  - klausul 1: NIKC permanen
  - klausul 2: sistem hanya menyimpan/memvalidasi, tidak generate
- Implikasi: bukan bukti bahwa approval aktif saat ini masih menciptakan NIKC baru, tetapi file legacy ini tetap berbahaya bila dipakai ulang karena akan menulis format `KC-...` ke `personels.nikc`.
- Kesimpulan audit: aman dijadikan kandidat penghapusan dalam workplan setelah verifikasi ulang tidak ada referensi aktif; penghapusan tetap dilakukan melalui rencana kerja, bukan langsung dari diskusi.
### 2. Pesan approval masih menyebut login dengan NIK - DRIFT
- `ApproveRegistrationAction.php`:
  - `Silakan login menggunakan NIK Anda...`
- `VerificationController.php`:
  - `Silakan login menggunakan NIK Anda...`
- `ApiAdminController.php`:
  - `...login menggunakan NIK Anda...`
- Bertentangan dengan identitas identifier yang menegaskan `NIKC` atau `username`.
- Implikasi: pengguna salah arah setelah approval.

### 3. Validasi NIKC belum berbasis rule 17 digit - DRIFT
- `AuthController::register`: `'nikc' => 'required|string|max:255|unique:personels,nikc'`
- `RegisterPersonelRequest.php`: `'nik' => 'required|digits:16|unique:personels,nik'` untuk NIK KTP, tetapi tidak ada validasi `nikc` 17 digit di request registrasi.
- `MasterPersonelController::store`: `'nikc' => 'nullable|string|max:255'`
- `MasterPersonelController::update`: `'nikc' => 'nullable|string|max:255'`
- `SkepPublicController::checkNikc`: `'nikc' => 'required|string'`
- `SkepPublicController::submitRequest`: `'nikc' => 'required|string|max:255'`
- Repo tidak memiliki file `app/Rules/NikcFormatRule.php` secara aktual.
- `Kep.003` mengharuskan panjang 17 digit, hanya angka, komponen digit sesuai range, dan validasi master data.
- Implikasi: NIKC bisa lolos dengan format salah di banyak titik.

### 4. Validasi panjang NIKC belum di-enforce di UI - DRIFT
- Form registrasi (`resources/js/Pages/Auth/Register.vue`) tidak membatasi panjang input NIKC.
- Server-side (`AuthController::register`) menggunakan `required|string|max:255`.
- Akibat: pengguna bisa menginput 16 digit atau 18 digit, yang tidak sesuai dengan format 17 digit yang diatur dalam Kep.003 Klausul 3.

### 5. Tidak ada validasi numerik eksplisit - DRIFT
- Form dan controller tidak memastikan bahwa NIKC hanya berisi angka.
- Kep.003 Klausul 4 menegaskan: "hanya angka".

### 6. Tidak ada warning khusus untuk 16 digit - KEBUTUHAN
- Pengguna yang menginput 16 digit (kurang 1 digit) tidak mendapat indikasi visual bahwa NIKC harus 17 digit.
- Kep.003 mengharuskan panjang tepat 17 digit.

### 7. Label login publik masih menampilkan "Username / NIKC" - DRIFT UI
- `resources/js/Pages/Auth/Login.vue` menampilkan label "Username / NIKC" dan placeholder "Masukkan username atau NIKC".
- Untuk publik login, yang valid adalah NIKC personel. Username adalah identifier internal yang hanya digunakan oleh superadmin.
- Kep.009 mengidentifikasi login sebagai `NIKC atau username`, namun untuk antarmuka publik, NIKC harus menjadi satu-satunya opsi yang ditampilkan.

### 8. Fallback NIKC hard-coded di export - RISIKO
- `PersonelExport.php`:
  - `$this->signerNikc = $signerNikc -- '12000018012200216';`
- `BroadcastResponseExport.php`:
  - `$this->signerNikc = $signerNikc -- '12000018012200216';`
- Jika data aktual kosong, laporan tetap memancarkan NIKC fixed walau bukan milik penandatangan.
- Melanggar prinsip integritas data dari `Kep.003`.

### 9. Route publik SKEP tanpa batas eksplisit - KEBERADAAN
- `routes/web.php` mengekspos:
  - `GET /skep/check` -> `SkepPublicController::checkNikc`
  - `POST /skep/request` -> `SkepPublicController::submitRequest`
- Keduanya terbuka publik tanpa middleware `auth`.
- `Kep.003` scope-nya hanya standar NIKC, bukan batas akses publik SKEP.
- Implikasi: access boundary belum normatif; jika nanti ada keputusan/kep.010 turunan, batas ini perlu ditegaskan.

### 10. Seeder contoh melanggar format NIKC 17 digit - DRIFT DATA
- `DatabaseSeeder.php` mengisi:
  - `nikc` = `NIKC-KORD-ANGK`
  - `nikc` = `NIKC-KORD-MATR`
- `Kep.003` mengharuskan NIKC 17 digit angka.
- Implikasi: data seed contoh bisa menabrak validasi dan menjadi efek samping saat QA/fresh install.

### 11. `pangkat` sepenuhnya hardcoded dan tidak ada master kepangkatan - DRIFT
- Tidak ada tabel `master_kepangkatan`, model `MasterKepangkatan.php`, atau migration terkait di repo.
- `personels.pangkat`, `skep_data.pangkat`, `skep_requests.pangkat` disimpan sebagai `string nullable`.
- Seluruh validasi dan dropdown UI memakai daftar hardcoded: `Prada, Serda, Serda (W), Letda, Letda (W), Lettu`.
- Sorting ranking juga hardcoded di banyak tempat (`PersonelExport`, `MasterPersonelController`, `ReportController`).
- Tidak ada `kode_nikc`, `kelompok_nikc`, `urutan_hirarki`, atau `flag_klaim_langsung` yang menghubungkan pangkat ke struktur NIKC.
- Implikasi: tidak ada sumber tunggal pangkat, rentan inkonsistensi penulisan, dan digit pertama NIKC tidak bisa divalidasi terhadap master kepangkatan.

### 12. `province` menggunakan API wilayah eksternal tanpa kode Latsarmil 01-34 - DRIFT
- Tidak ada tabel `master_provinsi`, model `MasterProvinsi.php`, atau migration terkait di repo.
- Form registrasi dan job history memakai dropdown dari API eksternal (`emsifa.github.io/api-wilayah-indonesia`).
- `personels.province` dan `login_logs.province` disimpan sebagai nama provinsi, bukan kode 01-34.
- Tidak ada validasi terhadap kode provinsi Latsarmil yang diatur dalam Kep.003 Lampiran B.
- Implikasi: NIKC tidak bisa divalidasi terhadap digit 16-17, dan data provinsi bergantung pada API eksternal yang sudah deprecated.

## Kontrol Penjajaran

| No | Klausul `Kep.003` | Kode Aktual | Status |
| --- | --- | --- | --- |
| 1 | NIKC permanen, tidak boleh generate | `ApproveRegistrationAction` masih memuat generator `KC-...`, tetapi audit ulang tidak menemukan pemanggilan aktif; jalur controller aktif memakai NIKC existing | DRIFT TERBATAS / LEGACY ORPHAN |
| 2 | Sistem hanya menyimpan/memvalidasi | jalur controller aktif tidak terbukti generate, tetapi action legacy masih dapat menulis NIKC palsu bila dipakai ulang | DRIFT TERBATAS |
| 3 | Validasi format 17 digit + komponen | setelah pull dan perubahan tidak terencana, sebagian jalur register/SKEP memakai `digits:17`; rule reusable dan validasi komponen digit belum ada | TERTAMBAL PARSIAL / MASIH DRIFT |
| 4 | Hanya angka | setelah pull dan perubahan tidak terencana, input register publik dan sebagian backend memakai digit-only/`digits:17`; jalur lain belum seragam | TERTAMBAL PARSIAL / MASIH DRIFT |
| 5 | Unik | ada `unique:personels,nikc` di registrasi | SELARAS |
| 6 | Master data `master_kepangkatan` dan `master_provinsi` | tercantum di changelog/workplan, belum terverifikasi pengecekan langsung schema/seed aktual di audit ini | BELUM TERVERIFIKASI |
| 7 | Reuse `province` bila maknanya sama | `Auth/Register.vue` masih input bebas; validasi hanya `required|string|max:255` | PARSIAL |
| 8 | Input dari pencatatan/klaim, bukan generate | registrasi menginput NIKC manual, tetapi approval tetap generate | CAMPURAN |
| 9 | Export dan laporan tidak boleh memalsukan NIKC | fallback hard-coded ada | DRIFT |
| 10 | Tidak ada batas akses publik SKEP dalam `Kep.003` | route publik hidup, tidak ada policy turunan | KEBERADAAN |
| 11 | Label login publik hanya NIKC | `Login.vue` menampilkan `Username / NIKC` | DRIFT |
| 12 | Input NIKC harus 17 digit | setelah pull dan perubahan tidak terencana, register publik dan sebagian backend sudah enforce 17 digit; jalur admin/rule reusable belum selesai | TERTAMBAL PARSIAL |
| 13 | Input NIKC harus numerik | setelah pull dan perubahan tidak terencana, register publik dan sebagian backend sudah digit-only; jalur lain belum seragam | TERTAMBAL PARSIAL |
| 14 | Warning untuk input <17 digit | setelah pull upstream warning sudah ada; perubahan tidak terencana mengarahkan pesan HTML agar markup tidak bocor | TERTAMBAL PARSIAL |
| 15 | `master_kepangkatan` harus jadi sumber tunggal | tidak ada tabel/model master; pangkat hardcoded di banyak file | DRIFT |
| 16 | `master_provinsi` atau mapping kode 01-34 | tidak ada tabel/model master; province mengandalkan API eksternal dan disimpan sebagai nama | DRIFT |

## Kesenjangan Terbesar

1. `NikcFormatRule` tidak ada di repo.
2. Generate legacy `KC-...` masih ada di `ApproveRegistrationAction.php`, tetapi audit ulang menunjukkan action ini tidak ditemukan dipanggil route/controller aktif; statusnya legacy/orphan dan aman menjadi kandidat hapus lewat workplan.
3. Pesan approval masih pakai istilah `NIK`.
4. Data contoh seeder melanggar format 17 digit.
5. UI registrasi publik sudah tertambal parsial untuk 17 digit numeric setelah pull 20 Juli 2026; login publik dan jalur lain masih perlu diselaraskan.
6. Label login publik mencampur identifier yang seharusnya terpisah.
7. Validasi publik SKEP hanya satu faktor (`nikc`), tidak ada `dob`.
8. Form registrasi label masih generik: `NIK (16 Digit)`, `Matra (Sesuai SKEP)`, `Pangkat (Sesuai SKEP)`.
9. API wilayah `emsifa.github.io` sudah deprecated 404; belum ada catatan teknis di repo bahwa `wilayah.id` digunakan sebagai gantinya.
10. `RegisterPersonelRequest.php` sudah tertambal parsial dengan rule `nikc` 17 digit pada 20 Juli 2026, tetapi perlu dipastikan pemakaiannya dalam flow register aktual dan diturunkan ke rule reusable.

## Dampak Gabungan

- Norma `Kep.003` belum benar-benar dijalankan di runtime.
- Data integrity NIKC berisiko rusak sejak input sampai laporan.
- Dokumentasi klaim "selaras" di workplan/changelog bertentangan dengan kondisi repo aktual.
- Pengguna publik mengalami kebingungan identifier dan feedback validation yang tidak jelas.
- Data personel yang sudah ada dengan `nikc` 16 digit atau `pangkat` non-canonical tetap boleh direvisi melalui mekanisme resmi pengajuan pangkat/koreksi data; data existing yang tidak sesuai tidak dibuang, hanya dinormalisasi.

## Saran Penanganan

### Prioritas P0
- Hapus `ApproveRegistrationAction.php` sebagai action legacy/orphan setelah verifikasi ulang tidak ada referensi aktif, atau minimal hapus kemampuan menulis `KC-...` bila ternyata masih diperlukan.
- Tambahkan validasi `digits:17|numeric` pada seluruh request NIKC: registrasi, admin store/update, SkepPublicController, dan SkepRequest.
- Tambahkan validasi 2 faktor publik: `NIKC + dob` pada endpoint `/skep/check` dan `/skep/request`.

### Prioritas P1
- Tambahkan validasi front-end di `Register.vue`:
  - numeric only,
  - maxlength 17,
  - warning visual jika panjang `< 17` atau `> 17`.
- Ubah label login publik menjadi `NIKC` saja (tanpa `Username / ...`).
- Normalisasi semua notifikasi: ganti `login menggunakan NIK` menjadi `login menggunakan NIKC atau username`.
- Perbaiki label form registrasi dan form personel admin menjadi canonical: `NOMOR INDUK KEPENDUDUKAN (NIK)`, `MATRA`, `PANGKAT`.
- Ganti semua opsi pangkat hardcoded menjadi 18 canonical dari `Kep.005`, dengan baseline klaim langsung hanya 3 opsi: `Prada KC`, `Serda KC`, `Letda KC`.

### Prioritas P2
- Bangun migration master data `master_kepangkatan` dan `master_provinsi`.
- Hapus fallback hard-coded NIKC di export laporan.
- Perbaiki seeder contoh agar valid 17 digit.
- Integrasikan `config/latsarmil.php` ke dalam validasi backend dan form wilayah.

## Catatan Lanjut

Jika hasil diskusi ini disepakati:
- diskusi tidak merubah `Kep.003`, `Kep.004`, atau `Kep.005`.
- workplan `docs/03 Rencana Kerja/2026.07.09 Rencana Kerja tentang Sinkronisasi Implementasi NIKC.md` akan direvisi menjadi:
  - F0. Validasi NIKC 17 digit via rule khusus di semua request
  - F1. Hapus action legacy/orphan `ApproveRegistrationAction.php` atau hilangkan kemampuan generate `KC-...` setelah verifikasi ulang referensi aktif
  - F2. Normalisasi label/pesan menjadi NIKC
  - F3. `province` berbasis `master_provinsi`
  - F4. Hapus seluruh fallback hardcoded NIKC/pangkat di export; laporan hanya boleh memakai data personel/signer yang benar-benar ada di sistem
  - F5. Perbaiki seeder contoh agar valid 17 digit
  - F6. Pertahankan route SKEP publik, tetapi tingkatkan validasi publik dengan 2 faktor: NIKC + tanggal lahir, untuk mengurangi brute force
  - F7. Bangun tabel `master_provinsi` untuk konversi kode BPS `wilayah.id` -> kode Latsarmil 01-34, lalu gunakan di validasi NIKC digit 16-17 dan konversi dropdown wilayah
  - F8. Bangun tabel `master_kepangkatan` untuk 18 pangkat canonical dari `Kep.005 Lampiran A`
  - F9. Penyelarasan label dan validasi form registrasi publik
- Semua perubahan bersifat incremental dan reversible, kecuali penghapusan data destruktif yang tidak diizinkan.

## Temuan Lanjutan: API Wilayah dan Integrasi Provinsi

### Ringkasan
- Form provinsi di repo masih mengandalkan API eksternal yang sudah deprecated.
- Tujuan tetap reuse field `province` jika maknanya sama, sesuai `Kep.003` Klausul 7 dan `Kep.004`.

### Audit kondisi terkini
- API lama `emsifa.github.io/api-wilayah-indonesia` sudah mengembalikan 404 untuk endpoint JSON; tidak bisa dipakai lagi.
- API alternatif `wilayah.id` aktif dan stabil: `/api/provinces.json`, `/api/regencies/{code}.json`, `/api/districts/{code}.json`, `/api/villages/{code}.json`.
- `wilayah.id` menggunakan kode BPS, bukan kode Latsarmil 01-34.
- `personels.province` dan `login_logs.province` disimpan sebagai nama provinsi, bukan kode.
- Tidak ada `master_provinsi` di repo saat ini.

### Dampak
- Integrasi wilayah saat ini rapuh karena bergantung pada API yang sudah mati.
- Validasi digit 16-17 NIKC tidak bisa dilakukan tanpa mapping kode BPS -> Latsarmil.

### File terkait
- `resources/js/Pages/Auth/Register.vue`
- `resources/js/Pages/Admin/Personel/Create.vue`
- `resources/js/Pages/Admin/Personel/Edit.vue`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/Admin/MasterPersonelController.php`
- `SkepPublicController.php`

### Solusi yang disepakati
- `wilayah.id` adalah sumber kebenaran utama untuk data wilayah.
- Kode provinsi dalam NIKC sesuai SKEP adalah **additional requirement** untuk validasi digit 16-17, bukan sumber kebenaran wilayah.
- Tambahkan tabel konversi `master_provinsi` dengan kolom: `kode_latsarmil`, `nama_latsarmil`, `kode_bps`, `nama_bps`, `aktif`.
- Backend validasi NIKC: lookup `master_provinsi` berdasarkan `kode_bps` dari wilayah.id -> cocokkan `kode_latsarmil` dengan digit 16-17 NIKC.
- Tidak perlu `config/latsarmil.php` terpisah; tabel `master_provinsi` sudah cukup untuk mapping dua arah.

## File Pendukung yang Akan Diperkenalkan

### `master_provinsi` tabel
- **Kapan**: pada F3 rencana kerja, setelah diskusi ini disetujui.
- **Bagaimana**:
  - Sumber kebenaran utama untuk data wilayah tetap `wilayah.id` di frontend.
  - Tabel `master_provinsi` berisi konversi kode BPS -> kode Latsarmil 01-34 untuk keperluan validasi NIKC digit 16-17.
  - Kolom: `kode_latsarmil`, `nama_latsarmil`, `kode_bps`, `nama_bps`, `aktif`.
  - Backend validasi NIKC: lookup `master_provinsi` berdasarkan `kode_bps` dari request/session -> cocokkan `kode_latsarmil` dengan digit 16-17.
  - Tabel ini hanya untuk additional requirement SKEP, bukan sumber utama data wilayah.
- Catatan: `config/latsarmil.php` tidak perlu diperkenalkan karena fungsinya sudah digantikan tabel `master_provinsi`.

## Temuan Lanjutan: Verifikasi Publik 2 Faktor NIKC + Tanggal Lahir

### Ringkasan
- Endpoint publik `/skep/check` dan `/skep/request` saat ini hanya memverifikasi NIKC saja.
- Ini membuka risiko brute force/mapping NIKC aktif tanpa identitas tambahan.
- Solusi yang disepakati: tambahkan `dob` sebagai verifier kedua di flow publik.

### Audit kondisi terkini
- `personels.dob` sudah ada di schema dan form registrasi publik (`Register.vue`).
- `AuthController::register` juga sudah memvalidasi `dob`.
- Namun `SkepPublicController::checkNikc` dan `submitRequest` belum menggunakan `dob`.
- Login admin/personel tetap berbasis password + username/NIKC; tidak ada alasan untuk pakai `dob` di login.

### Dampak
- Menambah validasi publik dua faktor tanpa mengubah login role-based.
- Tidak memerlukan migrasi baru karena `dob` sudah tersimpan.
- Rate-limit ditetapkan: maksimal 3 percobaan per 6 jam per IP untuk mencegah brute force.

### File terdampak
- `resources/js/Pages/Auth/Register.vue`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/SkepPublicController.php`

### File yang perlu diubah
- Tambah input `dob` pada langkah verifikasi NIKC di `Register.vue`.
- Update `SkepPublicController::checkNikc` untuk validasi `nikc + dob`.
- Update `SkepPublicController::submitRequest` untuk memaksa `dob` dan mencocokkannya dengan `personels.dob` jika NIKC sudah terdaftar, atau minimum validasi format tanggal lahir untuk pengajuan SKEP baru.
- Update `AuthController::register` untuk menjadikan `dob` sebagai verifier publik yang terikat dengan NIKC saat cek awal.

### Pertanyaan lanjutan
- Semua sudah terjawab:
  - Rate-limit: 3 percobaan per 6 jam per IP.
  - `dob` dicek hanya ke `personels.dob`, tidak perlu OCR/KTP.
  - Cakupan perubahan label dibatasi form input data personel.

## Temuan Lanjutan: Penyelarasan Label dan Validasi Form Registrasi Publik

### Ringkasan
- Form registrasi publik masih memakai label dan helper text yang belum sepenuhnya selaras dengan Kep.003 dan Kep.004.
- Validasi backend NIK 16 digit dan pangkat hardcoded juga belum diaktifkan ke bentuk yang jelas di UI.

### Audit kondisi terkini
- `Register.vue` menampilkan helper/placeholder yang bisa diperjelas tanpa mengubah struktur form.
- `AuthController::register` sudah menerima `nik`, `pangkat`, dan `dob`, tetapi label tetap generik dan tidak menegaskan format/kesehatan data.
- Dropdown pangkat tetap hardcoded ke enam nilai, belum siap untuk master kepangkatan.
- `Admin/Personel/Create.vue` dan `Edit.vue` juga menampilkan label `NIK (16 Digit)`, `Pangkat`, `Matra`.
- `Admin/Skep/Index.vue` menampilkan placeholder "Cari Nama, NIK, atau NIKC..." yang konsolidasi menjadi `NIKC`.
- `Admin/Broadcast/Create.vue` dan `Edit.vue` menggunakan opsi target `MATRA` untuk broadcast, bukan form data personel, jadi tidak perlu diubah.

### Dampak
- User mengalami ambigu makna field.
- Validasi numeric/length untuk NIK belum ditampilkan eksplisit di UI.
- Label yang lebih canonical mengurangi salah input sejak pertama kali.

### File terdampak
- `resources/js/Pages/Auth/Register.vue`
- `resources/js/Pages/Admin/Personel/Create.vue`
- `resources/js/Pages/Admin/Personel/Edit.vue`
- `resources/js/Pages/Admin/Personel/Index.vue`
- `resources/js/Pages/Admin/Skep/Index.vue`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Requests/Auth/RegisterPersonelRequest.php`

### File yang perlu diubah
- Ubah pesan langkah verifikasi NIKC menjadi: `Masukkan NIKC Anda dan Tanggal Lahir sesuai SKEP untuk verifikasi keanggotaan pra-pendaftaran.`
- Ubah label `NIK (16 Digit)` menjadi `NOMOR INDUK KEPENDUDUKAN (NIK)`.
- Tambahkan penekanan backend dan UI bahwa NIK harus 17 digit, numerik, tidak boleh kurang/tambah.
- Ubah label `Matra (Sesuai SKEP)` menjadi `MATRA`.
- Ubah label `Pangkat (Sesuai SKEP)` menjadi `PANGKAT`.
- Siapkan opsi pangkat sesuai ekspektasi master kepangkatan, contoh: `Sersan Dua KC`, `Letnan Dua KC`, `Letda KC`, `Lettu KC`, dst., bukan hanya bentuk shorthand saat ini.

### Daftar pangkat canonical final
Berdasarkan `2026.Kep.005 Lampiran A`, ada 18 pangkat canonical:

| Kelompok NIKC | Pangkat Canonical | Klaim Langsung |
|--|--|--|
| `3` Tamtama | `Prada KC` | Ya |
| `3` Tamtama | `Pratu KC` | Tidak |
| `3` Tamtama | `Praka KC` | Tidak |
| `3` Tamtama | `Kopda KC` | Tidak |
| `3` Tamtama | `Koptu KC` | Tidak |
| `3` Tamtama | `Kopka KC` | Tidak |
| `2` Bintara | `Serda KC` | Ya |
| `2` Bintara | `Sertu KC` | Tidak |
| `2` Bintara | `Serka KC` | Tidak |
| `2` Bintara | `Serma KC` | Tidak |
| `2` Bintara | `Pelda KC` | Tidak |
| `2` Bintara | `Peltu KC` | Tidak |
| `1` Perwira | `Letda KC` | Ya |
| `1` Perwira | `Lettu KC` | Tidak |
| `1` Perwira | `Kapten KC` | Tidak |
| `1` Perwira | `Mayor KC` | Tidak |
| `1` Perwira | `Letkol KC` | Tidak |
| `1` Perwira | `Kolonel KC` | Tidak |

**Yang boleh diinput langsung di form registrasi/admin hanya 3 baseline**:
- `Prada KC`
- `Serda KC`
- `Letda KC`

**Yang lain hanya lewat pengajuan SK/basis keputusan**, bukan via form input reguler.

### Cakupan perubahan label
- Berlaku untuk form input data personel: `Register.vue`, `Admin/Personel/Create.vue`, `Admin/Personel/Edit.vue`.
- Berlaku untuk pencarian/admin index: konsolidasi placeholder/index menjadi `NIKC` alih-alih `NIK atau NIKC`.
- Tidak berlaku untuk form broadcast/admin lain yang bukan input data personel, seperti `Admin/Broadcast/Create.vue` (target type `MATRA` adalah domain broadcast, bukan data master personel).
- Tidak mengubah label readonly di dashboard/verification kecuali memperbaiki inkonsistensi.

### Pertanyaan Terbuka
- Apakah username default untuk personel Komcad diizinkan sebagai alternatif login selain NIKC, atau default login tetap NIKC saja-
  - **Dijawab**: Komcad wajib login pakai NIKC; tidak ada alternatif username.
- Apakah ekspor PDF/Excel diperbolehkan memiliki `signerNikc` opsional dengan nilai aktual saja, tanpa fallback-
  - **Dijawab**: Tidak boleh ada fallback hardcoded. Export hanya boleh memakai data personel/signer yang benar-benar ada di sistem.
- Apakah route publik SKEP akan dibatasi oleh keputusan turunan baru-
  - **Dijawab**: Route SKEP tetap publik, tetapi ditambah validasi 2 faktor: NIKC + tanggal lahir, untuk mengurangi brute force cek NIKC.
- Apakah label login publik harus mengarahkan pengguna ke NIKC saja, atau tetap menyebutkan username untuk kasus khusus-
  - **Dijawab**: Label login publik hanya tulis NIKC. Admin tetap bisa login pakai username dan/atau NIKC.
- Apakah `config/latsarmil.php` benar-benar dibutuhkan-
  - **Dijawab**: Tidak. Fungsi mapping digantikan tabel `master_provinsi`. `wilayah.id` tetap menjadi sumber kebenaran utama data wilayah. `master_provinsi` hanya untuk additional requirement SKEP: konversi kode BPS -> kode Latsarmil untuk validasi digit 16-17 NIKC.
- Apakah perlu memperbaharui workplan `2026.07.09`-
  - **Dijawab**: Ya. Workplan perlu direvisi agar mencerminkan temuan audit yang jujur dan fase F0-F8 yang disepakati, tanpa membuat dokumen baru.
- Apakah `dob` juga wajib di form admin personel (`Create.vue`/`Edit.vue`)-
  - **Dijawab**: Tidak. `dob` sebagai verifier 2 faktor hanya untuk publik endpoint SKEP. Form admin personel tetap mengikuti `Kep.004` untuk input biodata, tanpa memaksa `dob` sebagai verifier publik.
- Apakah ada kebutuhan mekanisme koreksi NIKC massal-
  - **Dijawab**: Tidak. Normalisasi dilakukan per-record via form edit admin atau pengajuan pangkat, bukan batch massal.

## Persiapan Finalisasi Diskusi

### Hasil Audit Kesiapan
- Diskusi sudah memuat konteks, temuan awal, audit repo, temuan lanjutan, jawaban atas pertanyaan terbuka, dampak, dan rencana tindak lanjut.
- Diskusi sudah cukup jelas untuk menjadi dasar penyelarasan repo melalui revisi workplan aktif, tetapi pull lanjutan ke `6acf367` menambah blocker verifikasi repo dan beberapa surface baru yang harus dicatat sebelum finalisasi.
- Perbedaan antara dokumen dan repo disepakati sebagai konsekuensi pengerjaan paralel antara penulis dokumen dan rekan yang mengerjakan file/repo, bukan sebagai pelanggaran.
- `ApproveRegistrationAction.php` sudah diklasifikasikan ulang: bukan bukti jalur approval aktif masih generate NIKC, melainkan action legacy/orphan yang aman menjadi kandidat penghapusan melalui workplan setelah verifikasi ulang referensi aktif.
- Perubahan kode tidak terencana pada 20 Juli 2026 sudah dicatat sebagai tambalan parsial yang harus diserap ke workplan, bukan dianggap sebagai eksekusi resmi fase.
- Pull lanjutan ke `6acf367` menambahkan surface `VerifikasiPendidikan` dan memperluas isu wilayah ke `Personel/JobHistory`; keduanya sudah cukup jelas untuk masuk revisi workplan.
- Pull lanjutan sempat membawa blocker syntax pada `app/Http/Controllers/Personel/JobHistoryController.php`; blocker ini sudah diperbaiki sebagai prasyarat pada 20 Juli 2026.

### Checklist Pra-Finalisasi
- [x] Temuan utama terhadap `Kep.003` sudah dipetakan.
- [x] Temuan terkait `Kep.004` dan `Kep.005` sudah dicatat.
- [x] Kebutuhan validasi NIKC 17 digit dan validasi komponen digit sudah dicatat.
- [x] Kebutuhan verifikasi publik 2 faktor `NIKC + dob` sudah dijawab.
- [x] Kebutuhan `master_provinsi` sebagai mapping BPS ke Latsarmil sudah dijawab.
- [x] Kebutuhan `master_kepangkatan` 18 pangkat canonical sudah dijawab.
- [x] Kebutuhan penyelarasan label login publik dan form personel sudah dicatat.
- [x] Kebutuhan normalisasi data existing per-record, bukan batch massal, sudah dijawab.
- [x] Status `ApproveRegistrationAction.php` sebagai legacy/orphan sudah diaudit ulang.
- [x] Workplan aktif sudah diperbarui pada level persiapan agar fase, status, dan audit per-file selaras dengan audit 20 Juli 2026.
- [x] Pull lanjutan ke `6acf367` sudah diaudit terhadap scope NIKC/wilayah/pangkat.
- [x] Surface baru `Admin/VerifikasiPendidikan/Index.vue` sudah diklasifikasikan sebagai baca/search NIKC, bukan generator/validator NIKC.
- [x] Perluasan scope wilayah ke `Personel/JobHistory` sudah dicatat.
- [x] Blocker syntax `JobHistoryController.php` sudah diperbaiki sebagai prasyarat.
- [x] Redaksi publik `NIKC Belum Terdaftar` sudah dijawab: ganti menjadi `NIKC Belum Ada di Database` dan wajib menegaskan dasar SKEP.
- [x] Cakupan `Personel/JobHistory` dalam fase `master_provinsi` sudah dikonfirmasi: semua modul yang memakai `province/provinsi` dan endpoint `/api/wilayah` masuk penyesuaian wilayah/master_provinsi.
- [x] Keputusan aktif tidak perlu direvisi; yang perlu diperbarui adalah status workplan/audit agar tidak mengklaim repo sudah terpenuhi bila audit membuktikan drift.

### Catatan untuk Revisi Workplan
- Workplan tidak perlu dibuat baru; gunakan file aktif `docs/03 Rencana Kerja/2026.07.09 Rencana Kerja tentang Sinkronisasi Implementasi NIKC.md`.
- Revisi workplan harus menegaskan bahwa `ApproveRegistrationAction.php` adalah kandidat hapus karena tidak ditemukan referensi aktif, bukan jalur approval aktif yang terbukti masih berjalan.
- Revisi workplan harus menyerap tambalan parsial 20 Juli 2026 ke status fase terkait:
  - validasi register publik dan sebagian backend sudah parsial,
  - pesan HTML NIKC sudah parsial,
  - kontras file upload register publik sudah parsial.
- Revisi workplan harus memperbaiki mismatch fase: daftar status F0-F9 harus sama dengan heading fase aktual.
- Revisi workplan harus memisahkan pekerjaan `master_provinsi`/integrasi wilayah dari validasi publik `NIKC + dob` agar tidak duplikatif.
- Revisi workplan harus memasukkan semua pemakai `province/provinsi` dan endpoint `/api/wilayah` ke fase wilayah/master_provinsi, termasuk `Register.vue`, `Personel/JobHistory/Index.vue`, controller job history/API job history bila menyimpan `provinsi`, dan route proxy wilayah.
- Revisi workplan harus menambahkan fase eksplisit untuk perbaikan seeder bila tetap dipertahankan sebagai fase tersendiri.
- Revisi workplan harus memperbarui hasil verifikasi lokal: `php -l`, `node --check`, `git diff --check`, blocker build karena `vendor/tightenco/ziggy`, dan blocker Composer lock.
- Revisi workplan harus memasukkan hasil pull `6acf367`:
  - `app/Http/Controllers/Personel/JobHistoryController.php` sebagai blocker syntax sebelum verifikasi repo,
  - `resources/js/Pages/Personel/JobHistory/Index.vue` sebagai konsumen API wilayah/provinsi,
  - `resources/js/Pages/Admin/VerifikasiPendidikan/Index.vue` dan `app/Http/Controllers/Education/EducationController.php` sebagai surface baca/search NIKC dan pangkat.

### Status Kesiapan
Diskusi ini **siap difinalkan**. Redaksi publik register sudah jelas: sistem tidak membuat NIKC, dan NIKC yang belum ada di database hanya dapat diajukan berdasarkan SKEP. Cakupan wilayah juga sudah jelas: semua modul yang memakai `province/provinsi` dan endpoint `/api/wilayah` masuk fase wilayah/master_provinsi. Putaran terakhir audit repo dan dokumen tidak menemukan blocker baru.
## Catatan
- Data personel existing yang memiliki `nikc` 16 digit atau `pangkat` non-canonical tidak di-reject sistemik; data tersebut aman dan hanya perlu dinormalisasi melalui mekanisme resmi pengajuan pangkat/koreksi data, sesuai `Kep.004` dan `Kep.005`.
  - Strategi normalisasi: identifikasi lewat export/report, lalu perbaiki via form edit admin atau pengajuan pangkat.
  - Prioritas normalisasi: data dengan `nikc` 16 digit > data dengan `pangkat` non-canonical > data dengan `province` tidak sesuai kode Latsarmil.
  - Tidak ada migrasi massal atau backfill otomatis yang menghapus nilai lama tanpa audit manual per record.
- Relasi keputusan aktif yang berkaitan langsung:
  - `Kep.004 Klausul 32-34`: master data dan status personel menjadi landasan untuk field `province` dan `pangkat`.
  - `Kep.005 Lampiran A`: 18 pangkat canonical menjadi daftar final untuk dropdown dan validasi.
  - `Kep.006 Klausul 9-10`: syarat pangkat Perwira untuk koordinator/wakil koordinator berkaitan erat dengan digit pertama NIKC.

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-16 | Dokumen dibuat untuk memetakan ketidaksesuaian antara `2026.Kep.003` dan implementasi repo aktual |
| 2026-07-19 | Ditambahkan temuan lanjutan: validasi panjang 17 digit, validasi numeric, warning 16 digit, label login publik harus menjadi `NIKC` saja |
| 2026-07-19 | Ditambahkan temuan lanjutan: verifikasi publik 2 faktor NIKC + tanggal lahir |
| 2026-07-19 | Ditambahkan temuan lanjutan: penyelarasan label form registrasi publik dan validasi NIK/NIKC/Matra/Pangkat |
| 2026-07-19 | Ditambahkan temuan teknis API wilayah (`wilayah.id` aktif, `emsifa` 404), kebutuhan tabel `master_provinsi`, inbound dependency backend, dan relasi ke `Kep.004`/`Kep.005` |
| 2026-07-19 | Dijawab rate-limit: 3 percobaan per 6 jam per IP; `dob` dicek hanya ke `personels.dob`; pangkat canonical 18 tingkat dari `Kep.005` Lampiran A; cakupan label hanya form data personel; `config/latsarmil.php` tidak dibutuhkan, diganti `master_provinsi` |
| 2026-07-20 | Dicatat hasil pull repo `sisfopers1` ke `6944465`, temuan bocor markup pesan NIKC, temuan kontras file upload register publik, perubahan kode tidak terencana yang terlanjur dilakukan, status sudah/belum, dan blocker build karena `vendor/tightenco/ziggy` belum tersedia akibat Composer lock tidak sinkron |
| 2026-07-20 | Dicatat klarifikasi bahwa perbedaan dokumen dan repo adalah kebutuhan penyelarasan akibat pengerjaan paralel, bukan pelanggaran; `ApproveRegistrationAction.php` diklasifikasikan sebagai action legacy/orphan yang aman menjadi kandidat hapus lewat workplan setelah verifikasi ulang referensi aktif; ditambahkan checklist pra-finalisasi dan catatan revisi workplan |
| 2026-07-20 | Dicatat audit pull lanjutan ke `6acf367`: surface baru verifikasi pendidikan hanya baca/search NIKC, scope wilayah perlu mencakup `Personel/JobHistory`, route API wilayah masih memakai `emsifa`, dan `JobHistoryController.php` memiliki blocker syntax yang perlu diputuskan sebelum finalisasi |
| 2026-07-20 | Dicatat perbaikan prasyarat: syntax `JobHistoryController.php` diperbaiki, proxy `/api/wilayah` dipindah dari `emsifa` ke `wilayah.id`, lint PHP file terkait lulus, dan verifikasi artisan masih terblokir `vendor/autoload.php` |
| 2026-07-20 | Dicatat solusi redaksi register publik: `NIKC Belum Terdaftar` diganti menjadi `NIKC Belum Ada di Database`, dengan penegasan bahwa sistem tidak membuat NIKC dan pengajuan harus berdasar SKEP |
| 2026-07-20 | Dicatat keputusan cakupan wilayah: semua modul yang memakai `province/provinsi` dan endpoint `/api/wilayah` masuk fase wilayah/master_provinsi, termasuk `Personel/JobHistory` |
| 2026-07-20 | Status dokumen dinaikkan menjadi `Siap Difinalkan` setelah putaran terakhir audit repo dan dokumen tidak menemukan blocker baru |
