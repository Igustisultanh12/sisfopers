> Status Arsip: `Ditetapkan`
> Diarsipkan pada: 2026-07-03 14:15:56 WIB
> Keputusan final yang dihasilkan: `docs/02 Keputusan/2026.02.002 tentang Standar UI dan Design System Kctrimatra.md`
> Dokumen referensi teknis terkait: `docs/11 Referensi Teknis/2026.11.001 tentang Design Tokens Kctrimatra.md`; `docs/11 Referensi Teknis/2026.11.002 tentang Katalog Komponen UI Kctrimatra.md`
> Alasan pengarsipan: diskusi telah difinalkan dan seluruh substansi normatif utamanya dilahirkan sebagai keputusan aktif baru

# Diskusi
## tentang STANDAR UI DAN DESIGN SYSTEM KCTRIMATRA

Status: `Siap Difinalkan`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang Standar UI dan Design System Kctrimatra
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: prinsip UI global, identitas visual sistem, token desain, dan arah katalog komponen
Keputusan terkait: `docs/02 Keputusan/2026.02.002 tentang Standar UI dan Design System Kctrimatra.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `docs/02 Keputusan/2026.02.002 tentang Standar UI dan Design System Kctrimatra.md`

## Pemicu

Repo membutuhkan arah UI/design yang konsisten sebelum implementasi frontend dimulai lebih jauh. Kebutuhan ini mencakup identitas visual sistem, aturan pemakaian warna per matra, bahasa UI, dan batas komponen yang akan dipakai lintas halaman.

Karena repo masih berstatus `bootstrap` dan frontend stack resmi belum diputuskan, topik ini belum layak langsung dilahirkan sebagai keputusan final atau referensi teknis. Ia perlu dibahas dulu sebagai diskusi aktif agar prinsip yang benar-benar stabil bisa dipisahkan dari detail implementasi yang masih bisa berubah.

## Temuan Awal

### Kondisi repo yang terverifikasi

- repo masih `bootstrap`; skeleton Laravel 13 belum ada
- frontend stack resmi belum diputuskan
- belum ada keputusan domain atau keputusan UI produk yang aktif
- keputusan aktif `2026.02.001` secara eksplisit menyebut keputusan UI produk berada di luar cakupan keputusan payung governance saat ini

### Temuan substansi UI yang sudah dianalisis

- arah paling konsisten adalah satu identitas sistem utama berbasis hijau institusional yang dipakai untuk seluruh chrome aplikasi
- warna matra lebih aman dipakai secara kontekstual pada data personel atau satuan, bukan untuk mengubah tema penuh halaman
- pemisahan antara token sistem dan token matra membantu menjaga konsistensi navigasi, aksesibilitas, dan keterbacaan pada aplikasi padat data
- status keanggotaan harus dipisahkan secara visual dari identitas matra agar dua jenis informasi tidak tercampur
- di sistem ini pangkat tidak dipakai sebagai penanda matra; matra terlihat dari verifikasi atau surat keputusan
- representasi visual matra sebaiknya memakai motif ikon sederhana, bukan reproduksi lambang resmi kesatuan

### Batas yang sudah terlihat sejak awal

- karena frontend stack belum final, penamaan komponen, struktur file Blade, atau pola implementasi belum boleh diperlakukan sebagai kontrak final
- token tipografi boleh diusulkan, tetapi nama font belum perlu dikunci bila identitas tipografi proyek belum diputuskan
- warna untuk Mabes atau gabungan harus dibedakan jelas dari warna semantik error agar tidak menimbulkan ambiguitas

## Opsi yang Dipertimbangkan

### Opsi 1 - Tema penuh berubah mengikuti matra

Seluruh halaman, sidebar, tombol primer, dan chrome aplikasi berubah warna mengikuti matra anggota atau konteks data yang sedang dibuka.

Penilaian:

- plus: identitas matra terasa kuat
- minus: sistem kehilangan identitas tunggal
- minus: navigasi lintas modul terasa tidak stabil
- minus: risiko bentrok aksesibilitas dan makna warna lebih tinggi

Status evaluasi: tidak direkomendasikan

### Opsi 2 - Satu identitas sistem, warna matra kontekstual

Chrome aplikasi memakai identitas sistem tunggal. Warna matra hanya muncul pada badge, edge card, avatar ring, filter, kartu anggota, atau elemen lain yang memang merepresentasikan identitas personel atau satuan.

Penilaian:

- plus: paling konsisten secara institusional
- plus: paling aman untuk aplikasi operasional padat data
- plus: lebih mudah dijaga lintas modul dan lintas fase implementasi
- plus: membedakan bahasa sistem dari bahasa domain

Status evaluasi: kandidat utama

### Opsi 3 - Identitas sistem netral tanpa warna matra

Seluruh aplikasi memakai identitas sistem tanpa aksen matra sama sekali, kecuali teks.

Penilaian:

- plus: paling sederhana dikelola
- minus: kehilangan penanda visual domain yang justru penting pada data keanggotaan
- minus: kurang membantu pemindaian cepat di tabel, kartu profil, dan hasil pencarian

Status evaluasi: layak sebagai fallback minimal, tetapi terlalu miskin konteks bila dipakai jangka panjang

## Poin Diskusi

### 1. Identitas visual global

Repo mengadopsi identitas sistem hijau militer sebagai tema global resmi untuk shell internal, auth, tombol primer, focus ring, dan navigasi utama.

### 2. Batas pemakaian warna matra

Warna matra boleh dipakai secara kontekstual pada elemen yang memang merepresentasikan identitas personel, satuan, atau dokumen tertentu. Batas awal yang disepakati:

- boleh: badge matra, chip filter, edge kartu profil, avatar ring, kartu anggota
- tidak boleh: sidebar, topbar, tombol primer sistem, background halaman penuh, focus ring global

### 3. Bahasa domain di UI

Istilah operasional yang dibakukan untuk label, filter, status, dan form:

- gunakan istilah baku seperti `matra`, `satuan`, `pangkat`, `NRP/NIP`, `status keanggotaan`
- hindari kode teknis mentah sebagai label UI
- pangkat tidak dijadikan penanda matra di UI; identitas matra dibaca dari data verifikasi atau surat keputusan

### 4. Boundary simbol resmi

UI memakai motif ikon atau simbol sederhana sebagai bahasa visual matra, sambil menghindari reproduksi lambang resmi kesatuan sebagai dekorasi aplikasi.

### 5. Struktur token desain

Struktur token desain dibagi menjadi dua keluarga:

- token sistem untuk brand, chrome, surface, typography, spacing, shadow, dan semantik umum
- token matra untuk representasi identitas AD, AL, AU, dan bila perlu Mabes/gabungan

### 6. Status badge dan pemetaan semantik

Status keanggotaan dipisahkan dari badge matra dan dipetakan ke token semantik sistem tersendiri, misalnya:

- `Aktif` ke warna sukses
- `Purnawirawan` dan `Pensiun` ke warna netral
- `Pindah Tugas` ke warna warning
- `Non-Aktif` ke warna danger

### 7. Cakupan komponen inti

Komponen minimum yang dibutuhkan sejak fase awal:

- shell aplikasi internal dan guest/auth
- page header
- card
- button dan processing button
- input, select, textarea
- table, pagination, empty state
- alert, badge, loading overlay
- badge matra
- status badge
- filter matra

Komponen seperti avatar ring, card edge, dan kartu anggota cetak diposisikan sebagai fase lanjutan atau diskusi turunan bila scope implementasinya belum dekat.

### 8. Tingkat kekakuan naming komponen

Naming seperti `x-ui.*` dan `x-matra.*` belum dianggap kontrak final. Ia boleh dipakai sebagai arah naming awal, tetapi pembakuan naming teknis final ditetapkan setelah keputusan UI lahir dan stack frontend aktif benar-benar dipilih.

### 9. Tipografi

Proyek mengunci `IBM Plex Sans` sebagai font sans utama awal karena paling sesuai untuk karakter institusional, tegas, dan tetap nyaman untuk form, tabel, dan dashboard padat data.

### 10. Scope kartu anggota cetak

Kebutuhan cetak kartu anggota diakui sebagai scope yang mungkin dibutuhkan, tetapi desain visual dan aturan cetaknya dibahas terpisah saat scope cetak benar-benar mulai aktif.

## Hasil Diskusi

Diskusi ini sudah mengerucut dan siap difinalkan dengan arah berikut:

- satu identitas sistem hijau militer wajib dipakai untuk seluruh chrome aplikasi
- dalam kondisi tertentu, warna matra masing-masing boleh tampil secara kontekstual
- token sistem dan token matra dipisahkan secara eksplisit
- badge matra dan status keanggotaan dipisahkan sebagai dua lapisan informasi berbeda
- pangkat tidak dipakai untuk memperlihatkan matra; matra terlihat dari data verifikasi atau surat keputusan
- simbol visual matra boleh memakai motif ikon atau simbol sederhana sebagai pengganti lambang resmi
- `IBM Plex Sans` dipakai sebagai font sans utama awal
- scope cetak kartu anggota diakui mungkin dibutuhkan, tetapi desain detailnya dapat dibahas terpisah

Rincian yang sudah cukup stabil untuk menjadi turunan setelah keputusan lahir:

- komponen inti yang paling diperlukan pada fase awal adalah shell, page header, card, button, form controls, table, pagination, empty state, alert, loading overlay, badge matra, status badge, dan filter matra
- katalog token teknis dapat lahir sebagai referensi teknis turunan setelah keputusan UI final aktif
- katalog komponen teknis dapat lahir sebagai referensi teknis turunan setelah keputusan UI final aktif

## Checklist Pra-Finalisasi

- [x] identitas sistem hijau militer ditegaskan sebagai tema global
- [x] aturan warna matra kontekstual ditegaskan
- [x] posisi pangkat terhadap matra ditegaskan
- [x] batas lambang resmi dan motif sederhana ditegaskan
- [x] `IBM Plex Sans` disetujui sebagai font utama awal
- [ ] tetapkan daftar elemen yang sah memakai warna matra
- [ ] tetapkan apakah warna Mabes/gabungan memang perlu hidup sejak fase awal
- [x] tetapkan daftar komponen minimum fase awal
- [ ] pastikan tidak ada detail implementasi yang terlalu cepat dikunci sebelum keputusan final
- [x] putuskan bahwa desain kartu anggota dicatat sebagai scope turunan terpisah

## Catatan untuk AI Agent

- jangan melahirkan keputusan UI final, workplan implementasi UI, atau referensi teknis turunan selama dokumen ini belum final secara lifecycle
- jika user meminta token final, katalog komponen final, SOP implementasi, blueprint frontend, atau template UI saat diskusi ini masih aktif, tahan dulu dan catat kebutuhannya di dokumen ini
- jangan masukkan path luar repo, nama folder personal, atau asal file referensi eksternal ke dokumen aktif
- jika nanti implementasi frontend mulai lebih dulu daripada keputusan final lahir, perubahan baseline harus dicocokkan dengan `docs/01 Profil Sistem/PROJECT_STATUS.md`

## Pertanyaan Terbuka

- apakah warna Mabes/gabungan memang dibutuhkan pada fase awal, atau cukup ditunda sampai ada kebutuhan nyata
- apakah naming `x-ui.*` dan `x-matra.*` akan dipakai lintas proyek ini sebagai pola resmi setelah stack frontend aktif dipilih

Catatan:

- pertanyaan terbuka di tahap ini tidak lagi menghalangi lahirnya keputusan UI global
- sisanya dapat diturunkan sebagai rincian keputusan atau referensi teknis setelah keputusan final aktif

## Rencana Tindak Lanjut

- finalkan diskusi ini menjadi keputusan UI produk baru di `docs/02 Keputusan/`
- setelah keputusan UI aktif lahir, turunkan token desain teknis ke `docs/11 Referensi Teknis/`
- setelah keputusan UI aktif lahir, turunkan katalog komponen teknis ke `docs/11 Referensi Teknis/`
- bila implementasi UI mulai aktif dan perubahan yang dibutuhkan cukup banyak, lahirkan workplan formal di `docs/03 Rencana Kerja/`

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-03 | Dokumen diskusi dibuka untuk membahas standar UI dan design system repo sebelum implementasi frontend diformalisasi |
| 2026-07-03 | Arah utama diperjelas: identitas hijau militer wajib, warna matra kontekstual, pangkat tidak menjadi penanda matra, simbol resmi diganti motif sederhana, dan scope kartu anggota dicatat sebagai kebutuhan terpisah |
| 2026-07-03 | Diskusi dirapikan ke status siap difinalkan, `IBM Plex Sans` dikunci sebagai font utama awal, dan jalur turunan keputusan serta referensi teknis diperjelas |
| 2026-07-03 | Diskusi diarsipkan sebagai dokumen yang ditetapkan setelah melahirkan keputusan UI aktif dan dua referensi teknis turunannya |
