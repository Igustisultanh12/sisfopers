# Diskusi
## tentang RIWAYAT PENDIDIKAN DAN DIKLAT ANGGOTA

Status: `Siap Difinalkan`
> Status Arsip: `Selesai`
> Selesai pada: 2026-07-13 10:32:57 WIB
> Rujukan keputusan: `docs/02 Keputusan/2026.Kep.008 tentang Riwayat Pendidikan dan Diklat Anggota.md`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang Riwayat Pendidikan dan Diklat Anggota
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: pendidikan formal, diklat, riwayat pendidikan anggota, dokumen pendukung, dan keterkaitan ke profil anggota
Keputusan terkait: `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`, `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`, `docs/02 Keputusan/2026.Kep.006 tentang Struktur Organisasi dan Grup Angkatan.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `docs/02 Keputusan/2026.Kep.008 tentang Riwayat Pendidikan dan Diklat Anggota.md`

## Pemicu

Bahan user memuat dua rumpun data pendidikan: pendidikan formal dan diklat. Karena keduanya dapat memengaruhi profil anggota dan domain lain seperti pangkat, pembahasannya perlu dipisah dari master data anggota umum.

## Temuan Awal

- belum ada keputusan final tentang apakah ijazah utama anggota sama dengan lampiran riwayat pendidikan
- ada potensi relasi ke dokumen pendukung
- domain ini kemungkinan bukan fondasi MVP pertama, tetapi sudah penting sebagai rumah pembahasan agar tidak lupa

## Poin Diskusi

### 1. Pendidikan formal dan diklat perlu dibedakan

Perlu diputuskan apakah keduanya hidup sebagai domain yang terpisah tetapi beririsan pada profil anggota.

### 2. Posisi dokumen pendukung pendidikan

Perlu dibahas apakah ijazah atau dokumen pendidikan:

- wajib
- opsional
- berbeda antara klaim akun dan riwayat pendidikan

### 3. Apakah riwayat pendidikan menjadi scope awal

Fitur ini mungkin tidak masuk MVP pertama, tetapi topiknya perlu ditahan rapi di diskusi.

### 4. Suffix gelar untuk format nama

Jika format nama lengkap nantinya memakai suffix gelar otomatis, dokumen ini menjadi rumah tunggal untuk:

- gelar akademik formal
- aturan urutan gelar
- master data pendidikan yang menghasilkan suffix

Prefix pangkat dan varian `(W)` tidak dibahas di dokumen ini agar tidak bercampur dengan domain pangkat.

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Pendidikan formal vs diklat | Perlu dipisah; **Diklat/Latsarmil = bukti keanggotaan (rumah #2 T3)**, Pendidikan formal = sumber gelar (rumah #9) | Clear (lihat T1) |
| 2 | Dokumen pendukung pendidikan | **Dijawab:** ijazah akademik = bukti gelar (edit profil mau gelar -> wajib; SK ada gelar -> tak perlu), selaras #5 T2 | Clear (lihat T3) |
| 3 | Scope awal riwayat pendidikan | **Dijawab:** masuk tujuan 1 (butuh untuk suffix gelar #5 + import SK #8); tabel `riwayat_pendidikan` one-to-many | Clear (lihat T1) |
| 4 | Suffix gelar untuk format nama | **Dijawab:** #9 = rumah tunggal sumber suffix gelar; #5 hanya rumus display, tidak duplikasi | Clear (lihat T1/T5) |

## Catatan untuk AI Agent

- jangan lahirkan master data atau schema pendidikan sebelum user mematangkan scope domain ini
- kaitkan diskusi ini ke pangkat dan dokumen bila nanti final
- suffix gelar untuk nama lengkap adalah wilayah dokumen ini, bukan dokumen pangkat
- **PEMISAHAN WAJIB:** Ijazah Latsarmil/Diklat (#2 T3, bukti keanggotaan) ≠ Ijazah Akademik (#9, sumber gelar). Jangan campur.

## Pertanyaan Terbuka

- apakah pendidikan formal dan diklat sama-sama tampil di profil -> **Dijawab:** tampil, tapi dipisah; diklat/latsarmil di rumah #2 T3 (keanggotaan), pendidikan formal di #9 (gelar).
- apakah unggah ijazah pada riwayat pendidikan wajib atau opsional -> **Dijawab:** kondisional (#5 T2): edit profil mau nyantumkan gelar -> wajib; SK penetapan sudah ada gelar -> tak perlu.
- apakah domain ini masuk fase awal atau lanjutan -> **Dijawab:** fase awal (tujuan 1), karena dibutuhkan #5 (suffix) & #8 (parse SK).

## Rencana Tindak Lanjut

- [x] verifikasi kondisi file (Personel, migration, Kep.004)
- [x] pisahkan ijazah latsarmil (#2 T3) vs akademik (#9)
- [x] menjawab poin 1-4 + pertanyaan terbuka
- [ ] finalisasi #9 menunggu #8 (import SK -> isi riwayat pendidikan) & #12 (repositori) **matang** (keduanya SUDAH ADA; lihat T4/T8, bukan blocker kelahiran)

## Temuan Analisis (2026-07-13, fase bertahap menuju tujuan 1)

Berdasarkan: baca `Kep.004`, `Personel.php`, migration `personels`, sampel SK (halaman 4 KEP/1717/M/XII/2025),
dan diskusi #2 T3 / #5 T2 / #8 T2. Tanpa ubah kode (Kep.001 klausul 24). Masih `04 Diskusi`.

### T1. Riwayat pendidikan = tabel tersendiri (one-to-many)
- `personels.education_level`+`study_program` hanya muat 1 jenjang -> tidak cukup untuk orang S1+S2.
- Usulkan tabel `riwayat_pendidikan` (FK personel_id): `jenjang` (enum), `program_studi` (free-text),
  `nama_institusi` (opsional), `tahun_lulus` (opsional), `file_ijazah_path`, `jenis` (AKADEMIK/LATSARMIL),
  `verified_at`, `verified_by`.
- Suffix gelar (#5) disusun dari entri **AKADEMIK**, urut S1->S2->S3, dipisah koma+spasi.

### T2. Validasi jenjang (enum) + program bebas
- `jenjang` enum: `SD`, `SMP`, `SMA`/`SMK`, `D3`, `D4`, `S1`, `S2`, `S3`, `PROFESI`.
- `program_studi` free-text (selaras #5: sistem TIDAK validasi benar/salah; calon yang tulis, ijazah = bukti).
- TIDAK wajib `master_pendidikan` -> hindari over-engineering.

### T3. Verifikasi & alur upload (selaras #5 T2)
- Upload ijazah akademik: calon saat registrasi (jika tak ada di SK) ATAU edit profil mau nyantumkan gelar.
- Verifikasi: koordinator (dokumen hilang) / admin (sisanya) -> isi `verified_at`+`verified_by`
  (field sudah ada di `personels`, dipindah ke `riwayat_pendidikan` saat tabel lahir).
- Ijazah Latsarmil (bukti keanggotaan) diinput lewat #2 T3, masuk entri `jenis=LATSARMIL` terpisah.

### T4. Relasi & dependency
- **#5 (Format Nama/Gelar): MATANG** -> #9 = sumber suffix gelar; #5 tidak duplikasi rumus. ✅
- **#2 T3: MATANG** -> Ijazah Latsarmil (keanggotaan) dipisah dari akademik. ✅
- **#8 (OCR/Import): BELUM FINAL** -> parsing kolom Pendidikan SK -> isi `riwayat_pendidikan` menunggu #8 lahir.
- **#12 (Repositori): SUDAH ADA** (`2026.07.03 Diskusi tentang Repositori Dokumen...`, masih `04 Diskusi`).
  #9 finalisasi menunggu #12 **matang** (istilah surat/dokumen, relasi many-to-many, dokumen aktif belum final di #12),
  tapi #12 sudah lahir -> bukan blocker keberadaan. (Koreksi: revisi lalu salah bilang "#12 belum ada".)
- `Kep.004` klausul 13/24: TMT penetapan ijazah = riwayat pangkat (bukan angkatan); latsarmil = hak grup baru.

### T5. Kondisi Kode Aktual (snapshot, verifikasi statis)
- `Personel` fillable: `file_ijazah_path`, `education_level`(50), `study_program`(150),
  `education_verified_at`, `education_verified_by` -> sudah ada di level personel (single row).
- **TIDAK ADA** tabel `riwayat_pendidikan` / `master_pendidikan` -> pekerjaan baru saat implementasi.
- `RegisterPersonelRequest` **belum** mewajibkan upload ijazah akademik -> perlu tambah field saat #9 implementasi.
- `education_verified_*` sudah ada -> tinggal alur pengisian.
### T6. Tiga rumpun pendidikan (koreksi: pisahkan diklat dari militer)
`jenis` pada `riwayat_pendidikan` memiliki **3 nilai**, bukan 2:
- **AKADEMIK** = pendidikan umum/formal (SD s.d. S3/D4). Sumber **suffix gelar** (#5).
  Field penting: `jenjang`, `program_studi`, `file_ijazah_path` (bukti gelar).
  **Gelar profesi** (`dr.`/`Ir.`/`Apt.` dll) lahir dari **pendidikan profesi** (jenis AKADEMIK, jenjang=PROFESI)
  atau pendidikan umum, **BUKAN** dari DIKLAT. Diklat tidak menghasilkan gelar akademik/profesi.
- **DIKLAT** = kursus/pelatihan/sertifikasi umum (bukan ijazah, tidak ber-gelar). Field penting:
  `nama_diklat`, `penyelenggara`, `tanggal_mulai`, `tanggal_selesai`, `file_sertifikat_path`, `keterangan`.
  TIDAK menghasilkan suffix gelar.
  **Penting:** ikut diklat (mis. "diklat bahasa Inggris") BELUM TENTU punya sertifikat. Diklat ≠ sertifikat.
  Sertifikat hasil **ujian** (TOEFL/IELTS/TOEIC/HSK/JLPT) = **Soft Skill (#10)**, BUKAN entri DIKLAT di #9.
  Jadi: DIKLAT (ikut pelatihan) dan Sertifikat Bahasa (Soft Skill hasil ujian) adalah **DUA HAL BERBEDA**.
- **MILITER** = pendidikan dasar militer / Latsarmil. Bukti **keanggotaan Komcad** & dasar hak grup baru
  (Kep.004 klausul 24; arsip 2026.07.11:181 "mekanisme tambah pendidikan militer"). Field penting:
  `nama_pendidikan`, `lama`/`angka_kredit`, `file_ijazah_latsarmil_path`, `nomor_ijazah` (lihat T10).

> Catatan: draf sebelumnya (T1) menyatukan DIKLAT ke LATSARMIL -> **salah**. Diklat (sertifikat, tak ber-gelar,
> tak bukti keanggotaan) != pendidikan militer (ijazah latsarmil, bukti keanggotaan). Dipisah tegas di sini.
> Arahan user 2026-07-13: gelar profesi dari **pendidikan profesi/umum**, BUKAN diklat.
> Arahan user 2026-07-13: **Ijazah Latsarmil ≠ SK Penetapan** (lihat T10). Diklat bahasa ≠ sertifikat bahasa (#10).

### T7. Mekanisme Tambah / Ubah / Hapus (CRUD) riwayat pendidikan
**Belum ada** di kode maupun diskusi aktif -> dirumuskan di sini (tanpa ubah kode, Kep.001 klausul 24):
- **Input data:** HANYA **personel itu sendiri** (atau Super Admin atas nama personel) yang **input** data
  pendidikan. **Koordinator TIDAK input data pendidikan** -> koordinator hanya **memverifikasi** (selaras
  arsip 2026.07.03:124 "koordinator ubah data terbatas, harus diverifikasi super admin"; #2 T5).
- **Tambah:** personel input **draft** entri (akademik/diklat/militer) lewat profil; butuh `file_*` sbg bukti
  -> wajib upload. Verifikasi: koordinator/Admin (tergantung batas visibilitas Kep.006/2026.11.004) -> isi
  `verified_at`+`verified_by`. MILITER (ijazah latsarmil): **personel unggah sendiri** (dokumen individu 1 nama,
  lihat T10), tidak perlu SK bersama. Saat #8 import SK, kolom Pendidikan -> auto-isi entri AKADEMIK (lihat T12).
- **Ubah:** hanya data **belum terkunci** (belum verified) ATAU via ajuan koreksi (arsip 2026.07.09:564).
  Entri terverifikasi butuh alur koreksi, bukan edit langsung. Jika entri AKADEMIK diubah/hapus, **suffix
  gelar di #5 di-recompute** otomatis.
- **Hapus:** **soft-delete** (`deleted_at`) agar audit trail terjaga (arsip 2026.07.09:566). Tidak hard-delete.
- **Field yang masih kurang** (butuh migration baru): `jenis`(enum 3), `nama_institusi`/`penyelenggara`,
  `tanggal_mulai`/`tanggal_selesai`, `keterangan`, `nomor_ijazah`, `sk_penetapan_id` (nullable, hanya untuk
  entri yg terkait SK Penetapan; nullable karena ijazah latsarmil TIDAK pakai SK), `deleted_at`, FK `personel_id`.
  `education_*` di `personels` (single-row) akan **digantikan** tabel ini saat implementasi.
  **TIDAK PERLU** `berlaku_sampai`/`expires_at` (pendidikan/ijazah tak kadaluarsa; sertifikat kadaluarsa
  ditangani di #10 Soft Skill, bukan #9).

### T9. Visibilitas data pendidikan (hak akses lihat)
- **Super Admin**: bisa lihat **detail semua** data personel termasuk seluruh riwayat pendidikan.
- **Koordinator**: **terbatas pada biodata umum** anggotanya (TIDAK bisa lihat detail pendidikan/sertifikat
  orang lain kecuali yang harus diverifikasi). Aturan visibilitas terperinci di `Kep.006` + `2026.11.004`
  (turunan hak multi-grup). #9 hanya mencatat batas ini, tidak menduplikasi.
- **Personel**: hanya lihat/edit **riwayat pendidikan diri sendiri**.

### T10. Ijazah Latsarmil (personal) vs SK Penetapan (shared) — KOREKSI
**Dua dokumen BERBEDA, jangan campur:**
- **Ijazah Latsarmil** (contoh: `ijazah kc.webp`, nomor 325 VIII/KOMCAD/2022, nama Miftahul Royan, NIKC
  23000026309200012): dokumen **INDIVIDU 1 nama**, berisi nama/pangkat/NIKC/tanggal lulus. **Ranah personel**,
  **personel yang unggah sendiri** sebagai bukti lulus Latsarmil. Masuk entri `jenis=MILITER` di #9
  (satu personel satu ijazah). **TIDAK pakai `sk_penetapan_id`** (karena ini ijazah, bukan SK bersama).
- **SK Penetapan** (contoh: KEP/1717/M/XII/2025, nominatif ratusan nama): dokumen **SHARED multi-nama**.
  - Diunggah **Super Admin** -> langsung dipakai sistem (cocokkan semua nama ke `entries`, proses penyesuaian
    pangkat, lahirkan Grup Angkatan). Arsip 2026.07.12 Penyesuaian Pangkat: admin unggah SK massal -> cocokkan
    otomatis; softcopy dipakai ulang personel lain tak wajib unggah ulang.
  - Diunggah **personel** -> masuk **folder `tmp`** dulu (BELUM dipakai sistem), nunggu admin verifikasi
    dasar pembentukan/penautan grup (#2 T5 jalur sementara). Setelah diverifikasi admin, baru diproses.
- **Kesimpulan:** Ijazah Latsarmil (personal, personel unggah) ≠ SK Penetapan (shared, Super Admin unggah
  langsung / personel unggah -> tmp). Tidak ada "SK militer bersama multi-personel" untuk ijazah latsarmil.

### T11. Relasi ke #10 Soft Skill (sertifikat bahasa vs diklat bahasa)
- **Diklat bahasa** (ikut pelatihan) = entri DIKLAT di #9 (T6). Belum tentu punya sertifikat.
- **Sertifikat bahasa** (hasil ujian TOEFL/IELTS/TOEIC/HSK/JLPT) = **Soft Skill (#10)**, bukan #9.
  #10 sudah mengunci: `expires_at` opsional, status ACTIVE/EXPIRED/REVOKED, `verified_by` = Super Admin.
- Dua hal berbeda: diklat (proses) ≠ sertifikat (hasil ujian). #9 **tidak menampung** sertifikat bahasa.

### T12. Kontrak parse dari #8 (format "S2 Manajemen" -> jenjang/program)
Agar #8 tidak nebak saat import SK, #9 mengunci **kontrak minimal**:
- Pola "`<JENJANG> <PROGRAM>`" -> `jenjang` = token pertama (SD/SMP/SMA/D3/D4/S1/S2/S3/PROFESI),
  `program_studi` = sisa teks (mis. "S2 Manajemen" -> jenjang=S2, program=Manajemen;
  "S1 Agroteknologi" -> jenjang=S1, program=Agroteknosi; "S.Pd." di nama = suffix, bukan jenjang).
- Jika tak ter-parse -> entri `jenjang=LAIN` + `program_studi`=teks mentah, flag butuh review admin.
- Kontrak ini dipakai #8 saat mengisi entri AKADEMIK dari kolom Pendidikan SK.

### T13. Aturan anti-duplikat (unique constraint)
- Entri pendidikan tidak boleh ganda. Usulkan unique (soft, cek sebelum insert):
  `(personel_id, jenis, jenjang, program_studi, tahun_lulus)` untuk AKADEMIK/DIKLAT;
  `(personel_id, nomor_ijazah)` untuk MILITER (ijazah latsarmil unik per nomor).
- Mencegah personel upload ijazah S1 dua kali atau SK sama ter-parse ganda oleh #8.

### T8. Relasi antar dokumen & file (cross-check 2026-07-13)
- **#9 (ini) <-> #5 (Format Nama/Gelar):** #9 = sumber suffix gelar (entri AKADEMIK); #5 hanya rumus display.
  #5 T1/T2 tidak duplikasi struktur pendidikan. ✅
- **#9 <-> #2 T3/T5:** Ijazah Latsarmil (jenis=MILITER) = bukti keanggotaan di cabang registrasi (#2 T3);
  personel unggah ijazah latsarmil sendiri (T10); koordinator hanya verifikasi (T7/T9). ✅
- **#9 <-> #8 (OCR/Import):** parse kolom Pendidikan SK -> isi `riwayat_pendidikan` (jenis=AKADEMIK) pakai
  kontrak T12. #8 masih `04 Diskusi` (belum final) -> #9 menunggu #8 matang, tapi #8 SUDAH ADA. ⏳
- **#9 <-> #12 (Repositori Dokumen):** #12 SUDAH ADA (`2026.07.03 Diskusi tentang Repositori Dokumen...`),
  masih `04 Diskusi`. File ijazah/sertifikat/latsarmil disimpan di repositori (#12). **#12 sudah lahir**
  -> bukan blocker keberadaan, tapi #9 finalisasi menunggu #12 matang (istilah surat/dokumen, relasi
  many-to-many, dokumen aktif belum final di #12). ✅ (koreksi: revisi lalu salah bilang "#12 belum ada")
- **#9 <-> #10 (Soft Skill):** sertifikat bahasa = #10, diklat bahasa = #9 DIKLAT (T6/T11). Dua hal beda. ✅
- **#9 <-> Arsip 2026.07.11:181/190:** "mekanisme tambah pendidikan militer" + "tidak otomatis naikkan pangkat"
  -> diadopsi di T6/T7 (jenis=MILITER, hak grup & pangkat diajukan terpisah). ✅
- **#9 <-> Arsip 2026.07.12 Penyesuaian Pangkat:** SK Penetapan diunggah Super Admin -> langsung dipakai /
  cocokkan semua nama; personel unggah -> folder `tmp` (T10). ✅
- **#9 <-> Arsip 2026.07.09:561/566:** "penyesuaian ijazah -> dicatat di riwayat pendidikan", "NIKC permanen,
  perubahan dicatat di riwayat terpisah" -> prinsip dasar tabel `riwayat_pendidikan` & soft-delete. ✅
- **#9 <-> Kep.004 klausul 13/24:** TMT penetapan ijazah = riwayat pangkat (bukan angkatan); latsarmil = hak grup baru. ✅
- **Kode saat ini:** TIDAK ADA `EducationController`/route pendidikan (search `app/Http` = 0 match) ->
  CRUD pendidikan = pekerjaan implementasi baru seluruhnya. `Personel` hanya punya `education_*` single-row. ⚠️

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-13 | FINALISASI: diskusi dinyatakan final oleh user; status -> `Siap Difinalkan`; akan diarsipkan `[SELESAI]` & melahirkan `2026.Kep.008 tentang Riwayat Pendidikan dan Diklat Anggota`. Tanpa sentuh kode |
| 2026-07-13 | KONSISTENSI INTERNAL: Rencana Tindak Lanjut baris 80 "menunggu #8/#12 lahir" -> "matang" (selaras T4/T8). Tanpa sentuh kode |
| 2026-07-13 | AUDIT FIX B.7: field `«redacted:sk_…»`->`sk_penetapan_id` (nullable, entri terkait SK Penetapan). Tanpa sentuh kode |
| 2026-07-13 | Bersihkan metadata pra-finalisasi: isi `Keputusan terkait` = Kep.004 + Kep.005 + Kep.006 (sebelumnya kosong). Cabut "menunggu #8/#12 lahir" -> "menunggu #8/#12 matang" (sudah ada). Tanpa sentuh kode |
| 2026-07-13 | KOREKSI BESAR: T10 pisahkan Ijazah Latsarmil (INDIVIDU 1 nama, personel unggah sendiri, contoh ijazah kc.webp) vs SK Penetapan (SHARED multi-nama, Super Admin unggah langsung / personel unggah -> folder tmp, arsip 2026.07.12). T6: diklat bahasa ≠ sertifikat bahasa (#10, hasil ujian). T11 perjelas. T12 kontrak parse "S2 Manajemen"->jenjang/program (agar #8 tak nebak). T13 unique constraint anti-duplikat. T4/T8: #12 SUDAH ADA (bukan blocker). Cabut kesalahan "#12 belum ada". Tanpa s |
| 2026-07-13 | Koreksi arahan user: T6 gelar profesi dr./Ir./Apt. dari pendidikan PROFESI/UMUM (bukan diklat); DIKLAT tak ber-gelar. T7 koordinator HANYA verifikasi (tidak input); Super Admin/Personel yg input; tak perlu expires_at (kadaluarsa di #10). T9 visibilitas: Super Admin lihat semua, koordinator hanya biodata umum (Kep.006/2026.11.004), personel diri sendiri. T10 SK militer (Penyegaran/Latsarmil) dokumen bersama multi-personel (ranah Super Admin); pendidikan umum/diklat personal. T11 sertifikat bahasa = #10 Soft Skill, bukan #9. Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-13 | Perluas #9: T6 (tiga rumpun AKADEMIK/DIKLAT/MILITER; koreksi draf sebelumnya yang menyatukan diklat ke latsarmil -> salah), T7 (mekanisme CRUD tambah/ubah/hapus: draft personel, verifikasi koordinator/admin, soft-delete, field yang kurang), T8 (relasi lintas #2/#5/#8/#12 + arsip 2026.07.11:181/190 & 2026.07.09:561/566 + Kep.004). Konfirmasi kode TIDAK ADA EducationController/route -> CRUD = pekerjaan baru. Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-13 | Matangkan #9: pemisahan Ijazah Latsarmil/Diklat (#2 T3, bukti keanggotaan) vs Ijazah Akademik (#9, sumber gelar). T1-T5: tabel `riwayat_pendidikan` one-to-many, enum jenjang + program bebas, verifikasi koordinator/admin, relasi #2/#5/#8/#12, snapshot kondisi file (Personel sudah punya education_*, belum ada tabel riwayat_pendidikan). Catat #8 & #12 = blocker finalisasi. Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-03 | Dokumen dibuat untuk menampung pembahasan riwayat pendidikan dan diklat anggota |
