# Changelog Docs

# mencatat audit penuh keputusan aktif terhadap repo: drift utama tersisa pada penunjukan hak koordinator yang belum end-to-end, registrasi hybrid/KYC yang belum sepenuhnya sesuai Kep.009, `personels.nik` fresh-install yang masih wajib, unique soft-delete `riwayat_pendidikan`, akses dokumen koordinator yang masih lebih lebar dari Kep.007, OCR/KTA yang masih parsial, notifikasi struktural Kep.006 yang belum hidup, dan metadata/workplan yang masih menyisakan drift
# menyelesaikan drift implementasi role-hak efektif: query koordinator dan broadcast kini membaca role primer + `personel_role_haks`, registrasi/admin create langsung menulis pivot role-hak primer, dan test menutup celah transisi single-role lama
# membersihkan jalur fresh-install fase 1: migration transisional drop `personels.phone_number` yang sudah no-op dihapus dari repo, seeder role dibuat upsert-safe, seed hak efektif menambahkan role `personel` pada akun Komcad yang juga memegang hak admin/koordinator, dan workplan dicatat ulang dengan sisa jujur bahwa NIK sipil seed `mdwikiar` masih placeholder menunggu data sumber
# menegaskan bahwa Super Admin dapat melekat pada personel Komcad penuh, lalu menyelaraskan redaksi Kep.003/Kep.006/Kep.009 dan workplan agar tidak lagi membingungkan Komcad penuh dengan pengecualian non-Komcad
# menyesuaikan seed Super Admin ke identitas Komcad penuh `mdwikiar` / `gwganteng`, menambahkan pivot hak role agar Super Admin bisa menumpuk dengan hak organisasi lain, dan melipat schema fresh-install untuk menyingkirkan drift `phone_number` lama dari `personels`
- menyiapkan kredensial seed Super Admin fresh install: username `mdwikiar`, password `gwganteng`, lalu dipertegas sebagai Super Admin Komcad penuh sesuai data pengguna
- memperbaiki drift multi-grup lanjutan: koordinator/wakil kini wajib Komcad berpangkat Perwira, seeder koordinator/wakil memakai `Letda KC`, broadcast `GRUP_ANGKATAN` membaca pivot hak multi-grup, dashboard koordinator scoped ke grup yang dipimpin, pivot hak wajib SK, dan route MFA dibuat benar-benar dormant/tak reachable
- menyesuaikan constraint proyek ke PHP `^8.4` agar selaras dengan target Laravel 13 dan mayoritas hosting yang belum menyediakan PHP 8.5
- menahan WhatsApp gateway sebagai artefak dormant: dispatch WA dari approval/broadcast dimatikan, route uji koneksi hanya mengembalikan status hold, UI pengaturan menampilkan banner hold, sementara file job/log/setting tetap dipertahankan untuk keputusan domain berikutnya
- menambahkan referensi teknis `2026.11.005 tentang Panduan Runtime OCR Poppler dan Tesseract` berdasarkan pembanding lokal `dewa_cuan`, agar kebutuhan binary OCR hosting terdokumentasi tanpa menjadi keputusan normatif baru
- menutup temuan audit implementasi Kep.007/Kep.008: semua tautan dokumen sensitif di UI admin verifikasi/edit personel diarahkan ke route private berlog, disk `private` tidak lagi melahirkan route serve langsung, dan akses/verifikasi pendidikan koordinator-wakil dibatasi ke grup yang dikoordinasi
- menambahkan wrapper Composer permanen di repo (`scripts/composer.ps1` dan `scripts/composer.cmd`) agar Composer bisa dipakai meski `php`/`composer` global belum ada di PATH; status kebutuhan Composer dicatat di profil sistem
- menyiapkan fondasi Kep.010 OCR/import SK tanpa Composer tambahan: konfigurasi binary Poppler/Tesseract, service ekstraksi teks/OCR, parser entri SK, job queue import, job OCR dokumen KTP/ijazah/sertifikat, status import artefak, dan tombol admin `Ekstrak dari PDF`
- menguatkan OCR dokumen/import SK: entri lama tidak dihapus saat parse kosong, hasil teks OCR dapat dibuka via route private berlog, route dokumen private menolak path traversal/absolut, upload KTP/ijazah/sertifikat mengantrikan OCR dengan confidence/error, tombol import disabled jika `pdftotext` belum tersedia, health service dibuat jujur terhadap binary yang hilang, dan catatan cross-validation NIKC dasar ditambahkan ke hasil match
- menyelaraskan workplan implementasi terbaru: route aktual 76 setelah MFA route dibuat dormant/tak reachable, F5 memakai binary/service OCR tanpa package Composer baru, dan QA DB lokal ditunda karena fresh install belum diputuskan user
- memperbaiki kompatibilitas migration test SQLite untuk pencabutan `personels.phone_number` dengan menjatuhkan index sebelum drop column, sambil tetap mempertahankan target fresh install MySQL/MariaDB
- menyelaraskan test auth/profile dari scaffold `User`/Breeze lama ke model `Personel`, login `username`, route akun aktif, dan alur registrasi antrean verifikasi; test fitur auth bawaan yang belum aktif diberi skip eksplisit
- mencatat ulang status QA workplan implementasi akun/pendidikan/repositori/OCR/format nama: `route:list`, `php -l`, `php artisan test`, build frontend, Composer validate lulus, dan OCR health mendeteksi `pdftotext` tetapi masih membutuhkan `pdftoppm`/`tesseract`; verifikasi DB penuh menunggu database fresh install yang sesuai
- melahirkan `2026.Kep.007 tentang Repositori Dokumen dan Relasi Dokumen Anggota` (dari `[SELESAI] 2026.07.03 Diskusi tentang Repositori Dokumen dan Relasi Dokumen Anggota`)
- melahirkan `2026.Kep.008 tentang Riwayat Pendidikan dan Diklat Anggota` (dari `[SELESAI] 2026.07.03 Diskusi tentang Riwayat Pendidikan dan Diklat Anggota`)
- melahirkan `2026.Kep.009 tentang Akun Login Logout Role dan Verifikasi Anggota` (dari `[SELESAI] 2026.07.03 Diskusi tentang Klaim Akun Login Logout Role dan Verifikasi Anggota`)
- melahirkan `2026.Kep.010 tentang OCR Dokumen dengan Verifikasi Admin` (dari `[SELESAI] 2026.07.03 Diskusi tentang OCR Dokumen dengan Verifikasi Admin`)
- mengarsipkan kelima diskusi di atas ke `00 Arsip/` dengan prefix `[SELESAI]` (timestamp 2026-07-13 10:32:57 WIB)
- melahirkan workplan `2026.07.13 Rencana Kerja tentang Implementasi Keputusan Domain Akun, Pendidikan, Repositori, OCR, dan Format Nama` sebagai turunan Kep.007–011 (fase F1–F6)
- implementasi lanjut (commit ca43e0e + berikut): F1 sebagian (migration personels + riwayat_pendidikan + document_access_logs + kyc_submissions), F3 selesai (KYC aktif: model/relasi/route/middleware, login+registrasi nikc/username, submitted_at bukan self-verify), F2 (disk `private` + upload KTP/photo/SK/kyc ke private di AuthController/SuratKeputusanController/MasterPersonelController/KycSubmissionController), F6 selesai. Sisa: F2 route download+access log, F4 EducationController, F5 OCR, F1 seeder Super Admin. php -l lulus; runtime blokir (Laragon mati + Laravel 13 butuh PHP>=8.4.1)

- memfinalkan diskusi struktur organisasi (`2026.07.03`) sebagai `[DITETAPKAN]` di `00 Arsip` pada 20:15:35 WIB dan melahirkan `2026.Kep.006 tentang Struktur Organisasi dan Grup Angkatan` sebagai keputusan final (paradigma personel+hak, kewenangan koordinator/wakil, identitas super admin non-Komcad, notifikasi, legalisasi drift komandan->koordinator)
- melahirkan referensi teknis `2026.11.004 tentang Turunan Operasional Hak Multi-Grup Angkatan` sebagai turunan `2026.Kep.006` (model pivot, constraint, flag is_primary, interaksi penunjukan)
- mengamandemen `2026.Kep.003` klausul 16: pengecualian identifier `Super Admin` non-Komcad (NRP per matra / NIK KTP) merujuk `2026.Kep.006`; menambahkan metadata `Diubah oleh` dan changelog versi 1.3
- menyelaraskan `docs/02 Keputusan/README.md` agar mendaftarkan `2026.Kep.006` sebagai keputusan aktif
- memfinalkan dan mengarsipkan diskusi adaptasi format identitas keputusan sebagai `DITETAPKAN`
- merevisi keputusan payung identitas dokumen formal menjadi `2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal`
- menegaskan bahwa format keputusan `YYYY.Kep.XXX` berlaku mulai sejak ditetapkan dan tidak berlaku surut
- menyelaraskan README aktif agar keputusan payung governance menunjuk `2026.Kep.001`
- mencentang acceptance criteria keputusan payung setelah batch identitas keputusan dan sinkronisasi governance inti selesai
- menurunkan artefak `Surat Keputusan` ke runtime admin melalui modul pencatatan metadata, lampiran PDF opsional, indeks nama, pencocokan personel, dan reuse untuk penyesuaian pangkat
- melengkapi identitas grup angkatan aktif ke struktur `Matra-Tahun-Abituren-Batch`, termasuk relasi wajib ke artefak `Surat Keputusan`
- menghubungkan riwayat pangkat ke artefak `Surat Keputusan`, menambahkan checkpoint verifikasi, dan membuka jalur penyesuaian pangkat massal per entri lampiran
- menyelaraskan fresh install seed, sidebar admin, form grup angkatan, dan form edit personel agar reuse artefak `Surat Keputusan` hidup di UI
- membersihkan migration fresh install dengan melipat perubahan final ke base migration dan mencabut migration transisi/no-op yang hanya relevan untuk drift lama
- memisahkan verifikasi wajah awal dari `face recognition`; file modul wajah dikembalikan sebagai fondasi dormant nonaktif, sedangkan `face recognition` tetap ditahan dan dipindahkan ke diskusi khusus
- menahan arsip workplan 10 Juli dan 12 Juli sambil menunggu QA manual user
- mencatat drift audit terbaru ke diskusi broadcast, akun, dan struktur organisasi; sekaligus merapikan referensi teknis grup angkatan serta mengoreksi status workplan 12 Juli agar tidak mengklaim fase yang masih bergantung pada keputusan domain lain
- menonaktifkan sementara route ekspor PDF/Excel broadcast (`report.broadcast.pdf`, `report.broadcast.excel`) karena method `broadcastPdf`/`broadcastExcel` belum ada di `ReportController` sehingga route mengembalikan 500, sekaligus mematuhi status `hold` PDF/Excel pada diskusi broadcast
- menonaktifkan juga route ekspor PDF/Excel personel (`report.personel.pdf`, `report.personel.excel`) agar seluruh ekspor PDF/Excel patuh status `hold` diskusi broadcast; pencilan ini dicatat ke diskusi broadcast agar penyelesaiannya menunggu keputusan domain tersebut
- merevisi workplan 12 Juli untuk memotong klausul verifikasi `Super Admin` yang menggantung, menyelaraskan aktor pemrosesan `SK` massal ke role `admin` (sebutan harian `Super Admin` per klarifikasi user), serta mendeklarasikan workplan 12 Juli sebagai pemilik tunggal artefak `Surat Keputusan`
- mencatat klarifikasi `Super Admin` ke diskusi akun dan penahanan hak multi-grup ke diskusi struktur organisasi agar dependency scope punya rumah lifecycle yang bersih
- audit drift fresh install: `angkatan` di `personels` tetap sebagai field representasi `Tahun` yang diturunkan dari `TMT Penetapan` (sudah via accessor), sesuai Kep.004 klausul 204 yang mengizinkan `angkatan` disimpan sebagai field representasi `Tahun`; **tidak perlu diperbaiki** (bukan drift)
- merevisi migration `personel_status_histories` agar kolom `reason` nullable, selaras dengan Kep.004 klausul 35 yang membolehkan `alasan atau dasar yang sah` (sebelumnya `reason` dipaksa wajib); ini konformitas ke keputusan final, bukan topik diskusi
- memutuskan B3 (redundansi `phone_number` di `personels` vs `personel_phone_numbers`): arah Y2 **sudah dieksekusi** — kolom tunggal `personels.phone_number` dicabut via migration `2026_07_12_000001_drop_phone_number_from_personels_table` (fresh install, tanpa DB existing); akses `$personel->phone_number` kini dilayani accessor yang membaca nomor primer dari `personel_phone_numbers` (backward-compatible dengan 19 referensi runtime), sehingga tidak ada write lagi ke kolom personels. Aturan detail `is_primary`/`is_active`/`verified` dan kendala OTP/verifikasi belum siap tetap dibahas di diskusi Manajemen Kontak Anggota.
- **mengarsipkan** workplan `2026.07.10` (Sinkronisasi Master Data) dan `2026.07.12` (Sinkronisasi Artefak SK, Grup Angkatan, Penyesuaian Pangkat) ke `docs/00 Arsip/` dengan prefix `[SELESAI] 2026.07.12`. Kedua workplan ditutup dengan jujur: scope yang belum final (detail kontak HP, multi-grup sah, softcopy personel, broadcast PDF/Excel) dialihkan ke diskusi terkait, bukan diklaim selesai. Catatan: penutupan arsip ini belum didahului QA runtime penuh (`php artisan migrate:fresh`/`db:seed`/`test`) karena env Laragon + DB belum siap; verifikasi yang ada baru level `php -l` + ketiadaan write ke kolom ter-drop. QA runtime tetap menjadi pekerjaan pengguna saat env siap.

## 2026-07-03

- membuat struktur awal `docs/` untuk repo `kctrimatra`
- menetapkan lifecycle dokumen: `00 Arsip`, `01 Profil Sistem`, `02 Keputusan`, `03 Rencana Kerja`, `04 Diskusi`, `10 Referensi Internal`, `11 Referensi Teknis`
- menambahkan baseline governance untuk proyek baru berbasis Laravel 13
- menambahkan `AGENTS.md` sebagai guardrail kerja agent di level repo
- melahirkan keputusan payung `2026.02.001 tentang Standar Identitas dan Penamaan Dokumen Formal`
- mengarsipkan diskusi adaptasi governance sebagai sumber lahirnya keputusan payung pertama
- melahirkan workplan sinkronisasi dokumen governance inti
- menyelaraskan README dan instruksi governance inti agar mengikuti keputusan payung baru
- menutup dan mengarsipkan workplan sinkronisasi governance inti setelah QA user
- membuka diskusi baru `2026.07.03 Diskusi tentang Standar UI dan Design System Kctrimatra`
- merapikan diskusi standar UI dan design system hingga siap difinalkan, termasuk persetujuan `IBM Plex Sans` sebagai font utama awal
- mengarsipkan diskusi standar UI dan design system sebagai dokumen yang ditetapkan
- melahirkan keputusan aktif `2026.02.002 tentang Standar UI dan Design System Kctrimatra`
- melahirkan referensi teknis `2026.11.001 tentang Design Tokens Kctrimatra`
- melahirkan referensi teknis `2026.11.002 tentang Katalog Komponen UI Kctrimatra`

## 2026-07-09

- memperkaya diskusi autentikasi, master data, notifikasi, struktur organisasi, kontak, dan pangkat dengan temuan kode aktual
- membuka diskusi baru `2026.07.09 Diskusi tentang Broadcast Kegiatan, Presensi, Monitoring, dan Laporan Operasional`
- membuka diskusi baru `2026.07.09 Diskusi tentang Nomor Induk Komponen Cadangan`
- menyelaraskan scope NIKC dengan diskusi master data anggota dan klaim akun, termasuk auto-fill atribut turunan dari keputusan dan lampiran PDF keputusan
- menegaskan matra sebagai hardcode, kepangkatan sebagai master data, dan kewajiban unggah PDF keputusan jika file belum ada di sistem
- merapikan diskusi NIKC agar siap difinalisasi, menegaskan penggunaan ulang field `province` bila maknanya sama, dan menahan revisi implementasi ke rencana kerja
- mencatat drift implementasi NIKC secara eksplisit di diskusi agar audit berikutnya tidak mengulang temuan yang sama
- menambahkan arah penanganan drift NIKC agar penurunan ke rencana kerja nanti tidak menabrak hasil diskusi
- menegaskan alur `NIKC belum ada di sistem` sebagai out-of-scope untuk diskusi NIKC agar scope final tetap fokus
- menambahkan pemetaan diskusi terkait NIKC agar scope turunan bisa dicatat langsung ke file diskusi yang tepat saat finalisasi
- memfinalkan diskusi NIKC menjadi keputusan aktif `2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan`
- mengarsipkan diskusi NIKC sebagai `[DITETAPKAN]` setelah finalisasi
- menurunkan workplan formal untuk sinkronisasi implementasi NIKC terhadap keputusan baru
- menautkan keputusan NIKC ke diskusi master data serta klaim akun agar scope turunan tetap konsisten
- menyelaraskan `docs/01 Profil Sistem/PROJECT_STATUS.md` dengan kondisi repo yang sudah parsial
- menandai MFA, PDF, dan Excel sebagai `hold` dalam diskusi terkait
- memperbaiki bug route Komandan, broadcast personel, status presensi, dan view monitoring WhatsApp
- menormalkan tampilan pangkat perempuan lewat atribut `display_pangkat` dan merapikan nama migration lama yang berantakan
- memisahkan sumber tunggal pangkat, pendidikan, dan OCR dari draft format nama lengkap dengan membuat diskusi pengikat baru
- melengkapi diskusi pangkat dengan daftar draft awal 18 tingkat sekaligus menegaskan arah master pangkat dasar yang lebih sederhana
- menegaskan pangkat default klaim sebagai `Prada KC`, `Serda KC`, dan `Letda KC` dengan varian perempuan `(W)` dan mekanisme SK untuk pangkat lain
- melengkapi diskusi format nama lengkap dengan salinan draft user yang utuh, lalu memisahkan prefix pangkat, suffix gelar, dan OCR ke rumah domain masing-masing
- menyelaraskan implementasi NIKC di approval, registrasi, verifikasi, dan master personel agar tidak lagi mengenerate NIKC baru
- menambahkan master data referensi `master_kepangkatan` dan `master_provinsi` beserta seed awal untuk validasi NIKC
- memperbarui form registrasi agar memakai NIKC 17 digit tetap dan pilihan provinsi berbasis data referensi
- memverifikasi perubahan frontend dengan `pnpm build` setelah sinkronisasi Vue selesai
- membuka ulang workplan sinkronisasi NIKC setelah audit menemukan validasi format NIKC yang belum lengkap dan bypass approval tanpa NIKC
- memperketat validasi NIKC dengan rule reusable serta menutup bypass approval untuk personel tanpa NIKC
- mengikat validasi NIKC ke pangkat, matra, dan provinsi terpilih agar komponen NIKC konsisten dengan data personel
- menyamakan respons error approval kosong NIKC antara action dan controller verifikasi
- melengkapi jalur admin personel dengan view create yang nyata dan field `province` agar validasi NIKC tidak kehilangan komponen provinsi
- membetulkan mapping ekspor personel agar membaca field `SINYALEMEN` yang benar untuk tinggi badan dan golongan darah
- menghapus route import personel yang menggantung agar tidak menjadi endpoint 500 tersembunyi
- memperketat migration fresh install agar `kode_nikc`, `nikc`, dan `pangkat` tidak nullable dan selaras dengan keputusan
- mengarsipkan workplan sinkronisasi implementasi NIKC setelah seluruh temuan audit ditutup
- menyinkronkan approval personel agar status aktif melekat di `personels`, menormalkan validasi dan tampilan personel agar membaca email dari sumber canonical, dan merapikan ekspor serta PDF personel agar tidak lagi bergantung pada fallback `users` untuk data anggota
- memperbarui status repo agar mencerminkan bahwa sinkronisasi master data personel masih berjalan dan menunggu QA user sebelum diarsipkan

## 2026-07-10

- menegaskan penyatuan `users` dan `personels` ke arah `personels` sebagai sumber kebenaran profil anggota
- menambahkan klarifikasi `SINYALEMEN` sebagai domain turunan dari profil inti anggota
- merapikan catatan alamat domisili agar provinsi domisili dan provinsi latsarmil bisa berbagi sumber data provinsi yang sama bila maknanya sama
- memperkaya peta field master data anggota agar `users`, `personels`, `nomor_hp`, `agama`, `abituren`, dan `SINYALEMEN` punya batas yang lebih jelas
- mencatat drift kode aktif dari auth, profil, master personel, dan `SINYALEMEN` ke diskusi master data anggota
- menambahkan ringkasan hasil audit dan arah solusi agar `users` dapat dilebur ke `personels` dalam rencana kerja
- menambahkan audit per modul dan titik migrasi utama untuk peleburan `users` ke `personels`
- menambahkan migrasi persiapan `personels` agar kolom akun punya target schema sebelum `users` dihapus
- mengarahkan auth fallback ke `personels` dan menyalurkan write path profile/admin akun ke `personels` sebagai sumber data bertahap
- menyiapkan migration final untuk memindahkan sisa data akun ke `personels` lalu menghapus `users` setelah keputusan terbit
- menandai sisa pembaca `users` yang masih tersisa di seeder, relasi role, dashboard admin, dan UI profile agar daftar migrasi lengkap
- merapikan UI profile agar identitas membaca `personels` terlebih dulu dan relasi role memiliki jalur `personels()` sebagai target utama
- menegaskan migration peleburan `users` ke `personels` sebagai file baru yang additive dan bukan pengganti migration lama
- menandai scope `hold` master data untuk `tanda tangan`, `avatar`, dan `MFA`
- mencatat audit runtime sisa yang masih menyebut `users` sebagai daftar migrasi kerja yang harus diselesaikan nanti
- memperjelas alur ekstraksi data anggota dari surat keputusan, input manual minimal, struktur alamat bertingkat, multi nomor HP, dan status nonaktif nonhapus
- mencatat hasil baca PDF keputusan menteri sebagai sumber field ekstraksi utama: nomor keputusan, tanggal, judul, nama, lahir, pangkat, NIKC, pendidikan, dan TMT penetapan
- menambahkan referensi internal UU Nomor 23 Tahun 2019 sebagai aturan utama Komponen Cadangan dan acuan pemberhentian
- memformalkan status lifecycle anggota, verifikasi manual oleh personel, dan validasi otoritatif super admin
- memperdalam status transisi, alasan perubahan status, dan pemetaan lifecycle anggota
- menegaskan struktur role operasional anggota: personel, koordinator per matra/angkatan, dan super admin
- membuka diskusi baru `2026.07.10 Diskusi tentang Soft Skill Anggota`
- membuka diskusi baru `2026.07.10 Diskusi tentang Riwayat Pekerjaan dan Perusahaan Anggota`
- menandai nomor HP sebagai data multi-nomor dengan status aktif atau nonaktif per nomor
- memfinalkan diskusi master data anggota menjadi keputusan aktif `2026.Kep.004 tentang Standar Master Data dan Status Personel`
- mengarsipkan diskusi master data anggota sebagai `[DITETAPKAN]` setelah keputusan tersebut lahir
- melahirkan workplan aktif `2026.07.10 Rencana Kerja tentang Sinkronisasi Master Data dan Status Personels`
- mencatat temuan audit pra-perbaikan dalam workplan sinkronisasi master data dan status personels
- memperbaiki migration fresh install agar tabel `users` dicabut, schema `personels` memuat field keputusan sekaligus akun autentikasi, nomor HP multi-nomor punya tabel sendiri, dan riwayat status personel tersedia sejak instalasi awal
- mengubah auth provider Laravel agar memakai `personels` sebagai model autentikasi tunggal
- merapikan naming relasi monitoring log dari `user` ke `personel` dan menyeragamkan label gender ke `Pria/Wanita`
- memperbaiki statistik dashboard komandan agar membaca `personels` aktif secara langsung tanpa relasi `user`
- membatasi broadcast admin ke role `personel` agar admin/komandan tidak ikut ter-target saat mengirim maklumat
- menyeragamkan istilah user-facing `SINYALEMEN`, memperbaiki gender `Pria/Wanita`, serta menahan jalur `avatar` dan `MFA` sesuai keputusan aktif

## 2026-07-11

- menyelaraskan foreign key log internal ke `personel_id` pada migration, trait audit, model audit/login, controller login, dan dashboard admin
- mengembalikan kolom session fresh install ke `user_id` karena Laravel database session handler memang mengisi kolom itu meski auth model aktif sudah `personels`
- merapikan halaman profil agar tidak lagi membawa prop `user` dan memakai `personel` sebagai sumber data utama
- membetulkan aksi notifikasi dari `GET` ke `POST` supaya penandaan baca tidak mutasi state lewat request idempotent
- mencabut migration residu `broadcasts.matra` dan `broadcast_responses.status` yang tidak lagi dipakai code aktif
- membetulkan UI admin personel agar membaca `status_attendance` dan bukan `status`
- mengunci respons broadcast personel agar hanya bisa dikirim ke target yang memang dituju di `broadcast_targets`
- menegaskan bahwa `personel` dan `anggota` adalah sinonim domain; display boleh memakai `anggota`, tetapi backend tetap memakai `personel` sebagai istilah dominan
- membersihkan scaffold auth lama yang sudah tidak dipakai dari jalur aktif agar tidak ada route/controller starter kit yang tersisa secara diam-diam
- memperbarui diskusi struktur organisasi agar mengikuti kondisi nyata dashboard `admin`, `komandan`, dan `personel`, lalu menautkan target terminologi diskusi ke `admin`, `koordinator`, dan `anggota`
- membuka diskusi baru khusus role lapangan agar `Danton`, `Danru`, `Danpok`, dan `Koordinator Lapangan` tidak bercampur dengan struktur organisasi permanen
- memfinalkan diskusi pangkat menjadi keputusan aktif `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota`
- menormalkan pangkat ke suffix `KC`, mempersempit klaim langsung ke `Prada KC`, `Serda KC`, dan `Letda KC`, serta menyiapkan riwayat pangkat untuk perubahan yang dibuktikan SK
- merevisi keputusan NIKC dan pangkat agar digit pertama NIKC memakai kelompok pangkat, 18 tingkat pangkat tercantum sebagai lampiran normatif, serta rujukan arsip yang lama diselaraskan ke nama dokumen final
- mempertegas keputusan pangkat bahwa klaim langsung hanya berlaku pada penetapan awal atau klaim awal, sedangkan transisi seperti `Prada KC -> Serda KC`, `Prada KC -> Letda KC`, `Serda KC -> Letda KC`, serta pangkat antara seperti `Pratu KC`, `Sertu KC`, dan `Lettu KC` wajib melalui pengajuan
- memperkaya keputusan NIKC dengan lampiran kelompok pangkat, daftar kode provinsi Latsarmil, skenario input, sifat permanen, dan contoh parsing agar keputusan cukup lengkap sebagai dokumen utama
- mengaktifkan kembali workplan sinkronisasi implementasi NIKC karena audit user menemukan gap kelengkapan keputusan dan workplan sebelumnya diarsipkan terlalu cepat
- menegaskan struktur `Matra-Tahun-Batch` sebagai `UNIQUE`, menetapkan `batch` opsional, dan membatasi `wakil koordinator` satu per grup
- menegaskan verifikasi `koordinator` hanya untuk klaim awal, riwayat pergantian koordinator tersimpan di riwayat grup angkatan, dan notifikasi koordinator mencakup klaim akun, kegiatan, serta perubahan grup yang berdampak langsung
- menegaskan setiap personel hanya melekat ke satu grup angkatan, menambahkan tabel contoh grup angkatan, dan merinci perubahan data grup yang dianggap berdampak langsung
- menegaskan `TMT Penetapan` sebagai tanggal lengkap `DD-MM-YYYY` sesuai nomor keputusan menteri, bukan sekadar label tanggal
- menegaskan bahwa `Tahun` untuk kombinasi unik grup diambil dari tahun pada `TMT Penetapan`, sehingga kunci unik tetap `Matra-Tahun-Batch`
- menyinkronkan runtime permanen dari `komandan` ke `koordinator` pada route, controller, page Inertia, store auth, seeder, dan folder UI, serta membuka dashboard yang sama untuk role `wakil koordinator`
- menyesuaikan fresh install agar role database langsung memakai `admin`, `koordinator`, `wakil koordinator`, dan `personel` tanpa alias transisi `komandan`
- mencatat di diskusi aktif bahwa sinkronisasi implementasi `komandan -> koordinator` terjadi terlalu dini saat topik masih berada pada fase diskusi, sehingga statusnya adalah drift implementasi dan pelanggaran lifecycle
- menyelaraskan representasi matra aktif ke bentuk kanonik `Darat/Laut/Udara` pada schema fresh install, seed, filter UI admin, target broadcast, dan agregasi dashboard, sambil tetap mempertahankan lapisan normalisasi untuk data lama `AD/AL/AU`
- merapikan referensi `master_provinsi` agar kode provinsi NIKC mengikuti keputusan aktif dan tercatat sebagai temuan yang sudah ditangani di workplan sinkronisasi NIKC
- memaksa jalur edit pangkat admin membawa dasar pengajuan, nomor SK, tanggal SK, dan judul SK saat pangkat berubah dari penetapan awal, lalu mencatat penutupan drift tersebut di workplan aktif
- mencatat kebutuhan revisi keputusan master data agar `TMT Penetapan` menjadi sumber utama `Tahun`, sekaligus memetakan drift schema, validasi, UI, dan fitur aktif yang masih membaca `angkatan` sebagai input manual mandiri
- merevisi keputusan master data agar `TMT Penetapan` penetapan awal menjadi sumber kebenaran `angkatan`, sementara `TMT` pada penyesuaian ijazah atau kenaikan pangkat hanya berlaku untuk riwayat pangkat
- menyelaraskan model, validasi, seed, ekspor, dan form registrasi/admin agar `angkatan` diturunkan dari `TMT Penetapan` awal dan tidak lagi diminta sebagai input manual utama
- menambahkan rujukan silang singkat pada keputusan NIKC dan kepangkatan, serta merapikan label user-facing agar `Tahun Angkatan` konsisten dibaca sebagai tahun turunan dari `TMT Penetapan` awal
- menambahkan audit khusus di workplan master data tentang syarat agar `Matra-Tahun-Batch` benar-benar hidup di schema dan UI, serta merapikan diskusi aktif agar membedakan `tahun angkatan` dari `grup angkatan`
- melahirkan schema awal `grup_angkatans`, mengaitkan `personels` ke grup angkatan formal, dan menambahkan target broadcast `GRUP_ANGKATAN` agar struktur `Matra-Tahun-Batch` mulai hidup di runtime
- merapikan istilah teknis aktif dengan memisahkan `tahun_angkatan` dari `grup_angkatan_uuid`, menambahkan preview `Grup Angkatan` di form personel, serta menyelaraskan PDF/ekspor/daftar verifikasi ke istilah baru
- menyesuaikan batch ini ke target `fresh install` dengan melipat schema grup angkatan dan histori ke migration inti yang sudah ada, bukan mempertahankan migration tambahan terpisah
- menambahkan modul admin `Master Grup Angkatan`, histori perpindahan grup personel, histori pergantian koordinator grup, filter verifikasi berbasis grup, serta label target broadcast yang human-readable
- mengubah form admin personel agar memilih `grup angkatan` langsung dari master, menambahkan halaman detail grup angkatan, dan memperketat validasi bisnis koordinator/wakil agar tidak drift dari grup yang dipimpinnya
- menegaskan di keputusan master data bahwa koordinator/wakil hanya boleh terikat pada satu grup angkatan aktif dan setiap perpindahan grup personel wajib memiliki histori beserta alasan
- mewajibkan alasan perpindahan grup juga pada form edit personel admin, menambahkan badge `Koordinator` dan `Wakil` di daftar personel, serta memblokir grup angkatan berstatus `arsip` dari penerimaan anggota baru lewat semua jalur admin aktif
- merevisi keputusan master data dan melahirkan referensi teknis baru untuk mengikat kontrak teknis grup angkatan, form admin personel, histori perpindahan, badge peran, dan guard grup `arsip` agar file aktif tidak lebih maju daripada dokumen
- membuka diskusi baru tentang identitas grup angkatan berbasis `abituren` dan `Surat Keputusan`, lalu mematangkan diskusi referensi internal regulasi agar posisi `Surat Keputusan` sebagai artefak sumber formal domain aktif tercatat jelas
- mematangkan diskusi grup berbasis `abituren` dan diskusi regulasi dengan skenario hak multi-grup berbasis SK, reuse satu SK untuk hak grup dan penyesuaian pangkat, penegasan `ASN` sebagai payung `PNS` dan `PPPK`, serta catatan bahwa UI/UX pengelolaan grup dan SK wajib diseragamkan ke pedoman aktif
- menambahkan skenario unik jalur `SPPI` yang menunjukkan NIKC bisa tetap atau berubah, lalu mencatat bahwa sistem harus mengakomodasi kedua kemungkinan itu sampai dasar normatifnya jelas
- menutup pertanyaan normatif utama pada diskusi grup berbasis `abituren` dan `Surat Keputusan` dengan mengunci multi-grup berbasis SK penetapan sah, membedakan latsarmil dari penyegaran, menetapkan lampiran SK boleh menyusul, dan menegaskan arah umum NIKC tetap dengan jalur kasus khusus melalui super admin serta histori
- membuka diskusi baru tentang mekanisme penyesuaian pangkat berbasis satu SK massal yang dapat dipakai untuk banyak personel, lalu menyelaraskan diskusi regulasi agar artefak sumber tetap satu dan bisa direuse lintas proses
