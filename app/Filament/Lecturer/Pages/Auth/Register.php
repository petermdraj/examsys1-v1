<?php

namespace App\Filament\Lecturer\Pages\Auth;

use App\Models\User;
use App\Settings\PlatformSettings;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    public function register(): ?RegistrationResponse
    {
        if (! app(PlatformSettings::class)->lecturer_registration_open) {
            $this->addError('data.email', 'Lecturer registration is currently closed.');

            return null;
        }

        return parent::register();
    }

    protected function handleRegistration(array $data): Model
    {
        $settings = app(PlatformSettings::class);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        $user->forceFill([
            'role'                      => 'lecturer',
            'is_active'                 => true,
            'ai_credits_free_remaining' => (int) config('examsys.ai_free_generations_default', 10),
            'email_verified_at'         => $settings->require_email_verification ? null : now(),
        ])->save();

        $user->assignRole('lecturer');

        return $user;
    }
}
