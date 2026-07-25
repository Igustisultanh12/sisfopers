# Diskusi
## tentang MASTER DATA DAN STATUS ANGGOTA

Status: `Ditetapkan`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang Master Data dan Status Anggota
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: manajemen anggota dasar, profil/biodata anggota, identitas akses, alamat domisili, kontak multi-nomor, pangkat, SINYALEMEN, import awal, dan batas kelola admin
Keputusan terkait: `2026.Kep.004 tentang Standar Master Data dan Status Personel`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `2026.Kep.004 tentang Standar Master Data dan Status Personel`

## Pemicu

Repo `kctrimatra` sedang berada pada tahap bootstrap dan belum memiliki skeleton Laravel 13 maupun modul aplikasi aktif. User sudah menegaskan arah produk awal yang penting adalah website dengan fitur manajemen anggota dasar yang bisa dikelola oleh pihak berwenang.

Kebutuhan tersebut sudah difinalkan melalui keputusan aktif `2026.Kep.004 tentang Standar Master Data dan Status Personel`. Dokumen ini dipertahankan sebagai arsip jejak pembahasan yang melahirkan keputusan tersebut.

## Temuan Awal

Audit awal terhadap dokumen aktif yang beririsan menghasilkan temuan berikut.

### Dokumen yang diaudit

- `AGENTS.md`
- `docs/README.md`
- `docs/01 Profil Sistem/PROJECT_STATUS.md`
- `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
- `docs/04 Diskusi/README.md`

### Kondisi repo yang terverifikasi

- repo sudah memiliki kode aktif dan modul anggota dasar yang berjalan
- struktur dokumentasi lifecycle sudah aktif
- ada migrasi, model, controller, view, dan route yang menyentuh `users`, `personels`, dan `sinyalmen`
- belum ada keputusan final domain bisnis anggota
- belum ada workplan implementasi aktif yang lahir dari keputusan final

### Temuan dari dokumen aktif yang beririsan

- `docs/README.md` dan `docs/04 Diskusi/README.md` mewajibkan topik baru lahir di `04 Diskusi` sebelum menjadi keputusan
- `docs/01 Profil Sistem/PROJECT_STATUS.md` menandai keputusan domain dan workplan implementasi sebagai `rencana`
- `2026.02.001` melarang agent melahirkan keputusan, rencana kerja, referensi teknis, atau dokumen implementatif baru selama substansi masih berada pada level diskusi
- governance repo belum memiliki domain aktif tentang data anggota, role pengelola, atau status akun

### Kondisi kode aktual

- modul master personel sudah ada dalam bentuk controller admin, model personel, repository, dan halaman Vue
- pendaftaran anggota, aktivasi admin, login, profil akun, dan verifikasi anggota sudah memiliki alur implementasi dasar
- data personel aktif sudah memuat `user_id`, `nik`, `nikc`, `full_name`, `pob`, `dob`, `gender`, `matra`, `angkatan`, `phone_number`, `address`, `province`, `city`, `district`, `village`, `postal_code`, `photo_profile`, `ktp_document`, `status_profile`, `face_verified`, dan `pangkat`
- status profil dan verifikasi wajah sudah dipakai sebagai gate alur akses personel
- ada fitur import, export, penghapusan data personel, dan notifikasi WhatsApp dalam implementasi aktual
- beberapa field masih tumpang tindih dengan domain kontak, akun, login, dan status akses, sehingga keputusan batas domain perlu dipertegas

### Temuan substantif dari arahan user

- arah minimum produk adalah website
- website perlu memiliki fitur manajemen anggota dasar
- anggota perlu dapat dikelola oleh pihak yang berwenang
- pembahasan saat ini masih berada pada level domain, belum pada level implementasi teknis

### Klarifikasi terbaru dari user

- alur utama data anggota adalah diekstrak dari surat keputusan
- jika ekstrak PDF belum memungkinkan, admin boleh input minimal dari SK secara manual
- input minimal manual juga mencakup data pendidikan dasar: jenjang, program studi, dan bukti ijazah komcad atau surat keputusan menteri pertahanan jika tersedia
- input minimal manual idealnya tetap memuat `email aktif`, nomor `WhatsApp` atau `nomor_hp`, tempat lahir, tanggal lahir, pangkat, `NIKC`, jenjang pendidikan, dan program studi; bila ada, field opsional seperti `TMT Penetapan`, nomor keputusan menteri, tentang apa keputusan itu, dan tanggal keputusan juga dicatat
- import massal berarti ekstraksi data dari PDF, jadi perlu modul import PDF yang sesuai
- `email` dan `username` melekat pada `personels`
- anggota tidak langsung aktif saat ditambahkan manual
- anggota yang ditambahkan manual harus mendapat email bahwa perlu verifikasi unggah ijazah komcad dan atau surat keputusan menteri pertahanan
- penghapusan anggota tidak diperbolehkan bila ada keputusan menteri yang sah
- jika anggota/personel tidak aktif, dipecat, pensiun, atau meninggal, statusnya hanya bisa dinonaktifkan dengan keterangan yang sesuai
- jika anggota ternyata tidak terdaftar, tidak resmi, atau tidak ada keputusan menteri, data diarsipkan sebagai bahan bahwa ada usaha akses yang tidak resmi
- `abituren` adalah kewajiban inti
- agama memakai daftar resmi di Indonesia plus opsi kepercayaan
- nomor HP mendukung multi nomor dan perlu tabel tersendiri
- alamat wajib terstruktur bertingkat: provinsi, kabupaten/kota, kecamatan, kelurahan
- awalnya proses boleh memakai API, tetapi setelah data didapat langsung disimpan ke database dan scraping dilakukan bertahap
- koordinator boleh mengubah data anggota dalam batas tertentu, tetapi harus diverifikasi oleh super admin
- jika super admin mengubah data, perubahan wajib tercatat dalam log
- `users` dan `personels` diperlakukan sebagai satu rumpun data yang sama dan akan diarahkan untuk dilebur ke `personels`
- seluruh field yang overlap harus dirapikan agar hanya ada satu sumber kebenaran per data
- `NIK` adalah nomor identitas dalam KTP, sedangkan `NIKC` adalah nomor identitas Komcad
- login boleh menggunakan `NIKC` dan atau `username`
- `MFA` masih `hold`
- role tetap mengikuti diskusi akun dan akses yang sudah ada
- jenis kelamin di profil anggota akan memakai label `Pria` dan `Wanita`
- `Wanita` dipakai sebagai varian display untuk pangkat perempuan, bukan kategori pangkat baru
- `agama` perlu dropdown agama resmi dan opsi kepercayaan
- `nomor_hp` boleh lebih dari satu dan perlu status aktif atau nonaktif
- `tanda tangan` masih ditahan
- `SINYALEMEN` adalah istilah yang benar untuk domain ciri fisik, dan data yang sama harus bisa ditarik dari profil anggota ketika dibutuhkan
- field yang sama tetapi beda nama harus disatukan agar tidak bentrok
- `provinsi` domisili dan `provinsi` latsarmil boleh memakai sumber data yang sama jika maknanya sama
- `soft skill` dan `perusahaan` sudah dipisahkan ke diskusi baru agar scope biodata inti tetap bersih
- `riwayat perusahaan` sudah keluar dari scope dokumen ini dan harus dibahas hanya di dokumen riwayat pekerjaan/perusahaan yang terpisah
- istilah `SINYALEMEN` dipakai secara bertahap sebagai istilah baku dokumen, walaupun kode aktif masih bisa menyimpan nama lama selama proses transisi
- `tanda tangan` tetap `hold`
- `MFA` tetap `hold`
- `avatar` atau foto profil bisa sekalian masuk scope profil inti
- scope lain yang harus lahir di diskusi baru atau revisi dokumen terpisah: `soft skill`, `perusahaan` dan riwayat pekerjaan, serta detail autentikasi lanjutan seperti reset password, login final, dan aturan MFA final
- rumpun `undang-undang`, `peraturan`, `keputusan`, dan `regulasi` dipindahkan ke [diskusi terpisah tentang referensi internal regulasi](2026.07.10%20Diskusi%20tentang%20Referensi%20Internal%20Regulasi.md) agar dokumen ini tetap fokus pada master data, status, dan peta field `personels`

### Poin yang sudah terkunci dari arahan user

- `users` dan `personels` adalah satu rumpun yang harus dilebur ke `personels`
- `NIK` adalah nomor KTP, sedangkan `NIKC` adalah nomor Komcad
- login boleh memakai `NIKC` dan atau `username`
- `email` dan `username` melekat pada `personels`
- `abituren` adalah kewajiban inti
- `nomor_hp` boleh lebih dari satu dan harus punya status aktif atau nonaktif, sehingga lebih cocok di tabel tersendiri
- jenis kelamin profil memakai `Pria` dan `Wanita`
- `agama` perlu dropdown resmi plus opsi kepercayaan
- alamat harus terstruktur bertingkat dan wajib dipilih per level wilayah
- anggota manual tidak langsung aktif
- anggota yang ditambahkan manual harus dikirim email bahwa perlu verifikasi unggah ijazah komcad dan atau surat keputusan menteri pertahanan
- `WhatsApp` atau nomor HP aktif diperlakukan sebagai kanal kontak operasional, tetapi nomor utamanya tetap mengikuti peta multi-nomor di `personels`
- anggota yang tidak resmi atau tanpa keputusan menteri diarsipkan sebagai bukti ada usaha akses yang tidak sah
- anggota yang tidak aktif, dipecat, pensiun, atau meninggal hanya dinonaktifkan dengan keterangan
- penghapusan anggota tidak diperbolehkan jika ada keputusan menteri yang sah
- koordinator boleh ubah data terbatas, tetapi harus diverifikasi super admin
- jika super admin mengubah data, perubahan wajib tercatat dalam log
- import massal adalah ekstraksi data dari PDF sehingga perlu modul import PDF
- `avatar` atau foto profil bisa masuk scope profil inti
- `tanda tangan` masih di-hold
- `MFA` masih di-hold
- `SINYALEMEN` adalah domain turunan yang membaca data inti yang sama
- `soft skill` dan `perusahaan` tidak berada di scope dokumen ini
- `riwayat perusahaan` hanya dibahas di dokumen terpisah
- provinsi domisili dan provinsi latsarmil boleh memakai sumber data yang sama jika maknanya sama

## Opsi yang Dipertimbangkan

- **Opsi A: Diskusi anggota dibatasi pada data inti dan CRUD dasar**
  - Kelebihan:
    - paling fokus untuk fondasi awal modul anggota
    - memudahkan finalisasi keputusan domain inti tanpa bercampur dengan alur login
    - cocok untuk repo yang masih bootstrap
  - Kekurangan:
    - perlu diskusi lanjutan terpisah untuk autentikasi dan verifikasi

- **Opsi B: Diskusi anggota langsung mencakup data inti, CRUD, role, login, logout, dan verifikasi**
  - Kelebihan:
    - terasa lengkap dalam satu dokumen
    - mengurangi jumlah file diskusi
  - Kekurangan:
    - scope cepat melebar
    - berisiko mencampur domain data anggota dengan autentikasi
    - lebih sulit difinalkan bertahap

- **Opsi C: Diskusi anggota hanya mencatat struktur data, sedangkan CRUD dan role ditunda**
  - Kelebihan:
    - sangat ringan untuk tahap awal
    - fokus pada model domain
  - Kekurangan:
    - tidak cukup menjawab arahan user tentang fitur yang harus bisa dikelola
    - berisiko melahirkan keputusan yang terlalu abstrak

## Poin Diskusi

### 1. Scope minimum modul anggota perlu dibatasi secara sadar

Arahan user menunjukkan kebutuhan inti pada fase awal bukan sistem keanggotaan yang penuh, melainkan kemampuan mengelola anggota secara dasar melalui website. Karena itu diskusi ini perlu fokus pada kemampuan minimum yang benar-benar wajib ada terlebih dahulu.

Kemampuan minimum yang tampak paling relevan untuk dibahas di dokumen ini:

- membuat data anggota
- melihat daftar dan detail anggota
- mengubah data anggota
- menonaktifkan atau menghapus data anggota sesuai aturan yang nanti dipilih
- mengelompokkan anggota pada struktur organisasi yang sah bila struktur itu sudah diputuskan

### 2. Data inti anggota harus diputuskan sebelum CRUD dibakukan

Fitur CRUD anggota tidak bisa difinalkan secara sehat bila data inti anggota belum jelas. Diskusi ini perlu menyepakati terlebih dahulu data apa yang wajib ada sejak input awal dan data apa yang boleh menyusul kemudian.

Pertanyaan pentingnya:

- apakah `nomor_induk` wajib unik secara global
- apakah `email` wajib sejak admin input awal atau boleh diisi saat proses klaim akun
- apakah nomor HP utama termasuk data inti anggota atau dipisah sebagai domain kontak
- apakah anggota wajib langsung terkait ke grup atau boleh masuk sebagai data draft lebih dulu

#### 2.b. Penyatuan profil akun dan profil anggota ke `personels`

User telah mengarahkan bahwa data yang selama ini tersebar di `users` dan `personels` harus dipandang sebagai satu kesatuan profil anggota dan disiapkan untuk dilebur ke `personels`.

Ruang diskusi yang perlu dirapikan:

- apakah `username`, `email`, `password`, `role`, dan `MFA` ikut menjadi atribut profil `personels`
- apakah `NIKC` menjadi salah satu jalur login utama bersama `username`
- apakah `personels` menjadi sumber kebenaran tunggal untuk identitas anggota
- apakah field yang semula berada di `users` harus dimigrasikan secara normatif ke `personels`
- apakah role tetap diacu dari diskusi akses, tetapi secara data melekat pada profil `personels`

Catatan sinkronisasi:

- semua field yang sama tetapi beda nama harus diringkas menjadi satu nomenklatur aktif
- `Pria` dan `Wanita` adalah label yang dipakai untuk jenis kelamin profil
- `Wanita` pada pangkat hanya varian display, bukan master pangkat baru
- `agama` perlu dropdown agama resmi plus opsi kepercayaan
- `tanda tangan` masih di-hold
- `SINYALEMEN` harus dianggap bagian turunan yang menarik data dasar dari `personels` saat dibutuhkan

#### 2.c. Revisi alamat domisili

User juga mengarahkan bahwa alamat perlu dirapikan agar siap dipakai untuk filter domisili dan kebutuhan operasional lain.

Poin yang perlu dicatat:

- provinsi domisili dan provinsi latsarmil boleh memakai data provinsi yang sama jika maknanya memang sama
- kalau maknanya sama, jangan buat kolom provinsi ganda
- alamat tidak cukup disimpan sebagai teks bebas saja jika nanti dipakai untuk filter kegiatan
- alamat sebaiknya disiapkan terstruktur sampai level wilayah yang relevan
- `alamat_lengkap` tetap boleh ada untuk detail jalan, RT/RW, dan catatan bebas
- jika ada field yang sudah ada tetapi maknanya sama, cukup disatukan ke satu field aktif

#### 2.d. Peta sumber kebenaran kandidat `personels`

Tabel di bawah ini menjadi peta kerja untuk menyatukan field yang saat ini tersebar di `users`, `personels`, dan domain turunan lain.

| Kelompok | Field target tunggal | Nama / bentuk yang sedang muncul | Sumber kebenaran target | Kondisi kode aktif saat ini | Catatan sinkronisasi |
| --- | --- | --- | --- | --- | --- |
| Identitas inti | `full_name` | `nama_lengkap`, `full_name` | `personels` | Ada di `personels` | Jangan pecah nama depan/belakang |
| Identitas inti | `nik` | `NIK`, `Nomor Induk Kependudukan` | `personels` | Ada di `personels` | Ini nomor KTP, bukan NIKC |
| Identitas Komcad | `nikc` | `NIKC`, `Nomor Induk Komponen Cadangan` | `personels` | Ada di `personels` | Dipakai juga sebagai jalur login yang mungkin |
| Biodata dasar | `pob` | `tempat lahir` | `personels` | Ada di `personels` | Satu field saja |
| Biodata dasar | `dob` | `tanggal lahir` | `personels` | Ada di `personels` | Satu field saja |
| Biodata dasar | `gender` | `Pria`, `Wanita` | `personels` | Ada di `personels` | `Wanita` dipakai sebagai label display |
| Biodata dasar | `photo_profile` | `foto`, `pasfoto` | `personels` | Ada di `personels` | Jangan duplikasi di `users` |
| Biodata dasar | `address` / `alamat_lengkap` | `alamat`, `alamat rumah` | `personels` | Ada di `personels` | Detail wilayah disiapkan terstruktur |
| Biodata dasar | `province` | `provinsi domisili`, `provinsi latsarmil` | `personels` | Ada di `personels` | Sumber data provinsi boleh sama bila makna sama |
| Biodata dasar | `city` / `kabupaten_kota` | kabupaten/kota | `personels` | Ada di `personels` | Perlu nama baku yang stabil |
| Biodata dasar | `district` / `kecamatan` | kecamatan | `personels` | Ada di `personels` | Perlu nama baku yang stabil |
| Biodata dasar | `village` / `desa_kelurahan` | desa / kelurahan | `personels` | Ada di `personels` | Wajib, sebagai level terakhir alamat terstruktur |
| Biodata dasar | `postal_code` | kode pos | `personels` | Ada di `personels` | Bisa ditarik dari data wilayah bila tersedia |
| Biodata dasar | `religion` | agama, kepercayaan | `personels` | Belum ada di kode | Perlu dropdown agama resmi plus opsi kepercayaan |
| Biodata dasar | `signature_path` | tanda tangan | `personels` | Belum ada di kode | Saat ini di-hold |
| Organisasi | `matra` | Darat / Laut / Udara | `personels` | Ada di `personels` | Saat ini kode masih memakai AD/AL/AU |
| Organisasi | `angkatan` | 2021-2026 | `personels` | Ada di `personels` | Perlu finalisasi daftar nilai valid |
| Organisasi | `abituren` | Reguler / ASN/PPPK / SPPI | `personels` | Belum ada di kode | Kewajiban inti profil anggota |
| Kontak | `phone_number` | nomor HP | `anggota_nomor_hp` | Ada di `personels` | Multi-nomor wajib tabel tersendiri dengan status aktif |
| Kontak | `email` | email aktif | `personels` | Ada di `users` | Melekat pada `personels` dan dipakai untuk verifikasi |
| Akses akun | `username` | username | `personels` | Ada di `users` | Melekat pada `personels` |
| Akses akun | `password` | password | `personels` | Ada di `users` | Tidak boleh jadi sumber data ganda |
| Akses akun | `role` | role pengguna | `personels` | Ada di `users` | Harus konsisten dengan diskusi akses |
| Akses akun | `mfa` | MFA / Google Authenticator | `personels` | Ada di `users` | Status saat ini `hold` |
| Pangkat | `pangkat` | pangkat | `personels` | Ada di `personels` | Varian perempuan hanya display |
| Pangkat display | `display_pangkat` | `(W)` | hasil turunan dari `personels` | Ada di model | Jangan dianggap pangkat baru |
| Dokumen | `ktp_document` | berkas identitas | `personels` | Ada di `personels` | Tetap di profil inti |
| Pendidikan | `education_level` | jenjang pendidikan | `personels` | Belum ada di kode | Bagian inti profil pendidikan |
| Pendidikan | `study_program` | program studi / jurusan | `personels` | Belum ada di kode | Bagian inti profil pendidikan |
| Pendidikan | `file_ijazah_path` | ijazah / bukti pendidikan | `personels` | Belum ada di kode | Bukti wajib bila verifikasi pendidikan dibutuhkan |
| Pendidikan | `education_verified_at` | waktu verifikasi pendidikan | `personels` | Belum ada di kode | Diisi saat pendidikan diverifikasi |
| Pendidikan | `education_verified_by` | verifikator pendidikan | `personels` | Belum ada di kode | Menyimpan siapa yang memverifikasi |
| Sinyal fisik | `SINYALEMEN` | sinyalemen / sinyalmen | domain turunan dari `personels` | Ada tabel terpisah | Jangan duplikasi field inti di sini |
| Soft skill | `soft_skill` | soft skill, skill bahasa | domain terpisah | Belum ada di kode | Sudah dipindah ke diskusi baru |
| Perusahaan | `riwayat_perusahaan` | perusahaan, riwayat kerja | domain terpisah | Belum ada di kode | Sudah dipindah ke diskusi baru |
| Status | `status_profile` | kelengkapan profil | `personels` | Ada di `personels` | Status turunan internal |
| Status | `face_verified` | verifikasi wajah | `personels` | Ada di `personels` | Status turunan internal |
| Status | `status_keaktifan` | aktif / nonaktif / tidak resmi / diarsipkan / dipecat / pensiun / meninggal | `personels` | Ada di `users` dan `personels` | Dipakai untuk lifecycle anggota, bukan penghapusan |

#### 2.d.1. Field yang diekstrak dari surat keputusan

Berdasarkan PDF sumber resmi yang dibaca, dokumen sumber memuat field inti berikut untuk diekstrak:

| Field sumber SK | Keterangan |
| --- | --- |
| `nomor_keputusan` | nomor keputusan menteri pertahanan |
| `tanggal_keputusan` | tanggal keputusan |
| `judul_keputusan` | tentang apa keputusan itu |
| `kelompok_nominatif` | misalnya Perwira, Bintara, atau kelompok lain pada lampiran |
| `nama_lengkap` | nama anggota, termasuk gelar/suffix bila tercetak di lampiran |
| `tempat_lahir` | tempat lahir |
| `tanggal_lahir` | tanggal lahir |
| `pangkat` | pangkat penetapan pada lampiran |
| `jabatan` | jabatan atau rumpun status seperti Perwira Komcad / Bintara Komcad |
| `nikc` | nomor induk komcad |
| `jenjang_pendidikan` | misalnya SMP/Paket B, SMA/K/Paket C, D3, D4/S1, Profesi, S2, S3 |
| `program_studi` | contoh: Manajemen, Teknik Sipil, IPA, IPS, Hukum, dan seterusnya |
| `tmt_penetapan` | tanggal TMT penetapan di lampiran |
| `keterangan` | kolom catatan tambahan bila ada |

Catatan hasil baca PDF:

- PDF ini tidak memuat `email`, `nomor_hp`, `alamat`, `agama`, `avatar`, atau `MFA`
- field tersebut tetap berasal dari input manual atau sumber profil lain, bukan dari SK
- struktur pendidikan di lampiran cukup jelas untuk dijadikan dasar ekstraksi awal

#### 2.d.2. Blok pendidikan personel

Pendidikan menjadi satu blok di profil `personels`, bukan domain yang terpisah dari profil inti.

Aturan peta pendidikan:

- sumber awal pendidikan boleh berasal dari SK bila tersedia
- bila ekstraksi PDF belum memungkinkan, admin mengisi minimal secara manual
- jenjang pendidikan disimpan sebagai nilai baku, misalnya `SMP/Paket B`, `SMA/K/Paket C [IPA/IPS]`, `D3`, `D4/S1`, `Profesi`, `S2`, `S3`
- program studi disimpan sebagai teks terstruktur yang konsisten dengan ijazah
- ijazah menjadi bukti wajib saat verifikasi pendidikan dibutuhkan
- data pendidikan yang sudah diverifikasi harus bisa ditelusuri siapa dan kapan verifikasinya

Field pendidikan yang diputuskan masuk profil inti:

| Field | Keterangan |
| --- | --- |
| `education_level` | jenjang pendidikan terakhir atau jenjang pendidikan yang relevan |
| `study_program` | program studi / jurusan |
| `file_ijazah_path` | file bukti ijazah atau dokumen pendidikan |
| `education_verified_at` | timestamp verifikasi pendidikan |
| `education_verified_by` | verifikator pendidikan |

Catatan implementasi:

- pendidikan manual minimal tetap bisa diisi walau ekstraksi PDF gagal
- pendidikan awal tidak perlu dipisah ke rumah domain lain selama masih bagian dari profil inti personel
- detail tambahan seperti riwayat pendidikan berjenjang atau gelar turunan boleh dibahas di diskusi pendidikan jika nanti diperlukan, tetapi blok inti tetap ada di `personels`
Catatan field yang perlu dibaca sebagai bentuk baku untuk implementasi:

- `religion` berisi satu nilai pilihan dari agama resmi atau opsi kepercayaan yang disepakati; jangan ditulis sebagai teks bebas bila nanti dipakai filter
- `abituren` berisi klasifikasi asal anggota dan menjadi kewajiban inti profil, minimal `Reguler`, `ASN/PPPK`, atau `SPPI`; jika nanti ada nilai baru, harus masuk daftar referensi resmi dulu
- `status_keaktifan` menandai lifecycle anggota, bukan tombol hapus; perubahan status wajib menyimpan alasan atau keterangan
- `signature_path` menampung lokasi file tanda tangan atau artefaknya; saat ini tetap `hold` dan belum boleh dianggap wajib
- `honorary_title` atau tanda kehormatan diperlakukan sebagai atribut turunan/opsional, bukan core biodata yang harus ada sejak awal

Catatan peta:

- `users` saat ini masih menampung sebagian atribut akun, tetapi target normatif diskusi ini adalah satu sumber kebenaran di `personels`
- field yang sama tetapi beda nama harus dimigrasikan ke satu bentuk aktif
- `anggota_nomor_hp` belum ada di kode, tetapi sudah menjadi arah normatif untuk nomor HP multi-riwayat
- `SINYALEMEN` adalah domain turunan, bukan rumah data baru untuk field inti yang sudah ada di `personels`
- soft skill dan perusahaan tidak boleh dicampur ke master data inti karena sudah punya rumah diskusi sendiri
- kode aktif masih memakai `gender` sebagai `L`/`P` pada sebagian form dan view, jadi label `Pria`/`Wanita` masih butuh penyelarasan implementasi
- kode aktif masih menjadikan `email` wajib pada alur registrasi dan edit profil, sehingga peleburan ke `personels` belum selesai
- beberapa view masih menampilkan email akun dari relasi `user`, bukan dari `personels`
- `SINYALMEN` di UI dan route masih muncul sebagai nama lama, sehingga standardisasi istilah perlu dilakukan bertahap

#### 2.e. Drift kode aktif yang masih harus dicatat

Walaupun arah normatif diskusi ini adalah `personels` sebagai sumber kebenaran tunggal, kode aktif saat ini masih menyimpan beberapa lapisan `users` yang belum dilebur.

Lapisan drift yang sudah terverifikasi:

- `database/migrations/2026_07_06_055544_create_users_table.php`
  - masih membuat tabel `users` dengan `role_id`, `username`, `email`, `password`, `is_active`, `two_factor_enabled`, `two_factor_secret`, dan `two_factor_recovery_codes`
- `app/Models/User.php`
  - masih menjadi model autentikasi aktif
  - masih menampung relasi `role()` dan `personel()`
  - masih menyimpan `username`, `email`, `password`, dan secret MFA
- `app/Models/Personel.php`
  - masih menyimpan relasi `user()` dan `sinyalmen()`
  - masih menyimpan `phone_number` tunggal pada tabel inti
  - masih menghitung display pangkat perempuan sebagai sufiks `(W)` dari `gender`
- `app\Http\Controllers\Auth\AuthController.php`
  - masih melakukan login utama berbasis `username` ke tabel `users`
  - masih membuat user lalu menautkan `personels`
  - masih mengirim verifikasi awal dan approval melalui `user_id`
- `app\Http\Controllers\Profile\ProfileController.php`
  - masih membaca dan mengubah `username`, `email`, avatar, password, dan MFA pada `users`
  - masih mengambil `phone_number` dari `personels` jika ada, atau dari `users` jika belum ada
- `resources/js/Pages/Profile/Edit.vue`
  - masih menampilkan profil akun dari `user` sebagai sumber utama UI
  - masih menjadi titik drift karena `username`, `email`, dan nomor HP belum seluruhnya diprioritaskan dari `personels`
- `app\Models\Role.php`
  - masih membawa relasi `users()` sebagai nama relasi lama
  - sudah perlu relasi `personels()` agar peran dapat membaca sumber data target tanpa bergantung pada `users`
- `app\Http\Controllers\Auth\AuthenticatedSessionController.php` dan `app\Http\Requests\Auth\LoginRequest.php`
  - masih membawa jalur login Breeze/email yang hidup di repo
  - ini berarti login berbasis email masih ada sebagai jalur runtime aktif selain jalur `username`
- `app\Http\Controllers\Admin\MasterPersonelController.php` dan `app\Http\Requests\Auth\RegisterPersonelRequest.php`
  - masih mewajibkan `email`, `phone_number`, `pangkat`, `nikc`, dan `province` pada alur registrasi dan edit
  - masih menggunakan validasi `gender` berbasis `L`/`P` pada alur input tertentu
- `app\Http\Controllers\Admin\ReportController.php`, `app\Export\PersonelExport.php`, dan beberapa view laporan
  - masih membaca `user->email` ketika mengekspor atau menampilkan data personel
- `app\Http\Controllers\Admin\DashboardAdminController.php`
  - masih punya jalur audit trail yang join ke `users` karena log historis belum dipindah
  - statistik aktif personel sedang diarahkan ke `personels`, tetapi jalur audit masih menunjukkan dependency lama
- `database/seeders/DatabaseSeeder.php`
  - masih menanam data akun awal langsung ke tabel `users`
  - ini wajib ikut workplan migrasi karena nanti seeder harus menghasilkan sumber data yang selaras dengan `personels`
- `app\Http\Controllers\Personel\SinyalmenController.php`, `app\Models\Sinyalmen.php`, dan `database/migrations/2026_07_08_022029_create_sinyalmen_table.php`
  - masih memakai nama lama `sinyalmen` untuk route, model, table, dan view
  - field yang hidup di sana masih berupa tinggi badan, berat badan, golongan darah, rambut, mata, ciri khas, dan cacat tubuh
- `app\Http\Controllers\ProfileController.php`
  - masih ada sebagai controller legacy di root namespace
  - routes aktif saat ini memakai `App\Http\Controllers\Profile\ProfileController`, jadi file root ini adalah drift yang perlu dirapikan atau diarsipkan saat pelebaran final
- `resources/js/Pages/Auth/Register.vue`, `resources/js/Pages/Admin/Personel/Index.vue`, `resources/js/Pages/Personel/Dashboard.vue`, dan `resources/js/Pages/Personel/Sinyalmen.vue`
  - masih menampilkan label lama seperti `Laki-laki/Perempuan`, `Email Akun`, dan `SINYALMEN`
  - halaman-halaman ini belum sepenuhnya mengikuti terminologi `Pria/Wanita` dan `SINYALEMEN`
- `app\Http\Middleware\EnsureProfileComplete.php`, `app\Http\Middleware\EnsureFaceVerified.php`, dan controller turunan personel
  - masih mengandalkan relasi `personel` dari `user` aktif

Implikasi drift:

- saat finalisasi nanti perlu jelas field mana yang dipindahkan total ke `personels`
- perlu jelas apakah `users` dicabut bertahap atau masih dipertahankan sementara sebagai lapisan runtime
- perlu jelas mana yang hanya migrasi data, mana yang perlu perubahan route/auth middleware
- perlu jelas apakah login akhir mendukung `NIKC`, `username`, atau masih perlu jembatan sementara sampai peleburannya selesai

Catatan:

- drift di atas bukan alasan untuk mempertahankan model ganda sebagai norma
- drift di atas justru daftar yang harus dilebur atau diselaraskan pada fase implementasi berikutnya

#### 2.f. Ringkasan hasil audit dan arah solusi

Temuan audit ini disimpan di dokumen diskusi agar nanti rencana kerja tidak lahir dari tebakan, melainkan dari kondisi kode yang benar-benar hidup.

| Temuan audit | Dampak | Arah solusi |
| --- | --- | --- |
| `users` masih hidup di auth, profil, laporan, dan export | sumber kebenaran ganda | lebur atribut akun dan profil ke `personels` secara bertahap |
| `personels` sudah menyimpan sebagian besar biodata inti | kandidat sumber kebenaran utama sudah tersedia | jadikan `personels` rumah tunggal profil anggota |
| `email` masih wajib di alur registrasi dan edit profil | peleburan belum tuntas | pindahkan kepemilikan `email` ke `personels` atau domain akun yang disepakati |
| `phone_number` masih satu field di `personels` | tidak cocok dengan kebutuhan multi-nomor | siapkan entitas nomor HP terpisah dengan status aktif/nonaktif |
| `gender` masih banyak dipakai sebagai `L`/`P` di UI | label profil belum seragam | normalkan ke `Pria` / `Wanita` pada profil dan display pangkat |
| `sinyalmen` masih menjadi nama lama di route, model, dan view | istilah belum seragam | bertahap seragamkan dokumen ke `SINYALEMEN`, lalu selaraskan kode |
| `ProfileController` root namespace masih ada | potensi kebingungan controller aktif | arsipkan atau hapus drift setelah controller aktif dipastikan |
| `Profile/Edit.vue` masih membaca identitas dari `user` | UI profile belum sepenuhnya sumber-data `personels` | alihkan prioritas bacaan ke `personels`, sisakan fallback sementara bila perlu |
| `Role.php` masih menamai relasi sebagai `users()` | nama relasi belum mencerminkan target sumber kebenaran | tambahkan `personels()` dan jadikan `users()` hanya alias transisi |
| `DatabaseSeeder.php` masih menanam akun ke `users` | seeding akan tertinggal jika `users` dihapus | pindahkan seed akun awal ke jalur yang sejalan dengan `personels` |
| `DashboardAdminController.php` masih membaca audit trail yang join ke `users` | log historis masih bergantung pada tabel lama | tandai sebagai dependency migrasi audit trail yang akan diselesaikan di workplan |
| `avatar` masih dipakai di profile UI dan controller | rumah schema belum ditegaskan | putuskan apakah tetap di `users`, dipindah ke `personels`, atau dijadikan attachment terpisah |
| `agama`, `abituren`, dan `signature` belum difinalkan di kode | implementasi belum siap | putuskan dulu di diskusi, lalu turunkan ke keputusan dan schema |
| `riwayat perusahaan` sudah dipisah ke dokumen lain | scope master data tetap bersih | lanjutkan pembahasan di dokumen perusahaan, jangan tarik kembali ke sini |

#### 2.g. Arah rencana kerja kandidat

Bagian ini belum menjadi workplan formal, tetapi sudah cukup jelas untuk dijadikan dasar ketika keputusan lahir.

1. Menetapkan `personels` sebagai sumber kebenaran tunggal profil anggota.
2. Melebur field `users` yang overlap ke `personels`, termasuk `username`, `email`, `password`, dan status akun bila diputuskan.
3. Menyiapkan mekanisme transisi login agar `NIKC` dan atau `username` tetap bisa dipakai selama migrasi.
4. Memisahkan nomor HP menjadi multi-nomor dengan status aktif/nonaktif bila keputusan final mengharuskan riwayat.
5. Menyeragamkan jenis kelamin ke `Pria` dan `Wanita` di profil, UI, dan display pangkat.
6. Menambahkan field yang sudah disepakati tetapi belum ada di kode, seperti `religion`, `abituren`, dan `signature_path` bila `hold` dicabut.
7. Menetapkan `SINYALEMEN` sebagai domain turunan yang membaca data inti tanpa duplikasi.
8. Menyelaraskan seluruh route, controller, view, dan export yang masih membaca data dari `users`.
9. Memindahkan seluruh data akun ke `personels` sampai `users` tidak lagi diperlukan sebagai sumber data.
10. Menghapus tabel `users` setelah seluruh jalur baca-tulis dan runtime beralih ke `personels`.
11. Mengarsipkan controller legacy yang tidak lagi dipakai setelah jalur baru stabil.
12. Menggeser `riwayat perusahaan` ke workstream terpisah sesuai dokumen diskusi khususnya.
13. Menyelaraskan `Role.php` agar relasi utama mengarah ke `personels`, lalu menjadikan `users()` sekadar alias transisi bila masih dibutuhkan runtime lama.
14. Mengubah `DatabaseSeeder.php` agar seed akun awal tidak lagi bergantung penuh pada tabel `users`.
15. Menyelesaikan jalur audit trail dan dashboard yang masih membaca `users` hanya untuk histori atau dependency yang belum dimigrasi.

#### 2.h. Audit per modul dan titik migrasi

##### Modul migration

- `database/migrations/2026_07_06_055544_create_users_table.php`
  - masih membangun rumah data akun terpisah
  - menjadi sumber utama drift karena menyimpan `username`, `email`, password, MFA, dan status aktif
- `database/migrations/2026_07_06_055601_create_personels_table.php`
  - sudah membawa sebagian besar biodata inti
  - masih bergantung ke `users` lewat `user_id`, jadi belum bisa berdiri sendiri
- `database/migrations/2026_07_08_062112_add_pangkat_and_nikc_to_personels_table.php`
  - menandai bahwa `personels` terus diperluas sebagai rumah utama profil
  - mendukung arah lebur, tetapi belum menyentuh data akun
- `database/migrations/2026_07_10_000001_prepare_personels_account_columns_for_users_merge.php`
  - migrasi persiapan untuk menaruh kolom akun ke `personels`
  - belum menghapus `users`, tetapi sudah menyiapkan target schema peleburan
  - disusun sebagai migration baru yang additive dan bersih, bukan pengganti file lama
- `database/migrations/2026_07_10_000002_finalize_users_merge_and_drop_users.php`
  - migration final untuk memindahkan sisa data akun ke `personels`
  - menghapus `users` setelah seluruh jalur baca-tulis sudah bergeser
  - rollback bersifat best-effort dan tetap memerlukan backup bila dieksekusi sungguhan
  - hanya boleh dijalankan setelah runtime dan seeder sudah beralih ke `personels`
- `database/migrations/2026_07_08_022029_create_sinyalmen_table.php`
  - menunjukkan bahwa `SINYALEMEN` masih terpisah dan membaca profil inti
  - tidak boleh menampung field yang sudah hidup di `personels`

#### 2.i. Jalur runtime sisa yang masih menyebut `users`

Audit runtime terakhir menunjukkan `users` belum sepenuhnya hilang dari jalur baca-tulis. Daftar ini penting agar migration dan workplan nanti tidak kehilangan titik migrasi.

| Jalur | Kondisi saat ini | Arah target |
| --- | --- | --- |
| `database/seeders/DatabaseSeeder.php` | masih menanam akun awal ke `users` | pindahkan seed akun awal ke rumah data yang sejalan dengan `personels` |
| `app/Http/Requests/Auth/RegisterPersonelRequest.php` | masih validasi unik ke `users.username` dan `users.email` | cukupkan validasi ke `personels` setelah peleburan final |
| `app/Http/Controllers/Profile/ProfileController.php` | masih membaca dan menulis identitas akun ke `users` sebagai fallback utama | geser baca-tulis utama ke `personels` |
| `app/Http/Controllers/Admin/MasterPersonelController.php` | masih membuat, memperbarui, dan menghapus `User` di samping `Personel` | lebur ke `personels` dan hilangkan dependency `User` saat runtime siap |
| `app/Http/Controllers/Admin/DashboardAdminController.php` | masih join `audit_logs` ke `users` untuk histori aktivitas | tetap dicatat sebagai dependency audit historis sampai skema log diselaraskan |
| `app/Models/Role.php` | masih punya relasi legacy `users()` | jadikan `personels()` sebagai relasi utama dan simpan `users()` hanya sementara bila perlu |
| `database/migrations/2026_07_06_055601_create_personels_table.php` | masih punya `user_id` yang mengarah ke `users` | drop `user_id` setelah cutover final |
| `database/migrations/2026_07_06_055620_create_registrations_table.php` | `verified_by` masih menunjuk ke `users` | sesuaikan ke sumber otorisasi final saat workplan turunan lahir |
| `database/migrations/2026_07_06_055720_create_broadcasts_table.php` | `created_by` masih menunjuk ke `users` | sesuaikan ke sumber otorisasi final saat workplan turunan lahir |
| `database/migrations/2026_07_06_055808_create_logs_tables.php` | beberapa `user_id` masih menunjuk ke `users` | sesuaikan ke audit actor final saat workplan turunan lahir |

Catatan audit runtime:

- jalur yang masih menyebut `users` belum berarti harus dipertahankan selamanya
- sebagian memang legacy transisi, tetapi tetap harus masuk daftar migrasi agar penghapusan `users` tidak menyisakan titik putus
- fokus dokumen ini adalah memetakan dan mengikat arah pindahnya, bukan menjalankan migrasi sekarang

##### Modul auth

- login masih memakai `users` dan `username`
- login fallback sudah mulai membaca `personels.username`, `personels.nik`, dan `personels.nikc` sebelum menyerah
- register masih membuat `users` lalu `personels`
- register sudah menulis akun ke `personels` sebagai target data bertahap
- reset password, verify email, dan MFA masih terikat ke `users`
- ini berarti migrasi akun belum selesai dan harus diselaraskan ketika `personels` menjadi sumber kebenaran tunggal

##### Modul profile

- profil akun masih mengedit `username`, `email`, avatar, password, dan MFA di `users`
- profil akun sudah mulai menyinkronkan `username`, `email`, password, dan MFA ke `personels`
- profil personel masih mengambil `phone_number` dari `personels`, lalu fallback ke `users`
- ada controller legacy root yang menambah drift

##### Modul admin personel

- admin personel masih membuat `users` saat registrasi
- admin personel sudah mulai menulis `username`, `email`, password, status aktif, dan MFA ke `personels`
- admin personel masih menyimpan `phone_number` tunggal di `personels`
- admin personel masih mengandalkan `user_id` untuk banyak alur baca-tulis

##### Modul personel fisik / sinyalmen

- halaman dan controller masih memakai nama lama `sinyalmen`
- field yang hidup di sana masih sebagian overlap dengan profil inti
- migrasi nama dan struktur perlu dilakukan setelah peta field final disahkan

##### Titik migrasi utama yang perlu diturunkan ke workplan

1. Tambah kolom akun ke `personels` jika `users` memang dilebur penuh.
2. Migrasikan data `username`, `email`, password, status aktif, dan MFA ke `personels`.
3. Ubah auth provider dan controller agar membaca `personels`, bukan `users`.
4. Hapus atau arsipkan `users` setelah seluruh pemanggilnya beralih.
5. Hapus `user_id` dari `personels` setelah relasi lama tidak dipakai lagi.
6. Seragamkan penamaan `sinyalmen` menjadi `SINYALEMEN` di dokumen, lalu di kode bertahap.
7. Gunakan migrasi persiapan `2026_07_10_000001_prepare_personels_account_columns_for_users_merge.php` sebagai jembatan schema sebelum drop `users`.
8. Gunakan migration final `2026_07_10_000002_finalize_users_merge_and_drop_users.php` hanya setelah keputusan dan validasi runtime selesai.
9. Jaga agar migration yang disiapkan tetap additive, bersih, dan tidak memasukkan field yang masih hold seperti `avatar` sebelum ada keputusan diskusi.

Catatan migrasi:

- langkah di atas belum workplan formal, tetapi sudah cukup jelas untuk dimasukkan ke rencana kerja setelah keputusan lahir
- bila migrasi diturunkan sebelum auth dan UI ikut berpindah, runtime akan rusak
- karena itu peleburannya harus bertahap, dimulai dari keputusan data lalu keputusan schema, baru keputusan implementasi

#### 2.a. Data master yang diturunkan dari sumber resmi

Selain data inti anggota, ada data master atau atribut turunan yang sebaiknya diisi otomatis dari sumber keputusan resmi ketika NIKC atau nomor keputusan tersedia.

Ruang lingkup yang tampak relevan untuk diskusi ini:

- apakah matra boleh diisi otomatis dan dibekukan dari NIKC
- apakah pangkat boleh diisi otomatis dari NIKC tetapi tetap bisa dikoreksi melalui alur resmi
- apakah koreksi pangkat wajib menyimpan alasan perubahan
- apakah koreksi pangkat wajib menyimpan nomor keputusan
- apakah sistem wajib meminta unggahan PDF keputusan jika file keputusan belum tersedia di repositori sistem
- apakah data turunan dari keputusan dianggap bagian master data, atau hanya referensi yang menempel pada riwayat anggota

Catatan scope terbaru:

- matra Komcad hanya tiga dan diperlakukan hardcode di sistem, jadi tidak perlu master data terpisah
- master data yang tetap perlu dibangun dari keputusan resmi adalah kepangkatan
- keputusan final NIKC sudah lahir di `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`
- jika data dari keputusan sudah ada di sistem, nilai seperti pangkat bisa diisi otomatis lalu tetap punya riwayat koreksi resmi
- `province` yang sudah ada tetap dipakai untuk alamat/domisili dan juga bisa dipakai untuk kebutuhan provinsi NIKC bila maknanya sama; kalau maknanya berbeda, tambah field baru yang eksplisit, jangan ganti diam-diam
- untuk input provinsi yang memang berupa pilihan referensi, idealnya gunakan searchable dropdown atau combobox agar data bisa diketik lalu dipilih dari daftar yang ada
- daftar provinsi 01-34 tetap perlu masuk sebagai referensi data NIKC, tetapi tidak perlu membuat kolom provinsi ganda jika maknanya sama
- pembahasan hukum, regulasi, dan basis pemberhentian Komcad dipindahkan ke [diskusi terpisah tentang referensi internal regulasi](2026.07.10%20Diskusi%20tentang%20Referensi%20Internal%20Regulasi.md)

### 3. Status akun anggota perlu dipisahkan dari status data anggota

Karena user ingin anggota bisa dikelola dan juga nantinya bisa login, diskusi ini perlu membedakan:

- status keberadaan data anggota di sistem
- status akun untuk akses login

Pemisahan itu penting agar pengelolaan data anggota oleh admin tidak otomatis dianggap sama dengan aktivasi akun anggota.

#### 3.a. Formalisasi status keaktifan dan lifecycle anggota

Status keaktifan anggota dipakai sebagai penanda kondisi riwayat hidup anggota di sistem, bukan sebagai mekanisme penghapusan data. Dengan demikian, data anggota tetap dipertahankan sebagai jejak administratif dan audit, sedangkan statusnya yang berubah mengikuti kondisi resmi anggota.

Prinsip dasar lifecycle:

- satu personel hanya memiliki satu status lifecycle aktif pada satu waktu
- perubahan status harus dapat ditelusuri dari alasan, dasar surat keputusan, atau dasar administratif yang sah
- status lifecycle tidak boleh dipakai sebagai kedok untuk menghapus jejak anggota sah
- status lifecycle hanya boleh diturunkan atau diubah oleh pihak yang punya kewenangan
- status yang tidak lagi aktif harus tetap menyisakan riwayat perubahan agar audit tetap utuh

Formulasi status yang dipakai:

- `aktif`: personel sah, data lengkap, dan dapat dipakai untuk kebutuhan operasional
- `nonaktif`: personel sah, tetapi tidak sedang aktif bertugas atau tidak dipakai untuk operasional
- `tidak resmi`: data sudah masuk, tetapi belum terbukti sah sebagai anggota resmi
- `diarsipkan`: data disimpan sebagai catatan sejarah atau bahan audit
- `dipecat`: anggota keluar karena tindakan disiplin atau administratif, dan harus didukung surat keputusan
- `pensiun`: anggota keluar karena masa tugas selesai, dan harus didukung surat keputusan
- `meninggal`: status akhir karena wafat, dan harus didukung surat keputusan atau bukti administratif yang sah

Kondisi transisi status yang dipandang masuk akal:

| Dari | Ke | Pemicu | Keterangan |
| --- | --- | --- | --- |
| `draft` / data awal | `menunggu_verifikasi` | admin selesai input minimal atau hasil ekstraksi SK selesai | personel belum aktif |
| `menunggu_verifikasi` | `aktif` | personel dan super admin menyetujui kelengkapan | boleh dipakai operasional |
| `menunggu_verifikasi` | `diarsipkan` | data tidak resmi atau gagal diverifikasi | tetap disimpan untuk audit |
| `aktif` | `nonaktif` | tidak dipakai operasional sementara atau ada pembekuan sementara | status sah tetap ada |
| `aktif` | `pensiun` | ada dasar surat keputusan pensiun | status akhir nonaktif permanen |
| `aktif` | `dipecat` | ada dasar surat keputusan pemberhentian tidak hormat | status akhir nonaktif permanen |
| `aktif` | `meninggal` | ada bukti sah wafat | status akhir nonaktif permanen |
| `aktif` | `diarsipkan` | data terbukti tidak resmi atau perlu dipisah sebagai audit | tidak dihapus |
| `nonaktif` | `aktif` | dasar pemulihan status tersedia | jika dinilai masih sah |

Aturan lifecycle anggota:

- data anggota diambil dari surat keputusan atau diinput manual bila ekstraksi belum memungkinkan
- personel tidak langsung aktif saat data pertama kali ditambahkan
- personel perlu menjalani verifikasi data dan kelengkapan terlebih dahulu
- setelah disetujui, status berubah menjadi `aktif`
- bila kondisi keanggotaan berubah, status diubah, bukan dihapus
- bila anggota ternyata tidak resmi, data diarsipkan sebagai bahan audit dan pencegahan akses tidak sah
- perubahan ke status `dipecat`, `pensiun`, atau `meninggal` harus menyertakan keterangan dan dasar keputusan yang sah
- perubahan status `nonaktif` harus tetap menyimpan alasan nonaktif agar status tidak kabur
- jika personel kembali sah setelah status `nonaktif`, perubahan ke `aktif` harus direkam sebagai riwayat baru
- status `diarsipkan` dipakai saat data tidak layak dipakai operasional, tetapi tetap perlu dipertahankan sebagai catatan

Contoh formal:

- seorang personel dapat berstatus `aktif` ketika data dan dokumen pendukung telah diverifikasi
- jika kemudian yang bersangkutan pensiun, statusnya diubah menjadi `pensiun` tanpa menghapus jejak datanya
- jika ditemukan bahwa data yang masuk ternyata tidak memiliki dasar keputusan menteri yang sah, statusnya dapat diubah menjadi `diarsipkan`
- jika personel belum selesai verifikasi email atau dokumen, statusnya tetap `menunggu_verifikasi`
- jika personel dihentikan sementara karena alasan administratif, statusnya menjadi `nonaktif`

#### 3.b. Formalisasi alur verifikasi manual

Alur verifikasi manual dipakai ketika data anggota belum dapat diekstrak otomatis dari PDF atau ketika admin harus memasukkan data minimal terlebih dahulu.

Tahapan formal yang disarankan:

1. admin menambahkan data awal dari SK atau dari input minimal
2. sistem menandai data sebagai belum aktif / menunggu verifikasi
3. sistem mengirim email aktivasi atau email verifikasi
4. personel melakukan klaim akun dengan akses awal
5. personel memperbarui password pada login pertama
6. personel melengkapi data yang diminta sistem
7. personel mengunggah dokumen pendukung
8. super admin memeriksa kesesuaian data dan menentukan hasil verifikasi
9. status akhir ditetapkan sesuai hasil verifikasi dan dasar resmi yang ada

Tahap verifikasi oleh personel:

- personel menerima notifikasi email pada alamat aktif yang melekat pada `personels`
- sistem dapat mengirim password sekali pakai sebagai akses awal
- personel melakukan login pertama kali menggunakan akses awal tersebut
- setelah login pertama, personel wajib melakukan reset password
- personel melengkapi atau memeriksa data yang diminta sistem
- personel mengunggah dokumen pendukung yang dibutuhkan, misalnya ijazah komcad atau surat keputusan menteri pertahanan bila diminta
- personel memastikan nomor HP yang dipakai masih aktif jika diperlukan untuk kontak operasional

Tujuan verifikasi oleh personel:

- memastikan alamat email memang dimiliki oleh personel yang bersangkutan
- memastikan akun benar-benar dikuasai oleh personel tersebut
- memastikan dokumen pendukung tersedia sebelum status aktif diberikan
- memastikan kontak yang dipakai untuk notifikasi operasional benar dan dapat dihubungi

Tahap verifikasi oleh super admin:

- super admin memeriksa kecocokan data dengan surat keputusan
- super admin memeriksa status resmi atau tidak resminya anggota
- super admin memeriksa apakah perubahan data yang diajukan memang sah
- super admin memutuskan apakah status akhir layak menjadi `aktif`, `nonaktif`, `diarsipkan`, atau status lain yang relevan
- super admin boleh mengubah data dalam batas kewenangan yang diputuskan
- setiap perubahan oleh super admin wajib tercatat dalam log
- super admin dapat menolak aktivasi bila dokumen tidak cocok, data tidak lengkap, atau otorisasi belum sah

Contoh formal:

- admin menambahkan data manual karena ekstraksi PDF belum memungkinkan
- sistem mengirim email verifikasi ke personel
- personel login dengan password sekali pakai, lalu mengganti password pada login pertama
- personel mengunggah dokumen pendidikan yang diminta
- super admin memeriksa nama, pangkat, NIKC, tanggal lahir, dan pendidikan
- jika cocok, status diset `aktif`
- jika tidak cocok atau tidak terbukti resmi, status disesuaikan menjadi `diarsipkan` atau `tidak resmi`
- jika email belum diverifikasi, status tetap `menunggu_verifikasi`
- jika nomor HP berubah, status kontak harus diubah tanpa menghapus histori nomor lama

### 4. Role dasar yang terkait pengelolaan anggota cukup dibahas pada level kewenangan

Untuk diskusi ini, role tidak perlu dibahas sebagai matrix permission penuh lintas modul. Yang cukup dibutuhkan adalah batas kewenangan dasar terhadap data anggota, misalnya:

- siapa yang boleh membuat dan mengubah data anggota
- siapa yang boleh melihat seluruh anggota
- siapa yang hanya boleh melihat anggota dalam kelompok tertentu
- siapa yang boleh menghapus atau menonaktifkan data sensitif

#### 4.a. Struktur role operasional anggota

Struktur role operasional untuk pengelolaan anggota dipandang bertingkat dan terkait langsung dengan matra serta angkatan atau tahun penugasan.

Ruang role yang sudah terlihat relevan:

- `personel` atau `anggota` sebagai role dasar pemilik data
- `koordinator` sebagai role operasional pengelola data pada lingkup terbatas
- `super_admin` sebagai role otoritatif yang memverifikasi, menyetujui, dan mencatat perubahan

Pembagian tanggung jawab per role:

| Role | Kewenangan utama | Batasan |
| --- | --- | --- |
| `personel` / `anggota` | melihat dan melengkapi data miliknya sendiri, mengunggah dokumen, melakukan klaim akun, dan reset password | tidak boleh mengubah data final yang bersumber dari SK tanpa persetujuan |
| `koordinator` | menginput atau memperbarui data anggota dalam scope matra/tahun/batch yang ditugaskan, serta mengirim data untuk verifikasi | perubahan final tetap perlu pengesahan sesuai batas kewenangan |
| `super_admin` | memverifikasi, menyetujui, menolak, mengarsipkan, mengubah status, dan mencatat log perubahan | tidak boleh menghapus anggota sah tanpa dasar sah dan tanpa mengikuti aturan lifecycle |

Koordinator tidak bersifat tunggal untuk seluruh sistem. Koordinator perlu dibedakan per matra dan per angkatan/tahun, misalnya:

- koordinator udara 2025
- koordinator udara 2023
- koordinator udara 2022
- koordinator darat 2025
- koordinator darat 2024

Karena pada matra darat dapat muncul beberapa batch dalam satu tahun, maka koordinator darat dapat berjumlah lebih dari satu untuk tahun yang sama bila pembinaan atau batch memang dipisah. Dengan demikian, pengelompokan role harus mempertimbangkan:

- matra
- tahun atau angkatan
- batch bila diperlukan
- batas wilayah kewenangan koordinator
- status aktif koordinator untuk tahun yang sedang berjalan

Contoh struktur role yang operasional:

- koordinator udara 2025 bertanggung jawab atas anggota yang masuk batch udara tahun 2025
- koordinator udara 2023 bertanggung jawab atas batch yang terkait tahun 2023
- koordinator darat 2025 batch 1 dan batch 2 dapat memiliki koordinator berbeda bila proses pembinaan dipisah
- super admin tidak terikat batch tertentu dan bertugas sebagai validator final

Catatan operasional:

- koordinator boleh mengubah data dalam batas tertentu
- perubahan koordinator harus tetap divalidasi atau disahkan oleh super admin bila menyentuh data final
- perubahan data oleh super admin wajib tercatat dalam log
- role operasional ini tidak menghapus kebutuhan role dasar lain seperti akses login dan otorisasi sistem
- role koordinator sebaiknya dipetakan ke referensi matra dan angkatan agar tidak menimbulkan tumpang tindih kewenangan

### 5. Import massal layak dibahas sejak awal

Karena manajemen anggota dasar sering dimulai dari data eksisting, import massal adalah kebutuhan yang masuk akal untuk dibahas bersama CRUD manual. Jika ditunda terlalu jauh, keputusan anggota dasar bisa kurang operasional.

Namun import massal di dokumen ini masih perlu diposisikan sebagai capability domain, bukan desain template file teknis.

### 6. Status profil, verifikasi wajah, dan status akun sudah menjadi bagian nyata dari modul anggota

Implementasi aktual sudah memakai beberapa status berbeda:

- `is_active` pada akun user
- `status_profile` pada personel
- `face_verified` pada personel

Diskusi ini perlu menegaskan apakah tiga status itu akan tetap berdiri sebagai domain berbeda atau disederhanakan menjadi satu bahasa status yang lebih tegas.

### 7. Sinyalmen perlu diperlakukan sebagai domain turunan yang membaca profil inti

User sudah menegaskan penulisan yang benar adalah `SINYALEMEN`.

Field yang sudah tampak ada di repo dan perlu dicatat sebagai bagian dari domain ini:

- tinggi badan, satuan centimeter
- berat badan, satuan kilogram
- rambut
- mata
- golongan darah
- tempat lahir
- tanggal lahir
- agama
- alamat rumah
- tanda kehormatan
- tanda tangan, tetapi masih di-hold

Catatan penting:

- `tempat lahir`, `tanggal lahir`, dan `alamat rumah` jangan dibaca sebagai field baru bila sudah ada di profil inti `personels`
- data yang sama cukup disimpan sekali di profil inti, lalu ditarik dari sana oleh sinyalemen saat diperlukan
- jika ada field berbeda nama tetapi maksudnya sama, perlu disatukan supaya tidak bentrok
- `SINYALEMEN` tidak boleh menjadi rumah untuk duplikasi data yang sudah hidup di profil inti

### 8. Peta yang sudah cukup matang untuk difinalkan

Bagian berikut sudah cukup matang sebagai kandidat keputusan, tetapi tetap perlu disahkan formal melalui lifecycle keputusan:

- penyatuan `users` dan `personels` ke `personels`
- login berbasis `NIKC` dan atau `username`
- jenis kelamin `Pria` dan `Wanita`
- `nomor_hp` multi-nomor dengan status aktif atau nonaktif
- `agama` dropdown resmi plus opsi kepercayaan
- `SINYALEMEN` sebagai domain turunan
- `tanda tangan` masih hold
- `soft skill` dan `perusahaan` berada di diskusi terpisah
- `riwayat perusahaan` berada di diskusi terpisah

#### 8.a. Peta field `SINYALEMEN`

| Field | Sumber | Keterangan |
| --- | --- | --- |
| `height_cm` | `personels` atau input turunan | Tinggi badan dalam centimeter |
| `weight_kg` | `personels` atau input turunan | Berat badan dalam kilogram |
| `hair` | `personels` atau input turunan | Warna / model rambut jika dipakai |
| `eyes` | `personels` atau input turunan | Ciri mata yang dipakai untuk identifikasi |
| `blood_type` | `personels` atau input turunan | Golongan darah A, B, AB, O beserta rhesus bila ada |
| `place_of_birth` | `personels` | Jangan duplikasi jika sudah ada di profil inti |
| `date_of_birth` | `personels` | Jangan duplikasi jika sudah ada di profil inti |
| `religion` | `personels` | Agama resmi atau kepercayaan |
| `home_address` | `personels` | Alamat rumah, idealnya tarik dari profil inti |
| `honorary_title` | turunan / referensi | Tanda kehormatan bila diperlukan |
| `signature_path` | turunan / hold | Tanda tangan masih di-hold |

Catatan:

- jika field sudah ada di profil inti, `SINYALEMEN` hanya menarik data yang sama tanpa membuat duplikasi baru
- istilah lama yang masih muncul di kode harus dicatat sebagai drift, bukan dijadikan nama baku dokumen
- jika nanti ada field tambahan khusus identifikasi fisik, baru dibahas sebagai perluasan domain `SINYALEMEN`



## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Website membutuhkan modul anggota dasar | Sudah jelas dari arahan user | Clear |
| 2 | Data inti anggota final | Terkunci pada profil inti `personels` | Siap difinalkan |
| 3 | Status akun anggota | Terkunci sebagai lifecycle dan bukan mekanisme hapus | Siap difinalkan |
| 4 | Batas CRUD anggota | Dipindah ke diskusi struktur organisasi dan grup angkatan | Tercatat |
| 5 | Kewenangan role dasar pada modul anggota | Dipindah ke diskusi struktur organisasi dan grup angkatan | Tercatat |
| 6 | Import massal data anggota | Bergantung pada fitur PDF dan tetap relevan | Tercatat |
| 7 | Pemetaan status profil, wajah, dan akun | Terkunci pada peta kandidat profil | Siap difinalkan |
| 8 | Penyatuan `users` ke `personels` | Terkunci sebagai arah kerja | Siap difinalkan |
| 9 | `NIKC` dan `username` sebagai jalur login | Terkunci sebagai arah kerja | Siap difinalkan |
| 10 | `nomor_hp` multi-nomor dengan status aktif | Terkunci sebagai arah kerja | Siap difinalkan |
| 11 | Jenis kelamin `Pria` dan `Wanita` | Terkunci sebagai arah display | Siap difinalkan |
| 12 | Alamat domisili terstruktur | Terkunci sebagai arah kerja | Siap difinalkan |
| 13 | `agama` dropdown resmi plus kepercayaan | Terkunci sebagai arah kerja | Siap difinalkan |
| 14 | `SINYALEMEN` sebagai domain turunan | Terkunci sebagai domain turunan | Siap difinalkan |
| 15 | `tanda tangan` | Ditahan | Hold |
| 16 | `avatar` | Ditahan sementara karena rumah schema belum diputuskan | Hold |
| 17 | `soft skill` dan `perusahaan` | Dipindah ke diskusi baru | Tercatat |

### Scope yang harus tetap `Hold`

- `tanda tangan` tetap `Hold` dan belum boleh dianggap field wajib
- `avatar` tetap `Hold` sampai diputuskan apakah ia tetap milik akun, pindah ke `personels`, atau jadi attachment terpisah
- `MFA` tetap `Hold` di level diskusi master data karena pembahasannya lebih dekat ke domain akses dan autentikasi

### Scope yang sudah dipisah ke diskusi lain

- `soft skill` dipisah ke diskusi tersendiri
- `perusahaan` dan `riwayat pekerjaan` dipisah ke diskusi tersendiri
- pembahasan detail implementasi autentikasi tetap perlu mengikuti diskusi autentikasi yang relevan

## Poin yang Siap Difinalkan

1. `personels` menjadi sumber kebenaran tunggal untuk profil anggota.
2. Seluruh field yang overlap antara `users`, `personels`, dan domain turunan harus dilebur menjadi satu nomenklatur aktif.
3. `users` masih ada di kode aktif, tetapi seluruh atribut yang masih hidup di sana harus diarahkan ke `personels` dan disiapkan untuk dilebur dalam rencana kerja turunan.
4. Setelah peleburan penuh selesai, `personels` menjadi satu-satunya data utama untuk profil anggota.
5. `SINYALEMEN` ditetapkan sebagai domain turunan yang membaca field yang sama dari profil inti tanpa menduplikasi data.
6. `agama`, `abituren`, `nomor_hp` multi-nomor, dan `signature_path` memiliki peta field kandidat yang sudah cukup jelas untuk dibawa ke keputusan.
7. `desa/kelurahan` wajib sebagai level terakhir alamat terstruktur.
8. `riwayat perusahaan` dan `soft skill` berada di diskusi terpisah dan tidak lagi menjadi scope dokumen ini.
9. `avatar` dan `MFA` tetap berstatus `hold`.

## Checklist Pra-Finalisasi

| # | Yang Wajib Dicatat | Sudah Dibahas? | Catatan |
| --- | --- | --- | --- |
| 1 | Scope (`termasuk` dan `tidak termasuk`) | Ya | Fokus pada data anggota dan pengelolaannya, bukan login teknis |
| 2 | Non-goals | Ya | Tidak membahas implementasi Laravel, migration, API, atau UI detail |
| 3 | Exception (`jika relevan`) | Ya | Struktur organisasi dapat dirujuk ke diskusi lain bila lahir terpisah |
| 4 | Dampak ke fitur lain | Ya | Akan berdampak ke autentikasi, notifikasi, dan dokumen pendukung |
| 5 | Dampak ke Profil Sistem (`jika relevan`) | Ya | `PROJECT_STATUS.md` perlu diselaraskan setelah keputusan final lahir |
| 6 | Data / Schema impact (`jika relevan`) | Ya | Ada dampak schema, tetapi belum boleh dipakukan di tahap diskusi |
| 7 | Hal yang tidak berubah | Ya | Lifecycle docs dan status repo bootstrap tetap berlaku |
| 8 | Acceptance criteria | Ya | Diringkas di bawah sebagai syarat keputusan lanjutan |

Acceptance criteria yang diharapkan bila topik ini nanti difinalkan:

- ada keputusan resmi bahwa `personels` adalah sumber kebenaran tunggal untuk profil anggota
- ada keputusan resmi tentang field inti yang wajib ada dan field yang ditahan
- ada keputusan resmi tentang status dasar anggota atau akun
- ada keputusan resmi tentang jalur login `NIKC` dan atau `username`
- ada keputusan resmi tentang nomor HP multi-nomor dan status aktif
- ada keputusan resmi tentang struktur alamat domisili dan sumber provinsi
- ada keputusan resmi bahwa `desa/kelurahan` wajib sebagai level terakhir alamat terstruktur
- ada keputusan resmi tentang peran `Pria` dan `Wanita` pada profil dan display pangkat
- ada keputusan resmi tentang `SINYALEMEN` sebagai domain turunan
- ada keputusan resmi tentang batas kewenangan CRUD anggota di diskusi struktur organisasi dan grup angkatan
- ada keputusan resmi tentang apakah import massal menjadi bagian scope awal atau workplan turunan

## Catatan untuk AI Agent

- jangan lahirkan keputusan final atau referensi teknis dari dokumen ini sebelum user menyepakati scope dan kewenangan dasarnya
- jangan campurkan detail login, logout, sesi, atau reset password ke dokumen ini kecuali hanya sebagai dependensi yang dirujuk
- jika nanti struktur organisasi anggota menjadi syarat wajib, pastikan ada diskusi atau keputusan yang mendefinisikannya secara eksplisit
- bila repo aktual berubah saat bootstrap Laravel dimulai, audit ulang `PROJECT_STATUS.md` sebelum melahirkan implementasi
- perlakukan `users` dan `personels` sebagai satu rumpun yang sedang disiapkan untuk dilebur ke `personels`
- pastikan field yang overlap tidak disimpan ganda dengan nama berbeda
- gunakan `Pria` dan `Wanita` untuk jenis kelamin profil anggota, dengan `Wanita` sebagai display pangkat perempuan saja
- catat `SINYALEMEN` sebagai domain turunan yang menarik data sama dari profil inti, bukan sebagai sumber data baru
- alamat domisili perlu disiapkan lebih terstruktur karena akan dipakai untuk filter kegiatan
- jaga agar diskusi ini menjadi pengikat satu sumber kebenaran untuk field yang sekarang tersebar di `users`, `personels`, dan domain turunan lain

## Pertanyaan Terbuka

- tidak ada pertanyaan utama yang tersisa di scope dokumen ini
- pertanyaan yang sebelumnya terbuka sudah dipindahkan ke catatan keputusan atau ke diskusi lain bila memang scope-nya berbeda

### Pertanyaan yang masih terbuka di luar scope dokumen ini

- detail `soft skill` dan `skill bahasa`
- detail `riwayat perusahaan` dan `surat izin kantor`
- detail autentikasi lanjutan seperti MFA, reset password, dan aturan login final

### Pertanyaan yang ditahan sebagai `Hold`

- apakah `tanda tangan` benar-benar masuk final profile atau tetap hold
- apakah `avatar` menjadi bagian profil inti atau tetap berada di rumah attachment lain
- apakah `MFA` akan dijadikan atribut profil `personels` atau tetap dipertahankan di domain akses

## Rencana Tindak Lanjut

- [ ] menunggu arahan user untuk data inti anggota dan batas CRUD
- [ ] menyelaraskan diskusi ini dengan diskusi autentikasi agar status akun tidak bentrok
- [ ] jika semua poin utama disepakati, finalkan diskusi ini sebagai sumber keputusan domain anggota

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-03 | Dokumen dibuat untuk membahas fondasi modul anggota dasar pada website sebelum lahir keputusan final |

