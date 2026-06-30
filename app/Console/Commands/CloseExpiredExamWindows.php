<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use App\Services\Exam\AttemptInterventionService;
use Illuminate\Console\Command;

class CloseExpiredExamWindows extends Command
{
    protected $signature = 'examsys:close-exam-windows';

    protected $description = 'Force-submit in-progress attempts when a quiz window ends (if enabled)';

    public function handle(AttemptInterventionService $interventions): int
    {
        $submitted = 0;

        Quiz::query()
            ->where('status', 'published')
            ->where('force_submit_at_end', true)
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->whereHas('attempts', fn ($q) => $q->whereIn('status', ['in_progress', 'paused']))
            ->with(['attempts' => fn ($q) => $q->whereIn('status', ['in_progress', 'paused'])])
            ->each(function (Quiz $quiz) use ($interventions, &$submitted) {
                foreach ($quiz->attempts as $attempt) {
                    $interventions->terminate($attempt);
                    $submitted++;
                }
            });

        $this->info("Force-submitted {$submitted} attempt(s) at window close.");

        return self::SUCCESS;
    }
}
