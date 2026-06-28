<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AiCreditTransaction extends Model
{
    use HasUuid;
    public $timestamps = false;
    protected $fillable = ['user_id','type','amount','balance_after','description','reference_id'];
    protected function casts(): array { return ['created_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
}
