# Rencana Kerja
## tentang SINKRONISASI KEPUTUSAN STANDAR IDENTITAS DAN PENAMAAN DOKUMEN FORMAL

Status: `Selesai`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Rencana Kerja tentang Sinkronisasi Keputusan Standar Identitas dan Penamaan Dokumen Formal
Jenis dokumen: Rencana Kerja
Domain: kctrimatra
Topik: sinkronisasi dokumen governance inti setelah lahirnya keputusan payung pertama
Keputusan terkait: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: -

> Status Arsip: `Selesai`
> Selesai pada: 2026-07-03 13:26:41 WIB
> QA user: disetujui
> Catatan penutupan: sinkronisasi governance inti diterima user dan workplan ditutup sesuai lifecycle.

## Instruksi Baca Wajib

1. `AGENTS.md`
2. `docs/README.md`
3. `docs/CHANGELOG.md`
4. dokumen ini
5. `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
6. dokumen governance inti yang disentuh

## Tujuan

Menyelaraskan dokumen governance inti agar keputusan payung pertama menjadi source of truth aktif yang konsisten di seluruh jalur instruksi utama repo.

## Kondisi Stop

Berhenti dan laporkan ke user jika:

- ditemukan konflik normatif nyata antara keputusan dan dokumen aktif yang tidak bisa diselesaikan tanpa mengubah tafsir keputusan
- sinkronisasi ternyata lebih luas dari yang direncanakan dan mulai menyentuh domain di luar tujuh dokumen inti
- verifikasi koherensi dokumen gagal

## Klasifikasi Operasi

Operasi dalam workplan ini bersifat `Reversible`.

## Status Eksekusi

| Fase | Tujuan | Status | Catatan |
| --- | --- | --- | --- |
| 1 | Melahirkan keputusan payung dan workplan turunan | Selesai | Keputusan `2026.02.001` dan workplan ini lahir |
| 2 | Menyelaraskan tujuh dokumen governance inti | Selesai | README inti, instruksi agent, arsip, diskusi, dan keputusan diselaraskan |
| 3 | Verifikasi koherensi dokumen | Selesai | Pembacaan ulang dan audit status dilakukan |
| 4 | QA user dan penutupan normatif | Selesai | User menyatakan pekerjaan selesai dan workplan ditutup |

## Fase 1

### Tujuan

Melahirkan keputusan payung dan workplan turunan sesuai lifecycle.

### Klasifikasi

Reversible

### File yang Disentuh

- `docs/00 Arsip/[SELESAI] 2026.07.03 Diskusi tentang Adaptasi Standar Identitas dan Penamaan Dokumen Formal.md`
- `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
- `docs/03 Rencana Kerja/2026.07.03 Rencana Kerja tentang Sinkronisasi Keputusan Standar Identitas dan Penamaan Dokumen Formal.md`

### Verifikasi fase

- keputusan lahir di `02 Keputusan`
- diskusi final diarsipkan ke `00 Arsip`
- workplan lahir di `03 Rencana Kerja`

### Langkah jika gagal

- hentikan lifecycle
- laporkan file atau metadata yang gagal

## Fase 2

### Tujuan

Menyelaraskan tujuh dokumen governance inti dengan keputusan payung.

### Klasifikasi

Reversible

### File yang Disentuh

- `docs/README.md`
- `AGENTS.md`
- `docs/02 Keputusan/README.md`
- `docs/04 Diskusi/README.md`
- `docs/03 Rencana Kerja/README.md`
- `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`
- `docs/00 Arsip/README.md`
- `docs/CHANGELOG.md`

### Verifikasi fase

- setiap dokumen inti menyebut source of truth normatif secara konsisten
- wording lifecycle tidak drift dari keputusan payung
- aturan workplan dan QA selaras

### Langkah jika gagal

- tandai dokumen yang masih drift
- hentikan penutupan workplan

## Fase 3

### Tujuan

Memverifikasi koherensi hasil sinkronisasi.

### Klasifikasi

Reversible

### File yang Disentuh

- tidak ada perubahan substansi baru; fase ini berfokus pada audit baca ulang

### Verifikasi fase

- `git status --short`
- pembacaan ulang dokumen aktif utama

### Langkah jika gagal

- buka gap sebagai temuan
- jangan klaim sinkronisasi selesai

## Verifikasi Final

- keputusan payung lahir
- diskusi sumber diarsipkan
- workplan turunan lahir
- tujuh dokumen governance inti sudah diselaraskan
- tidak ada drift normatif yang tersisa pada jalur instruksi utama yang disentuh

## Hasil Verifikasi Lokal

- arsip diskusi final lahir di `docs/00 Arsip`
- keputusan `2026.02.001` lahir di `docs/02 Keputusan`
- workplan ini lahir di `docs/03 Rencana Kerja`
- tujuh dokumen governance inti dibaca ulang setelah sinkronisasi
- `git status --short` dipakai untuk memastikan perubahan yang tersisa

## Catatan Perubahan Detail

- diskusi final dipindahkan ke arsip dengan metadata `Selesai`
- keputusan payung pertama dilahirkan tanpa menyebut repo pembanding
- dokumen governance inti diselaraskan agar keputusan payung menjadi hirarki tertinggi
- topik GitHub/Gitea tetap ditahan sebagai calon referensi teknis turunan

## Status Commit Lokal

Belum commit. Workplan selesai dan siap masuk ke batch commit berikutnya bila user menginginkannya.

## Catatan QA dan Penutupan

- QA user terjadi melalui persetujuan eksplisit user bahwa pekerjaan selesai
- workplan ditutup dan dipindahkan ke arsip sesuai lifecycle
