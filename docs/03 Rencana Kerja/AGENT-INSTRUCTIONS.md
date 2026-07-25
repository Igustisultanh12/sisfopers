# Instruksi Wajib untuk AI Agent

Dokumen ini adalah protokol eksekusi untuk setiap file di `docs/03 Rencana Kerja/`. Baca dokumen ini sebelum menjalankan workplan apa pun.

## 1. Konteks Teknis Minimum

- Stack target: Laravel 13, PHP 8.3+, MySQL atau MariaDB
- OS lokal utama: Windows, Laragon
- repo saat ini masih tahap bootstrap; jangan mengasumsikan command, package, atau struktur file sudah tersedia jika belum diverifikasi

## 2. Urutan Baca Wajib

Sebelum mengubah satu file pun, baca urutan berikut:

1. `AGENTS.md`
2. `docs/README.md`
3. `docs/CHANGELOG.md`
4. workplan aktif yang sedang dijalankan
5. keputusan sumber yang dirujuk workplan
6. dokumen profil sistem atau referensi lain yang dirujuk keputusan

Jika salah satu dokumen wajib tidak ditemukan, berhenti dan laporkan ke user.

## 3. Protokol Sebelum Eksekusi

1. Pastikan nama file workplan mengikuti format `YYYY.MM.DD Rencana Kerja tentang Judul Dokumen.md`.
2. Pastikan workplan merujuk minimal satu keputusan aktif.
3. Pastikan setiap fase memiliki:
   - tujuan
   - klasifikasi operasi
   - daftar file yang disentuh
   - verifikasi fase
   - langkah jika gagal
4. Pastikan workplan formal aktif memuat heading eksplisit:
   - `Instruksi Baca Wajib`
   - `Tujuan`
   - `Kondisi Stop`
   - `Klasifikasi Operasi`
   - `Status Eksekusi`
   - `Verifikasi Final`
   - `Hasil Verifikasi Lokal`
   - `Catatan Perubahan Detail`
   - `Status Commit Lokal`
   - `Catatan QA dan Penutupan`
5. Jika domain yang akan dikerjakan masih berada pada level diskusi dan belum punya keputusan final, jangan lahirkan workplan baru.
6. Jika user meminta manual, referensi, spesifikasi, blueprint, SOP, template implementasi, atau referensi teknis saat topik masih diskusi, jangan pakai workplan sebagai jalan pintas; catat dulu kebutuhan itu di dokumen diskusi aktif.
7. Validasi workplan wajib berbasis teks normatif final yang benar-benar tertulis pada keputusan aktif, README, dan instruksi agent aktif.

## 4. Protokol Eksekusi Per Fase

1. Kerjakan fase secara berurutan.
2. Selesaikan satu fase penuh sebelum berpindah ke fase berikutnya.
3. Jalankan verifikasi fase segera setelah perubahan fase selesai.
4. Jangan menandai fase selesai jika verifikasi belum lulus.
5. Setelah fase lulus, perbarui status eksekusi workplan.
6. Buat commit lokal setelah fase kecil atau batch kecil yang lulus verifikasi.
7. Jika perubahan turunan dari keputusan ternyata hanya ringan dan terbatas, agent boleh menyelaraskan langsung tanpa melahirkan workplan baru; jika perubahan banyak atau kompleks, workplan formal wajib lahir.

## 5. Verifikasi Minimum

Gunakan verifikasi minimum berikut jika workplan tidak memberi instruksi yang lebih spesifik:

- `php -l` untuk semua file PHP yang diubah
- `php artisan about` bila skeleton Laravel sudah tersedia
- `php artisan test` bila project dan test suite sudah tersedia
- verifikasi domain-spesifik yang diwajibkan keputusan

Jika command Laravel belum tersedia karena repo masih tahap bootstrap, catat kondisi itu secara eksplisit di hasil verifikasi.

## 6. Klasifikasi Operasi

### Reversible

Contoh:

- edit file kode
- edit Blade
- edit config
- edit dokumentasi

Tindakan:

1. kerjakan
2. verifikasi
3. commit bila lulus

### Irreversible

Contoh:

- migration yang mengubah data existing
- hapus data
- hapus file penting
- ubah data lingkungan aktif

Tindakan:

1. tampilkan command yang akan dijalankan
2. minta konfirmasi eksplisit user
3. eksekusi hanya setelah user setuju
4. catat hasilnya di workplan dan laporan

## 7. Kondisi Stop

Berhenti dan laporkan ke user jika:

- keputusan sumber tidak ditemukan
- workplan bertentangan dengan kondisi repo aktual
- ada ambiguitas yang mengubah perilaku sistem
- verifikasi fase gagal
- operasi irreversible diperlukan tetapi belum dikonfirmasi
- scope kerja keluar dari repo tanpa izin eksplisit
- heading atau blok wajib workplan tidak tertulis eksplisit
- domain pekerjaan ternyata masih hidup sebagai diskusi dan belum punya keputusan final
- ada drift nyata antara keputusan aktif dan README atau instruksi turunan yang tidak bisa diselesaikan tanpa mengubah tafsir keputusan

## 8. Aturan Commit dan Push

- commit lokal dilakukan setelah fase kecil atau batch kecil lulus verifikasi
- push ditunda sampai seluruh workplan selesai, kecuali user meminta lain
- jangan push saat masih ada kondisi stop terbuka
- urutan publish wajib adalah `commit -> push -> deploy`
- jangan deploy dari perubahan yang belum di-commit atau belum di-push

## 9. Penutupan

Workplan baru dianggap selesai jika:

1. semua fase berstatus `Selesai`
2. verifikasi final lulus
3. `Selesai pada` terisi
4. file dipindahkan ke `docs/00 Arsip/` dengan prefix `[SELESAI]`

Aturan timestamp:

- field `Selesai pada`, `Dicabut pada`, atau timestamp `WIB` lain wajib memakai waktu eksekusi aktual `UTC+07:00`
- sebelum menulis timestamp, agent wajib cek jam sistem terlebih dahulu
