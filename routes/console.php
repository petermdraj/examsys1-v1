<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('quizora:recalculate-stats --published')->daily()->runInBackground();
\Illuminate\Support\Facades\Schedule::job(\App\Jobs\HardDeleteExpiredUsersJob::class)->daily();
\Illuminate\Support\Facades\Schedule::job(\App\Jobs\SendWeeklyDigestJob::class)->weeklyOn(1, '8:00');
// Subscription lifecycle: send renewal reminders, mark expired, enforce grace period
\Illuminate\Support\Facades\Schedule::command('quizora:process-subscription-expiry')->dailyAt('07:00');
