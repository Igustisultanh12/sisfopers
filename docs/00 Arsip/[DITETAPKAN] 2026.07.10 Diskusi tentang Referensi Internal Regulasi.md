> Status Arsip: Ditetapkan
> Ditetapkan pada: 2026-07-12 10:11:14 WIB
> Rujukan keputusan: docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md
> Rujukan workplan: docs/03 Rencana Kerja/2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat.md
> Catatan: diskusi ini telah difinalkan dan menjadi dasar revisi keputusan payung dokumentasi serta workplan lintas domain terkait artefak `Surat Keputusan`

# Diskusi
## tentang REFERENSI INTERNAL REGULASI

Status: `Ditetapkan`
Tanggal dibuka: 10 Juli 2026
Identitas dokumen: 2026.07.10 Diskusi tentang Referensi Internal Regulasi
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: rumah referensi internal, taxonomy regulasi, penyimpanan artefak sumber di repo, surat keputusan sebagai artefak sumber domain, reuse lintas proses, dan pemisahan scope regulasi dari master data personels
Keputusan terkait: `2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal`
Rencana kerja terkait: `2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat`
Mengubah: -
Digantikan oleh: `2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal`

## Pemicu

Diskusi tentang master data anggota sudah mengerucut ke kebutuhan inti profil, status, pendidikan, dan peta field `personels`. Namun rumpun rujukan hukum seperti undang-undang, peraturan, keputusan, dan regulasi ternyata punya scope tersendiri dan tidak lagi nyaman jika dicampur ke diskusi master data.

Selain itu, user menginginkan agar referensi regulasi tidak bergantung pada file eksternal yang sewaktu-waktu bisa hilang, melainkan disimpan di dalam repo sebagai bagian dari `docs/`.

## Temuan Awal

Audit awal terhadap rumah dokumen dan referensi yang sudah ada menunjukkan:

- `docs/10 Referensi Internal/README.md` sudah ada, tetapi masih berfungsi sebagai rumah referensi umum yang flat
- saat ini baru ada satu referensi utama yang dicatat untuk UU Nomor 23 Tahun 2019
- diskusi master data masih sempat memuat rujukan legal yang seharusnya dipisah agar scope-nya tetap bersih
- keputusan payung `2026.Kep.001` sudah mengakui rumah dokumen dan batas lifecycle dokumen, tetapi belum menetapkan taksonomi detail untuk `10 Referensi Internal`
- diskusi grup angkatan terbaru menegaskan bahwa `Surat Keputusan` bukan sekadar lampiran umum, tetapi dapat menjadi entitas sumber formal bagi satu atau lebih grup angkatan
- kebutuhan domain aktif sekarang tidak hanya menyentuh undang-undang atau peraturan umum, tetapi juga SK penetapan yang memuat `nomor surat`, `tentang`, `tanggal surat`, dan kemungkinan lampiran file
- skenario domain terbaru menunjukkan bahwa satu `Surat Keputusan` dapat dipakai ulang untuk lebih dari satu proses, misalnya sebagai dasar hak grup baru sekaligus dasar pengajuan penyesuaian pangkat, sehingga rumah referensi internal perlu nyaman untuk reuse dokumen sumber yang sama
- kebutuhan baru pada domain pangkat menunjukkan bahwa satu `Keputusan Menteri Pertahanan` juga dapat memuat banyak nama dalam lampiran dan dipakai berkali-kali untuk memproses penyesuaian pangkat personel yang berbeda, sehingga pola penyimpanan dan rujukannya perlu tetap konsisten

## Tujuan Diskusi

Diskusi ini menampung rancangan struktur rumah referensi internal agar:

- dokumen hukum tetap tersimpan di repo dan bisa dirujuk ulang
- substansi regulasi tidak bercampur dengan master data personels
- penamaan subfolder regulasi menjadi konsisten
- setiap referensi hukum punya rumah yang jelas
- aturan minimum artefak sumber dan reuse dokumen lintas domain dapat dikunci secara jujur

## Opsi yang Dipertimbangkan

### Opsi A: Tetap flat di `docs/10 Referensi Internal/`

Semua referensi hukum disimpan di satu folder tanpa subfolder.

Kelebihan:

- paling sederhana
- sedikit perpindahan file

Kekurangan:

- cepat berantakan
- sulit membedakan jenis regulasi
- kurang nyaman kalau referensi makin banyak

### Opsi B: Subfolder berdasarkan jenis regulasi

Struktur dasar:

- `docs/10 Referensi Internal/Undang-Undang/`
- `docs/10 Referensi Internal/Peraturan/`
- `docs/10 Referensi Internal/Peraturan Menteri/`
- `docs/10 Referensi Internal/Keputusan Menteri/`
- `docs/10 Referensi Internal/Edaran/`
- `docs/10 Referensi Internal/Lainnya/`

Kelebihan:

- rapi dan mudah dibaca
- cocok untuk pertumbuhan referensi
- memisahkan jenis hukum dengan jelas

Kekurangan:

- perlu konsistensi penamaan sejak awal
- `Peraturan/` masih perlu ditegaskan apakah cukup umum atau nanti harus dipecah lagi

### Opsi C: Subfolder + ringkasan markdown per dokumen

Setiap dokumen regulasi punya:

- file PDF sebagai sumber utama yang disimpan di repo
- file ringkasan markdown sebagai catatan kerja

Kelebihan:

- mudah dibaca oleh agent maupun manusia
- ringkasan bisa dipakai untuk menyusun keputusan dan workplan

Kekurangan:

- menambah jumlah file
- perlu disiplin agar ringkasan tidak menggantikan isi sumber

## Draft yang Diusulkan

Draft struktur yang paling masuk akal untuk saat ini adalah gabungan Opsi B dan Opsi C.

### Draft struktur rumah

- `docs/10 Referensi Internal/Undang-Undang/`
- `docs/10 Referensi Internal/Peraturan/`
- `docs/10 Referensi Internal/Peraturan Menteri/`
- `docs/10 Referensi Internal/Keputusan Menteri/`
- `docs/10 Referensi Internal/Edaran/`
- `docs/10 Referensi Internal/Lainnya/`

### Draft isi rumah

- setiap dokumen regulasi yang dipertahankan wajib disimpan di dalam repo
- file PDF menjadi artefak sumber
- file markdown ringkasan boleh dibuat untuk kebutuhan kerja internal
- `Surat Keputusan` yang menjadi sumber formal domain aktif seperti grup angkatan perlu diperlakukan sebagai artefak referensi internal yang sah, bukan sekadar catatan teks di field aplikasi
- jika dokumen dibuat atau dicatat oleh `Super Admin`, lampiran file dapat menyusul dan metadata minimum dapat dibypass pada fase awal bila memang diperlukan untuk kebutuhan lapangan
- jika dokumen diajukan oleh `personel`, `Surat Keputusan` wajib sudah ada sebagai lampiran sumber
- untuk `SK` massal, struktur minimal artefaknya adalah:
  - satu artefak sumber
  - satu ringkasan dokumen
  - satu indeks nama yang memetakan entri lampiran
- jika substansi sebuah regulasi sudah bisa diserap ke dokumen aktif, prioritasnya adalah penyerapan lalu minimalkan lampiran
- master data, keputusan, dan rencana kerja hanya menyerap substansi yang relevan, bukan membiarkan isi regulasi tercecer di berbagai dokumen

### Draft klausul untuk keputusan payung

Calon klausul yang bisa masuk ke keputusan payung atau README rumah referensi internal:

1. `10 Referensi Internal` diakui sebagai rumah lampiran internal yang dipertahankan karena substansinya tidak bisa diserap memadai ke diskusi atau keputusan.
2. Referensi hukum dikelompokkan minimal ke dalam subfolder `Undang-Undang`, `Peraturan`, `Peraturan Menteri`, `Keputusan Menteri`, `Edaran`, dan `Lainnya`.
3. Setiap referensi hukum yang dipertahankan wajib tersimpan di repo agar tidak bergantung pada file eksternal.
4. Jika substansi referensi hukum sudah diserap ke dokumen aktif, lampiran boleh diperkecil tetapi jejak historisnya tetap dijaga.
5. Diskusi domain lain, termasuk master data personels, tidak boleh memuat analisis hukum yang semestinya hidup di rumah referensi regulasi ini.
6. `Surat Keputusan` yang menjadi dasar pembentukan entitas domain, seperti grup angkatan, harus punya rumah referensi yang konsisten dan dapat dirujuk ulang dari file aktif maupun dokumen domain.
7. Jalur pencatatan dokumen oleh `Super Admin` boleh mengizinkan lampiran menyusul sebagai bypass operasional, tetapi jalur pengajuan oleh `personel` wajib membawa lampiran sumber yang sah.
8. `SK` massal tetap diperlakukan sebagai satu artefak sumber yang dapat direuse lintas proses, dengan satu ringkasan dan satu indeks nama.

## Poin Diskusi

### 1. Rumah referensi internal perlu jadi rumah yang jelas

Pertanyaan kuncinya adalah apakah `docs/10 Referensi Internal/` cukup dibiarkan sebagai folder umum, atau perlu diberi struktur subfolder yang tegas berdasarkan jenis regulasi.

### 2. PDF harus hidup di repo

User sudah menegaskan bahwa referensi regulasi tidak boleh hanya mengarah ke file eksternal. Karena itu, diskusi ini perlu menegaskan bahwa PDF sumber harus disimpan di `docs/` sebagai artefak yang benar-benar bisa dirujuk ulang.

### 3. Ringkasan markdown tetap berguna

Ringkasan markdown penting supaya agent tidak perlu membuka PDF mentah setiap kali, tetapi ringkasan tidak boleh menjadi pengganti sumber.

### 4. Pemisahan scope dari master data harus dipertahankan

Setelah pemisahan ini, diskusi master data cukup menyebut bahwa ada dasar regulasi yang dipindahkan ke rumah lain, tanpa membawa seluruh analisis hukum ke dalamnya.

### 5. Posisi `Surat Keputusan` perlu ditegaskan

Kebutuhan terbaru menunjukkan bahwa SK penetapan bukan hanya dokumen pendukung pasif. Ia dapat menjadi sumber formal bagi:

- pembentukan grup angkatan
- metadata `TMT Penetapan`
- pengelompokan `abituren`
- relasi satu dokumen ke lebih dari satu grup
- reuse satu dokumen sumber untuk lebih dari satu proses domain yang sah

Karena itu, diskusi ini perlu menjawab apakah:

- SK diperlakukan sebagai bagian dari rumah `Keputusan Menteri`
- atau perlu pola yang lebih eksplisit untuk membedakan SK sumber domain aktif dari lampiran regulasi umum lain

Keputusan arah yang dicatat:

- `Surat Keputusan` tetap hidup di rumpun `Keputusan Menteri`
- bila nanti diperlukan pembeda tambahan, pembeda itu cukup hidup sebagai metadata atau indeks, bukan dengan memecah rumah folder utama menjadi struktur liar baru

### 6. Hubungan `Surat Keputusan` dan proses domain perlu dimatangkan

Skenario terbaru menunjukkan bahwa satu SK yang sama dapat dipakai untuk:

- menetapkan hak personel pada grup angkatan baru
- menjadi dasar pengajuan penyesuaian pangkat

Implikasinya:

- repo perlu menghindari pola yang memaksa unggah file SK yang sama berulang kali untuk proses yang berbeda
- taxonomy referensi internal dan desain entitas aplikasi perlu memungkinkan satu artefak sumber dirujuk berkali-kali oleh domain berbeda
- ringkasan markdown, bila dipakai, sebaiknya cukup satu per SK dan tidak digandakan per proses
- untuk SK massal, satu artefak sumber dan satu ringkasan tidak cukup sendirian; perlu satu indeks nama agar entri lampiran dapat dirujuk per personel tanpa memecah dokumen sumber
- untuk SK massal yang memuat banyak nama, satu artefak sumber juga tidak boleh dipecah menjadi banyak salinan hanya karena personelnya banyak

Arahan yang kini sudah diputuskan pada diskusi grup:

- lampiran file `Surat Keputusan` pada create grup boleh menyusul
- metadata minimum `nomor surat`, `tentang`, dan `tanggal surat` tetap wajib dicatat sejak awal
- satu SK yang sama boleh dipakai sebagai dasar lebih dari satu proses domain tanpa unggah ulang file
- bila `NIKC` berubah pada kasus khusus, perubahan itu harus dibuktikan dengan SK yang sesuai dan dicatat secara historis

Arahan tambahan yang kini diputuskan user:

- jika dokumen dicreate oleh `Super Admin`, SK bisa menyusul dan metadata tidak wajib pada fase awal karena `Super Admin` boleh bypass operasional
- jika dokumen diajukan oleh `personel`, SK wajib ada
- untuk SK massal, bentuk minimum artefaknya adalah satu artefak sumber, satu ringkasan, dan satu indeks nama

### 7. UI dan UX pengelolaan artefak sumber perlu dicatat

Karena domain aktif mulai bergantung pada SK sebagai sumber formal:

- UI atau alur admin nantinya perlu membedakan antara metadata SK dan lampiran file SK
- reuse SK yang sama di dua proses tidak boleh membingungkan admin
- label, field, dan tampilan status dokumen perlu diseragamkan terhadap pedoman UI/UX aktif dan tidak boleh lahir sebagai pola liar per halaman
- jalur `Super Admin` dan `personel` juga perlu dibedakan jelas di UI agar bypass operasional tidak tertukar dengan syarat normal pengajuan dokumen

### 8. Arah keputusan turunan perlu ditegaskan

Karena topik ini menyentuh rumah dokumen, taxonomy folder, dan otoritas repo atas artefak sumber, arah normatif yang paling tepat adalah:

- dikunci di keputusan
- secara default menumpang pada scope sejenis yang sudah ada, yaitu keputusan payung dokumentasi
- keputusan baru hanya diperlukan bila user ingin memisahkan domain rumah referensi internal menjadi keputusan mandiri

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Pemisahan scope regulasi dari master data | Disepakati sebagai arah kerja | Clear |
| 2 | Subfolder `Undang-Undang` | Disetujui | Clear |
| 3 | Subfolder `Peraturan` | Disetujui | Clear |
| 4 | Subfolder `Peraturan Menteri` | Disetujui | Clear |
| 5 | Subfolder `Keputusan Menteri` | Disetujui | Clear |
| 6 | Subfolder `Edaran` dan `Lainnya` | Disetujui | Clear |
| 7 | PDF sumber harus disimpan di repo | Disetujui | Clear |
| 8 | Ringkasan markdown per dokumen | Disetujui sebagai pola yang sah | Clear |
| 9 | `Surat Keputusan` sebagai artefak sumber domain aktif | Disetujui hidup di rumpun `Keputusan Menteri` | Clear |
| 10 | satu SK dapat dipakai ulang oleh lebih dari satu proses domain | Sudah diputuskan di diskusi grup | Clear |
| 11 | kebutuhan UI/UX untuk metadata dan reuse SK | Sudah muncul sebagai kebutuhan | Clear untuk arah, diturunkan nanti |
| 12 | lampiran file SK boleh menyusul selama metadata minimum sudah dicatat | Disetujui untuk jalur `Super Admin`; jalur `personel` wajib lampiran | Clear |
| 13 | SK dapat menjadi dasar historis perubahan NIKC pada kasus khusus | Sudah diputuskan di diskusi grup | Clear |
| 14 | SK massal dapat menjadi dasar penyesuaian pangkat banyak personel | Disetujui | Clear |
| 15 | bentuk minimum SK massal | Disetujui: satu artefak + satu ringkasan + satu indeks nama | Clear |
| 16 | rumah referensi internal perlu dikunci di keputusan | Disetujui | Clear |

## Checklist Pra-Finalisasi

- [x] scope dipisahkan dari master data
- [x] kebutuhan repo-local untuk referensi regulasi dicatat
- [x] draft struktur folder sudah ditulis
- [x] draft klausul untuk keputusan payung sudah disiapkan
- [x] nama subfolder `Peraturan/` dipertahankan sebagai payung generik
- [x] format final penyimpanan PDF dan ringkasan sudah cukup jelas untuk arah diskusi
- [x] hubungan dokumen ini dengan README `10 Referensi Internal` sudah terpetakan
- [x] arah normatifnya diputuskan harus dikunci di keputusan
- [x] posisi final `Surat Keputusan` dalam taxonomy referensi internal sudah diputuskan
- [x] hubungan diskusi ini dengan diskusi identitas grup berbasis `abituren` dan `Surat Keputusan` sudah tersinkron
- [x] pola reuse satu SK untuk lebih dari satu proses domain sudah diputuskan di diskusi grup
- [x] kebutuhan UI/UX pengelolaan metadata SK dan lampiran SK sudah dicatat untuk workplan setelah keputusan lahir
- [x] pola referensi internal untuk SK massal yang memuat banyak personel sudah disinkronkan dengan diskusi pangkat

## Catatan untuk AI Agent

- jangan melahirkan keputusan final dari dokumen ini sebelum user menyetujui struktur akhirnya
- jangan mencampur analisis hukum ke diskusi master data lagi
- jika nanti struktur folder sudah disepakati, sinkronkan README rumah referensi internal sebelum menyentuh dokumen turunan lain
- jika ada referensi regulasi lain yang akan disimpan di repo, catat di dokumen ini dulu agar taxonomy-nya konsisten
- jika domain aktif mulai bergantung pada `Surat Keputusan` sebagai sumber formal entitas, pastikan dokumen ini mencatat taxonomy dan pola penyimpanannya sebelum file implementasi bergerak lebih jauh
- jangan biarkan UI atau flow unggah dokumen bergerak lebih jauh tanpa dasar taxonomy dan reuse artefak yang tertulis di diskusi ini
- jika satu SK memuat banyak personel, jangan pecah artefak sumber menjadi banyak dokumen referensi palsu; cukup satu sumber dengan relasi reuse yang benar, satu ringkasan, dan satu indeks nama

## Pertanyaan Terbuka

Tidak ada pertanyaan terbuka normatif utama yang tersisa pada dokumen ini. Turunan teknis implementasi, README, keputusan, dan workplan dilanjutkan setelah diskusi ini difinalkan.

## Rencana Tindak Lanjut

- rapikan `docs/10 Referensi Internal/README.md` setelah struktur disepakati
- pindahkan atau salin PDF regulasi ke rumah referensi internal di repo
- sinkronkan master data agar hanya menyebut bahwa analisis hukum telah dipindah
- revisi keputusan payung dokumentasi agar rumah referensi internal dan taxonomy regulasi terkunci secara normatif; keputusan baru hanya dipilih jika user menginginkan domain rumah referensi internal berdiri sendiri
- sinkronkan arah dokumen ini dengan diskusi identitas grup berbasis `abituren` dan `Surat Keputusan`
- saat sinkronisasi berikutnya, bawa juga catatan UI/UX pengelolaan SK agar turunan implementasinya patuh ke pedoman UI aktif
- sinkronkan juga dengan diskusi mekanisme penyesuaian pangkat berbasis SK massal agar pola reuse artefak sumber tetap konsisten lintas domain

## Pemetaan Clause ke Keputusan

Clause hasil diskusi ini dipetakan ke keputusan berikut:

### Masuk ke revisi `2026.Kep.001`

- taxonomy rumah `10 Referensi Internal`:
  - `Undang-Undang`
  - `Peraturan`
  - `Peraturan Menteri`
  - `Keputusan Menteri`
  - `Edaran`
  - `Lainnya`
- referensi hukum yang dipertahankan wajib tersimpan di repo
- `Surat Keputusan` sebagai artefak sumber domain aktif hidup di rumpun `Keputusan Menteri`
- satu `SK` massal tetap diperlakukan sebagai:
  - satu artefak sumber
  - satu ringkasan
  - satu indeks nama
- reuse satu artefak sumber lintas proses domain diperbolehkan
- rumah referensi internal dan taxonomy regulasi dikunci di keputusan, bukan dibiarkan hanya di README

### Diteruskan ke revisi `2026.Kep.004`

- pada domain grup angkatan, jalur `Super Admin` dapat mencatat dokumen sumber dengan lampiran menyusul sebagai bypass operasional bila memang dibutuhkan lapangan
- metadata minimum dokumen sumber tetap menjadi bagian dari kontrol domain grup angkatan saat dipakai pada proses create atau update grup

### Diteruskan ke revisi `2026.Kep.005`

- pada domain penyesuaian pangkat, satu `SK` dapat direuse lintas pengajuan dan tidak perlu diunggah ulang bila metadata dan softcopy yang sama sudah tersedia di sistem
- pada jalur pengajuan oleh `personel`, lampiran `SK` wajib ada

### Butuh workplan turunan

- penyelarasan README rumah `10 Referensi Internal`
- desain entitas artefak sumber, ringkasan, dan indeks nama
- UI atau UX pengelolaan metadata SK, lampiran SK, dan reuse SK
- sinkronisasi lintas domain antara grup angkatan dan penyesuaian pangkat yang sama-sama merujuk `SK`

## Changelog Dokumen

- 2026-07-10: draft diskusi baru dibuat untuk memisahkan scope regulasi dari diskusi master data
- 2026-07-11: diskusi dimatangkan agar juga menampung posisi `Surat Keputusan` sebagai artefak sumber formal domain aktif, khususnya bagi grup angkatan
- 2026-07-11: ditambahkan skenario reuse satu `Surat Keputusan` untuk lebih dari satu proses domain serta catatan bahwa UI/UX pengelolaan metadata dan lampiran SK perlu diseragamkan dengan pedoman aktif
- 2026-07-12: diselaraskan dengan keputusan diskusi grup bahwa lampiran SK boleh menyusul, metadata minimum tetap wajib, reuse satu SK lintas proses diperbolehkan, dan perubahan NIKC kasus khusus harus bertumpu pada SK serta histori yang sah
- 2026-07-12: ditambahkan kebutuhan bahwa satu SK massal dapat memuat banyak personel untuk penyesuaian pangkat dan harus tetap diperlakukan sebagai satu artefak sumber yang direuse lintas proses
- 2026-07-12: user menutup taxonomy rumah referensi internal menjadi `Undang-Undang`, `Peraturan`, `Peraturan Menteri`, `Keputusan Menteri`, `Edaran`, dan `Lainnya`; menegaskan jalur `Super Admin` dapat bypass lampiran awal sedangkan jalur `personel` wajib lampiran; serta mengunci pola SK massal sebagai satu artefak, satu ringkasan, dan satu indeks nama


