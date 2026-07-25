# Dokumentasi Kctrimatra

Framework acuan: Laravel 13 | PHP 8.3+ | MySQL/MariaDB | Environment lokal: Laragon

`docs/` adalah pintu masuk dokumentasi kerja untuk repository `kctrimatra`. Repo ini masih berada pada tahap bootstrap, jadi baseline dokumentasi dibuat lebih dulu agar keputusan, diskusi, dan implementasi berikutnya berjalan disiplin sejak awal.

Keputusan payung aktif untuk governance dokumen saat ini adalah `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`. Jika ada konflik antara file ini dan keputusan aktif, keputusan aktif yang berlaku.

## Mulai Dari Sini

Urutan baca minimum:

1. `docs/README.md`
2. `docs/01 Profil Sistem/PROJECT_STATUS.md`
3. `docs/01 Profil Sistem/STACK_DAN_BASELINE.md`
4. `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
5. `AGENTS.md`
6. `docs/CHANGELOG.md`

Catatan:

- jika ada konflik antara dokumen umum dan dokumen keputusan/domain, dokumen keputusan/domain yang berlaku
- karena repo masih baru, dokumen baseline harus jujur membedakan mana yang `rencana`, `siap dibuat`, dan `sudah berjalan`
- dokumen di `01 Profil Sistem` bersifat deskriptif dan baseline; ia tidak menyalip keputusan aktif sebagai norma

## Struktur Ringkas

### Lifecycle docs

- `00 Arsip/` - dokumen historis yang sudah dicabut, selesai, ditetapkan, atau hanya disimpan sebagai jejak
- `01 Profil Sistem/` - baseline sistem, status repo, arsitektur awal, dan referensi stabil yang masih berlaku
- `02 Keputusan/` - keputusan final yang mengunci arah aktif repo
- `03 Rencana Kerja/` - dokumen pelaksana turunan keputusan final
- `04 Diskusi/` - pembahasan yang belum final dan belum boleh dibaca sebagai keputusan

Alur ringkas:

- yang difinalkan adalah `04 Diskusi`; keputusan baru di `02 Keputusan` lahir sebagai hasil finalisasi diskusi, bukan karena file diskusi diubah peran
- dokumen diskusi yang berhasil difinalkan lalu dipindahkan ke `00 Arsip` dengan status selesai, timestamp `WIB`, dan rujukan keputusan
- `03 Rencana Kerja` dipindahkan ke `00 Arsip` saat implementasi selesai
- `02 Keputusan` dipindahkan ke `00 Arsip` saat dicabut dan digantikan keputusan baru
- saat membuka atau membuat diskusi baru di `04 Diskusi`, wajib audit dulu file kode dan dokumen aktif yang beririsan agar referensi terkait sudah tersedia sejak awal
- sejak awal membuat file diskusi, jangan tulis path eksternal, nama folder personal, atau asal referensi luar repo; yang boleh masuk hanya laporan temuan, hasil verifikasi, pola, dan substansi yang sudah diserap
- selama topik masih berstatus diskusi dan belum final, agent dilarang melahirkan dokumen turunan domain baru di rumah aktif lain, termasuk `02 Keputusan`, `03 Rencana Kerja`, `11 Referensi Teknis`, manual, referensi operasional, spesifikasi, blueprint, SOP, atau template implementasi
- jika user meminta manual, referensi operasional, referensi teknis, spesifikasi, blueprint, SOP, atau template implementasi saat diskusi belum final, permintaan itu wajib ditahan dulu dan dicatat sebagai kebutuhan atau scope di dokumen diskusi aktif
- jika finalisasi diskusi mengubah banyak dari keputusan lama, jangan revisi setengah jalan: finalkan diskusi dulu, siapkan seluruh aturan aktif yang masih relevan untuk keputusan pengganti, cabut keputusan lama ke arsip dengan metadata lengkap dan timestamp `WIB`, lalu lahirkan keputusan baru dan selaraskan dokumen canonical, `03 Rencana Kerja`, `AGENTS.md`, dan dokumen aktif lain yang terdampak
- jika keputusan baru memakai atau mengubah substansi dari dokumen `01 Profil Sistem` lama, agent wajib menarik seluruh clause aktif yang relevan ke keputusan baru; jangan hanya merujuk dokumen lama
- setiap batch kecil atau fase rencana kerja yang selesai sebaiknya langsung dibuat commit lokal agar progres aman dan risiko bentrok paralel berkurang
- push dilakukan setelah seluruh rencana kerja atau seluruh scope batch yang sedang dijalankan benar-benar selesai
- file di `04 Diskusi/` wajib mengikuti format `YYYY.MM.DD Diskusi tentang Judul Dokumen.md` dan tidak boleh memakai prefix status aktif
- file di `03 Rencana Kerja/` wajib mengikuti format `YYYY.MM.DD Rencana Kerja tentang Judul Dokumen.md`
- pola nama berbasis nomor urut `YYYY.03.XXXX` atau `YYYY.04.XXXX` tidak lagi dipakai untuk dokumen aktif baru di lifecycle `03` dan `04`
- file atau dokumen di luar `C:\laragon\www\kctrimatra\` tidak boleh diakses tanpa konfirmasi eksplisit user; jika scope kerja tampak menyentuh file luar repo, agent wajib berhenti dan minta konfirmasi dulu

## Workflow Lifecycle Wajib

Setiap lifecycle harus mengikuti aturan foldernya masing-masing secara disiplin. Jangan melompati tahap, jangan mencampur status hidup dokumen, dan jangan mengubah dokumen lama menjadi peran baru tanpa melahirkan file yang benar.

1. Jika topik masih dibahas, dokumen hidup di `04 Diskusi/` dan mengikuti aturan diskusi.
2. Jika diskusi sudah final, file diskusi asli wajib diarsipkan lebih dulu ke `00 Arsip/` dengan ketentuan arsip.
3. Setelah arsip diskusi selesai, jika perubahan menyentuh keputusan lama maka keputusan lama yang terdampak wajib dicabut dulu ke `00 Arsip/` dengan metadata lengkap sesuai ketentuan arsip.
4. Jika selama finalisasi ada bahasan lain yang keluar dari scope diskusi utama, bahasan itu tidak ikut dipaksa masuk ke keputusan; lahirkan dokumen diskusi baru yang berdiri sendiri.
5. Setelah diskusi selesai diarsipkan dan keputusan lama yang relevan sudah dicabut bila memang ada penggantian, barulah lahir file baru di `02 Keputusan/` dengan ketentuan keputusan.
6. Jika lahir keputusan baru atau revisi keputusan baru yang membutuhkan perubahan repo, wajib lahir file baru di `03 Rencana Kerja/` dengan ketentuan rencana kerja.
7. Jika rencana kerja selesai, file rencana kerja dipindahkan ke `00 Arsip/` dengan ketentuan arsip.
8. Dokumen turunan domain seperti referensi teknis, manual, referensi operasional, spesifikasi, blueprint, SOP, atau template implementasi hanya boleh lahir setelah ada keputusan final yang menjadi landasan atau instruksi eksplisit user yang memang meng-override urutan lifecycle.

Larangan utama:

- jangan rename file diskusi menjadi keputusan
- jangan melahirkan keputusan baru sebelum dokumen diskusi sumber diarsipkan
- jangan melahirkan dokumen turunan domain baru selama topik masih berada pada level diskusi, walau namanya terdengar ringan seperti manual, referensi, spesifikasi, atau template
- jangan melahirkan keputusan pengganti sebelum keputusan lama yang terdampak dicabut dan dipindahkan ke arsip dengan metadata lengkap
- jangan menaruh bahasan luar scope ke keputusan; pindahkan menjadi diskusi baru
- jangan memakai file keputusan sebagai checklist implementasi
- jangan membiarkan rencana kerja selesai tetap tinggal di folder aktif
- jangan mengarsipkan file tanpa memenuhi syarat arsip di `00 Arsip/README.md`
- jangan menganggap dokumen profil sistem lama sebagai target pembaruan rutin; arah defaultnya adalah `tarik, jangan poles`

## Urutan Publish Wajib

Urutan release dan sinkronisasi perubahan adalah:

1. `commit`
2. `push`
3. `deploy`

Jangan membalik urutan ini. Deploy tidak boleh dianggap selesai bila perubahan belum di-commit dan belum di-push ke remote yang menjadi jalur deploy.

### Konvensi Nama Lifecycle

| Folder | Status hidup | Format nama wajib |
| --- | --- | --- |
| `04 Diskusi/` | aktif | `YYYY.MM.DD Diskusi tentang Judul Dokumen.md` |
| `03 Rencana Kerja/` | aktif | `YYYY.MM.DD Rencana Kerja tentang Judul Dokumen.md` |
| `02 Keputusan/` | aktif | `YYYY.Kep.XXX tentang Judul Dokumen.md` |
| `11 Referensi Teknis/` | aktif | `YYYY.11.XXX tentang Judul Dokumen.md` |
| `00 Arsip/` | arsip | `[DICABUT]` / `[SELESAI]` / `[DITETAPKAN]` / `[HISTORIS]` + nama file |

Catatan:

- prefix status hanya sah di `00 Arsip/`
- untuk `03 Rencana Kerja/` dan `04 Diskusi/`, nomor urut tidak lagi dipakai; keunikan dijaga lewat tanggal dan judul yang spesifik
- file legacy dengan pola lama tidak perlu di-rename massal; migrasikan saat file disentuh lagi
- diskusi tidak boleh di-rename menjadi keputusan; keputusan harus lahir sebagai file baru

## Root `docs/`

- `README.md` - pintu masuk utama
- `CHANGELOG.md` - catatan perubahan repo lokal
- `02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md` - source of truth normatif payung untuk lifecycle dokumen, metadata, rumah dokumen, batas aksi agent, dan prinsip workplan
- `01 Profil Sistem/PROJECT_STATUS.md` - snapshot kondisi repo dan status bootstrap
- `01 Profil Sistem/STACK_DAN_BASELINE.md` - baseline stack teknis dan asumsi proyek awal
- `10 Referensi Internal/` - lampiran internal yang benar-benar perlu dipertahankan karena tidak bisa diserap memadai ke file diskusi atau keputusan

## Dokumen Penting per Topik

| Jika ingin... | Baca... |
| --- | --- |
| Memahami kondisi repo yang benar-benar berjalan | `01 Profil Sistem/PROJECT_STATUS.md` |
| Memahami baseline stack proyek | `01 Profil Sistem/STACK_DAN_BASELINE.md` |
| Membuat keputusan baru | `02 Keputusan/README.md` |
| Menulis workplan formal | `03 Rencana Kerja/README.md` lalu `03 Rencana Kerja/AGENT-INSTRUCTIONS.md` |
| Membuka diskusi baru | `04 Diskusi/README.md` |
| Memahami aturan arsip | `00 Arsip/README.md` |

## Prinsip Dokumentasi

- dokumen umum harus menyebut status aktual repo: `rencana`, `bootstrap`, `berjalan`, `parsial`, atau `historis`
- jangan mengulang isi yang sama di banyak file bila cukup dirujuk
- dokumen keputusan aktif harus jelas menimpa asumsi lama
- keputusan aktif adalah hirarki tertinggi untuk norma dokumentasi repo
- perubahan runtime atau baseline penting wajib dicatat di `docs/CHANGELOG.md`
- diskusi yang belum menghasilkan keputusan final tetap tinggal di `docs/04 Diskusi/`
- dokumen arsip hanya memakai status `[DICABUT]`, `[SELESAI]`, `[DITETAPKAN]`, atau `[HISTORIS]`

## Catatan Awal Repo

- repo ini sedang disiapkan untuk proyek baru Laravel 13
- struktur dokumentasi dibuat sebelum bootstrap kode agar governance sudah tegas sejak awal
- keputusan payung governance kini hidup di `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
- jika nanti hasil inisialisasi Laravel 13 berbeda dari baseline dokumen, perbarui `01 Profil Sistem/PROJECT_STATUS.md` dan `01 Profil Sistem/STACK_DAN_BASELINE.md` terlebih dahulu
