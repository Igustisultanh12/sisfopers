> Status Arsip: Ditetapkan
> Ditetapkan pada: 2026-07-09 20:26:17 WIB
> Rujukan keputusan: docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md
> Catatan: diskusi ini telah difinalkan dan menjadi dasar lahirnya keputusan serta workplan turunan

# Diskusi
## tentang NOMOR INDUK KOMPONEN CADANGAN

Status: `Ditetapkan`
Tanggal dibuka: 9 Juli 2026
Identitas dokumen: 2026.07.09 Diskusi tentang Nomor Induk Komponen Cadangan
Jenis dokumen: Diskusi
Domain: Manajemen Anggota
Topik: Nomor Induk Komponen Cadangan, struktur 17 digit, validasi, keunikan, verifikasi, permanensi, dan relasi dengan master data anggota
Keputusan terkait: `2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan`
Rencana kerja terkait: - (akan diturunkan setelah keputusan final jika diperlukan)
Mengubah: -
Digantikan oleh: `2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan`
Regulasi: Peraturan Menteri Pertahanan Republik Indonesia Nomor 3 Tahun 2021 tentang Pembentukan, Penetapan, dan Pembinaan Komponen Cadangan
Peserta: User, AI Agent

## Pemicu

User melihat bahwa NIKC perlu menjadi catatan khusus karena diatur dalam Peraturan Menteri Pertahanan. Karena NIKC menyentuh identitas resmi, validasi format, verifikasi anggota, serta relasi dengan master data dan klaim akun, topik ini perlu dibuka sebagai diskusi tersendiri agar boundary domain tetap rapi.

## Temuan Awal

Audit awal terhadap dokumen aktif yang beririsan menghasilkan temuan berikut.

### Dokumen yang diaudit

- `AGENTS.md`
- `docs/README.md`
- `docs/CHANGELOG.md`
- `docs/01 Profil Sistem/PROJECT_STATUS.md`
- `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
- `docs/04 Diskusi/README.md`
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Master Data dan Status Anggota.md`
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Klaim Akun Login Logout Role dan Verifikasi Anggota.md`
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Pangkat dan Format Nama Anggota.md`
- `docs/04 Diskusi/2026.07.09 Diskusi tentang Broadcast Kegiatan, Presensi, Monitoring, dan Laporan Operasional.md`
- `docs/04 Diskusi/2026.07.09 Diskusi tentang Format Nama Lengkap dengan Prefix Pangkat dan Suffix Gelar.md`

### File kode dan schema yang beririsan

- `app/Actions/ApproveRegistrationAction.php`
- `app/Http/Controllers/Admin/VerificationController.php`
- `app/Http/Controllers/Admin/MasterPersonelController.php`
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Export/PersonelExport.php`
- `app/Http/Middleware/EnsureProfileComplete.php`
- `app/Models/Personel.php`
- `database/migrations/2026_07_06_055601_create_personels_table.php`
- `database/migrations/2026_07_08_062112_add_pangkat_and_nikc_to_personels_table.php`
- `resources/js/Pages/Auth/Register.vue`
- `resources/js/Pages/Admin/Personel/Edit.vue`
- `resources/js/Pages/Admin/Personel/Index.vue`
- `resources/js/Pages/Admin/Verification/Index.vue`
- `resources/js/Pages/Personel/Dashboard.vue`

### Catatan Penempatan Rujukan

- PDF Peraturan Menteri Pertahanan paling cocok disalin ke `docs/10 Referensi Internal/` sebagai bahan rujukan hukum yang dipakai lintas diskusi
- jika ada surat keputusan eksternal yang menjadi sumber verifikasi, simpan juga di `docs/10 Referensi Internal/`
- jika diskusi ini sudah final, salinan PDF Permenhan dipindahkan ke `docs/10 Referensi Internal/` dan boleh dijadikan referensi dalam diskusi maupun dokumen turunan repo
- jika nanti lahir keputusan normatif milik repo ini, dokumennya harus hidup di `docs/02 Keputusan/`
- jika nanti lahir template, SOP, checklist, atau prosedur turunan dari keputusan, tempatnya di `docs/11 Referensi Teknis/`
- detail auto-populate dari NIKC, pembaruan pangkat berbasis alasan, nomor keputusan, dan unggah PDF keputusan lebih tepat difinalkan di diskusi master data anggota serta diskusi klaim akun dan verifikasi anggota
- alur `NIKC belum ada di sistem`, termasuk `pending verifikasi`, nomor keputusan tambahan, dan unggah PDF keputusan, dinyatakan out-of-scope untuk diskusi ini dan wajib dibahas di diskusi modul lain

### Rujukan Diskusi Terkait

Kalau hasil diskusi ini nantinya perlu dicatat atau diturunkan ke scope lain, catat di diskusi berikut:

- `docs/04 Diskusi/2026.07.03 Diskusi tentang Master Data dan Status Anggota.md`
  - untuk master kepangkatan
  - untuk master provinsi yang dipakai ulang sebagai `province` bila maknanya sama
  - untuk aturan auto-fill pangkat dari NIKC dan koreksi pangkat berbasis histori

- `docs/04 Diskusi/2026.07.03 Diskusi tentang Klaim Akun Login Logout Role dan Verifikasi Anggota.md`
  - untuk alur klaim akun
  - untuk verifikasi manual
  - untuk alur `NIKC belum ada di sistem`, `pending verifikasi`, nomor keputusan tambahan, dan unggah PDF keputusan

- `docs/04 Diskusi/2026.07.03 Diskusi tentang Pangkat dan Format Nama Anggota.md`
  - untuk hirarki pangkat yang lebih luas di luar digit NIKC
  - untuk pengaruh perubahan pangkat terhadap format tampilan nama bila dibutuhkan

- `docs/04 Diskusi/2026.07.09 Diskusi tentang Format Nama Lengkap dengan Prefix Pangkat dan Suffix Gelar Pendidikan.md`
  - untuk detail format nama lengkap yang memakai prefix pangkat dan suffix gelar
  - untuk batas antara data pangkat formal dan format tampilan nama

### Kondisi repo yang terverifikasi

- repo masih berada pada status `parsial`
- struktur lifecycle dokumen sudah aktif dan wajib dipatuhi
- topik NIKC sudah muncul di kode aktual, tetapi belum memiliki rumah diskusi tersendiri
- data personel sudah memuat field `nikc` dan dipakai pada alur registrasi, verifikasi, pencarian, ekspor, dan tampilan dashboard
- ada indikasi ketidaksinkronan antara cara NIKC diperlakukan di kode dan aturan normatif yang berasal dari Permenhan

### Temuan dari dokumen aktif yang beririsan

- `docs/README.md` dan `docs/04 Diskusi/README.md` mewajibkan topik yang belum final tetap hidup di `04 Diskusi`
- `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md` melarang pelahiran keputusan, workplan, atau referensi teknis baru sebelum diskusi final
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Master Data dan Status Anggota.md` sudah menyebut `nikc` sebagai bagian data anggota, sehingga topik ini harus menjaga boundary agar tidak bercampur dengan CRUD anggota umum
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Klaim Akun Login Logout Role dan Verifikasi Anggota.md` sudah membahas klaim akun dan verifikasi anggota, sehingga NIKC di sini hanya boleh menjadi dependensi, bukan menggantikan domain akun
- `docs/04 Diskusi/2026.07.03 Diskusi tentang Pangkat dan Format Nama Anggota.md` sudah membahas pangkat sebagai domain tersendiri; digit kepangkatan pada NIKC hanya menjadi irisan teknis, bukan rumah utama

### Kondisi kode aktual

- ada alur approval registrasi yang membentuk NIKC dari komponen kode internal
- ada tampilan registrasi yang meminta input NIKC
- ada controller verifikasi admin yang membaca NIKC untuk proses persetujuan
- ada controller master personel yang mencari berdasarkan NIKC
- ada export personel yang menampilkan NIKC sebagai kolom laporan
- ada migration yang membuat kolom `nikc` dan migration lanjutan yang menambah `pangkat` serta `nikc`
- ada dashboard personel dan halaman admin yang menampilkan NIKC pada UI
- field `province` sudah ada, jadi dipakai ulang untuk kebutuhan NIKC jika maknanya sama; jika maknanya berbeda, baru tambah field baru yang eksplisit
- `matra` sudah diperlakukan statik di schema dan validasi, sehingga arah hardcode konsisten dengan kode aktual
- `pangkat` sudah dipakai sebagai atribut aktif personel, tetapi belum ada master data kepangkatan yang rapi sebagai sumber kebenaran formal
- `ApproveRegistrationAction` masih membangkitkan NIKC baru dengan format lama, ini bertentangan langsung dengan arah NIKC permanen
- daftar kode provinsi 01-34 tetap diperlukan untuk NIKC dan dapat disimpan pada field `province` yang sudah ada selama maknanya memang provinsi Latsarmil

## Temuan Repo Aktual

Temuan yang perlu menjadi dasar revisi repo:

- alur approval masih membuat NIKC baru
- form registrasi masih meminta `matra`, `pangkat`, dan `nikc` sebagai input bebas
- controller admin masih menyimpan `matra` dan `pangkat` dari pilihan yang belum dibakukan sebagai master data resmi
- schema personel sudah memiliki `province`, sehingga untuk kebutuhan provinsi yang berbeda makna perlu field tambahan yang eksplisit, bukan penggantian diam-diam
- migration tambahan `pangkat` dan `nikc` masih perlu ditinjau ulang agar tidak melawan schema awal yang sudah ada

### Drift Implementasi yang Sudah Dicatat

Perbedaan antara keputusan yang sedang dibahas dan kode aktual di repo sudah dicatat sebagai drift, bukan sebagai temuan baru yang perlu diulang.

- `ApproveRegistrationAction` masih membangkitkan NIKC baru dengan format lama `KC-...`, padahal arah diskusi menegaskan NIKC tidak digenerate sistem
- form registrasi dan controller admin masih menganggap `matra`, `pangkat`, dan `nikc` sebagai input bebas, padahal arah diskusi menahan input NIKC ke pencatatan atau klaim yang lebih terstruktur
- `province` masih tampil sebagai input bebas di UI, padahal arah diskusi menghendaki pemakaian ulang field yang sama dengan pilihan data yang jelas dan searchable dropdown atau combobox
- migration tambahan `pangkat` dan `nikc` masih perlu diselaraskan dengan schema awal agar tidak menciptakan duplikasi makna

Drift di atas tidak mengubah hasil diskusi ini. Drift tersebut hanya menjadi bahan penurunan ke rencana kerja setelah keputusan final lahir.

### Penanganan Drift

Agar drift tidak menjadi kendala di kemudian hari, arah penanganannya sudah ditetapkan sebagai berikut:

- alur approval harus berhenti membangkitkan NIKC baru dan hanya membaca NIKC resmi yang sudah tercatat
- input NIKC pada registrasi harus diperlakukan sebagai pencatatan atau klaim sesuai konteks, bukan generator identitas
- `matra` tetap hardcode dan tidak dibangun sebagai master data
- `pangkat` dan `province` yang dipakai untuk NIKC harus mengambil sumber data yang jelas, dengan `province` tetap dipakai ulang bila maknanya sama
- UI `province` harus disiapkan sebagai pilihan yang searchable, bukan input bebas
- migration yang beririsan harus diselaraskan agar tidak ada duplikasi makna atau schema yang saling menabrak
- seluruh penyesuaian teknis di atas diturunkan ke rencana kerja setelah keputusan final, supaya diskusi ini tetap fokus pada norma dan batas scope

## Rencana Perbaikan Repo

### 1. Hentikan generate NIKC baru

File yang perlu direvisi:

- `app/Actions/ApproveRegistrationAction.php`

Arah perbaikannya:

- hapus formulasi `KC-...` yang membangkitkan NIKC baru
- ubah approval supaya hanya menulis NIKC dari keputusan resmi yang sudah ada
- jika NIKC belum tersedia, jangan diproduksi di action ini

### 2. Selaraskan registrasi dengan alur NIKC permanen

File yang perlu direvisi:

- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Requests/Auth/RegisterPersonelRequest.php`
- `resources/js/Pages/Auth/Register.vue`

Arah perbaikannya:

- pisahkan input yang memang untuk data utama dan input yang seharusnya mengikuti keputusan resmi
- kalau NIKC tetap diinput manual, perlakukan sebagai pencatatan atau klaim akun, bukan generate
- matra tetap hardcode statik
- `province` tetap dipakai untuk alamat; jika ada kebutuhan provinsi lain yang berbeda makna, tambahkan field baru yang eksplisit

### 3. Bangun master kepangkatan dan master provinsi

File yang perlu ditambah atau direvisi:

- migration baru `master_kepangkatan`
- migration baru `master_provinsi`
- seeder untuk data awal master
- lookup dan validasi di controller yang memerlukan referensi itu

Arah perbaikannya:

- master kepangkatan menjadi sumber kebenaran untuk kode pangkat NIKC
- master provinsi menjadi sumber validasi untuk provinsi NIKC
- matra tidak dibuat master karena cukup hardcode
- seluruh perubahan implementasi turunan dari butir ini diturunkan ke rencana kerja setelah diskusi difinalkan

### 4. Tinjau ulang schema personel yang sudah ada

File yang perlu ditinjau:

- `database/migrations/2026_07_06_055601_create_personels_table.php`
- `database/migrations/2026_07_08_062112_add_pangkat_and_nikc_to_personels_table.php`

Arah perbaikannya:

- cek apakah `pangkat` dan `nikc` sudah cukup diwakili schema awal atau masih butuh revisi migration tambahan
- gunakan field `province` yang sudah ada untuk kebutuhan provinsi NIKC jika maknanya sama
- jika kebutuhan provinsi berbeda makna, baru tambahkan field baru yang jelas dan eksplisit

### 5. Selaraskan UI dengan sumber data resmi

File yang perlu direvisi:

- `app/Http/Controllers/Admin/MasterPersonelController.php`
- `app/Http/Controllers/Admin/VerificationController.php`
- `resources/js/Pages/Admin/Personel/Edit.vue`
- `resources/js/Pages/Admin/Personel/Index.vue`
- `resources/js/Pages/Admin/Verification/Index.vue`
- `resources/js/Pages/Personel/Dashboard.vue`

Arah perbaikannya:

- pangkat dibaca dari referensi yang disepakati, bukan dari pilihan bebas
- jika ada koreksi pangkat, sediakan alur pembaruan plus histori
- input `province` idealnya berupa combobox atau searchable dropdown sehingga bisa diketik lalu dipilih dari data yang ada
- kalau ada kebutuhan provinsi lain yang berbeda makna, tampilkan field berbeda secara eksplisit
- untuk NIKC, isi `province` tetap memakai daftar 01-34 yang sama; tidak perlu field ganda jika maknanya sama

### 6. Pindahkan referensi hukum ke rumah internal saat final

File target:

- salinan PDF Permenhan di `docs/10 Referensi Internal/`

Arah perbaikannya:

- saat diskusi final, salinan PDF Permenhan dipindahkan ke `docs/10 Referensi Internal/`
- setelah itu PDF boleh dijadikan referensi lintas diskusi dan dokumen turunan repo

## Latar Belakang

Berdasarkan Peraturan Menteri Pertahanan Republik Indonesia Nomor 3 Tahun 2021 tentang Pembentukan, Penetapan, dan Pembinaan Komponen Cadangan, setiap Warga Negara yang telah ditetapkan sebagai Komponen Cadangan wajib memiliki Nomor Induk Komponen Cadangan atau NIKC sebagai identitas resmi.

Ketentuan mengenai NIKC diatur dalam:

| Pasal | Ayat | Keterangan |
| :--- | :---: | :--- |
| Pasal 32 | (1) | Komponen Cadangan yang telah ditetapkan diberikan nomor induk Komponen Cadangan |
| Pasal 32 | (2) | Nomor induk Komponen Cadangan mencantumkan kode angka yang meliputi kepangkatan, kematraan, nomor registrasi, bulan kelahiran, tahun kelahiran, dan provinsi tempat pendidikan pelatihan dasar kemiliteran |
| Pasal 32 | (3) | Format nomor induk Komponen Cadangan tercantum dalam lampiran yang merupakan bagian tidak terpisahkan dari peraturan menteri |
| Pasal 31 | (1) | Penetapan dilakukan Menteri terhadap calon Komponen Cadangan yang telah dinyatakan lulus pelatihan dasar kemiliteran dan diangkat menjadi Komponen Cadangan |
| Pasal 31 | (5) | Penetapan, pelantikan, dan pengucapan sumpah atau janji dilaksanakan sesuai dengan ketentuan peraturan perundang-undangan |

## Dasar Hukum Pemberian NIKC

Berdasarkan Peraturan Menteri Pertahanan Nomor 3 Tahun 2021, NIKC diberikan setelah penetapan oleh Menteri dan pelantikan.

## Struktur Nomor Induk

Berdasarkan lampiran Peraturan Menteri Pertahanan Nomor 3 Tahun 2021, nomor induk terdiri atas 17 digit angka yang tersusun sebagai berikut:

| Posisi Digit | Panjang | Keterangan | Referensi Lampiran |
| :--- | :---: | :--- | :---: |
| Digit ke-1 | 1 digit | Kode Kepangkatan | Huruf B |
| Digit ke-2 | 1 digit | Kode Kematraan | Huruf C |
| Digit ke-3 s/d ke-9 | 7 digit | Nomor Registrasi | Huruf D |
| Digit ke-10 s/d ke-11 | 2 digit | Kode Bulan Kelahiran | Huruf E |
| Digit ke-12 s/d ke-15 | 4 digit | Kode Tahun Kelahiran | Huruf F |
| Digit ke-16 s/d ke-17 | 2 digit | Kode Provinsi Tempat Pelatihan Dasar Kemiliteran | Huruf G |

### Representasi Visual

Struktur 17 digit dibaca dari kiri ke kanan sebagai berikut:

1. digit ke-1: kode kepangkatan
2. digit ke-2: kode kematraan
3. digit ke-3 s/d ke-9: nomor registrasi
4. digit ke-10 s/d ke-11: bulan kelahiran
5. digit ke-12 s/d ke-15: tahun kelahiran
6. digit ke-16 s/d ke-17: kode provinsi tempat Latsarmil

## A. Kode Kepangkatan - Lampiran Huruf B

Berdasarkan lampiran huruf B:

| No. | Jenjang Kepangkatan | Kode |
| :--- | :--- | :---: |
| 1 | Perwira | 1 |
| 2 | Bintara | 2 |
| 3 | Tamtama | 3 |

### Dasar Pemberian Kepangkatan

Berdasarkan Pasal 34:

| Ayat | Ketentuan | Keterangan |
| :--- | :--- | :--- |
| (1) | Kepangkatan diberikan berdasarkan ijazah yang digunakan untuk mendaftar | Ijazah saat pendaftaran |
| (2a) | Diploma III, Diploma IV, S1, S1 Profesi | Pangkat Perwira (Letnan Dua) -> Kode 1 |
| (2b) | Sekolah Lanjutan Tingkat Atas sederajat | Pangkat Bintara (Sersan Dua) -> Kode 2 |
| (2c) | Sekolah Lanjutan Tingkat Pertama sederajat | Pangkat Tamtama (Prajurit Dua) -> Kode 3 |

### Pemetaan Jenjang ke Pangkat TNI

| Kode | Jenjang | Pangkat Awal | Pangkat di TNI |
| :---: | :--- | :--- | :--- |
| 1 | Perwira | Letnan Dua (Letda) | Letnan Dua s/d Kolonel |
| 2 | Bintara | Sersan Dua (Serda) | Sersan Dua s/d Pembantu Letnan Dua |
| 3 | Tamtama | Prajurit Dua (Prada) | Prajurit Dua s/d Kopral Kepala |

> Catatan penting:
> - detail hirarki pangkat tetap dirujuk ke diskusi pangkat terpisah
> - di dokumen ini, pangkat hanya dipakai sejauh dibutuhkan untuk membaca digit pertama NIKC
> - Kopda, Koptu, Kopka tetap Tamtama
> - Pelda, Peltu tetap Bintara

## B. Kode Kematraan - Lampiran Huruf C

Berdasarkan lampiran huruf C dan Pasal 4:

| No. | Kematraan | Kode | Dasar Pasal |
| :--- | :--- | :---: | :---: |
| 1 | Matra Darat | 1 | Pasal 4 huruf a |
| 2 | Matra Laut | 2 | Pasal 4 huruf b |
| 3 | Matra Udara | 3 | Pasal 4 huruf c |

> Catatan: kode ini sesuai dengan enum `matra` yang akan digunakan di sistem: `DARAT`, `LAUT`, `UDARA`.

## C. Nomor Registrasi - Lampiran Huruf D

Berdasarkan lampiran huruf D:

- Nomor registrasi terdiri atas 7 digit angka yang berurutan
- Minimum: `0000001`
- Maksimum: `9999999`

> Catatan: nomor registrasi diberikan oleh Menteri melalui SK Penetapan. Sistem tidak mengenerate nomor registrasi, hanya mencatat dan memvalidasi.

## D. Kode Bulan Kelahiran - Lampiran Huruf E

Berdasarkan lampiran huruf E:

| No. | Bulan | Kode | No. | Bulan | Kode |
| :--- | :--- | :---: | :--- | :--- | :---: |
| 1 | Januari | 01 | 7 | Juli | 07 |
| 2 | Februari | 02 | 8 | Agustus | 08 |
| 3 | Maret | 03 | 9 | September | 09 |
| 4 | April | 04 | 10 | Oktober | 10 |
| 5 | Mei | 05 | 11 | November | 11 |
| 6 | Juni | 06 | 12 | Desember | 12 |

## E. Kode Tahun Kelahiran - Lampiran Huruf F

Berdasarkan lampiran huruf F:

- Kode tahun kelahiran terdiri atas 4 digit angka tahun kelahiran
- Contoh: `1993` berarti tahun 1993

> Catatan: menggunakan tahun kelahiran Masehi atau Gregorian 4 digit.

## F. Kode Provinsi Tempat Latsarmil - Lampiran Huruf G

Berdasarkan lampiran huruf G, berikut daftar kode provinsi:

| No. | Provinsi | Kode | No. | Provinsi | Kode |
| :---: | :--- | :---: | :---: | :--- | :---: |
| 1 | Aceh | 01 | 18 | Nusa Tenggara Barat | 18 |
| 2 | Sumatera Utara | 02 | 19 | Nusa Tenggara Timur | 19 |
| 3 | Sumatera Barat | 03 | 20 | Kalimantan Barat | 20 |
| 4 | Riau | 04 | 21 | Kalimantan Selatan | 21 |
| 5 | Kepulauan Riau | 05 | 22 | Kalimantan Tengah | 22 |
| 6 | Jambi | 06 | 23 | Kalimantan Timur | 23 |
| 7 | Bengkulu | 07 | 24 | Kalimantan Utara | 24 |
| 8 | Sumatera Selatan | 08 | 25 | Gorontalo | 25 |
| 9 | Kepulauan Bangka Belitung | 09 | 26 | Sulawesi Barat | 26 |
| 10 | Lampung | 10 | 27 | Sulawesi Selatan | 27 |
| 11 | Banten | 11 | 28 | Sulawesi Tengah | 28 |
| 12 | Jawa Barat | 12 | 29 | Sulawesi Tenggara | 29 |
| 13 | DKI Jakarta | 13 | 30 | Sulawesi Utara | 30 |
| 14 | Jawa Tengah | 14 | 31 | Maluku | 31 |
| 15 | Yogyakarta | 15 | 32 | Maluku Utara | 32 |
| 16 | Jawa Timur | 16 | 33 | Papua | 33 |
| 17 | Bali | 17 | 34 | Papua Barat | 34 |

> Catatan penting:
> - Kode provinsi diisi berdasarkan lokasi tempat pelaksanaan Pelatihan Dasar Kemiliteran, bukan berdasarkan tempat lahir atau domisili
> - Provinsi Latsarmil berbeda dengan matra
> - Dalam satu provinsi bisa terdapat dua atau lebih pendidikan matra

## Contoh Nomor Induk

Contoh 1:

- NIKC: `11000000107199323`
- Kepangkatan: `1` = Perwira
- Kematraan: `1` = Matra Darat
- Registrasi: `0000001`
- Bulan lahir: `07` = Juli
- Tahun lahir: `1993`
- Provinsi Latsarmil: `23` = Kalimantan Timur

Hasil parsing:

| Komponen | Nilai | Arti | Referensi |
| :--- | :--- | :--- | :---: |
| Kepangkatan | 1 | Perwira | Lampiran Huruf B |
| Kematraan | 1 | Matra Darat | Lampiran Huruf C |
| Registrasi | 0000001 | Nomor urut 1 | Lampiran Huruf D |
| Bulan Lahir | 07 | Juli | Lampiran Huruf E |
| Tahun Lahir | 1993 | 1993 | Lampiran Huruf F |
| Provinsi Latsarmil | 23 | Kalimantan Timur | Lampiran Huruf G |

Contoh 2:

- NIKC: `12000011206199412`
- Kepangkatan: `1` = Perwira
- Kematraan: `2` = Matra Laut
- Registrasi: `0000112`
- Bulan lahir: `06` = Juni
- Tahun lahir: `1994`
- Provinsi Latsarmil: `12` = Jawa Barat

Hasil parsing:

| Komponen | Nilai | Arti | Referensi |
| :--- | :--- | :--- | :---: |
| Kepangkatan | 1 | Perwira | Lampiran Huruf B |
| Kematraan | 2 | Matra Laut | Lampiran Huruf C |
| Registrasi | 0000112 | Nomor urut 112 | Lampiran Huruf D |
| Bulan Lahir | 06 | Juni | Lampiran Huruf E |
| Tahun Lahir | 1994 | 1994 | Lampiran Huruf F |
| Provinsi Latsarmil | 12 | Jawa Barat | Lampiran Huruf G |

Contoh 3:

- NIKC: `21000150903199001`
- Kepangkatan: `2` = Bintara
- Kematraan: `1` = Matra Darat
- Registrasi: `0001509`
- Bulan lahir: `03` = Maret
- Tahun lahir: `1990`
- Provinsi Latsarmil: `01` = Aceh

Hasil parsing:

| Komponen | Nilai | Arti | Referensi |
| :--- | :--- | :--- | :---: |
| Kepangkatan | 2 | Bintara | Lampiran Huruf B |
| Kematraan | 1 | Matra Darat | Lampiran Huruf C |
| Registrasi | 0001509 | Nomor urut 1509 | Lampiran Huruf D |
| Bulan Lahir | 03 | Maret | Lampiran Huruf E |
| Tahun Lahir | 1990 | 1990 | Lampiran Huruf F |
| Provinsi Latsarmil | 01 | Aceh | Lampiran Huruf G |

Contoh 4:

- NIKC: `33000234511199727`
- Kepangkatan: `3` = Tamtama
- Kematraan: `3` = Matra Udara
- Registrasi: `0002345`
- Bulan lahir: `11` = November
- Tahun lahir: `1997`
- Provinsi Latsarmil: `27` = Sulawesi Selatan

Hasil parsing:

| Komponen | Nilai | Arti | Referensi |
| :--- | :--- | :--- | :---: |
| Kepangkatan | 3 | Tamtama | Lampiran Huruf B |
| Kematraan | 3 | Matra Udara | Lampiran Huruf C |
| Registrasi | 0002345 | Nomor urut 2345 | Lampiran Huruf D |
| Bulan Lahir | 11 | November | Lampiran Huruf E |
| Tahun Lahir | 1997 | 1997 | Lampiran Huruf F |
| Provinsi Latsarmil | 27 | Sulawesi Selatan | Lampiran Huruf G |

Contoh 5:

- NIKC: `21000000107199316`
- Kepangkatan: `2` = Bintara
- Kematraan: `1` = Matra Darat
- Registrasi: `0000001`
- Bulan lahir: `07` = Juli
- Tahun lahir: `1993`
- Provinsi Latsarmil: `16` = Jawa Timur

Hasil parsing:

| Komponen | Nilai | Arti | Referensi |
| :--- | :--- | :--- | :---: |
| Kepangkatan | 2 | Bintara | Lampiran Huruf B |
| Kematraan | 1 | Matra Darat | Lampiran Huruf C |
| Registrasi | 0000001 | Nomor urut 1 | Lampiran Huruf D |
| Bulan Lahir | 07 | Juli | Lampiran Huruf E |
| Tahun Lahir | 1993 | 1993 | Lampiran Huruf F |
| Provinsi Latsarmil | 16 | Jawa Timur | Lampiran Huruf G |

## Aturan Validasi NIKC

Berdasarkan lampiran Peraturan Menteri Pertahanan Nomor 3 Tahun 2021, sistem harus memvalidasi NIKC yang diinput dengan aturan berikut:

| No. | Aturan Validasi | Keterangan | Referensi |
| :---: | :--- | :--- | :---: |
| 1 | Panjang tepat 17 digit | Tidak boleh kurang atau lebih | Lampiran |
| 2 | Hanya angka 0-9 | Tidak boleh huruf, spasi, atau karakter khusus | Lampiran |
| 3 | Unik di seluruh sistem | Tidak boleh ada duplikasi | Pasal 32 |
| 4 | Digit ke-1: 1, 2, atau 3 | 1=Perwira, 2=Bintara, 3=Tamtama | Lampiran Huruf B |
| 5 | Digit ke-2: 1, 2, atau 3 | 1=Darat, 2=Laut, 3=Udara | Lampiran Huruf C |
| 6 | Digit ke-3 s/d ke-9: 0000001-9999999 | 7 digit angka | Lampiran Huruf D |
| 7 | Digit ke-10 s/d ke-11: 01-12 | Bulan valid | Lampiran Huruf E |
| 8 | Digit ke-12 s/d ke-15: tahun valid | 4 digit, rentang masuk akal, misalnya 1900-sekarang | Lampiran Huruf F |
| 9 | Digit ke-16 s/d ke-17: 01-34 | Provinsi valid sesuai daftar | Lampiran Huruf G |

> Catatan: sistem tidak mengenerate NIKC. NIKC sudah ditetapkan dalam SK Penetapan dari Menteri. Sistem hanya mencatat, memvalidasi, dan memverifikasi NIKC yang diinput.

## Skenario Input NIKC

NIKC dapat diinput ke dalam sistem melalui dua skenario:

### Skenario 1: Input oleh Super Admin sebagai Data Utama

| Langkah | Pelaku | Keterangan |
| :---: | :--- | :--- |
| 1 | Super Admin | Mengakses menu input data anggota |
| 2 | Super Admin | Menginput NIKC beserta data anggota lainnya sesuai SK Penetapan |
| 3 | Super Admin | Menyimpan data anggota |
| 4 | Sistem | Memvalidasi format NIKC dan keunikannya |
| 5 | Sistem | Menyimpan data anggota dengan status `TERVERIFIKASI` |

### Skenario 2: Input oleh Personel untuk Klaim Akun

| Langkah | Pelaku | Keterangan |
| :---: | :--- | :--- |
| 1 | Personel atau Calon Anggota | Mendaftar akun dan menginput NIKC |
| 2 | Sistem | Memvalidasi format NIKC dan keunikannya |
| 3 | Sistem | Mencocokkan dengan data yang sudah ada jika ada |
| 4a | Sistem | Jika data cocok, akun langsung aktif dan terhubung dengan data anggota |
| 4b | Sistem | Jika data tidak cocok atau belum ada, status menjadi `PENDING VERIFIKASI` dan menunggu verifikasi manual |
| 5 | Koordinator atau Super Admin | Memverifikasi data yang pending secara manual |

### Diagram Alur Input NIKC

1. input NIKC
2. validasi format dan keunikan
3. jika data cocok, akun langsung aktif dan terverifikasi
4. jika data belum cocok atau belum ada, status menjadi `PENDING VERIFIKASI`
5. verifikasi manual oleh Koordinator atau Super Admin
6. akun aktif dan terverifikasi setelah verifikasi selesai

## Sifat NIKC

NIKC bersifat permanen dan tidak berubah meskipun terjadi perubahan data anggota. NIKC tidak digenerate oleh sistem; sumber utamanya adalah keputusan Menteri yang sudah ada dan kemudian dicatat ke sistem.

| Skenario | Perubahan Data | Apakah NIKC Berubah? | Keterangan |
| :--- | :--- | :---: | :--- |
| Kenaikan pangkat | Kepangkatan berubah | Tidak | NIKC tetap, dicatat di riwayat kepangkatan |
| Penyesuaian ijazah | Ijazah berubah | Tidak | NIKC tetap, dicatat di riwayat pendidikan |
| Perubahan matra | Matra berubah | Tidak | NIKC tetap |
| Perubahan data pribadi | Nama, alamat, dan lain-lain | Tidak | NIKC tetap |
| Kesalahan input | Data salah | Tidak | NIKC tetap, data diperbaiki melalui mekanisme koreksi |

> Prinsip: NIKC adalah identifier permanen yang merekam data pada saat penetapan. Perubahan data lain dicatat dalam riwayat terpisah seperti tabel `riwayat_pangkat`, `riwayat_pendidikan`, dan lain-lain.

## Kebutuhan Data Master

Sistem memerlukan tabel master untuk mendukung validasi NIKC:

### Master Kepangkatan

| Kode | Nama | Jenjang | Keterangan |
| :---: | :--- | :--- | :--- |
| 1 | Perwira | Perwira | Letda s/d Kolonel |
| 2 | Bintara | Bintara | Serda s/d Pembantu Letnan Dua |
| 3 | Tamtama | Tamtama | Prada s/d Kopral Kepala |

### Master Provinsi

Berdasarkan lampiran huruf G:

| Kode | Nama Provinsi |
| :---: | :--- |
| 01 | Aceh |
| 02 | Sumatera Utara |
| ... | ... |
| 34 | Papua Barat |

> Catatan: tabel master `provinsi` diperlukan karena:
> 1. validasi NIKC membutuhkan daftar provinsi yang valid
> 2. provinsi Latsarmil dipakai melalui field `province` yang sudah ada, selama maknanya sama
> 3. dalam satu provinsi bisa terdapat dua atau lebih pendidikan matra
> 4. fleksibel jika ada perubahan atau penambahan provinsi di masa depan
> 5. data master idealnya dapat diekstrak dari keputusan resmi agar sumber kebenarannya jelas

## Skenario Validasi NIKC dengan Master Data

Input contoh: `11000000107199323`

Validasi:

- panjang 17 digit
- hanya angka
- unik di database
- digit 1 valid untuk kepangkatan
- digit 2 valid untuk matra
- digit 3-9 valid untuk registrasi
- digit 10-11 valid untuk bulan
- digit 12-15 valid untuk tahun
- digit 16-17 valid untuk provinsi

Hasil: NIKC valid

## Opsi yang Dipertimbangkan

- **Opsi A: NIKC hanya dicatat sebagai identitas resmi**
  - Kelebihan:
    - paling sederhana
    - minim risiko pengulangan domain
  - Kekurangan:
    - tidak membantu auto-fill data turunan
    - verifikasi anggota tetap banyak manual

- **Opsi B: NIKC dicatat dan dipakai untuk auto-fill data turunan yang sudah ditetapkan**
  - Kelebihan:
    - matra bisa dibekukan otomatis dari NIKC
    - pangkat bisa terisi otomatis dari NIKC
    - pencatatan master data lebih konsisten
    - cocok dengan sumber keputusan resmi sebagai kebenaran utama
  - Kekurangan:
    - perlu master data kepangkatan dan provinsi yang rapi
    - koreksi pangkat butuh alur tambahan

- **Opsi C: NIKC dipakai untuk auto-fill sekaligus klaim akun jika belum ada data sistem**
  - Kelebihan:
    - mendukung klaim akun berbasis data resmi
    - cocok untuk data lama atau data yang baru masuk
  - Kekurangan:
    - butuh verifikasi lebih ketat
    - butuh lampiran keputusan bila file belum tersedia di sistem

- **Opsi D: NIKC langsung menjadi kunci klaim jika sudah ada di sistem, dan menjadi input pendaftaran jika belum ada**
  - Kelebihan:
    - membedakan jelas antara klaim dan input baru
    - alur operasional lebih tegas
  - Kekurangan:
    - perlu aturan status yang lebih detail
    - perlu penanganan unggah PDF keputusan

## Potensi Fitur Sistem Terkait NIKC

| No. | Fitur | Keterangan | Manfaat |
| :---: | :--- | :--- | :--- |
| 1 | Validasi format NIKC | Memastikan NIKC sesuai format 17 digit | Mencegah input salah |
| 2 | Cek keunikan NIKC | Memastikan tidak ada duplikat | Menjaga integritas data |
| 3 | Parsing NIKC | Membaca dan menampilkan informasi dari NIKC | Memudahkan verifikasi data |
| 4 | Verifikasi silang | Mencocokkan NIKC dengan data anggota | Mendukung auto-verification |
| 5 | Pencarian berdasarkan NIKC | Filter dan pencarian anggota | Memudahkan administrasi |
| 6 | Status verifikasi | Menampilkan status verifikasi akun | Mendukung klaim akun |

## Kesimpulan Sementara

| No. | Poin | Status | Keterangan |
| :---: | :--- | :---: | :--- |
| 1 | NIKC tidak digenerate sistem | Sudah ditegaskan | Sistem hanya mencatat NIKC dari keputusan resmi |
| 2 | NIKC bersifat permanen | Sudah ditegaskan | NIKC tidak berubah setelah penetapan |
| 3 | Input NIKC manual punya dua keperluan | Sudah ditegaskan | Bisa untuk pencatatan data utama atau klaim akun |
| 4 | Matra diperlakukan hardcode | Disetujui | Hanya ada Darat, Laut, Udara; tidak perlu master data |
| 5 | Pangkat dari NIKC bisa dikoreksi melalui alur resmi | Disetujui | Jika diubah perlu alasan, nomor keputusan, histori, dan lampiran PDF jika belum ada di sistem |
| 6 | Tabel master kepangkatan diperlukan | Disetujui | Untuk validasi dan auto-fill pangkat dari NIKC |
| 7 | Tabel master provinsi diperlukan | Disetujui | Untuk validasi kode provinsi 01-34 |
| 8 | Data master diekstrak dari keputusan resmi | Disetujui | Keputusan menjadi source of truth utama untuk klaim dan turunan data |

## Catatan Fokus Diskusi

- detail hirarki pangkat tetap dirujuk ke diskusi pangkat terpisah
- detail angka kepangkatan di sini hanya dibutuhkan untuk membaca digit pertama NIKC
- relasi NIKC dengan klaim akun tetap dibahas, tetapi tidak mengambil alih domain autentikasi
- provinsi Latsarmil tetap dibahas sebagai bagian validasi NIKC, bukan sebagai domain lokasi umum
- alur perubahan pangkat, alasan perubahan, nomor keputusan, dan unggah PDF keputusan lebih tepat difinalkan di diskusi master data anggota atau diskusi klaim akun dan verifikasi anggota
- matra tidak perlu master data; cukup hardcode Darat, Laut, dan Udara
- revisi implementasi repo turunannya tidak dikerjakan di diskusi ini, melainkan dituangkan ke rencana kerja setelah diskusi final
- alur `NIKC belum ada di sistem`, `pending verifikasi`, dan field tambahan untuk nomor keputusan/PDF keputusan dinyatakan out-of-scope di dokumen ini agar keputusan NIKC tetap fokus pada identitas, format, validasi, dan data master yang sudah disepakati

## Checklist Pra-Finalisasi

| # | Yang Wajib Dicatat | Sudah Ada? | Catatan |
| --- | --- | --- | --- |
| 1 | Sumber kebenaran NIKC | Ya | Keputusan Menteri, bukan generate sistem |
| 2 | Sifat permanen NIKC | Ya | NIKC tidak berubah setelah penetapan |
| 3 | Skenario input NIKC | Ya | Input admin untuk data utama atau input user untuk klaim |
| 4 | Validasi format 17 digit | Ya | Panjang, angka, kode pangkat, matra, registrasi, bulan, tahun, provinsi |
| 5 | Master data pendukung | Ya | Kepangkatan dan provinsi diperlukan; matra hardcode |
| 6 | Aturan auto-fill dari NIKC | Ya | Matra hardcode, pangkat dibekukan lalu bisa dikoreksi melalui alur resmi |
| 7 | Alur koreksi pangkat | Ya | Perlu alasan, nomor keputusan, histori, dan PDF bila belum ada di sistem |
| 8 | Relasi ke klaim akun | Ya | Jika NIKC sudah ada di sistem, masuk klaim; jika belum ada, keluar dari scope dokumen ini |
| 9 | Batas scope dokumen | Ya | Alur NIKC yang belum ada di sistem dinyatakan out-of-scope dan dibahas di modul lain |
| 10 | Referensi internal PDF Permenhan saat final | Ya | Saat diskusi selesai, PDF dipindahkan ke `docs/10 Referensi Internal/` |

## Opsi Pra-Finalisasi yang Tersisa

- tidak ada opsi substantif yang tersisa di dokumen ini
- detail implementasi turunan tetap ditahan untuk keputusan atau rencana kerja setelah finalisasi diskusi ini
- revisi teknis repo berikutnya tidak dimasukkan lagi ke diskusi ini, tetapi akan dipecah ke rencana kerja saat waktunya tiba

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | NIKC diatur oleh Permenhan | Sudah jelas dari bahan user | Clear |
| 2 | Struktur 17 digit | Sudah dirinci lengkap | Clear |
| 3 | NIKC tidak digenerate sistem | Disetujui | Sistem hanya mencatat NIKC dari keputusan resmi |
| 4 | NIKC permanen | Disetujui | NIKC tidak berubah setelah penetapan |
| 5 | NIKC divalidasi format dan keunikannya | Disetujui | Panjang, angka, kode pangkat, matra, registrasi, bulan, tahun, provinsi |
| 6 | NIKC bisa diinput manual untuk dua kebutuhan | Disetujui | Pencatatan data utama atau klaim akun |
| 7 | Master kepangkatan dan master provinsi diperlukan | Disetujui | Keduanya diekstrak dari keputusan resmi dan dipakai untuk validasi |
| 8 | Koreksi pangkat memakai pembaruan + histori + alasan + nomor keputusan | Disetujui | Jika file keputusan belum ada di sistem, unggah PDF wajib |
| 9 | NIKC yang belum ada di sistem keluar dari scope dokumen ini | Disetujui | Dinyatakan out-of-scope dan dibahas di modul lain |

## Catatan untuk AI Agent

- jangan ubah diskusi ini menjadi keputusan, rencana kerja, atau referensi teknis sebelum topik final
- jangan campurkan domain autentikasi, pangkat, atau master anggota ke dalam keputusan lain bila fokus utamanya NIKC
- jika nanti topik ini difinalkan, cek kembali ketergantungan ke master data, verifikasi akun, dan tampilan personel
- bila ada perbedaan antara perilaku kode aktual dan bahan normatif Permenhan, catat sebagai drift dan jangan dianggap final sebelum user memutuskan
- drift implementasi yang sudah dicatat di dokumen ini tidak perlu diulang lagi pada audit berikutnya kecuali ada perubahan baru di repo

## Pertanyaan Terbuka

- tidak ada pertanyaan terbuka substantif yang tersisa di dokumen ini
- jika nanti ada kebutuhan turunan, itu masuk ke keputusan atau rencana kerja setelah diskusi difinalkan
- jika user menghendaki perubahan implementasi, itu ditangani pada rencana kerja, bukan menambah beban diskusi ini
- tidak ada lagi pertanyaan terbuka terkait alur `NIKC belum ada di sistem` karena sudah dipindahkan ke scope modul lain

## Rencana Tindak Lanjut

- [ ] menunggu konfirmasi user untuk melahirkan keputusan final dari diskusi ini
- [ ] setelah final, pindahkan PDF Permenhan ke docs/10 Referensi Internal/ bila belum ada di sana
- [ ] jika keputusan final melahirkan pekerjaan implementasi, turunkan ke rencana kerja formal
- [ ] jadikan penanganan drift di atas sebagai bahan utama saat rencana kerja dibuat, agar tidak ada langkah implementasi yang bertentangan dengan hasil diskusi
- [ ] jika saat finalisasi ada scope yang bergeser ke diskusi lain, catat hasilnya langsung di file diskusi terkait yang sudah dirujuk di atas

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-09 | Dokumen dibuat untuk menampung analisis NIKC sebagai identitas khusus Komponen Cadangan, termasuk regulasi, struktur 17 digit, validasi, skenario input, dan irisan ke master data serta klaim akun |
| 2026-07-09 | Diselaraskan ulang agar lebih siap difinalisasi, termasuk penegasan scope implementasi ke rencana kerja, penggunaan ulang field `province` bila maknanya sama, serta pembersihan blok diagram yang semula mengalami mojibake |
| 2026-07-09 | Drift implementasi dicatat beserta arah penanganannya agar tidak menjadi kendala saat finalisasi atau penurunan ke rencana kerja |
| 2026-07-09 | Ditambahkan pemetaan diskusi terkait agar scope turunan bisa langsung dicatat ke file yang tepat saat finalisasi |





