<?php

namespace App\Models;

use App\Traits\HasUuid;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'certificate_logo', 'bio', 'phone', 'country_code',
        'timezone', 'role', 'is_active', 'ai_credits_used', 'ai_credits_free_remaining', 'wallet_balance',
        'payout_gateway', 'payout_details', 'notification_preferences',
        'email_otp', 'email_otp_expires_at', 'email_verified_at',
        'preferred_locale',
        // Mass-assignment security note: sensitive fields (role, wallet_balance, etc.) are protected at the
        // form-request boundary — UpdateProfileRequest::validated() only returns the 4 safe profile fields.
        // Admin panel access is restricted to super_admin role via Filament auth.
    ];

    protected $attributes = [
        'notification_preferences' => '{"notify_quiz_results":true,"notify_purchases":true,"notify_weekly_digest":false}',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'email_otp_expires_at'       => 'datetime',
            'password'                   => 'hashed',
            'is_active'                  => 'boolean',
            'wallet_balance'             => 'decimal:2',
            'payout_details'             => 'array',
            'notification_preferences'   => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->role === 'super_admin',
            'creator' => in_array($this->role, ['creator', 'super_admin']),
            default => false,
        };
    }

    /** Allow super_admin to impersonate others (but not other super_admins). */
    public function canImpersonate(): bool
    {
        return $this->role === 'super_admin';
    }

    public function canBeImpersonated(): bool
    {
        return $this->role !== 'super_admin';
    }

    public function quizzes() { return $this->hasMany(Quiz::class, 'creator_id'); }
    public function favourites() { return $this->hasMany(QuizFavourite::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }
    public function activeSubscription() { return $this->hasOne(Subscription::class)->where('status', 'active')->latestOfMany(); }
    public function orders() { return $this->hasMany(Order::class); }
    public function enrollments() { return $this->hasMany(QuizEnrollment::class); }
    public function attempts() { return $this->hasMany(Attempt::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
    public function aiGenerationLogs() { return $this->hasMany(AiGenerationLog::class); }
    public function aiCreditTransactions() { return $this->hasMany(AiCreditTransaction::class); }
    public function payouts() { return $this->hasMany(CreatorPayout::class, 'creator_id'); }
}
