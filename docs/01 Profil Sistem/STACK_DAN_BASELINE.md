# Stack dan Baseline

Status dokumen: `Aktif`
Domain: `kctrimatra`
Topik: baseline teknis proyek baru Laravel 13

## Baseline Awal

- framework target: Laravel 13
- bahasa: PHP 8.3+
- web server lokal: Laragon
- database target: MySQL atau MariaDB
- package manager PHP: Composer
- frontend stack: belum diputuskan
- autentikasi: belum diputuskan
- queue/cache/search/storage pihak ketiga: belum diputuskan

## Guardrail Baseline

- jangan menulis seolah stack yang belum dipilih sudah final
- jika ada pilihan teknis yang mengubah arsitektur atau operasional, lahirkan diskusi dulu lalu keputusan formal
- jika user meminta bootstrap cepat tanpa diskusi panjang, minimal catat keputusan teknis yang diambil setelah implementasi awal selesai

## Daftar Keputusan yang Masih Kosong

- struktur modul/domain awal
- pilihan frontend resmi
- strategi auth dan role
- strategi queue, cache, dan scheduler
- strategi upload file dan penyimpanan
- strategi migrasi database
- strategi deploy

## Catatan

Dokumen ini adalah baseline awal, bukan bukti bahwa seluruh stack di atas sudah berjalan di repo.
