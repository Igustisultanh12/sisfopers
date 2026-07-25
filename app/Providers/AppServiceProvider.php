<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa skema HTTPS secara absolut untuk mengatasi Mixed Content di Nginx/aaPanel SSL
        URL::forceScheme('https');

        // Blade directive @tanggalId — format tanggal Indonesia (dipakai di semua blade PDF)
        // Contoh: @tanggalId => "20 Juli 2026"
        Blade::directive('tanggalId', function () {
            return "<?php
                \$_bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                echo now()->format('d') . ' ' . \$_bulan[now()->month] . ' ' . now()->format('Y');
            ?>";
        });
    }
}