<?php
namespace Database\Seeders;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                     => 'Free',
                'slug'                     => 'free',
                'price_monthly'            => 0,
                'price_yearly'             => 0,
                'ai_free_generations'      => 10,
                'ai_charge_per_generation' => 0.50,
                'commission_rate'          => 30,
                'max_published_quizzes'    => 3,
                'max_questions_per_quiz'   => 10,
                'max_questions_in_bank'    => 100,
                'can_sell_paid_quizzes'    => false,
                'sort_order'               => 1,
                'features' => [
                    '3 published quizzes',
                    '10 questions/quiz',
                    '100 Qb limit',
                    '10 AI generations',
                    'Basic analytics',
                ],
            ],
            [
                'name'                     => 'Pro',
                'slug'                     => 'pro',
                'price_monthly'            => 19,
                'price_yearly'             => 190,
                'ai_free_generations'      => 100,
                'ai_charge_per_generation' => 0.25,
                'commission_rate'          => 20,
                'max_published_quizzes'    => 10,
                'max_questions_per_quiz'   => 100,
                'max_questions_in_bank'    => 1000,
                'can_sell_paid_quizzes'    => true,
                'sort_order'               => 2,
                'features' => [
                    '10 published quizzes',
                    '100 questions/quiz',
                    '1,000 Qb limit',
                    '100 AI generations/mo',
                    'Sell paid quizzes',
                    'Advanced analytics',
                    'Certificates',
                ],
            ],
            [
                'name'                     => 'Business',
                'slug'                     => 'business',
                'price_monthly'            => 49,
                'price_yearly'             => 490,
                'ai_free_generations'      => 500,
                'ai_charge_per_generation' => 0.10,
                'commission_rate'          => 10,
                'max_published_quizzes'    => 50,
                'max_questions_per_quiz'   => 500,
                'max_questions_in_bank'    => null,   // null = Unlimited
                'can_sell_paid_quizzes'    => true,
                'sort_order'               => 3,
                'features' => [
                    '50 published quizzes',
                    '500 questions/quiz',
                    'Unlimited Qb',
                    '500 AI generations/mo',
                    '10% commission only',
                    'Priority support',
                    'White-label options',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
