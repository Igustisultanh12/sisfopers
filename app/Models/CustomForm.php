<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomForm extends Model
{
    use SoftDeletes;

    protected $table = 'custom_forms';

    protected $fillable = [
        'uuid',
        'title',
        'category',
        'description',
        'deadline',
        'is_active',
        'target_rank_category',
        'target_matra',
        'target_angkatan',
        'requirements',
        'questions',
        'created_by_user_id',
        'created_by_pju_id',
        'creator_role',
    ];

    protected $casts = [
        'requirements' => 'array',
        'questions' => 'array',
        'deadline' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function responses(): HasMany
    {
        return $this->hasMany(CustomFormResponse::class, 'form_id');
    }

    public function creatorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function creatorPju(): BelongsTo
    {
        return $this->belongsTo(Pju::class, 'created_by_pju_id');
    }

    /**
     * Memeriksa apakah seorang personel berhak (eligible) mengakses dan mengisi form ini
     */
    public function isPersonelEligible(Personel $personel): bool
    {
        // 1. Cek Matra
        if ($this->target_matra !== 'ALL' && !empty($this->target_matra)) {
            if (strtoupper($personel->matra) !== strtoupper($this->target_matra)) {
                return false;
            }
        }

        // 2. Cek Angkatan
        if ($this->target_angkatan !== 'ALL' && !empty($this->target_angkatan)) {
            if ((string)$personel->angkatan !== (string)$this->target_angkatan) {
                return false;
            }
        }

        // 3. Cek Kategori Kepangkatan (Perwira, Bintara, Tamtama)
        if ($this->target_rank_category !== 'ALL' && !empty($this->target_rank_category)) {
            $rankName = MasterKepangkatan::canonicalName($personel->pangkat);
            $rankMaster = MasterKepangkatan::where('nama', $rankName)->first();

            $kelompok = $rankMaster ? (string)$rankMaster->kelompok_nikc : null;

            // '1' = Perwira, '2' = Bintara, '3' = Tamtama
            $expectedKelompok = match (strtoupper($this->target_rank_category)) {
                'PERWIRA' => '1',
                'BINTARA' => '2',
                'TAMTAMA' => '3',
                default => null,
            };

            if ($expectedKelompok && $kelompok !== $expectedKelompok) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cek apakah personel sudah pernah mengirimkan respon pada form ini
     */
    public function hasPersonelSubmitted(Personel $personel): bool
    {
        return $this->responses()->where('personel_id', $personel->id)->exists();
    }
}
