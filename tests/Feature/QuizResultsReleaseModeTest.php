<?php

namespace Tests\Feature;

use App\Filament\Lecturer\Resources\QuizResource;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizResultsReleaseModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_immediate_mode_results_are_visible_without_publish(): void
    {
        $quiz = Quiz::factory()->create([
            'show_result_immediately'      => true,
            'hold_results_until_published' => false,
            'results_published_at'         => null,
        ]);

        $this->assertTrue($quiz->resultsAreVisible());
    }

    public function test_held_mode_results_are_hidden_until_published(): void
    {
        $quiz = Quiz::factory()->create([
            'show_result_immediately'      => false,
            'hold_results_until_published' => true,
            'results_published_at'         => null,
        ]);

        $this->assertFalse($quiz->resultsAreVisible());

        $quiz->update(['results_published_at' => now()]);

        $this->assertTrue($quiz->fresh()->resultsAreVisible());
    }

    public function test_apply_results_release_mode_maps_immediate(): void
    {
        $data = ['results_release_mode' => 'immediate', 'title' => 'Test'];

        QuizResource::applyResultsReleaseMode($data);

        $this->assertTrue($data['show_result_immediately']);
        $this->assertFalse($data['hold_results_until_published']);
        $this->assertArrayNotHasKey('results_release_mode', $data);
    }

    public function test_apply_results_release_mode_maps_held(): void
    {
        $data = ['results_release_mode' => 'held', 'title' => 'Test'];

        QuizResource::applyResultsReleaseMode($data);

        $this->assertFalse($data['show_result_immediately']);
        $this->assertTrue($data['hold_results_until_published']);
        $this->assertArrayNotHasKey('results_release_mode', $data);
    }

    public function test_legacy_both_true_record_normalizes_to_held(): void
    {
        $quiz = Quiz::factory()->create([
            'show_result_immediately'      => true,
            'hold_results_until_published' => true,
        ]);

        $this->assertSame('held', QuizResource::resultsReleaseModeFromQuiz($quiz));
    }

    public function test_show_result_immediately_false_without_hold_hides_results(): void
    {
        $quiz = Quiz::factory()->create([
            'show_result_immediately'      => false,
            'hold_results_until_published' => false,
        ]);

        $this->assertFalse($quiz->resultsAreVisible());
    }
}
