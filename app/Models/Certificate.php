<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasUuid, HasFactory;
    public $timestamps = false;
    protected $fillable = ['attempt_id','user_id','quiz_id','uuid','issued_at','pdf_path'];
    protected function casts(): array { return ['issued_at' => 'datetime', 'created_at' => 'datetime']; }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) $model->uuid = (string) Str::uuid();
        });
    }

    public function attempt() { return $this->belongsTo(Attempt::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }
}
