<?php

namespace App\Jobs;

use App\Models\Quiz;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct() {}

    public function handle(): void
    {
        // Fetch the 6 newest published quizzes from the past 7 days
        $newQuizzes = Quiz::published()
            ->with('category', 'creator')
            ->where('created_at', '>=', now()->subWeek())
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // Nothing new this week — skip the blast entirely
        if ($newQuizzes->isEmpty()) {
            return;
        }

        $available = array_keys(config('app.available_locales', ['en' => 'English']));

        // Send in chunks to avoid memory spikes on large user tables
        User::where('is_active', true)
            ->whereNotNull('email_verified_at')
            ->chunkById(200, function ($users) use ($newQuizzes, $available) {
                foreach ($users as $user) {
                    $prefs = $user->notification_preferences ?? [];
                    if (($prefs['notify_weekly_digest'] ?? false) === false) {
                        continue;
                    }

                    // Set locale per-user for each email send
                    $locale = $user->preferred_locale ?? config('app.locale', 'en');
                    if (!in_array($locale, $available)) {
                        $locale = config('app.locale', 'en');
                    }
                    App::setLocale($locale);
                    Carbon::setLocale($locale);

                    Mail::send(
                        'emails.weekly-digest',
                        ['user' => $user, 'quizzes' => $newQuizzes],
                        fn ($m) => $m->to($user->email, $user->name)
                                     ->subject(__('emails.digest_subject'))
                    );
                }

                // Restore default locale after chunk
                App::setLocale(config('app.locale', 'en'));
                Carbon::setLocale(config('app.locale', 'en'));
            });
    }
}
