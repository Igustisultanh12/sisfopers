<?php

namespace App\Repositories\Eloquent;

use App\Models\Personel;
use App\Repositories\PersonelRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PersonelRepository extends BaseRepository implements PersonelRepositoryInterface
{
    public function __construct(Personel $model)
    {
        parent::__construct($model);
    }

    public function findByUuid(string $uuid): ?Personel
    {
        return $this->model->where('uuid', $uuid)->with(['user', 'registration', 'sinyalmen'])->first();
    }

    public function getPendingVerification(array $filters)
    {
        $query = $this->model->whereHas('registration', function ($q) {
            $q->where('status_verification', 'PENDING');
        })->with(['user', 'registration']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nik', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }
}