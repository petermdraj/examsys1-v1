<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QuizFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        return [
            'lecturer_id'               => User::factory(),
            'category_id'              => Category::factory(),
            'title'                    => $title,
            'slug'                     => Str::slug($title) . '-' . Str::random(4),
            'description'              => $this->faker->paragraph(),
            'status'                   => 'published',
            'visibility'               => 'public',
            'pass_percentage'          => 60,
            'shuffle_questions'        => false,
            'shuffle_options'          => false,
            'show_result_immediately'  => true,
            'allow_review_after_submit'=> true,
            'negative_marking_enabled' => false,
            'proctoring_enabled'       => false,
            'certificate_enabled'      => false,
            'total_questions'          => 0,
            'total_marks'              => 0,
            'total_attempts'           => 0,
            'average_score'            => 0,
        ];
    }
}
