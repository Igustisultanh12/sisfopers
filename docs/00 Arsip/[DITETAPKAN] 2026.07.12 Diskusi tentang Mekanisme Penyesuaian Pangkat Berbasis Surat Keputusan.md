> Status Arsip: Ditetapkan
> Ditetapkan pada: 2026-07-12 10:11:14 WIB
> Rujukan keputusan: docs/02 Keputusan/2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota.md
> Rujukan workplan: docs/03 Rencana Kerja/2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat.md
> Catatan: diskusi ini telah difinalkan dan menjadi dasar revisi keputusan kepangkatan terkait `Surat Keputusan` massal, reuse softcopy, dan checkpoint verifikasi

# Diskusi
## tentang MEKANISME PENYESUAIAN PANGKAT BERBASIS SURAT KEPUTUSAN

Status: `Ditetapkan`
Tanggal dibuka: 12 Juli 2026
Identitas dokumen: 2026.07.12 Diskusi tentang Mekanisme Penyesuaian Pangkat Berbasis Surat Keputusan
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: penyesuaian pangkat, SK massal tingkat kementerian, reuse satu dokumen untuk banyak personel, dan dampak ke dashboard admin serta riwayat pangkat
Keputusan terkait: `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota`
Rencana kerja terkait: `2026.07.12 Rencana Kerja tentang Sinkronisasi Artefak Surat Keputusan, Grup Angkatan, dan Penyesuaian Pangkat`
Mengubah: -
Digantikan oleh: `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota`

## Pemicu

Keputusan pangkat aktif sudah menegaskan bahwa perubahan pangkat setelah penetapan awal wajib melalui mekanisme pengajuan dengan dasar SK yang sah. Namun dari kasus lapangan terbaru, pola pengajuan tersebut ternyata tidak selalu berupa SK individual per personel.

Ada kebutuhan untuk mengakomodasi `Keputusan Menteri Pertahanan` yang memuat banyak nama sekaligus dalam satu dokumen, lalu dokumen yang sama dapat dipakai berkali-kali untuk memproses penyesuaian pangkat beberapa personel berbeda selama nama personel tersebut memang tercantum di lampiran.

## Temuan Awal

Analisis awal terhadap dokumen aktif dan kebutuhan terbaru menunjukkan:

- keputusan pangkat aktif `2026.Kep.005` sudah mengunci bahwa penyesuaian pangkat wajib berbasis SK, tetapi format dan mekanisme SK masih dinyatakan belum dibakukan
- diskusi regulasi aktif sudah mulai mengakui `Surat Keputusan` sebagai artefak sumber domain aktif, tetapi belum menurunkan pola reuse satu SK untuk banyak personel pada domain pangkat
- diskusi grup berbasis `abituren` dan `Surat Keputusan` sudah menegaskan bahwa satu SK dapat dipakai untuk lebih dari satu proses domain tanpa unggah ulang file
- file aktif admin personel saat ini sudah menuntut data pengajuan pangkat seperti dasar pengajuan, nomor SK, tanggal SK, dan judul SK, tetapi belum memiliki pola khusus untuk satu SK massal yang dipakai banyak personel
- kebutuhan terbaru menunjukkan bahwa setelah admin mengunggah satu surat, admin ingin bisa mengajukan nama-nama di dalam lampiran surat itu untuk diproses otomatis naik pangkat, sambil tetap mencatat hasilnya di dashboard atau riwayat personel masing-masing

## Opsi yang Dipertimbangkan

### Opsi A: Tetap anggap semua penyesuaian pangkat sebagai proses per personel

Setiap personel tetap membawa entri pengajuan sendiri walaupun sumber SK-nya sama.

Kelebihan:

- lebih sederhana untuk alur existing
- lebih dekat dengan pola form edit personel yang sudah ada

Kekurangan:

- rawan unggah atau input berulang untuk surat yang sama
- kurang efisien untuk SK dengan puluhan atau ratusan nama

### Opsi B: Satu SK menjadi sumber massal, lalu personel dipilih dari lampiran

Admin mengunggah atau mendaftarkan satu SK, lalu sistem memproses personel yang tercantum di dalamnya.

Kelebihan:

- lebih sesuai dengan bentuk dokumen lapangan
- meminimalkan duplikasi metadata SK
- cocok untuk proses batch

Kekurangan:

- perlu pola validasi tambahan antara data lampiran dan master personel
- perlu penanganan kasus ketika personel belum ada di database atau datanya belum cocok penuh

## Poin Diskusi

### 1. Bentuk dokumen sumber penyesuaian pangkat perlu dicatat

Contoh bentuk dokumen yang diberikan user:

- `Keputusan Menteri Pertahanan`
- nomor `Kep/880/M/V/2026`
- tentang `Penyesuaian Kepangkatan Anggota Komponen Cadangan Tahun Anggaran 2026`
- tanggal `08 Mei 2026`

Ini menunjukkan bahwa dokumen sumber penyesuaian pangkat bisa berupa SK tingkat kementerian yang bersifat massal, bukan hanya SK individual.

### 2. Struktur lampiran perlu dikenali sebagai sumber data kerja

Contoh kolom lampiran yang diberikan user:

- `No urut`
- `Nama lengkap`
- `Pangkat dan jabatan lama`
- `NIKC`
- `Pangkat dan jabatan baru`
- `Pendidikan`
- `TMT Penetapan`
- `Ket`

Contoh data yang diberikan user juga menunjukkan bahwa satu surat dapat memuat banyak matra, banyak basis pendidikan, dan banyak jenis perubahan pangkat, misalnya:

- `Prada KC` menjadi `Letda KC`
- `Prada KC` menjadi `Serda KC`
- personel dari matra berbeda dipetakan ke jabatan baru yang mungkin lintas satuan atau lintas konteks

### 3. Satu SK harus bisa dipakai berkali-kali untuk personel yang berbeda

Norma awal yang perlu dicatat dari kasus user:

- satu dokumen `Keputusan Menteri Pertahanan` yang sama dapat dipakai berkali-kali
- syaratnya, nama personel tersebut memang tercantum di dokumen itu
- admin tidak perlu mengunggah dokumen yang sama berulang kali hanya karena memproses nama yang berbeda pada waktu yang berbeda

Norma yang diputuskan user:

- jika SK diunggah oleh `Super Admin`, `Super Admin` boleh menggunakan kewenangannya untuk memproses penyesuaian pangkat sesuai `Surat Keputusan` tersebut
- pemrosesan oleh `Super Admin` hanya sah bila nama personel dan `NIKC` sesuai dengan data di database
- bila nama atau `NIKC` tidak sesuai dengan database, penyesuaian pangkat tidak boleh langsung diproses
- jika `personel` mengajukan manual karena `Super Admin` belum memiliki softcopy surat, surat yang diunggah `personel` tetap disimpan di sistem dan dapat direuse
- jika pada pengajuan berikutnya metadata SK yang sama sudah terdeteksi di sistem dan softcopy sudah tersedia, `personel` lain tidak wajib mengunggah ulang SK yang sama

### 4. Hasil proses harus tetap tercermin pada dashboard personel masing-masing

Kebutuhan user menegaskan bahwa walaupun proses sumbernya massal:

- hasil penyesuaian pangkat tetap harus tercatat pada dashboard atau riwayat personel masing-masing
- sistem tidak cukup hanya mencatat bahwa satu surat pernah diunggah
- perlu ada relasi yang jelas antara dokumen sumber dan personel-personel yang diproses dari dokumen tersebut

Jika ada nama di lampiran tetapi belum ada di database:

- nama lengkap dan `NIKC` tetap perlu dicatat sebagai entri lampiran
- jika di kemudian hari ada `personel` yang mendaftar dengan nama lengkap dan `NIKC` yang sesuai, relasi ke dashboard personel dapat disambungkan otomatis

### 5. Kebutuhan batch admin perlu dibedakan dari hak normatif

Ada dua lapisan yang jangan dicampur:

- lapisan normatif:
  - perubahan pangkat hanya sah jika nama personel memang ada di lampiran SK
  - riwayat pangkat per personel wajib tercatat
- lapisan operasional:
  - admin perlu bisa memproses banyak nama dari satu surat
  - surat yang sama dapat direferensikan ulang tanpa unggah ulang

Arahan yang diputuskan user:

- saat admin mengunggah SK massal, sistem boleh langsung mencoba mencocokkan semua nama ke database
- tetapi sistem juga tetap perlu membuka opsi agar admin memilih atau memproses nama satu per satu
- dengan demikian, kedua pola operasional sah:
  - pencocokan massal otomatis
  - pemilihan per entri secara manual

### 6. Hubungan dengan NIKC dan grup perlu dijaga

Walaupun fokus dokumen ini adalah pangkat, contoh lampiran menunjukkan bahwa:

- `NIKC` tetap muncul sebagai data penting di lampiran
- `TMT Penetapan` juga muncul di surat penyesuaian pangkat

Karena itu, mekanisme penyesuaian pangkat tidak boleh diam-diam:

- mengubah `angkatan`
- mengubah hak grup
- atau mengubah `NIKC`

kecuali ada keputusan domain lain yang memang membenarkannya.

### 7. UI dan UX alur batch perlu diselaraskan dengan pedoman aktif

Jika alur unggah satu SK untuk banyak nama nantinya diimplementasikan:

- UI admin perlu membedakan jelas antara metadata surat, lampiran surat, daftar nama yang cocok ke database, dan hasil proses per personel
- status tiap nama perlu terbaca jelas, misalnya cocok, belum cocok, sudah diproses, atau perlu verifikasi manual
- pola visual dan komponen harus mengikuti keputusan UI aktif dan tidak boleh melahirkan pola baru yang liar

Norma operasional tambahan yang diputuskan user:

- baik pengajuan oleh `personel` maupun pemrosesan oleh `Super Admin` wajib melalui halaman checkpoint verifikasi
- checkpoint verifikasi ini dipakai untuk memastikan data yang akan diajukan benar-benar sesuai dengan `Surat Keputusan`
- perubahan status pangkat tidak boleh langsung final tanpa checkpoint verifikasi tersebut

### 8. Arah import terstruktur dan OCR dicatat sebagai fase berikutnya

User menegaskan bahwa:

- arah `import` terstruktur dan atau `OCR` memang dibutuhkan
- tetapi ia diposisikan sebagai fase berikutnya, bukan prasyarat untuk menutup norma dasar mekanisme penyesuaian pangkat sekarang

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | SK penyesuaian pangkat bisa berupa dokumen massal tingkat kementerian | Muncul jelas dari contoh user | Clear |
| 2 | Satu SK dapat memuat banyak nama dan banyak entri perubahan pangkat | Muncul jelas dari contoh user | Clear |
| 3 | Satu SK yang sama dapat dipakai berkali-kali untuk memproses personel yang tercantum di dalamnya | Disetujui oleh user | Clear |
| 4 | Hasil penyesuaian pangkat tetap harus tercatat pada dashboard atau riwayat personel masing-masing | Disetujui oleh user | Clear |
| 5 | `Super Admin` boleh memproses langsung dari SK yang diunggah bila nama dan `NIKC` cocok dengan database | Disetujui oleh user | Clear |
| 6 | Pengajuan manual oleh `personel` dapat menjadi sumber softcopy yang kemudian direuse untuk orang lain pada SK yang sama | Disetujui oleh user | Clear |
| 7 | Jika metadata dan softcopy SK yang sama sudah ada di sistem, pengaju berikutnya tidak wajib unggah ulang | Disetujui oleh user | Clear |
| 8 | Saat admin unggah SK massal, sistem boleh mencocokkan semua nama otomatis dan juga boleh memproses per entri manual | Disetujui oleh user | Clear |
| 9 | Nama dan `NIKC` yang belum punya personel di database tetap dicatat dan dapat disambungkan otomatis di kemudian hari | Disetujui oleh user | Clear |
| 10 | Checkpoint verifikasi wajib sebelum status pangkat berubah, baik untuk jalur `personel` maupun `Super Admin` | Disetujui oleh user | Clear |
| 11 | Import terstruktur dan atau OCR diposisikan sebagai fase berikutnya | Disetujui oleh user | Clear |

## Checklist Pra-Finalisasi

- [x] keputusan aktif yang beririsan sudah diaudit
- [x] contoh struktur SK dan lampiran sudah dicatat
- [x] kebutuhan reuse satu SK untuk banyak personel sudah dicatat
- [x] kebutuhan pencatatan hasil per personel di dashboard masing-masing sudah dicatat
- [x] arah mekanisme reuse satu SK untuk banyak personel sudah diputuskan
- [x] checkpoint verifikasi sebelum perubahan pangkat final sudah diputuskan
- [x] arah OCR atau import terstruktur sebagai fase berikutnya sudah diputuskan
- [x] hubungan dokumen ini dengan diskusi regulasi dan diskusi grup berbasis SK sudah cukup jelas untuk difinalkan
- [x] dampak normatifnya siap diturunkan ke revisi keputusan pangkat aktif

## Catatan untuk AI Agent

- jangan langsung revisi keputusan pangkat aktif sebelum user menyatakan arah final diskusi ini
- jangan asumsikan satu SK massal otomatis memproses semua nama tanpa checkpoint verifikasi
- jangan biarkan alur batch admin melahirkan perilaku yang menimpa riwayat pangkat individual
- sinkronkan diskusi ini dengan diskusi regulasi agar pola reuse satu dokumen tetap konsisten
- jangan paksa unggah ulang SK bila metadata dan softcopy yang sama sudah lebih dulu ada di sistem
- pastikan entri lampiran yang belum punya akun personel tetap dapat dicatat sebagai calon relasi yang akan disambungkan nanti

## Pertanyaan Terbuka

Tidak ada pertanyaan terbuka normatif utama yang tersisa pada dokumen ini. Turunan keputusan, referensi teknis, dan workplan implementasi dilanjutkan setelah diskusi ini difinalkan.

## Rencana Tindak Lanjut

- sinkronkan dokumen ini dengan diskusi regulasi dan keputusan pangkat aktif
- jika arah normatifnya disetujui, turunkan ke revisi keputusan `2026.Kep.005`
- setelah keputusan final lahir, turunkan kebutuhan batch upload, pencocokan lampiran, histori pangkat, dan tampilan dashboard ke workplan formal

## Pemetaan Clause ke Keputusan

### Clause yang masuk ke revisi `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota`

- penyesuaian pangkat dapat berbasis satu `Keputusan Menteri` atau `Surat Keputusan` massal yang memuat banyak nama sekaligus
- satu `Surat Keputusan` yang sama dapat dipakai berkali-kali untuk memproses personel berbeda selama nama personel dan `NIKC`-nya memang tercantum dan cocok
- `Super Admin` boleh memproses langsung dari `Surat Keputusan` yang tersedia bila data nama dan `NIKC` sesuai dengan database
- bila `Super Admin` belum memiliki softcopy, unggahan `personel` dapat menjadi sumber softcopy yang kemudian direuse pada pengajuan berikutnya
- jika metadata dan softcopy `Surat Keputusan` yang sama sudah ada di sistem, pengaju berikutnya tidak wajib mengunggah ulang
- saat SK massal diunggah, sistem boleh melakukan pencocokan massal otomatis dan juga boleh memproses per entri manual
- entri lampiran yang belum punya pasangan personel di database tetap dicatat berdasarkan nama lengkap dan `NIKC`, lalu dapat disambungkan otomatis di kemudian hari bila data cocok
- perubahan status pangkat wajib melalui checkpoint verifikasi, baik pada jalur `personel` maupun `Super Admin`
- hasil proses penyesuaian pangkat wajib tercermin pada dashboard atau riwayat personel masing-masing
- mekanisme penyesuaian pangkat tidak boleh diam-diam mengubah `Grup Angkatan` atau `NIKC` tanpa dasar keputusan domain lain

### Clause yang masuk ke revisi `2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal`

- artefak sumber `Surat Keputusan` massal cukup disimpan sebagai satu artefak sumber, satu ringkasan, dan satu indeks nama
- pola reuse `Surat Keputusan` lintas pengajuan harus konsisten dengan rumah referensi internal regulasi

### Clause yang diturunkan ke workplan dan referensi teknis

- desain alur batch upload dan reuse `Surat Keputusan`
- desain halaman checkpoint verifikasi sebelum perubahan pangkat final
- desain indeks nama lampiran dan mekanisme auto-link ke dashboard personel
- desain tahap lanjut `import` terstruktur dan atau `OCR`
- audit UI/UX daftar status nama, hasil pencocokan, dan histori proses agar tetap mengikuti pedoman aktif

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-12 | Dokumen dibuat untuk menampung kebutuhan mekanisme penyesuaian pangkat berbasis satu SK massal yang dapat dipakai untuk banyak personel dan tetap tercermin pada dashboard masing-masing |
| 2026-07-12 | User memutuskan bahwa `Super Admin` boleh memproses langsung dari SK yang cocok, pengajuan `personel` dapat menyumbang softcopy untuk reuse, pencocokan massal dan proses per entri sama-sama sah, entri lampiran yang belum cocok tetap dicatat, checkpoint verifikasi wajib, dan OCR atau import terstruktur diposisikan sebagai fase berikutnya |
| 2026-07-12 | Ditambahkan pemetaan clause ke keputusan target agar revisi keputusan pangkat, keputusan regulasi, dan workplan turunan tidak saling tumpang tindih |
