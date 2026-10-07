<template>
  <!-- Komponen Vektor Lambang Komponen Cadangan (Tri-Matra Garuda Jangkar Sayap) -->
  <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full object-contain">
    <!-- Lingkaran Luar / Perisai Padi & Kapas Emas -->
    <circle cx="100" cy="100" r="92" fill="#FFFFFF" fill-opacity="0.1" stroke="#D97706" stroke-width="3" stroke-dasharray="2 1"/>
    
    <!-- Lingkaran Dalam Merah Putih -->
    <circle cx="100" cy="100" r="76" fill="#F8FAFC" stroke="#B45309" stroke-width="2"/>
    <path d="M24 100 A76 76 0 0 1 176 100 Z" fill="#DC2626"/>
    <path d="M24 100 A76 76 0 0 0 176 100 Z" fill="#FFFFFF"/>

    <!-- Untaian Padi & Kapas Melingkar Emas -->
    <path d="M36 125 C30 85 45 45 80 30 C75 42 75 60 85 70 C70 75 55 95 60 115 Z" fill="#F59E0B" opacity="0.9"/>
    <path d="M164 125 C170 85 155 45 120 30 C125 42 125 60 115 70 C130 75 145 95 140 115 Z" fill="#F59E0B" opacity="0.9"/>

    <!-- Bintang Emas Puncak Tri-Matra -->
    <polygon points="100,20 105,34 120,34 108,43 112,57 100,48 88,57 92,43 80,34 95,34" fill="#FBBF24" stroke="#B45309" stroke-width="1"/>

    <!-- Sayap Tri-Matra AU (Kiri & Kanan) Emas -->
    <path d="M100 85 C70 65 30 78 28 92 C50 96 75 92 100 102 C125 92 150 96 172 92 C170 78 130 65 100 85 Z" fill="#FBBF24" stroke="#B45309" stroke-width="1.5"/>
    <path d="M100 95 C75 80 45 88 40 102 C60 104 80 100 100 112 C120 100 140 104 160 102 C155 88 125 80 100 95 Z" fill="#F59E0B" opacity="0.85"/>

    <!-- Jangkar AL Matra Laut Emas & Perak -->
    <circle cx="100" cy="72" r="7" fill="none" stroke="#F59E0B" stroke-width="3"/>
    <rect x="96" y="78" width="8" height="62" rx="3" fill="#F59E0B" stroke="#B45309" stroke-width="1"/>
    <!-- Batang Horizontal Jangkar -->
    <rect x="82" y="88" width="36" height="6" rx="2" fill="#F59E0B" stroke="#B45309" stroke-width="1"/>
    <!-- Busur Bawah Jangkar -->
    <path d="M70 120 C70 146 130 146 130 120 C126 120 124 128 118 132 C108 136 92 136 82 132 C76 128 74 120 70 120 Z" fill="#F59E0B" stroke="#B45309" stroke-width="1.5"/>

    <!-- Pedang / Lambang AD di Tengah -->
    <polygon points="100,55 103,130 100,135 97,130" fill="#DC2626"/>

    <!-- Pita Merah "KOMPONEN CADANGAN" di Bagian Bawah -->
    <path d="M40 156 Q100 178 160 156 L154 172 Q100 190 46 172 Z" fill="#DC2626" stroke="#991B1B" stroke-width="1.5"/>
    <path d="M40 156 L26 166 L42 174 L38 164 Z" fill="#B91C1C"/>
    <path d="M160 156 L174 166 L158 174 L162 164 Z" fill="#B91C1C"/>
    
    <!-- Teks di Pita Merah -->
    <text x="100" y="169" text-anchor="middle" font-size="7.5" font-family="'Arial Black', sans-serif" font-weight="900" fill="#FFFFFF" letter-spacing="0.8">KOMPONEN CADANGAN</text>
  </svg>
</template>
