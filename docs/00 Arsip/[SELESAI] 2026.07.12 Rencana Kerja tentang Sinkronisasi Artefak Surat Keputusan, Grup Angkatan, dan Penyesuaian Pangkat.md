# Rencana Kerja
## tentang SINKRONISASI ARTEFAK SURAT KEPUTUSAN, GRUP ANGKATAN, DAN PENYESUAIAN PANGKAT

Status: `Selesai (Diarsipkan)`
Tanggal dibuat: 12 Juli 2026
Tanggal arsip: 12 Juli 2026
Identitas dokumen: 2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat
Arsip: `docs/00 Arsip/[SELESAI] 2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat.md`
Jenis dokumen: Rencana Kerja
Domain: kctrimatra
Topik: rumah referensi internal, artefak `Surat Keputusan`, identitas `Grup Angkatan`, hak multi-grup, reuse `SK`, penyesuaian pangkat massal, dan sinkronisasi UI/UX turunan
Keputusan terkait: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`, `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`, `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`, `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`
Rencana kerja induk: -
Mengubah: -
Digantikan oleh: -

## Instruksi Baca Wajib

Sebelum mengubah file apa pun, baca berurutan:

1. `AGENTS.md`
2. `docs/README.md`
3. `docs/CHANGELOG.md`
4. `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
5. `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`
6. `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
7. `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`
8. `docs/11 Referensi Teknis/2026.11.003 tentang Kontrak Teknis Grup Angkatan dan Administrasi Personel.md`
9. `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`

## Tujuan

- menyelaraskan rumah `10 Referensi Internal` dengan taxonomy regulasi yang sudah diputuskan
- menyiapkan kontrak dokumen dan metadata untuk `Surat Keputusan` sebagai artefak sumber domain aktif
- menurunkan norma `Grup Angkatan` berbasis `Matra-Tahun-Abituren-Batch` ke file aktif, schema, dan UI
- menyiapkan alur reuse satu `Surat Keputusan` lintas grup angkatan dan penyesuaian pangkat tanpa drift
- menyiapkan alur penyesuaian pangkat berbasis `Surat Keputusan` massal yang tetap menjaga checkpoint verifikasi
- menutup ambiguitas istilah user-facing antara `Tahun Angkatan`, `Grup Angkatan`, `Abituren`, `TMT Penetapan`, dan `Surat Keputusan`

## Kondisi Stop

Hentikan pekerjaan jika:

- keputusan `2026.Kep.001`, `2026.Kep.003`, `2026.Kep.004`, atau `2026.Kep.005` berubah lagi dan memengaruhi norma yang sedang diturunkan
- ditemukan implementasi aktif yang bertentangan langsung dengan keputusan, tetapi kontrak teknisnya belum dapat dipetakan aman
- perubahan mulai menyentuh alur hukum atau data sensitif yang belum punya keputusan tertulis
- dibutuhkan migrasi destruktif atau perubahan data runtime yang tidak aman untuk fresh install tanpa pemetaan ulang
- kebutuhan UI/UX baru bertentangan dengan pedoman aktif dan belum dicatat sebagai drift

## Klasifikasi Operasi

| Jenis | Contoh | Tindakan |
| --- | --- | --- |
| Reversible | revisi README, keputusan turunan, referensi teknis, controller, request, view | kerjakan dan verifikasi lokal |
| Semi-reversible | revisi migration fresh install, relasi baru, indeks baru, metadata baru | kerjakan dengan audit ekstra |
| Irreversible | perubahan data existing yang menghapus histori atau memutus relasi sah | jangan eksekusi tanpa konfirmasi eksplisit user |

## Status Eksekusi

| Fase | Status | Ringkasan |
| --- | --- | --- |
| 1 | Selesai | sinkronisasi keputusan, arsip diskusi final, dan rujukan silang sudah hidup di repo |
| 2 | Selesai | penataan rumah `10 Referensi Internal` dan kontrak artefak `Surat Keputusan` sudah diturunkan ke modul admin |
| 3 | Berjalan | schema dan runtime grup tunggal aktif sudah memakai identitas `Matra-Tahun-Abituren-Batch`, tetapi hak multi-grup sah masih menunggu pematangan diskusi struktur organisasi |
| 4 | Berjalan | pencatatan artefak admin, pencocokan entri, dan proses per orang sudah hidup, tetapi jalur personel, bypass `Super Admin`, dan penutupan governance lintas domain masih menunggu diskusi terkait |
| 5 | Berjalan | audit UI/UX, verifikasi akhir, dan penutupan jejak kerja menunggu QA manual user |

## Temuan Audit Pra-Perbaikan

- tiga diskusi final baru memuat clause lintas `referensi internal`, `grup angkatan`, `NIKC`, dan `penyesuaian pangkat`, tetapi keputusan aktif sebelumnya belum menangkap seluruh detail itu
- `Surat Keputusan` sudah hidup sebagai konsep domain, tetapi README rumah referensi internal belum sepenuhnya mencerminkan taxonomy dan pola artefaknya
- keputusan master data lama masih bertumpu pada identitas grup `Matra-Tahun-Batch` dan belum mengunci `abituren` sebagai bagian identitas formal
- keputusan pangkat lama belum mengunci reuse satu `Surat Keputusan` massal, checkpoint verifikasi, dan pencatatan entri lampiran yang belum cocok
- relasi antara hak multi-grup, perubahan pangkat, dan pengecualian `NIKC` perlu tetap dipisahkan agar runtime tidak melahirkan perubahan implisit lintas domain

## Fase 1 - Sinkronisasi Keputusan dan Arsip Lifecycle

### Tujuan

- menormalkan keputusan aktif ke format `2026.Kep.xxx`
- mengarsipkan tiga diskusi final ke `00 Arsip`
- memastikan workplan dan README aktif menunjuk keputusan yang benar

### Klasifikasi

- reversible

### File yang disentuh

- `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
- `docs/02 Keputusan/2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan.md`
- `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`
- `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`
- `docs/02 Keputusan/README.md`
- `docs/00 Arsip/[DITETAPKAN] 2026.07.10 Diskusi tentang Referensi Internal Regulasi.md`
- `docs/00 Arsip/[DITETAPKAN] 2026.07.11 Diskusi tentang Identitas Grup Angkatan Berbasis Abituren dan Surat Keputusan.md`
- `docs/00 Arsip/[DITETAPKAN] 2026.07.12 Diskusi tentang Mekanisme Penyesuaian Pangkat Berbasis Surat Keputusan.md`

### Verifikasi fase

- pastikan keputusan aktif sudah memuat clause final yang dipetakan dari diskusi
- pastikan nama file keputusan sudah patuh ke `2026.Kep.xxx`
- pastikan tiga diskusi final berpindah ke arsip dengan header penetapan yang jujur

### Langkah jika gagal

- hentikan propagasi rujukan silang
- jangan turunkan fase implementasi sebelum keputusan dan arsip stabil

## Fase 2 - Penataan Referensi Internal dan Artefak Surat Keputusan

### Tujuan

- menyelaraskan README `10 Referensi Internal`
- mengunci rumah artefak `Surat Keputusan`
- menyiapkan struktur minimum `satu artefak + satu ringkasan + satu indeks nama`

### Klasifikasi

- reversible

### File yang disentuh

- `docs/10 Referensi Internal/README.md`
- dokumen atau folder referensi internal terkait `Keputusan Menteri`
- referensi teknis turunan baru bila benar-benar diperlukan

### Verifikasi fase

- pastikan taxonomy `Undang-Undang`, `Peraturan`, `Peraturan Menteri`, `Keputusan Menteri`, `Edaran`, dan `Lainnya` tertulis jelas
- pastikan `Surat Keputusan` massal diposisikan sebagai satu artefak sumber yang dapat direuse
- pastikan jalur bypass `Super Admin` dan syarat lampiran jalur `personel` tidak tertukar

### Langkah jika gagal

- tahan implementasi UI/UX dokumen
- catat drift ke workplan ini, jangan tebak kontrak teknisnya

## Fase 3 - Sinkronisasi Grup Angkatan Berbasis Surat Keputusan

### Tujuan

- menurunkan identitas `Matra-Tahun-Abituren-Batch`
- memastikan `Surat Keputusan` menjadi sumber formal grup
- menjaga hak multi-grup tetap berbasis penetapan yang sah

### Klasifikasi

- semi-reversible

### File yang disentuh

- migration, model, controller, request, dan UI grup angkatan
- kontrak teknis grup angkatan dan administrasi personel
- modul personel yang memilih `Grup Angkatan` dari master

### Verifikasi fase

- pastikan istilah `Abituren`, `Tahun Angkatan`, dan `Grup Angkatan` tampil tegas dan tidak saling menggantikan
- pastikan grup `arsip` tetap tertutup untuk anggota baru
- pastikan satu `Surat Keputusan` dapat menjadi dasar bagi lebih dari satu grup
- pastikan hak multi-grup tidak muncul tanpa dasar `Surat Keputusan` yang sah

### Langkah jika gagal

- jangan lanjut ke sinkronisasi pangkat berbasis `SK` yang bergantung pada grup baru
- pecah gap teknis ke referensi teknis jika memang perlu

## Fase 4 - Sinkronisasi Penyesuaian Pangkat Berbasis Surat Keputusan Massal

### Tujuan

- menyiapkan reuse `Surat Keputusan` lintas pengajuan pangkat
- menyiapkan checkpoint verifikasi
- menyiapkan pencatatan entri lampiran yang belum cocok ke database

### Klasifikasi

- semi-reversible

### File yang disentuh

- modul pengajuan pangkat
- tabel dan relasi riwayat pangkat
- halaman verifikasi dan reuse `Surat Keputusan`
- referensi teknis penyesuaian pangkat bila nanti dilahirkan

### Verifikasi fase

- pastikan aktor pemrosesan penyesuaian pangkat berbasis `SK` adalah role `admin`, yang dalam kemudahan sebutan merujuk pada `Super Admin` (lihat catatan klarifikasi di Diskusi Akun 2026.07.03); runtime `role:admin` pada `SuratKeputusanController::applyRankAdjustment` sudah mencakup aktor tersebut. Pemisahan `admin` dari `Super Admin` kelak hanya terjadi bila lahir keputusan terpisah.
- pastikan pengajuan `personel` dapat menyumbang softcopy untuk reuse
- pastikan sistem membuka jalur pencocokan massal otomatis dan pemrosesan manual per entri
- pastikan perubahan pangkat tidak diam-diam mengubah `NIKC` atau `Grup Angkatan`

### Langkah jika gagal

- hentikan perubahan status pangkat otomatis
- turunkan gap ke backlog workplan ini

## Fase 5 - Audit UI/UX, Verifikasi Final, dan Penutupan

### Tujuan

- memastikan pola UI/UX turunan tetap patuh ke pedoman aktif
- memastikan seluruh dokumen aktif dan referensi teknis tidak drift
- menyiapkan penutupan workplan yang jujur

### Klasifikasi

- reversible

### File yang disentuh

- halaman admin dan user-facing yang menampilkan `Grup Angkatan`, `Abituren`, `SK`, dan status penyesuaian pangkat
- dokumen README, keputusan, dan referensi teknis terkait

### Verifikasi fase

- pastikan label human-readable konsisten di daftar, detail, filter, dan histori
- pastikan tidak ada istilah lama yang masih rancu antara `angkatan`, `tahun`, dan `grup angkatan`
- pastikan workplan ini hanya ditutup setelah QA user atau `skip QA` eksplisit

### Langkah jika gagal

- jangan arsipkan workplan
- catat gap secara eksplisit di bagian QA dan penutupan

## Verifikasi Final

Verifikasi final minimal yang diharapkan:

- audit keputusan aktif dan arsip final sudah sinkron
- audit README rumah keputusan dan referensi internal sudah sinkron
- audit kontrak istilah `SK`, `TMT Penetapan`, `Abituren`, `Tahun Angkatan`, dan `Grup Angkatan` sudah konsisten
- audit bahwa perubahan pangkat, hak grup, dan pengecualian `NIKC` tetap dipisahkan secara normatif

## Hasil Verifikasi Lokal

- `npm run build` lulus setelah modul admin `Artefak Surat Keputusan`, relasi grup angkatan, dan form reuse SK ditambahkan
- binary `php` tidak tersedia di `PATH` shell ini, sehingga `php -l`, `php artisan about`, dan `php artisan test` belum dapat dijalankan dari sesi kerja ini
- keputusan aktif `2026.Kep.001`, `2026.Kep.003`, `2026.Kep.004`, dan `2026.Kep.005` tetap menjadi dasar normatif batch implementasi ini
- grup angkatan sekarang membawa `abituren`, `surat_keputusan_artifact_id`, dan label user-facing berbasis `Matra-Tahun-Abituren-Batch`
- admin kini dapat mencatat artefak `Surat Keputusan`, mengunggah PDF opsional, menyimpan indeks nama, mencocokkan entri ke personel, dan memproses penyesuaian pangkat tanpa mengubah `NIKC` atau `Grup Angkatan`

## Catatan Perubahan Detail

- workplan ini lahir karena sinkronisasi pascakeputusan dinilai kompleks dan lintas beberapa clause domain
- workplan ini tidak mengubah tafsir keputusan; ia hanya memetakan area implementasi, dokumentasi, dan verifikasi yang harus diselaraskan
- batch implementasi aktif melahirkan tabel `surat_keputusan_artifacts` dan `surat_keputusan_personel_entries` untuk fresh install
- modul admin baru `Artefak Surat Keputusan` kini menangani metadata `nomor surat`, `tentang`, `tanggal surat`, taxonomy, ringkasan, PDF opsional, dan indeks nama
- `Grup Angkatan` kini wajib memilih artefak `Surat Keputusan` sumber dan membawa field `abituren` canonical
- form edit personel kini dapat reuse artefak `Surat Keputusan` yang sudah ada saat memproses perubahan pangkat
- proses penyesuaian pangkat massal dilakukan per entri lampiran dengan checkpoint pencocokan personel, dasar pengajuan, dan catatan verifikasi
- audit migration fresh install menutup residu `backfill` dan `no-op` dengan mempertahankan hanya migration yang benar-benar dibutuhkan instalasi baru
- audit yang sama menegaskan bahwa hak multi-grup sah dan jalur unggah softcopy personel belum boleh diklaim selesai di workplan ini sebelum diskusi terkait difinalkan; untuk aktor pemrosesan `SK` massal, klarifikasi user menetapkan bahwa `admin` adalah sebutan harian untuk `Super Admin` (jika kelak keduanya berbeda, akan lahir keputusan terpisah), sehingga runtime `role:admin` pada `SuratKeputusanController` sudah mencakup aktor tersebut dan klausul verifikasi tidak lagi menggantung pada keputusan domain yang belum lahir
- audit 12 Juli 2026 menemukan bahwa referensi teknis aktif sempat tertinggal pada identitas `Matra-Tahun-Batch`; kontrak dokumentasi kini harus dibaca sebagai `Matra-Tahun-Abituren-Batch`
- workplan ini adalah pemilik tunggal artefak `Surat Keputusan` (`surat_keputusan_artifacts`, `surat_keputusan_personel_entries`, modul admin `Artefak Surat Keputusan`, dan `SuratKeputusanController`); workplan `2026.07.10` hanya boleh merujuk artefak ini, tidak menarasikan ulang implementasinya

## Status Commit Lokal

- perubahan runtime, schema fresh install, dan dokumen batch ini masih berada pada tahap kerja lokal dan belum diarsipkan

## Catatan QA dan Penutupan

- workplan ini tidak boleh diarsipkan sebelum ada QA user atau `skip QA` eksplisit
- bila sebagian fase selesai tetapi masih ada gap lintas UI/UX, workplan tetap aktif sampai seluruh drift utama ditutup
- fase 3 dan fase 4 tetap dibaca sebagai `Berjalan` sampai:
  - model operasional hak multi-grup sah difinalkan pada diskusi struktur organisasi
  - jalur registrasi atau unggah softcopy personel yang relevan difinalkan pada diskusi akun dan verifikasi
  - nomenklatur `Super Admin` diselaraskan ke sebutan harian `admin` per klarifikasi user (admin adalah sebutan harian Super Admin; pemisahan baru terjadi bila lahir keputusan terpisah); tidak lagi menjadi blocker workplan ini
