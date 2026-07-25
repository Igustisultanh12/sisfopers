<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cari personel yang memiliki lebih dari 1 data pendidikan MILITER/Komcad
        $personelIds = DB::table('riwayat_pendidikans')
            ->where(function ($q) {
                $q->where('jenis', 'MILITER')
                  ->orWhere('jenis', 'Militer')
                  ->orWhere('jenjang', 'LIKE', '%Komcad%');
            })
            ->select('personel_id')
            ->groupBy('personel_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('personel_id');

        foreach ($personelIds as $personelId) {
            // Hapus record auto-generated DIKBATSIS_KOMCAD jika sudah ada record Komcad yang lebih spesifik
            $hasPrimary = DB::table('riwayat_pendidikans')
                ->where('personel_id', $personelId)
                ->where('jenjang', '!=', 'DIKBATSIS_KOMCAD')
                ->where(function ($q) {
                    $q->where('jenis', 'MILITER')
                      ->orWhere('jenis', 'Militer')
                      ->orWhere('jenjang', 'LIKE', '%Komcad%')
                      ->orWhere('program_studi', 'LIKE', '%Komcad%');
                })
                ->exists();

            if ($hasPrimary) {
                DB::table('riwayat_pendidikans')
                    ->where('personel_id', $personelId)
                    ->where('jenjang', 'DIKBATSIS_KOMCAD')
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
