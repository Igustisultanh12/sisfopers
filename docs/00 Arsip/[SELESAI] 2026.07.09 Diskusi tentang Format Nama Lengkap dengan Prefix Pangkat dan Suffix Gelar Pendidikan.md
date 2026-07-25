# Diskusi
## tentang FORMAT NAMA LENGKAP DENGAN PREFIX PANGKAT DAN SUFFIX GELAR

Status: `Siap Difinalkan`
> Status Arsip: `Selesai`
> Selesai pada: 2026-07-13 10:32:57 WIB
> Rujukan keputusan: `docs/02 Keputusan/2026.Kep.011 tentang Format Nama Lengkap dengan Prefix Pangkat dan Suffix Gelar.md`
Tanggal dibuka: 9 Juli 2026
Identitas dokumen: 2026.07.09 Diskusi tentang Format Nama Lengkap dengan Prefix Pangkat dan Suffix Gelar Pendidikan
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: Prefix Pangkat, Nama Lengkap, Suffix Gelar Pendidikan Otomatis
Keputusan terkait: `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md` (model pendidikan, status, verifikasi), `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md` (boundary pangkat ≠ suffix gelar, `(W)`, 3 pangkat klaim)
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `docs/02 Keputusan/2026.Kep.011 tentang Format Nama Lengkap dengan Prefix Pangkat dan Suffix Gelar.md`

## Latar Belakang

Sistem Informasi Keanggotaan memerlukan format penulisan nama yang konsisten dan otomatis. Format ini terdiri dari tiga komponen:

1. Prefix Pangkat: singkatan pangkat yang melekat di depan nama, termasuk varian `(W)` untuk perempuan.
2. Nama Lengkap: nama asli anggota tanpa gelar.
3. Suffix Gelar: gelar akademik yang dihasilkan otomatis dari riwayat pendidikan formal.

Format ini digunakan di seluruh sistem: profil anggota, dokumen, laporan, kartu anggota, dan tampilan lainnya.

## Bahan User Lengkap

### Komponen 1: Prefix Pangkat

Prefix pangkat sepenuhnya mengacu pada manajemen kepangkatan. Berikut bahan draft lengkap yang harus tercatat:

#### Hirarki Pangkat Lengkap (18 Tingkat)

| Urutan | Nama Pangkat | Singkatan | Format untuk Laki-laki | Format untuk Perempuan |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Prajurit Dua | Prada KC | `Prada KC [nama]` | - |
| 2 | Prajurit Satu | Pratu KC | `Pratu KC [nama]` | - |
| 3 | Prajurit Kepala | Praka KC | `Praka KC [nama]` | - |
| 4 | Kopral Dua | Kopda KC | `Kopda KC [nama]` | - |
| 5 | Kopral Satu | Koptu KC | `Koptu KC [nama]` | - |
| 6 | Kopral Kepala | Kopka KC | `Kopka KC [nama]` | - |
| 7 | Sersan Dua | Serda KC | `Serda KC [nama]` | `Serda KC (W) [nama]` |
| 8 | Sersan Satu | Sertu KC | `Sertu KC [nama]` | `Sertu KC (W) [nama]` |
| 9 | Sersan Kepala | Serka KC | `Serka KC [nama]` | `Serka KC (W) [nama]` |
| 10 | Sersan Mayor | Serma KC | `Serma KC [nama]` | `Serma KC (W) [nama]` |
| 11 | Pelda | Pelda KC | `Pelda KC [nama]` | `Pelda KC (W) [nama]` |
| 12 | Peltu | Peltu KC | `Peltu KC [nama]` | `Peltu KC (W) [nama]` |
| 13 | Letnan Dua | Letda KC | `Letda KC [nama]` | `Letda KC (W) [nama]` |
| 14 | Letnan Satu | Lettu KC | `Lettu KC [nama]` | `Lettu KC (W) [nama]` |
| 15 | Kapten | Kapten KC | `Kapten KC [nama]` | `Kapten KC (W) [nama]` |
| 16 | Mayor | Mayor KC | `Mayor KC [nama]` | `Mayor KC (W) [nama]` |
| 17 | Letnan Kolonel | Letkol KC | `Letkol KC [nama]` | `Letkol KC (W) [nama]` |
| 18 | Kolonel | Kolonel KC | `Kolonel KC [nama]` | `Kolonel KC (W) [nama]` |

#### Aturan Fundamental Pangkat dari Draft Awal

1. Pangkat bersifat hirarkis dengan urutan 1 (terendah) sampai 18 (tertinggi).
2. Anggota perempuan tidak mungkin memiliki pangkat di bawah Sersan Dua (urutan 7).
3. Semua anggota perempuan selalu mendapatkan tambahan `(W)` pada format penulisan nama.
4. Anggota laki-laki tidak pernah mendapatkan tambahan `(W)`.
5. Jika admin mencoba memberikan pangkat urutan < 7 kepada anggota perempuan, sistem menolak.

#### Logika Pemilihan Prefix dari Draft Awal

```text
JIKA anggota.jenis_kelamin == "PEREMPUAN":
    prefix = pangkat.singkatan + " (W)"
JIKA anggota.jenis_kelamin == "LAKI_LAKI":
    prefix = pangkat.singkatan
```

#### Contoh Prefix dari Draft Awal

| Anggota | Jenis Kelamin | Pangkat | Prefix |
| :--- | :--- | :--- | :--- |
| Andi Pratama | Laki-laki | Prajurit Dua | `Prada KC` |
| Budi Santoso | Laki-laki | Sersan Dua | `Serda KC` |
| Siti Rahayu | Perempuan | Prajurit Dua | tidak mungkin, sistem menolak |
| Dewi Lestari | Perempuan | Sersan Dua | `Serda KC (W)` |
| Rina Anggraini | Perempuan | Letnan Dua | `Letda KC (W)` |

### Komponen 2: Nama Lengkap

1. Nama lengkap disimpan dalam satu field `full_name`.
2. Tidak ada pemisahan nama depan dan nama belakang.
3. Nama lengkap diisi apa adanya sesuai dokumen resmi.
- Field `full_name` wajib.

### Komponen 3: Suffix Gelar Pendidikan Otomatis

1. Suffix gelar tidak diisi manual.
2. Suffix gelar dihasilkan otomatis berdasarkan riwayat pendidikan formal.
3. Hanya gelar akademik yang ditulis.
4. Gelar dari pendidikan non-formal tidak ditulis.
5. Riwayat gelar diambil dari tabel pendidikan yang relevan.

### Contoh Penyusunan Suffix

- S1 Psikologi => `S.Psi.`
- S1 Psikologi + S2 Humaniora => `S.Psi., M.Hum.`
- D3 + S1 + S2 => `A.Md.T., S.T., M.M.`

### Rumus Format Nama Lengkap

```text
FORMAT_FINAL = PREFIX + SPASI + NAMA_LENGKAP + SUFFIX
```

### Contoh Hasil Akhir dari Draft Awal

| Anggota | Jenis Kelamin | Pangkat Aktif | Nama Lengkap | Gelar Akademik | Hasil Format |
| :--- | :--- | :--- | :--- | :--- | :--- |
| Andi Pratama | Laki-laki | Prajurit Dua | Andi Pratama | - | `Prada KC Andi Pratama` |
| Budi Santoso | Laki-laki | Sersan Dua | Budi Santoso | S.Psi. | `Serda KC Budi Santoso, S.Psi.` |
| Dewi Lestari | Perempuan | Sersan Dua | Dewi Lestari | S.Psi., M.Hum. | `Serda KC (W) Dewi Lestari, S.Psi., M.Hum.` |

## Klarifikasi Terkini dari User

Setelah arahan terbaru, baseline operasional pangkat yang bisa diklaim langsung adalah:

| Nama Pangkat | Singkatan | Varian Perempuan |
| --- | --- | --- |
| Prada KC | `Prada KC` | - |
| Serda KC | `Serda KC` | `Serda KC (W)` |
| Letda KC | `Letda KC` | `Letda KC (W)` |

Aturan tambahan yang berlaku dari klarifikasi terkini:

- hanya tiga pangkat di atas yang bisa diklaim langsung
- pangkat di luar itu hanya boleh lewat penyesuaian pangkat berdasarkan ijazah atau kenaikan pangkat
- dua mekanisme tersebut harus disertai Surat Keputusan
- `(W)` tetap varian gender, bukan pangkat baru
- daftar 18 tingkat dari draft awal tetap dicatat sebagai bahan user lengkap, bukan otomatis menjadi baseline klaim

## Pemetaan Sumber

### A. Masih satu domain dengan dokumen pangkat lama

Bagian yang masih satu domain dengan diskusi pangkat:

- prefix pangkat di depan nama
- varian `(W)` untuk perempuan
- validasi pangkat sebagai domain kepangkatan
- pangkat default yang bisa diklaim
- pangkat selain default yang membutuhkan SK

### B. Sebaiknya dipisah ke diskusi pendidikan

Bagian yang lebih tepat berada di diskusi pendidikan:

- suffix gelar akademik otomatis
- riwayat pendidikan formal sebagai sumber gelar
- urutan gelar
- aturan gelar mana yang ditulis dan mana yang tidak ditulis

### C. Jelas harus jadi diskusi baru

Bagian yang sudah beda scope dan perlu rumah diskusi sendiri:

- pembersihan prefix pangkat saat OCR membaca nama dokumen
- pembersihan suffix gelar saat OCR membaca nama dokumen
- confidence score hasil OCR
- verifikasi admin atas hasil OCR

## Dampak pada Database

## Pemetaan Sumber

- Field nama tetap satu field.
- Suffix gelar dihitung otomatis.
- Prefix pangkat dipisah dari suffix gelar.
- Varian `(W)` tetap berbasis gender.
- Pangkat default klaim dibatasi pada tiga baseline operasional.

## Pertanyaan Pending

|1. Urutan gelar apakah sudah final. -> **Dijawab (default):** naik S1->S2->S3, dipisah koma + spasi; string diambil apa adanya dari calon (lihat Temuan Analisis T1).
|2. Apakah gelar S3 perlu variasi penulisan. -> **Dijawab:** dalam negeri `Dr.` (suffix, SETELAH nama); luar negeri `Ph.D.` (edge). Bukan `Dr. rer. nat.` (Jerman).
|3. Apakah perlu master data gelar. -> **Dijawab (drop):** TIDAK. Sistem TIDAK memvalidasi benar/salah; calon yang tulis (free-text), ijazah = bukti. `S.Si.` vs `S.Farm.` sama-sah asal ada ijazah.
|4. Apakah gelar di depan nama juga perlu diatur. -> **Dijawab:** HANYA jika dibuktikan dengan sertifikat profesi / ijazah akademik. `dr.`, `Ir.`, `Apt.` masuk (akademik); `H.`, `Hj.`, `Ky.` dikeluarkan (bukan akademik).
|5. Bagaimana format keputusan SK untuk penyesuaian dan kenaikan pangkat. -> **Dijawab:** di `Kep.005` + ekstraksi SK (lihat diskusi #2 T2); sistem TIDAK generate SK, hanya mencatat.

## Catatan

- Dokumen ini menjadi pengikat format nama lengkap anggota.
- Prefix pangkat harus selalu merujuk ke dokumen pangkat.
- Suffix gelar harus selalu merujuk ke diskusi pendidikan.
- OCR harus selalu merujuk ke diskusi OCR.
- Draft awal 18 tingkat tetap dicatat agar sejarah arahan tidak hilang.

## Temuan Analisis (2026-07-13, fase bertahap menuju tujuan 1)

Catatan ini menyintesis hasil baca dokumen repo (`Kep.003/004/005`, arsip), referensi
Wikipedia "Gelar akademik" (bagian Indonesia), dan klarifikasi user. Bertujuan mematangkan
scope finalisasi tanpa mengubah status diskusi. Tetap level `04 Diskusi`.

### T1. Rumus display (target implementasi, BELUM ada di kode)
> Status implementasi: rumus di bawah adalah **rencana/diskusi**, BELUM diimplementasi.
> Kode aktual: `full_name` = plain `string(150)` (`RegisterPersonelRequest`, `Personel.php`);
> tidak ada `display_name`/`formatName`. Yang ada hanya accessor `display_pangkat` (pangkat + `(W)`).
> Saat finalisasi -> lahir kebutuhan accessor `display_name` (prefix pangkat + front-title + nama + suffix).
```
FORMAT_FINAL = PREFIX_PANGKAT [ + FRONT_TITLE ] + SPASI + NAMA_LENGKAP + SPASI + SUFFIX_GELAR
```
- Q1 (urutan): naik S1->S2->S3, dipisah koma + spasi. String **diambil apa adanya dari calon** (bukan dari master).
  Contoh: `S1 Psikologi + S2 Humaniora` -> `S.Psi., M.Hum.`
- Q2 (S3): dalam negeri `Dr.` (suffix, SETELAH nama); luar negeri `Ph.D.` (edge). Bukan `Dr. rer. nat.`
- Q3 (free-text, DROP `master_gelar`): calon yang tulis; sistem TIDAK validasi benar/salah; ijazah = bukti.
  Contoh sah: `S.Si.` (Sarjana Sains) & `S.Farm.` (Sarjana Farmasi) & `S.Tr.` (Diploma 4 / Sarjana Terapan) -> **semua valid asal ada ijazah**.
- Q4 (FRONT_TITLE, aturan presisi): HANYA dimasukkan JIKA (a) ada sertifikat profesi ATAU ijazah akademik, DAN (b) gelar = hasil pendidikan umum/akademik.
  - Dimasukkan: `dr.` (dokter), `Ir.` (insinyur), `Apt.` (apoteker) -- akademik/profesi, ada ijazah.
  - Dikeluarkan: `H.` (haji), `Hj.` (hajah), `Ky.` (kyai) -- keagamaan/sosial, BUKAN akademik, tak bisa dibuktikan ijazah.
  - ⚠️ `dr.` (depan, profesi) ≠ `Dr.` (suffix, S3).
- Q5: sistem TIDAK generate SK; hanya mencatat (upload + ekstraksi field, lihat #2 T2).

### T2. Gelar melekat pada pendidikan umum/akademik (klarifikasi user)
- Gelar (depan maupun belakang) **melekat pada pendidikan umum/akademik** karena harus **dibuktikan** (ijazah/sertifikat).
- Jika dari **SK sudah ada gelar** (tercantum di SK penetapan), sistem **TIDAK perlu meminta upload ijazah** -- SK sudah jadi bukti.
- Jika personel **edit profil dan mau mencantumkan gelarnya**, maka **perlu upload ijazah** sebagai bukti (karena belum ada di SK).
- Implikasi: aturan "ijazah wajib" di `#9` / `Kep.004` (field `file_ijazah_path`) berlaku KONDISIONAL -- hanya saat gelar/bukti pendidikan memang dibutuhkan dan belum ada di SK.
- **Catatan pemisahan:** ijazah di sini = **Ijazah Pendidikan Umum/Akademik** (sumber suffix gelar), **BERBEDA** dengan **Ijazah Latsarmil / Pendidikan Militer** yang dipakai sebagai bukti keanggotaan Komcad di #2 T3. Keduanya tidak saling menggantikan.

### T3. Delegasi scope (jangan bentrok dengan dokumen lain)
- **Rujuk saja (sudah final, jangan sentuh):**
  - Boundary pangkat ≠ suffix gelar -> `Kep.005` klausul 1 ("pangkat domain tersendiri", diperkuat ruang lingkup "pangkat tidak bercampur dengan suffix gelar pendidikan").
  - Varian `(W)` gender -> `Kep.005` klausul 3.
  - 3 pangkat klaim (Prada/Serda/Letda KC) -> `Kep.005` / `Kep.003`.
  - Model data pendidikan (`education_level`, `study_program`, `file_ijazah_path`, `education_verified_*`) -> `Kep.004` (dari arsip Master Data 254-314).
  - Stabilitas NIKC & skenario SPPI (Prada->Letda, NIKC tetap 3) -> `Kep.003` + arsip Identitas Grup Angkatan 155-192, 271, 335, 340.
- **Delegasikan ke #9 (Pendidikan):** sumber suffix (riwayat pendidikan), urutan gelar (Q1), variasi S3 (Q2).
- **#8 (OCR) cukup strip** gelar depan/belakang saat match -- #5 cukup definisikan canonical.

## Kondisi Kode Aktual (snapshot 2026-07-13, verifikasi statis)

> Merekam keadaan file/schema SAAT INI agar implementasi FORMAT_FINAL mudah dipetakan saat
> lahir keputusan/revisi. Verifikasi STATIS (baca file); runtime tidak diuji.

- `personels.full_name` = plain `string(150)` (migration `2026_07_06_055601`) -- satu kolom nama utuh, TANPA dekomposisi prefix/suffix.
- `personels.pangkat` = `string(50)`; ada accessor `display_pangkat` (pangkat + `(W)` dari gender) di `Personel.php`. Ini SATU-SATUNYA formatting display yang sudah ada.
- Pendidikan: kolom `education_level`, `study_program`, `file_ijazah_path` ADA (sumber suffix gelar per #9), tapi TIDAK ada kolom `gelar`/`front_title`/`suffix` khusus.
- **BELUM ADA** di kode: `display_name`/`formatName` (rumus FORMAT_FINAL), field gelar depan/belakang terstruktur. -> FORMAT_FINAL = pekerjaan implementasi baru (accessor + kemungkinan kolom gelar), bukan kondisi berjalan.
- Validasi input: `RegisterPersonelRequest.full_name` = `required|string|max:150`; `MasterPersonelController` idem. Tidak ada validasi/parse gelar.

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-13 | Tambah catatan pemisahan di T2: ijazah di sini = Ijazah Akademik (sumber gelar), BERBEDA dengan Ijazah Latsarmil (bukti keanggotaan Komcad, #2 T3). Keduanya tak saling ganti. Tetap `04 Diskusi` |
| 2026-07-13 | FINALISASI: diskusi dinyatakan final oleh user; status -> `Siap Difinalkan`; diarsipkan `[SELESAI]` & melahirkan `2026.Kep.011 tentang Format Nama Lengkap dengan Prefix Pangkat dan Suffix Gelar`. Tanpa sentuh kode |
| 2026-07-13 | KONSISTENSI/TEMPLATE: header disesuaikan ke template standar `04 Diskusi` (`Identitas dokumen`, `Jenis dokumen`, `Keputusan terkait` alih-alih `Terkait keputusan`). Tanpa sentuh kode |
| 2026-07-13 | AUDIT FIX B.2/B.5/B.6: B2 cabut blok "Dampak pada Database" (ALTER master_pendidikan) yg kontradiksi Q3 (DROP master_gelar); B5 `nama_lengkap`->`full_name` (seragam migration); B6 metadata duplikat Kep.005 dihapus. Tanpa sentuh kode |
| 2026-07-13 | Bersihkan metadata pra-finalisasi: ganti rujukan `2026.02.007`/`2026.02.002` (tidak ada di repo) -> `Kep.004` (model pendidikan) + `Kep.005` (boundary pangkat≠gelar). Catat #5 final prematur selama #9/#8 belum dikunci. Tanpa sentuh kode |
| 2026-07-13 | Tambah blok "Kondisi Kode Aktual": `full_name` plain string(150), accessor `display_pangkat` satu-satunya display formatting, tak ada `display_name`/kolom gelar terstruktur -> FORMAT_FINAL = pekerjaan implementasi baru. Tetap `04 Diskusi` |
| 2026-07-13 | Koreksi pasca re-audit: (E) betulkan sitasi `Kep.005` -- boundary pangkat≠gelar = klausul 1, `(W)` = klausul 3 (sebelumnya salah tulis nomor baris 22/52/167 sebagai klausul). (F) tandai FORMAT_FINAL sebagai target implementasi yang BELUM ada di kode (`full_name` masih plain string(150), belum ada `display_name`/`formatName`). Tetap `04 Diskusi` |
| 2026-07-13 | Menambahkan bagian "Temuan Analisis" berisi T1-T3: rumus display final (Q1-Q5), aturan presisi FRONT_TITLE (hanya akademik/profesi: `dr.`/`Ir.`/`Apt.` masuk, `H.`/`Hj.`/`Ky.` dikeluarkan), gelar melekat pada pendidikan umum/akademik (SK sudah ada gelar = tak perlu ijazah; edit profil mau nyantumkan = perlu ijazah), dan delegasi scope ke `Kep.005`/`Kep.004`/`Kep.003` + #9/#8. 5 Pertanyaan Pending ditandai Dijawab. Tetap level `04 Diskusi`, belum final |
| 2026-07-09 | Dokumen dilengkapi dengan bahan draft lengkap user, klarifikasi terkini pangkat default klaim, dampak OCR, dan pemisahan sumber domain |
