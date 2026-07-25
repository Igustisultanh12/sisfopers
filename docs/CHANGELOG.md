# Changelog Docs

Catatan perubahan repo lokal yang masih aktif. Entri historis lama berada di `docs/CHANGELOG_OLD.md`.

## 2026-07-20

- mencatat audit pull `https://github.com/Igustisultanh12/sisfopers1.git` ke `6944465` pada diskusi NIKC, termasuk batas bahwa permintaan semula adalah audit/analisis dan perubahan kode yang terlanjur dilakukan harus diperlakukan sebagai tambalan parsial yang perlu diturunkan ke rencana kerja.
- mencatat temuan bocor markup pesan validasi NIKC 17 digit dan kontras nama file upload pada register publik; working tree sempat ditambal pada `Register.vue`, `useSwal.js`, `AuthController.php`, `SkepPublicController.php`, dan `RegisterPersonelRequest.php`.
- mencatat hasil verifikasi ringan: PHP lint untuk tiga file backend lulus, `node --check` untuk `useSwal.js` lulus, `git diff --check` lulus; build frontend penuh masih terblokir karena `vendor/tightenco/ziggy` belum tersedia dan `composer.lock` tidak sinkron dengan `composer.json`.
- mencatat penyelarasan diskusi bahwa perbedaan antara dokumen dan repo terjadi karena jalur kerja paralel, bukan pelanggaran; statusnya menjadi kebutuhan sinkronisasi sebelum finalisasi.
- mencatat hasil audit `ApproveRegistrationAction.php`: file berisi generator `KC-{tahun}.{matra}.{random}` yang menulis ke `personels.nikc`, tetapi tidak ditemukan sebagai jalur approval aktif; aman sebagai kandidat hapus melalui rencana kerja setelah verifikasi referensi aktif.
- menyiapkan checklist pra-finalisasi diskusi dan revisi rencana kerja aktif agar tambalan parsial 20 Juli 2026, fase penghapusan action legacy, fase seeder, serta blocker verifikasi lokal tercatat.
- mencatat audit pull lanjutan ke `6acf367`: surface baru verifikasi pendidikan hanya baca/search NIKC, scope wilayah perlu mencakup `Personel/JobHistory`, route API wilayah masih memakai `emsifa`, dan `JobHistoryController.php` memiliki blocker syntax yang perlu diputuskan sebelum finalisasi diskusi.
- memperbaiki blocker syntax `JobHistoryController.php`, memindahkan proxy `/api/wilayah` ke `wilayah.id`, dan mencatat hasilnya di diskusi NIKC sebagai perbaikan prasyarat sebelum diskusi dilanjutkan.
- mengarsipkan diskusi final NIKC ke `docs/00 Arsip/[DITETAPKAN] 2026.07.16 Diskusi tentang Audit dan Temuan Lanjutan Implementasi NIKC.md` dan mengarahkan rujukan workplan aktif ke arsip tersebut.
- menyelaraskan `AGENTS.md` dan `docs/01 Profil Sistem/PROJECT_STATUS.md` dengan kondisi repo aktual sebelum implementasi workplan NIKC dilanjutkan.
- menyelesaikan F0 workplan NIKC: validasi NIKC 17 digit angka dirapikan pada register backend, SKEP publik, master personel admin, dan import SKEP; lint PHP Laragon 8.3.30 lulus untuk file yang disentuh.
- menyelesaikan F1 workplan NIKC: menghapus `app/Actions/ApproveRegistrationAction.php` setelah audit referensi ulang memastikan action legacy/orphan tersebut tidak dipanggil kode aktif.
- menyelesaikan F2 workplan NIKC: normalisasi label login, pesan approval, redaksi `NIKC Belum Ada di Database`, dan alert HTML validasi NIKC; build frontend masih terblokir `vendor/tightenco/ziggy`.
- menyelesaikan F3 workplan NIKC: menambahkan `master_provinsi`, seed 34 provinsi Latsarmil, integrasi `/api/wilayah/provinces` dengan master lokal, dan validasi `province/provinsi` register serta riwayat pekerjaan ke master.
- menyelesaikan F4 workplan NIKC: menghapus fallback hardcoded penandatangan dan pangkat pada export/PDF laporan, memakai data nyata atau placeholder generik saat kosong.
- menyelesaikan F5 workplan NIKC: menambahkan DOB pada data/pengajuan SKEP, verifikasi publik NIKC + tanggal lahir, rate-limit 3 percobaan per 6 jam per IP, dan sinkronisasi UI register/admin SKEP.
- menyelesaikan F6 workplan NIKC: menambahkan `NikcFormatRule` untuk validasi struktur NIKC, DOB, kode provinsi Latsarmil dari `master_provinsi`, dan kecocokan provinsi pada jalur register/SKEP/admin.
- menyelesaikan F7 workplan NIKC: menambahkan `master_kepangkatan` 18 pangkat canonical, normalisasi pangkat legacy, validasi/dropdown berbasis master, dan pembersihan fallback `Prada` palsu.
- menyelesaikan F8 workplan NIKC: menyelaraskan label register publik, DOB verifier, opsi pangkat master, alert HTML NIKC, dan kontras upload.
- menyelesaikan F10 workplan NIKC: mengganti seed koordinator `NIKC-KORD-*` menjadi NIKC 17 digit valid dan pangkat canonical.
- menyelesaikan F9 workplan NIKC: audit akhir menemukan dan menyelaraskan drift UI kecil pada label/placeholder admin dan kordinator; `php -l -n` seluruh file PHP berubah lulus, `git diff --check` lulus, dan pencarian pola aktif lama tidak menemukan sisa di kode.
- menahan pemindahan workplan NIKC ke arsip atas instruksi user agar dokumen tetap bisa diperiksa sebelum lifecycle penutupan final.
- mencatat audit review workplan NIKC: UI login publik dan pesan approval user-facing harus hanya menampilkan `NIKC`; backend username ditunda karena hanya admin yang dapat mengubah username.
- memperbaiki admin create personel agar DOB dan provinsi wajib diisi serta dipakai oleh `NikcFormatRule`, sehingga NIKC yang diinput admin tidak lolos bila tidak cocok dengan bulan/tahun lahir atau kode provinsi Latsarmil.
- mencatat risiko migration `users` ganda sebagai perhatian deploy production terpisah karena aplikasi sudah running production dan push GitHub akan langsung berdampak ke live.
- menambahkan migration upsert master reference data NIKC agar `master_provinsi` dan `master_kepangkatan` terisi di production lewat migration, bukan bergantung pada seeder lokal.
- membuat migration NIKC production-safe terhadap tabel/kolom yang sudah ada, menambahkan fallback lokal `/api/wilayah/provinces`, menolak approval tanpa NIKC, dan membersihkan mojibake error DOB/provinsi pada admin create personel.
- menyesuaikan register production agar kabupaten/kecamatan/desa/kode pos boleh kosong di backend dan UI saat `wilayah.id` down, menambahkan endpoint `/api/wilayah/villages/{district_code}`, dan membuat UI kelurahan/desa memakai data `wilayah.id` dengan fallback input manual.
