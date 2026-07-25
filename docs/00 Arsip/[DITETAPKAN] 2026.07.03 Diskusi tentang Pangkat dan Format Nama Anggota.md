# Diskusi
## tentang PANGKAT DAN PREFIX NAMA ANGGOTA

Status: `Ditetapkan`
Tanggal dibuka: 3 Juli 2026
Identitas dokumen: 2026.07.03 Diskusi tentang Pangkat dan Format Nama Anggota
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: master pangkat, hirarki pangkat, prefix nama anggota, riwayat pangkat, dan validasi aturan pangkat anggota
Keputusan terkait: `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota`
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: `2026.Kep.005 tentang Standar Kepangkatan dan Riwayat Pangkat Anggota`

## Pemicu

Bahan user memuat domain pangkat yang rinci, termasuk hirarki, format nama, dan batas tertentu berdasarkan jenis kelamin. Karena dampaknya besar pada identitas tampilan anggota dan riwayat karier, topik ini perlu dipisahkan dari diskusi anggota umum.

## Temuan Awal

- belum ada keputusan final tentang apakah pangkat termasuk data inti anggota atau riwayat terpisah
- bahan user memuat aturan hirarki pangkat dan format display nama
- topik ini beririsan dengan pendidikan dan dokumen pendukung, tetapi belum boleh dilompati ke referensi teknis

## Bahan User Awal yang Harus Tercatat

Bahan yang sempat disampaikan user pada sesi ini memuat daftar pangkat yang lebih lengkap untuk dibahas sebagai rancangan awal:

| Urutan | Nama Pangkat | Singkatan | Varian Perempuan |
| --- | --- | --- | --- |
| 1 | Prajurit Dua | Prada KC | - |
| 2 | Prajurit Satu | Pratu KC | - |
| 3 | Prajurit Kepala | Praka KC | - |
| 4 | Kopral Dua | Kopda KC | - |
| 5 | Kopral Satu | Koptu KC | - |
| 6 | Kopral Kepala | Kopka KC | - |
| 7 | Sersan Dua | Serda KC | `Serda KC (W)` |
| 8 | Sersan Satu | Sertu KC | `Sertu KC (W)` |
| 9 | Sersan Kepala | Serka KC | `Serka KC (W)` |
| 10 | Sersan Mayor | Serma KC | `Serma KC (W)` |
| 11 | Pelda | Pelda KC | `Pelda KC (W)` |
| 12 | Peltu | Peltu KC | `Peltu KC (W)` |
| 13 | Letnan Dua | Letda KC | `Letda KC (W)` |
| 14 | Letnan Satu | Lettu KC | `Lettu KC (W)` |
| 15 | Kapten | Kapten KC | `Kapten KC (W)` |
| 16 | Mayor | Mayor KC | `Mayor KC (W)` |
| 17 | Letnan Kolonel | Letkol KC | `Letkol KC (W)` |
| 18 | Kolonel | Kolonel KC | `Kolonel KC (W)` |

Catatan atas bahan awal ini:

- daftar di atas adalah bahan draft yang harus tercatat, bukan otomatis keputusan final
- huruf `KC` pada draft awal diperlakukan sebagai bagian rancangan user yang perlu dipastikan lagi sebelum dipakai sebagai norma
- varian `(W)` di draft awal tetap dibaca sebagai varian gender, bukan master pangkat baru

## Arah Terkini

Setelah klarifikasi terbaru dari user, pangkat default yang bisa diklaim langsung adalah:

| Nama Pangkat | Singkatan | Varian Perempuan |
| --- | --- | --- |
| Prada KC | `Prada KC` | - |
| Serda KC | `Serda KC` | `Serda KC (W)` |
| Letda KC | `Letda KC` | `Letda KC (W)` |

Aturan inti yang mengikuti daftar ini:

- hanya ada 3 pangkat default yang bisa diklaim langsung
- `(W)` bukan pangkat baru, hanya varian display untuk perempuan
- laki-laki tidak pernah memakai `(W)`
- perempuan selalu memakai `(W)` saat tampil di prefix pangkat
- pangkat selain tiga di atas tidak menjadi pangkat default klaim
- pangkat selain tiga di atas hanya boleh muncul melalui:
  - penyesuaian pangkat berdasarkan ijazah
  - kenaikan pangkat
- kedua mekanisme di atas wajib memiliki Surat Keputusan
- kalau ada data lama yang menyimpan variasi lain, itu dianggap drift atau data warisan yang perlu diselaraskan pada domain lain

### Kondisi kode aktual

- pangkat sudah dipakai di registrasi awal, input admin, dan tampilan anggota
- front-end sudah menampilkan pangkat pada dashboard, daftar personel, dan detail personel
- tampilan pangkat kini dapat dinormalisasi dari gender melalui atribut display, sehingga suffix `(W)` tidak perlu ditulis manual di setiap view
- data pangkat juga ikut dipakai saat broadcast dan laporan
- implementasi aktual menunjukkan pangkat bukan lagi wacana murni, tetapi atribut operasional yang sudah menyentuh banyak modul
- dokumen ini menjadi rumah tunggal untuk prefix pangkat, termasuk varian `(W)` berdasarkan gender
- format nama lengkap dengan suffix gelar dipisah ke diskusi baru agar sumbernya tidak bercampur
- validasi pangkat di form login dan master personel masih mengizinkan opsi lama, sehingga ada drift implementasi yang perlu diselaraskan ke tiga pangkat default klaim

## Poin Diskusi

### 1. Posisi pangkat sebagai data aktif vs riwayat

Perlu diputuskan apakah anggota hanya menyimpan pangkat aktif atau seluruh riwayat pangkat.

### 2. Format nama dengan pangkat perlu distabilkan

Jika pangkat muncul di banyak tampilan, format nama perlu dibakukan secara normatif terlebih dahulu.

### 3. Aturan khusus perlu diverifikasi secara domain

Jika ada batas tertentu berdasarkan jenis kelamin atau mekanisme kenaikan, hal itu perlu dipastikan sebagai kebijakan bisnis yang benar-benar diinginkan user.

### 4. Format display pangkat sudah perlu dibakukan

Karena pangkat sudah muncul di banyak layar, perlu diputuskan apakah format bakunya:

- selalu di depan nama
- hanya tampil di kartu profil dan daftar tertentu
- mengikuti format singkat atau lengkap

### 5. Batas sumber tunggal perlu ditegaskan

Untuk menjaga satu sumber kebenaran, dokumen ini hanya memegang:

- master pangkat
- prefix pangkat di depan nama
- varian `(W)` untuk perempuan

Yang tidak lagi dibahas di sini:

- suffix gelar pendidikan
- normalisasi nama lengkap
- OCR atau pencocokan nama dokumen

## Hasil Diskusi

| # | Topik | Hasil | Status |
| --- | --- | --- | --- |
| 1 | Pangkat sebagai domain terpisah | Sudah jelas | Clear |
| 2 | Riwayat pangkat | Sudah diputuskan menjadi bagian keputusan pangkat | Clear |
| 3 | Format nama dengan pangkat | Rumah terpisah di keputusan penghubung | Clear |
| 4 | Aturan validasi khusus | Klaim langsung hanya untuk penetapan awal atau klaim awal; perpindahan pangkat berikutnya wajib melalui pengajuan berbasis SK | Clear |
| 5 | Pangkat sudah dipakai lintas modul | Tetap dipakai lintas modul dengan sumber kebenaran canonical dan riwayat pangkat sebagai jejak perubahan | Clear |
| 6 | Prefix `(W)` sebagai varian display perempuan | `(W)` hanya varian display untuk perempuan; laki-laki tidak memakainya | Clear |
| 7 | Daftar pangkat draft awal user | 18 tingkat tercatat | Clear |
| 8 | Pangkat default yang bisa diklaim | Prada KC, Serda KC, Letda KC | Clear |
| 9 | Pangkat selain default klaim | Hanya lewat ijazah atau kenaikan dengan SK | Clear |

## Catatan untuk AI Agent

- jangan lahirkan seed, SQL, atau fungsi helper sebelum aturan domain pangkat benar-benar final
- jika ada aturan sensitif, minta penegasan user saat pembahasan substantif dimulai
- jika format display sudah dipakai di UI, konsolidasikan satu bentuk baku sebelum finalisasi
- jangan masukkan suffix gelar pendidikan ke dokumen ini; arahkan ke diskusi pendidikan
- jangan masukkan OCR atau parsing nama dokumen ke dokumen ini; arahkan ke diskusi OCR
- jika keputusan baru nanti dibentuk, keputusan itu harus memuat tiga pangkat default klaim dan mekanisme kenaikan/penyesuaian berbasis SK

## Pertanyaan Terbuka

- tidak ada pertanyaan utama yang tersisa di scope pangkat
- format SK untuk pengajuan pangkat berikutnya akan dibahas pada keputusan atau workplan terpisah

## Rencana Tindak Lanjut

- [x] menunggu arahan user untuk struktur aturan pangkat
- [x] menyelaraskan hasilnya dengan diskusi dokumen dan pendidikan bila diperlukan
- [x] menurunkan diskusi ini ke keputusan baru tentang manajemen kepangkatan
- [x] memisahkan mekanisme SK ke klausul tersendiri pada keputusan berikutnya

## Changelog Dokumen

| Tanggal | Perubahan |
| --- | --- |
| 2026-07-03 | Dokumen dibuat untuk menampung pembahasan pangkat dan format nama anggota |
