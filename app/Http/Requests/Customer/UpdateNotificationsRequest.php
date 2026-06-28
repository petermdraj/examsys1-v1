<?php

namespace App\Http\Requests\Customer;

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
            'notify_quiz_results'   => ['boolean'],
            'notify_purchases'      => ['boolean'],
            'notify_weekly_digest'  => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Checkboxes not submitted = false
        $this->merge([
            'notify_quiz_results'  => $this->boolean('notify_quiz_results'),
            'notify_purchases'     => $this->boolean('notify_purchases'),
            'notify_weekly_digest' => $this->boolean('notify_weekly_digest'),
        ]);
    }
}
