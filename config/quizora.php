<?php

return [
    'demo_mode'                   => env('DEMO_MODE', false),
    'ai_free_generations_default' => env('QUIZORA_AI_FREE_GENS', 10),
    'ai_charge_per_gen'           => env('QUIZORA_AI_CHARGE_PER_GEN', 5.00),
    'platform_commission'         => env('QUIZORA_COMMISSION', 20),
];
