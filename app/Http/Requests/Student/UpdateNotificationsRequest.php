<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'notify_quiz_results'  => ['boolean'],
            'notify_weekly_digest' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notify_quiz_results'  => $this->boolean('notify_quiz_results'),
            'notify_weekly_digest' => $this->boolean('notify_weekly_digest'),
        ]);
    }
}
