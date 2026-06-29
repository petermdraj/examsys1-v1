<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }
}
