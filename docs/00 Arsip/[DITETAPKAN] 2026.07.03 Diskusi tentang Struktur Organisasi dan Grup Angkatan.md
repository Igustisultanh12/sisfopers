# Diskusi
## tentang STRUKTUR ORGANISASI DAN GRUP ANGKATAN

Status: `Ditetapkan`
Tanggal dibuka: 3 Juli 2026
Tanggal ditetapkan: 12 Juli 2026 20:15:35 WIB
Identitas dokumen: 2026.07.03 Diskusi tentang Struktur Organisasi dan Grup Angkatan
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: struktur organisasi personel, matra, TMT Penetapan, batch, koordinator, wakil koordinator, super admin, dan pengelompokan personel
Keputusan terkait: `docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md`, `docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md`, `docs/02 Keputusan/2026.Kep.006 tentang Struktur Organisasi dan Grup Angkatan.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `docs/02 Keputusan/2026.Kep.006 tentang Struktur Organisasi dan Grup Angkatan.md`

> Ditetapkan pada: 2026-07-12 20:15:35 WIB
> Keputusan yang lahir: `2026.Kep.006 tentang Struktur Organisasi dan Grup Angkatan`
> Jejak: diskusi ini difinalisasi dari status `Siap Difinalkan` dan menjadi source of truth normatif via keputusan di atas.

## Pemicu

Bahan kebutuhan user menempatkan struktur organisasi berbasis grup angkatan sebagai fondasi verifikasi dan pengelompokan personel. Setelah sebagian fondasi master data lahir menjadi keputusan di `2026.Kep.004`, diskusi ini perlu dipersempit agar tidak mengulang hal yang sudah normatif dan hanya membahas sisi organisasi permanen yang masih terbuka.

## Temuan Awal

- repo masih `bootstrap`, tetapi domain struktur permanen sudah tidak sepenuhnya kosong karena `2026.Kep.004` telah mengunci sebagian fondasi master data dan grup angkatan
- `2026.Kep.005` sudah mengunci hirarki 18 pangkat dengan kelompok NIKC `1` = Perwira (Letda KC ke atas), menjadi dasar gerbang "Wajib Perwira" untuk koordinator dan wakil koordinator
- diskusi akun dan diskusi role lapangan tetap membutuhkan dokumen ini sebagai rujukan, tetapi tidak boleh lagi mengulang hal yang sudah diputuskan di keputusan
- `2026.Kep.004` sudah menetapkan `TMT Penetapan` sebagai sumber `Tahun Angkatan`, identitas `Grup Angkatan` sebagai `Matra-Tahun-Abituren-Batch`, `abituren` sebagai bagian identitas, `Grup Angkatan` sebagai entitas master yang merujuk ke `Surat Keputusan`, dan aturan koordinator/wakil satu grup aktif, perpindahan wajib alasan, serta grup `arsip` tidak menerima anggota baru
- kondisi kode aktual sudah terlanjur disinkronkan ke tiga jalur dashboard permanen: `admin`, `koordinator`, dan `personel`, dengan `wakil koordinator` memakai dashboard `koordinator`
- istilah `komandan` sudah dihapus dari jalur runtime aktif dan diganti langsung ke `koordinator` tanpa alias transisi

### Catatan pelanggaran lifecycle (status disepakati)

Terjadi pelanggaran lifecycle pada topik ini. Saat status dokumen masih `04 Diskusi`, implementasi runtime sudah sempat diubah pada route, controller, page, store, seeder, folder UI, dan role database permanen.

**Status drift per 2026-07-12 (disepakati user):** drift implementasi `komandan -> koordinator` **dilegalisasi dan diselaraskan dalam keputusan `2026.Kep.006`** yang lahir dari diskusi ini. Catatan ini berubah status dari "pelanggaran murni" menjadi "drift yang diakui dan dilegalisasi lewat keputusan domain ini".

### Kondisi kode aktual

- data `matra` dan representasi `tahun angkatan` masih dipakai aktif sebagai filter personel dan target broadcast
- route, controller, page Inertia, store auth, seeder, dan folder UI permanen sudah terlanjur disinkronkan dari `komandan` ke `koordinator`
- akses dashboard `koordinator` sudah terlanjur dibuka untuk role `koordinator` dan `wakil koordinator`
- relasi runtime personel ke grup angkatan saat ini masih berbentuk satu `grup_angkatan_id` aktif pada satu waktu; hak multi-grup sah yang disebut keputusan belum punya model operasional final
- keputusan dan workplan sudah memakai istilah `super admin`, tetapi runtime role permanen yang aktif masih `admin`, `koordinator`, `wakil koordinator`, dan `personel`

## Batas Setelah Keputusan Terkait

Hal berikut sudah diputuskan di `2026.Kep.004` dan `2026.Kep.005` dan tidak dibahas ulang:

- `TMT Penetapan` adalah tanggal lengkap yang menjadi sumber `Tahun Angkatan`
- identitas `Grup Angkatan` memakai kombinasi `Matra-Tahun-Abituren-Batch`
- `batch` bersifat opsional
- `Grup Angkatan` adalah entitas master yang merujuk ke `Surat Keputusan`
- satu `koordinator` dan satu `wakil koordinator` hanya boleh terikat ke satu grup angkatan aktif pada satu waktu
- perpindahan grup wajib menyimpan alasan dan riwayat
- grup `arsip` tidak boleh menerima anggota baru
- gerbang pangkat Perwira merujuk ke kelompok NIKC `1` di `2026.Kep.005`

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Kebutuhan struktur organisasi personel | Sudah muncul jelas dari bahan user | Clear |
| 2 | Fondasi `Grup Angkatan` | Sudah diputuskan di `2026.Kep.004` | Clear |
| 3 | Paradigma personel + hak yang melekat | By default seluruhnya personel Komcad; koordinator, wakil, super admin adalah hak yang melekat | Clear |
| 4 | Batas antara role permanen dan role situasional | Sudah dipisah dokumennya | Clear |
| 5 | Nomenklatur `komandan` versus `koordinator` | Arah final jelas; drift dilegalisasi lewat `2026.Kep.006` | Clear |
| 6 | Dashboard `koordinator` untuk `wakil koordinator` | Sesuai kondisi kode | Clear |
| 7 | Verifikasi akun oleh `koordinator` | Hanya klaim awal; koordinator angkatan adalah verifikator, bukan pemberi tugas | Clear |
| 8 | Aksi `wakil koordinator` | Sama dengan koordinator, saling ganti; notif ke koordinator | Clear |
| 9 | Kewenangan `super admin` vs `admin` | `admin` = `Super Admin` (klarifikasi diskusi akun 2026-07-12) | Clear |
| 10 | Gerbang pangkat koordinator & wakil | Super Admin bebas pangkat; koordinator/wakil wajib Perwira | Clear |
| 11 | Penunjuk koordinator & wakil | Hanya Super Admin; arah SK, sementara boleh langsung; log tercatat | Clear |
| 12 | Data personel yang boleh dilihat koordinator/wakil | Terbatas 8 field; lengkap hanya Super Admin | Clear |
| 13 | Notifikasi organisasi permanen | Aturan & format minimum dikunci | Clear |
| 14 | Hak multi-grup sah berbasis `Surat Keputusan` | Model operasional jadi turunan teknis (`2026.11.004`) | Clear |
| 15 | Identitas Super Admin non-Komcad | NRP/NIK; amandemen `2026.Kep.003`; perluas `personels` | Clear |
| 16 | Koordinator Angkatan vs Koordinator Kegiatan | Koordinator Angkatan murni verifikator | Clear |

## Paradigma Personel dan Hak

- By default seluruh aktor adalah **personel Komponen Cadangan** (ber-NIKC, rujukan `2026.Kep.003`).
- Personel dapat ditunjuk menjadi `Super Admin`, `Koordinator`, atau `Wakil Koordinator` sebagai **hak yang melekat**.
- Hak dapat multiple dan menumpuk: seseorang bisa sekaligus `Koordinator` dan `Super Admin`.
- `Koordinator` dan `Wakil Koordinator` **tidak pernah** bisa melekat bersamaan pada orang yang sama (mutual exclusive), walau pada grup berbeda.
- Personel dapat punya **multi-grup angkatan** sesuai `Surat Keputusan` (Kep.004 klausul 24).
- `Koordinator` dan `Wakil Koordinator` **melekat ke tepat satu grup angkatan**.

Contoh (disepakati user):
> Prada KC A adalah personel grup `Matra Udara Reguler 2022`. A ikut pendidikan lagi dan menjadi Letda KC A grup `Matra Laut SPPI 2025` sekaligus diangkat `Koordinator` grup `Matra Laut SPPI 2025`.
> Maka A secara personel tercatat di `Matra Udara Reguler 2022` dan memegang hak `Koordinator` di `Matra Laut SPPI 2025`.

## Kewenangan & Notifikasi

### Kewenangan dasar

- `Super Admin` tidak dibatasi pangkat.
- `Koordinator` dan `Wakil Koordinator` **wajib Perwira** (kelompok NIKC `1` di `2026.Kep.005`): Letda KC ke atas.
- Kewenangan `Wakil Koordinator` sama persis dengan `Koordinator` dan keduanya saling menggantikan.
- `Koordinator Angkatan` adalah **verifikator anggotanya**, bukan pemberi tugas. Pemberian tugas/penugasan adalah ranah `Koordinator Kegiatan` di `docs/04 Diskusi/2026.07.11 Diskusi tentang Role Lapangan dan Struktur Kegiatan.md`.
- Yang menunjuk dan mengganti `Koordinator`/`Wakil Koordinator` hanya `Super Admin`; arah berdasarkan `Surat Keputusan`, sementara boleh ditunjuk langsung. Penunjukan dan pergantian tercatat dalam log.

### Aturan notifikasi (penerima)

| Aktor | Subjek aksi | Push notifikasi ke | Log (Wakil & Super Admin) |
| --- | --- | --- | --- |
| Wakil Koordinator | apa saja | `Koordinator` grup terkait | ya |
| Super Admin | anggota grup X | `Koordinator` grup X | ya |
| Koordinator | apa saja | — (tidak push) | ya (Wakil & Super Admin dapat melihat) |
| Sistem/struktural | pergantian Koord/Wakil, ubah identitas grup, pindah personel, ubah komposisi | `Koordinator` grup terkait | ya |

### Format teks notifikasi minimum

```
[{JENIS}] - Grup {MATRA-TAHUN-ABITUREN-BATCH}
Pelaku : {NAMA} ({HAK_PELAKU})
Subjek : {NAMA_SUBJEK} ({NIKC / NRP / NIK})
Aksi   : {DESKRIPSI_SINGKAT}
Waktu  : {YYYY-MM-DD HH:mm WIB}
Ref    : {SURAT_KEPUTUSAN_ID | LOG_ID}
```

`{JENIS}` untuk notifikasi struktural: `PERGANTIAN_KOORDINATOR`, `PERGANTIAN_WAKIL`, `UBAH_IDENTITAS_GRUP`, `PINDAH_PERSONEL`, `UBAH_KOMPOSISI`.

Jika subjek/pelaku punya hak di lebih dari satu grup, notifikasi dikirim ke **semua** `Koordinator` grup tempat subjek punya hak.

### Daftar perubahan grup yang "berdampak langsung" memicu notifikasi

1. Penggantian `Koordinator`
2. Penggantian `Wakil Koordinator`
3. Perubahan identitas grup yang mengubah struktur operasional
4. Pemindahan personel antar grup
5. Perubahan komposisi grup yang memengaruhi otoritas verifikasi atau target notifikasi
6. Aksi `Super Admin` terhadap anggota grup → notifikasi ke `Koordinator` grup tersebut

### Pemisahan ranah notifikasi (kesepakatan user)

- **Tetap di diskusi ini (aturan kewenangan):** siapa notifikasi ke siapa, format teks minimum, dan daftar perubahan grup berdampak langsung.
- **Dipindah/menjadi ranah diskusi Broadcast (`2026.07.09`):** trigger notifikasi klaim akun awal (sudah overlap Poin 6 diskusi Broadcast), channel distribusi (WhatsApp / inbox aplikasi), dan notifikasi kegiatan yang melibatkan personel grup.

## Identitas Super Admin Non-Komcad

### Sumber kebenaran tunggal

- `personels` adalah sumber kebenaran tunggal seluruh aktor manusia (selaras arah peleburan `users -> personels` di `2026.Kep.004`).
- `personel` sebagai peran Komcad (ber-NIKC, berhak Grup Angkatan) hanya berlaku bagi baris `is_komcad = true`.
- `Super Admin` non-Komcad adalah baris `personels` dengan `is_komcad = false`, tanpa NIKC, menggunakan NRP/NIK.

### Kelas identitas Super Admin

| Asal Super Admin | Identifier | Keterangan |
| --- | --- | --- |
| Personel Komcad diangkat Super Admin | NIKC | `is_komcad = true`, punya hak grup angkatan |
| Dari Komponen Utama | NRP | Format berbeda per matra (TNI-AD / TNI-AL / TNI-AU); `is_komcad = false` |
| Bukan Komcad & bukan Komponen Utama | NIK | Nomor Induk Kependudukan KTP 16 digit; `is_komcad = false` |

- `NRP` mengikuti format per-matra (TNI-AD / AL / AU punya pola berbeda, bukan satu format seragam).
- `NIK` di sini adalah NIK KTP sipil (16 digit), bukan NIKC/Komcad.
- Pengecualian identifier hanya berlaku untuk `Super Admin`. Selain itu, **seluruhnya adalah anggota Komponen Cadangan yang memiliki NIKC**.

### Hak grup Super Admin non-Komcad

- `Super Admin` luar Komcad **tidak punya hak Grup Angkatan** (bukan anggota angkatan; angkatan diturunkan dari `TMT Penetapan` Komcad).
- Konsekuensi: `Koordinator` dan `Wakil Koordinator` **mensyaratkan `is_komcad = true`**, sehingga `Super Admin` luar Komcad otomatis tidak bisa diangkat sebagai `Koordinator`/`Wakil`.
- `personel_grup_angkatan_hak` hanya untuk baris `is_komcad = true`.

### Registrasi

- Registrasi mandiri hanya terbuka untuk anggota/personel Komponen Cadangan.
- `Super Admin` non-Komcad hanya dapat didaftarkan/diregistrasikan oleh `Super Admin` lain melalui dashboard admin.

### Perluasan schema `personels` (usulan)

```
personels
- is_komcad   boolean   default true
- nikc        nullable  (null untuk Super Admin luar Komcad)
- nrp         nullable  (Super Admin Komponen Utama, format per matra)
- nik         nullable  (Super Admin luar keduanya, NIK KTP 16 digit)
- hak_grants  (set: super_admin, koordinator, wakil - via pivot/tabel hak)
```

### Amandemen `2026.Kep.003` (dilakukan)

`2026.Kep.003` (standar NIKC) mengasumsikan personel = Komcad ber-NIKC. Diamandemen eksplisit:

> "Keputusan ini (standar NIKC) berlaku untuk seluruh personel Komponen Cadangan. Super Admin yang bukan personel Komcad dikecualikan dan menggunakan NRP (jika dari Komponen Utama) atau NIK (jika bukan Komponen Utama) sesuai keputusan struktur organisasi."

## Turunan Operasional Hak Multi-Grup (Draft -> Referensi Teknis)

Draft ini telah dijadikan referensi teknis `docs/11 Referensi Teknis/2026.11.004 tentang Turunan Operasional Hak Multi-Grup Angkatan.md` setelah keputusan ini lahir. Ringkas:

- Ganti single-FK `personels.grup_angkatan_id` dengan tabel pivot `personel_grup_angkatan_hak` (personel_id, grup_angkatan_id, surat_keputusan_id wajib, jenis_hak, is_primary flag, tmt_hak, created_by).
- Constraint: tanpa SK sah hanya 1 baris; `jenis_hak = penyegaran` ditolak; hanya 1 `is_primary = true`.
- `is_primary`/current = flag preference, bukan perpindahan (Kep.004 klausul 19 berlaku saat kehilangan hak).
- Hak sekunder di grup X: Koordinator X boleh lihat (data terbatas), tidak menugaskan.
- Super Admin luar Komcad tidak masuk tabel hak.

## Checklist Pra-Finalisasi

| # | Yang Wajib Dicatat | Sudah Dibahas? | Catatan |
| --- | --- | --- | --- |
| 1 | Scope | Ya | Paradigma personel+hak, kewenangan, identitas SA non-Komcad, notifikasi, multi-grup |
| 2 | Non-goals | Ya | Tidak ulang Kep.004/Kep.005; penugasan di role lapangan |
| 3 | Dampak ke fitur lain | Ya | Verifikasi, dashboard, notifikasi, akses lintas grup, registrasi |
| 4 | Data / Schema impact | Ya | Perluas `personels`; pivot hak; amandemen Kep.003 |
| 5 | Drift implementasi | Ya | Drift `komandan -> koordinator` dilegalisasi via Kep.006 |

## Catatan untuk AI Agent

- jangan campur keputusan organisasi ke diskusi personel/akun tanpa rujukan jelas
- drift `komandan -> koordinator` sudah dilegalisasi via `2026.Kep.006`, bukan keputusan final di luar lifecycle
- jangan ulangi norma `TMT Penetapan`, `Tahun Angkatan`, `abituren`, `Surat Keputusan`, hak multi-grup, status grup dari `2026.Kep.004`
- `Koordinator Angkatan` adalah verifikator, bukan pemberi tugas
- `Super Admin` non-Komcad tidak punya hak Grup Angkatan dan tidak boleh jadi Koordinator/Wakil
- satu istilah: `personel`; "anggota" sinonim `personel`

## Rencana Tindak Lanjut (selesai)

- [x] mengunci kewenangan final koordinator/wakil
- [x] menyelaraskan dengan diskusi akun (`admin` = `Super Admin`)
- [x] menurunkan temuan runtime single-FK; hak multi-grup ditahan sampai keputusan lahir
- [x] memutuskan penanganan drift `komandan -> koordinator` (dilegalisasi via Kep.006)
- [x] menyusun draft operasional hak multi-grup (jadikan referensi teknis `2026.11.004`)
- [x] mengunci identitas Super Admin non-Komcad (NRP/NIK, amandemen Kep.003)
- [x] memisahkan ranah notifikasi: aturan di doc ini, channel di diskusi Broadcast
- [x] difinalkan dan dilahirkan keputusan `2026.Kep.006`; diarsipkan sebagai `[DITETAPKAN]`

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-12 20:15:35 WIB | Difinalkan dan ditetapkan; melahirkan `2026.Kep.006`; diarsipkan sebagai `[DITETAPKAN]` |
| 2026-07-12 | Menyepakati paradigma personel + hak; mutual-exclusion; multi-grup via SK; koordinator angkatan = verifikator; super admin non-Komcad = NRP/NIK; perluas personels; is_primary = flag; amandemen Kep.003 |
| 2026-07-12 | Menambahkan temuan audit runtime single-FK; drift nomenklatur super admin vs role runtime |
| 2026-07-12 | Menegaskan hak multi-grup ditahan di runtime single-FK sampai final |
| 2026-07-12 | Menyempitkan scope agar tidak mengulang Kep.004 |
| 2026-07-11 | Mencatat sinkronisasi `komandan -> koordinator` sebagai drift prematur |
| 2026-07-11 | Menyelaraskan kode dari `komandan` ke `koordinator`; buka dashboard untuk wakil |
| 2026-07-11 | Pemetaan role permanen vs situasional; isu nomenklatur |
| 2026-07-03 | Dokumen dibuat |
