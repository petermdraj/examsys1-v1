<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class QuizFavourite extends Model
{
    use HasUuid;

    public $timestamps = true;
    const UPDATED_AT = null; // only track created_at

    protected $fillable = ['user_id', 'quiz_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }
}
