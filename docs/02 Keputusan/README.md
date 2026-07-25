# 02 Keputusan

Folder ini berisi keputusan aktif yang memegang norma final untuk repo.

Source of truth payung aktif saat ini:

- `2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
- `2026.02.002 tentang Standar UI dan Design System Kctrimatra.md`
- `2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`
- `2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
- `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`
- `2026.Kep.006 tentang Struktur Organisasi dan Grup Angkatan.md`
- `2026.Kep.007 tentang Repositori Dokumen dan Relasi Dokumen Anggota.md`
- `2026.Kep.008 tentang Riwayat Pendidikan dan Diklat Anggota.md`
- `2026.Kep.009 tentang Akun Login Logout Role dan Verifikasi Anggota.md`
- `2026.Kep.010 tentang OCR Dokumen dengan Verifikasi Admin.md`
- `2026.Kep.011 tentang Format Nama Lengkap dengan Prefix Pangkat dan Suffix Gelar.md`

## Peran Folder Ini

- `02 Keputusan` memegang source of truth normatif aktif
- `03 Rencana Kerja` mengeksekusi keputusan
- `04 Diskusi` membahas sebelum keputusan lahir
- `11 Referensi Teknis` menjabarkan keputusan ke panduan teknis atau template implementatif

## Identitas Dokumen

Dokumen aktif baru di folder ini wajib memakai format:

```text
YYYY.Kep.XXX tentang Judul Dokumen.md
```

Contoh:

```text
2026.Kep.001 tentang Standar Bootstrap Laravel 13.md
```

Aturan:

- nomor reset setiap tahun
- nomor unik per folder
- prefix status seperti `[FINAL]` atau `[REVISI]` tidak dipakai lagi pada dokumen aktif baru
- revisi dilakukan in-place pada dokumen aktif yang sama dan dicatat di changelog dokumen

## Metadata Minimal

Setiap keputusan aktif wajib memakai blok metadata berikut tepat di bawah judul:

```md
Status: Berlaku
Tanggal penetapan: DD Bulan YYYY
Identitas dokumen: YYYY.Kep.XXX
Jenis dokumen: Keputusan
Domain: kctrimatra
Topik: ...
Keputusan induk: -
Turunan dari: -
Mengubah: -
Mencabut: -
Digantikan oleh: -
```

## Template Ringkas

```md
# Keputusan No. XXX Tahun YYYY
## tentang JUDUL DOKUMEN

Status: Berlaku
Tanggal penetapan: DD Bulan YYYY
Identitas dokumen: YYYY.Kep.XXX
Jenis dokumen: Keputusan
Domain: kctrimatra
Topik: ...
Keputusan induk: -
Turunan dari: -
Mengubah: -
Mencabut: -
Digantikan oleh: -

## Latar Belakang
...

## Keputusan
1. ...
2. ...

## Acceptance Criteria
- [ ] ...
```

## Aturan Perubahan

- jika arah keputusan masih sama, revisi dilakukan pada dokumen aktif yang sama
- jika arah keputusan berubah besar, lahirkan keputusan baru dan arsipkan dokumen lama sebagai `DICABUT`
- keputusan baru wajib menyalin seluruh clause aktif lama yang masih berlaku
- keputusan aktif tidak boleh lahir dari rename file diskusi
- keputusan hanya boleh dicabut oleh keputusan
- jika ada konflik dengan README atau instruksi turunan, keputusan aktif menang dan dokumen turunan wajib diselaraskan

## Relasi dengan Dokumen Lain

- sebelum keputusan lahir, diskusi sumber wajib difinalkan dan diarsipkan
- jika keputusan butuh eksekusi, turunkan ke `03 Rencana Kerja`
- jika keputusan butuh panduan teknis, turunkan ke `11 Referensi Teknis`
- selama topik masih berada pada level diskusi dan belum final secara lifecycle, jangan lahirkan keputusan baru maupun dokumen turunan lain hanya karena substansinya terasa sudah jelas
- jika user meminta manual, referensi, spesifikasi, blueprint, SOP, template implementasi, atau referensi teknis saat diskusi belum final, tahan dulu dokumen turunan itu dan catat kebutuhannya di diskusi aktif
- detail implementatif seperti checklist teknis, setup remote, atau panduan langkah kerja hidup di `11 Referensi Teknis` kecuali keputusan normatif baru benar-benar membutuhkannya

