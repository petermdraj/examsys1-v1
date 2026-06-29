<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['quiz_id','lecturer_id','type','content','explanation','marks','negative_marks',
        'time_limit_seconds','sort_order','is_mandatory','hint','difficulty','collection_id','category_id','source_bank_question_id'];

    protected function casts(): array {
        return ['marks' => 'decimal:2', 'negative_marks' => 'decimal:2', 'is_mandatory' => 'boolean'];
    }

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function lecturer() { return $this->belongsTo(User::class, 'lecturer_id'); }
    public function options() { return $this->hasMany(QuestionOption::class)->orderBy('sort_order'); }
    public function fillBlankAnswers() { return $this->hasMany(FillBlankAnswer::class); }
    public function attemptAnswers() { return $this->hasMany(AttemptAnswer::class); }
    public function collection() { return $this->belongsTo(QuestionCollection::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function sourceQuestion() { return $this->belongsTo(Question::class, 'source_bank_question_id'); }
    public function derivedQuestions() { return $this->hasMany(Question::class, 'source_bank_question_id'); }

    /** Scope: bank questions (no quiz) owned by a creator */
    public function scopeBankFor($query, string $creatorId)
    {
        return $query->whereNull('quiz_id')->where('lecturer_id', $creatorId);
    }

    /** Count distinct quizzes this bank question has been imported into */
    public function usedInQuizzesCount(): int
    {
        return Question::where('source_bank_question_id', $this->id)
            ->whereNotNull('quiz_id')
            ->distinct()
            ->count('quiz_id');
    }
}
