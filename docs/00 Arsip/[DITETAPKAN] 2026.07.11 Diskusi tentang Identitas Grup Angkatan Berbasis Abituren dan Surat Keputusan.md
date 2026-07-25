> Status Arsip: Ditetapkan
> Ditetapkan pada: 2026-07-12 10:11:14 WIB
> Rujukan keputusan: docs/02 Keputusan/2026.Kep.004 tentang Standar Master Data dan Status Personel.md
> Rujukan workplan: docs/03 Rencana Kerja/2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat.md
> Catatan: diskusi ini telah difinalkan dan menjadi dasar revisi keputusan master data terkait identitas `Grup Angkatan`, `Abituren`, dan relasi `Surat Keputusan`

# Diskusi
## tentang IDENTITAS GRUP ANGKATAN BERBASIS ABITUREN DAN SURAT KEPUTUSAN

Status: `Ditetapkan`
Tanggal dibuka: 11 Juli 2026
Identitas dokumen: 2026.07.11 Diskusi tentang Identitas Grup Angkatan Berbasis Abituren dan Surat Keputusan
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: identitas grup angkatan, abituren, surat keputusan penetapan, relasi satu SK ke banyak grup, dan dampak ke label serta UI admin
Keputusan terkait: `2026.Kep.004 tentang Standar Master Data dan Status Personel`
Rencana kerja terkait: `2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat`
Mengubah: -
Digantikan oleh: `2026.Kep.004 tentang Standar Master Data dan Status Personel`

## Pemicu

Fitur `create grup angkatan` sudah lahir di file aktif, tetapi identitas grup yang berjalan saat ini masih bertumpu pada `Matra-Tahun-Batch`. Setelah audit lanjutan dan konfirmasi user, arah domain ternyata lebih tegas:

- `abituren` resmi menjadi bagian identitas grup
- kombinasi unik grup berubah menjadi `Matra-Tahun-Abituren-Batch`
- `Surat Keputusan` disimpan sebagai entitas tersendiri
- satu `Surat Keputusan` dapat menjadi dasar bagi lebih dari satu grup angkatan

Karena perubahan ini menyentuh norma identitas grup, entitas sumber formal, dan desain UI admin, dibutuhkan rumah diskusi tersendiri agar keputusan berikutnya tidak lahir setengah jalan.

## Temuan Awal

Analisis awal terhadap file aktif dan dokumen yang beririsan menunjukkan:

- file aktif sudah memiliki master `grup angkatan`, tetapi schema dan form create saat ini belum memuat `abituren`
- file aktif juga belum memuat entitas `Surat Keputusan` tersendiri untuk menjadi sumber formal grup
- form create grup saat ini belum meminta data wajib `nomor surat`, `tentang`, dan `tanggal surat`
- keputusan aktif dan referensi teknis terbaru masih membaca identitas grup sebagai `Matra-Tahun-Batch`, sehingga dokumen belum sepenuhnya selaras dengan arah domain yang baru dikonfirmasi user
- diskusi struktur organisasi dan grup angkatan aktif masih memakai struktur lama `Matra-Tahun-Batch`
- diskusi referensi internal regulasi sudah membuka kebutuhan rumah dokumen hukum, tetapi belum secara eksplisit memosisikan `Surat Keputusan` penetapan sebagai artefak sumber bagi pembentukan grup angkatan
- dari sisi UI dan UX, file aktif masih memakai pola warna dan komponen existing yang belum sepenuhnya selaras dengan keputusan design system global, walau istilah operasionalnya mulai membaik
- file aktif juga masih membaca keanggotaan grup sebagai satu keterikatan aktif per personel, padahal skenario domain terbaru membuka kemungkinan hak personel terhadap lebih dari satu grup angkatan berdasarkan SK yang berbeda

## Opsi yang Dipertimbangkan

### Opsi A: `abituren` hanya label tampilan

Grup tetap unik pada `Matra-Tahun-Batch`, sedangkan `abituren` hanya dipakai sebagai label tambahan.

Kelebihan:

- perubahan schema lebih kecil
- lebih cepat ditambal di UI

Kekurangan:

- tidak cukup untuk kasus `Matra Laut 2025 - Reguler` dan `Matra Laut 2025 - ASN`
- memaksa `batch` atau label lain menanggung makna yang bukan tugasnya

### Opsi B: `abituren` menjadi bagian identitas grup

Grup unik pada `Matra-Tahun-Abituren-Batch`.

Kelebihan:

- sesuai dengan realitas domain yang sudah dikonfirmasi user
- memisahkan jelas `jenis jalur` dari `batch`
- membuat label grup lebih jujur dan stabil

Kekurangan:

- perlu revisi keputusan, referensi teknis, schema, controller, dan UI

### Opsi C: `Surat Keputusan` hanya metadata grup

Data SK langsung ditempel ke tabel grup tanpa entitas terpisah.

Kelebihan:

- lebih sederhana di schema awal
- cepat diimplementasikan

Kekurangan:

- tidak cocok jika satu SK menjadi dasar lebih dari satu grup
- rawan duplikasi data SK

### Opsi D: `Surat Keputusan` menjadi entitas tersendiri

Grup merujuk ke entitas SK, dan satu SK dapat menjadi induk bagi banyak grup.

Kelebihan:

- sesuai dengan kebutuhan domain yang sudah dikonfirmasi user
- lebih rapi untuk audit dan regulasi
- mengurangi duplikasi metadata SK

Kekurangan:

- butuh relasi tambahan dan penataan UI admin

## Poin Diskusi

### 1. `abituren` resmi menjadi bagian identitas grup

Hasil konfirmasi user menegaskan bahwa grup seperti berikut harus bisa hidup sebagai entitas berbeda:

- `Matra Laut 2025 - Reguler`
- `Matra Laut 2025 - ASN`
- `Matra Udara 2024 - SPPI`

Dengan demikian, `abituren` tidak cukup diperlakukan sebagai label tambahan, melainkan harus menjadi unsur identitas formal grup.

### 2. Kombinasi unik grup berubah menjadi `Matra-Tahun-Abituren-Batch`

Makna setiap unsur perlu dipisahkan tegas:

- `Matra`: domain matra
- `Tahun`: tahun turunan dari `TMT Penetapan`
- `Abituren`: jenis jalur atau kelompok penetapan
- `Batch`: pembeda tambahan bila pada kelompok yang sama terdapat lebih dari satu gelombang

Konsekuensinya, `batch` tetap opsional, tetapi tidak lagi dipakai untuk menutupi perbedaan `abituren`.

### 3. `Surat Keputusan` harus menjadi entitas tersendiri

Karena satu SK dapat menjadi dasar bagi lebih dari satu grup, relasi yang lebih jujur adalah:

- satu `Surat Keputusan`
- dapat memiliki banyak `Grup Angkatan`

Data minimum SK yang harus diminta sejak awal:

- `nomor surat`
- `tentang`
- `tanggal surat`

Lampiran file SK juga muncul sebagai kebutuhan kuat agar identitas grup tidak terlepas dari sumber formalnya.

### 4. Posisi `abituren` perlu dibakukan

Konfirmasi user saat ini:

- `ASN` diperlakukan sebagai satu payung kelompok untuk saat ini
- `ASN` mencakup `PNS` dan `PPPK`
- daftar awal `abituren` yang diakui untuk arah aktif saat ini minimal adalah `Reguler`, `ASN`, dan `SPPI`
- `ASN` tetap tampil sebagai satu nilai kelompok pada identitas grup, sedangkan rincian `PNS` dan `PPPK` berada di dalam payung tersebut sampai ada keputusan lain

### 5. Skenario hak multi-grup perlu dicatat eksplisit

Skenario yang diberikan user:

- A awalnya `Prada KC` dari `Matra Darat 2021`
- A kemudian mengikuti pendidikan lagi pada abituren `SPPI 2025`
- A lalu ditetapkan sebagai `Letda KC` dari `Matra Laut 2025`

Makna domain yang perlu dijaga dari skenario ini:

- hak A pada grup `Matra Darat 2021` tidak otomatis hilang hanya karena ada penetapan baru
- A juga berhak tercatat pada grup `Matra Laut 2025` sesuai SK terbaru
- dengan demikian, repo perlu membedakan antara:
  - grup asal atau grup penetapan awal
  - grup tambahan yang sah karena SK pendidikan atau penetapan berikutnya
  - status aktif keanggotaan grup pada konteks operasional tertentu

Norma yang diputuskan user:

- personel boleh memiliki lebih dari satu hak grup angkatan secara sah
- hak multi-grup hanya berlaku jika personel memiliki lebih dari satu `Surat Keputusan` penetapan yang sah atau penetapan baru yang sah
- personel tidak boleh lintas grup angkatan di luar hak yang didukung `Surat Keputusan`
- hak multi-grup hanya berlaku bila personel mengikuti pendidikan dasar militer, latihan dasar militer, atau latsarmil sebanyak dua kali atau lebih dan dibuktikan dengan `Surat Keputusan` penetapan
- pendidikan dasar militer, latihan dasar militer, dan latsarmil berbeda dari `penyegaran`

Dengan demikian, aturan lama "satu personel hanya melekat ke satu grup angkatan" dinyatakan tidak lagi cukup untuk seluruh domain aktif.

### 6. Hak grup dan penyesuaian pangkat perlu dipisahkan sebagai dua proses

Arahan yang muncul dari user:

- perubahan pangkat dari `Prada KC` ke `Letda KC` pada skenario ini terjadi melalui mekanisme tambah pendidikan militer
- sistem perlu mencatat `abituren SPPI`
- sistem perlu memakai `Surat Keputusan` terbaru yang sama sebagai dasar pengajuan penyesuaian pangkat
- user menegaskan bahwa tidak perlu unggah SK baru kedua kali jika SK yang sama sudah dipakai

Implikasi proses:

- proses 1: pencatatan hak grup baru berdasarkan SK dan abituren baru
- proses 2: pengajuan penyesuaian pangkat berdasarkan SK yang sama
- pembaruan pendidikan militer tidak otomatis menaikkan atau menyesuaikan pangkat
- kenaikan atau penyesuaian pangkat harus diajukan melalui mekanisme tersendiri yang khusus untuk domain pangkat
- penyesuaian pangkat biasanya terjadi karena tugas dan atau karena mengikuti pendidikan militer atau penyegaran, tetapi tetap tidak otomatis lahir hanya karena hak grup baru dicatat

Alternatif yang hanya melakukan penyesuaian pangkat tanpa pencatatan hak grup baru dinilai belum cukup, karena sistem tidak akan tahu bahwa A juga sah berada pada grup angkatan baru.

### 7. Posisi NIKC pada perpindahan jalur belum final

Pada skenario di atas, status NIKC masih belum diketahui apakah:

- tetap
- berubah
- atau bergantung pada keputusan lain yang belum lahir

Karena itu, perubahan identitas grup dan perubahan pangkat tidak boleh diam-diam diasumsikan ikut mengubah NIKC sebelum ada keputusan final tersendiri.

Skenario unik lain yang juga perlu diakomodasi:

- `Prada KC X` berasal dari `Matra Laut 2022 Reguler`
- `Prada KC Y` berasal dari `Matra Udara 2023 Reguler`
- X dan Y sama-sama mengikuti pendidikan `SPPI`
- keduanya kemudian ditetapkan sebagai `Letda KC` pada `Matra Darat 2025 SPPI`
- pada kasus X, `NIKC` tetap sama dengan `NIKC` saat masih `Prada KC`
- pada kasus Y, `NIKC` justru berbeda atau terbit baru

Makna praktis dari skenario ini:

- lapangan belum menunjukkan satu pola tunggal perubahan NIKC pada jalur pendidikan militer lanjutan
- sistem tidak boleh meng-hardcode bahwa jalur `SPPI` selalu mempertahankan NIKC lama
- sistem juga tidak boleh meng-hardcode bahwa jalur `SPPI` selalu menerbitkan NIKC baru
- sistem perlu mengakomodasi kedua kemungkinan tersebut sampai alasan normatif dari pimpinan benar-benar jelas
- norma umum yang diputuskan user adalah `NIKC` tidak berubah
- jika dalam kasus tertentu `NIKC` berubah, perubahan itu harus diajukan ke `super admin`, dibuktikan dengan `Surat Keputusan` yang sesuai, dan wajib dicatat secara historis
- jika kelak terjadi perubahan `NIKC`, sistem minimal harus mencatat dasar atau alasan administrasi saat `NIKC` dipertahankan atau diganti

### 8. Dampak ke istilah user-facing dan UI admin perlu dicatat sejak sekarang

Temuan penting yang perlu dibawa ke workplan berikutnya:

- label grup di UI harus berubah dari pola lama ke pola yang menyertakan `abituren`, misalnya `Matra Laut 2025 - Reguler`
- form create dan edit grup perlu memiliki section `Surat Keputusan` yang jelas dan bukan tempelan field acak
- UI hak grup personel kemungkinan perlu membedakan antara:
  - grup penetapan awal
  - grup tambahan yang sah karena SK berikutnya
  - grup operasional aktif bila konsep itu nantinya dipakai
- daftar, filter, badge, dan label human-readable lintas modul harus membedakan:
  - `Tahun Angkatan`
  - `Abituren`
  - `Grup Angkatan`
- warna, badge, dan penanda visual di area admin perlu diaudit ulang dan diseragamkan terhadap keputusan UI aktif `2026.02.002` agar tidak terus membawa drift visual lama
- karena user menegaskan penyelarasan UI/UX wajib mengikuti pedoman aktif, seluruh turunan implementasi domain ini nantinya harus membaca keputusan UI sebagai batas visual resmi

Catatan ini sengaja ditahan sebagai kebutuhan workplan berikutnya dan belum boleh dibaca sebagai referensi teknis final selama diskusi ini belum difinalkan.

### 9. Keterkaitan dengan regulasi dan rumah referensi internal perlu diperjelas

Jika grup angkatan melekat pada SK, maka diskusi regulasi tidak lagi berhenti pada rumah PDF hukum umum. Ia juga harus menjawab:

- bagaimana SK penetapan disimpan dan dirujuk di repo
- apakah setiap SK perlu ringkasan markdown internal
- apakah satu SK yang sama boleh dipakai sebagai dasar lebih dari satu proses domain tanpa unggah ulang
- apakah kategori `Keputusan Menteri` sudah cukup, atau perlu pengelompokan yang juga nyaman untuk SK penetapan grup

Norma proses yang diputuskan user:

- lampiran file `Surat Keputusan` pada create grup boleh menyusul
- metadata minimum `nomor surat`, `tentang`, dan `tanggal surat` tetap wajib dicatat sejak awal
- satu `Surat Keputusan` yang sama dapat dipakai sebagai dasar lebih dari satu proses domain tanpa unggah ulang file kedua kali

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | `abituren` menjadi bagian identitas grup | Disetujui oleh user | Clear |
| 2 | unique grup berubah menjadi `Matra-Tahun-Abituren-Batch` | Disetujui oleh user | Clear |
| 3 | `Surat Keputusan` menjadi entitas tersendiri | Disetujui oleh user | Clear |
| 4 | satu SK dapat menjadi dasar lebih dari satu grup | Disetujui oleh user | Clear |
| 5 | data wajib SK: nomor surat, tentang, tanggal surat | Muncul jelas dari kebutuhan user | Clear |
| 6 | `ASN` saat ini diperlakukan sebagai satu payung yang mencakup `PNS` dan `PPPK` | Ditegaskan oleh user | Clear |
| 7 | personel dapat memiliki hak pada grup penetapan awal dan grup baru dari SK berikutnya | Disetujui oleh user dengan batas berbasis SK penetapan sah | Clear |
| 8 | pencatatan hak grup baru dan penyesuaian pangkat adalah dua proses yang berbeda | Sudah muncul jelas dari arahan user | Clear |
| 9 | arah umum NIKC pada kasus pendidikan militer atau penetapan baru | Diputuskan: secara umum tidak berubah; kasus khusus melalui super admin + SK + histori | Clear |
| 10 | pada jalur `SPPI`, ada kasus NIKC tetap dan ada kasus NIKC berubah | Diakui sebagai kebutuhan lapangan yang harus diakomodasi sistem | Clear |
| 11 | lampiran file SK pada create grup | Diputuskan: boleh menyusul, tetapi metadata minimum wajib | Clear |
| 12 | daftar awal nilai `abituren` | Diputuskan: minimal `Reguler`, `ASN`, dan `SPPI` | Clear |
| 13 | detail UI/UX create grup berbasis SK dan hak multi-grup | Sudah muncul sebagai kebutuhan dan wajib mengikuti pedoman UI aktif | Clear untuk arah, implementasi diturunkan nanti |

## Checklist Pra-Finalisasi

- [x] file aktif dan dokumen beririsan sudah diaudit
- [x] gap identitas grup lama versus kebutuhan baru sudah dicatat
- [x] relasi satu SK ke banyak grup sudah ditulis eksplisit
- [x] data wajib SK minimum sudah dicatat
- [x] posisi `ASN` sebagai payung `PNS` dan `PPPK` saat ini sudah dicatat
- [x] skenario hak personel pada dua grup berbasis SK berbeda sudah dicatat
- [x] pemisahan proses hak grup dan penyesuaian pangkat sudah dicatat
- [x] daftar awal `abituren` yang diakui sudah ditutup eksplisit
- [x] kebutuhan lampiran file SK sudah diputuskan eksplisit
- [x] norma final bahwa personel boleh memiliki lebih dari satu hak grup sudah diputuskan
- [x] posisi NIKC pada skenario pendidikan militer dan penetapan baru sudah diputuskan
- [x] kebutuhan pencatatan historis saat NIKC berubah sudah diputuskan
- [x] posisi dokumen ini terhadap diskusi struktur organisasi dan grup angkatan sudah cukup jelas untuk siap difinalkan
- [x] dampak ke keputusan master data, referensi teknis grup angkatan, dan schema aktif sudah siap diturunkan setelah diskusi final

## Catatan untuk AI Agent

- jangan revisi keputusan aktif atau referensi teknis seolah-olah struktur `Matra-Tahun-Abituren-Batch` sudah final sebelum user menyatakan diskusi ini siap difinalkan
- jangan lanjut membuat workplan turunan baru hanya dari diskusi ini; cukup catat kebutuhan implementasi dan UI/UX di sini lebih dulu
- saat diskusi ini disentuh lagi, audit ulang file aktif `grup angkatan`, `personel`, `broadcast`, dan `verifikasi` karena semuanya terdampak oleh perubahan identitas grup
- jangan asumsikan model lama satu personel satu grup masih final; skenario hak multi-grup harus dibaca sebagai pembuka revisi norma yang serius
- jangan asumsikan perubahan pangkat otomatis menyelesaikan pencatatan hak grup baru; keduanya harus dipisahkan bila diskusi final tetap mengarah ke sana
- jangan asumsikan jalur `SPPI` mempunyai satu pola baku perubahan NIKC; norma umumnya adalah `NIKC` tetap, tetapi sistem wajib membuka jalur kasus khusus melalui `super admin` dengan `Surat Keputusan` dan histori yang sah
- sinkronkan hasil final dokumen ini dengan diskusi regulasi agar posisi `Surat Keputusan` konsisten sebagai sumber formal grup

## Pertanyaan Terbuka

Tidak ada pertanyaan terbuka normatif utama yang tersisa pada dokumen ini. Pertanyaan teknis dan implementatif diturunkan ke keputusan, referensi teknis, dan workplan setelah diskusi ini difinalkan.

## Rencana Tindak Lanjut

- selaraskan diskusi ini dengan diskusi struktur organisasi dan grup angkatan yang masih memakai identitas lama
- matangkan diskusi regulasi agar rumah referensi internal dan penanganan SK sejalan dengan kebutuhan grup angkatan
- setelah isi diskusi ini final, turunkan ke revisi keputusan master data atau keputusan domain grup angkatan yang lebih tepat, termasuk norma tentang hak multi-grup bila memang disetujui
- setelah keputusan final lahir, turunkan dampak schema, controller, migration, form admin, filter, label, dan audit UI/UX ke workplan formal

## Pemetaan Clause ke Keputusan

### Clause yang masuk ke revisi `2026.Kep.004 tentang Standar Master Data dan Status Personel`

- identitas formal `Grup Angkatan` berubah menjadi `Matra-Tahun-Abituren-Batch`
- `abituren` adalah unsur identitas formal grup, bukan sekadar label tampilan
- `batch` tetap opsional dan hanya menjadi pembeda tambahan di dalam kombinasi identitas tersebut
- `Surat Keputusan` menjadi entitas tersendiri yang dirujuk oleh `Grup Angkatan`
- satu `Surat Keputusan` dapat menjadi dasar bagi lebih dari satu `Grup Angkatan`
- metadata minimum `Surat Keputusan` untuk pembentukan grup adalah `nomor surat`, `tentang`, dan `tanggal surat`
- lampiran file `Surat Keputusan` pada create grup boleh menyusul
- nilai awal `abituren` yang dibakukan saat ini minimal `Reguler`, `ASN`, dan `SPPI`
- `ASN` pada identitas grup diperlakukan sebagai satu nilai payung yang mencakup `PNS` dan `PPPK`
- personel hanya boleh terikat ke grup yang memang sah menurut `Surat Keputusan` yang dimilikinya
- hak multi-grup hanya sah bila didukung `Surat Keputusan` penetapan yang sah akibat pendidikan dasar militer, latihan dasar militer, atau `latsarmil`, bukan sekadar `penyegaran`

### Clause yang masuk ke revisi `2026.Kep.003 tentang Standar Nomor Induk Komponen Cadangan`

- norma umum `NIKC` tidak berubah walaupun personel memperoleh hak grup baru
- jika dalam kasus khusus `NIKC` berubah, perubahan hanya boleh melalui `Super Admin`, harus dibuktikan dengan `Surat Keputusan` yang sah, dan wajib dicatat historis
- sistem wajib mengakomodasi kemungkinan khusus bahwa pendidikan militer lanjutan dapat melahirkan penetapan grup baru dengan `NIKC` tetap maupun `NIKC` berubah, tetapi perubahan `NIKC` tetap diperlakukan sebagai pengecualian yang harus tervalidasi

### Clause yang masuk ke revisi `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota`

- pencatatan hak grup baru dan penyesuaian pangkat adalah dua proses yang berbeda
- pendidikan militer lanjutan atau penetapan grup baru tidak otomatis menaikkan atau menyesuaikan pangkat
- `Surat Keputusan` yang sama dapat menjadi dasar untuk proses hak grup dan proses penyesuaian pangkat tanpa unggah ulang file, selama konteks prosesnya tetap dipisahkan

### Clause yang diturunkan ke workplan dan referensi teknis

- desain schema `Surat Keputusan`, `Grup Angkatan`, hak multi-grup, dan histori relasinya
- penyesuaian label human-readable `Grup Angkatan` di seluruh UI admin
- pemecahan istilah user-facing antara `Tahun`, `Abituren`, dan `Grup Angkatan`
- audit serta penyeragaman pola visual terhadap pedoman UI/UX aktif
- aturan teknis create grup, filter, detail grup, dan mekanisme pengikatan personel ke lebih dari satu grup yang sah

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-11 | Dokumen dibuat untuk menampung perubahan identitas grup angkatan menjadi berbasis `abituren` dan `Surat Keputusan`, berikut temuan awal file aktif dan kebutuhan UI/UX yang belum boleh langsung diturunkan ke workplan |
| 2026-07-11 | Ditambahkan skenario pendidikan militer yang melahirkan hak pada dua grup angkatan, penegasan bahwa `ASN` mencakup `PNS` dan `PPPK`, pemisahan proses hak grup dan penyesuaian pangkat, serta catatan bahwa UI/UX wajib diseragamkan ke pedoman aktif |
| 2026-07-11 | Ditambahkan skenario unik jalur `SPPI` yang menunjukkan dua kemungkinan berbeda pada `NIKC`, sehingga sistem harus mengakomodasi baik NIKC tetap maupun NIKC baru sampai dasar normatifnya jelas |
| 2026-07-12 | User memutuskan norma multi-grup berbasis SK penetapan sah, membedakan pendidikan dasar militer dari penyegaran, menegaskan penyesuaian pangkat sebagai proses tersendiri, menetapkan lampiran SK boleh menyusul, mengunci arah umum NIKC tetap dengan jalur kasus khusus melalui super admin, dan menutup pertanyaan normatif utama sehingga dokumen siap difinalkan |
| 2026-07-12 | Ditambahkan pemetaan clause ke keputusan target agar lifecycle revisi berikutnya mengikuti format keputusan baru `2026.Kep.xxx` dan tidak mencampur norma grup, NIKC, dan pangkat |
