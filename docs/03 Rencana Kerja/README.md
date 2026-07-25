# 03 Rencana Kerja

Folder ini berisi workplan aktif yang diturunkan dari keputusan final.

## Nama File Wajib

- gunakan format `YYYY.MM.DD Rencana Kerja tentang Judul Dokumen.md`
- nomor urut tidak dipakai lagi untuk workplan aktif
- field `Identitas dokumen` wajib mengikuti nama file aktif tanpa ekstensi
- prefix status tidak dipakai di folder aktif
- saat selesai, pindahkan ke `00 Arsip` dengan prefix `[SELESAI]`

## Aturan Inti

1. Setiap workplan wajib merujuk keputusan aktif di `02 Keputusan`.
2. Workplan tidak boleh melahirkan keputusan baru atau mengubah tafsir keputusan.
3. Workplan formal baru tidak boleh lahir dari topik yang masih berstatus diskusi; jika domainnya belum punya keputusan final, kebutuhan kerja harus tetap ditahan di dokumen diskusi.
4. Jika perlu panduan teknis tambahan, workplan boleh merujuk `11 Referensi Teknis`.
5. Workplan selesai wajib diarsipkan.
6. Jika QA user atau audit pasca-penutupan membuktikan pekerjaan belum tuntas, workplan yang terlanjur diarsipkan wajib dipindahkan kembali ke `03 Rencana Kerja/`, statusnya diaktifkan ulang, dan waktu temuan gap dicatat jujur.
7. Setiap workplan formal aktif wajib memuat heading eksplisit: `Instruksi Baca Wajib`, `Tujuan`, `Kondisi Stop`, `Klasifikasi Operasi`, `Status Eksekusi`, `Verifikasi Final`, `Hasil Verifikasi Lokal`, `Catatan Perubahan Detail`, `Status Commit Lokal`, dan `Catatan QA dan Penutupan`.
8. Setiap fase implementasi wajib memuat blok eksplisit: `Tujuan`, `Klasifikasi`, `File yang disentuh`, `Verifikasi fase`, dan `Langkah jika gagal`.
9. Workplan yang belum memuat seluruh heading dan blok wajib tersebut belum sah sebagai workplan formal aktif yang self-contained.
10. Validasi workplan harus berbasis teks normatif yang benar-benar tertulis pada keputusan aktif dan instruksi turunan, bukan asumsi.
11. Jika sinkronisasi turunan dari keputusan hanya ringan dan terbatas, perubahan boleh langsung dikerjakan tanpa workplan baru; jika banyak atau kompleks, wajib lahir workplan formal.

## Catatan Bootstrap

Karena repo ini masih baru, workplan pertama kemungkinan besar berkaitan dengan:

- bootstrap skeleton Laravel 13
- penetapan struktur modul awal
- setup environment lokal
- baseline auth, database, dan deploy
