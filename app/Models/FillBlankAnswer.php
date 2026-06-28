<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FillBlankAnswer extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['question_id','answer','is_regex'];
    protected function casts(): array { return ['is_regex' => 'boolean']; }
    public function question() { return $this->belongsTo(Question::class); }
}
