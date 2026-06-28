<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptAnswer extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['attempt_id','question_id','selected_options','text_answer','is_correct',
        'marks_earned','is_marked_for_review','time_spent_seconds','answered_at'];
    protected function casts(): array {
        return ['selected_options' => 'array', 'is_correct' => 'boolean', 'marks_earned' => 'decimal:2',
            'is_marked_for_review' => 'boolean', 'answered_at' => 'datetime'];
    }

    public function attempt() { return $this->belongsTo(Attempt::class); }
    public function question() { return $this->belongsTo(Question::class); }
}
