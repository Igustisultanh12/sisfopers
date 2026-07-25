# AGENTS

Panduan ini berlaku untuk agent yang bekerja di repo `kctrimatra`.

## Prinsip Utama

- repo ini adalah aplikasi Laravel 13 parsial/aktif dalam tahap bootstrap lanjutan
- `docs/` adalah source of truth dokumentasi kerja
- keputusan aktif di `docs/02 Keputusan/` adalah hirarki normatif tertinggi untuk governance dokumen
- jangan melompati lifecycle dokumen: `04 Diskusi` -> `00 Arsip` -> `02 Keputusan` -> `03 Rencana Kerja`
- jangan membuat keputusan final atau workplan formal dari topik yang masih berupa diskusi
- eksekusi workplan wajib mengikuti `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`
- urutan publish wajib: `commit` -> `push` -> `deploy`

## Urutan Baca Minimum

1. `AGENTS.md`
2. `docs/README.md`
3. `docs/CHANGELOG.md`
4. dokumen lifecycle yang sedang dikerjakan
5. dokumen profil sistem atau keputusan yang dirujuk

Jika menjalankan workplan, baca juga:

1. `docs/03 Rencana Kerja/README.md`
2. `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`
3. keputusan sumber yang dirujuk workplan

## Protokol Workplan

- kerjakan fase secara berurutan
- selesaikan dan verifikasi satu fase sebelum lanjut ke fase berikutnya
- catat status fase di workplan setelah verifikasi lulus
- hentikan pekerjaan jika ada ambiguitas yang mengubah perilaku sistem, verifikasi gagal, atau operasi irreversible belum disetujui
- commit lokal setelah batch/fase kecil yang lulus verifikasi

## Guardrail

- jangan akses file di luar `C:\laragon\www\kctrimatra\` tanpa konfirmasi eksplisit user
- jangan ubah dokumen arsip menjadi source of truth aktif
- jangan rename file diskusi menjadi keputusan
- jangan biarkan workplan selesai tetap tinggal di `docs/03 Rencana Kerja/`
- gunakan delta edit minimum yang memadai; jangan `hapus lalu buat ulang` bila `revisi` atau `rename` sudah cukup
- gunakan urutan kerja dokumen yang hemat jejak: `revisi -> rename -> arsip -> file baru`
- jika repo aktual berbeda dari baseline dokumen, perbarui `docs/01 Profil Sistem/PROJECT_STATUS.md` lebih dulu sebelum memperluas implementasi
- saat ini `vendor/` belum tersedia di repo lokal; verifikasi Laravel penuh dapat terblokir sampai dependency PHP terpasang
