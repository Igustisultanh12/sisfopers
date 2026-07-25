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

        if (strpos($p, '(W)') !== false || strpos($p, ' W') !== false) {
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

        $longRank = $map[$p] ?? $p;

        if ($isWanita) {
            return "{$longRank} (KC/W)";
        }
        return "{$longRank} (KC)";
    }

    public static function formatShortRank(?string $pangkat): string
    {
        if (!$pangkat) return '-';

        $p = trim($pangkat);
        $isWanita = false;

        if (stripos($p, '(W)') !== false || stripos($p, ' W') !== false) {
            $isWanita = true;
            $p = trim(str_ireplace(['(W)', ' W'], '', $p));
        }

        // Jadikan CamelCase yang rapi (misal LETDA -> Letda, SERDA -> Serda)
        $p = ucwords(strtolower($p));

        if ($isWanita) {
            return "{$p} (KC/W)";
        }
        return "{$p} (KC)";
    }

    protected $fillable = [
        'uuid', 'user_id', 'nik', 'nikc', 'pangkat', 'full_name', 'pob', 'dob', 
        'gender', 'matra', 'angkatan', 'phone_number', 'address', 
        'province', 'city', 'district', 'village', 'postal_code', 
        'photo_profile', 'ktp_document', 'status_profile', 'face_verified',
        'sumber_rekrutmen', 'skep_file',
        'is_asn', 'asn_nip', 'asn_jenis', 'asn_tmt', 'asn_sk',
        'manual_otp', 'manual_otp_expired_at', 'manual_otp_printed_at'
    ];

    protected $casts = [
        'dob'                    => 'date',
        'face_verified'          => 'boolean',
        'is_asn'                 => 'boolean',
        'asn_tmt'                => 'date',
        'manual_otp_expired_at'  => 'datetime',
        'manual_otp_printed_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class);
    }

    public function faceVerification(): HasOne
    {
        return $this->hasOne(FaceVerification::class);
    }

    public function sinyalmen(): HasOne
    {
        return $this->hasOne(Sinyalmen::class, 'personel_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(BroadcastTarget::class);
    }

    /**
     * Relasi Riwayat Balasan Kegiatan Komcad (Diselaraskan dengan MasterPersonelController)
     */
    public function broadcastResponses(): HasMany
    {
        return $this->hasMany(BroadcastResponse::class, 'personel_id');
    }

    /**
     * Kebalikan/Alias Relasi Lama untuk Menjaga Dependensi Modul Lain
     */
    public function responses(): HasMany
    {
        return $this->hasMany(BroadcastResponse::class, 'personel_id');
    }

    public function riwayatPendidikan(): HasMany
    {
        return $this->hasMany(RiwayatPendidikan::class, 'personel_id');
    }

    public function jobHistories(): HasMany
    {
        return $this->hasMany(JobHistory::class, 'personel_id');
    }

    public function currentJob(): HasOne
    {
        return $this->hasOne(JobHistory::class, 'personel_id')->where('is_current', true);
    }

    /**
     * Memastikan data Pendidikan Militer Komponen Cadangan terisi otomatis dari profil database.
     */
    public function ensureKomcadEducationExists(): void
    {
        $hasKomcadMiliter = $this->riwayatPendidikan()
            ->where('jenis', 'MILITER')
            ->where('program_studi', 'Pendidikan Militer Komponen Cadangan')
            ->first();

        $pangkat = MasterKepangkatan::canonicalName($this->pangkat);
        $masterPangkat = $pangkat
            ? MasterKepangkatan::where('nama', $pangkat)->where('is_active', true)->first()
            : null;
        $jenjangMiliter = 'Pendidikan Militer Komponen Cadangan';

        if ($masterPangkat?->kelompok_nikc === '1') {
            $jenjangMiliter = 'Perwira Komcad';
        } elseif ($masterPangkat?->kelompok_nikc === '2') {
            $jenjangMiliter = 'Bintara Komcad';
        } elseif ($masterPangkat?->kelompok_nikc === '3') {
            $jenjangMiliter = 'Tamtama Komcad';
        }

        if (!$hasKomcadMiliter) {
            $matraMap = [
                'AD' => 'Matra Darat',
                'AL' => 'Matra Laut',
                'AU' => 'Matra Udara'
            ];
            $matraStr = $matraMap[$this->matra] ?? 'Matra Laut';
            
            $this->riwayatPendidikan()->create([
                'jenis' => 'MILITER',
                'jenjang' => $jenjangMiliter,
                'program_studi' => 'Pendidikan Militer Komponen Cadangan',
                'nama_institusi' => 'Kementerian Pertahanan RI - Komponen Cadangan ' . $matraStr,
                'tahun_lulus' => $this->angkatan ?? date('Y'),
                'nomor_ijazah' => $this->nikc ?? '-',
                'verified_at' => now(),
                'verified_by' => 1, // Auto-verified by System Admin
                'ocr_status' => 'DONE'
            ]);
        } else {
            // Selaraskan/Perbarui jika jenjang militer berubah (misal naik pangkat Letda/Serda/Prada)
            if ($hasKomcadMiliter->jenjang !== $jenjangMiliter) {
                $hasKomcadMiliter->update([
                    'jenjang' => $jenjangMiliter
                ]);
            }
        }
    }

    /**
     * Menghapus secara permanen personel, seluruh berkas fisik di storage disk, tabel relasi,
     * serta akun User induk (force delete) agar NIK, NIKC, email, dan username bebas terhapus.
     */
    public static function purgePersonelCompletely(Personel $personel): void
    {
        $userId = $personel->user_id;

        // 1. Hapus berkas fisik utama (pasfoto & KTP)
        if ($personel->photo_profile) {
            $photoPath = str_replace('/storage/', '', $personel->photo_profile);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($photoPath);
        }
        if ($personel->ktp_document) {
            $ktpPath = str_replace('/storage/', '', $personel->ktp_document);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($ktpPath);
        }

        // 2. Hapus berkas fisik verifikasi wajah (face_verifications)
        $faceVerifications = \App\Models\FaceVerification::where('personel_id', $personel->id)->get();
        foreach ($faceVerifications as $fv) {
            if ($fv->photo_path) {
                $path = str_replace('/storage/', '', $fv->photo_path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }

        // 3. Hapus berkas fisik riwayat pendidikan (ijazah & sertifikat)
        $riwayatPendidikan = \App\Models\RiwayatPendidikan::where('personel_id', $personel->id)->get();
        foreach ($riwayatPendidikan as $rp) {
            if ($rp->file_ijazah_path) {
                try {
                    $path = \Illuminate\Support\Facades\Crypt::decryptString($rp->file_ijazah_path);
                } catch (\Exception $e) {
                    $path = $rp->file_ijazah_path;
                }
                $path = str_replace('/storage/', '', $path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
            if ($rp->file_sertifikat_path) {
                try {
                    $path = \Illuminate\Support\Facades\Crypt::decryptString($rp->file_sertifikat_path);
                } catch (\Exception $e) {
                    $path = $rp->file_sertifikat_path;
                }
                $path = str_replace('/storage/', '', $path);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
        }

        // 4. Bersihkan seluruh data di tabel relasi anak
        \Illuminate\Support\Facades\DB::table('registrations')->where('personel_id', $personel->id)->delete();
        \Illuminate\Support\Facades\DB::table('face_verifications')->where('personel_id', $personel->id)->delete();
        \Illuminate\Support\Facades\DB::table('sinyalmens')->where('personel_id', $personel->id)->delete();
        \Illuminate\Support\Facades\DB::table('broadcast_responses')->where('personel_id', $personel->id)->delete();
        \Illuminate\Support\Facades\DB::table('riwayat_pendidikan')->where('personel_id', $personel->id)->delete();
        \Illuminate\Support\Facades\DB::table('job_histories')->where('personel_id', $personel->id)->delete();
        \Illuminate\Support\Facades\DB::table('job_otp_verifications')->where('personel_id', $personel->id)->delete();

        // 5. Force Delete data Personel secara permanen dari database
        $personel->forceDelete();

        // 6. Force Delete data User secara permanen (MUTLAK: menghapus email, username & ID secara permanen dari tabel users)
        if ($userId) {
            \Illuminate\Support\Facades\DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->where('tokenable_id', $userId)
                ->delete();

            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->where('notifiable_id', $userId)
                ->delete();

            \App\Models\User::withTrashed()->where('id', $userId)->forceDelete();
        }
    }
}