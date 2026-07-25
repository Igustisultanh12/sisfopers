# 00 Arsip

Folder ini menampung dokumen yang sudah tidak aktif tetapi masih perlu disimpan sebagai jejak.

## Status Arsip yang Sah

| Status | Prefix | Kapan Dipakai |
| --- | --- | --- |
| `Dicabut` | `[DICABUT]` | keputusan atau dokumen normatif lama sudah digantikan |
| `Selesai` | `[SELESAI]` | workplan atau pekerjaan dokumentasi sudah tuntas |
| `Ditetapkan` | `[DITETAPKAN]` | diskusi sudah menghasilkan rumusan final yang ditetapkan |
| `Historis` | `[HISTORIS]` | snapshot, audit, log, atau dokumen lama disimpan untuk transparansi |

## Aturan

- prefix status hanya sah di folder arsip
- file aktif tidak boleh dipindah ke arsip tanpa alasan lifecycle yang jelas
- dokumen `DICABUT` wajib punya pengganti yang jelas bila memang ada penerus normatif
- diskusi yang diarsipkan sebagai `DITETAPKAN` wajib merujuk keputusan final yang lahir darinya
- diskusi yang ditutup setelah melahirkan keputusan final juga boleh diarsipkan sebagai `[SELESAI]` selama waktu penutupan `WIB` dan rujukan keputusan final dicatat jujur
- arsip tidak boleh kembali menjadi source of truth aktif

## Metadata Wajib untuk Arsip `DICABUT`

Jika yang diarsipkan adalah keputusan atau dokumen normatif yang dicabut, metadata minimumnya wajib membedakan:

- `Dicabut pada`
- `Dicabut oleh` sebagai keputusan yang mencabut
- `Alasan pencabutan`
- `Dokumen pengganti normatif` jika ada
- `Dokumen referensi teknis terkait` jika ada
- `Bagian normatif yang disalin ke keputusan baru` jika ada
- `Bagian implementatif yang dipindahkan ke referensi teknis` jika ada

Aturan tambahan:

- `Dicabut oleh` tidak boleh menunjuk dokumen di `11 Referensi Teknis`
- jika tidak ada pengganti normatif, tulis `-` secara eksplisit
- jika satu dokumen lama memuat norma dan implementasi sekaligus, dua bagian perpindahan substansi di atas wajib dipisahkan agar auditnya jujur
- semua timestamp berlabel `WIB` wajib memakai waktu eksekusi aktual `UTC+07:00` yang diverifikasi dari jam sistem

## Template Ringkas Arsip `DICABUT`

```md
> Status Arsip: `Dicabut`
> Dicabut pada: YYYY-MM-DD HH:MM:SS WIB
> Dicabut oleh: `docs/02 Keputusan/...`
> Alasan pencabutan: ...
> Dokumen pengganti normatif: `docs/02 Keputusan/...` / `-`
> Dokumen referensi teknis terkait: `docs/11 Referensi Teknis/...` / `-`
> Bagian normatif yang disalin ke keputusan baru: ...
> Bagian implementatif yang dipindahkan ke referensi teknis: ...
```
