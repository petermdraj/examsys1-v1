<?php

namespace App\Models;

use App\Traits\BelongsToLecturer;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use BelongsToLecturer, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'lecturer_id', 'category_id', 'title', 'slug', 'description', 'cover_image', 'status',
        'visibility', 'duration_minutes', 'start_at', 'end_at', 'max_attempts', 'pass_percentage',
        'shuffle_questions', 'shuffle_options', 'show_result_immediately', 'allow_review_after_submit',
        'negative_marking_enabled', 'proctoring_enabled', 'certificate_enabled', 'certificate_template',
        'eligibility_rules', 'meta_keywords', 'meta_description', 'total_questions', 'total_marks',
        'total_attempts', 'average_score',
    ];

    protected function casts(): array
    {
        return [
            'eligibility_rules'          => 'array',
            'shuffle_questions'          => 'boolean',
            'shuffle_options'            => 'boolean',
            'show_result_immediately'    => 'boolean',
            'allow_review_after_submit'  => 'boolean',
            'negative_marking_enabled'   => 'boolean',
            'proctoring_enabled'         => 'boolean',
            'certificate_enabled'        => 'boolean',
            'total_marks'                => 'decimal:2',
            'average_score'              => 'decimal:2',
            'start_at'                   => 'datetime',
            'end_at'                     => 'datetime',
        ];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function questions() { return $this->hasMany(Question::class)->orderBy('sort_order'); }
    public function bankQuestions() { return $this->belongsToMany(Question::class, 'quiz_questions')->withPivot('sort_order')->orderByPivot('sort_order'); }

    public function allQuestions()
    {
        $direct = $this->questions()->get();
        $banked = $this->bankQuestions()->get();

        return $direct->merge($banked)->unique('id')->sortBy('sort_order')->values();
    }

    public function enrollments() { return $this->hasMany(QuizEnrollment::class); }
    public function assignments() { return $this->hasMany(QuizAssignment::class); }
    public function attempts() { return $this->hasMany(Attempt::class); }

    public function scopePublished($q) { return $q->where('status', 'published'); }
    public function scopePublic($q) { return $q->where('visibility', 'public'); }

    public function isFree(): bool
    {
        return true;
    }

    protected static function booted(): void
    {
        static::updated(function (self $quiz) {
            if ($quiz->wasChanged('certificate_template')) {
                $quiz->certificates()->update(['pdf_path' => null]);
            }
        });
    }

    public function certificates() { return $this->hasMany(Certificate::class); }
    public function favourites() { return $this->hasMany(QuizFavourite::class); }
}
