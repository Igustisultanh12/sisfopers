<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkepData extends Model
{
    use HasFactory;

    protected $table = 'skep_data';

    protected $fillable = [
        'nama_lengkap',
        'dob',
        'pangkat',
        'nikc',
        'angkatan',
        'matra',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public static function reconstructNikc(?string $nikc): ?string
    {
        if ($nikc === null) {
            return null;
        }

        $nikc = trim($nikc);
        if (strlen($nikc) === 16) {
            return substr($nikc, 0, 9) . '0' . substr($nikc, 9);
        }

        return $nikc;
    }
}
