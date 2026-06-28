<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait BelongsToCreator
{
    protected static function bootBelongsToCreator(): void
    {
        static::creating(function ($model) {
            if (empty($model->creator_id) && Auth::check()) {
                $model->creator_id = Auth::id();
            }
        });
    }

    public function scopeByCreator($query, $userId = null)
    {
        return $query->where('creator_id', $userId ?? Auth::id());
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'creator_id');
    }
}
