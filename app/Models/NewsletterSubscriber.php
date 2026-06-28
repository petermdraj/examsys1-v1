<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasUuid;

    protected $fillable = [
        'email',
        'name',
        'source',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'subscribed_at'    => 'datetime',
        'unsubscribed_at'  => 'datetime',
    ];
}
