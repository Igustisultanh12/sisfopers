# Project Status

Status repo: `parsial`
Tanggal pembaruan: 20 Juli 2026
Domain: `kctrimatra`
Framework target: `Laravel 13`
PHP target: `^8.3`

## Ringkasan

Repo ini sudah melewati tahap bootstrap awal. Struktur dokumentasi lifecycle sudah dibuat dan kode aplikasi Laravel 13 sudah hadir secara parsial, dengan modul auth, register, SKEP, personel, pendidikan, pekerjaan, broadcast, OTP, report/export, dan dashboard berbasis Laravel/Inertia/Vue. Dependency JavaScript sudah tersedia lokal melalui `node_modules`, tetapi dependency PHP belum terpasang karena `vendor/` belum tersedia. Database aktif lokal belum terverifikasi. Implementasi workplan sinkronisasi NIKC sudah selesai secara lokal pada 20 Juli 2026 dan sedang menunggu review user sebelum penutupan lifecycle/arsip final.

Catatan penting: pada domain struktur organisasi dan akses permanen, sempat terjadi drift implementasi karena sinkronisasi `komandan -> koordinator` dilakukan saat topik masih berada pada fase `04 Diskusi`. Kondisi ini harus dibaca sebagai pelanggaran lifecycle dokumentasi, bukan sebagai legitimasi keputusan final.

Konvensi istilah aktif:

- backend dan naming kode tetap memprioritaskan `personel`
- display dan teks user-facing kini juga diarahkan memakai `personel`
- `SINYALEMEN` adalah istilah user-facing baku; nama teknis kode yang masih bertahan tetap memakai `Sinyalemen` sebagai alias transisi sampai refactor khusus dilakukan

## Kondisi Saat Ini

| Area | Status | Catatan |
| --- | --- | --- |
| Struktur docs | `berjalan` | Folder governance awal sudah tersedia |
| Kode aplikasi Laravel | `parsial` | Skeleton dan beberapa modul utama sudah ada |
| Keputusan domain | `parsial` | Sebagian domain sudah didiskusikan, sebagian masih menunggu keputusan |
| Workplan implementasi | `menunggu review` | Workplan NIKC tetap berada di `docs/03 Rencana Kerja/2026.07.09 Rencana Kerja tentang Sinkronisasi Implementasi NIKC.md` atas instruksi user untuk diperiksa sebelum arsip final |
| Workplan sinkronisasi personel | `tidak terverifikasi dalam audit ini` | Dokumen lama menyebut peleburan `users` ke `personels`, tetapi kondisi ini belum diaudit ulang pada 20 Juli 2026 |
| Workplan artefak SK, grup, dan pangkat | `tidak terverifikasi dalam audit ini` | Dokumen lama menyebut artefak `Surat Keputusan`, tetapi kondisi ini belum diaudit ulang pada 20 Juli 2026 |
| Referensi teknis | `parsial` | Sudah ada referensi teknis UI, namun domain lain masih menunggu keputusan final |
| Composer proyek | `tersedia sebagai composer.json/lock` | `composer.json` dan `composer.lock` ada, tetapi `vendor/` belum tersedia dan wrapper `scripts/composer.ps1`/`scripts/composer.cmd` tidak ditemukan di repo |
| Frontend dependency | `tersedia lokal` | `node_modules` dan `package-lock.json` ada di workspace lokal |

## Implikasi Kerja

- diskusi domain baru wajib dimulai di `docs/04 Diskusi/`
- keputusan final aktif sudah ada di `docs/02 Keputusan/`
- sinkronisasi berikutnya perlu menutup gap antara implementasi aktual dan baseline dokumen
- jika ditemukan implementasi yang mendahului keputusan formal, statusnya harus dicatat sebagai drift implementasi dan tidak boleh langsung dianggap norma aktif
- implementasi NIKC sudah selesai secara lokal melalui workplan aktif dan menunggu review user sebelum dipindahkan ke arsip selesai
- UI login publik menampilkan `NIKC` saja; dukungan login username masih ditunda sebagai jalur backend/internal admin dan tidak ditampilkan ke user publik.
- Karena production sudah berjalan dan push GitHub berdampak langsung ke live, risiko migration ganda harus ditangani melalui strategi deploy kompatibel production, bukan asumsi fresh install.
- Master referensi NIKC kini disiapkan juga melalui migration upsert agar deploy live tidak bergantung pada eksekusi seeder manual.
- Migration NIKC terbaru memakai guard tabel/kolom untuk mengurangi risiko gagal pada production yang sudah pernah ditambal manual.
- Endpoint provinsi register memiliki fallback lokal dari `master_provinsi`; kabupaten/kecamatan/desa dapat memakai `wilayah.id` saat tersedia, tetapi tidak lagi memblokir registrasi bila API turun.
- Approval pendaftaran tanpa NIKC kini ditolak sebelum aktivasi akun.
- sinkronisasi personel lintas auth/profile/admin sekarang membaca `personels` sebagai model auth tunggal
- karena repo belum dipasang dan belum ada database aktif, koreksi schema dilakukan pada migration agar instalasi awal nanti tidak membuat tabel `users` sama sekali dan langsung membawa field keputusan personel
- `SINYALEMEN` adalah istilah user-facing baku; nama teknis `Sinyalemen` yang tersisa diperlakukan sebagai alias transisi kode sampai ada refactor tersendiri
- `avatar` dan `MFA` tetap berstatus `hold`; jalur profile tidak boleh mengaktifkan keduanya sebelum ada keputusan baru
- artefak `Surat Keputusan` pernah dicatat sebagai sumber reusable lintas grup angkatan dan penyesuaian pangkat, tetapi status aktualnya belum diaudit ulang dalam pembaruan ini.
- Composer adalah dependency permanen proyek Laravel 13. Di environment lokal ini `vendor/` belum tersedia; perintah artisan penuh dapat gagal sampai dependency PHP terpasang.
- OCR/import SK memakai binary Poppler/Tesseract melalui `config/ocr.php`; upload KTP/ijazah/sertifikat mengantrikan OCR bantu dengan hasil teks private + confidence/error, sedangkan SK massal memakai structured import. Health lokal saat ini mendeteksi `pdftotext`, tetapi `pdftoppm` dan `tesseract` belum tersedia sebagai runtime aplikasi stabil; QA end-to-end tetap menunggu DB/queue/sampel PDF/dokumen dan binary lengkap.
- WhatsApp gateway operasional berstatus `hold/dormant`: file job, log, setting, dan halaman monitoring tetap ada sebagai artefak yatim, tetapi dispatch WA dan ping gateway tidak dipakai sampai diskusi broadcast/notifikasi melahirkan keputusan final.
- target migration `fresh install` kini sudah dirapikan agar tidak lagi membawa migration transisi untuk backfill atau no-op yang hanya relevan bagi database lama
- verifikasi wajah saat ini belum aktif di route produksi; file controller, model, middleware, view, dan migration dasar sudah disiapkan sebagai modul dormant, sedangkan `face recognition` otomatis tetap ditunda sampai diskusi khususnya selesai
- `AGENTS.md` sudah tersedia sebagai pedoman umum repo dan harus dibaca sebelum workplan, bersama `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`.

## Trigger Update Dokumen Ini

Perbarui dokumen ini jika:

- skeleton Laravel 13 sudah dibuat
- struktur folder aplikasi utama sudah tersedia
- database, auth, queue, cache, atau integrasi dasar sudah dipilih
- proses deploy, environment, atau remote repo sudah ditetapkan
- ada mismatch besar antara baseline dokumen dan kode aktual
- workplan peleburan `users` ke `personels` sudah mencapai fase verifikasi lokal
- dependency PHP (`vendor/`) sudah tersedia atau strategi Composer lokal sudah ditetapkan
