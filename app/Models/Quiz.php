<?php
namespace App\Models;
use App\Traits\BelongsToCreator;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, BelongsToCreator, HasUuid, SoftDeletes;
    protected $fillable = ['creator_id','category_id','title','slug','description','cover_image','status',
        'visibility','price','currency','duration_minutes','start_at','end_at','max_attempts','pass_percentage',
        'shuffle_questions','shuffle_options','show_result_immediately','allow_review_after_submit',
        'negative_marking_enabled','proctoring_enabled','certificate_enabled','certificate_template','eligibility_rules',
        'meta_keywords','meta_description','total_questions','total_marks','total_attempts','average_score'];
    protected function casts(): array {
        return [
            'eligibility_rules' => 'array', 'shuffle_questions' => 'boolean', 'shuffle_options' => 'boolean',
            'show_result_immediately' => 'boolean', 'allow_review_after_submit' => 'boolean',
            'negative_marking_enabled' => 'boolean', 'proctoring_enabled' => 'boolean',
            'certificate_enabled' => 'boolean', 'price' => 'decimal:2', 'total_marks' => 'decimal:2',
            'average_score' => 'decimal:2', 'start_at' => 'datetime', 'end_at' => 'datetime',
        ];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function questions() { return $this->hasMany(Question::class)->orderBy('sort_order'); }

    // Questions assigned from the question bank via pivot
    public function bankQuestions() { return $this->belongsToMany(Question::class, 'quiz_questions')->withPivot('sort_order')->orderByPivot('sort_order'); }

    /**
     * All questions for this quiz: direct (quiz_id = this) + bank-assigned, deduplicated and ordered.
     */
    public function allQuestions()
    {
        $direct = $this->questions()->get();
        $banked = $this->bankQuestions()->get();
        return $direct->merge($banked)->unique('id')->sortBy('sort_order')->values();
    }
    public function enrollments() { return $this->hasMany(QuizEnrollment::class); }
    public function attempts() { return $this->hasMany(Attempt::class); }
    public function orders() { return $this->hasMany(Order::class); }

    public function scopePublished($q) { return $q->where('status', 'published'); }
    public function scopePublic($q) { return $q->where('visibility', 'public'); }
    public function isFree(): bool { return $this->price == 0; }

    protected static function booted(): void
    {
        static::updated(function (self $quiz) {
            if ($quiz->wasChanged('certificate_template')) {
                // Invalidate cached PDFs so they are regenerated with the new template
                $quiz->certificates()->update(['pdf_path' => null]);
            }
        });
    }

    public function certificates() { return $this->hasMany(Certificate::class); }
    public function favourites()   { return $this->hasMany(QuizFavourite::class); }
}
