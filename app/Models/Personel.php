<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MasterKepangkatan;

class Personel extends Model
{
    use SoftDeletes;

    public static function formatLongRank(?string $pangkat): string
    {
        if (!$pangkat) return '-';

        $p = strtoupper(trim($pangkat));
        $isWanita = false;

        if (str_contains($p, '(W)') || str_contains($p, ' W')) {
            $isWanita = true;
            $p = trim(str_replace(['(W)', ' W'], '', $p));
        }

        $map = [
            'PRADA'   => 'Prajurit Dua',
            'PRATU'   => 'Prajurit Satu',
            'PRAKA'   => 'Prajurit Kepala',
            'KOPDA'   => 'Kopral Dua',
            'KOPTU'   => 'Kopral Satu',
            'KOPKA'   => 'Kopral Kepala',
            'SERDA'   => 'Sersan Dua',
            'SERTU'   => 'Sersan Satu',
            'SERKA'   => 'Sersan Kepala',
            'SERMA'   => 'Sersan Mayor',
            'PELDA'   => 'Pembantu Letnan Dua',
            'PELTU'   => 'Pembantu Letnan Satu',
            'LETDA'   => 'Letnan Dua',
            'LETTU'   => 'Letnan Satu',
            'KAPTEN'  => 'Kapten',
            'MAYOR'   => 'Mayor',
            'LETKOL'  => 'Letnan Kolonel',
            'KOLONEL' => 'Kolonel',
        ];

        $parts = explode(' ', $p);
        $rankKey = $parts[0];
        $korps = isset($parts[1]) ? ' ' . ucfirst(strtolower($parts[1])) : '';

        if (isset($map[$rankKey])) {
            $longRank = $map[$rankKey] . $korps;
        } else {
            $longRank = ucfirst(strtolower($p));
        }

        if (!str_contains(strtoupper($longRank), 'KC')) {
            if ($isWanita) {
                return "{$longRank} KC/W";
            }
            return "{$longRank} KC";
        }

        return $longRank;
    }

    public static function formatShortRank(?string $pangkat): string
    {
        if (!$pangkat) return '-';

        $p = strtoupper(trim($pangkat));
        $isWanita = false;

        if (str_contains($p, '(W)') || str_contains($p, ' W')) {
            $isWanita = true;
            $p = trim(str_replace(['(W)', ' W'], '', $p));
        }

        if ($isWanita) {
            return "{$p} (KC/W)";
        }
        return "{$p} (KC)";
    }

    protected $table = 'personels';

    protected $fillable = [
        'uuid', 'user_id', 'full_name', 'dob', 'pangkat', 'nik', 'nikc', 'phone_number',
        'subdistrict', 'city', 'province', 'zip_code', 'face_verified', 'status_keaktifan',
        'status_verification', 'catatan_pembinaan', 'sumber_rekrutmen', 'angkatan', 'matra', 'gender'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function riwayatPendidikan(): HasMany
    {
        return $this->hasMany(RiwayatPendidikan::class, 'personel_id');
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class, 'personel_id');
    }

    public function pengkinianData(): HasMany
    {
        return $this->hasMany(PengkinianData::class, 'personel_id');
    }

    public function jobHistories(): HasMany
    {
        return $this->hasMany(JobHistory::class, 'personel_id');
    }

    public function faceVerifications(): HasMany
    {
        return $this->hasMany(FaceVerification::class, 'personel_id');
    }

    public function sinyalmen(): HasOne
    {
        return $this->hasOne(Sinyalmen::class, 'personel_id');
    }
}
