<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['question_id','content','is_correct','sort_order'];
    protected function casts(): array { return ['is_correct' => 'boolean']; }
    public function question() { return $this->belongsTo(Question::class); }
}
