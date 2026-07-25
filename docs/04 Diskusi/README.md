# 04 Diskusi

Folder ini berisi analisis, evaluasi, dan pembahasan yang belum final.

## Kapan Dipakai

- diskusi awal
- analisis alternatif
- evaluasi arsitektur
- pembahasan yang belum menghasilkan keputusan final
- catatan deferensi yang sengaja belum dibawa ke keputusan atau rencana kerja
- bahan bantu seperti mockup, contoh, atau ilustrasi yang masih diperdebatkan

## Aturan

- dokumen di folder ini belum boleh dibaca sebagai keputusan final
- nama file diskusi aktif wajib mengikuti format `YYYY.MM.DD Diskusi tentang Judul Dokumen.md`
- field `Identitas dokumen` wajib mengikuti nama file aktif tanpa ekstensi
- nama file diskusi tidak boleh memakai kata `keputusan` atau bentuk lain yang membuatnya tampak seperti source of truth final
- saat membuka diskusi lama, melanjutkan diskusi, atau membuat file diskusi baru, wajib analisis dulu file kode dan dokumen aktif yang beririsan agar konteks, konflik, dan referensi terkait sudah tersedia di dalam diskusi
- hasil analisis awal file atau dokumen beririsan wajib diringkas di bagian `Temuan Awal`, `Poin Diskusi`, atau `Catatan untuk AI Agent`
- sejak awal menulis file diskusi, jangan tulis path eksternal, nama folder personal, atau asal referensi luar repo; yang boleh masuk ke repo hanya laporan temuan, hasil verifikasi, pola, dan substansi yang sudah diserap
- selama dokumen ini masih berstatus `Diskusi`, `Menunggu Keputusan`, `Deferred`, atau belum final secara lifecycle, agent dilarang membuat dokumen turunan domain baru di rumah aktif lain, termasuk keputusan, rencana kerja, referensi teknis, manual, referensi operasional, spesifikasi, blueprint, SOP, atau template implementasi
- jika user meminta manual, referensi, referensi teknis, spesifikasi, blueprint, SOP, atau template implementasi saat diskusi belum final, agent wajib menahan pembuatan dokumen tersebut dan menuliskannya dulu sebagai kebutuhan atau acceptance criteria di diskusi aktif
- jika diskusi sudah mengerucut dan tinggal dipilih, tandai sebagai `Menunggu Keputusan`
- jika semua poin sudah disepakati, tandai sebagai `Siap Difinalkan`; yang difinalkan adalah diskusinya, lalu keputusan baru lahir sebagai hasilnya
- jika diskusi menghasilkan keputusan final, dokumen diskusi dipindahkan ke `00 Arsip` dengan prefix status selesai, waktu selesai `WIB`, dan rujukan ke dokumen keputusan yang dihasilkan
- jika pembahasan berkembang menjadi pekerjaan implementasi, turunkan ke `03 Rencana Kerja` setelah keputusan final ditetapkan
- jika diskusi sengaja ditunda, tandai sebagai `Deferred` dan catat alasan penundaannya
- jika diskusi ditutup tanpa keputusan, tandai sebagai `Ditutup Tanpa Keputusan` dan tulis alasan penutupannya
- jika sesudah keputusan lahir ternyata sinkronisasi dokumen hanya ringan, perubahan boleh langsung dikerjakan; jika banyak atau kompleks, wajib lahir workplan formal

## Status Dokumen

### Diskusi

- dipakai untuk ide, opsi, dan analisis yang masih terbuka

### Menunggu Keputusan

- diskusi sudah mengerucut
- tinggal menunggu pemangku keputusan memilih opsi

### Siap Difinalkan

- semua poin relevan sudah disepakati
- siap diarsipkan dan, bila relevan, melahirkan keputusan baru di `02 Keputusan`

### Deferred

- dipakai untuk topik yang sengaja ditunda agar tidak masuk scope aktif

### Ditutup Tanpa Keputusan

- diskusi tidak menghasilkan keputusan final
- dokumen tetap berada di `04 Diskusi` sebagai jejak pembahasan yang ditutup tanpa keputusan

## Template Standar Diskusi

```md
# Diskusi
## tentang JUDUL DOKUMEN

Status: `Diskusi` | `Menunggu Keputusan` | `Siap Difinalkan` | `Deferred` | `Ditutup Tanpa Keputusan`
Tanggal dibuka: DD Bulan YYYY
Identitas dokumen: YYYY.MM.DD Diskusi tentang Judul Dokumen
Jenis dokumen: Diskusi
Domain: kctrimatra
Topik: ...
Keputusan terkait: -
Rencana kerja terkait: -
Mengubah: -
Digantikan oleh: -

## Pemicu

## Temuan Awal

## Opsi yang Dipertimbangkan

## Poin Diskusi

## Hasil Diskusi

## Checklist Pra-Finalisasi

## Catatan untuk AI Agent

## Pertanyaan Terbuka

## Rencana Tindak Lanjut

## Changelog Dokumen
```
