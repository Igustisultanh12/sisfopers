<?php

namespace App\Repositories;

use App\Repositories\Eloquent\BaseRepositoryInterface;
use App\Models\Personel;

interface PersonelRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUuid(string $uuid): ?Personel;
    public function getPendingVerification(array $filters);
}