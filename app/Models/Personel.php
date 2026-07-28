<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
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
        'district', 'subdistrict', 'city', 'province', 'village', 'zip_code', 'postal_code', 'address', 'pob',
        'face_verified', 'status_keaktifan', 'status_profile', 'catatan_pembinaan',
        'sumber_rekrutmen', 'angkatan', 'matra', 'gender',
        'manual_otp', 'manual_otp_expired_at', 'manual_otp_printed_at',
        'reset_password_otp', 'reset_password_otp_expired_at', 'reset_password_otp_printed_at',
        'photo_profile', 'ktp_document', 'skep_file',
        'is_asn', 'asn_nip', 'asn_jenis', 'asn_tmt', 'asn_sk'
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

    public function broadcastResponses(): HasMany
    {
        return $this->hasMany(BroadcastResponse::class, 'personel_id');
    }

    public function ensureKomcadEducationExists(): void
    {
        // Pengecekan apakah personel ini sudah memiliki catatan Pendidikan Militer SKEP
        $existing = $this->riwayatPendidikan()
            ->where(function($q) {
                $q->where('jenis', 'MILITER')
                  ->orWhere('jenjang', 'LIKE', '%Komcad%')
                  ->orWhere('program_studi', 'LIKE', '%Komponen Cadangan%');
            })
            ->first();

        // Tentukan jenjang pendidikan militer dasar berdasarkan pangkat SKEP personel
        $pangkatLower = strtolower($this->pangkat ?? '');
        if (str_contains($pangkatLower, 'perwira') || str_contains($pangkatLower, 'letda') || str_contains($pangkatLower, 'lettu') || str_contains($pangkatLower, 'kapten') || str_contains($pangkatLower, 'mayor') || str_contains($pangkatLower, 'letkol') || str_contains($pangkatLower, 'kolonel')) {
            $jenjang = 'Perwira Komcad';
        } elseif (str_contains($pangkatLower, 'bintara') || str_contains($pangkatLower, 'serda') || str_contains($pangkatLower, 'sertu') || str_contains($pangkatLower, 'serka') || str_contains($pangkatLower, 'serma') || str_contains($pangkatLower, 'pelda') || str_contains($pangkatLower, 'peltu')) {
            $jenjang = 'Bintara Komcad';
        } elseif (str_contains($pangkatLower, 'tamtama') || str_contains($pangkatLower, 'prada') || str_contains($pangkatLower, 'pratu') || str_contains($pangkatLower, 'praka') || str_contains($pangkatLower, 'kopda') || str_contains($pangkatLower, 'koptu') || str_contains($pangkatLower, 'kopka')) {
            $jenjang = 'Tamtama Komcad';
        } else {
            $jenjang = 'Latsarmil Komcad';
        }

        $matraLongName = match (strtoupper($this->matra ?? 'AD')) {
            'AL' => 'Matra Laut',
            'AU' => 'Matra Udara',
            default => 'Matra Darat',
        };

        $programStudi = 'Pendidikan Militer Komponen Cadangan';
        $namaInstitusi = 'Kementerian Pertahanan RI - Komponen Cadangan ' . $matraLongName;
        $nomorIjazah = $this->nikc ?? $this->nik;
        $tahunLulus = $this->angkatan ?: date('Y');

        if ($existing) {
            // Update jika ada entri lama agar formatnya persis seragam
            $existing->update([
                'jenis' => 'MILITER',
                'jenjang' => $jenjang,
                'program_studi' => $programStudi,
                'nama_institusi' => $namaInstitusi,
                'nomor_ijazah' => $existing->nomor_ijazah ?: $nomorIjazah,
                'tahun_lulus' => $existing->tahun_lulus ?: $tahunLulus,
                'verified_at' => $existing->verified_at ?: now(),
            ]);
            return;
        }

        // Buat entri pendidikan militer utama secara otomatis
        $this->riwayatPendidikan()->create([
            'jenis' => 'MILITER',
            'jenjang' => $jenjang,
            'program_studi' => $programStudi,
            'nama_institusi' => $namaInstitusi,
            'nomor_ijazah' => $nomorIjazah,
            'tahun_lulus' => $tahunLulus,
            'verified_at' => now(),
        ]);
    }

    public static function purgePersonelCompletely(Personel $personel): void
    {
        // 1. Hapus berkas foto & dokumen dari storage (private & public)
        if ($personel->photo_profile) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($personel->photo_profile);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($personel->photo_profile);
        }
        if ($personel->ktp_document) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($personel->ktp_document);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($personel->ktp_document);
        }

        // 2. Hapus relasi
        $personel->riwayatPendidikan()->delete();
        $personel->jobHistories()->delete();
        $personel->pengkinianData()->delete();
        $personel->faceVerifications()->delete();
        $personel->broadcastResponses()->delete();
        if ($personel->sinyalmen) {
            $personel->sinyalmen()->delete();
        }
        if ($personel->registration) {
            $personel->registration()->delete();
        }

        // 3. Hapus Akun User & Record Personel
        $user = $personel->user;
        $personel->forceDelete();
        if ($user) {
            $user->forceDelete();
        }
    }
}
