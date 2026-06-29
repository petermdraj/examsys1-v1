<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBatch extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function students()
    {
        return $this->hasMany(User::class, 'student_batch_id')->where('role', 'student');
    }

    public function quizAssignments()
    {
        return $this->hasMany(QuizAssignment::class);
    }
}
