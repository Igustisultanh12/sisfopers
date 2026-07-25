# Diskusi
## tentang ADAPTASI STANDAR IDENTITAS DAN PENAMAAN DOKUMEN FORMAL

Status: `Selesai`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang Adaptasi Standar Identitas dan Penamaan Dokumen Formal
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: adaptasi keputusan standar identitas dokumen formal dari repo pembanding ke repo bootstrap `kctrimatra`
Keputusan terkait: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: -

> Status Arsip: `Selesai`
> Selesai pada: 2026-07-03 13:18:11 WIB
> Keputusan final yang dihasilkan: `docs/02 Keputusan/2026.Kep.001 tentang Standar Identitas dan Penamaan Dokumen Formal.md`
> Catatan penutupan: diskusi difinalkan dan diarsipkan untuk melahirkan keputusan governance payung pertama repo.

## Pemicu

Diskusi ini dibuka untuk meniru dan mengadaptasi pola keputusan berikut ke repo `kctrimatra`:

- standar identitas dan penamaan dokumen formal dari repo `dewa_cuan`
- standar identitas dan penamaan dokumen formal dari repo `profildiri`

Kebutuhan ini muncul karena `kctrimatra` sudah memiliki struktur `docs/` lifecycle, tetapi belum memiliki keputusan formal yang menetapkan:

- identitas dokumen aktif
- otoritas rumah dokumen
- batas aksi agent
- aturan transisi, arsip, dan penutupan workplan

## Temuan Awal

Audit awal terhadap file dan dokumen aktif yang beririsan menghasilkan temuan berikut.

### File dan dokumen yang diaudit

- `docs/README.md`
- `docs/01 Profil Sistem/PROJECT_STATUS.md`
- `docs/04 Diskusi/README.md`
- `AGENTS.md`
- diskusi aktif tentang GitHub, Gitea, dan jalur deploy
- dua keputusan `2026.02.001` dari repo pembanding

### Kondisi repo `kctrimatra` yang terverifikasi

- status repo masih `bootstrap`
- struktur lifecycle docs sudah berjalan
- belum ada keputusan formal aktif di `docs/02 Keputusan/`
- belum ada workplan aktif di `docs/03 Rencana Kerja/`
- referensi teknis belum lahir dan masih berstatus `rencana`

### Clause yang sudah implicit hidup di `kctrimatra`

Beberapa aturan hasil adaptasi sebenarnya sudah hidup secara operasional di repo ini melalui `docs/README.md`, `AGENTS.md`, dan `README` per-folder:

- lifecycle utama `04 Diskusi -> 00 Arsip -> 02 Keputusan -> 03 Rencana Kerja`
- penamaan aktif untuk diskusi dan rencana kerja memakai format tanggal, bukan nomor urut
- keputusan memakai format `YYYY.02.XXX`
- referensi teknis memakai format `YYYY.11.XXX`
- prefix status hanya sah di arsip
- dokumen turunan tidak boleh lahir saat topik masih diskusi
- urutan publish wajib adalah `commit -> push -> deploy`

Namun semua aturan di atas masih tersebar di beberapa dokumen dan belum berdiri sebagai keputusan formal tunggal yang memegang norma payung.

### Clause penting dari repo pembanding yang layak diadopsi

Dari dua repo pembanding, pola yang paling kuat dan lintas-domain adalah:

- identitas dokumen aktif harus stabil dan tidak memakai status sementara
- metadata formal umum harus seragam
- keputusan hanya boleh dicabut oleh keputusan
- `11 Referensi Teknis` adalah rumah teknis turunan, bukan otoritas normatif
- agent wajib memilih delta perubahan minimum
- `hapus lalu buat ulang` bukan pendekatan default
- selama topik masih diskusi, agent dilarang melahirkan keputusan, workplan, atau referensi teknis baru
- workplan tidak boleh ditutup otomatis tanpa QA user atau `skip QA` yang jujur
- validasi kepatuhan workplan harus berbasis teks normatif yang tertulis, bukan niat
- merger atau lebur dokumen lifecycle wajib menjaga arsip sumber dan metadata historis

### Clause dari repo pembanding yang tidak perlu diserap mentah

Berikut adalah clause yang dinilai terlalu kontekstual terhadap sejarah `dewa_cuan` atau `profildiri`, sehingga tidak perlu ikut ke keputusan pertama `kctrimatra`:

- clause yang menyebut dokumen lama spesifik repo asal, seperti `UI_STANDARD.md`, `SPESIFIKASI_TERKINI.md`, `00-pedoman-proyek/...`, atau dokumen deploy tertentu
- clause yang lahir dari audit atau insiden historis repo asal pada tanggal tertentu
- clause domain-spesifik seperti purchase, supplier, finance, access code, payment, reviewer Midtrans, public page sensitif, batch payment, atau instrumen tes
- clause yang mengatur rumah dokumen tambahan yang belum ada di `kctrimatra`
- clause transisi yang hanya relevan untuk canonical lama repo asal
- clause yang bergantung pada kompleksitas domain yang belum lahir di repo `kctrimatra`

Yang diserap ke `kctrimatra` sebaiknya hanya aturan yang umum, lintas-domain, dan tegas.

### Gap atau konflik yang terdeteksi

- `kctrimatra` sudah memakai format naming baru, tetapi belum punya keputusan formal yang membakukan format itu
- belum ada keputusan yang menegaskan `11 Referensi Teknis` sebagai rumah teknis turunan
- belum ada keputusan formal tentang urutan kerja dokumen yang hemat jejak seperti `revisi -> rename -> arsip -> file baru`
- belum ada keputusan formal tentang batas aksi agent saat user masih bekerja di level diskusi
- belum ada keputusan formal tentang QA penutupan workplan, walau workplan sendiri belum lahir
- karena repo masih baru, sebagian clause dari repo pembanding perlu dipadatkan agar tidak terlalu berat untuk baseline awal

### Potensi drift yang sudah terlihat

- aturan lifecycle, naming, dan status arsip sudah hidup di `docs/README.md`, `AGENTS.md`, dan README per-folder, tetapi belum punya satu keputusan payung yang mengunci semuanya
- `docs/03 Rencana Kerja/README.md` dan `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md` sudah cukup detail, tetapi belum dinaungi keputusan formal yang setara
- diskusi GitHub/Gitea berpotensi drift menjadi keputusan operasional, padahal arahnya lebih sehat bila diturunkan sebagai referensi teknis setelah keputusan governance lahir
- repo masih kosong, sehingga ada risiko dokumen bootstrap yang terlalu singkat ditafsirkan longgar, atau sebaliknya dokumen yang terlalu berat ditinggalkan saat implementasi mulai berjalan
- `PROJECT_STATUS.md` dan `STACK_DAN_BASELINE.md` bisa terbaca terlalu normatif bila tidak dijaga sebagai dokumen baseline yang tunduk pada keputusan payung

## Opsi yang Dipertimbangkan

- **Opsi A: Adopsi ketat dengan adaptasi minimal**
  - Kelebihan:
    - paling konsisten dengan repo pembanding
    - sejak awal repo punya governance yang tegas
  - Kekurangan:
    - berisiko terlalu berat untuk repo yang masih bootstrap
    - beberapa clause bisa terasa prematur sebelum workplan dan keputusan domain pertama lahir

- **Opsi B: Adopsi inti governance, ringkas bagian yang belum relevan**
  - Kelebihan:
    - cocok untuk kondisi `kctrimatra` yang masih baru
    - tetap menjaga fondasi normatif yang kuat
    - lebih mudah dipahami dan dirawat pada fase awal
  - Kekurangan:
    - perlu disiplin untuk menambah clause lanjutan saat kompleksitas repo meningkat

- **Opsi C: Cukup pakai README yang ada tanpa keputusan formal baru**
  - Kelebihan:
    - tidak menambah dokumen normatif baru sekarang
  - Kekurangan:
    - source of truth tetap tersebar
    - aturan agent, arsip, metadata, dan transisi belum punya satu otoritas payung
    - berisiko drift saat repo mulai bertambah kompleks

## Poin Diskusi

### 1. `kctrimatra` sudah siap punya keputusan governance pertama

Berbeda dari kondisi nol total, repo ini sudah punya:

- struktur lifecycle aktif
- guardrail dasar agent
- pola penamaan diskusi, keputusan, dan referensi teknis

Artinya, kebutuhan berikutnya bukan lagi menyusun struktur dasar, tetapi mengunci struktur itu menjadi keputusan resmi agar tidak mudah drift.

### 2. Pola dua repo pembanding sangat cocok dijadikan kerangka

Dua keputusan `2026.02.001` dari repo pembanding pada dasarnya bukan keputusan domain bisnis, melainkan keputusan governance repo. Itu membuatnya sangat cocok untuk ditiru ke repo baru selama ditulis ulang sesuai konteks `kctrimatra`.

Yang paling aman untuk diserap hampir tanpa perubahan konsep adalah:

- identitas dokumen formal aktif
- status arsip yang sah
- metadata header formal umum
- relasi rumah dokumen
- otoritas pencabutan keputusan
- guardrail lifecycle saat topik masih diskusi

### 3. Clause efisiensi edit agent juga layak diadaptasi

Salah satu bagian paling kuat dari repo pembanding adalah guardrail teknis kerja agent, misalnya:

- pilih delta perubahan minimum
- prioritaskan `revisi -> rename -> arsip -> file baru`
- jangan `hapus lalu buat ulang` bila perubahan kecil cukup diedit

Untuk repo yang baru tumbuh, clause seperti ini sangat berguna karena bisa menjaga histori dan mencegah noise sejak awal.

### 4. Clause workplan perlu diadaptasi proporsional

Repo pembanding punya clause yang sangat detail tentang schema workplan, QA user, `skip QA`, dan penutupan. Secara prinsip ini bagus dan layak ditiru.

Untuk arah repo ini, user menegaskan bahwa clause schema workplan detail boleh dan layak diadopsi penuh jika memang diperlukan. Karena `kctrimatra` ingin punya pondasi dokumen yang kuat sejak awal, arah paling konsisten adalah:

- keputusan governance pertama tetap memuat prinsip dan schema workplan formal yang tegas
- detail operasional workplan yang sudah hidup di `03 Rencana Kerja/README.md` dan `AGENT-INSTRUCTIONS.md` diperlakukan sebagai turunan yang harus selaras, bukan sumber norma yang berdiri sendiri

Dengan demikian, area workplan tidak dibiarkan longgar hanya karena repo masih bootstrap.

### 5. Tingkat adaptasi yang paling sehat bukan menyalin mentah dan bukan juga merampingkan berlebihan

Repo pembanding lahir dari proyek yang sudah kompleks, sehingga keputusannya ikut memuat banyak clause korektif dari insiden dan drift lama. `kctrimatra` belum punya beban historis itu.

Karena itu, adaptasi yang baik bukan menyalin seluruh panjang dokumen, tetapi juga bukan merampingkan sampai kehilangan ketegasan. Arah yang paling sehat adalah:

- mempertahankan clause yang sifatnya struktural
- mempertahankan ketegasan pada identitas, lifecycle, metadata, rumah dokumen, batas aksi agent, efisiensi edit, dan penutupan workplan
- menyederhanakan clause yang terlalu reaktif terhadap konteks repo lama
- menunda hanya bagian yang benar-benar belum punya objek nyata di repo ini

### 6. Aturan efisiensi edit agent memang layak masuk langsung ke keputusan pertama

User sudah menegaskan bahwa aturan efisiensi edit agent perlu langsung dimasukkan ke keputusan formal pertama. Ini konsisten dengan manfaatnya untuk repo baru:

- menjaga histori tetap rapi
- mengurangi noise edit saat dokumen masih sedikit
- mencegah pola `hapus lalu buat ulang` menjadi kebiasaan sejak awal

Clause seperti `delta minimum`, `revisi -> rename -> arsip -> file baru`, dan larangan menghapus lalu membuat ulang tanpa kebutuhan substantif karenanya layak diperlakukan sebagai bagian inti keputusan payung pertama.

### 7. Satu keputusan payung dulu, teknis lahir sebagai referensi teknis

Arah yang sudah dipilih user adalah mengikuti pola `dewa_cuan` dan `profildiri`:

- satu keputusan governance/payung lahir lebih dulu
- keputusan itu memegang norma lifecycle, identitas dokumen, metadata formal, rumah dokumen, batas aksi agent, dan prinsip workplan
- hal yang implementatif lahir kemudian sebagai `11 Referensi Teknis`

Implikasinya untuk diskusi lain yang sudah hidup di repo ini:

- diskusi GitHub/Gitea tidak perlu dilebur substansinya ke keputusan governance
- jika nanti topik GitHub/Gitea perlu hidup sebagai dokumen aktif, rumah yang paling sehat adalah `11 Referensi Teknis`
- bila ada norma kecil yang tetap perlu dikunci untuk remote atau deploy, ia cukup lahir sebagai keputusan turunan terpisah hanya jika benar-benar diperlukan

### 8. Keputusan ini kemungkinan akan berdampak ke dokumen aktif yang sudah ada

Jika keputusan formal nanti lahir, sangat mungkin ia perlu menyelaraskan atau minimal mengukuhkan isi dari:

- `docs/README.md`
- `docs/02 Keputusan/README.md`
- `docs/03 Rencana Kerja/README.md`
- `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`
- `docs/04 Diskusi/README.md`
- `docs/00 Arsip/README.md`
- `AGENTS.md`

Karena aturan-aturan dasar yang sekarang tersebar di file-file itu akan punya sumber normatif baru yang lebih tinggi.

### 9. GitHub/Gitea untuk saat ini cukup turun menjadi referensi teknis

Hasil analisis lanjutan dan arahan user menyepakati bahwa topik GitHub/Gitea untuk fase repo saat ini belum perlu melahirkan keputusan kecil terpisah.

Arah yang dipilih:

- diskusi GitHub/Gitea cukup diturunkan menjadi satu referensi teknis setelah keputusan payung lahir
- keputusan baru terkait remote atau deploy boleh lahir di kemudian hari jika repo sudah punya kebutuhan normatif yang lebih tegas

Dengan arah ini, detail seperti:

- URL remote
- standar SSH Gitea
- verifikasi remote
- checklist push

tetap hidup sebagai panduan teknis, bukan norma payung.

### 10. Batch sinkronisasi pertama harus fokus ke dokumen governance inti

Setelah keputusan payung lahir, dokumen aktif yang paling perlu diselaraskan pada batch pertama adalah:

- `docs/README.md`
- `AGENTS.md`
- `docs/02 Keputusan/README.md`
- `docs/04 Diskusi/README.md`
- `docs/03 Rencana Kerja/README.md`
- `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`
- `docs/00 Arsip/README.md`

Alasan prioritas:

- ketujuh dokumen tersebut saat ini membentuk perilaku agent, lifecycle, naming, arsip, dan workplan
- bila tidak segera diselaraskan, keputusan payung baru akan langsung drift terhadap instruksi aktif yang dibaca harian

Dokumen yang tidak wajib masuk batch pertama:

- `docs/01 Profil Sistem/PROJECT_STATUS.md`
- `docs/01 Profil Sistem/STACK_DAN_BASELINE.md`
- diskusi GitHub/Gitea
- `docs/11 Referensi Teknis/README.md`

Dokumen-dokumen itu boleh diselaraskan setelah inti governance stabil, kecuali keputusan payung nanti mengubah fungsi atau statusnya secara eksplisit

### 11. Sinkronisasi pascakeputusan mengikuti skala perubahan

Arah yang disepakati untuk repo ini adalah:

- jika sinkronisasi pascakeputusan hanya ringan dan terbatas pada penyelarasan kecil, ia boleh langsung dikerjakan
- jika sinkronisasi pascakeputusan banyak, kompleks, menyentuh banyak clause lintas dokumen, atau butuh verifikasi bertahap, wajib lahir workplan formal di `03 Rencana Kerja/`

Dengan demikian, keputusan payung tidak otomatis memaksa workplan untuk setiap penyelarasan, tetapi lifecycle tetap dijaga saat skala perubahan sudah cukup besar.

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Kebutuhan keputusan governance formal di `kctrimatra` | Nyata dan relevan | Clear |
| 2 | Kelayakan dua dokumen `2026.02.001` sebagai referensi utama | Sangat layak ditiru dan diadaptasi | Clear |
| 3 | Identitas dokumen, status arsip, metadata formal, dan rumah dokumen | Layak diadopsi | Clear |
| 4 | Guardrail delta edit minimum dan batas aksi agent saat level diskusi | Layak diadopsi | Clear |
| 5 | Detail schema workplan dari repo pembanding | Layak diadopsi penuh bila diperlukan dan dinaungi keputusan payung | Clear |
| 6 | Tingkat kepadatan keputusan pertama `kctrimatra` | Harus meniru yang perlu: lengkap, tegas, dan kontekstual; bukan salin mentah dan bukan versi terlalu ramping | Clear |
| 7 | Aturan efisiensi edit agent | Harus masuk langsung ke keputusan formal pertama | Clear |
| 8 | Relasi keputusan payung vs referensi teknis | Satu keputusan payung dulu; hal implementatif lahir sebagai referensi teknis turunan | Clear |
| 9 | Posisi topik GitHub/Gitea saat ini | Cukup diturunkan menjadi referensi teknis; keputusan baru bisa lahir nanti bila kebutuhan normatif membesar | Clear |
| 10 | Batch sinkronisasi pertama setelah keputusan payung | Harus fokus ke tujuh dokumen governance inti agar tidak drift | Clear |
| 11 | Cara menentukan perlu tidaknya workplan pascakeputusan | Perubahan ringan boleh langsung; perubahan banyak atau kompleks wajib memakai workplan formal | Clear |
| 12 | Dampak penyelarasan ke dokumen aktif repo | Ada dan perlu direncanakan | Clear |

## Checklist Pra-Finalisasi

| # | Yang Wajib Dicatat | Sudah Dibahas? | Catatan |
| --- | --- | --- | --- |
| 1 | Scope (`termasuk` dan `tidak termasuk`) | Ya | Fokus pada identitas dokumen, lifecycle, metadata, guardrail agent, dan penutupan workplan |
| 2 | Non-goals | Ya | Tidak membahas domain bisnis Laravel, deploy, atau UI produk |
| 3 | Exception (`jika relevan`) | Ya | Clause yang terlalu berat untuk repo bootstrap boleh dipadatkan |
| 4 | Dampak ke fitur lain | Ya | Mempengaruhi semua dokumen governance aktif |
| 5 | Dampak ke Profil Sistem (`jika relevan`) | Ya | `PROJECT_STATUS.md` bisa perlu diselaraskan setelah keputusan lahir |
| 6 | Data / Schema impact (`jika relevan`) | Ya | Tidak ada dampak schema database |
| 7 | Hal yang tidak berubah | Ya | Struktur lifecycle yang sudah ada tetap dipertahankan |
| 8 | Acceptance criteria | Ya | Dirangkum di bawah |

Acceptance criteria yang diharapkan bila topik ini nanti difinalkan:

- ada keputusan resmi yang membakukan identitas dokumen aktif dan status arsip
- ada keputusan resmi yang menetapkan metadata formal umum
- ada keputusan resmi yang menetapkan `11 Referensi Teknis` sebagai rumah turunan teknis
- ada keputusan resmi yang menegaskan batas aksi agent saat topik masih diskusi
- ada keputusan resmi yang menetapkan prinsip penutupan workplan dan QA secara jujur
- ada keputusan resmi yang memasukkan aturan efisiensi edit agent
- ada batas yang jelas antara keputusan payung dan referensi teknis turunan
- ada rencana sinkronisasi batch pertama untuk dokumen governance inti

## Catatan untuk AI Agent

- jangan menyalin mentah dokumen repo pembanding; tulis ulang sesuai domain `kctrimatra`
- prioritaskan clause yang struktural dan lintas-domain
- hindari memasukkan clause yang terlalu spesifik pada domain yang belum ada di repo ini
- jika keputusan lahir nanti, pastikan isinya menjadi source of truth yang lebih tinggi daripada README yang sekarang
- pastikan aturan efisiensi edit agent masuk eksplisit ke keputusan payung
- pastikan workplan schema yang sudah hidup di `docs/03 Rencana Kerja/README.md` dan `AGENT-INSTRUCTIONS.md` diselaraskan ke keputusan, bukan dibiarkan setara tanpa hirarki
- jangan lebur detail GitHub/Gitea ke keputusan governance; arahkan topik tersebut menjadi referensi teknis turunan setelah rumah normatifnya sah
- setelah keputusan lahir, prioritaskan penyelarasan tujuh dokumen governance inti sebelum memperluas repo ke area dokumen lain

## Pertanyaan Terbuka

- clause mana dari repo pembanding yang benar-benar terlalu kontekstual terhadap sejarah `dewa_cuan` atau `profildiri` sehingga sebaiknya tidak ikut diserap ke keputusan pertama `kctrimatra`

## Jawaban atas Pertanyaan yang Sudah Mengerucut

- clause yang terlalu historis, terlalu domain-spesifik, atau terlalu bergantung pada dokumen lama repo pembanding tidak perlu ikut diserap ke keputusan pertama `kctrimatra`
- diskusi GitHub/Gitea untuk saat ini cukup turun menjadi satu referensi teknis; keputusan terpisah boleh lahir nanti bila kebutuhan normatifnya membesar
- batch pertama penyelarasan setelah keputusan payung lahir harus fokus pada tujuh dokumen governance inti: `docs/README.md`, `AGENTS.md`, `docs/02 Keputusan/README.md`, `docs/04 Diskusi/README.md`, `docs/03 Rencana Kerja/README.md`, `docs/03 Rencana Kerja/AGENT-INSTRUCTIONS.md`, dan `docs/00 Arsip/README.md`
- jika sinkronisasi pascakeputusan hanya ringan, ia boleh langsung dikerjakan; jika banyak atau kompleks, wajib lahir workplan formal sesuai lifecycle

## Potensi Drift yang Perlu Dijaga

- keputusan payung lahir tetapi `docs/README.md`, `AGENTS.md`, dan README per-folder tidak segera diselaraskan, sehingga source of truth kembali tersebar
- `03 Rencana Kerja/README.md` dan `AGENT-INSTRUCTIONS.md` mempertahankan schema yang sedikit berbeda dari keputusan payung
- diskusi GitHub/Gitea melahirkan referensi teknis yang diam-diam memuat norma baru tanpa keputusan payung atau keputusan turunan yang memadai
- dokumen bootstrap awal nanti direvisi per bagian tanpa menjaga urutan `revisi -> rename -> arsip -> file baru`, sehingga histori governance cepat berantakan
- saat repo mulai berisi kode Laravel, perubahan runtime dikerjakan lebih cepat daripada pembaruan dokumen normatif, sehingga drift antara praktik dan keputusan muncul terlalu dini
- dokumen baseline profil sistem seperti `PROJECT_STATUS.md` dan `STACK_DAN_BASELINE.md` terbaca sebagai norma final, padahal keduanya seharusnya hanya menggambarkan kondisi bootstrap dan asumsi teknis awal

## Rencana Tindak Lanjut

- [x] arah adaptasi dipersempit: meniru yang perlu, lengkap dan tegas, tidak menyalin mentah, dan tidak merampingkan berlebihan
- [x] arah rumah dokumen dipersempit: satu keputusan payung lebih dulu, teknis lahir sebagai referensi teknis turunan
- [x] posisi GitHub/Gitea dipersempit: untuk saat ini cukup diturunkan menjadi referensi teknis
- [x] batch sinkronisasi pertama dipetakan ke tujuh dokumen governance inti
- [ ] finalkan diskusi ini menjadi dasar keputusan governance formal pertama di `kctrimatra`
- [ ] setelah keputusan lahir, selaraskan dokumen aktif yang terdampak
- [ ] jika diperlukan, turunkan workplan sinkronisasi docs aktif dari keputusan tersebut

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-03 | Dokumen dibuat untuk membahas adaptasi standar identitas dan penamaan dokumen formal dari `dewa_cuan` dan `profildiri` ke `kctrimatra` |
| 2026-07-03 | Dokumen dilengkapi dengan arah keputusan payung, posisi referensi teknis turunan, potensi drift, dan penyempitan pertanyaan terbuka |
| 2026-07-03 | Dokumen dilengkapi dengan analisis clause yang tidak perlu diserap, posisi GitHub/Gitea sebagai referensi teknis, dan prioritas batch sinkronisasi pertama |
| 2026-07-03 | Dokumen dilengkapi dengan aturan penentuan sinkronisasi langsung vs workplan formal setelah keputusan payung lahir |
