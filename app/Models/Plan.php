<?php
namespace App\Models;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasUuid;
    protected $fillable = ['name','slug','price_monthly','price_yearly','ai_free_generations',
        'ai_charge_per_generation','commission_rate','max_published_quizzes','can_sell_paid_quizzes',
        'max_questions_per_quiz','max_questions_in_bank',
        'allow_custom_certificate_logo',
        'features','is_active','sort_order'];
    protected function casts(): array {
        return [
            'features' => 'array', 'is_active' => 'boolean',
            'can_sell_paid_quizzes' => 'boolean',
            'allow_custom_certificate_logo' => 'boolean',
            'price_monthly' => 'decimal:2', 'price_yearly' => 'decimal:2',
        ];
    }

    public function subscriptions() { return $this->hasMany(Subscription::class); }
}
