<?php

namespace App\Models;

use App\Traits\BelongsToCreator;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class QuestionCollection extends Model
{
    use HasUuid, BelongsToCreator;

    protected $fillable = ['creator_id', 'name', 'description', 'color'];

    public function questions()
    {
        return $this->hasMany(Question::class, 'collection_id');
    }
}
