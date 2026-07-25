<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->boolean('is_asn')->default(false)->after('sumber_rekrutmen');
            $table->string('asn_nip')->nullable()->after('is_asn');
            $table->enum('asn_jenis', ['CPNS', 'PNS', 'P3K'])->nullable()->after('asn_nip');
            $table->date('asn_tmt')->nullable()->after('asn_jenis');
            $table->string('asn_sk')->nullable()->after('asn_tmt'); // Path berkas SK ASN di storage privat
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->dropColumn(['is_asn', 'asn_nip', 'asn_jenis', 'asn_tmt', 'asn_sk']);
        });
    }
};
