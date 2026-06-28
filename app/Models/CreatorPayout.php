<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CreatorPayout extends Model
{
    use HasUuid;
    protected $fillable = ['creator_id','amount','status','gateway','gateway_reference','note','admin_note','requested_at','processed_at'];
    protected function casts(): array {
        return ['amount' => 'decimal:2', 'requested_at' => 'datetime', 'processed_at' => 'datetime'];
    }

    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
}
