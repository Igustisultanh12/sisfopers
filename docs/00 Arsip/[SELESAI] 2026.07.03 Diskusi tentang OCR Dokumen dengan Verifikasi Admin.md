# Diskusi
## tentang OCR DOKUMEN DENGAN VERIFIKASI ADMIN

Status: `Siap Difinalkan`
> Status Arsip: `Selesai`
> Selesai pada: 2026-07-13 10:32:57 WIB
> Rujukan keputusan: `docs/02 Keputusan/2026.Kep.010 tentang OCR Dokumen dengan Verifikasi Admin.md`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang OCR Dokumen dengan Verifikasi Admin
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: OCR, ekstraksi teks, auto-match anggota, confidence score, verifikasi admin, dan audit hasil OCR
Keputusan terkait: -
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `docs/02 Keputusan/2026.Kep.010 tentang OCR Dokumen dengan Verifikasi Admin.md`

## Pemicu

Bahan user memuat kebutuhan OCR untuk membantu membaca dokumen yang berisi banyak nama anggota. Karena fitur ini melibatkan otomasi, verifikasi manusia, dan potensi salah cocok, topiknya perlu dipisah dari repositori dokumen umum.

## Temuan Awal

- OCR adalah fitur bantu, bukan fondasi minimum website
- domain ini bergantung pada keputusan tentang anggota dan dokumen
- bahan user menekankan adanya verifikasi admin sebelum hasil disimpan

## Poin Diskusi

### 1. Posisi OCR sebagai bantuan, bukan otoritas

Perlu ditegaskan apakah OCR hanya membantu percepatan input dan tidak pernah menjadi keputusan final tanpa verifikasi admin.

### 2. Jenis dokumen yang layak diproses OCR

Tidak semua dokumen mungkin perlu OCR. Scope awal perlu dibatasi.

### 3. Model verifikasi hasil OCR

Perlu dibahas:

- bagaimana hasil match ditampilkan
- apakah admin boleh koreksi, hapus, atau tambah nama
- apakah perlu confidence score

### 4. Normalisasi nama sebelum pencocokan

Jika OCR membaca nama yang mengandung prefix pangkat atau suffix gelar, normalisasi nama masuk ke rumah diskusi ini:

- hapus prefix pangkat sebelum pencocokan
- hapus suffix gelar sebelum pencocokan
- simpan hasil pencocokan yang sudah diverifikasi admin

Bagian ini bukan domain pangkat atau pendidikan, melainkan domain OCR dan verifikasi hasil.

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | OCR sebagai fitur bantu | Sudah cukup jelas dari bahan user | Clear |
| 2 | Scope jenis dokumen OCR | **Dijawab:** KTP/KTA/Ijazah (OCR gambar, MVP); SK massal = Structured Import (bukan OCR) | Clear (lihat T4) |
| 3 | Verifikasi admin | **Dijawab:** semua hasil auto-match wajib verifikasi admin/koordinator; checkpoint wajib (rujuk #2 T5) | Clear (lihat T4) |
| 4 | Audit hasil OCR | **Dijawab:** log tiap match/correction; flag confidence rendah -> review (rujuk T4 + arsip) | Clear (lihat T4) |
| 5 | Normalisasi nama sebelum match | **Dijawab:** strip prefix pangkat + suffix gelar -> reuse `display_pangkat` (#5), bukan domain OCR | Clear (lihat T1/T5) |

## Catatan untuk AI Agent

- jangan turunkan OCR menjadi referensi teknis, service, API, atau library sebelum keputusan domain OCR lahir
- OCR boleh ditunda dari MVP jika fondasi dokumen dan anggota belum stabil
- normalisasi prefix pangkat dan suffix gelar untuk matching nama dibahas di sini, bukan di dokumen pangkat atau pendidikan
- #8 **menunggu #9 & #12 MATANG** (keduanya sudah ada sebagai file diskusi, tinggal diselaraskan). Finalisasi #8 ditahan sampai #9 (ijazah) & #12 (repositori) matang.

## Pertanyaan Terbuka

- dokumen jenis apa yang masuk scope OCR awal -> **Dijawab:** KTP/KTA/Ijazah (OCR) + SK (structured import). Lihat T4.
- apakah OCR termasuk MVP awal atau fase lanjutan -> **Dijawab:** OCR KTP/KTA/Ijazah = MVP; SK import = fase lanjutan via queue (selaras arsip Penyesuaian Pangkat). Lihat T4.
- apakah threshold confidence perlu dibahas di keputusan atau cukup di referensi teknis nantinya -> **Dijawab:** cukup di referensi teknis; hanya relevan untuk OCR gambar (KTP/KTA/Ijazah), tidak untuk SK teks. Lihat T4.

## Rencana Tindak Lanjut

- [x] menurunkan sampel riil SK + field-mapping (#8 T1/T2)
- [x] verifikasi parse NIKC vs Kep.003 (T3)
- [x] menjawab Q1-Q3 (T4)
- [x] memetakan relasi #2/#5/#9/#12 (T5)
- [ ] **finalisasi #8 ditahan** sampai #9 (ijazah) & #12 (repositori) **matang** (keduanya SUDAH ADA sebagai file diskusi; T5 sudah akui, lihat baris 68)

## Temuan Analisis (2026-07-13, fase bertahap menuju tujuan 1)

Berdasarkan: baca `Kep.003` (struktur NIKC), sampel riil halaman 4 SK KEP/1717/M/XII/2025, kode `Personel.php`/`NikcFormatRule`/`RegisterPersonelRequest`/`SuratKeputusanController`, dan arsip Penyesuaian Pangkat. Tanpa ubah kode (`Kep.001` klausul 24). Masih `04 Diskusi`.

### T1. Sampel riil struktur SK (halaman 4, KEP/1717/M/XII/2025)
Bukan tabel grid, melainkan **label + value per blok**, dengan header kolom di BAWAH daftar:
```
URUT | BAG | 1 2 3 4 5 6 7 8
PERWIRA
1 | 1 | Ervin Yoga Pratama, S.Kom, M.M., Gr. | Sragen | 20-06-2000 | Letnan Dua | Perwira Komcad | Kodam IV/Diponegoro | 11000001306200014 | S2 Manajemen | 20 November 2025
...
NOMINATIF ANGGOTA KOMPONEN CADANGAN REGULER / MATRA DARAT TA. 2025
NO | NAMA, TEMPAT DAN TANGGAL LAHIR | PANGKAT DAN JABATAN | NOMOR INDUK KOMCAD | PENDIDIKAN | TMT PENETAPAN | KET
7. Aang Ganda Saputra, S.H….
```
**8 kolom sebenarnya:** ① No urut ② Nama **+ suffix gelar** (`S.Kom, M.M., Gr.`, `S.Pd.`, `S.H.`) ③ Tempat lahir ④ Tgl lahir (DD-MM-YYYY) ⑤ Pangkat (`Letnan Dua`) ⑥ Jabatan (`Perwira Komcad`) ⑦ Kodam/Matra ⑧ **NIKC (17 digit)** + Pendidikan + TMT.
~6 baris/halaman × 264 halaman ≈ **ratusan–ribuan entri** → konfirmasi butuh queue bertahap.

### T2. Field mapping dari SK -> schema (selaras Kep.003 & #2 T2)
| Kolom SK | Field target | Catatan |
| --- | --- | --- |
| NIKC (17 digit) | `personels.nikc` / `entries.nikc` | Validasi `NikcFormatRule` (sudah ada, pas dengan Kep.003 klausul 3). |
| Nama (tanpa gelar) | `full_name` | Harus **strip suffix gelar** dulu (rujuk #5). |
| Suffix gelar (`S.Kom, M.M.`) | tidak disimpan di `full_name` | Sumber `education_level`/`study_program` (#9). #5 T2: gelar sudah di SK -> **tidak wajib upload ijazah** untuk entri ini. |
| Tempat lahir | `pob` | **SUDAH ada** di `personels` (baris 38). |
| Tgl lahir (DD-MM-YYYY) | `dob` | **SUDAH ada** (`personels.dob`, date). |
| Pangkat (`Letnan Dua`) | `pangkat` | `Personel::normalizeRankLabel` + `display_pangkat` (sudah strip `(W)`). |
| Jabatan (`Perwira Komcad`) | `grup_angkatan`/role context | bukan role login; pemetaan ke Grup Angkatan (#2 T6, Kep.006). |
| Kodam/Matra | `matra` (Darat/Laut/Udara) | `Personel::normalizeMatra`. |
| Pendidikan (`S2 Manajemen`) | `education_level`+`study_program` | sudah ada di `personels`. |
| TMT Penetapan | `tmt_penetapan` | sudah ada; `angkatan` otomatis = tahun TMT (booted observer). |

### T3. Parse NIKC (VERIFIKASI ULANG, sudah selaras Kep.003)
`11000001306200014` = `1`(Perwira) `1`(Darat) `0000013`(reg) `06`(bln) `2000`(thn) `14`(Jateng).
- `NikcFormatRule` (baris 31-36) memotong **persis sama** -> kode SUDAH konsisten Kep.003 klausul 3.
- **Cross-validation otomatis**: NIKC mengembed bulan+tahun lahir (digit 10-15) & provinsi Latsarmil (16-17). Parser SK bisa derive DOB & provinsi dari NIKC, lalu **cocokkan dengan teks SK** ("Sragen, 20-06-2000"). Tidak cocok -> flag "perlu review admin".
- **Gender tidak ada di NIKC** & tak tertulis di sampel -> derive dari `(W)` di pangkat, atau default `Pria`; `gender` enum `Pria`/`Wanita` (`Personel.php:40`).

### T4. Keputusan OCR (#8) — menjawab Q1-Q3 user
- **Q1. Scope OCR MVP:** **KTP / KTA / Ijazah** (scan gambar, 1-2 hlm, ringan, async). **SK massal = Structured Import** (bukan OCR) karena SK = PDF berlapis teks + struktur label/value.
- **Q2. SK ribuan nama:** **Structured Import + Queue bertahap** (1 Job per PDF, proses per-halaman/chunk, parser per-format SK posisional). **TIDAK sinkron** (hindari timeout/memori).
- **Q3. Threshold & model:** Confidence score **hanya untuk OCR gambar** (KTP/KTA/Ijazah), cukup di referensi teknis. **Semua hasil auto-match -> wajib verifikasi admin/koordinator** (#2 T5). Checkpoint verifikasi wajib (arsip Penyesuaian Pangkat).
- Posisi OCR = bantuan, **bukan otoritas**.

### T5. Relasi & dependency (WAJIB dicatat)
- **#5 (Format Nama/Gelar): MATANG** -> #8 reuse `display_pangkat` + aturan strip gelar sebelum match. ✅
- **#2 (Akun/Verifikasi): MATANG** -> verifikasi admin/koordinator, gating `is_active`. ✅
- **#9 (Riwayat Pendidikan/Ijazah): SUDAH ADA** (`2026.07.03 Diskusi tentang Riwayat Pendidikan dan Diklat Anggota.md`, `04 Diskusi`) -> aturan "ijazah kondisional" (#5 T2) & kolom `education_level`/`study_program`/`file_ijazah_path` dikunci di sana. ✅
- **#12 (Repositori Dokumen): SUDAH ADA** (`2026.07.03 Diskusi tentang Repositori Dokumen dan Relasi Dokumen Anggota.md`, `04 Diskusi`) -> #8 butuh #12 untuk simpan SK + lampiran + audit. Finalisasi #8 menunggu #12 **matang** (bukan lahir). ✅
- Arsip `[DITETAPKAN] 2026.07.12 Penyesuaian Pangkat` baris 181/198: "OCR/import terstruktur = fase berikutnya" -> draft #8 selaras (OCR KTP/KTA/Ijazah MVP, SK import fase lanjut via queue).

### T6. Kondisi Kode Aktual (snapshot, verifikasi statis)
- `AuthController:152/181/288/293` **sudah simpan** `ktp_document` (`personel/documents`) & `sk_document` (`surat-keputusan`). Fondasi upload ada.
- **TIDAK ADA** library OCR / PDF-text parser di `composer.json`/`package.json` -> OCR/import = pekerjaan BARU (belum ada migration untuk hasil OCR/queue).
- `surat_keputusan_personel_entries` hanya `nikc|full_name|target_pangkat` -> perlu kolom `pob`/`dob`/`pendidikan`/`jabatan`/`kodam` agar cross-check SK jalan (selaras #2 T2).
- `Personel.php` `two_factor_*` masih di fillable/casts/hidden -> drift MFA (lihat #2).

## Kondisi Kode Aktual (snapshot 2026-07-13, verifikasi statis)

> Merekap keadaan file SAAT INI agar implementasi #8 mudah dipetakan. Verifikasi STATIS (baca file); runtime tidak diuji (Laragon/MySQL mati).

- Upload dokumen: `AuthController` simpan `ktp_document` + `sk_document` (sudah jalan); path `personel/documents` & `surat-keputusan`.
- `Personel` fillable punya `ktp_document`, `file_ijazah_path`, `education_level`, `study_program`, `pob`, `dob` -> sebagian besar field SK sudah ada di level personel.
- `NikcFormatRule` validasi 17 digit persis Kep.003 klausul 3 (digit1=kelompok, digit2=matra, reg, bln, thn, prov).
- `Personel::display_pangkat` (accessor) sudah strip `(W)` + tambah dari gender -> engine normalisasi pangkat untuk #8 reuse.
- **Gap:** tidak ada OCR/parser library; `entries` belum punya `pob`/`dob`/pendidikan; #9 & #12 sudah ada sebagai file diskusi (tinggal matangkan).

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-13 | FINALISASI: diskusi dinyatakan final oleh user; status -> `Siap Difinalkan`; diarsipkan `[SELESAI]` & melahirkan `2026.Kep.010 tentang OCR Dokumen dengan Verifikasi Admin`. Tanpa sentuh kode |
| 2026-07-13 | KONSISTENSI INTERNAL: Rencana Tindak Lanjut baris 82 "menunggu #9/#12 lahir" -> "matang" (selaras Catatan AI Agent baris 68). Tanpa sentuh kode |
| 2026-07-13 | AUDIT FIX B.1: #8 T5 basi "BELUM ADA file diskusi" (#9/#12) -> SUDAH ADA, finalisasi menunggu kematangan. Tanpa sentuh kode |
| 2026-07-13 | Bersihkan blocker basi: baris 68/149 "#9 & #12 belum ada file diskusi" -> keduanya SUDAH ADA, finalisasi #8 menunggu kematangan (bukan kelahiran). Tanpa sentuh kode |
| 2026-07-13 | Matangkan #8 berdasar sampel riil SK (halaman 4 KEP/1717/M/XII/2025) + verifikasi ulang NIKC vs `Kep.003`/`NikcFormatRule`. Tambah T1-T6: field-mapping SK->schema, parse NIKC benar + cross-validation DOB/provinsi, jawaban Q1-Q3 (OCR KTP/KTA/Ijazah MVP, SK=structured import+queue, confidence hanya OCR gambar), relasi #2/#5/#9/#12, snapshot kondisi file. Catat #9 & #12 BELUM ADA = blocker finalisasi #8. Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-03 | Dokumen dibuat untuk menampung pembahasan OCR dokumen dengan verifikasi admin |
