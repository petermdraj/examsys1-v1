<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAssignment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'quiz_id',
        'lecturer_id',
        'student_batch_id',
        'user_id',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function batch()
    {
        return $this->belongsTo(StudentBatch::class, 'student_batch_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isBatchAssignment(): bool
    {
        return $this->student_batch_id !== null;
    }

    public function isIndividualAssignment(): bool
    {
        return $this->user_id !== null;
    }
}
