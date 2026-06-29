<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait BelongsToLecturer
{
    protected static function bootBelongsToLecturer(): void
    {
        static::creating(function ($model) {
            if (empty($model->lecturer_id) && Auth::check()) {
                $model->lecturer_id = Auth::id();
            }
        });
    }

    public function scopeByCreator($query, $userId = null)
    {
        return $query->where('lecturer_id', $userId ?? Auth::id());
    }

    public function lecturer()
    {
        return $this->belongsTo(\App\Models\User::class, 'lecturer_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'lecturer_id');
    }
}
