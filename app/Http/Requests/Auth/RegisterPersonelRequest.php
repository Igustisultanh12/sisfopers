<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NikcFormatRule;
use Illuminate\Validation\Rule;
use App\Models\MasterKepangkatan;

class RegisterPersonelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:150',
            'pob' => 'required|string|max:50',
            'dob' => 'required|date',
            'gender' => 'required|in:L,P',
            'matra' => 'required|in:AD,AL,AU',
            'angkatan' => 'required|digits:4',
            'nik' => 'required|digits:16|unique:personels,nik',
            'nikc' => ['required', 'digits:17', 'numeric', new NikcFormatRule($this->input('province'), $this->input('dob'), $this->input('pangkat')), 'unique:personels,nikc'],
            'address' => 'required|string',
            'province' => ['required', 'string', 'max:50', Rule::exists('master_provinsi', 'nama')->where('is_active', true)],
            'city' => 'nullable|string|max:50',
            'district' => 'nullable|string|max:50',
            'village' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:10',
            'email' => 'required|email|max:100|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'photo_profile' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'ktp_document' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
            'agreement' => 'required|accepted',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('pangkat')) {
            $this->merge([
                'pangkat' => MasterKepangkatan::canonicalName($this->input('pangkat')),
            ]);
        }
    }
}
