<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    /**
     * Mengamankan value setting sensitif (misal: wa_api_key) menggunakan enkripsi bawaan laravel secara dinamis.
     */
    public function getValueAttribute($value)
    {
        if ($this->key === 'wa_api_key' && !empty($value)) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($this->key === 'wa_api_key' && !empty($value)) {
            $this->attributes['value'] = encrypt($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}