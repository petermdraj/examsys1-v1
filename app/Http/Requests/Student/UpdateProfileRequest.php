<?php

namespace App\Http\Requests\Student;

use App\Enums\CountryCodes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'country_code' => ['nullable', 'string', Rule::in(array_keys(CountryCodes::options()))],
            'timezone'     => ['nullable', 'string', 'max:60'],
        ];
    }
}
