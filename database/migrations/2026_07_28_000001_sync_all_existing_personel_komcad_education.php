<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Personel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Loop seluruh data personel yang sudah ada di database dan terbitkan pendidikan militer utamanya
        Personel::chunk(50, function ($personels) {
            foreach ($personels as $personel) {
                $personel->ensureKomcadEducationExists();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
