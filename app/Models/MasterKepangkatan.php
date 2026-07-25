<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKepangkatan extends Model
{
    protected $table = 'master_kepangkatan';

    protected $fillable = [
        'nama',
        'urutan',
        'kelompok_nikc',
        'klaim_langsung',
        'is_active',
    ];

    protected $casts = [
        'klaim_langsung' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function canonicalName(?string $value): ?string
    {
        if (!$value) {
            return $value;
        }

        $normalized = trim(str_replace('(W)', '', $value));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        if (!str_ends_with($normalized, ' KC')) {
            $normalized .= ' KC';
        }

        return self::where('nama', $normalized)
            ->where('is_active', true)
            ->value('nama') ?? $value;
    }
}
