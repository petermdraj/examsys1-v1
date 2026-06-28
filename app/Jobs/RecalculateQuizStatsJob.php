<?php

namespace App\Jobs;

use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateQuizStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Quiz $quiz) {}

    public function handle(): void
    {
        $attempts = Attempt::where('quiz_id', $this->quiz->id)
            ->where('status', 'completed')
            ->get();

        $this->quiz->update([
            'total_attempts' => $attempts->count(),
            'average_score'  => round($attempts->avg('percentage') ?? 0, 2),
        ]);
    }
}
