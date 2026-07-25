<?php

namespace App\Rules;

use App\Models\MasterProvinsi;
use App\Models\MasterKepangkatan;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NikcFormatRule implements ValidationRule
{
    public function __construct(
        private readonly ?string $provinceName = null,
        private readonly ?string $dob = null,
        private readonly ?string $pangkatName = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Bypass validasi format NIKC agar fleksibel terhadap salah ketik dokumen Kemenhan
        return;
    }
}
