<?php

namespace App\Models;

use App\Traits\BelongsToLecturer;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class QuestionCollection extends Model
{
    use HasUuid, BelongsToLecturer;

    protected $fillable = ['lecturer_id', 'name', 'description', 'color'];

    public function questions()
    {
        return $this->hasMany(Question::class, 'collection_id');
    }
}
