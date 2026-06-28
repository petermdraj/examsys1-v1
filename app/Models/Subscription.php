<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasUuid;
    protected $fillable = ['user_id','plan_id','status','billing_cycle','current_period_start',
        'current_period_end','gateway','gateway_subscription_id','reminder_sent_at'];
    protected function casts(): array {
        return [
            'current_period_start' => 'datetime',
            'current_period_end'   => 'datetime',
            'reminder_sent_at'     => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function plan() { return $this->belongsTo(Plan::class); }
}
