<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PersonelRepositoryInterface;
use App\Repositories\Eloquent\PersonelRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PersonelRepositoryInterface::class, PersonelRepository::class);
    }
}