<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuid;
    protected $fillable = ['user_id','quiz_id','amount','currency','platform_commission','creator_earning',
        'status','gateway','gateway_order_id','gateway_payment_id','gateway_response','paid_at','settled_at'];
    protected function casts(): array {
        return ['amount' => 'decimal:2', 'platform_commission' => 'decimal:2', 'creator_earning' => 'decimal:2',
            'gateway_response' => 'array', 'paid_at' => 'datetime', 'settled_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function enrollment() { return $this->hasOne(QuizEnrollment::class); }
}
