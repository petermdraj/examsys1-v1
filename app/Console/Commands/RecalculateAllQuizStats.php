<?php

namespace App\Console\Commands;

use App\Jobs\RecalculateQuizStatsJob;
use App\Models\Quiz;
use Illuminate\Console\Command;

class RecalculateAllQuizStats extends Command
{
    protected $signature   = 'quizora:recalculate-stats {--published : Only published quizzes}';
    protected $description = 'Recalculate attempt stats for all quizzes';

    public function handle(): void
    {
        $query = Quiz::query();
        if ($this->option('published')) {
            $query->where('status', 'published');
        }

        $quizzes = $query->get();
        $bar     = $this->output->createProgressBar($quizzes->count());

        foreach ($quizzes as $quiz) {
            RecalculateQuizStatsJob::dispatch($quiz);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched stats recalculation for {$quizzes->count()} quizzes.");
    }
}
