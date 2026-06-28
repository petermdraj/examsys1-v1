<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizEnrollment extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['quiz_id','user_id','enrolled_at','source','order_id'];
    protected function casts(): array { return ['enrolled_at' => 'datetime']; }

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function order() { return $this->belongsTo(Order::class); }
    public function attempts() { return $this->hasMany(Attempt::class, 'enrollment_id'); }
}
