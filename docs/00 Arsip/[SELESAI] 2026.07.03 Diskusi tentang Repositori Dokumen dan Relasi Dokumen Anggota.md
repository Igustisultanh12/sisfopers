# Diskusi
## tentang REPOSITORI DOKUMEN DAN RELASI DOKUMEN ANGGOTA

Status: `Siap Difinalkan`
> Status Arsip: `Selesai`
> Selesai pada: 2026-07-13 10:32:57 WIB
> Rujukan keputusan: `docs/02 Keputusan/2026.Kep.007 tentang Repositori Dokumen dan Relasi Dokumen Anggota.md`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang Repositori Dokumen dan Relasi Dokumen Anggota
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: unggah dokumen, klasifikasi dokumen, repositori file, relasi dokumen ke anggota, dan status dokumen aktif
Keputusan terkait: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md` (klausul 14 taxonomy regulasi), `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`, `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `docs/02 Keputusan/2026.Kep.007 tentang Repositori Dokumen dan Relasi Dokumen Anggota.md`

## Pemicu

Bahan user memuat kebutuhan repositori dokumen sebagai tempat menyimpan surat atau dokumen yang diunggah, bukan generator surat keluar. Karena domain ini akan menjadi dasar banyak fitur lain, perlu diskusi khusus yang menstabilkan istilah, klasifikasi, dan relasinya ke anggota.

## Temuan Awal

- ada dua istilah yang sempat muncul: `surat` dan `dokumen`
- belum ada keputusan final apakah istilah resmi sistem akan memakai salah satu atau keduanya dengan makna berbeda
- domain ini beririsan dengan pangkat, jabatan, pendidikan, penugasan, dan OCR

## Poin Diskusi

### 1. Istilah resmi perlu distabilkan

Perlu diputuskan apakah sistem akan memakai:

- `dokumen` sebagai istilah payung
- `surat` untuk subset tertentu
- atau nomenklatur lain yang lebih tepat

### 2. Peran sistem sebagai repositori

Diskusi perlu menegaskan bahwa sistem menyimpan dan mengelola dokumen yang diunggah, bukan membuat surat keluar.

### 3. Relasi dokumen ke anggota

Perlu diputuskan:

- apakah satu dokumen dapat terkait ke banyak anggota
- apakah satu anggota dapat terkait ke banyak dokumen
- jenis dokumen apa yang memiliki konsep satu aktif pada satu waktu

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Sistem sebagai repositori dokumen | Sudah muncul jelas | Clear |
| 2 | Istilah resmi `surat` vs `dokumen` | **Dijawab:** `dokumen` = payung; `surat` = subset (Surat Keputusan/SK). Di kode: `surat_keputusan_artifacts` sudah pakai istilah `surat` | Clear (lihat T1) |
| 3 | Relasi many-to-many ke anggota | **Dijawab:** satu dokumen (SK Penetapan) bisa ke banyak personel; satu personel punya banyak dokumen. Butuh tabel pivot `dokumen_anggota` (atau `surat_keputusan_personel_entries` sdh ada untuk SK) | Clear (lihat T3) |
| 4 | Aturan dokumen aktif | **Dijawab:** satu dokumen bisa punya status `aktif`/`kadaluarsa`/`ditolak`; untuk SK penetapan, yang berlaku = SK terbaru per nomor | Clear (lihat T4) |

## Catatan untuk AI Agent

- jangan lahirkan referensi teknis upload atau struktur tabel sebelum istilah dan relasi final
- topik OCR harus tetap hidup di diskusi terpisah (#8) agar domain ini tidak terlalu berat
- repositori dokumen personel BERBEDA dengan repositori PDF regulasi (arsip 2026.07.10: PDF disimpan di `docs/`, bukan storage app)

## Pertanyaan Terbuka

- istilah resmi apa yang ingin dipakai -> **Dijawab:** `dokumen` payung, `surat`/SK subset (T1).
- jenis dokumen apa saja yang masuk scope awal -> **Dijawab:** KTP, Ijazah (akademik+latsarmil), SK Penetapan, Sertifikat (diklat/soft skill), foto profil. Lainnya menyusul (T2).
- apakah admin boleh menghapus dokumen yang sudah terkait riwayat anggota -> **Dijawab:** tidak hard-delete; soft-delete + audit (selaras prinsip riwayat #9/arsip 2026.07.09:566).

## Rencana Tindak Lanjut

- [x] verifikasi kondisi file (AuthController, SuratKeputusanController, storage disk public)
- [x] matangkan istilah, klasifikasi, relasi, dokumen aktif (T1-T5)
- [x] petakan relasi ke #2/#8/#9/#13/#14
- [ ] finalisasi #12 menunggu #8/#9 matang (sudah ada, tinggal selaraskan)

## Temuan Analisis (2026-07-13, fase bertahap menuju tujuan 1)

Berdasarkan: baca #12 asli, kode `AuthController`/`SuratKeputusanController`/`MasterPersonelController`/`Personel.php`,
arsip 2026.07.12 (Penyesuaian Pangkat), #9 T10, #2 T5. Tanpa ubah kode (Kep.001 klausul 24). Masih `04 Diskusi`.

### T1. Istilah & klasifikasi
- `dokumen` = istilah payung untuk semua file terunggah (KTP, ijazah, SK, sertifikat, foto).
- `surat` / **Surat Keputusan (SK)** = subset dokumen yang bersifat keputusan resmi (multi-nama, bersama).
  Di kode sudah konsisten: tabel `surat_keputusan_artifacts` + `surat_keputusan_personel_entries`.
- Klasifikasi `jenis_dokumen`: `KTP`, `IJAZAH_AKADEMIK`, `IJAZAH_LATARMIL`, `SK_PENETAPAN`,
  `SERTIFIKAT` (diklat/soft skill), `FOTO_PROFIL`. (Soft skill sertifikat diinput lewat #10, tapi file
  fisiknya disimpan di repositori ini.)

### T2. Apa yang disimpan & di mana (snapshot kode)
- `personel/photos` (foto profil), `personel/documents` (KTP), `surat-keputusan` (SK) -> `Storage::disk('public')`.
- ⚠️ **RISIKO SECURITY (belum dibahas, perlu keputusan):** disk `public` di Laravel = dapat diakses siapa saja via URL (`/storage/KTP/xxx.jpg`) setelah `php artisan storage:link`. Dokumen **KTP / ijazah / SK = data pribadi sensitif** -> **TIDAK BOLEH publik**.
  - **Rekomendasi implementasi (untuk 03 Rencana Kerja, bukan keputusan diskusi):** simpan di disk `private`/`local`; akses via route berproteksi + `response()->file()` dengan otorisasi per-personel/per-role. Tambah `document_access_logs` (siapa akses dokumen apa+kapan) untuk audit privasi.
- `file_ijazah_path` di `Personel` **SUDAH ada field tapi BELUM dipakai di controller manapun** ->
  upload ijazah (akademik/latsarmil) belum tersimpan. Perlu `EducationController` (#9) yg simpan ke sini
  atau ke tabel `riwayat_pendidikan.file_path`.
- **TIDAK ADA folder `tmp`** di kode -> aturan "personel unggah SK -> folder tmp nunggu verifikasi" (#9 T10,
  arsip 2026.07.12) **belum diimplementasi**. Drift: butuh mekanisme `status = PENDING_TMP` atau disk terpisah.

### T3. Relasi many-to-many (dokumen <-> anggota)
- **SK Penetapan** = 1 dokumen -> BANYAK personel. Sudah ada `surat_keputusan_personel_entries`
  (pivot SK ke personel, berisi nikc/nama target). Ini model many-to-many yang benar untuk SK.
- **Ijazah / KTP / sertifikat** = 1 dokumen -> 1 personel (personal). Disimpan di `personels`/`riwayat_pendidikan`.
- Usulan: tabel `dokumen_anggota` (generic pivot) jika kelak dokumen non-SK perlu dibagikan; untuk fase awal
  cukup `surat_keputusan_personel_entries` (SK) + field di personel/riwayat (personal).

### T4. Dokumen aktif & lifecycle
- Setiap dokumen punya `status`: `PENDING_TMP` (unggah personel, belum diverifikasi), `AKTIF`,
  `DITOLAK`, `KADALUARSA` (untuk sertifikat berlaku terbatas, rujuk #10), `SOFT_DELETED`.
- SK Penetapan: yang berlaku = SK dengan nomor sama & tanggal terbaru (arsip 2026.07.12: reuse softcopy).
- Soft-delete wajib (tidak hard-delete) agar audit trail terjaga (selaras #9, arsip 2026.07.09:566).

### T7. Keamanan & privasi dokumen (usulan untuk 03 Rencana Kerja, belum keputusan diskusi)
- **Penyimpanan:** dokumen pribadi (KTP/ijazah/SK) **TIDAK di disk `public`**. Gunakan disk `private`/`local`; akses via route berproteksi + `response()->file()` dengan otorisasi (lihat T2).
- **Access audit log:** tambah `document_access_logs` (`document_id`, `accessed_by`, `accessed_at`, `ip`) agar setiap lihat/download tercatat (privasi, selaras prinsip audit #9/arsip 2026.07.09:566).
- **Enkripsi at rest:** kolom path dokumen sensitif dienkripsi (Laravel `encrypted` cast) atau enkripsi volume; KTP/SK mengandung NIK/NIKC + data diri.
- **Retensi / right-to-erasure:** soft-delete wajib (#12 T4); kebijakan hapus permanen (GDPR-like) ditetapkan nanti di 03 Rencana Kerja (belum dibahas di diskusi).
- **KTP berlaku SEUMUR HIDUP** (tidak ada kadaluarsa — meski di kartu tertulis tanggal, data tetap berlaku seumur hidup per aturan kependudukan). Maka `KADALUARSA` **TIDAK dipakai untuk KTP**; status `KADALUARSA` hanya untuk sertifikat berlaku-terbatas (#10 Soft Skill).
- **NIK (16 digit) = profil internal personel SAJA**, BUKAN identifier login. Login = **NIKC (17 digit) atau username** (#2 T5/T6). NIK dipakai untuk keperluan pencocokan/internal (mis. OCR KTP -> cocok ke `personels.nik`), bukan untuk masuk akun.
- **PENDING_TMP dikunci sebagai STATUS FLAG (bukan folder fisik):** personel unggah SK -> `status = PENDING_TMP` di `surat_keputusan_artifacts`; admin verifikasi -> `AKTIF`. Tidak perlu folder `tmp` terpisah (lebih sederhana & audit-friendly).

### T8. Visibilitas koordinator vs kebutuhan verifikasi (klarifikasi)
- #9 T9: koordinator **terbatas pada biodata umum** (tidak boleh browsing semua detail pendidikan).
- #2 T5: koordinator **memverifikasi dokumen** (KTA/Ijazah hilang).
- **Reconcile:** koordinator BOLEH melihat dokumen yang **sedang diverifikasinya** (termasuk ijazah/sertifikat milik orang lain yang masuk antrean verifikasi), TETAPI **TIDAK boleh browsing** seluruh riwayat pendidikan semua anggota. Batas: akses dokumen = scoped ke task verifikasi aktif (`document_access_logs` + queue verifikasi), bukan akses pasif全局.
- Super Admin: lihat semua (tanpa batas). Personel: hanya diri sendiri.
Ada **dua repositori terpisah**, jangan dicampur:
- **(A) Repositori Regulasi Hukum** = `docs/10 Referensi Internal/` (SUDAH FINAL, arsip 2026.07.10 +
  `Kep.001` klausul 14). Taxonomy: `Undang-Undang` / `Peraturan` / `Peraturan Menteri` /
  `Keputusan Menteri` / `Edaran` / `Lainnya`. PDF sumber **wajib di repo** (`docs/`), bukan storage app.
  **Surat Keputusan hidup di rumpun `Keputusan Menteri`** sebagai artefak sumber domain aktif.
  Satu SK massal = satu artefak + satu ringkasan + satu indeks nama; boleh di-reuse lintas proses.
- **(B) Repositori Dokumen Personel/Operasional** = storage app (`Storage::disk('public')`): KTP, ijazah,
  foto profil, sertifikat, dan **softcopy SK operasional** (`surat-keputusan/`). Ini ranah #12.
- **SK Penetapan = jembatan**: secara hukum masuk (A) rumpun `Keputusan Menteri`; secara operasional
  softcopy-nya masuk (B) + tabel `surat_keputusan_artifacts`/`_entries`. Keduanya rujuk nomor SK yang sama.
- #12 hanya mengatur (B). Rujukan ke (A) cukup sebut "sesuai arsip 2026.07.10 / Kep.001 kl 14", tidak
  menduplikasi taxonomy regulasi.

### T5. Relasi & dependency (cross-check)
- **#12 <-> #9:** file ijazah akademik/latsarmil & sertifikat disimpan di repositori (B) ini; #9 butuh #12 matang. ✅
- **#12 <-> #2 T5:** personel unggah SK -> `PENDING_TMP` (folder tmp konseptual) -> admin verifikasi -> `AKTIF`. ✅
- **#12 <-> #8:** import SK massal butuh repositori (B) + pivot entries; #8 BELUM FINAL. ⏳
- **#12 <-> #13 (Riwayat Jabatan) / #14 (Riwayat Penugasan):** jika jabatan/penugasan bergantung dokumen,
  rujuk repositori ini. #13/#14 belum dibahas matang. ⏳
- **#12 <-> Arsip 2026.07.12:** SK diunggah Super Admin -> langsung dipakai; personel unggah -> disimpan &
  direuse, tidak wajib unggah ulang. ✅
- **#12 <-> Arsip 2026.07.10 (Regulasi) + Kep.001 kl 14:** repo regulasi hukum = `docs/10 Referensi Internal`
  (taxonomy UU/Peraturan/Peraturan Menteri/Keputusan Menteri/Edaran/Lainnya), PDF di repo bukan storage app;
  **SK masuk rumpun Keputusan Menteri** (lihat T6). #12 = repo operasional personel (B), berbeda rumah
  tapi SK menjadi jembatan. ✅ (koreksi: revisi lalu hanya bilang "beda repositori", kurang tepat)
- **Kode:** pakai `Storage::disk('public')` -> butuh `php artisan storage:link` (belum diuji, Laragon mati).
  `file_ijazah_path` belum terisi; `tmp` belum ada. ⚠️

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-13 | FINALISASI: diskusi dinyatakan final oleh user; status -> `Siap Difinalkan`; diarsipkan `[SELESAI]` & melahirkan `2026.Kep.007 tentang Repositori Dokumen dan Relasi Dokumen Anggota`. Tanpa sentuh kode |
| 2026-07-13 | KONSISTENSI INTERNAL: Hasil Diskusi baris 52-54 status "Perlu diputuskan" -> "Clear (lihat T1/T3/T4)" karena sudah dijawab di analisis bawah. Tanpa sentuh kode |
| 2026-07-13 | KOREKSI user: KTP berlaku SEUMUR HIDUP (status KADALUARSA tdk dipakai KTP); NIK (16 digit) profil internal SAJA, BUKAN login (login = NIKC/username). Tanpa sentuh kode |
| 2026-07-13 | AUDIT FIX B.3/B.7 gap: #12 T2 tambah RISIKO SECURITY (disk public -> private + route terproteksi); T7 (keamanan & privasi: disk private, access audit log, enkripsi at rest, retensi, KTP kadaluarsa, PENDING_TMP = status flag BUKAN folder); T8 (reconcile visibilitas koordinator vs verifikasi). B.7 #9 field `«redacted»`->`sk_penetapan_id`. Tanpa sentuh kode |
| 2026-07-13 | Bersihkan metadata pra-finalisasi: isi `Keputusan terkait` = Kep.001 kl14 + Kep.004 + Kep.005 (sebelumnya kosong). Status lifecycle PENDING_TMP/dst tetap usulan diskusi (belum terkunci). Tanpa sentuh kode |
| 2026-07-13 | Matangkan #12: T1 (istilah dokumen/surat + klasifikasi jenis), T2 (snapshot kode: personel/photos, personel/documents, surat-keputusan; `file_ijazah_path` belum dipakai; `tmp` belum ada = drift), T3 (relasi many-to-many: SK via `surat_keputusan_personel_entries`, personal via personels/riwayat), T4 (lifecycle status PENDING_TMP/AKTIF/DITOLAK/KADALUARSA/SOFT_DELETED; SK terbaru yg berlaku; soft-delete wajib), T5 (relasi #2/#8/#9/#13/#14 + arsip 2026.07.12/2026.07.10). Jawab poin 2-4 + pertanyaan terbuka. Tetap `04 Diskusi`, tanpa sentuh kode |
| 2026-07-03 | Dokumen dibuat untuk menampung pembahasan repositori dokumen dan relasinya ke anggota |
