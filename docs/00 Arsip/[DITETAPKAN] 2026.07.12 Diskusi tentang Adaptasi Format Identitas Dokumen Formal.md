> Status Arsip: `Ditetapkan`
> Ditetapkan pada: 2026-07-12 00:55:22 WIB
> Ditetapkan oleh: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
> Catatan: Diskusi ini telah difinalkan dan menjadi dasar revisi keputusan payung identitas dokumen formal.

# Diskusi
## tentang ADAPTASI FORMAT IDENTITAS DOKUMEN FORMAL

Status: `Ditetapkan`
Tanggal dibuka: 12 Juli 2026
Identitas dokumen: 2026.07.12 Diskusi tentang Adaptasi Format Identitas Dokumen Formal
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: perubahan format identitas dokumen formal keluarga keputusan dari `YYYY.02.NNN` ke `YYYY.Kep.NNN` dengan aturan tidak berlaku surut
Keputusan terkait: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: -

## Pemicu

Diskusi ini dibuka karena repo `kctrimatra` masih berada pada fase pertumbuhan awal, tetapi keputusan formal aktifnya sudah terbentuk dan masih memakai `YYYY.02.NNN`. User ingin mengarahkan keluarga keputusan ke `YYYY.Kep.NNN` tanpa memaksa perubahan surut pada dokumen yang tidak disentuh.

## Temuan Awal

- valid: keputusan payung identitas dokumen formal sudah lahir dan saat ini masih membakukan `YYYY.02.XXX`
- valid: repo ini paling muda dan paling sedikit keputusan formal aktifnya
- valid: diskusi dan rencana kerja aktif sudah cukup konsisten memakai format tanggal
- valid: repo ini paling cocok dijadikan titik mulai adopsi format baru karena beban legacy-nya paling ringan

Ringkasan audit awal:

- dokumen aktif yang diaudit: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`, `docs/02 Keputusan/README.md`, `docs/03 Rencana Kerja/README.md`, `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`, `docs/04 Diskusi/README.md`
- keputusan formal aktif saat ini kini memakai pola campuran historis yang telah dinormalisasi ke `2026.Kep.001`, `2026.Kep.003`, `2026.Kep.004`, dan `2026.Kep.005`, sementara keputusan UI masih berada pada `2026.02.002`
- seluruh rencana kerja aktif memakai pola tanggal
- diskusi aktif juga sudah memakai pola tanggal secara konsisten
- gap yang ditemukan: keputusan masih memakai kode `02`, padahal repo ini cukup bersih untuk langsung mulai membedakan keluarga keputusan dengan penanda yang lebih semantik

## Opsi yang Dipertimbangkan

- **Opsi A: Pertahankan `YYYY.02.NNN`**
  - Kelebihan:
    - tidak ada perubahan aturan
    - kontinuitas penuh dengan keputusan aktif
  - Kekurangan:
    - repo baru kehilangan kesempatan membangun boundary semantik yang lebih jelas sejak dini

- **Opsi B: Ubah ke `YYYY.Kep.NNN` hanya untuk keputusan baru atau keputusan lama yang direvisi**
  - Kelebihan:
    - transisi ringan
    - cocok untuk repo muda
    - bisa membuat repo ini menjadi adopsi paling cepat tanpa retrofit massal
  - Kekurangan:
    - akan ada masa transisi dua generasi identitas

- **Opsi C: Ubah ke `YYYY.Kep.NNN` dan sekaligus retrofit semua keputusan formal aktif**
  - Kelebihan:
    - cepat seragam
  - Kekurangan:
    - tidak sesuai dengan arah user
    - tetap menambah pekerjaan rename yang belum punya nilai substantif

## Poin Diskusi

### 1. Repo ini cocok memulai adopsi lebih cepat

Karena keputusan formal aktifnya masih sedikit, repo ini dapat:

- mulai memakai `YYYY.Kep.NNN` lebih cepat
- memakai wording transisi yang ringkas sejak awal

tanpa menanggung beban legacy yang besar.

### 2. Aturan non-retroaktif tetap lebih sehat daripada retrofit penuh

Walaupun repo ini ringan, model non-retroaktif tetap lebih konsisten dengan prinsip:

- edit minimum
- tidak membuat perubahan kosmetik besar tanpa kebutuhan substantif
- migrasi berjalan hanya saat ada revisi nyata

### 3. Scope diskusi tetap hanya keluarga keputusan

Diskusi ini tidak membahas perubahan pada:

- diskusi aktif
- rencana kerja aktif
- referensi teknis
- naming folder lain

karena semuanya sudah cukup stabil untuk saat ini.

### 4. Repo ini dapat memakai wording transisi yang paling sederhana

Karena konteksnya lebih bersih, keputusan akhir nanti dapat:

- menulis aturan transisi paling ringkas
- berdiri dengan aturan transisi yang ringkas

tanpa terlalu dibebani pengecualian historis.

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Arah format keluarga keputusan | Mengarah ke `YYYY.Kep.NNN` | Clear |
| 2 | Berlaku surut atau tidak | Tidak berlaku surut | Clear |
| 3 | Kondisi adopsi format baru | Ringan karena beban legacy kecil | Clear |
| 4 | Scope keluarga dokumen lain | Tetap di luar scope | Clear |
| 5 | Perlu tidaknya retrofit massal | Tidak perlu | Clear |

## Checklist Pra-Finalisasi

| # | Yang Wajib Dicatat | Sudah Dibahas? | Catatan |
| --- | --- | --- | --- |
| 1 | Scope (`termasuk` dan `tidak termasuk`) | Ya | Fokus pada keluarga keputusan |
| 2 | Non-goals | Ya | Tidak mengubah keluarga dokumen lain |
| 3 | Exception (`jika relevan`) | Ya | Tidak ada exception besar selain aturan non-retroaktif |
| 4 | Dampak ke fitur lain | Ya | Hanya ke governance dokumen |
| 5 | Dampak ke Profil Sistem (`jika relevan`) | Ya | README dan template keputusan terdampak |
| 6 | Data / Schema impact (`jika relevan`) | Ya | Tidak ada dampak schema database |
| 7 | Hal yang tidak berubah | Ya | Diskusi dan rencana kerja tetap dengan format sekarang |
| 8 | Acceptance criteria | Ya | Dirangkum di bawah |

Acceptance criteria yang diharapkan bila topik ini difinalkan:

- keputusan hasil finalisasi menetapkan keluarga keputusan memakai `YYYY.Kep.NNN`
- keputusan hasil finalisasi menegaskan aturan tidak berlaku surut
- keputusan lama `YYYY.02.NNN` tetap sah sampai direvisi
- README dan template keluarga keputusan diselaraskan
- repo dapat mulai memakai format baru pada keputusan berikutnya yang lahir atau direvisi

## Catatan untuk AI Agent

- pertahankan wording keputusan nanti tetap ringkas karena konteks repo ini masih cukup bersih
- jangan memperluas diskusi ini ke topik klasifikasi folder atau referensi teknis
- arah finalisasi yang paling sehat adalah revisi in-place terhadap keputusan payung identitas dokumen formal yang sama, bukan melahirkan keputusan payung baru, karena perubahan ini masih satu domain normatif

## Rencana Tindak Lanjut

- [x] finalkan diskusi ini dan arsipkan sesuai lifecycle
- [x] revisi in-place keputusan payung identitas dokumen formal agar keluarga keputusan berubah ke `YYYY.Kep.NNN`
- [x] selaraskan README keputusan dan template yang terdampak

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-12 | Dokumen dibuat untuk membahas adaptasi format identitas keluarga keputusan ke `YYYY.Kep.NNN` dengan aturan tidak berlaku surut |
