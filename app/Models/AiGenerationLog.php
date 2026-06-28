<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AiGenerationLog extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected $fillable = ['user_id','quiz_id','prompt','options','questions_generated','tokens_used',
        'charge_applied','was_free','model','status','error_message'];
    protected function casts(): array {
        return ['options' => 'array', 'was_free' => 'boolean', 'charge_applied' => 'decimal:4',
            'created_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }
}
