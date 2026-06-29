<?php

namespace App\Models;

use App\Traits\HasUuid;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'certificate_logo', 'bio', 'phone', 'country_code',
        'timezone', 'notification_preferences', 'preferred_locale',
    ];

    protected $attributes = [
        'notification_preferences' => '{"notify_quiz_results":true,"notify_weekly_digest":false}',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'email_otp_expires_at'     => 'datetime',
            'password'                 => 'hashed',
            'is_active'                => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'    => $this->role === 'super_admin',
            'lecturer' => in_array($this->role, ['lecturer', 'super_admin'], true),
            default    => false,
        };
    }

    public function canImpersonate(): bool
    {
        return $this->role === 'super_admin';
    }

    public function canBeImpersonated(): bool
    {
        return $this->role !== 'super_admin';
    }

    public function assignEmailOtp(string $otp): void
    {
        $this->forceFill([
            'email_otp'            => Hash::make($otp),
            'email_otp_expires_at' => now()->addMinutes(10),
        ])->save();
    }

    public function verifyEmailOtp(string $otp): bool
    {
        if (! $this->email_otp || ! $this->email_otp_expires_at || now()->isAfter($this->email_otp_expires_at)) {
            return false;
        }

        return Hash::check($otp, $this->email_otp);
    }

    public function markEmailVerified(): void
    {
        $this->forceFill([
            'email_verified_at'    => now(),
            'email_otp'            => null,
            'email_otp_expires_at' => null,
        ])->save();
    }

    public function quizzes() { return $this->hasMany(Quiz::class, 'lecturer_id'); }
    public function studentBatch() { return $this->belongsTo(StudentBatch::class, 'student_batch_id'); }
    public function quizAssignments() { return $this->hasMany(QuizAssignment::class, 'user_id'); }
    public function favourites() { return $this->hasMany(QuizFavourite::class); }
    public function enrollments() { return $this->hasMany(QuizEnrollment::class); }
    public function attempts() { return $this->hasMany(Attempt::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
    public function aiGenerationLogs() { return $this->hasMany(AiGenerationLog::class); }
    public function aiCreditTransactions() { return $this->hasMany(AiCreditTransaction::class); }
}
