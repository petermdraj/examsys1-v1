<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['quiz_id','user_id','enrollment_id','attempt_number','status','started_at',
        'submitted_at','time_taken_seconds','score','total_marks','percentage','is_passed','ip_address','user_agent'];
    protected function casts(): array {
        return ['started_at' => 'datetime', 'submitted_at' => 'datetime', 'score' => 'decimal:2',
            'total_marks' => 'decimal:2', 'percentage' => 'decimal:2', 'is_passed' => 'boolean'];
    }

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function enrollment() { return $this->belongsTo(QuizEnrollment::class); }
    public function answers() { return $this->hasMany(AttemptAnswer::class); }
    public function certificate() { return $this->hasOne(Certificate::class); }
}
