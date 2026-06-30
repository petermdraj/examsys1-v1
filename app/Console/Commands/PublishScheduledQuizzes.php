<?php

namespace App\Console\Commands;

use App\Services\Quiz\QuizPublishService;
use Illuminate\Console\Command;

class PublishScheduledQuizzes extends Command
{
    protected $signature = 'examsys:publish-scheduled';

    protected $description = 'Publish quizzes whose scheduled start time has arrived';

    public function handle(QuizPublishService $publishService): int
    {
        $count = $publishService->publishDueScheduled();

        $this->info("Published {$count} scheduled quiz(es).");

        return self::SUCCESS;
    }
}
